<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
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
        } else {
            $users = $entityManager->getRepository(User::class)->findAll();
        }
        return $this->render('admin/users.html.twig', [
            'users' => $users,
            'user' => $user
        ]);
    }
}