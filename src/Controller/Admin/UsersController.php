<?php

namespace App\Controller\Admin;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/utilisateurs', name: 'admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        // Récupérer la liste des utilisateurs depuis la base de données
        $users = $usersRepository->findAll();

        // Rendre la vue et passer la variable $users à la vue Twig
        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }
}
