<?php
namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'title'           => $title,
            'slug'            => Str::slug($title),
            'content'         => $this->faker->paragraphs(5, true),
            'excerpt'         => $this->faker->sentence(),
            'seo_title'       => $this->faker->sentence(),
            'seo_description' => $this->faker->text(160),
            'seo_keywords'    => $this->faker->words(5, true),
            'status'          => $this->faker->randomElement(['draft', 'published', 'archived']),
            'user_id'         => User::factory(),
        ];
    }
}
