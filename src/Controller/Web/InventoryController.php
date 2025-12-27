<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Inventory;
use App\Service\Inventory\InventoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/inventories')]
final class InventoryController extends AbstractController
{
    #[Route('', name: 'inventory_index')]
    public function index(InventoryService $service): Response
    {
        $inventories = $service->getInventoriesForUser($this->getUser());

        return $this->render('inventory/index.html.twig', [
            'inventories' => $inventories,
        ]);
    }

    #[Route('/create', name: 'inventory_create', methods: ['GET', 'POST'])]
    public function create(Request $request, InventoryService $service): Response
    {
        if ($request->isMethod('POST')) {
            $inventory = $service->create(
                $this->getUser(),
                $request->request->get('name'),
                (bool) $request->request->get('is_public')
            );

            return $this->redirectToRoute('inventory_index');
        }

        return $this->render('inventory/create.html.twig');
    }
}
