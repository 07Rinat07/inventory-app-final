<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CustomField;
use App\Entity\Inventory;
use App\Form\CustomFieldType;
use App\Repository\CustomFieldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventories/{id}/fields', name: 'inventory_custom_fields_')]
final class InventoryCustomFieldController extends AbstractController
{
    #[Route('/new', name: 'new')]
    public function new(
        Inventory $inventory,
        Request $request,
        EntityManagerInterface $em,
        CustomFieldRepository $repository
    ): Response {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        $field = new CustomField();
        $field->setInventory($inventory);
        $field->setPosition($repository->getNextPosition($inventory));

        $form = $this->createForm(CustomFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($field);
            $em->flush();

            return $this->redirectToRoute('inventory_custom_fields', [
                'id' => $inventory->getId(),
            ]);
        }

        return $this->render('custom_field/form.html.twig', [
            'form' => $form,
            'inventory' => $inventory,
        ]);
    }

    #[Route('/{fieldId}/edit', name: 'edit')]
    public function edit(
        Inventory $inventory,
        CustomField $field,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        $form = $this->createForm(CustomFieldType::class, $field);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('inventory_custom_fields', [
                'id' => $inventory->getId(),
            ]);
        }

        return $this->render('custom_field/form.html.twig', [
            'form' => $form,
            'inventory' => $inventory,
        ]);
    }

    #[Route('/{fieldId}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Inventory $inventory,
        CustomField $field,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('INVENTORY_MANAGE_FIELDS', $inventory);

        $em->remove($field);
        $em->flush();

        return $this->redirectToRoute('inventory_custom_fields', [
            'id' => $inventory->getId(),
        ]);
    }
}
