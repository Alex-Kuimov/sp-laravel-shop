<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');
        
        // Фильтрация по имени
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        // Фильтрация по категории
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Фильтрация по цене (минимальная цена)
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        // Фильтрация по цене (максимальная цена)
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Пагинация
        $products = $query->paginate(10);
        
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);
        
        $product = Product::create($request->validated());
        
        // Обработка загрузки изображений
        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        }
        
        return response()->json($product->load('category'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'media']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        
        $product->update($request->validated());
        
        // Обработка загрузки изображений
        if ($request->hasFile('image')) {
            // Удаляем старые изображения
            $product->clearMediaCollection('images');
            // Добавляем новое изображение
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        }
        
        return response()->json($product->load(['category', 'media']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        
        // Удаляем изображения
        $product->clearMediaCollection('images');
        
        $product->delete();
        
        return response()->json(null, 204);
    }
}
