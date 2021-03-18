<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

/**
 * Class OrderImage
 *
 * @package App\Rules
 */
class OrderImage implements Rule
{
    private const SUPPORTED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    private const IMAGE_MAX_SIZE = 10; // 10 MB

    /** @var string */
    private $errorMessage;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $image
     * @return bool
     */
    public function passes($attribute, $image)
    {
        if (false === ($image instanceof UploadedFile)) {
            $this->errorMessage = 'Uploaded image is invalid';

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
