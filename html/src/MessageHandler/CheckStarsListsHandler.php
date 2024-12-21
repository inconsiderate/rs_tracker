<?php

namespace App\MessageHandler;

use App\Entity\StoryTracker;
use App\Message\CheckStarsLists;
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
        $this->logger->notice('>>>> Generating arrays for Rising Stars checks...');

        $storys = $this->entityManager->getRepository(StoryTracker::class)->findAll();
        $storyGenreMap = [];
        $allStories = [];
        
        // Initialize the $storyGenreMap with all possible genres as keys and empty arrays as values
        $allGenres = [
            'adventure', 'action', 'comedy', 'contemporary', 'drama', 
            'fantasy', 'historical', 'horror', 'mystery', 
            'psychological', 'romance', 'satire', 'sci_fi', 
            'one_shot', 'tragedy'
        ];
        
        foreach ($allGenres as $genre) {
            $storyGenreMap[$genre] = [];
        }
        
        // Populate the $storyGenreMap
        foreach ($storys as $story) {
            $storyName = $story->getStoryName();
            // Add this story to the main list, if it's not already there
            if (!in_array($storyName, $allStories, true)) {
                $allStories[] = $storyName;
            }

            $genres = $story->getTrackedGenres(); // Assuming this returns an array of genres
        
            foreach ($genres as $genre) {
                if (isset($storyGenreMap[$genre])) {
                    $storyGenreMap[$genre][] = $storyName;
                }
            }
        }

        $this->logger->notice('>>>> Querying Rising Stars main page...');

        $urlMainPage = "https://www.royalroad.com/fictions/rising-stars";
        $pageContent = file_get_contents($urlMainPage);
        $foundMatches = [];
        foreach ($allStories as $string) {
            if (stripos($pageContent, $string) !== false) {
                $foundMatches[] = $string;
            }
        }
        
        $this->logger->notice( "Found RS homepage matches: " . implode(', ', $foundMatches));

        $foundMatches = [];
        $this->logger->notice('>>>> Querying RR genre pages...');
        // Search for the strings in the fetched content
        foreach ($storyGenreMap as $genre => $searchStrings) {

            // construct and hit URLs
            $url = "https://www.royalroad.com/fictions/rising-stars?genre=" . $genre;
            // check for matches for each genre
            $pageContent = file_get_contents($url);
            if ($pageContent === false) {            
                $this->warning->notice( "Failed to fetch content from $url ");
                die("Failed to fetch content from $url");
            }


            foreach ($searchStrings as $string) {
                if (stripos($pageContent, $string) !== false) {
                    $foundMatches[$genre][] = $string;
                }
            }
        }

        // Output the results
        foreach ($foundMatches as $genre => $matches) {
            $this->logger->notice( "Checking genre list: $genre");
            $this->logger->notice( "Found matches: " . implode(', ', $matches));
        }

        
        $this->logger->notice( ">>>> Scheduled Checks Complete <<<< ");
    }


}
