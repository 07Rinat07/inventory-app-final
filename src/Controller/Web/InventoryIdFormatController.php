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

/**
 * Настройка формата ID для предметов инвентаря.
 */
final class InventoryIdFormatController extends AbstractController
{
    /**
     * Показываем форму и сохраняем изменения в формате ID.
     */
    #[Route('/inventory/{id}/id-format', name: 'inventory_id_format_edit', methods: ['GET', 'POST'])]
    public function edit(
        Inventory $inventory,
        Request $request,
        InventoryIdFormatService $service,
    ): Response {
        // Редактировать может только хозяин или админ
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        if ($request->isMethod('POST')) {
            // Собираем данные из формы (массивы типов и параметров)
            $types  = $request->request->all('part_type');
            $param1 = $request->request->all('param1');
            $param2 = $request->request->all('param2');

            $service->replaceFromForm($inventory, $types, $param1, $param2);

            $this->addFlash('success', 'Формат ID успешно обновлен.');

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
