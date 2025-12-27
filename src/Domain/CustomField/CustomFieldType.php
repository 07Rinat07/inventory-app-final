<?php

declare(strict_types=1);

namespace App\Domain\CustomField;

/**
 * Типы пользовательских полей инвентаря.
 *
 * Используется:
 * - в Entity CustomField
 * - в UI (select)
 * - в Items / FieldValues
 * - в API DTO
 */
enum CustomFieldType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case DATE = 'date';
    case BOOLEAN = 'boolean';

    /**
     * Человекочитаемые названия (для UI)
     */
    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::NUMBER => 'Number',
            self::DATE => 'Date',
            self::BOOLEAN => 'Yes / No',
        };
    }
}
