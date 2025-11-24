<?php
namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    /**
     * Получить список категорий с пагинацией и фильтрацией
     *
     * @param string|null $name
     * @return LengthAwarePaginator
     */
    public function getCategories(int $page, string $search): LengthAwarePaginator
    {
        return Category::orderBy('id', 'desc')
            ->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('name', 'like', '%' . $search . '%');
            })
            ->paginate(12, ['*'], 'page', $page);
    }

    /**
     * Создать новую категорию
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Category
     */
    public function createCategory(array $data, ?UploadedFile $image = null): Category
    {
        $category = Category::create($data);

        // Обработка загрузки изображений
        if ($image) {
            $category->addMedia($image)->toMediaCollection('images');
        }

        return $category;
    }

    /**
     * Обновить данные категории
     *
     * @param Category $category
     * @param array $data
     * @param UploadedFile|null $image
     * @return Category
     */
    public function updateCategory(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        $category->update($data);

        // Обработка загрузки изображений
        if ($image) {
            // Удаляем старые изображения
            $category->clearMediaCollection('images');
            // Добавляем новое изображение
            $category->addMedia($image)->toMediaCollection('images');
        }

        return $category;
    }

    /**
     * Удалить категорию
     *
     * @param Category $category
     * @return bool|null
     * @throws \Exception
     */
    public function deleteCategory(Category $category): ?bool
    {
        // Удаляем изображения
        $category->clearMediaCollection('images');

        return $category->delete();
    }
}
