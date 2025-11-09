<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderStoreRequest;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'products', 'payment']);
        
        // Если пользователь не админ, показываем только его заказы
        if (!$request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }
        
        // Фильтрация по статусу
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Фильтрация по пользователю (только для админов)
        if ($request->user()->isAdmin() && $request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Пагинация
        $orders = $query->paginate(10);
        
        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderStoreRequest $request)
    {
        // Получаем все товары из корзины пользователя
        $cartItems = Cart::where('user_id', $request->user()->id)->with('product')->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }
        
        // Рассчитываем общую сумму заказа
        $total = 0;
        foreach ($cartItems as $item) {
            $price = $item->product->discount_price ?? $item->product->price;
            $total += $price * $item->quantity;
        }
        
        // Создаем заказ
        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'new',
            'total' => $total
        ]);
        
        // Создаем записи о продуктах в заказе
        foreach ($cartItems as $item) {
            $price = $item->product->discount_price ?? $item->product->price;
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $price
            ]);
        }
        
        // Очищаем корзину
        Cart::where('user_id', $request->user()->id)->delete();
        
        return response()->json($order->load('products'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Проверяем, что пользователь имеет право просматривать заказ
        if (!auth()->user()->can('view', $order)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json($order->load(['user', 'products', 'payment', 'history']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, Order $order)
    {
        // Только админ может обновлять заказ
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validatedData = $request->validated();
        $order->update($validatedData);
        
        return response()->json($order->load(['user', 'products', 'payment', 'history']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        // Проверяем, что пользователь имеет право удалять заказ
        if (!auth()->user()->can('delete', $order)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $order->delete();
        
        return response()->json(null, 204);
    }
    
}

