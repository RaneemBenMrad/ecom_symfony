<?php

// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new Users();
        // Créer le formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe de l'utilisateur
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // Persister l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Génération du JWT pour l'utilisateur
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            // Générer un token JWT
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Envoi d'un e-mail de vérification à l'utilisateur
            $mail->send(
                'no-reply@monsite.net',  // Adresse de l'expéditeur
                $user->getEmail(),  // Email du destinataire
                'Activation de votre compte sur le site e-commerce',  // Sujet de l'email
                'register',  // Le template Twig utilisé pour l'email
                compact('user', 'token')  // Variables à passer au template
            );

            // Authentifier l'utilisateur automatiquement après inscription
            $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );

            // Rediriger l'utilisateur vers la page principale après l'inscription et l'authentification
            return $this->redirectToRoute('app_main');  // Remplacer 'app_main' par la route de la page principale
        }

        // Rendre le formulaire d'inscription dans la vue
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    // Méthode pour vérifier l'activation du compte via le token
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        // Vérification du token (validité, expiration, etc.)
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // Récupérer le payload et l'utilisateur
            $payload = $jwt->getPayload($token);
            $user = $usersRepository->find($payload['user_id']);

            // Activer l'utilisateur si non déjà activé
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('profile_index');  // Rediriger vers la page de profil après activation
            }
        }

        // En cas de problème avec le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');  // Rediriger vers la page de connexion en cas d'erreur
    }

    // Méthode pour renvoyer l'email de vérification
    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

        // Génération d'un nouveau token pour la vérification
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // Envoi de l'email de vérification
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte sur le site e-commerce',
            'register',
            compact('user', 'token')
        );

        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('profile_index');  // Rediriger vers la page de profil
    }
}
