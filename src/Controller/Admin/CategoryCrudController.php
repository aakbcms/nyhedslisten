<?php

namespace App\Controller\Admin;

use App\Admin\Field\SuccesField;
use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('Category')
            ->setEntityLabelInPlural('Category')
            ->setSearchFields(['name', 'cqlSearch', 'createdBy', 'updatedBy']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield CodeEditorField::new('cqlSearch', 'CQL search')
            ->hideOnIndex()
            ->setHelp('Do not include "holdingsitem.accessionDate" ("bad") or "facet.acSource" parameters in the CQL statement. These will be added automatically.');
        yield DateTimeField::new('lastSearchRunAt', 'Last run')->hideOnForm();
        yield SuccesField::new('lastSearchRunSuccess', 'Succes')->hideOnForm();
        yield AssociationField::new('searchRuns')->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name');
    }
}
