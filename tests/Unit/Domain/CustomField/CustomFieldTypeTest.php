<?php

namespace App\Tests\Unit\Domain\CustomField;

use App\Domain\CustomField\CustomFieldType;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для Enum кастомных полей.
 * Проверяем, что все типы имеют названия и корректно ведут себя при неверных данных.
 */
final class CustomFieldTypeTest extends TestCase
{
    /**
     * У каждого типа должно быть человекопонятное название для вывода в интерфейсе.
     */
    public function testLabelIsNotEmpty(): void
    {
        foreach (CustomFieldType::cases() as $type) {
            $this->assertNotEmpty($type->label());
        }
    }

    /**
     * Если придет абракадабра вместо типа — должны получить null.
     */
    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(CustomFieldType::tryFrom('invalid'));
    }
}
