<?php

namespace App\Controller;

use Patreon\API;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PatreonService;

class AdminController extends AbstractController
{
    private $patreonService;

    public function __construct(PatreonService $patreonService)
    {
        $this->patreonService = $patreonService;
    }

    #[Route('/admin', name: 'app_admin')]
    public function app_admin(EntityManagerInterface $entityManager): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/admin/users/{id}', name: 'app_admin_user')]
    public function app_admin_user(EntityManagerInterface $entityManager, ?string $id = null): Response
    {
        $users = [];
        $user = [];
        if ($id) {
            $user = $entityManager->getRepository(User::class)->findOneById($id);
            $accessToken = $user->getPreference('patreonAccessToken');
            
            if (!$accessToken) {
                $this->addFlash('error', 'No access token found, please link to Patreon');
                return false;
            }
            
            $patronData = $this->patreonService->fetchPatreonData($accessToken);
        } else {
            $users = $entityManager->getRepository(User::class)->findAll();
        }

        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'user' => $user,
            'patronData' => $patronData,
        ]);
    }

    
}