<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Category::class;
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
        yield CodeEditorField::new('cqlSearch', 'CQL search')->hideOnIndex();
        yield DateTimeField::new('lastSearchRunAt', 'Last run')->hideOnForm();
        yield BooleanField::new('lastSearchRunSuccess', 'Succes')->renderAsSwitch(false)->hideOnForm();
    }
}
