<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Repository\CustomFieldRepository;
use App\Repository\InventoryItemValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryItemController extends AbstractController
{
    #[Route(
        '/inventory/{id}/items/{item}/edit',
        name: 'inventory_item_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        Inventory $inventory,          // <- {id} автоматически подставится как Inventory по id
        InventoryItem $item,           // <- {item} автоматически подставится как InventoryItem по id
        Request $request,
        CustomFieldRepository $fieldRepository,
        InventoryItemValueRepository $valueRepository,
        EntityManagerInterface $em,
    ): Response {
        // 1) Права (как и в new)
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        // 2) Защита от "подмены URL":
        // item должен принадлежать именно этому inventory
        if ($item->getInventory()->getId() !== $inventory->getId()) {
            throw $this->createNotFoundException('Item does not belong to this inventory.');
        }

        // 3) Дальше твоя логика edit (GET форма / POST сохранение)
        // ...
        return new Response('TODO edit'); // заглушка, если еще не реализовал
    }
}
