<?php
namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return $this->user()->can('create', Article::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'           => 'required|string|max:255',
            'slug'            => 'required|string|max:255|unique:articles',
            'content'         => 'required|string',
            'excerpt'         => 'nullable|string|max:500',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords'    => 'nullable|string',
            'status'          => [
                'required',
                Rule::in(ArticleStatus::values()),
            ],
            'user_id'         => 'sometimes|exists:users,id',
            'category_id'     => 'required|exists:categories,id',
        ];
    }
}
