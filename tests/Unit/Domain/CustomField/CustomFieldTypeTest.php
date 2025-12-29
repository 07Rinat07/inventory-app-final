<?php

namespace App\Tests\Unit\Domain\CustomField;

use App\Domain\CustomField\CustomFieldType;
use PHPUnit\Framework\TestCase;

final class CustomFieldTypeTest extends TestCase
{
    public function testLabelIsNotEmpty(): void
    {
        foreach (CustomFieldType::cases() as $type) {
            $this->assertNotEmpty($type->label());
        }
    }

    public function testTryFromInvalidValue(): void
    {
        $this->assertNull(CustomFieldType::tryFrom('invalid'));
    }
}
// проверяем домен, а не Symfony
//— Enum используется осознанно
