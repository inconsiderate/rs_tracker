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
            // Fetch open RSMatch rows for the story
            $openMatches = $this->entityManager->getRepository(RSMatch::class)->findOpenMatchesForStory($storyId);
            // Fetch RSMatch rows that were closed in the last 24 hours
            $recentlyClosedMatches = $this->entityManager->getRepository(RSMatch::class)->findRecentlyClosedMatchesForStory($storyId);
            $allMatches = array_merge($openMatches, $recentlyClosedMatches);
        
            // Save highest rank for the day for each genre
            foreach ($allMatches as $match) {
        
                // Create the RSDaily entry
                $daily = new RSDaily();
                $daily->setStory($story);
                $daily->setGenre($match->getGenre());
                $daily->setHighestPosition($match->getHighestPosition());
                $daily->setDate(new \DateTimeImmutable('today'));
    
                $this->entityManager->persist($daily);
            }
        }
        
        $this->entityManager->flush();
    }
}