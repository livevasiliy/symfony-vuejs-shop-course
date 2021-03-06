<?php

namespace App\Services\Manager;

use App\Entity\ProductImage;
use App\Services\File\ImageResizer;
use App\Services\Filesystem\FilesystemWorker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class ProductImageManager extends AbstractBaseManager
{
    private FilesystemWorker $filesystemWorker;
    private string $uploadsTempDir;
    private ImageResizer $imageResizer;

    public function __construct(EntityManagerInterface $entityManager, FilesystemWorker $filesystemWorker, ImageResizer $imageResizer, string $uploadsTempDir)
    {
        parent::__construct($entityManager);
        $this->filesystemWorker = $filesystemWorker;
        $this->uploadsTempDir = $uploadsTempDir;
        $this->imageResizer = $imageResizer;
    }

    public function saveImageForProduct(string $productImagesDirectory, string $tempImageFilename = null): ?ProductImage
    {
        if (!$tempImageFilename) {
            return null;
        }

        $this->filesystemWorker->createFolderIfNotExist($productImagesDirectory);
        $filenameId = uniqid();

        $imageSmallParams = [
            'width' => 60,
            'height' => null,
            'newFolder' => $productImagesDirectory,
            'newFilename' => sprintf('%s_%s.jpg', $filenameId, 'small')
        ];

        $imageSmall = $this->imageResizer->resizeImageAndSave($this->uploadsTempDir, $tempImageFilename, $imageSmallParams);

        $imageMiddleParams = [
            'width' => 430,
            'height' => null,
            'newFolder' => $productImagesDirectory,
            'newFilename' => sprintf('%s_%s.jpg', $filenameId, 'middle')
        ];
        $imageMiddle = $this->imageResizer->resizeImageAndSave($this->uploadsTempDir, $tempImageFilename, $imageMiddleParams);;

        $imageBigParams = [
            'width' => 800,
            'height' => null,
            'newFolder' => $productImagesDirectory,
            'newFilename' => sprintf('%s_%s.jpg', $filenameId, 'big')
        ];
        $imageBig = $this->imageResizer->resizeImageAndSave($this->uploadsTempDir, $tempImageFilename, $imageBigParams);;

        $productImage = new ProductImage();
        $productImage->setFilenameSmall($imageSmall);
        $productImage->setFilenameMiddle($imageMiddle);
        $productImage->setFilenameBig($imageBig);

        return $productImage;
    }

    public function removeImageFromProduct(ProductImage $productImage, string $productImagesDirectory): void
    {
        $smallFilePath = $productImagesDirectory . '/' . $productImage->getFilenameSmall();
        $this->filesystemWorker->remove($smallFilePath);

        $middleFilePath = $productImagesDirectory . '/' . $productImage->getFilenameMiddle();
        $this->filesystemWorker->remove($middleFilePath);

        $bigFilePath = $productImagesDirectory . '/' . $productImage->getFilenameBig();
        $this->filesystemWorker->remove($bigFilePath);

        $product = $productImage->getProduct();
        $product->removeProductImage($productImage);

        $this->entityManager->flush();
    }

    public function getRepository(): ObjectRepository
    {
        return $this->entityManager->getRepository(ProductImage::class);
    }
}