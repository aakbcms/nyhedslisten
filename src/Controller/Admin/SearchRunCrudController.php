<?php

namespace App\Controller\Admin;

use App\Admin\Field\SuccesField;
use App\Entity\SearchRun;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
            ->setDefaultSort(['runAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->setEntityLabelInSingular('SearchRun')
            ->setEntityLabelInPlural('SearchRun')
            ->setSearchFields(['id', 'errorMessage']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('runAt');
        yield SuccesField::new('isSuccess');
        yield TextareaField::new('errorMessage');
        yield AssociationField::new('category');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('runAt')
            ->add('isSuccess')
            ->add('errorMessage')
            ->add('category')
        ;
    }
}
