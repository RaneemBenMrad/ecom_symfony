<?php

// src/Controller/CategoriesController.php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoriesController extends AbstractController
{
    private $entityManager;

    // Injection du service EntityManagerInterface
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/categories/{slug}', name: 'categories_list')]
    public function list(string $slug, ProductsRepository $productsRepository): Response
    {
        // Récupérer la catégorie par son slug
        $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['slug' => $slug]);

        if (!$category) {
            throw $this->createNotFoundException('La catégorie n\'existe pas.');
        }

        // Récupérer les produits associés à cette catégorie
        $products = $productsRepository->findBy(['category' => $category]);

        // Passer la catégorie et les produits à la vue
        return $this->render('categories/list.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
