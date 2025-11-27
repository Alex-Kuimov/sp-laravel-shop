<?php
namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
    /**
     * Получить список статей с пагинацией
     *
     * @return LengthAwarePaginator
     */
    public function getArticles(int $page, string $search): LengthAwarePaginator
    {
        return Article::orderBy('id', 'desc')
            ->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('title', 'like', '%' . $search . '%');
            })
            ->paginate(12, ['*'], 'page', $page);
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
