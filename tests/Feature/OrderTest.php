<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_orders_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        
        $customer = User::factory()->create(['role' => 'customer']);
        Order::factory()->count(5)->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'status', 'total']
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_can_list_own_orders_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        Order::factory()->count(3)->create(['user_id' => $customer->id]);
        Order::factory()->count(2)->create(); // Other users' orders

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_show_an_order_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'user_id' => $customer->id,
                'status' => $order->status,
                'total' => $order->total,
            ]);
    }

    /** @test */
    public function it_can_show_own_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'user_id' => $customer->id,
                'status' => $order->status,
                'total' => $order->total,
            ]);
    }

    /** @test */
    public function it_cannot_show_other_user_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $otherCustomer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_create_an_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'price' => 100.00]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'user_id' => $customer->id,
                'status' => 'pending',
                'total' => 100.00,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'user_id' => $customer->id,
                'status' => 'pending',
                'total' => 100.00,
            ]);
        
        $this->assertDatabaseHas('orders', ['user_id' => $customer->id]);
    }

    /** @test */
    public function it_can_update_an_order_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/orders/' . $order->id, [
                'status' => 'completed',
                'total' => 150.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $order->id,
                'user_id' => $customer->id,
                'status' => 'completed',
                'total' => 150.00,
            ]);
    }

    /** @test */
    public function it_cannot_update_an_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/orders/' . $order->id, [
                'status' => 'completed',
                'total' => 150.00,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_an_order_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;
        
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function it_cannot_delete_an_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;
        
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(403);
    }
}