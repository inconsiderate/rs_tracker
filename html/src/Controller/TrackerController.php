<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Entity\RSDaily;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TrackerController extends AbstractController
{     
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
                $genreMatches[] = [
                    'storyName' => $entry->getStoryID()->getStoryName(),
                    'authorName' => $entry->getStoryID()->getStoryAuthor(),
                    'date' => $entry->getDate(),
                    'genre' => $humanReadableGenre,
                    'rsLink' => "https://www.royalroad.com/fictions/rising-stars?genre=" . $entry->getGenre(),
                    'highestPosition' => $entry->getHighestPosition(),
                    'timeOnList' => $entry->getTimeOnList(),
                    'timeOnListInt' => $entry->getTimeOnListInt(),
                    'active' => $entry->getActive(),
                ];
                $genreData[$entry->getStoryID()->getStoryName()][] = [
                    'rank' => $entry->getHighestPosition(),
                    'genre' => $humanReadableGenre,
                ];
            } else {
                $tagMatches[] = [
                    'storyName' => $entry->getStoryID()->getStoryName(),
                    'authorName' => $entry->getStoryID()->getStoryAuthor(),
                    'date' => $entry->getDate(),
                    'genre' => $humanReadableGenre,
                    'rsLink' => "https://www.royalroad.com/fictions/rising-stars?genre=" . $entry->getGenre(),
                    'highestPosition' => $entry->getHighestPosition(),
                    'timeOnList' => $entry->getTimeOnList(),
                    'timeOnListInt' => $entry->getTimeOnListInt(),
                    'active' => $entry->getActive(),
                ];

                $tagData[$entry->getStoryID()->getStoryName()][] = [
                    'rank' => $entry->getHighestPosition(),
                    'genre' => $humanReadableGenre,
                ];
            } 
        }
        // remove duplicates and keep only the highest rank for the graph
        foreach ($genreData as $storyName => &$genres) {
            $genres = array_values(array_reduce($genres, function ($carry, $entry) {
                $genre = $entry['genre'];
                if (!isset($carry[$genre]) || $entry['rank'] < $carry[$genre]['rank']) {
                    $carry[$genre] = $entry;
                }
                return $carry;
            }, []));
        }
        unset($genres); 
        // remove duplicates and keep only the highest rank for the graph
        foreach ($tagData as $storyName => &$genres) {
            $genres = array_values(array_reduce($genres, function ($carry, $entry) {
                $genre = $entry['genre'];
                if (!isset($carry[$genre]) || $entry['rank'] < $carry[$genre]['rank']) {
                    $carry[$genre] = $entry;
                }
                return $carry;
            }, []));
        }
        unset($genres); 
        
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


    #[Route('/chart-data', name: 'filtered_chart_data')]
    public function filtered_chart_data(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();

        $startDate = new \DateTime($request->query->get('start'));
        $endDate = new \DateTime($request->query->get('end'));

        $entries = $entityManager->getRepository(RSDaily::class)->findByUser($user);

        $filtered = [];

        foreach ($entries as $entry) {
            $entryDate = $entry->getDate();
            if ($entryDate >= $startDate && $entryDate <= $endDate) {
                $filtered[$entry->getStory()->getStoryName()][] = [
                    'rank' => $entry->getHighestPosition(),
                    'genre' => RSMatch::getHumanReadableName($entry->getGenre()),
                    'day' => $entryDate->format('Y-m-d'),
                ];
            }
        }

        return new JsonResponse($filtered);
    }
}
