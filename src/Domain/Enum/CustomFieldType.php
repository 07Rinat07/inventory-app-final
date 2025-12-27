<?php

declare(strict_types=1);

namespace App\Domain\Enum;

enum CustomFieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case LINK = 'link';
    case BOOLEAN = 'boolean';
}
