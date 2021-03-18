<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Rules\CategoryImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Class CreateCategoryRequest
 *
 * @package App\Http\Requests
 */
class CreateCategoryRequest extends FormRequest
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
            'title' => 'required|min:1|max:' . Category::TITLE_MAX_LENGTH,
            'slug' => 'required|min:' . Category::SLUG_MIN_LENGTH . '|max:' . Category::SLUG_MAX_LENGTH,
            'image' => [
                new CategoryImage(),
            ],
            'show_in_bottom' => [
                'required',
                Rule::in(['true', 'false']),
            ]
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
