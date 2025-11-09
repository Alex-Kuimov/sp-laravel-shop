<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data'  => [
                    '*' => ['id', 'name', 'description', 'status'],
                ],
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links' => [
                    '*' => ['url', 'label', 'active'],
                ],
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
    }

    /** @test */
    public function it_can_show_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'          => $category->id,
                'name'        => $category->name,
                'description' => $category->description,
                'status'      => $category->status,
            ]);
    }

    /** @test */
    public function it_can_create_a_category_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name'        => 'Test Category',
                'description' => 'Test Description',
                'status'      => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'status',
                'description',
                'created_at',
                'updated_at',
            ])
            ->assertJsonPath('name', 'Test Category')
            ->assertJsonPath('description', 'Test Description');

        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    /** @test */
    public function it_cannot_create_a_category_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name'        => 'Test Category',
                'description' => 'Test Description',
                'status'      => 'active',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_a_category_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/categories/' . $category->id, [
                'name'        => 'Updated Category',
                'description' => 'Updated Description',
                'status'      => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id'          => $category->id,
                'name'        => 'Updated Category',
                'description' => 'Updated Description',
                'status'      => 'inactive',
            ]);
    }

    /** @test */
    public function it_cannot_update_a_category_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/categories/' . $category->id, [
                'name'        => 'Updated Category',
                'description' => 'Updated Description',
                'status'      => 'inactive',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_category_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_cannot_delete_a_category_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(403);
    }
}
