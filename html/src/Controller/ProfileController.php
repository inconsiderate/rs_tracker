<?php

namespace App\Controller;

use App\Entity\Story;
use App\Entity\RSMatch;
use App\Form\ChangeUsernameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function app_home(Request $request, EntityManagerInterface $entityManager): Response
    {        
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to access this functionality.');
        }

        $changeUsernameForm = $this->createForm(ChangeUsernameType::class);
        $changeUsernameForm->handleRequest($request);

        if ($changeUsernameForm->isSubmitted() && $changeUsernameForm->isValid()) {
            $data = $changeUsernameForm->getData();
            $newUsername = $data['username'];
            $user->setUsername($newUsername);
            $this->addFlash('success', 'Username successfully changed to: ' . $newUsername);
        }

        $userPreferencesForm = $this->createFormBuilder()
        ->add('sendMeEmails', CheckboxType::class, [
            'label' => 'sendMeEmails',
            'attr' => ['placeholder' => 'sendMeEmails'],
            'data' => $user->getSendMeEmails()
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();
        $userPreferencesForm->handleRequest($request);

        if ($userPreferencesForm->isSubmitted() && $userPreferencesForm->isValid()) {
            $preferencesData = $userPreferencesForm->getData();
            $user->setPreferences(['sendMeEmails' => $preferencesData['sendMeEmails']]);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('profile/profile.html.twig', [
            'usernameForm' => $changeUsernameForm->createView(),
            'userPreferencesForm' => $userPreferencesForm->createView(),
            // 'userProPreferencesForm' => $userProPreferencesForm->createView(),
        ]);
    }
}

?>