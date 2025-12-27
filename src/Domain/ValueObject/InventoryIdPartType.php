<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum InventoryIdPartType: string
{
    case FIXED = 'FIXED';
    case RANDOM = 'RANDOM';
    case DATETIME = 'DATETIME';
    case SEQ = 'SEQ';

    public static function values(): array
    {
        return array_map(static fn(self $c) => $c->value, self::cases());
    }
}
