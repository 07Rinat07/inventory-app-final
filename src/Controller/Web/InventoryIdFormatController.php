<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Domain\ValueObject\InventoryIdPartType;
use App\Entity\Inventory;
use App\Service\CustomId\InventoryIdFormatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class InventoryIdFormatController extends AbstractController
{
    #[Route('/inventory/{id}/id-format', name: 'inventory_id_format_edit', methods: ['GET', 'POST'])]
    public function edit(
        Inventory $inventory,
        Request $request,
        InventoryIdFormatService $service,
    ): Response {
        // ACL: редактировать может только тот, кому разрешён EDIT (у тебя через Voter)
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        if ($request->isMethod('POST')) {
            /**
             * ВАЖНО (Symfony):
             * - InputBag::get() НЕ принимает массив как default.
             * - Для полей вида name="part_type[]" нужно читать через all('part_type').
             */
            $types  = $request->request->all('part_type'); // array
            $param1 = $request->request->all('param1');     // array
            $param2 = $request->request->all('param2');     // array

            $service->replaceFromForm($inventory, $types, $param1, $param2);

            $this->addFlash('success', 'Custom ID format saved.');

            // Redirect-After-Post
            return $this->redirectToRoute('inventory_id_format_edit', [
                'id' => $inventory->getId(),
            ]);
        }

        return $this->render('inventory/id_format/edit.html.twig', [
            'inventory' => $inventory,
            'parts' => $service->getPartsForEdit($inventory),
            'partTypes' => InventoryIdPartType::cases(),
        ]);
    }
}
