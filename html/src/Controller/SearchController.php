<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Form\StorySearchFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
}
