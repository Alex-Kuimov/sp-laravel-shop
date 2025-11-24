<?php
namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //return $this->user()->can('update', $this->article);
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
            'title'           => 'sometimes|string|max:255',
            'slug'            => 'sometimes|string|max:255|unique:articles,slug,' . $this->article->id,
            'content'         => 'sometimes|string',
            'excerpt'         => 'nullable|string|max:500',
            'seo_title'       => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords'    => 'nullable|string',
            'status'          => [
                'sometimes',
                Rule::in(ArticleStatus::values()),
            ],
            'user_id'         => 'sometimes|exists:users,id',
        ];
    }
}
