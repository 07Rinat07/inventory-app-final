<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\CustomField\CustomFieldType;
use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Контроллер для управления кастомными полями инвентаря.
 */
#[Route('/inventories/{id<\d+>}/fields', name: 'inventory_custom_fields_')]
final class InventoryCustomFieldController extends AbstractController
{
    /**
     * Ограничение из требований менторов: до 3 кастомных полей каждого типа.
     * (Если поменяется требование — менять только эту константу.)
     */
    private const LIMIT_PER_TYPE = 3;

    /**
     * Отображает список кастомных полей инвентаря.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Inventory $inventory, CustomFieldRepository $repository): Response
    {
        // Управление полями — только тем, кому разрешил Voter
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        return $this->render('inventory/fields/index.html.twig', [
            'inventory' => $inventory,
            'fields' => $repository->findByInventoryOrdered($inventory),
            'types' => CustomFieldType::cases(),
        ]);
    }

    /**
     * Создает новое кастомное поле.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Inventory $inventory, Request $request, CustomFieldRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        // Значения по умолчанию для формы (GET и повторный рендер при ошибке)
        $selectedType = null;
        $isRequired = false;

        if ($request->isMethod('POST')) {
            // Symfony 6.4: безопасные геттеры (InputBag)
            $rawType = trim($request->request->getString('type', ''));
            $selectedType = $rawType;
            $isRequired = $request->request->getBoolean('is_required', false);

            // 1) Валидация типа
            $type = CustomFieldType::tryFrom($rawType);
            if ($type === null) {
                $this->addFlash('danger', 'Invalid field type.');

                return $this->render('inventory/fields/new.html.twig', [
                    'inventory' => $inventory,
                    'types' => CustomFieldType::cases(),
                    'selectedType' => $selectedType,
                    'isRequired' => $isRequired,
                ], new Response(status: 422));
            }

            // 2) Лимит “до 3 каждого типа”
            $countOfThisType = $repository->countByInventoryAndType($inventory, $type);
            if ($countOfThisType >= self::LIMIT_PER_TYPE) {
                $this->addFlash('danger', sprintf(
                    'Type limit reached: max %d fields of type "%s" per inventory.',
                    self::LIMIT_PER_TYPE,
                    $type->label()
                ));

                return $this->render('inventory/fields/new.html.twig', [
                    'inventory' => $inventory,
                    'types' => CustomFieldType::cases(),
                    'selectedType' => $selectedType,
                    'isRequired' => $isRequired,
                ], new Response(status: 422));
            }

            // 3) Позиция = max(position)+1, чтобы порядок всегда был корректный
            $position = $repository->getNextPosition($inventory);

            // 4) Создаём сущность сразу в валидном состоянии (как задумано в Entity)
            $field = new CustomField($inventory, $type, $position);
            $field->setIsRequired($isRequired);

            // 5) Сохраняем
            $repository->save($field, flush: true);

            $this->addFlash('success', 'Custom field created.');
            return $this->redirectToRoute('inventory_custom_fields_index', ['id' => $inventory->getId()]);
        }

        // GET
        return $this->render('inventory/fields/new.html.twig', [
            'inventory' => $inventory,
            'types' => CustomFieldType::cases(),
            'selectedType' => $selectedType,
            'isRequired' => $isRequired,
        ]);
    }

    /**
     * Редактирует кастомное поле.
     */
    #[Route('/{fieldId<\d+>}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Inventory $inventory, int $fieldId, Request $request, CustomFieldRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        // Жёстко проверяем, что поле принадлежит этому inventory
        $field = $repository->findOneBy(['id' => $fieldId, 'inventory' => $inventory]);
        if (!$field instanceof CustomField) {
            throw $this->createNotFoundException('Custom field not found.');
        }

        if ($request->isMethod('POST')) {
            $rawType = trim($request->request->getString('type', ''));
            $isRequired = $request->request->getBoolean('is_required', false);

            // 1) Валидация типа
            $type = CustomFieldType::tryFrom($rawType);
            if ($type === null) {
                $this->addFlash('danger', 'Invalid field type.');

                return $this->render('inventory/fields/edit.html.twig', [
                    'inventory' => $inventory,
                    'field' => $field,
                    'types' => CustomFieldType::cases(),
                    'selectedType' => $rawType,
                    'isRequired' => $isRequired,
                ], new Response(status: 422));
            }

            // 2) Лимит “до 3 каждого типа” — важный нюанс для edit:
            //    - если тип НЕ меняем, лимит не проверяем (иначе сам себя заблокируешь)
            //    - если тип меняем, проверяем лимит для нового типа
            $currentType = $field->getType();
            if ($type->value !== $currentType->value) {
                $countOfNewType = $repository->countByInventoryAndType($inventory, $type);
                if ($countOfNewType >= self::LIMIT_PER_TYPE) {
                    $this->addFlash('danger', sprintf(
                        'Type limit reached: max %d fields of type "%s" per inventory.',
                        self::LIMIT_PER_TYPE,
                        $type->label()
                    ));

                    return $this->render('inventory/fields/edit.html.twig', [
                        'inventory' => $inventory,
                        'field' => $field,
                        'types' => CustomFieldType::cases(),
                        'selectedType' => $rawType,
                        'isRequired' => $isRequired,
                    ], new Response(status: 422));
                }
            }

            // 3) Применяем изменения
            $field->setType($type);
            $field->setIsRequired($isRequired);

            // 4) Сохраняем
            $repository->save($field, flush: true);

            $this->addFlash('success', 'Custom field updated.');
            return $this->redirectToRoute('inventory_custom_fields_index', ['id' => $inventory->getId()]);
        }

        // GET
        return $this->render('inventory/fields/edit.html.twig', [
            'inventory' => $inventory,
            'field' => $field,
            'types' => CustomFieldType::cases(),
            'selectedType' => $field->getType()->value,
            'isRequired' => $field->isRequired(),
        ]);
    }

    /**
     * Удаляет кастомное поле.
     */
    #[Route('/{fieldId<\d+>}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Inventory $inventory, int $fieldId, Request $request, CustomFieldRepository $repository): Response
    {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        $field = $repository->findOneBy(['id' => $fieldId, 'inventory' => $inventory]);
        if (!$field instanceof CustomField) {
            throw $this->createNotFoundException('Custom field not found.');
        }

        /**
         * CSRF:
         * - В шаблоне: <input type="hidden" name="_token" value="{{ csrf_token('custom_field_delete_' ~ f.id) }}">
         * - Здесь: tokenId = custom_field_delete_{fieldId}
         */
        $token = $request->request->getString('_token', '');
        if (!$this->isCsrfTokenValid('custom_field_delete_' . $field->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $repository->remove($field, flush: true);

        $this->addFlash('success', 'Custom field deleted.');
        return $this->redirectToRoute('inventory_custom_fields_index', ['id' => $inventory->getId()]);
    }
}
