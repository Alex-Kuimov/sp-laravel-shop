<?php

namespace App\Http\Controllers;

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
    
}
