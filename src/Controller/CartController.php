<?php
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart', name: 'cart_')]  // Main route for Cart
class CartController extends AbstractController
{
    #[Route('/', name: 'index')]  // Index route for Cart
    public function index(SessionInterface $session, ProductsRepository $productsRepository)
    {
        $panier = $session->get('panier', []);
        
        // Initialize variables
        $data = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);

            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }

        return $this->render('cart/index.html.twig', compact('data', 'total'));
    }

    #[Route('/add/{id}', name: 'add')]  // Add to cart route
    public function add(Products $product, SessionInterface $session)
    {
        // Retrieve product ID
        $id = $product->getId();

        // Get existing cart
        $panier = $session->get('panier', []);

        // Add product to cart if it's not there yet, otherwise increment quantity
        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $session->set('panier', $panier);

        // Redirect to cart page
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/remove/{id}', name: 'remove')]  // Remove item from cart
    public function remove(Products $product, SessionInterface $session)
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        // Remove product from cart or decrease quantity
        if (!empty($panier[$id])) {
            if ($panier[$id] > 1) {
                $panier[$id]--;
            } else {
                unset($panier[$id]);
            }
        }

        $session->set('panier', $panier);

        // Redirect to cart page
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/delete/{id}', name: 'delete')]  // Delete item from cart
    public function delete(Products $product, SessionInterface $session)
    {
        $id = $product->getId();
        $panier = $session->get('panier', []);

        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }

        $session->set('panier', $panier);

        // Redirect to cart page
        return $this->redirectToRoute('cart_index');
    }

    #[Route('/empty', name: 'empty')]  // Empty the cart
    public function empty(SessionInterface $session)
    {
        $session->remove('panier');

        // Redirect to cart page
        return $this->redirectToRoute('cart_index');
    }
}
