<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Class CategoryService
 *
 * @package App\Services
 */
class CategoryService
{
    /** @var string */
    private static $categoryImageDirName = 'category_images/';

    /**
     * @note this method uploads category image and returns it's relative path
     *
     * @param UploadedFile $categoryImage
     *
     * @return string
     */
    public function uploadCategoryImage(UploadedFile $categoryImage): string
    {
        $categoryImageDirPath = $this->getCategoryImageDirPath();
        $categoryImageOriginalName = $categoryImage->getClientOriginalName();
        $categoryImageNewName = $this->generateCategoryImageName($categoryImageOriginalName);

        $categoryImage->move($categoryImageDirPath, $categoryImageNewName);

        return $categoryImageNewName;
    }

    /**
     * @param string $imageName
     */
    public function deleteCategoryImage(string $imageName): void
    {
        $imagePath = $this->getCategoryImageDirPath() . $imageName;

        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    /**
     * @param string $categoryImageOriginalName
     *
     * @return string
     */
    private function generateCategoryImageName(string $categoryImageOriginalName): string
    {
        $categoryImageName = snake_case(pathinfo($categoryImageOriginalName, PATHINFO_FILENAME)) . time();
        $categoryImageName = md5($categoryImageName)  . '.' . pathinfo($categoryImageOriginalName, PATHINFO_EXTENSION);

        return $categoryImageName;
    }

    /**
     * @return string
     */
    private function getCategoryImageDirPath(): string
    {
        return storage_path('app/public/') . self::$categoryImageDirName;
    }

    /**
     * @return string
     */
    public function getCategoryImageDirRelPath(): string
    {
        return 'storage/' . self::$categoryImageDirName;
    }
}
