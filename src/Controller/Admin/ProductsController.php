<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/ajout', name: 'add')]
    public function add(): Response
    {
         // On vérifie si l'utilisateur peut supprimer avec le Voter
         $this->denyAccessUnlessGranted('ROLE_ADMIN');
         //On crée un "nouveau produit"
        $product = new Products();

        // On crée le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

       
            return $this->render('admin/products/add.html.twig',[
                 'productForm' => $productForm->createView()
                ]);
        
       
        }


    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product): Response
    {
        
            return $this->redirectToRoute('admin_products_index');
        }


    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {

        return $this->render('admin/products/index.html.twig');
    }

   

}