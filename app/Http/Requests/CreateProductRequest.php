<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Rules\ProductImages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Class CreateProductRequest
 *
 * @package App\Http\Requests
 */
class CreateProductRequest extends FormRequest
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
            'title' => 'required|min:1|max:' . Product::TITLE_MAX_LENGTH,
            'description' => 'required|min:' . Product::DESCRIPTION_MIN_LENGTH .'|max:' . Product::DESCRIPTION_MAX_LENGTH,
            'price' => 'required|integer|min:1',
            'category_id' => 'required|integer|exists:categories,id',
            'images' => [
                'required_if:video_url,',
                new ProductImages(),
            ],
            'video_url' => [
                'required_if:images,',
                'url',
            ],
            'show_on_website' => [
                'required',
                Rule::in(['true', 'false']),
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'images.required_if' => 'Обязательно, загрузить картинку продукта, если ссылка на видео продукта не указана.',
            'video_url.required_if' => 'Обязательно, указать видео продукта, если картинки продукта не загружены.',
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
