<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
                'data' => [
                    '*' => ['id', 'name', 'description', 'status']
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_can_show_a_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson('/api/categories/' . $category->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'status' => $category->status,
            ]);
    }

    /** @test */
    public function it_can_create_a_category_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name' => 'Test Category',
                'description' => 'Test Description',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Test Category',
                'description' => 'Test Description',
                'status' => 'active',
            ]);
        
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    /** @test */
    public function it_cannot_create_a_category_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/categories', [
                'name' => 'Test Category',
                'description' => 'Test Description',
                'status' => 'active',
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
                'name' => 'Updated Category',
                'description' => 'Updated Description',
                'status' => 'inactive',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $category->id,
                'name' => 'Updated Category',
                'description' => 'Updated Description',
                'status' => 'inactive',
            ]);
    }

    /** @test */
    public function it_cannot_update_a_category_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/categories/' . $category->id, [
                'name' => 'Updated Category',
                'description' => 'Updated Description',
                'status' => 'inactive',
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
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/categories/' . $category->id);

        $response->assertStatus(403);
    }
}