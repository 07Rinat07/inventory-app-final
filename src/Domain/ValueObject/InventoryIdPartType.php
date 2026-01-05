<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Типы кусочков, из которых собирается ID.
 */
enum InventoryIdPartType: string
{
    /** Обычный текст (префикс, тире и т.д.) */
    case FIXED = 'FIXED';

    /** Случайные символы */
    case RANDOM = 'RANDOM';

    /** Дата или время */
    case DATETIME = 'DATETIME';

    /** Порядковый номер (должен быть один на весь формат) */
    case SEQ = 'SEQ';

    /**
     * Все доступные значения строкой.
     */
    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }
}
