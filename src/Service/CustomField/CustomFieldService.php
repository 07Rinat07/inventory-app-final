<?php

declare(strict_types=1);

namespace App\Service\CustomField;

use App\Domain\ValueObject\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

final class CustomFieldService
{
    public function __construct(
        private CustomFieldRepository $repository,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Создаёт поле, соблюдая правило: максимум 3 каждого типа на Inventory.
     */
    public function create(Inventory $inventory, string $label, string $type): CustomField
    {
        Assert::stringNotEmpty($label, 'Label is required');
        Assert::maxLength($label, 100, 'Label is too long');
        Assert::oneOf($type, CustomFieldType::values(), 'Invalid field type');

        // лимит "≤ 3 каждого типа"
        $count = $this->repository->countByInventoryAndType($inventory, $type);
        Assert::lessThanEq($count, 2, 'Limit exceeded: max 3 fields per type'); // если уже 3, то count=3 => нельзя

        $field = new CustomField();
        $field->setInventory($inventory);
        $field->setLabel($label);
        $field->setType($type);
        $field->setPosition($this->repository->getNextPosition($inventory));
        $field->setIsVisible(true);

        $this->em->persist($field);
        $this->em->flush();

        return $field;
    }

    /**
     * Bulk: show/hide выбранных.
     *
     * @param int[] $ids
     */
    public function setVisibilityBulk(Inventory $inventory, array $ids, bool $visible): void
    {
        Assert::allInteger($ids);

        $this->em->wrapInTransaction(function () use ($inventory, $ids, $visible) {
            if ($ids === []) {
                return;
            }

            $fields = $this->repository->findBy(['inventory' => $inventory, 'id' => $ids]);

            foreach ($fields as $f) {
                $f->setIsVisible($visible);
            }

            $this->em->flush();
        });
    }

    /**
     * Bulk delete.
     *
     * @param int[] $ids
     */
    public function deleteBulk(Inventory $inventory, array $ids): int
    {
        Assert::allInteger($ids);

        return $this->em->wrapInTransaction(function () use ($inventory, $ids) {
            return $this->repository->deleteByIds($inventory, $ids);
        });
    }

    /**
     * Reorder: "move up/down" для выбранного ID (toolbar action).
     */
    public function move(Inventory $inventory, int $fieldId, int $direction): void
    {
        Assert::oneOf($direction, [-1, 1]);

        $this->em->wrapInTransaction(function () use ($inventory, $fieldId, $direction) {
            $fields = $this->repository->findByInventoryOrdered($inventory);

            // индекс текущего
            $index = null;
            foreach ($fields as $i => $f) {
                if ($f->getId() === $fieldId) {
                    $index = $i;
                    break;
                }
            }
            if ($index === null) {
                return;
            }

            $swapWith = $index + $direction;
            if ($swapWith < 0 || $swapWith >= count($fields)) {
                return;
            }

            // меняем position местами
            $a = $fields[$index];
            $b = $fields[$swapWith];

            $posA = $a->getPosition();
            $a->setPosition($b->getPosition());
            $b->setPosition($posA);

            $this->em->flush();
        });
    }
}
