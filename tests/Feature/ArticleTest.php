<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Str;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_articles()
    {
        Article::factory()->count(3)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_show_an_article()
    {
        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson([
                'title' => $article->title,
                'content' => $article->content,
            ]);
    }

    /** @test */
    public function it_can_create_an_article()
    {
        $articleData = Article::factory()->make()->toArray();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJson([
                'title' => $articleData['title'],
                'content' => $articleData['content'],
            ]);

        $this->assertDatabaseHas('articles', [
            'title' => $articleData['title'],
        ]);
    }

    /** @test */
    public function it_can_update_an_article()
    {
        $article = Article::factory()->create();
        $updatedData = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'content' => 'Updated Content',
            'excerpt' => 'Updated Excerpt',
            'user_id' => $article->user_id,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/articles/{$article->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Title',
                'content' => 'Updated Content',
            ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Updated Title',
        ]);
    }

    /** @test */
    public function it_can_delete_an_article()
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }
}
