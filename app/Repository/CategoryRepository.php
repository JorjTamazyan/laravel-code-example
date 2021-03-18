<?php

namespace App\Repository;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CategoryRepository
 *
 * @package App\Repository
 */
class CategoryRepository
{
    /**
     * @param string $title
     *
     * @return Category|null
     */
    public function findByTitle(string $title): ?Category
    {
        /** @var Category|null */
        $category = Category::where('title', $title)->first();

        return $category;
    }

    /**
     * @param int $id
     *
     * @return Category
     */
    public function getById(int $id): Category
    {
        /** @var Category */
        $category = Category::where('id', $id)->firstOrFail();

        return $category;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(): Collection
    {
        $categories = Category::orderBy('created_at', 'ASC')->get();

        return $categories;
    }

    /**
     * @return array
     */
    public function getCategoryProductsCount(): array
    {
        $categoryTableName = Category::TABLE_NAME;
        $productTableName = Product::TABLE_NAME;

        /** @var \Illuminate\Support\Collection $categoriesProductsCount */
        $categoriesProductsCount = DB::table($categoryTableName)
            ->leftJoin($productTableName, "$categoryTableName.id", "=", "$productTableName.category_id")
            ->select(
                DB::raw("COUNT($productTableName.id) as products_count"),
                "$productTableName.category_id as id"
            )
            ->groupBy("$productTableName.category_id")
            ->get();

        $categoriesProductsCount = \GuzzleHttp\json_decode($categoriesProductsCount->toJson(), true);

        return $categoriesProductsCount;
    }

    /**
     * @param string $slug
     *
     * @return Category|null
     */
    public function findBySlug(string $slug): ?Category
    {
        /** @var Category|null $category */
        $category = Category::where('slug', $slug)->first();

        return $category;
    }

    /**
     * @param string $slug
     *
     * @return Category
     */
    public function getBySlug(string $slug): Category
    {
        /** @var Category $category */
        $category = Category::where('slug', $slug)->firstOrFail();

        return $category;
    }
}
