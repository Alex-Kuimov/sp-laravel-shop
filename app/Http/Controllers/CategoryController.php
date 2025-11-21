<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $categories = $this->categoryService->getCategories($request->name ?? null);
        return new CategoryCollection($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        if (! $this->categoryService->canCreate()) {
            return ApiResponse::unauthorized();
        }

        $category = $this->categoryService->createCategory(
            $request->validated(),
            $request->file('image')
        );

        return new CategoryResource($category->load('media'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return new CategoryResource($category->load('media'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        if (! $this->categoryService->canUpdate($category)) {
            return ApiResponse::unauthorized();
        }

        $category = $this->categoryService->updateCategory(
            $category,
            $request->validated(),
            $request->file('image')
        );

        return new CategoryResource($category->load('media'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (! $this->categoryService->canDelete($category)) {
            return ApiResponse::unauthorized();
        }

        $this->categoryService->deleteCategory($category);

        return ApiResponse::deleted();
    }
}
