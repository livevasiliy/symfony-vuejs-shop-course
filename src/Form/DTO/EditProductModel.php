<?php

namespace App\Form\DTO;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class EditProductModel
{
    public ?int $id = null;

    /**
     * @Assert\NotBlank(message="Please enter a title")
     *
     * @var string|null
     */
    public ?string $title = null;


    public ?string $description = null;

    /**
     * @Assert\File(
     *     maxSize="5024k",
     *     mimeTypes={"image/jpeg", "image/png"},
     *     mimeTypesMessage="Please upload a valid image"
     * )
     *
     * @var UploadedFile|null
     */
    public ?UploadedFile $newImage = null;

    /**
     * @Assert\NotBlank(message="Please indicate the quantity")
     *
     * @var int|null
     */
    public ?int $quantity = null;

    /**
     * @Assert\NotBlank(message="Please enter a price")
     * @Assert\GreaterThanOrEqual(value="0")
     *
     * @var string|null
     */
    public ?string $price = null;

    public bool $isDeleted = false;

    public bool $isPublished = false;

    public static function makeFromProduct(?Product $product): self
    {
        $model = new self();

        if (!$product) {
            return $model;
        }

        $model->id = $product->getId();
        $model->title = $product->getTitle();
        $model->description = $product->getDescription();
        $model->quantity = $product->getQuantity();
        $model->price = $product->getPrice();
        $model->isDeleted = $product->getIsDeleted();
        $model->isPublished = $product->getIsPublished();

        return $model;
    }
}