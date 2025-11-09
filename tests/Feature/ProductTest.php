<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_products()
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data'  => [
                    '*' => ['id', 'name', 'description', 'price', 'discount_price', 'category_id', 'status'],
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
    public function it_can_show_a_product()
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'             => $product->id,
                'name'           => $product->name,
                'description'    => $product->description,
                'price'          => $product->price,
                'discount_price' => $product->discount_price,
                'category_id'    => $category->id,
                'status'         => $product->status,
            ]);
    }

    /** @test */
    public function it_can_create_a_product_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name'           => 'Test Product',
                'description'    => 'Test Description',
                'price'          => 100.00,
                'discount_price' => 90.00,
                'category_id'    => $category->id,
                'status'         => 'active',
            ]);

        $response->assertStatus(201);

        // Проверяем, что ключевые поля действительно установлены
        $response->assertJsonFragment([
            'name'           => 'Test Product',
            'description'    => 'Test Description',
            'price'          => 100.00,
            'discount_price' => 90.00,
            'category_id'    => $category->id,
            'status'         => 'active',
        ]);

        // Проверяем структуру ответа, включая вложенные объекты и даты (без фиксации значений)
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
            'discount_price',
            'category_id',
            'status',
            'created_at',
            'updated_at',
            'category' => [
                'id',
                'name',
                'description',
                'status',
                'created_at',
                'updated_at',
            ],
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    /** @test */
    public function it_cannot_create_a_product_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/products', [
                'name'           => 'Test Product',
                'description'    => 'Test Description',
                'price'          => 100.00,
                'discount_price' => 90.00,
                'category_id'    => $category->id,
                'status'         => 'active',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_a_product_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/products/' . $product->id, [
                'name'           => 'Updated Product',
                'description'    => 'Updated Description',
                'price'          => 150.00,
                'discount_price' => 140.00,
                'category_id'    => $category->id,
                'status'         => 'inactive',
            ]);

        $response->assertStatus(200);

        // Проверяем, что ключевые поля действительно обновлены
        $response->assertJsonFragment([
            'name'           => 'Updated Product',
            'description'    => 'Updated Description',
            'price'          => 150.00,
            'discount_price' => 140.00,
            'category_id'    => $category->id,
            'status'         => 'inactive',
        ]);

        // Проверяем структуру ответа и наличие необходимых полей
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'price',
            'discount_price',
            'category_id',
            'status',
            'created_at',
            'updated_at',
            'category' => [
                'id',
                'name',
                'description',
                'status',
                'created_at',
                'updated_at',
            ],
            'media', // как правило, массив объектов медиа, если есть
        ]);
    }

    /** @test */
    public function it_cannot_update_a_product_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/products/' . $product->id, [
                'name'           => 'Updated Product',
                'description'    => 'Updated Description',
                'price'          => 150.00,
                'discount_price' => 140.00,
                'category_id'    => $category->id,
                'status'         => 'inactive',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_product_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function it_cannot_delete_a_product_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(403);
    }
}
