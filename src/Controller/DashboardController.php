<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        // Vérifier si l'utilisateur a le rôle SUPER_ADMIN
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        // Récupérer les paniers non achetés (état = false)
        $nonAchetes = $em->getRepository(Panier::class)->findBy(['etat' => false]);

        $paniers = [];
        foreach ($nonAchetes as $panier) {
            $paniers[] = [
                'numero' => $panier->getId(),
                'utilisateur' => $panier->getUser()->getEmail(), // Supposant que chaque panier a un utilisateur associé
                'contenu' => $panier->getContenuPaniers(), // Supposant que cette méthode existe pour récupérer les produits
            ];
        }

        // Récupérer les utilisateurs triés du plus récent au plus ancien
        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/index.html.twig', [
            'paniers' => $paniers,
            'users' => $users,
        ]);
    }
}
