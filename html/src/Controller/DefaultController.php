<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
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
    public function app_home(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->render('homepage.html.twig');
        }

        // get user data for page view
        $activeEntries = $entityManager->getRepository(RSMatch::class)
            ->findByUser($user);

        $data = [];
        foreach ($activeEntries as $entry) {
            $data[] = [
                'storyName' => $entry->getStoryID()->getStoryName(),
                'date' => $entry->getDate(),
                'genre' => $entry->getGenre(),
                'highestPosition' => $entry->getHighestPosition(),
                'timeOnList' => $entry->getTimeOnList(),
                'active' => $entry->isActive(),
            ];
        }

        // Render the form view
        return $this->render('homepage.html.twig', [
            'data' => $data,
        ]);
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
            if (preg_match('/\/fiction\/(\d+)\//', $storyUrl, $matches)) {
                $storyId = $matches[1];
            } else {
                $this->addFlash('error', 'Error: No ID was found in the supplied URL');
            }

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
            // Set the logged-in user as the User for the Story
            $story->addUser($user);
            $story->setStoryName($storyName);
            $story->setStoryId($storyId);
            $story->setStoryAddress($storyUrl);
            $story->setTrackedGenres($trackedGenres);
    
            // Persist the Story entity
            $entityManager->persist($story);
            $entityManager->flush();

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
        if ($currentUser->getId() == $entry->getUsers()->getId()){        
            $entityManager->remove($entry);
            $entityManager->flush();
        } else {
            $this->addFlash('error', "Error: Cannot delete entries that don't belong to you");
        }

        return $this->redirectToRoute('app_trackers');
    }
}
