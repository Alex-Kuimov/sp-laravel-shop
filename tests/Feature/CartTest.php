<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_cart_items()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Cart::factory()->create(['user_id' => $customer->id, 'product_id' => $product->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/carts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'user_id', 'product_id', 'quantity', 'product']
            ]);
    }

    /** @test */
    public function it_can_add_item_to_cart()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/carts', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => 2,
            ]);
        
        $this->assertDatabaseHas('carts', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function it_can_show_a_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/carts/' . $cartItem->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $cartItem->id,
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => 3,
            ]);
    }

    /** @test */
    public function it_cannot_show_other_user_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/carts/' . $cartItem->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_update_a_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/carts/' . $cartItem->id, [
                'quantity' => 5,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $cartItem->id,
                'user_id' => $customer->id,
                'product_id' => $product->id,
                'quantity' => 5,
            ]);
    }

    /** @test */
    public function it_cannot_update_other_user_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/carts/' . $cartItem->id, [
                'quantity' => 5,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_a_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/carts/' . $cartItem->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('carts', ['id' => $cartItem->id]);
    }

    /** @test */
    public function it_cannot_delete_other_user_cart_item()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $cartItem = Cart::factory()->create([
            'user_id' => $otherCustomer->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/carts/' . $cartItem->id);

        $response->assertStatus(403);
    }
}