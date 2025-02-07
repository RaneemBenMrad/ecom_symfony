<?php
// src/Controller/Admin/ProductsController.php

namespace App\Controller\Admin;

use App\Entity\Products;
use App\Entity\Categories;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
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
        // Récupérer tous les produits via le repository
        $produits = $productsRepository->findAll();

        // Rendre la vue avec la liste des produits
        return $this->render('admin/products/index.html.twig', [
            'produits' => $produits
        ]);
    }

    // Ajouter un produit
    #[Route('/ajout', name: 'add')]
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
#[Route('/edition/{id}', name: 'edit')]
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

// Supprimer un produit
#[Route('/suppression/{id}', name: 'delete', methods: ['POST'])]
public function delete($id, EntityManagerInterface $entityManager): Response
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

    $this->addFlash('success', 'Produit supprimé avec succès.');
    return $this->redirectToRoute('admin_products_index');
}
#[Route('/product/{id}/add-image', name: 'add_image_to_product')]
public function addImageToProduct(Products $product, Request $request, EntityManagerInterface $em): Response
{
    // Création d'une nouvelle image (si ce n'est pas un upload direct)
    $image = new Images();
    $image->setName('image_name.jpg'); // Exemple: tu peux récupérer le nom de l'image via un formulaire d'upload

    // Associer l'image au produit
    $product->addImage($image);

    // Sauvegarder l'image dans la base de données
    $em->persist($image);
    $em->flush();

    return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
}
}
