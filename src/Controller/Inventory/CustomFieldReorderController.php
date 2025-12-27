<?php

declare(strict_types=1);

namespace App\Controller\Inventory;

use App\Entity\Inventory;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class CustomFieldReorderController extends AbstractController
{
    #[Route(
        '/inventories/{id}/custom-fields/reorder',
        name: 'inventory_custom_fields_reorder',
        methods: ['POST']
    )]
    public function __invoke(
        Inventory $inventory,
        Request $request,
        CustomFieldRepository $customFieldRepository,
        EntityManagerInterface $em,
    ): JsonResponse {
        // SECURITY
        $this->denyAccessUnlessGranted(
            'INVENTORY_MANAGE_FIELDS',
            $inventory
        );

        $data = $request->toArray();

        if (!isset($data['order']) || !is_array($data['order'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $em->wrapInTransaction(function () use ($data, $inventory, $customFieldRepository) {
            foreach ($data['order'] as $position => $fieldId) {
                $field = $customFieldRepository->findOneBy([
                    'id' => $fieldId,
                    'inventory' => $inventory,
                ]);

                if ($field === null) {
                    continue;
                }

                $field->setPosition((int) $position);
            }
        });

        return new JsonResponse(['status' => 'ok']);
    }
}
