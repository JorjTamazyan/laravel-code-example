<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CreateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use App\Repository\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

/**
 * Class CategoryController
 *
 * @package App\Http\Controllers\Admin
 */
class CategoryController extends BaseController
{
    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var CategoryService */
    private $categoryService;

    /**
     * CategoryController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryRepository $categoryRepository, CategoryService $categoryService)
    {
        $this->middleware(['auth', 'check_user_role:' . User::ROLE_ADMIN]);

        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
    }

    /**
     * @param CreateCategoryRequest $request
     *
     * @return JsonResponse
     */
    public function createOneAction(CreateCategoryRequest $request): JsonResponse
    {
        $title = $request->get('title');
        $slug = $request->get('slug');
        $showInBottom = $request->get('show_in_bottom') === 'true' ? true : false;
        $image = $request->file('image');

        $categoryWithSameTitle = $this->categoryRepository->findByTitle($title);
        if (false === is_null($categoryWithSameTitle)) {
            return response()->json(
                [
                    'field' => 'title',
                    'message' => 'Category with same title already exists',
                ], 409
            );
        }

        $categoryWithSameSlug = $this->categoryRepository->findBySlug($slug);
        if (false === is_null($categoryWithSameSlug)) {
            return response()->json(
                [
                    'field' => 'slug',
                    'message' => 'Category with same slug already exists',
                ], 409
            );
        }

        $category = new Category();
        $category
            ->setTitle($title)
            ->setSlug($slug)
            ->setShowInBottom($showInBottom);

        if (false === is_null($image)) {
            $imageName = $this->categoryService->uploadCategoryImage($image);
            $category->setImage($imageName);
        }

        $category->save();

        return response()->json([], 201);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function deleteOneAction(int $id): JsonResponse
    {
        $category = $this->categoryRepository->getById($id);

        if (false === is_null($category->getImage())) {
            $this->categoryService->deleteCategoryImage($category->getImage());
        }

        $category->delete();

        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param CreateCategoryRequest $request
     *
     * @return JsonResponse
     */
    public function updateOneAction(int $id, CreateCategoryRequest $request): JsonResponse
    {
        $title = $request->get('title');
        $slug = $request->get('slug');
        $showInBottom = $request->get('show_in_bottom') === 'true' ? true : false;
        $image = $request->file('image');

        $category = $this->categoryRepository->getById($id);

        $categoryWithSameTitle = $this->categoryRepository->findByTitle($title);
        if (false === is_null($categoryWithSameTitle) && $categoryWithSameTitle->getId() !== $category->getId()) {
            return response()->json(
                [
                    'field' => 'title',
                    'message' => 'Category with same title already exists',
                ], 409
            );
        }

        $categoryWithSameSlug = $this->categoryRepository->findBySlug($slug);
        if (false === is_null($categoryWithSameSlug) && $categoryWithSameSlug->getId() !== $category->getId()) {
            return response()->json(
                [
                    'field' => 'slug',
                    'message' => 'Category with same slug already exists',
                ], 409
            );
        }

        if (false === is_null($image)) {
            $categoryOldImage = $category->getImage();
            $imageName = $this->categoryService->uploadCategoryImage($image);
            $category->setImage($imageName);

            if (false === is_null($categoryOldImage)) {
                $this->categoryService->deleteCategoryImage($categoryOldImage);
            }
        }

        $category
            ->setTitle($title)
            ->setSlug($slug)
            ->setShowInBottom($showInBottom);
        $category->save();

        return response()->json([], 204);
    }

    /**
     * @param int $categoryId
     *
     * @return JsonResponse
     */
    public function getOneAction(int $categoryId): JsonResponse
    {
        $category = $this->categoryRepository->getById($categoryId);
        $categoryData = [
            'title' => e($category->getTitle()),
            'slug' => e($category->getSlug()),
            'show_in_bottom' => $category->isShownInBottom(),
        ];

        if (false === is_null($category->getImage())) {
            $categoryDirRelPath = $this->categoryService->getCategoryImageDirRelPath();
            $categoryData['image'] = asset($categoryDirRelPath . $category->getImage());
        }

        return response()->json($categoryData, 200);
    }

    /**
     * @return JsonResponse
     */
    public function getListAction(): JsonResponse
    {
        $categories = $this->categoryRepository->getAll();

        $categoriesData = [];

        /** @var Category $category */
        foreach ($categories as $category) {
            $categoriesData[] = [
                'id' => $category->getId(),
                'title' => e($category->getTitle()),
                'slug' => e($category->getSlug()),
                'show_in_bottom' => $category->isShownInBottom(),
            ];
        }

        return response()->json($categoriesData, 200);
    }
}
