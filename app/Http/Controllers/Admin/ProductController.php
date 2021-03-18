<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\GetProductListRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;
use App\Repository\ProductRepository;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Admin
 */
class ProductController extends BaseController
{
    private const PRODUCTS_PER_PAGE = 15;

    /** @var ProductRepository */
    private $productRepository;

    /** @var ProductService */
    private $productService;

    /**
     * ProductController constructor.
     *
     * @param ProductRepository $productRepository
     * @param ProductService $productService
     */
    public function __construct(ProductRepository $productRepository, ProductService $productService)
    {
        $this->middleware(['auth', 'check_user_role:' . User::ROLE_ADMIN]);

        $this->productRepository = $productRepository;
        $this->productService = $productService;
    }

    /**
     * @param CreateProductRequest $request
     *
     * @return JsonResponse
     */
    public function createOneAction(CreateProductRequest $request): JsonResponse
    {
        $title = $request->get('title');
        $description = $request->get('description');
        $price = $request->get('price');
        $categoryId = $request->get('category_id');
        $images = $request->file('images');
        $videoUrl = $request->get('video_url');
        $showOnWebsite = $request->get('show_on_website') === 'true' ? true : false;

        $imageNames = [];
        if (false === is_null($images)) {
            $imageNames = $this->productService->uploadProductImages($images);
        }

        $product = new Product();
        $product
            ->setTitle($title)
            ->setDescription($description)
            ->setPrice($price)
            ->setCategoryId($categoryId)
            ->setShowOnWebsite($showOnWebsite);

        if (false === is_null($images)) {
            $product->setImages(\GuzzleHttp\json_encode($imageNames));
        }

        if (false === is_null($videoUrl)) {
            $product->setVideoUrl($videoUrl);
        }

        $product->save();

        return response()->json([], 201);
    }

    /**
     * @note for updating product images - existing image names are also sent from
     * frontend, because there might be deleted images, which must be removed.
     *
     * @param int $id
     * @param UpdateProductRequest $request
     *
     * @return JsonResponse
     */
    public function updateOneAction(int $id, UpdateProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->getById($id);

        $title = $request->get('title', null);
        $description = $request->get('description', null);
        $price = $request->get('price', null);
        $categoryId = $request->get('category_id', null);
        $existingImageUrlsJson = $request->get('existing_images', null);
        $newImages = $request->file('images', null);
        $videoUrl = $request->get('video_url', null);
        $showOnWebsite = $request->get('show_on_website');

        if (false === is_null($title) && $product->getTitle() !== $title) {
            $product->setTitle($title);
        }

        if (false === is_null($description) && $product->getDescription() !== $description) {
            $product->setDescription($description);
        }

        if (false === is_null($price) && $product->getPrice() !== $price) {
            $product->setPrice($price);
        }

        if (false === is_null($categoryId) && $product->getCategoryId() !== $categoryId) {
            $product->setCategoryId($categoryId);
        }

        if (false === is_null($videoUrl)) {
            $product->setVideoUrl($videoUrl);
        }

        $existingImageUrls = \GuzzleHttp\json_decode($existingImageUrlsJson, true);
        $existingImageNames = [];
        foreach ($existingImageUrls as $existingImageUrl) {
            $existingImageNames[] = basename($existingImageUrl);
        }

        if (false === is_null($newImages) && count($existingImageNames) > 0) {
            if ((count($existingImageNames) + count($newImages)) > Product::IMAGES_MAX_COUNT) {
                return response()->json([
                    'message' => sprintf(
                        'There can not be more than %d images for product',
                        Product::IMAGES_MAX_COUNT
                    ),
                ], 400);
            }

            $newImageNames = $this->productService->uploadProductImages($newImages);
            $currentImages = array_merge($existingImageNames, $newImageNames);
            $product->setImages(\GuzzleHttp\json_encode($currentImages));
        } elseif (count($existingImageNames) > 0) {
            /**
             * @note There may be not added images, but some existing images maybe
             * are removed from frontend
             */
            $productImageNames = \GuzzleHttp\json_decode($product->getImages(), true);
            foreach ($productImageNames as $productImageName) {
                if (false === in_array($productImageName, $existingImageNames)) {
                    $this->productService->deleteProductImage($productImageName);
                }
            }

            $product->setImages(\GuzzleHttp\json_encode($existingImageNames));
        }

        if (false === is_null($showOnWebsite)) {
            if ($showOnWebsite === 'true') {
                $product->setShowOnWebsite(true);
            } else {
                $product->setShowOnWebsite(false);
            }
        }

        $product->save();

        return response()->json([], 204);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteOneAction(int $id): JsonResponse
    {
        $product = $this->productRepository->getById($id);
        if (false === $this->productService->canDeleteProduct($product)) {
            return response()->json(['message' => 'Product can not be deleted'], 400);
        }

        if (false === is_null($product->getImages())) {
            $this->productService->deleteProductImages($product);
        }

        $product->delete();

        return response()->json([], 204);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     */
    public function getOneAction(int $id): JsonResponse
    {
        $product = $this->productRepository->getById($id);
        $productData = $this->decorateProductData($product);

        return response()->json($productData, 200);
    }

    /**
     * @param GetProductListRequest $request
     *
     * @return JsonResponse
     */
    public function getListAction(GetProductListRequest $request): JsonResponse
    {
        $offset = $request->get('start');
        $perPage = $request->get('length', self::PRODUCTS_PER_PAGE);
        $categoryId = $request->get('category_id', null);

        $page = $offset / $perPage + 1;

        if (is_null($categoryId)) {
            $products = $this->productRepository->getAll($page, $perPage);
        } else {
            $products = $this->productRepository->getAllByCategoryId($categoryId, null, $page, $perPage);
        }

        $products = $this->decorateProducts($products);

        return response()->json($products, 200);
    }

    /**
     * @param int $productId
     *
     * @return JsonResponse
     */
    public function getCanDeleteOneAction(int $productId): JsonResponse
    {
        $product = $this->productRepository->getById($productId);

        $canDeleteProduct = $this->productService->canDeleteProduct($product);

        return response()->json([
            'can_delete_product' => $canDeleteProduct,
        ], 200);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function decorateProductData(Product $product): array
    {
        $result = [
            'id' => $product->getId(),
            'title' => e($product->getTitle()),
            'description' => e($product->getDescription()),
            'price' => $product->getPrice(),
            'category' => e($product->getCategory()->getTitle()),
            'category_id' => $product->getCategoryId(),
            'show_on_website' => $product->isShowOnWebsite(),
        ];

        if (false === is_null($product->getImages())) {
            $productImageDirRelPath = $this->productService->getProductImageDirRelPath();

            $productImageNames = \GuzzleHttp\json_decode($product->getImages(), true);
            foreach ($productImageNames as $productImageName) {
                $result['images'][] = asset($productImageDirRelPath . $productImageName);
            }
        }

        if (false === empty($product->getVideoUrl())) {
            $result['video_url'] = $product->getVideoUrl();
        }

        return $result;
    }

    /**
     * @param array $products
     *
     * @return array
     */
    private function decorateProducts(array $products)
    {
        $products['recordsTotal'] = $products['recordsFiltered'] = $products['total'];

        unset($products['from']);
        unset($products['to']);
        unset($products['path']);
        unset($products['total']);

        $productImageDirRelPath = $this->productService->getProductImageDirRelPath();
        foreach ($products['data'] as $productIndex => $productData) {
            $products['data'][$productIndex]['DT_RowAttr'] = [
                'data-id' => $productData['id'],
            ];

            $products['data'][$productIndex]['title'] = e($productData['title']);
            $products['data'][$productIndex]['description'] = e($productData['description']);
            $products['data'][$productIndex]['short_description'] = e(str_limit($productData['description'], 30));
            $products['data'][$productIndex]['category_title'] = e($productData['category_title']);

            if (false === is_null($productData['images'])) {
                $productImageNames = \GuzzleHttp\json_decode($productData['images'], true);
                $productImageUrls = [];
                foreach ($productImageNames as $productImageName) {
                    $productImageUrls[] = Storage::url($productImageDirRelPath . $productImageName);
                }

                $products['data'][$productIndex]['images'] = $productImageUrls;
            }

            if ($productData['show_on_website']) {
                $products['data'][$productIndex]['show_on_website'] = '<span class="text-success">Yes</span>';
            } else {
                $products['data'][$productIndex]['show_on_website'] = '<span class="text-danger">No</span>';
            }

            $products['data'][$productIndex]['view_icon_html'] =
                '<span class="icon-holder view-product-icon edit-icon">
                        <i class="c-blue-500 ti-eye" title="View Details"></i>
                    </span>';

            $products['data'][$productIndex]['edit_icon_html'] =
                '<span class="icon-holder edit-product-icon edit-icon">
                        <i class="c-blue-500 ti-pencil" title="Edit"></i>
                    </span>';

            $products['data'][$productIndex]['delete_icon_html'] =
                '<span class="icon-holder delete-product-icon edit-icon">
                        <i class="c-blue-500 ti-trash" title="Delete"></i>
                    </span>';
        }

        return $products;
    }
}
