<?php

namespace App\Controller\Admin;

use App\Entity\Material;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MaterialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Material::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Material')
            ->setEntityLabelInPlural('Material')
            ->setSearchFields(['id', 'titleFull', 'creatorFiltered', 'creator', 'creatorAut', 'creatorCre', 'contributor', 'contributorAct', 'contributorAut', 'contributorCtb', 'contributorDkfig', 'abstract', 'pid', 'publisher', 'uri', 'coverUrl', 'type']);
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
        yield TextField::new('titleFull');
        yield TextField::new('creatorFiltered');
        yield TextField::new('creator')->hideOnIndex();
        yield TextField::new('creatorAut', 'CreatorAut')->hideOnIndex();
        yield TextField::new('creatorCre', 'CreatorCre')->hideOnIndex();
        yield TextField::new('contributor')->hideOnIndex();
        yield TextField::new('contributorAct', 'ContributorAct')->hideOnIndex();
        yield TextField::new('contributorAut', 'ContributorAut')->hideOnIndex();
        yield TextField::new('contributorCtb', 'ContributorCtb')->hideOnIndex();
        yield TextField::new('contributorDkfig', 'ContributorDkfig')->hideOnIndex();
        yield TextareaField::new('abstract')->hideOnIndex();
        yield TextField::new('pid');
        yield TextField::new('publisher')->hideOnIndex();
        yield DateField::new('date');
        yield TextField::new('uri')->hideOnIndex();
        yield ImageField::new('coverUrl')->hideOnIndex();
        yield TextField::new('type');
        yield AssociationField::new('categories');
    }
}
