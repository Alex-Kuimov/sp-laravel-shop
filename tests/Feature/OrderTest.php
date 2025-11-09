<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
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
                'current_page',
                'data'  => [
                    '*' => ['id', 'user_id', 'status', 'total'],
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
    public function it_can_list_own_orders_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

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
        $order    = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'      => $order->id,
                'user_id' => $customer->id,
                'status'  => $order->status,
                'total'   => $order->total,
            ]);
    }

    /** @test */
    public function it_can_show_own_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJson([
                'id'      => $order->id,
                'user_id' => $customer->id,
                'status'  => $order->status,
                'total'   => $order->total,
            ]);
    }

    /** @test */
    public function it_cannot_show_other_user_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order         = Order::factory()->create(['user_id' => $otherCustomer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_create_an_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test-token')->plainTextToken;

        // Создаем категорию
        $category = \App\Models\Category::create([
            'name' => 'Test Category',
            'description' => 'Test Category Description'
        ]);

        // Создаем продукты с фиксированными ценами напрямую через модель
        $product1 = Product::create([
            'name' => 'Test Product 1',
            'description' => 'Test Product 1 Description',
            'price' => 100.00,
            'discount_price' => 90.00,
            'category_id' => $category->id,
            'status' => 'active'
        ]); // Со скидкой
        
        $product2 = Product::create([
            'name' => 'Test Product 2',
            'description' => 'Test Product 2 Description',
            'price' => 50.00,
            'category_id' => $category->id,
            'status' => 'active'
        ]); // Без скидки

        // Добавляем продукты в корзину
        Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $product1->id,
            'quantity' => 2
        ]);

        Cart::factory()->create([
            'user_id' => $customer->id,
            'product_id' => $product2->id,
            'quantity' => 1
        ]);

        // Создаем заказ (не передаем данные в теле запроса)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders');

        // Проверяем успешный ответ
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'status',
                'total',
                'created_at',
                'updated_at',
                'products' => [
                    '*' => ['id', 'name', 'description', 'price', 'discount_price', 'category_id', 'status', 'created_at', 'updated_at', 'pivot' => ['order_id', 'product_id', 'quantity', 'price']]
                ]
            ]);

        // Рассчитываем ожидаемую сумму заказа
        $expectedTotal = ($product1->discount_price * 2) + ($product2->price * 1); // (90.00 * 2) + (50.00 * 1) = 180.00 + 50.00 = 230.00

        // Проверяем, что заказ создан правильно
        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'new',
            'total' => $expectedTotal
        ]);

        // Проверяем, что созданы записи о продуктах в заказе
        $order = Order::where('user_id', $customer->id)->first();
        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => $product1->discount_price // discount_price
        ]);

        $this->assertDatabaseHas('order_product', [
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => $product2->price // обычный price
        ]);

        // Проверяем, что корзина очищена
        $this->assertDatabaseMissing('carts', [
            'user_id' => $customer->id
        ]);
    }

    /** @test */
    public function it_can_update_an_order_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $customer = User::factory()->create(['role' => 'customer']);
        $order    = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/orders/' . $order->id, [
                'status' => 'completed',
                'total'  => 150.00,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id'      => $order->id,
                'user_id' => $customer->id,
                'status'  => 'completed',
                'total'   => 150.00,
            ]);
    }

    /** @test */
    public function it_cannot_update_an_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => 'pending']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/orders/' . $order->id, [
                'status' => 'completed',
                'total'  => 150.00,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_delete_an_order_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $customer = User::factory()->create(['role' => 'customer']);
        $order    = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function it_cannot_delete_an_order_as_customer()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token    = $customer->createToken('test-token')->plainTextToken;

        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/orders/' . $order->id);

        $response->assertStatus(403);
    }
}
