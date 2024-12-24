<?php

namespace App\MessageHandler;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Message\CheckStarsLists;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
final class CheckStarsListsHandler
{
    
    public function __construct(private LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CheckStarsLists $message): void
    {
        $this->logger->notice((new \DateTime())->format('Y-m-d H:i:s') . '>>>> Starting Rising Stars checks...');

        // Fetch stories and initialize genre map
        $storys = $this->entityManager->getRepository(Story::class)->findAll();
        $storyGenreMap = [];
        $allStories = [];
    
        foreach (RSMatch::ALL_GENRES as $genre) {
            $storyGenreMap[$genre] = [];
        }
    
        foreach ($storys as $story) {
            $storyId = $story->getStoryId();
            $allStories[] = $storyId;
    
            // Check all genres for the story
            foreach (RSMatch::ALL_GENRES as $genre) {
                $storyGenreMap[$genre][] = $storyId;
            }
        }
        // Check main page
        $this->logger->notice((new \DateTime())->format('Y-m-d H:i:s') . '>>>> Checking Rising Stars main page...');
        $mainPageUrl = "https://www.royalroad.com/fictions/rising-stars";
        $mainPageContent = $this->fetchPageContent($mainPageUrl);
        
        if ($mainPageContent) {
            $crawler = new Crawler($mainPageContent, $mainPageUrl);
            $matches = $this->findFictionListMatches($crawler, $allStories);
            $this->processMatches($matches, 'main');
        }
        
        // Check genre pages
        $this->logger->notice((new \DateTime())->format('Y-m-d H:i:s') . '>>>> Checking Rising Stars genre pages...');
        $baseGenreUrl = "https://www.royalroad.com/fictions/rising-stars?genre=";
        
        foreach (RSMatch::ALL_GENRES as $genre) {
            $genreUrl = $baseGenreUrl . urlencode($genre);
            $genrePageContent = $this->fetchPageContent($genreUrl);
        
            if ($genrePageContent) {
                $crawler = new Crawler($genrePageContent, $genreUrl);
                // Pass $allStories to check all stories for the current genre
                $matches = $this->findFictionListMatches($crawler, $allStories);
                $this->processMatches($matches, $genre);
            }
        }
        
        $this->logger->notice((new \DateTime())->format('Y-m-d H:i:s') . ">>>> Rising Stars checks complete. <<<<");
    }
    
    private function fetchPageContent($url)
    {
        $content = @file_get_contents($url);
        if ($content === false) {
            $this->logger->error((new \DateTime())->format('Y-m-d H:i:s') . "Failed to fetch content from {$url}");
        }
        return $content;
    }
    
    private function findFictionListMatches($crawler, $storyIds)
    {
        $matches = [];
        $crawler->filter('.fiction-list .fiction-list-item')->each(function (Crawler $node, $position) use ($storyIds, &$matches) {
            $link = $node->filter('a')->link();
            foreach ($storyIds as $storyId) {
                if (strpos($link->getUri(), '/fiction/' . $storyId) !== false) {
                    $matches[] = ['storyId' => $storyId, 'position' => $position + 1];
                }
            }
        });
        return $matches;
    }
    
    private function processMatches($matches, $genre)
    {
        $storyIds = array_column($matches, 'storyId');
        $existingStories = $this->entityManager->getRepository(Story::class)
            ->createQueryBuilder('s')
            ->where('s.storyId IN (:ids)')
            ->setParameter('ids', $storyIds)
            ->getQuery()
            ->getResult();
    
        $existingStoriesMap = [];
        foreach ($existingStories as $story) {
            $existingStoriesMap[$story->getStoryId()] = $story;
        }
    
        $newEntries = [];
        foreach ($matches as $match) {
            $storyId = $match['storyId'];
            $position = $match['position'];
    
            if (!isset($existingStoriesMap[$storyId])) {
                $this->logger->notice((new \DateTime())->format('Y-m-d H:i:s') . "Story ID {$storyId} found in matches but does not exist in the database. Skipping.");
                continue;
            }
    
            $storyEntity = $existingStoriesMap[$storyId];
            $existingEntry = $this->entityManager->getRepository(RSMatch::class)
                ->findOneBy(['storyID' => $storyEntity, 'genre' => $genre, 'active' => 1]);
    
            if ($existingEntry) {
                if ($existingEntry->getHighestPosition() > $position) {
                    $existingEntry->setHighestPosition($position);
                }
            } else {
                $newEntry = new RSMatch();
                $newEntry->setStoryID($storyEntity);
                $newEntry->setGenre($genre);
                $newEntry->setHighestPosition($position);
                $newEntry->setDate(new \DateTime());
                $newEntry->setActive(1);
                $newEntries[] = $newEntry;
            }
        }
    
        foreach ($newEntries as $entry) {
            $this->entityManager->persist($entry);
        }
    
        $activeEntries = $this->entityManager->getRepository(RSMatch::class)
            ->findBy(['active' => 1, 'genre' => $genre]);
    
        $this->deactivateUnmatchedEntries($activeEntries, $storyIds, $genre);
        $this->entityManager->flush();
    }
    
    private function deactivateUnmatchedEntries($activeEntries, $matchedIds, $genre)
    {
        foreach ($activeEntries as $entry) {
            if (!in_array($entry->getStoryID()->getStoryId(), $matchedIds)) {
                $entry->setActive(0);
                $entry->setRemovedDate(new \DateTime());
            }
        }
    }
}
