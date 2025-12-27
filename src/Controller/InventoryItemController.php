<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Repository\CustomFieldRepository;
use App\Repository\InventoryItemValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryItemController extends AbstractController
{
    #[Route(
        '/inventory/{inventoryId}/items/{itemId}/edit',
        name: 'inventory_item_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        int $inventoryId,
        int $itemId,
        Request $request,
        CustomFieldRepository $fieldRepository,
        InventoryItemValueRepository $valueRepository,
        EntityManagerInterface $em,
    ): Response {
        $inventory = $em->getRepository(Inventory::class)->find($inventoryId);
        $item = $em->getRepository(InventoryItem::class)->find($itemId);

        if (!$inventory || !$item || $item->getInventory()->getId() !== $inventory->getId()) {
            throw $this->createNotFoundException();
        }

        // security
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        // Custom fields
        $fields = $fieldRepository->findByInventoryOrdered($inventory);

        // Existing values
        $values = $valueRepository->findIndexedByField($item);

        // ---------- GET ----------
        if ($request->isMethod('GET')) {
            return $this->render('inventory_item/edit.html.twig', [
                'inventory' => $inventory,
                'item' => $item,
                'fields' => $fields,
                'values' => $values,
            ]);
        }

        // ---------- POST ----------
        $connection = $em->getConnection();
        $connection->beginTransaction();

        try {
            // optimistic locking
            $expectedVersion = (int) $request->request->get('version');
            $em->lock($item, \Doctrine\DBAL\LockMode::OPTIMISTIC, $expectedVersion);

            foreach ($fields as $field) {
                $rawValue = $request->request->get('field_' . $field->getId());

                $valueRepository->setValue(
                    item: $item,
                    field: $field,
                    rawValue: $rawValue
                );
            }

            $em->flush();
            $connection->commit();

            return $this->redirectToRoute(
                'inventory_show',
                ['id' => $inventory->getId()]
            );
        } catch (OptimisticLockException $e) {
            $connection->rollBack();

            return $this->render('inventory_item/conflict.html.twig', [
                'inventory' => $inventory,
                'item' => $item,
            ]);
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
