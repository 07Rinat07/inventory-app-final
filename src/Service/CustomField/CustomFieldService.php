<?php

declare(strict_types=1);

namespace App\Service\CustomField;

use App\Domain\ValueObject\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * Сервис для управления доп. полями (custom fields).
 */
final class CustomFieldService
{
    public function __construct(
        private CustomFieldRepository $repository,
        private EntityManagerInterface $em,
    ) {}

    /**
     * Создаём новое кастомное поле.
     * Есть правило: максимум 3 поля каждого типа на один инвентарь.
     *
     * @throws \InvalidArgumentException Если данные кривые или превышен лимит.
     */
    public function create(Inventory $inventory, string $label, string $type): CustomField
    {
        Assert::stringNotEmpty($label, 'Label is required');
        Assert::maxLength($label, 100, 'Label is too long');
        Assert::oneOf($type, CustomFieldType::values(), 'Invalid field type');

        // Лимит: не больше 3-х штук каждого типа
        $count = $this->repository->countByInventoryAndType($inventory, CustomFieldType::from($type));
        Assert::lessThanEq($count, 2, 'Лимит превышен: максимум 3 поля одного типа.');

        $field = new CustomField($inventory, CustomFieldType::from($type), $this->repository->getNextPosition($inventory));
        $field->setIsRequired(false);

        $this->em->persist($field);
        $this->em->flush();

        return $field;
    }

    /**
     * Массово меняем видимость полей.
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
                if (method_exists($f, 'setIsVisible')) {
                    $f->setIsVisible($visible);
                }
            }

            $this->em->flush();
        });
    }

    /**
     * Удаляем сразу несколько полей.
     */
    public function deleteBulk(Inventory $inventory, array $ids): int
    {
        Assert::allInteger($ids);

        return $this->em->wrapInTransaction(function () use ($inventory, $ids) {
            return $this->repository->deleteByIds($inventory, $ids);
        });
    }

    /**
     * Двигаем поле вверх или вниз по списку.
     * $direction: -1 (вверх) или 1 (вниз).
     */
    public function move(Inventory $inventory, int $fieldId, int $direction): void
    {
        Assert::oneOf($direction, [-1, 1]);

        $this->em->wrapInTransaction(function () use ($inventory, $fieldId, $direction) {
            $fields = $this->repository->findByInventoryOrdered($inventory);

            // Ищем, на какой позиции сейчас наше поле
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

            // Вычисляем соседа, с которым надо поменяться
            $swapWith = $index + $direction;
            if ($swapWith < 0 || $swapWith >= count($fields)) {
                return;
            }

            // Меняем position местами
            $a = $fields[$index];
            $b = $fields[$swapWith];

            $posA = $a->getPosition();
            $a->setPosition($b->getPosition());
            $b->setPosition($posA);

            $this->em->flush();
        });
    }
}
