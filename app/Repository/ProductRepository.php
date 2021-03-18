<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class ProductRepository
 *
 * @package App\Repository
 */
class ProductRepository
{
    /**
     * @param int $id
     *
     * @return Product
     */
    public function getById(int $id): Product
    {
        /** @var Product $product */
        $product = Product::where('id', $id)->firstOrFail();

        return $product;
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getAll(int $page, int $limit): array
    {
        $productsJson = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.description',
                'products.category_id',
                'products.price',
                'products.images',
                'products.show_on_website',
                'categories.title as category_title'
            )
            ->orderBy('products.created_at', 'ASC')
            ->paginate($limit, ['*'], 'start', $page)
            ->toJson();

        $products = \GuzzleHttp\json_decode($productsJson, true);

        return $products;
    }

    /**
     * @param int $categoryId
     * @param bool|null $showOnWebsite
     * @param int|null $page
     * @param int|null $limit
     *
     * @return array
     */
    public function getAllByCategoryId(
        int $categoryId,
        bool $showOnWebsite = null,
        int $page = null,
        int $limit = null
    ): array {
        /** @var Builder $queryBuilder */
        $queryBuilder = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.title',
                'products.description',
                'products.category_id',
                'products.price',
                'products.images',
                'products.video_url',
                'products.show_on_website',
                'categories.title as category_title'
            )
            ->where('products.category_id', $categoryId);

        if (false === is_null($showOnWebsite)) {
            $queryBuilder->where('products.show_on_website', $showOnWebsite);
        }

        $queryBuilder->orderBy('products.created_at', 'ASC');

        if (false === is_null($page) && false === is_null($limit)) {
            /** @var LengthAwarePaginator $paginator */
            $paginator = $queryBuilder->paginate($limit, ['*'], 'start', $page);
            $productsJson = $paginator->toJson();
        } else {
            $productsJson = $queryBuilder->get()->toJson();
        }

        $products = \GuzzleHttp\json_decode($productsJson, true);

        return $products;
    }


    /**
     * @param int[]    $ids
     * @param string[] $productFields
     *
     * @return array
     */
    public function getProductFieldsByIds(array $ids, array $productFields): array
    {
        $productsJson = DB::table('products')->select($productFields)->whereIn('products.id', $ids)->get()->toJson();

        $products = \GuzzleHttp\json_decode($productsJson, true);

        return $products;
    }
}
