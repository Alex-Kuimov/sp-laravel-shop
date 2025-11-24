<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ArticleService
{
    /**
     * Получить список статей с пагинацией
     *
     * @return LengthAwarePaginator
     */
    public function getArticles(): LengthAwarePaginator
    {
        return Article::with('user')->latest()->paginate(10);
    }

    /**
     * Создать новую статью
     *
     * @param array $data
     * @param User $user
     * @return Article
     */
    public function createArticle(array $data, User $user): Article
    {
        // Устанавливаем текущего пользователя как автора статьи, если не указан другой пользователь
        if (!isset($data['user_id'])) {
            $data['user_id'] = $user->id;
        }
        
        return Article::create($data);
    }

    /**
     * Обновить данные статьи
     *
     * @param Article $article
     * @param array $data
     * @return Article
     */
    public function updateArticle(Article $article, array $data): Article
    {
        $article->update($data);
        return $article;
    }

    /**
     * Удалить статью
     *
     * @param Article $article
     * @return bool|null
     * @throws \Exception
     */
    public function deleteArticle(Article $article): ?bool
    {
        return $article->delete();
    }
}