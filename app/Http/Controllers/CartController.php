<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $carts = Cart::where('user_id', $request->user()->id)->with('product')->get();
        
        return response()->json($carts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request)
    {
        // Проверяем, есть ли уже такой товар в корзине пользователя
        $cartItem = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();
            
        if ($cartItem) {
            // Если есть, увеличиваем количество
            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity
            ]);
            
            return response()->json($cartItem);
        }
        
        // Если нет, создаем новую запись
        $cartItem = Cart::create([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);
        
        return response()->json($cartItem, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Cart $cart)
    {
        // Проверяем, что пользователь запрашивает свою корзину
        if ($cart->user_id !== request()->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json($cart->load('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, Cart $cart)
    {
        // Проверяем, что пользователь обновляет свою корзину
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $cart->update($request->validated());
        
        return response()->json($cart->load('product'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Cart $cart)
    {
        // Проверяем, что пользователь удаляет из своей корзины
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $cart->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Create an order from the cart.
     */
    public function createOrder(Request $request)
    {
        // Получаем все товары из корзины пользователя
        $cartItems = Cart::where('user_id', $request->user()->id)->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }
        
        // Рассчитываем общую сумму заказа
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->product->price * $item->quantity;
        }
        
        // Создаем заказ
        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'total' => $total
        ]);
        
        // Создаем записи о продуктах в заказе
        foreach ($cartItems as $item) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price
            ]);
            
        }
        
        // Очищаем корзину
        Cart::where('user_id', $request->user()->id)->delete();
        
        return response()->json($order->load('products'), 201);
    }
}
