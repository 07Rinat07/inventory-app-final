<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use App\Service\CustomField\CustomFieldService;
use App\Security\Voter\InventoryVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для управления кастомными полями через веб-интерфейс.
 */
#[Route('/inventory/{id}/fields')]
final class CustomFieldController extends AbstractController
{
    /**
     * Отображает список кастомных полей.
     */
    #[Route('', name: 'custom_field_index', methods: ['GET'])]
    public function index(
        Inventory $inventory,
        CustomFieldRepository $repo
    ): Response {
        $this->denyAccessUnlessGranted(InventoryVoter::MANAGE_FIELDS, $inventory);

        return $this->render('custom_field/index.html.twig', [
            'inventory' => $inventory,
            'fields' => $repo->findByInventoryOrdered($inventory),
        ]);
    }

    /**
     * Создает новое кастомное поле.
     */
    #[Route('/create', name: 'custom_field_create', methods: ['POST'])]
    public function create(
        Inventory $inventory,
        Request $request,
        CustomFieldService $service
    ): Response {
        $this->denyAccessUnlessGranted(InventoryVoter::MANAGE_FIELDS, $inventory);

        $service->create(
            $inventory,
            (string) $request->request->get('label'),
            (string) $request->request->get('type')
        );

        return $this->redirectToRoute('custom_field_index', ['id' => $inventory->getId()]);
    }

    /**
     * Массовые действия над кастомными полями (удаление, видимость).
     */
    #[Route('/bulk', name: 'custom_field_bulk', methods: ['POST'])]
    public function bulk(
        Inventory $inventory,
        Request $request,
        CustomFieldService $service
    ): Response {
        $this->denyAccessUnlessGranted(InventoryVoter::MANAGE_FIELDS, $inventory);

        $action = (string) $request->request->get('action');
        $ids = array_map('intval', (array) $request->request->all('ids'));

        match ($action) {
            'delete' => $service->deleteBulk($inventory, $ids),
            'show'   => $service->setVisibilityBulk($inventory, $ids, true),
            'hide'   => $service->setVisibilityBulk($inventory, $ids, false),
            default  => null,
        };

        return $this->redirectToRoute('custom_field_index', ['id' => $inventory->getId()]);
    }

    /**
     * Перемещает кастомное поле вверх или вниз.
     */
    #[Route('/move', name: 'custom_field_move', methods: ['POST'])]
    public function move(
        Inventory $inventory,
        Request $request,
        CustomFieldService $service
    ): Response {
        $this->denyAccessUnlessGranted(InventoryVoter::MANAGE_FIELDS, $inventory);

        $fieldId = (int) $request->request->get('field_id');
        $direction = (int) $request->request->get('direction'); // -1 или +1

        $service->move($inventory, $fieldId, $direction);

        return $this->redirectToRoute('custom_field_index', ['id' => $inventory->getId()]);
    }
}
