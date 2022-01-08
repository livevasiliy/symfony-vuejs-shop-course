<?php

namespace App\Form\Handler;

use App\Entity\Product;
use App\Form\DTO\EditProductModel;
use App\Services\File\FileSaver;
use App\Services\Manager\ProductManager;
use Symfony\Component\Form\FormInterface;


class ProductFormHandler
{
    private FileSaver $fileSaver;
    private ProductManager $productManager;

    public function __construct(ProductManager $productManager, FileSaver $fileSaver)
    {
        $this->fileSaver = $fileSaver;
        $this->productManager = $productManager;
    }

    public function processEditForm(EditProductModel $editProductModel, FormInterface $form): Product
    {
        $product = new Product();

        if ($editProductModel->id) {
            $product = $this->productManager->find($editProductModel->id);
        }

        $product->setTitle($editProductModel->title);
        $product->setDescription($editProductModel->description);
        $product->setPrice($editProductModel->price);
        $product->setQuantity($editProductModel->quantity);
        $product->setIsDeleted($editProductModel->isDeleted);
        $product->setIsPublished($editProductModel->isPublished);

        $this->productManager->save($product);

        $newImageFile = $form->get('newImage')->getData();

        $tempImageFilename = $newImageFile
            ? $this->fileSaver->saveUploadedFileIntoTemp($newImageFile)
            : null;

        $this->productManager->updateProductImages($product, $tempImageFilename);

        $this->productManager->save($product);

        return $product;
    }
}