<?php

namespace App\Services\Manager;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class ProductManager
{
    private EntityManagerInterface $entityManager;

    private string $productImagesDirectory;
    private ProductImageManager $productImageManager;


    public function __construct(EntityManagerInterface $entityManager, string $productImagesDirectory, ProductImageManager $productImageManager)
    {
        $this->entityManager = $entityManager;
        $this->productImagesDirectory = $productImagesDirectory;
        $this->productImageManager = $productImageManager;
    }

    public function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(Product::class);
    }

    public function remove(Product $product): void
    {
        $product->setIsDeleted(true);
        $this->save($product);
    }

    public function getProductImagesDirectory(Product $product): string
    {
        return sprintf('%s/%s', $this->productImagesDirectory, $product->getId());
    }

    public function updateProductImages(Product $product, string $tempImageFilename = null): Product
    {
        if (!$tempImageFilename) {
            return $product;
        }

        $productImagesDirectory = $this->getProductImagesDirectory($product);

        $productImage = $this->productImageManager->saveImageForProduct($productImagesDirectory, $tempImageFilename);
        $productImage->setProduct($product);
        $product->addProductImage($productImage);

        return $product;
    }

    public function save(Product $product)
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }
}