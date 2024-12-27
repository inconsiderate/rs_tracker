<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Form\ChangeUsernameType;
use App\Form\StoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function app_home(Request $request, EntityManagerInterface $entityManager): Response
    {        
        $changeUsernameForm = $this->createForm(ChangeUsernameType::class);
       
        $changeUsernameForm->handleRequest($request);

        if ($changeUsernameForm->isSubmitted() && $changeUsernameForm->isValid()) {
            $data = $changeUsernameForm->getData();
            $newUsername = $data['username'];
            $user = $this->getUser(); 
            $user->setUsername($newUsername);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Username successfully changed to: ' . $newUsername);
        }
        return $this->render('profile/profile.html.twig', [
            'usernameForm' => $changeUsernameForm->createView(),
        ]);
    }

}

?>