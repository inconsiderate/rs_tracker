<?php

namespace App\MessageHandler;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Message\CheckStarsLists;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final class CheckStarsListsHandler
{
    
    public function __construct(private LoggerInterface $logger, EntityManagerInterface $entityManager, MailerInterface $mailerInterface)
    {
        $this->entityManager = $entityManager;
        $this->mailerInterface = $mailerInterface;
    }

    public function __invoke(CheckStarsLists $message)
    {
        // echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Starting Rising Stars checks...' . PHP_EOL;
        $allStories = [];
        
        // Check main page
        // echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Checking Rising Stars main page...' . PHP_EOL;
        $mainPageUrl = "https://www.royalroad.com/fictions/rising-stars";
        $mainPageContent = $this->fetchPageContent($mainPageUrl);

        $matches = $this->verifyStoriesExist($mainPageContent, $mainPageUrl);
        $this->processMatches($matches, 'main');

        // Check genre pages
        // echo (new \DateTime())->format('Y-m-d H:i:s') . ' >>>> Checking Rising Stars genre pages...' . PHP_EOL;
        $baseGenreUrl = "https://www.royalroad.com/fictions/rising-stars?genre=";
        
        // pull data for all the main genres
        foreach (RSMatch::ALL_GENRES as $genre) {
            $genreUrl = $baseGenreUrl . urlencode($genre);
            $genrePageContent = $this->fetchPageContent($genreUrl);
        
            $matches = $this->verifyStoriesExist($genrePageContent, $genreUrl);
            $this->processMatches($matches, $genre);
        }

        // then pull data for all the secondary tags
        foreach (RSMatch::ALL_TAGS as $genre) {
            $genreUrl = $baseGenreUrl . urlencode($genre);
            $genrePageContent = $this->fetchPageContent($genreUrl);
        
            $matches = $this->verifyStoriesExist($genrePageContent, $genreUrl);
            $this->processMatches($matches, $genre);
        }
        
        // echo (new \DateTime())->format('Y-m-d H:i:s') . " >>>> Rising Stars checks complete." . PHP_EOL;
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

                $htmlContent = file_get_contents($match['storyAddress']);
                
                $crawler = new Crawler($htmlContent);
                $storyName = $crawler->filter('.fic-title h1')->text();
                $authorName = $crawler->filter('.fic-title h4 a')->text();
                $authorProfileUrl = $crawler->filter('.fic-title h4 a')->attr('href');
                preg_match('/\/profile\/(\d+)/', $authorProfileUrl, $authorProfileMatches);
                $authorId = $authorProfileMatches[1] ?? null;


                // echo (new \DateTime())->format('Y-m-d H:i:s') . " {$match['storyName']} not in DB. Creating new story." . PHP_EOL;
                $newStory = new Story();
                $newStory->setStoryName($storyName);
                $newStory->setStoryId($match['storyId']);
                $newStory->setStoryAddress($match['storyAddress']);
                $newStory->setStoryAuthorId($authorId);
                $newStory->setStoryAuthor($authorName);
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
            
            if (preg_match('/\/fiction\/(\d+)\//', $url, $idMatch)) {
                $storyId = $idMatch[1];
            }

            if (strpos($url, '/fiction/') !== false) {
                // Add the match to the list
                $matches[] = [
                    'storyAddress' => $url,
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
                // echo (new \DateTime())->format('Y-m-d H:i:s') . " Story ID {$storyId} found in matches but does not exist in the database. Skipping." . PHP_EOL;
                continue;
            }
            $sendEmail = false;
            $storyEntity = $existingStoriesMap[$storyId];
            $existingEntry = $this->entityManager->getRepository(RSMatch::class)
                ->findOneBy(['storyID' => $storyEntity, 'genre' => $genre, 'active' => 1]);
    
            if ($existingEntry) {
                if ($existingEntry->getHighestPosition() > $position) {
                    // echo (new \DateTime())->format('Y-m-d H:i:s') . " {$existingEntry->getStoryID()->getStoryName()} moved up from {$existingEntry->getHighestPosition()} to {$position} on {$genre}!" . PHP_EOL;
                    $existingEntry->setHighestPosition($position);
                    $mailMessage = "Your tracked story '{$storyEntity->getStoryName()}' has moved up the " . RSMatch::getHumanReadableName($genre) . " list to #{$position}!";
                    $sendEmail = true;

                    if ($position == 1) {
                        // echo (new \DateTime())->format('Y-m-d H:i:s') . " {$existingEntry->getStoryID()->getStoryName()} has reached #{$position} on {$genre}!" . PHP_EOL;
                        $mailMessage = "Your tracked story '{$storyEntity->getStoryName()}' has reached **NUMBER ONE** on the " . RSMatch::getHumanReadableName($genre) . " list! Congrats!";
                        $sendEmail = true;
                    }
                }
            } else {
                // echo (new \DateTime())->format('Y-m-d H:i:s') . " {$storyEntity->getStoryName()} entered the {$genre} list at #{$position} " . PHP_EOL;
                $mailMessage = "Your tracked story '{$storyEntity->getStoryName()}' has entered the " . RSMatch::getHumanReadableName($genre) . " list at #{$position}!";
                $sendEmail = true;

                $newEntry = new RSMatch();
                $newEntry->setStoryID($storyEntity);
                $newEntry->setGenre($genre);
                $newEntry->setHighestPosition($position);
                $newEntry->setDate(new \DateTime());
                $newEntry->setActive(1);
                $newEntries[] = $newEntry;
            }

            $userList = $storyEntity->getUsers();
            foreach ($userList as $user) {

                // don't send emails if they opt-out
                if (!$user->getSendMeEmails()) {
                    $sendEmail = false;
                }
                // if the position is lower (numerically higher) than the user's minimum rank, do not send
                if ($position > $user->getMinRankToNotify()) {
                    $sendEmail = false;
                }

                // if the user has already recieved a notice for this rise, and it's not at #1, do not send
                if ($position != 1 && $existingEntry && $existingEntry->hasBeenEmailed($user->getId())) {
                    $sendEmail = false;
                }

                // if the genre is in the hidden list only send if they opt-in to hidden list
                if (isset(RSMatch::ALL_TAGS[$genre]) && !$user->getEmailHiddenLists()) {
                    $sendEmail = false;
                }
            
                if ($sendEmail) {
                    // echo (new \DateTime())->format('Y-m-d H:i:s') . " {$storyEntity->getStoryName()} at position {$position} on {$genre}, sending email to: " . $user->getEmailAddress() . PHP_EOL;
                    if ($existingEntry) {
                        $existingEntry->addHasBeenEmailed($user->getId());
                        $this->entityManager->persist($existingEntry);
                    } else {
                        $newEntry->addHasBeenEmailed($user->getId());
                        $this->entityManager->persist($newEntry);
                    }
                    $this->sendEmail($this->mailerInterface, $mailMessage, $user->getEmailAddress());
                }
            }
        }
    
        foreach ($newEntries as $entry) {
            $this->entityManager->persist($entry);
        }
    
        $activeEntries = $this->entityManager->getRepository(RSMatch::class)
            ->findBy(['active' => 1, 'genre' => $genre]);

        $this->deactivateUnmatchedEntries($activeEntries, $storyIds);
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

    private function sendEmail(MailerInterface $mailer, $message, $toAddress)
    {
        $email = (new Email())
            ->from('Royal Road Watch <notifications@royalroadwatch.site>')
            ->to($toAddress)
            ->subject('Your Story is on Rising Stars!')
            ->html($message);

        $mailer->send($email);
        return true;
    }
}
