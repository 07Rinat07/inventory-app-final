<?php

declare(strict_types=1);

namespace App\Service\CustomField;

use App\Entity\CustomField;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ReorderCustomFieldsService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CustomFieldRepository $repository,
    ) {}

    /**
     * @param int[] $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        $this->em->wrapInTransaction(function () use ($orderedIds) {
            foreach ($orderedIds as $position => $fieldId) {
                $field = $this->repository->find($fieldId);

                if (!$field instanceof CustomField) {
                    continue; // или throw — по желанию
                }

                $field->setPosition($position);
            }

            $this->em->flush();
        });
    }
}
