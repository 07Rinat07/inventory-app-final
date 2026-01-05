<?php

declare(strict_types=1);

namespace App\Service\CustomField;

use App\Entity\CustomField;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Сервис для изменения порядка кастомных полей.
 */
final class ReorderCustomFieldsService
{
    /**
     * Создает новый экземпляр сервиса.
     */
    public function __construct(
        private EntityManagerInterface $em,
        private CustomFieldRepository $repository,
    ) {}

    /**
     * Обновляет позиции полей на основе переданного списка ID.
     *
     * @param int[] $orderedIds Массив идентификаторов полей в нужном порядке.
     */
    public function reorder(array $orderedIds): void
    {
        $this->em->wrapInTransaction(function () use ($orderedIds) {
            foreach ($orderedIds as $position => $fieldId) {
                $field = $this->repository->find($fieldId);

                if (!$field instanceof CustomField) {
                    continue;
                }

                $field->setPosition($position);
            }

            $this->em->flush();
        });
    }
}
