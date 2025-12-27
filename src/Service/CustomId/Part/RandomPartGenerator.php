<?php

declare(strict_types=1);

namespace App\Service\CustomId\Part;

use App\Domain\Policy\InventoryIdPartGeneratorInterface;
use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;

final class RandomPartGenerator implements InventoryIdPartGeneratorInterface
{
    public function supports(InventoryIdFormatPart $part): bool
    {
        return $part->getType() === InventoryIdPartType::RANDOM;
    }

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
