<?php
 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Utilisation de cette interface dans Symfony 6
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;

class AdminController extends AbstractController
{
    public function rehashAdminPassword(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        // Récupérer l'utilisateur avec son e-mail
        $user = $entityManager->getRepository(Users::class)->findOneByEmail('admin@demo.fr');

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Hacher le nouveau mot de passe
        $newPassword = $passwordHasher->hashPassword($user, '123456'); // Nouveau mot de passe en clair

        // Mettre à jour le mot de passe dans la base de données
        $user->setPassword($newPassword);
        $entityManager->flush();

        return new Response('Mot de passe mis à jour avec succès');
    }
}
