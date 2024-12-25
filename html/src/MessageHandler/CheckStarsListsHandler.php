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

    public function __invoke(CheckStarsLists $message)
    {
        echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Starting Rising Stars checks...' . PHP_EOL;
        $storyGenreMap = [];
        $allStories = [];
        
        foreach (RSMatch::ALL_GENRES as $genre) {
            $storyGenreMap[$genre] = [];
        }
        
        // Check main page
        // echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Checking Rising Stars main page...' . PHP_EOL;
        $mainPageUrl = "https://www.royalroad.com/fictions/rising-stars";
        $mainPageContent = $this->fetchPageContent($mainPageUrl);

        $matches = $this->verifyStoriesExist($mainPageContent, $mainPageUrl);
        $this->processMatches($matches, 'main');


        // Check genre pages
        // echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Checking Rising Stars genre pages...' . PHP_EOL;
        $baseGenreUrl = "https://www.royalroad.com/fictions/rising-stars?genre=";
        
        foreach (RSMatch::ALL_GENRES as $genre) {
            $genreUrl = $baseGenreUrl . urlencode($genre);
            $genrePageContent = $this->fetchPageContent($genreUrl);
        
            $matches = $this->verifyStoriesExist($genrePageContent, $genreUrl);
            $this->processMatches($matches, $genre);

        }
        
        echo (new \DateTime())->format('Y-m-d H:i:s') . " >>>> Rising Stars checks complete." . PHP_EOL;
    }
    
    private function verifyStoriesExist($pageContent, $pageUrl)
    {
        $crawler = new Crawler($pageContent, $pageUrl);
        $matches = $this->findFictionListMatches($crawler);
    
        foreach ($matches as $match) {
            // Check if the story already exists in the database
            $existingStory = $this->entityManager->getRepository(Story::class)->findOneBy(['storyId' => $match['storyId']]); 
            if (!$existingStory) {
                // Create a new Story if it doesn't exist
                $newStory = new Story();
                $newStory->setStoryName($match['storyName']);
                $newStory->setStoryId($match['storyId']);
                $newStory->setStoryAddress($match['storyAddress']);
                $this->entityManager->persist($newStory);
            }
        }
        $this->entityManager->flush();
        return $matches;
    }
    private function fetchPageContent($url)
    {
        $content = @file_get_contents($url);
        if ($content === false) {
            echo (new \DateTime())->format('Y-m-d H:i:s') . " Failed to fetch content from {$url}" . PHP_EOL;
        }
        return $content;
    }
    
    private function findFictionListMatches(Crawler $crawler): array
    {
        $matches = [];
        $crawler->filter('.fiction-list .fiction-list-item')->each(function (Crawler $node, $position) use (&$matches) {
            // Extract the link etc
            $link = $node->filter('a')->link();
            $url = $link->getUri(); // 
            $storyName = $node->filter('h2.fiction-title')->text(); 
            if (preg_match('/\/fiction\/(\d+)\//', $url, $idMatch)) {
                $storyId = $idMatch[1];
            }

            if (strpos($url, '/fiction/') !== false) {
                // Add the match to the list
                $matches[] = [
                    'storyAddress' => $url,
                    'storyName' => $storyName,
                    'position' => $position + 1, 
                    'storyId' => $storyId
                ];
            }
        });
    
        return $matches;
    }
    
    private function processMatches($matches, $genre)
    {
        // echo (new \DateTime())->format('Y-m-d H:i:s') . " Processing matches..." . PHP_EOL;
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
                echo (new \DateTime())->format('Y-m-d H:i:s') . " Story ID {$storyId} found in matches but does not exist in the database. Skipping." . PHP_EOL;
                continue;
            }
    
            $storyEntity = $existingStoriesMap[$storyId];
            $existingEntry = $this->entityManager->getRepository(RSMatch::class)
                ->findOneBy(['storyID' => $storyEntity, 'genre' => $genre, 'active' => 1]);
    
            if ($existingEntry) {
                if ($existingEntry->getHighestPosition() > $position) {
                    echo (new \DateTime())->format('Y-m-d H:i:s') . " Story {$existingEntry->getStoryID()->getStoryName()} moved up from {$existingEntry->getHighestPosition()} to {$position}!" . PHP_EOL;
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
    
        $this->deactivateUnmatchedEntries($activeEntries, $storyIds);
        
        // echo (new \DateTime())->format('Y-m-d H:i:s') . " Processing {$genre} complete" . PHP_EOL;
        $this->entityManager->flush();
    }
    
    private function deactivateUnmatchedEntries($activeEntries, $matchedIds)
    {
        foreach ($activeEntries as $entry) {
            if (!in_array($entry->getStoryID()->getStoryId(), $matchedIds)) {
                $entry->setActive(0);
                $entry->setRemovedDate(new \DateTime());
            }
        }
    }
}
