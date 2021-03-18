<?php

namespace App\Services;

use App\Models\Product;
use App\Repository\OrderRepository;
use Illuminate\Http\UploadedFile;

/**
 * Class ProductService
 *
 * @package App\Services
 */
class ProductService
{
    /** @var string */
    private static $productImageDirName = 'product_images/';

    /** @var OrderRepository */
    private $orderRepository;

    /**'
     * ProductService constructor.
     *
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @note this method uploads files and returns their relative paths
     *
     * @param UploadedFile[] $productImages
     *
     * @return array
     */
    public function uploadProductImages(array $productImages): array
    {
        $productImageNames = [];
        $productImageDirPath = $this->getProductImageDirPath();

        foreach ($productImages as $productImage) {
            $productImageOriginalName = $productImage->getClientOriginalName();
            $productImageNewName = $this->generateProductImageName($productImageOriginalName);

            $productImage->move($productImageDirPath, $productImageNewName);
            $productImageNames[] = $productImageNewName;
        }

        return $productImageNames;
    }

    /**
     * @param Product $product
     */
    public function deleteProductImages(Product $product): void
    {
        $productImageNames = \GuzzleHttp\json_decode($product->getImages(), true);
        foreach ($productImageNames as $productImageName) {
            $productImagePath = $this->getProductImageDirPath() . $productImageName;

            if (false === is_file($productImagePath)) {
                continue;
            }

            unlink($productImagePath);
        }
    }

    /**
     * @param string $imageName
     */
    public function deleteProductImage(string $imageName): void
    {
        $imagePath = $this->getProductImageDirPath() . $imageName;

        unlink($imagePath);
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function canDeleteProduct(Product $product): bool
    {
        if ($this->isProductOrdered($product)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $productImageOriginalName
     *
     * @return string
     */
    private function generateProductImageName(string $productImageOriginalName): string
    {
        $productImageName = snake_case(pathinfo($productImageOriginalName, PATHINFO_FILENAME)) . time() . '.' . pathinfo($productImageOriginalName, PATHINFO_EXTENSION);

        return $productImageName;
    }

    /**
     * @return string
     */
    private function getProductImageDirPath(): string
    {
        return storage_path('app/public/') . self::$productImageDirName;
    }

    /**
     * @return string
     */
    public function getProductImageDirRelPath(): string
    {
        return 'storage/' . self::$productImageDirName;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    private function isProductOrdered(Product $product): bool
    {
        $order = $this->orderRepository->findByProductId($product->getId());

        if (is_null($order)) {
            return false;
        }

        return true;
    }
}
