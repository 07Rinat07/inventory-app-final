<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/**
 * Типы кастомных полей (ограниченный набор).
 * Это Domain-уровень, не UI и не Entity.
 */
enum CustomFieldType: string
{
    case TEXT = 'TEXT';
    case TEXTAREA = 'TEXTAREA';
    case NUMBER = 'NUMBER';
    case LINK = 'LINK';
    case BOOL = 'BOOL';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }
}

