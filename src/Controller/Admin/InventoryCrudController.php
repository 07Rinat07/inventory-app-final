<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class InventoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Inventory::class;
    }

    /**
     * READ-ONLY + moderation
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // запрещаем создание и удаление
            ->disable(Action::NEW, Action::DELETE)

            // ✔️ разрешаем просмотр
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Inventory')
            ->setEntityLabelInPlural('Inventories')
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            TextField::new('name', 'Name'),

            AssociationField::new('owner', 'Owner')
                ->onlyOnIndex(),

            BooleanField::new('isPublic', 'Public'),

            // optimistic lock — readonly
            IdField::new('version')
                ->onlyOnDetail()
                ->setLabel('Version'),
        ];
    }
}
