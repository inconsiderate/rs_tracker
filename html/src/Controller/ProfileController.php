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
use Symfony\Component\Form\Extension\Core\Type\RangeType;
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
        // handle username change form
        $changeUsernameForm = $this->createForm(ChangeUsernameType::class);
        $changeUsernameForm->handleRequest($request);

        if ($changeUsernameForm->isSubmitted() && $changeUsernameForm->isValid()) {
            $data = $changeUsernameForm->getData();
            $newUsername = $data['username'];
            $user->setUsername($newUsername);
            $this->addFlash('success', 'Username successfully changed to: ' . $newUsername);
        }

        // handle basic user preferences
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
            $user->setPreference('sendMeEmails', $preferencesData['sendMeEmails']);
        }

        // handle PRO user preferences
        $userProPreferencesForm = $this->createFormBuilder()
        ->add('displayHiddenLists', CheckboxType::class, [
            'label' => 'displayHiddenLists',
            'attr' => ['placeholder' => 'displayHiddenLists'],
            'data' => $user->getDisplayHiddenLists()
        ])
        ->add('emailHiddenLists', CheckboxType::class, [
            'label' => 'emailHiddenLists',
            'attr' => ['placeholder' => 'emailHiddenLists'],
            'data' => $user->getEmailHiddenLists()
        ])
        ->add('minRankToNotify', RangeType::class, [
            'attr' => [
                'min' => 1, 
                'max' => 50,
                'step' => 1,
                'style' => 'direction: rtl;height:0.6rem;width:88%',
            ],
            'data' => $user->getMinRankToNotify()
        ])
        ->add('save', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();
        $userProPreferencesForm->handleRequest($request);

        if ($userProPreferencesForm->isSubmitted() && $userProPreferencesForm->isValid()) {
            $proPreferencesData = $userProPreferencesForm->getData();
            $user->setPreferences([
                'displayHiddenLists' => $proPreferencesData['displayHiddenLists'],
                'emailHiddenLists' => $proPreferencesData['emailHiddenLists'],
                'minRankToNotify' => $proPreferencesData['minRankToNotify'],
            ]);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('profile/profile.html.twig', [
            'usernameForm' => $changeUsernameForm->createView(),
            'userPreferencesForm' => $userPreferencesForm->createView(),
            'userProPreferencesForm' => $userProPreferencesForm->createView(),
            'user' => $user,
        ]);
    }
}

?>