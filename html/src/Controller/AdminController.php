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

    #[Route('/admin/{id}', name: 'app_admin_user')]
    public function app_admin_user(EntityManagerInterface $entityManager, ?string $id = null): Response
    {
        $users = [];
        $user = [];
        $patronData = [];
        $statsData = [];

        if ($id) {
            $user = $entityManager->getRepository(User::class)->findOneById($id);
            $patronData = $user->getPatreonData();
            $accessToken = isset($patronData['patreonAccessToken']) ?? null;

            // $patronData = $this->patreonService->fetchPatreonData($accessToken);
        } else {
            $users = $entityManager->getRepository(User::class)->findAll();
        }
        

        return $this->render('admin/admin.html.twig', [
            'users' => $users,
            'user' => $user,
            'patronData' => $patronData,
            'stats' => $statsData,
        ]);
    }

    
}