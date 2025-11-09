<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Фильтрация по имени
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Пагинация
        $categories = $query->paginate(10);

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::create($request->validated());

        // Обработка загрузки изображений
        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json($category->load('media'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category->update($request->validated());

        // Обработка загрузки изображений
        if ($request->hasFile('image')) {
            // Удаляем старые изображения
            $category->clearMediaCollection('images');
            // Добавляем новое изображение
            $category->addMediaFromRequest('image')->toMediaCollection('images');
        }

        return response()->json($category->load('media'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Удаляем изображения
        $category->clearMediaCollection('images');

        $category->delete();

        return response()->json(null, 204);
    }
}
