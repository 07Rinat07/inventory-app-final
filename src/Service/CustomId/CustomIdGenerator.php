<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use App\Entity\InventorySequence;
use App\Repository\InventoryIdFormatPartRepository;
use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

final class CustomIdGenerator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryIdFormatPartRepository $formatPartRepository,
        private readonly InventorySequenceRepository $sequenceRepository,
    ) {}

    public function generate(Inventory $inventory): string
    {
        return $this->em->transactional(function () use ($inventory): string {
            $parts = $this->formatPartRepository
                ->findBy(
                    ['inventory' => $inventory],
                    ['position' => 'ASC']
                );

            if ($parts === []) {
                throw new \LogicException('Inventory has no ID format configuration.');
            }

            $result = [];

            foreach ($parts as $part) {
                $result[] = $this->resolvePart($inventory, $part);
            }

            return implode('-', $result);
        });
    }

    private function resolvePart(
        Inventory $inventory,
        InventoryIdFormatPart $part
    ): string {
        return match ($part->getType()) {
            InventoryIdFormatPart::TYPE_FIXED =>
            (string) $part->getParam1(),

            InventoryIdFormatPart::TYPE_DATETIME =>
            (new \DateTimeImmutable())->format($part->getParam1() ?? 'Y'),

            InventoryIdFormatPart::TYPE_RANDOM =>
            $this->random((int) ($part->getParam1() ?? 6)),

            InventoryIdFormatPart::TYPE_SEQ =>
            $this->nextSequence($inventory, (int) ($part->getParam1() ?? 6)),

            InventoryIdFormatPart::TYPE_GUID =>
            substr(bin2hex(random_bytes(16)), 0, 12),

            default =>
            throw new \LogicException('Unsupported ID format part type.'),
        };
    }

    private function nextSequence(Inventory $inventory, int $length): string
    {
        $sequence = $this->sequenceRepository
            ->findOneByInventoryForUpdate($inventory);

        if ($sequence === null) {
            $sequence = new InventorySequence();
            $sequence->setInventory($inventory);
            $this->em->persist($sequence);
        }

        $value = $sequence->next();

        return str_pad((string) $value, $length, '0', STR_PAD_LEFT);
    }

    private function random(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
