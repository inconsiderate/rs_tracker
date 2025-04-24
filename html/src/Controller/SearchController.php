<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Form\StorySearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/{id?}', name: 'app_story_search')]
    public function search(?int $id = null, Request $request, EntityManagerInterface $entityManager): Response
    {
        $query = '';
        $form = $this->createForm(StorySearchFormType::class);

        if ($id) {
            $story = $entityManager->getRepository(Story::class)->findOneByStoryId($id);
    
            return $this->render('search/story_result.html.twig', [
                'hiddenGenres' => RSMatch::ALL_TAGS,
                'form' => $form->createView(),
                'story' => $story,
            ]);

        } else {
            $form->handleRequest($request);
            $stories = [];
            
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $query = $data['query'];
                $stories = $entityManager->getRepository(Story::class)->createQueryBuilder('s')
                    ->where('s.storyName LIKE :query')
                    ->setParameter('query', '%' . $query . '%')
                    ->setMaxResults(15)
                    ->getQuery()
                    ->getResult();
            }
    
            return $this->render('search/search.html.twig', [
                'form' => $form->createView(),
                'query' => $query,
                'stories' => $stories,
            ]);
        }
    }

    #[Route('/get-current-position', name: 'current_position')]
    public function current_position(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $storyId = $request->query->get('story_id');
        $baseGenreUrl = "https://www.royalroad.com/fictions/rising-stars?genre=";
        $genres = [];

        $genres['main'] = $this->fetchStoryPositionByGenre('https://www.royalroad.com/fictions/rising-stars', $storyId);

        foreach (RSMatch::ALL_GENRES as $genre) {
            $genreUrl = $baseGenreUrl . urlencode($genre);
            $genres[$genre] = $this->fetchStoryPositionByGenre($genreUrl, $storyId);
        }
        
        return $this->json($genres);
    }
    
    private function fetchStoryPositionByGenre($genreUrl, $targetStoryId)
    {
        $content = @file_get_contents($genreUrl);
        if ($content === false) {
            throw new \Exception("Failed to fetch content from {$genreUrl}");
        }
    
        $crawler = new Crawler($content, $genreUrl);
        $genrePosition = '-';
    
        $crawler->filter('.fiction-list .fiction-list-item')->each(function (Crawler $node, $position) use (&$genrePosition, $targetStoryId) {
            $link = $node->filter('a')->link()->getUri();
    
            if (preg_match('/\/fiction\/(\d+)\//', $link, $matches)) {
                $storyId = $matches[1];
                if ((int)$storyId === (int)$targetStoryId) {
                    $genrePosition = $position + 1;
                }
            }
        });
    
        return $genrePosition;
    }
}
