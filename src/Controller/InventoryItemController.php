<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Repository\CustomFieldRepository;
use App\Repository\InventoryItemValueRepository;
use App\Service\CustomId\InventoryItemIdGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryItemController extends AbstractController
{
    #[Route('/inventory/{id}/items/new', name: 'inventory_item_new')]
    public function new(
        Inventory $inventory,
        Request $request,
        CustomFieldRepository $fieldRepository,
        InventoryItemIdGenerator $idGenerator,
        InventoryItemValueRepository $valueRepository,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        $fields = $fieldRepository->findByInventoryOrdered($inventory);

        if ($request->isMethod('GET')) {
            return $this->render('inventory_item/new.html.twig', [
                'inventory' => $inventory,
                'fields' => $fields,
            ]);
        }

        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $item = new InventoryItem();
            $item->setInventory($inventory);

            $customId = $idGenerator->generate($inventory);
            $item->setCustomId($customId);

            $em->persist($item);

            foreach ($fields as $field) {
                $rawValue = $request->request->get('field_' . $field->getId());

                $valueRepository->setValue(
                    item: $item,
                    field: $field,
                    rawValue: $rawValue
                );
            }

            $em->flush();
            $conn->commit();

            return $this->redirectToRoute(
                'inventory_show',
                ['id' => $inventory->getId()]
            );
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}
