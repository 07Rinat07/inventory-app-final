<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryCustomFieldController extends AbstractController
{
    #[Route('/inventories/{id}/fields', name: 'inventory_fields', methods: ['GET'])]
    public function index(
        Inventory $inventory,
        CustomFieldRepository $repository,
    ): Response {
        $this->denyAccessUnlessGranted(
            'INVENTORY_MANAGE_FIELDS',
            $inventory
        );

        return $this->render('inventory/fields/index.html.twig', [
            'inventory' => $inventory,
            'fields' => $repository->findBy(
                ['inventory' => $inventory],
                ['position' => 'ASC']
            ),
        ]);
    }
}
