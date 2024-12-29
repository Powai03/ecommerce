<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\ContenuPanier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends AbstractController
{
    // Affiche le profil de l'utilisateur et l'historique des commandes
    #[Route('/mon-compte', name: 'app_user_profile')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Récupérer l'historique des paniers/commandes de l'utilisateur
        $paniers = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user], ['dateAchat' => 'DESC']);
        
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'paniers' => $paniers,
            'now' => new \DateTime(),
        ]);
    }

    // Affiche les détails d'une commande (panier)
    #[Route('/commande/{id}', name: 'app_order_details')]
    public function orderDetails(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Récupérer le panier/commande par son ID
        $panier = $em->getRepository(Panier::class)->find($id);
        
        // Vérifier si le panier existe
        if (!$panier) {
            throw $this->createNotFoundException('La commande n\'existe pas');
        }

        // Récupérer le contenu du panier
        $contenuPaniers = $em->getRepository(ContenuPanier::class)->findBy(['panier' => $panier]);

        return $this->render('user/order_details.html.twig', [
            'panier' => $panier,
            'contenuPaniers' => $contenuPaniers,
        ]);
    }
}
