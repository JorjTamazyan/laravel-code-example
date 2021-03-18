<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\ProductImages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest
 *
 * @package App\Http\Requests
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'min:1|max:' . Product::TITLE_MAX_LENGTH,
            'description' => 'min:' . Product::DESCRIPTION_MIN_LENGTH .'|max:' . Product::DESCRIPTION_MAX_LENGTH,
            'price' => 'integer|min:1',
            'category_id' => 'integer|exists:categories,id',
            'images' => [
                new ProductImages(),
            ],
            'video_url' => [
                'url',
            ],
            'show_on_website' => [
                Rule::in(['true', 'false']),
            ],
        ];
    }

    public function messages()
    {
        return [
            'images.required_if' => 'Product must have at least 1 image.',
        ];
    }

    /**
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json(['errors' => $validator->errors()], 422));
    }
}
