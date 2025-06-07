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
        $genreScaling = $this->calculateGenreScalingFactors($entityManager, $user);
        // var_dump('Genre scaling factors: ' . json_encode($genreScaling));die;
        $mainListProgress = $this->getMainListProgress($entityManager, $user, $genreScaling, 50);

        return $this->render('trackers.html.twig', [
            'genreMatches' => $genreMatches,
            'tagMatches' => $tagMatches,
            'genreChartData' => json_encode($genreChartData),
            'tagChartData' => json_encode($tagChartData),
            'genreDailyData' => json_encode($genreDailyData),
            'genreScaling' => $genreScaling,
            'mainListProgress' => $mainListProgress,
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

    private function getMainListProgress(EntityManagerInterface $entityManager, $user, $genreScaling, $mainListCutoff = 50): array
    {
        $trackedStories = $entityManager->getRepository(RSMatch::class)->findByUser($user);

        $progress = [];
        $seen = [];
        foreach ($trackedStories as $entry) {
            $story = $entry->getStoryID();
            $storyId = $story->getId();

            if (isset($seen[$storyId])) {
                continue;
            }
            $seen[$storyId] = true;

            $matches = $entityManager->getRepository(RSMatch::class)->findBy(['storyID' => $storyId]);
            $positions = [];
            foreach ($matches as $match) {
                $positions[$match->getGenre()] = $match->getHighestPosition();
            }

            $mainPos = $positions['main'] ?? null;
            unset($positions['main']);
            $bestGenre = null;
            $bestGenrePos = null;
            if (!empty($positions)) {
                $bestGenre = array_keys($positions, min($positions))[0];
                $bestGenrePos = min($positions);
            }

            $scalingFactor = $genreScaling[$bestGenre] ?? null;
            $estimatedMainPos = ($scalingFactor && $bestGenrePos) ? round($bestGenrePos * $scalingFactor) : null;

            $distance = ($estimatedMainPos && $mainPos === null) ? $estimatedMainPos - $mainListCutoff : null;

            $progress[] = [
                'storyName' => $story->getStoryName(),
                'mainPosition' => $mainPos,
                'bestGenre' => $bestGenre,
                'bestGenrePosition' => $bestGenrePos,
                'estimatedMainPosition' => $estimatedMainPos,
                'distanceToMain' => $distance,
            ];
        }

        return $progress;
    }

    private function calculateGenreScalingFactors(EntityManagerInterface $entityManager): array
    {
        $today = (new \DateTime())->format('Y-m-d');

        $mainEntries = $entityManager->getRepository(RSDaily::class)
            ->createQueryBuilder('r')
            ->where('r.genre = :main')
            ->andWhere('r.date = :today')
            ->setParameter('main', 'main')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $genreEntries = $entityManager->getRepository(RSDaily::class)
            ->createQueryBuilder('r')
            ->where('r.genre != :main')
            ->andWhere('r.date = :today')
            ->setParameter('main', 'main')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        $positions = [];
        foreach ($mainEntries as $entry) {
            $storyId = $entry->getStory()->getId();
            $positions[$storyId]['main'] = $entry->getHighestPosition();
        }
        foreach ($genreEntries as $entry) {
            $storyId = $entry->getStory()->getId();
            $genre = $entry->getGenre();
            $positions[$storyId][$genre] = $entry->getHighestPosition();
        }

        $allGenres = RSMatch::ALL_GENRES ?? [];
        $genreScaling = [];
        foreach ($allGenres as $genre) {
            if ($genre === 'main') continue;
            $factors = [];
            foreach ($positions as $storyId => $pos) {
                if (isset($pos['main'], $pos[$genre]) && $pos[$genre] > 0) {
                    $factors[] = $pos['main'] / $pos[$genre];
                }
            }
            if (count($factors) > 0) {
                $genreScaling[$genre] = round(array_sum($factors) / count($factors), 2);
            }
        }

        return $genreScaling;
    }
}
