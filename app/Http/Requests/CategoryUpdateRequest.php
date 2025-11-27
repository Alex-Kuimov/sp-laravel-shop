<?php

namespace App\Http\Requests;

use App\Enums\CategoryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name'            => 'sometimes|string|max:255',
            'slug'            => 'sometimes|string|max:255|unique:categories,slug',
            'status'          => [
                'sometimes',
                Rule::in(CategoryStatus::values()),
            ],
            'description'      => 'nullable|string',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords'    => 'nullable|string',
            'image'           => 'nullable|image|max:2048', // Максимальный размер 2MB
        ];

        // Для обновления исключаем текущую категорию из проверки уникальности
        if ($this->category && $this->category instanceof \App\Models\Category) {
            $rules['slug'] = 'sometimes|string|max:255|unique:categories,slug,' . $this->category->id;
        }

        return $rules;
    }
}