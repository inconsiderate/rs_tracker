<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Entity\RSDaily;
use App\Form\StoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class DefaultController extends AbstractController
{     
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_home')]
    public function app_home(EntityManagerInterface $entityManager): Response
    {                
        $genreNumberOnes = [];
        $recentFeedItems = [];
        $recentFeed = $entityManager->getRepository(RSMatch::class)->findStoriesRecentlyAdded();
        $mainNumberOne = $entityManager->getRepository(RSMatch::class)->findStoryWithLongestTimeAtNumberOne('main');
        foreach (RSMatch::ALL_GENRES as $genre) {
            $item = $entityManager->getRepository(RSMatch::class)->findStoryWithLongestTimeAtNumberOne($genre);        
            $genreNumberOnes[$genre]['name'] = $item['story']->getStoryName();
            $genreNumberOnes[$genre]['author'] = $item['story']->getStoryAuthor();
            $genreNumberOnes[$genre]['duration'] = $item['duration'];
            $genreNumberOnes[$genre]['url'] = $item['story']->getStoryAddress();
            $genreNumberOnes[$genre]['genre'] = RSMatch::getHumanReadableName($genre);
        }

        foreach ($recentFeed as $item) {
            $feedItem = [];
            $feedItem['name'] = $item['story']->getStoryName();
            $feedItem['author'] = $item['story']->getStoryAuthor();
            $feedItem['url'] = $item['story']->getStoryAddress();
            $feedItem['id'] = $item['story']->getStoryID();
            $feedItem['genre'] = RSMatch::getHumanReadableName($item['genre']);
            $feedItem['duration'] = $item['duration'];
            $recentFeedItems[] = $feedItem;
        }
        // Render the form view
        return $this->render('homepage.html.twig', [
            'main' => [
                'name' => $mainNumberOne['story']->getStoryName(),
                'author' => $mainNumberOne['story']->getStoryAuthor(),
                'duration' => $mainNumberOne['duration'],
                'url' => $mainNumberOne['story']->getStoryAddress(),
            ],
            'numberOnes' => $genreNumberOnes,
            'recentFeedItems' => $recentFeedItems
        ]);
    }

    #[Route('/trackers', name: 'app_trackers')]
    public function app_trackers(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        // get user data for page view
        $activeEntries = $entityManager->getRepository(RSMatch::class)->findByUser($user);

        $genreMatches = [];
        $tagMatches = [];
        $genreData = [];
        $tagData = [];
        $genreDailyData = [];
        
        $dailyEntries = $entityManager->getRepository(RSDaily::class)->findByUser($user);
        $today = new \DateTime();
        $interval = new \DateInterval('P14D'); // only if within the last 14 days
        $cutoffDate = $today->sub($interval);
        
        foreach ($dailyEntries as $entry) {
            $entryDate = $entry->getDate();
        
            if ($entryDate >= $cutoffDate) {
                $genreDailyData[$entry->getStory()->getStoryName()][] = [
                    'rank' => $entry->getHighestPosition(),
                    'genre' => RSMatch::getHumanReadableName($entry->getGenre()),
                    'day' => $entryDate->format('Y-m-d'),
                ];
            }
        }


        foreach ($activeEntries as $entry) {
            $humanReadableGenre = RSMatch::getHumanReadableName($entry->getGenre());
            if (in_array($entry->getGenre(), RSMatch::ALL_GENRES, true) || $entry->getGenre() == 'main')  {
                if ($entry->getHighestPosition() <= $user->getMinRankToNotify()) {
                    $genreMatches[] = [
                        'storyName' => $entry->getStoryID()->getStoryName(),
                        'authorName' => $entry->getStoryID()->getStoryAuthor(),
                        'date' => $entry->getDate(),
                        'genre' => $humanReadableGenre,
                        'rsLink' => "https://www.royalroad.com/fictions/rising-stars?genre=" . $entry->getGenre(),
                        'highestPosition' => $entry->getHighestPosition(),
                        'timeOnList' => $entry->getTimeOnList(),
                        'active' => $entry->isActive(),
                    ];
                    $genreData[$entry->getStoryID()->getStoryName()][] = [
                        'rank' => $entry->getHighestPosition(),
                        'genre' => $humanReadableGenre,
                    ];
                }
            } else {
                if ($entry->getHighestPosition() <= $user->getMinRankToNotify()) {
                    $tagMatches[] = [
                        'storyName' => $entry->getStoryID()->getStoryName(),
                        'authorName' => $entry->getStoryID()->getStoryAuthor(),
                        'date' => $entry->getDate(),
                        'genre' => $humanReadableGenre,
                        'rsLink' => "https://www.royalroad.com/fictions/rising-stars?genre=" . $entry->getGenre(),
                        'highestPosition' => $entry->getHighestPosition(),
                        'timeOnList' => $entry->getTimeOnList(),
                        'active' => $entry->isActive(),
                    ];

                    $tagData[$entry->getStoryID()->getStoryName()][] = [
                        'rank' => $entry->getHighestPosition(),
                        'genre' => $humanReadableGenre,
                    ];
                }
            }
            
        }

        $genreChartData = [];
        foreach ($genreData as $name => $entries) {
            $genreChartData[] = [
                'label' => $name,
                'data' => array_map(function ($entry) {
                    return [
                        'x' => $entry['genre'],
                        'y' => $entry['rank'],
                        'r' => 10,
                    ];
                }, $entries)
            ];
        }
        $tagChartData = [];
        foreach ($tagData as $name => $entries) {
            $tagChartData[] = [
                'label' => $name,
                'data' => array_map(function ($entry) {
                    return [
                        'x' => $entry['genre'],
                        'y' => $entry['rank'],
                        'r' => 10,
                    ];
                }, $entries)
            ];
        }

        return $this->render('trackers.html.twig', [
            'genreMatches' => $genreMatches,
            'tagMatches' => $tagMatches,
            'genreChartData' => json_encode($genreChartData),
            'tagChartData' => json_encode($tagChartData),
            'genreDailyData' => json_encode($genreDailyData),
        ]);
    }


    #[Route('/support', name: 'app_support')]
    public function app_support(): Response
    {
        return $this->render('support.html.twig');
    }
    
    // create a new story and display existing stories
    #[Route('/trackers/edit', name: 'app_trackers_edit')]
    public function app_trackers_edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        // Create a new Story instance
        $story = new Story();
        $storyForm = $this->createForm(StoryFormType::class, $story);
        $storyForm->handleRequest($request);
        
        if ($storyForm->isSubmitted() && $storyForm->isValid()) {   
            $storyUrl = $request->request->all('story')['storyName'];
            if (preg_match('/\/fiction\/(\d+)\//', $storyUrl, $matches)) {
                $storyId = $matches[1];
            } else {
                $this->addFlash('error', 'Error: No ID was found in the supplied URL');
            }

            $fetchedStory = $entityManager->getRepository(Story::class)->findOneByStoryId($storyId);
            if ($fetchedStory) {
                //story already exists in the DB, add this user to it and we're done
                $fetchedStory->addUser($user);
                $entityManager->persist($fetchedStory);
            } else {
                $htmlContent = file_get_contents($storyUrl);
                if ($htmlContent === false) {
                    $this->addFlash('error', 'Error: No content was found at the supplied URL');
                    return $this->redirectToRoute('app_trackers_edit');
                }
                
                $crawler = new Crawler($htmlContent);
                $storyName = $crawler->filter('.fic-title h1')->text();
                $authorName = $crawler->filter('.fic-title h4 a')->text();
                $authorProfileUrl = $crawler->filter('.fic-title h4 a')->attr('href');
                preg_match('/\/profile\/(\d+)/', $authorProfileUrl, $authorProfileMatches);
                $authorId = $authorProfileMatches[1] ?? null;


                if (!$storyName){
                    return $this->redirectToRoute('app_trackers_edit');
                }
                // Set the logged-in user as the User for the Story
                $story->addUser($user);
                $story->setStoryName($storyName);
                $story->setStoryAuthorId($authorId);
                $story->setStoryAuthor($authorName);
                $story->setStoryId($storyId);
                $story->setStoryAddress($storyUrl);
                $entityManager->persist($story);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_trackers_edit');
        }

        // fetch stories assigned to this user
        $stories = $entityManager->getRepository(Story::class)->createQueryBuilder('s')
            ->innerJoin('s.users', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
        ->getResult();

        $data = [];
        foreach ($stories as $story) {
            $data[] = [
                'storyName' => $story->getStoryName(),
                'authorName' => $story->getStoryAuthor(),
                'id' => $story->getId(),
            ];
        }
        
        // Render the form view
        return $this->render('edit_trackers.html.twig', [
            'form' => $storyForm->createView(),
            'data' => $data,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_tracker', methods: ['POST', 'DELETE'])]
    public function app_trackers_delete(int $id, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        $fetchedStory = $entityManager->getRepository(Story::class)->findOneById($id);
        $user->removeStory($fetchedStory);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_trackers_edit');
    }

    #[Route('/delete/{id}', name: 'refresh_story', methods: ['GET'])]
    public function app_story_refresh(int $id, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        $fetchedStory = $entityManager->getRepository(Story::class)->findOneById($id);
            if (!$fetchedStory) {
                $this->addFlash('error', 'Error: Story not found.');
                return $this->redirectToRoute('app_trackers_edit');
            } else {
                $htmlContent = file_get_contents($fetchedStory->getStoryAddress());
                if ($htmlContent === false) {
                    $this->addFlash('error', 'Error: No content was found at the supplied URL');
                    return $this->redirectToRoute('app_trackers_edit');
                }
                
                $crawler = new Crawler($htmlContent);
                $storyName = $crawler->filter('.fic-title h1')->text();
                $authorName = $crawler->filter('.fic-title h4 a')->text();
                $authorProfileUrl = $crawler->filter('.fic-title h4 a')->attr('href');
                preg_match('/\/profile\/(\d+)/', $authorProfileUrl, $authorProfileMatches);
                $authorId = $authorProfileMatches[1] ?? null;

                $fetchedStory->setStoryName($storyName);
                $fetchedStory->setStoryAuthorId($authorId);
                $fetchedStory->setStoryAuthor($authorName);
                $entityManager->persist($fetchedStory);
            }
            $entityManager->flush();

        $this->addFlash('success', $storyName . ' has been refreshed');
        return $this->redirectToRoute('app_trackers_edit');
    }
}
