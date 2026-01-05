<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\Inventory;
use App\Entity\User;
use App\Service\Inventory\InventoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Основной контроллер для управления инвентарями через веб-интерфейс.
 */
#[Route('/inventories')]
#[IsGranted('ROLE_USER')] // Доступ только аутентифицированным пользователям
final class InventoryController extends AbstractController
{
    /**
     * Отображает список инвентарей, доступных текущему пользователю.
     *
     * @param InventoryService $service Сервис для работы с инвентарями.
     * @return Response Ответ с отрендеренным списком инвентарей.
     */
    #[Route('', name: 'inventory_index', methods: ['GET'])]
    public function index(InventoryService $service): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Authentication required.');
        }

        return $this->render('inventory/index.html.twig', [
            'inventories' => $service->getInventoriesForUser($user),
        ]);
    }

    /**
     * Обрабатывает создание нового инвентаря (отображение формы и сохранение).
     *
     * @param Request $request Объект запроса.
     * @param InventoryService $service Сервис для работы с инвентарями.
     * @return Response Ответ с формой или редирект при успехе.
     */
    #[Route('/create', name: 'inventory_create', methods: ['GET', 'POST'])]
    public function create(Request $request, InventoryService $service): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Authentication required.');
        }

        $name = '';
        $isPublic = false;

        if ($request->isMethod('POST')) {
            // Symfony 6.4: безопасные геттеры InputBag
            $name = trim($request->request->getString('name', ''));
            $isPublic = $request->request->getBoolean('is_public', false);

            if ($name === '') {
                $this->addFlash('danger', 'Name is required.');

                return $this->render('inventory/create.html.twig', [
                    'name' => $name,
                    'is_public' => $isPublic,
                ], new Response(status: 422));
            }

            $service->create($user, $name, $isPublic);
            $this->addFlash('success', 'Inventory created.');

            return $this->redirectToRoute('inventory_index');
        }

        return $this->render('inventory/create.html.twig', [
            'name' => $name,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Отображает детали конкретного инвентаря.
     *
     * Requirements ограничивает id цифрами, чтобы маршрут /create не конфликтовал с /{id}.
     *
     * @param Inventory $inventory Объект инвентаря (автоматическое преобразование id).
     * @return Response Ответ с деталями инвентаря.
     */
    #[Route('/{id}', name: 'inventory_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Inventory $inventory): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_VIEW', $inventory);

        return $this->render('inventory/show.html.twig', [
            'inventory' => $inventory,
        ]);
    }

    /**
     * Обрабатывает редактирование основных данных инвентаря.
     *
     * @param Request $request Объект запроса.
     * @param Inventory $inventory Объект инвентаря.
     * @param InventoryService $service Сервис для работы с инвентарями.
     * @return Response Ответ с формой или редирект при успехе.
     */
    #[Route('/{id}/edit', name: 'inventory_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Inventory $inventory, InventoryService $service): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_EDIT', $inventory);

        $name = $inventory->getName();
        $isPublic = $inventory->isPublic();

        if ($request->isMethod('POST')) {
            $name = trim($request->request->getString('name', ''));
            $isPublic = $request->request->getBoolean('is_public', false);

            if ($name === '') {
                $this->addFlash('danger', 'Name is required.');

                return $this->render('inventory/edit.html.twig', [
                    'inventory' => $inventory,
                    'name' => $name,
                    'is_public' => $isPublic,
                ], new Response(status: 422));
            }

            $service->update($inventory, $name, $isPublic);
            $this->addFlash('success', 'Inventory updated.');

            return $this->redirectToRoute('inventory_show', ['id' => $inventory->getId()]);
        }

        return $this->render('inventory/edit.html.twig', [
            'inventory' => $inventory,
            'name' => $name,
            'is_public' => $isPublic,
        ]);
    }

    /**
     * Обрабатывает удаление инвентаря.
     * Требует валидный CSRF-токен.
     *
     * @param Request $request Объект запроса.
     * @param Inventory $inventory Объект инвентаря.
     * @param InventoryService $service Сервис для работы с инвентарями.
     * @return Response Редирект на список инвентарей.
     */
    #[Route('/{id}/delete', name: 'inventory_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Inventory $inventory, InventoryService $service): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_DELETE', $inventory);

        /**
         * CSRF защита:
         * - в шаблоне: <input type="hidden" name="_token" value="{{ csrf_token('inventory_delete_' ~ inventory.id) }}">
         */
        $token = $request->request->getString('_token', '');
        if (!$this->isCsrfTokenValid('inventory_delete_' . $inventory->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $service->delete($inventory);
        $this->addFlash('success', 'Inventory deleted.');

        return $this->redirectToRoute('inventory_index');
    }
}
