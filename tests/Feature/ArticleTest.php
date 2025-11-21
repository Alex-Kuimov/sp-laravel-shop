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
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'customer']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    public function it_can_list_articles()
    {
        Article::factory()->count(3)->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'content', 'excerpt', 'status', 'user_id', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_can_show_an_article()
    {
        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'content', 'excerpt', 'status', 'user_id', 'created_at', 'updated_at']
            ])
            ->assertJson([
                'data' => [
                    'title' => $article->title,
                    'content' => $article->content,
                ]
            ]);
    }

    /** @test */
    public function it_can_create_an_article()
    {
        $articleData = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is a test article content.',
            'excerpt' => 'This is a test excerpt.',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'content', 'excerpt', 'status', 'user_id', 'created_at', 'updated_at']
            ])
            ->assertJson([
                'data' => [
                    'title' => $articleData['title'],
                    'content' => $articleData['content'],
                    'status' => $articleData['status'],
                    'user_id' => $this->user->id, // Проверяем, что user_id установлен автоматически
                ]
            ]);

        $this->assertDatabaseHas('articles', [
            'title' => $articleData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_create_an_article_with_specified_user_id()
    {
        // Только админ может указать user_id явно
        $otherUser = User::factory()->create();
        $articleData = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is a test article content.',
            'excerpt' => 'This is a test excerpt.',
            'status' => 'draft',
            'user_id' => $otherUser->id,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'user_id' => $otherUser->id,
                ]
            ]);
    }

    /** @test */
    public function it_cannot_create_an_article_with_invalid_status()
    {
        $articleData = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is a test article content.',
            'excerpt' => 'This is a test excerpt.',
            'status' => 'invalid-status', // Недопустимый статус
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/articles', $articleData);

        $response->assertStatus(422); // Unprocessable Entity
    }

    /** @test */
    public function it_can_update_an_article()
    {
        // Создаем статью от имени пользователя
        $article = Article::factory()->create(['user_id' => $this->user->id]);
        
        $updatedData = [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'content' => 'Updated Content',
            'excerpt' => 'Updated Excerpt',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/articles/{$article->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'title', 'slug', 'content', 'excerpt', 'status', 'user_id', 'created_at', 'updated_at']
            ])
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title',
                    'content' => 'Updated Content',
                    'status' => 'published',
                ]
            ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Updated Title',
            'status' => 'published',
        ]);
    }

    /** @test */
    public function admin_can_update_any_article()
    {
        // Создаем статью от имени обычного пользователя
        $article = Article::factory()->create(['user_id' => $this->user->id]);
        
        $updatedData = [
            'title' => 'Updated Title by Admin',
            'content' => 'Updated Content by Admin',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/articles/{$article->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Updated Title by Admin',
                    'content' => 'Updated Content by Admin',
                    'status' => 'published',
                ]
            ]);
    }

    /** @test */
    public function it_cannot_update_another_users_article()
    {
        // Создаем статью от имени другого пользователя
        $otherUser = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $otherUser->id]);
        
        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/articles/{$article->id}", $updatedData);

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_can_delete_an_article()
    {
        // Создаем статью от имени пользователя
        $article = Article::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    /** @test */
    public function admin_can_delete_any_article()
    {
        // Создаем статью от имени обычного пользователя
        $article = Article::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('articles', [
            'id' => $article->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_another_users_article()
    {
        // Создаем статью от имени другого пользователя
        $otherUser = User::factory()->create();
        $article = Article::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function it_cannot_create_article_without_authentication()
    {
        $articleData = [
            'title' => 'Test Article',
            'slug' => 'test-article',
            'content' => 'This is a test article content.',
            'excerpt' => 'This is a test excerpt.',
            'status' => 'draft',
        ];

        $response = $this->postJson('/api/articles', $articleData);

        $response->assertStatus(401); // Unauthorized
    }
}
