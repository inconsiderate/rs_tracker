<?php

namespace App\MessageHandler;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Entity\RSDaily;
use App\Message\DailyStarsLists;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class DailyStarsListsHandler
{
    
    public function __construct(private LoggerInterface $logger, EntityManagerInterface $entityManager, MailerInterface $mailerInterface)
    {
        $this->entityManager = $entityManager;
        $this->mailerInterface = $mailerInterface;
    }

    public function __invoke(DailyStarsLists $message)
    {
        $storiesWithUsers = $this->entityManager->getRepository(Story::class)->findStoriesWithUsers();
    
        foreach ($storiesWithUsers as $story) {
            
            $storyId = $story->getId();
            $openMatches = $this->entityManager->getRepository(RSMatch::class)->findOpenMatchesForStory($storyId);
            $recentlyClosedMatches = $this->entityManager->getRepository(RSMatch::class)->findRecentlyClosedMatchesForStory($storyId);
    
            $allMatches = array_merge($openMatches, $recentlyClosedMatches);
    
            // Group matches by genre and keep only the best rank
            $bestRanksByGenre = [];
    
            foreach ($allMatches as $match) {
                $genre = $match->getGenre();
                $position = $match->getHighestPosition();
    
                if (!isset($bestRanksByGenre[$genre]) || $position < $bestRanksByGenre[$genre]->getHighestPosition()) {
                    $bestRanksByGenre[$genre] = $match;
                }
            }
    
            // Create RSDaily entries for the best ranked match per genre
            foreach ($bestRanksByGenre as $genre => $match) {    
                $daily = new RSDaily();
                $daily->setStory($story);
                $daily->setGenre($genre);
                $daily->setHighestPosition($match->getHighestPosition());
                $daily->setDate(new \DateTimeImmutable('today'));
    
                $this->entityManager->persist($daily);
            }
        }
    
        $this->entityManager->flush();
    }
}