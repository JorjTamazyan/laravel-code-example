<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

/**
 * Class ProductImages
 *
 * @package App\Rules
 */
class ProductImages implements Rule
{
    private const SUPPORTED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const IMAGE_MAX_SIZE = 10; // 10 MB

    /** @var string */
    private $errorMessage;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $images
     * @return bool
     */
    public function passes($attribute, $images)
    {
        if (false === is_array($images)) {
            $this->errorMessage = 'Images field must be of type array';

            return false;
        }

        if (count($images) > Product::IMAGES_MAX_COUNT) {
            $this->errorMessage = sprintf('Maximum %d images can be uploaded', Product::IMAGES_MAX_COUNT);

            return false;
        }

        /** @var UploadedFile $image */
        foreach ($images as $image) {
            if (false === ($image instanceof UploadedFile)) {
                $this->errorMessage = 'Uploaded images are invalid';

                return false;
            }

            if (false === in_array($image->extension(), self::SUPPORTED_IMAGE_EXTENSIONS)) {
                $this->errorMessage = sprintf(
                    'Unsupported image extension. Must be one of these [%s]',
                    implode(',', self::SUPPORTED_IMAGE_EXTENSIONS)
                );

                return false;
            }

            if ($image->getSize() > self::IMAGE_MAX_SIZE * 1024 * 1024) {
                $this->errorMessage = sprintf(
                    'Image max size must not be more than %d MB',
                    self::IMAGE_MAX_SIZE
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
