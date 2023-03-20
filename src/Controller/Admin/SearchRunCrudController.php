<?php

namespace App\Controller\Admin;

use App\Admin\Field\SuccessField;
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
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('runAt');
        yield SuccessField::new('isSuccess', 'Success');
        yield TextareaField::new('errorMessage')->hideOnIndex();
        yield TextareaField::new('errorMessage')->hideOnDetail()->setMaxLength(30);
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
