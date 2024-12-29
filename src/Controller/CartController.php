<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Product;
use App\Entity\ContenuPanier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Récupérer le panier actif pour l'utilisateur
        $panier = $em->getRepository(Panier::class)->findOneBy(['user' => $user, 'etat' => false]);
        $contenus = $panier ? $panier->getContenuPaniers() : [];

        return $this->render('cart/index.html.twig', [
            'contenus' => $contenus,
            'panier' => $panier,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_add_to_cart', methods: ['POST'])]
    public function addToCart(Request $request, EntityManagerInterface $em, Product $product): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        $csrfToken = $request->request->get('csrf');
        if (!$this->isCsrfTokenValid('add_to_cart' . $product->getId(), $csrfToken)) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        $quantity = (int) $request->request->get('quantity', 1);
        if ($quantity <= 0) {
            $this->addFlash('error', 'Quantité invalide');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        
        $panier = $em->getRepository(Panier::class)->findOneBy(['user' => $user, 'etat' => false]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $panier->setDateAchat(new \DateTime());
            $panier->setEtat(false);
            $em->persist($panier);
        }

        $contenuPanier = $em->getRepository(ContenuPanier::class)->findOneBy(['panier' => $panier, 'product' => $product]);
        if ($contenuPanier) {
            $contenuPanier->setQuantite($contenuPanier->getQuantite() + $quantity);
        } else {
            $contenuPanier = new ContenuPanier();
            $contenuPanier->setPanier($panier);
            $contenuPanier->setProduct($product);
            $contenuPanier->setQuantite($quantity);
            $contenuPanier->setDateAjout(new \DateTime());
            $em->persist($contenuPanier);
        }

        $em->flush();

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function removeFromCart(Request $request, EntityManagerInterface $em, ContenuPanier $contenuPanier): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Vérification du token CSRF
        $csrfToken = $request->request->get('csrf');
        if (!$this->isCsrfTokenValid('remove_from_cart' . $contenuPanier->getId(), $csrfToken)) {
            $this->addFlash('error', 'Invalid CSRF token');
            return $this->redirectToRoute('app_cart');
        }

        // Suppression du contenu du panier
        $em->remove($contenuPanier);
        $em->flush();

        $this->addFlash('success', 'Produit retiré du panier');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        $panier = $em->getRepository(Panier::class)->findOneBy(['user' => $user, 'etat' => false]);

        if (!$panier) {
            $this->addFlash('error', 'Votre panier est vide');
            return $this->redirectToRoute('app_cart');
        }

        $panier->setEtat(true);
        $em->flush();

        $this->addFlash('success', 'Commande validée avec succès');
        return $this->redirectToRoute('app_cart');
    }
}
