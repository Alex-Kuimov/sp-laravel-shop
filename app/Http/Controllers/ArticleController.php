<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Http\Requests\ArticleStoreRequest;
use App\Http\Requests\ArticleUpdateRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\ArticleCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with('user')->latest()->paginate(10);
        return new ArticleCollection($articles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleStoreRequest $request)
    {
        // Устанавливаем текущего пользователя как автора статьи, если не указан другой пользователь
        $data = $request->validated();
        if (!isset($data['user_id'])) {
            $data['user_id'] = $request->user()->id;
        }
        
        $article = Article::create($data);
        return (new ArticleResource($article))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $article->load('user');
        return new ArticleResource($article);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleUpdateRequest $request, Article $article)
    {
        $article->update($request->validated());
        return new ArticleResource($article);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        if (!Gate::allows('delete', $article)) {
            abort(403);
        }
        
        $article->delete();
        return response()->noContent();
    }
}
