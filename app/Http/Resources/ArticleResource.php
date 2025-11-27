<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{

    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'slug'            => $this->slug,
            'content'         => $this->content,
            'excerpt'         => $this->excerpt,
            'status'          => $this->status,
            'seo_title'       => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords'    => $this->seo_keywords,
            'user_id'         => $this->user_id,
            'category_id'     => $this->category_id,
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
            'user'            => $this->whenLoaded('user'),
            'category'        => $this->whenLoaded('category'),
        ];
    }
}
