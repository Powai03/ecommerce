<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }

                $product->setPhoto($newFilename);
            }
            $product->setIsDeleted(false); // Assurez-vous que le produit est actif par défaut
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Product added');

            return $this->redirectToRoute('app_product');
        }

        $products = $em->getRepository(Product::class)->findBy(['isDeleted' => false]); // Filtrer les produits actifs
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'ajout_produit' => $form,
        ]);
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        // Vérifiez le token CSRF pour la sécurité
        $csrfToken = $request->request->get('csrf');
        if (!$this->isCsrfTokenValid('delete' . $product->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('app_product');
        }

        // Passer le produit à supprimé logiquement
        $product->setIsDeleted(true);
        $em->flush();

        $this->addFlash('success', 'Produit marqué comme supprimé');
        return $this->redirectToRoute('app_product');
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function edit(Request $request, EntityManagerInterface $em, Product $product): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        if ($product === null) {
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('app_product');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading file');
                }

                $product->setPhoto($newFilename);
            }
            $em->flush();

            $this->addFlash('success', 'Product edited');

            return $this->redirectToRoute('app_product');
        }
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'edit_produit' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product = null): Response
    {
        $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
        if ($product === null || $product->getIsDeleted()) { // Vérifier si le produit est supprimé
            $this->addFlash('error', 'Product not found');
            return $this->redirectToRoute('app_product');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
