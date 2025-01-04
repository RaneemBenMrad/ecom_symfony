<?php
// src/Controller/Admin/ProductsController.php

namespace App\Controller\Admin;

use App\Entity\Products;
use App\Entity\Categories;
use App\Form\ProductsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/admin/produits', name: 'admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig', compact('produits'));
    }

    // Ajouter un produit
    #[Route('/admin/produits/ajout', name: 'admin_products_add')]
    public function add(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Products();
        $categories = $entityManager->getRepository(Categories::class)->findAll();

        $form = $this->createForm(ProductsFormType::class, $product, [
            'categories' => $categories,
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$product->getCategory()) {
                $defaultCategory = $entityManager->getRepository(Categories::class)->find(1);
                $product->setCategory($defaultCategory);
            }

            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');
        }

        return $this->render('admin/products/add.html.twig', [
            'productForm' => $form->createView(),
        ]);
    }

    // Modifier un produit
  // Modifier un produit
#[Route('/admin/produits/edition/{id}', name: 'admin_products_edit')]
public function edit($id, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $product = $entityManager->getRepository(Products::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Produit non trouvé');
    }

    // Récupérer toutes les catégories
    $categories = $entityManager->getRepository(Categories::class)->findAll();

    // Créer le formulaire
    $form = $this->createForm(ProductsFormType::class, $product, [
        'categories' => $categories,
    ]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $slug = $slugger->slug($product->getName());
        $product->setSlug($slug);

        $entityManager->flush();

        $this->addFlash('success', 'Produit modifié avec succès');
    }

    // Rendre la vue avec le formulaire
    return $this->render('admin/products/edit.html.twig', [
        'form' => $form->createView(), // Assurez-vous que la variable 'form' est bien passée
    ]);
}

#[Route('/admin/produits/suppression/{id}', name: 'admin_products_delete')]


public function delete($id, EntityManagerInterface $entityManager): Response
{
    // Récupération du produit par son identifiant
    $product = $entityManager->getRepository(Products::class)->find($id);

    // Vérification si le produit existe
    if (!$product) {
        throw $this->createNotFoundException('Produit non trouvé');
    }

    // Suppression du produit
    $entityManager->remove($product);
    $entityManager->flush();

    // Message de succès
    $this->addFlash('success', 'Produit supprimé avec succès');

    // Redirection vers la liste des produits
    return $this->redirectToRoute('admin_products_list');
}


}


