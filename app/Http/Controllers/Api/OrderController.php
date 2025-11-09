<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;

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
    public function store(OrderRequest $request)
    {
        $order = Order::create($request->validated());
        
        return response()->json($order, 201);
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
        // Проверяем, что пользователь имеет право обновлять заказ
        if (!auth()->user()->can('update', $order)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $order->update($request->validated());
        
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
    
    /**
     * Update the status of the order.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Только админы могут менять статус заказа
        if (!auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'status' => 'required|string'
        ]);
        
        $order->update(['status' => $request->status]);
        
        // Создаем запись в истории заказа
        $order->history()->create([
            'status' => $request->status,
            'changed_at' => now()
        ]);
        
        return response()->json($order->load(['user', 'products', 'payment', 'history']));
    }
}
