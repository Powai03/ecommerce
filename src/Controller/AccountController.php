<?php


namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\UserType;
use App\Form\UserProfileType;

class AccountController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_account')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Formulaire de modification du profil
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès');
            return $this->redirectToRoute('app_account');
        }

        // Récupérer l'historique des commandes
        $commandes = $em->getRepository(Panier::class)->findBy(['user' => $user, 'etat' => true]);

        // Filtrer les commandes qui contiennent des produits supprimés
        $validCommandes = [];
        foreach ($commandes as $commande) {
            $hasInvalidProduct = false;
            $montant = 0;

            // Vérifier si la commande contient des produits supprimés
            foreach ($commande->getContenuPaniers() as $contenu) {
                $product = $contenu->getProduct();
                if (!$product || $product->getIsDeleted()) {
                    $hasInvalidProduct = true;
                    break;
                }

                // Calculer le montant total pour ce produit
                $montant += $contenu->getQuantite() * $product->getPrice();
            }

            if (!$hasInvalidProduct) {
                // Ajouter la commande avec son montant calculé
                $validCommandes[] = [
                    'commande' => $commande,
                    'montant' => $montant
                ];
            }
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'commandes' => $validCommandes,
        ]);
    }

    #[Route('/mon-compte/commande/{id}', name: 'app_account_order_show')]
    public function showOrder(EntityManagerInterface $em, Panier $panier): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Vérifier que la commande appartient à l'utilisateur
        
        if ($panier->getUser() !== $user) {
            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/detail.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/mon-compte/modifier', name: 'app_account_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
{
    // Récupérer l'utilisateur actuellement connecté
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Créer et gérer le formulaire de mise à jour du profil
    $form = $this->createForm(UserProfileType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Sauvegarder les modifications sans modifier le mot de passe
        $em->flush();

        // Ajouter un message de succès et rediriger vers la page du compte
        $this->addFlash('success', 'Profil mis à jour avec succès');
        return $this->redirectToRoute('app_account');
    }

    return $this->render('account/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
