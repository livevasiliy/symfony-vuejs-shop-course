<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\EditProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * DefaultController constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'main_homepage')]
    public function index(): Response
    {
        $productList = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('main/default/index.html.twig', []);
    }
}
