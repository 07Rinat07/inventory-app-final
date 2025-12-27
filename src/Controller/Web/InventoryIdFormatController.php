<?php

namespace App\Controller\Web;

use App\Service\CustomId\InventoryIdFormatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryIdFormatController extends AbstractController
{
    #[Route('/inventory/{id}/id-format', name: 'inventory_id_format_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        InventoryIdFormatService $service
    ): Response {
        $dto = $service->getFormatForEdit($id);

        if ($request->isMethod('POST')) {
            $service->updateFromRequest($id, $request);
            return $this->redirectToRoute('inventory_id_format_edit', ['id' => $id]);
        }

        return $this->render('inventory/id_format/edit.html.twig', [
            'inventoryId' => $id,
            'format' => $dto,
        ]);
    }
}
