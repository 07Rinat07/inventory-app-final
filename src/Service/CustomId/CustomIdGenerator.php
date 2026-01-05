<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Entity\InventoryIdFormatPart;
use App\Entity\InventorySequence;
use App\Repository\InventoryIdFormatPartRepository;
use App\Repository\InventorySequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Генератор кастомных идентификаторов для предметов инвентаря на основе настроенного формата.
 */
final class CustomIdGenerator
{
    /**
     * Создает новый экземпляр генератора.
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly InventoryIdFormatPartRepository $formatPartRepository,
        private readonly InventorySequenceRepository $sequenceRepository,
    ) {}

    /**
     * Генерирует уникальную строку идентификатора для указанного инвентаря.
     *
     * @param Inventory $inventory Инвентарь, для которого генерируется ID.
     * @return string Сгенерированный идентификатор.
     * @throws \LogicException Если формат идентификатора не настроен.
     */
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

    /**
     * Разрешает значение конкретной части формата.
     */
    private function resolvePart(
        Inventory $inventory,
        InventoryIdFormatPart $part
    ): string {
        return match ($part->getType()) {
            InventoryIdPartType::FIXED =>
            (string) $part->getParam1(),

            InventoryIdPartType::DATETIME =>
            (new \DateTimeImmutable())->format($part->getParam1() ?? 'Y'),

            InventoryIdPartType::RANDOM =>
            $this->random((int) ($part->getParam1() ?? 6)),

            InventoryIdPartType::SEQ =>
            $this->nextSequence($inventory, (int) ($part->getParam1() ?? 6)),

            default =>
            throw new \LogicException('Unsupported ID format part type.'),
        };
    }

    /**
     * Получает следующее значение последовательности для инвентаря.
     */
    private function nextSequence(Inventory $inventory, int $length): string
    {
        // В Repository метод называется findForUpdate, а здесь вызывался findOneByInventoryForUpdate.
        // Нужно проверить соответствие имен. Ранее в InventorySequenceRepository я видел findForUpdate.
        $sequence = $this->sequenceRepository
            ->findForUpdate($inventory);

        if ($sequence === null) {
            $sequence = new InventorySequence();
            $sequence->setInventory($inventory);
            $this->em->persist($sequence);
        }

        $value = $sequence->getNextValue();
        $sequence->increment();

        return str_pad((string) $value, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Генерирует случайную шестнадцатеричную строку заданной длины.
     */
    private function random(int $length): string
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
