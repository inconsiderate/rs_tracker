<?php

namespace App\Controller;

use App\Entity\Story;
use App\Form\StoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function app_home(): Response
    {
        return $this->render('homepage.html.twig');
    }


    #[Route('/support', name: 'app_support')]
    public function app_support(): Response
    {
        return $this->render('support.html.twig');
    }



    
    // create a new story and display existing stories
    #[Route('/trackers', name: 'app_trackers')]
    public function app_trackers(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        // get data for page view
        $stories = $entityManager->getRepository(Story::class)->findAll();
        $data = [];
        foreach ($stories as $story) {
            $data[] = [
                'storyName' => $story->getStoryName(),
                'trackedGenres' => $story->getTrackedGenres(),
                'id' => $story->getId(),
            ];
        }


        // Create a new Story instance
        $story = new Story();
        $storyForm = $this->createForm(StoryFormType::class, $story);
        $storyForm->handleRequest($request);

        if ($storyForm->isSubmitted() && $storyForm->isValid()) {   
            $storyUrl = $request->request->all('story')['storyName'];

            $htmlContent = file_get_contents($storyUrl);
            if ($htmlContent === false) {
                $this->addFlash('error', 'Error: No content was found at the supplied URL');
                return $this->redirectToRoute('app_trackers');
            }
            
            $crawler = new Crawler($htmlContent);
            $storyName = $crawler->filter('.fic-title h1')->text();

            $trackedGenres = isset($request->request->all('story')['trackedGenres']) ? $request->request->all('story')['trackedGenres'] : null;

            if (!$storyName || !$trackedGenres){
                $this->addFlash('error', 'Error: Both Name and at least one Genre must be selected');
                return $this->redirectToRoute('app_trackers');
            }
            // Set the logged-in user as the UserStories for the Story
            $story->setUserStories($user);
            $story->setStoryName($storyName);
            $story->setStoryAddress($storyUrl);
            $story->setTrackedGenres($trackedGenres);
    
            // Persist the Story entity
            $entityManager->persist($story);
            $entityManager->flush();

            // Redirect to another page (for example, the same profile page after saving)
            return $this->redirectToRoute('app_trackers');
        }

        // Render the form view
        return $this->render('trackers.html.twig', [
            'form' => $storyForm->createView(),
            'data' => $data,
        ]);
    }


    #[Route('/delete/{id}', name: 'delete_tracker', methods: ['POST', 'DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $entry = $entityManager->getRepository(Story::class)->find($id);

        if (!$entry) {
            throw $this->createNotFoundException('Entry not found.');
        }

        // ensure this Story belongs to the logged in user
        $currentUser = $this->getUser();
        if ($currentUser->getId() == $entry->getUserStories()->getId()){        
            $entityManager->remove($entry);
            $entityManager->flush();
        } else {
            $this->addFlash('error', "Error: Cannot delete entries that don't belong to you");
        }

        return $this->redirectToRoute('app_trackers');
    }
}
