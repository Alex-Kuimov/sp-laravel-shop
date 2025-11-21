<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';
    
    /**
     * Получить все возможные значения статусов
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}