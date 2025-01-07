<?php

// src/Controller/Admin/UsersController.php
namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\UsersFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/users', name: 'admin_users_')]
class UsersController extends AbstractController
{
    // Liste des utilisateurs
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findAll();
        
        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

  

    // Ajouter un utilisateur
    #[Route('/ajout', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new Users();

        // Créer le formulaire pour ajouter un utilisateur
        $form = $this->createForm(UsersFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persister l'entité de l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Utilisateur ajouté avec succès.');

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('admin/users/add.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    // Modifier un utilisateur
    #[Route('/modifier/{id}', name: 'edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, UsersRepository $usersRepository): Response
    {
        $user = $usersRepository->find($id);
    
        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('admin_users_index');
        }
    
        $form = $this->createForm(UsersFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist les modifications
            $entityManager->flush();
    
            // Message de succès
            $this->addFlash('success', 'Utilisateur modifié avec succès.');
    
            return $this->redirectToRoute('admin_users_index');
        }
    
        return $this->render('admin/users/edit.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

     // Supprimer un utilisateur
     #[Route('/suppression/{id}', name: 'admin_users_delete', methods: ['POST'])]
     public function delete(int $id, EntityManagerInterface $entityManager): Response
     {
         // Trouver le produit par ID
         $product = $entityManager->getRepository(Products::class)->find($id);
     
         if (!$product) {
             $this->addFlash('error', 'Produit non trouvé.');
             return $this->redirectToRoute('admin_products_index');
         }
     
         // Supprimer le produit
         $entityManager->remove($product);
         $entityManager->flush();
     
         // Message de succès
         $this->addFlash('success', 'Produit supprimé avec succès.');
     
         return $this->redirectToRoute('admin_products_index');
     }
    }