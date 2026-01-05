<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

/**
 * Генератор случайной части идентификатора.
 */
final class RandomPartGenerator implements InventoryIdPartGeneratorInterface
{
    /**
     * Проверяет, поддерживает ли данный генератор указанную часть формата.
     */
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::RANDOM;
    }

    /**
     * Генерирует случайную строку заданной длины из указанного алфавита.
     *
     * @param InventoryIdFormatPart $part Часть формата.
     * @param int|null $sequenceValue Значение последовательности (не используется).
     * @return string Сгенерированная случайная строка.
     * @throws \LogicException Если параметры генерации некорректны.
     */
    public function generate(InventoryIdFormatPart $part, ?int $sequenceValue): string
    {
        // param1 — длина
        $length = (int) ($part->getParam1() ?? 6);
        if ($length <= 0 || $length > 64) {
            throw new \LogicException('RANDOM part param1 must be between 1 and 64.');
        }

        // param2 — алфавит (опционально)
        $alphabet = (string) ($part->getParam2() ?? '');
        $alphabet = $alphabet !== '' ? $alphabet : 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // без 0/O/1/I

        return $this->randomFromAlphabet($alphabet, $length);
    }

    /**
     * Вспомогательный метод для выбора случайных символов из алфавита.
     */
    private function randomFromAlphabet(string $alphabet, int $length): string
    {
        $alphabet = array_values(array_unique(mb_str_split($alphabet)));
        if (count($alphabet) < 2) {
            throw new \LogicException('RANDOM part alphabet (param2) must contain at least 2 distinct characters.');
        }

        $max = count($alphabet) - 1;
        $out = '';

        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }
}
