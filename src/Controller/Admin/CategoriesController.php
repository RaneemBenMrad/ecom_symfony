<?php

// src/Controller/Admin/CategoriesController.php
namespace App\Controller\Admin;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;  // Importer la classe Request correctement
use Doctrine\ORM\EntityManagerInterface; // Importez la bonne classe EntityManagerInterface
use App\Entity\Categories;  // Ajoutez cette ligne pour importer la classe Categories
use App\Form\CategoriesFormType; // Importer la classe
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Products;  // Assurez-vous que cette ligne est présente




#[Route('/admin/categories', name: 'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();

        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    // Ajouter une catégorie
// Ajouter une catégorie
#[Route('/ajout', name: 'add')]
public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $category = new Categories();
    $category->setCategoryOrder(0); // Définir la valeur par défaut pour category_order

    // Vérifier si le nom de la catégorie est défini avant de générer le slug
    if ($category->getName()) {
        $slug = $slugger->slug($category->getName());
        $category->setSlug($slug);
    } else {
        // Si le nom est vide, on peut définir un slug par défaut
        $category->setSlug('slug-par-defaut');
    }

    // Créer le formulaire pour ajouter une catégorie
    $form = $this->createForm(CategoriesFormType::class, $category);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Persist l'entité de la catégorie
        $entityManager->persist($category);
        $entityManager->flush();

        // Message de succès
        $this->addFlash('success', 'Catégorie ajoutée avec succès');

        // Redirection vers la liste des catégories
        return $this->redirectToRoute('admin_categories_index');
    }

    // Afficher le formulaire
    return $this->render('admin/categories/add.html.twig', [
        'categoryForm' => $form->createView(),
    ]);
}


    // Modifier une catégorie
    #[Route('/edition/{id}', name: 'edit')]
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la catégorie à modifier
        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Créer le formulaire pour modifier la catégorie
        $form = $this->createForm(CategoriesFormType::class, $category);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Catégorie modifiée avec succès');

            // Redirection vers la liste des catégories
            return $this->redirectToRoute('admin_categories_index');
        }

        // Afficher le formulaire de modification
        return $this->render('admin/categories/edit.html.twig', [
            'categoryForm' => $form->createView(),
        ]);
    }

    // Supprimer une catégorie
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete($id, EntityManagerInterface $entityManager): Response
{
    // Trouver la catégorie par ID
    $category = $entityManager->getRepository(Categories::class)->find($id);

    if (!$category) {
        // Si la catégorie n'existe pas, afficher une erreur
        $this->addFlash('error', 'Catégorie non trouvée.');
        return $this->redirectToRoute('admin_categories_index');
    }

    // Supprimer tous les produits associés à cette catégorie
    $products = $entityManager->getRepository(Products::class)->findBy(['category' => $category]);

    foreach ($products as $product) {
        // Dissocier la catégorie des produits ou les supprimer
        $product->setCategory(null);  // Ou $entityManager->remove($product);
    }

    // Persist les changements
    $entityManager->flush();

    // Supprimer la catégorie
    $entityManager->remove($category);
    $entityManager->flush();

    // Message de succès
    $this->addFlash('success', 'Catégorie supprimée avec succès.');

    return $this->redirectToRoute('admin_categories_index');
}
}
