<?php

declare(strict_types=1);

namespace App\Service\CustomId;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\InventoryIdFormatPart;
use App\Repository\InventoryIdFormatPartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class InventoryIdFormatService
{
    public function __construct(
        private InventoryIdFormatPartRepository $repository,
        private EntityManagerInterface $em,
    ) {}

    public function updateFromRequest(int $inventoryId, Request $request): void
    {
        $parts = $request->request->all('parts');

        Assert::isArray($parts);
        Assert::maxCount($parts, 10, 'Too many format parts');

        $this->em->wrapInTransaction(function () use ($inventoryId, $parts) {

            // удаляем старые
            $this->repository->deleteByInventory($inventoryId);

            foreach ($parts as $position => $data) {

                Assert::keyExists($data, 'type');
                Assert::oneOf($data['type'], InventoryIdPartType::values());

                $part = new InventoryIdFormatPart();
                $part->setInventoryId($inventoryId);
                $part->setPosition($position);
                $part->setType($data['type']);
                $part->setParam1($data['param1'] ?? null);
                $part->setParam2($data['param2'] ?? null);

                $this->em->persist($part);
            }
        });
    }
}
