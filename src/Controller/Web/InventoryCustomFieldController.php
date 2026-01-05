<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Inventory;
use App\Security\Voter\InventoryVoter;
use App\Service\CustomField\ReorderCustomFieldsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для управления порядком кастомных полей инвентаря.
 */
final class InventoryCustomFieldController extends AbstractController
{
    /**
     * Обрабатывает AJAX запрос на изменение порядка полей.
     *
     * @param Inventory $inventory Объект инвентаря.
     * @param Request $request Объект запроса с JSON-данными.
     * @param ReorderCustomFieldsService $service Сервис для переупорядочивания полей.
     * @return JsonResponse Результат операции в формате JSON.
     */
    #[Route(
        '/inventory/{id}/custom-fields/reorder',
        name: 'inventory_custom_fields_reorder',
        methods: ['POST']
    )]
    public function reorder(
        Inventory $inventory,
        Request $request,
        ReorderCustomFieldsService $service,
    ): JsonResponse {
        $this->denyAccessUnlessGranted(
            InventoryVoter::MANAGE_FIELDS,
            $inventory
        );

        $payload = json_decode($request->getContent(), true);

        if (!isset($payload['order']) || !is_array($payload['order'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $service->reorder($payload['order']);

        return new JsonResponse(['status' => 'ok']);
    }
}

