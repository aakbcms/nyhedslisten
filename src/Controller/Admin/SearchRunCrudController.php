<?php

namespace App\Controller\Admin;

use App\Entity\SearchRun;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class SearchRunCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SearchRun::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('SearchRun')
            ->setEntityLabelInPlural('SearchRun')
            ->setSearchFields(['id', 'errorMessage']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'edit', 'delete');
    }

    public function configureFields(string $pageName): iterable
    {
        $runAt = DateTimeField::new('runAt');
        $isSuccess = BooleanField::new('isSuccess');
        $errorMessage = TextareaField::new('errorMessage');
        $category = AssociationField::new('category');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$runAt, $isSuccess, $errorMessage];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$runAt, $isSuccess, $errorMessage];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$runAt, $isSuccess, $errorMessage, $category];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$runAt, $isSuccess, $errorMessage, $category];
        }
    }
}
