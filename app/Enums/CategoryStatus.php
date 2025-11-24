<?php
namespace App\Enums;

enum CategoryStatus: string {
    case DRAFT     = 'draft';
    case PUBLISHED = 'published';

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
