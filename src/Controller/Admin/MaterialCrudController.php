<?php

namespace App\Controller\Admin;

use App\Admin\Field\MaterialTypeField;
use App\Admin\Field\TextMonospaceField;
use App\Entity\Material;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

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
            ->setSearchFields(['id', 'titleFull', 'creatorFiltered', 'creator', 'creatorAut', 'creatorCre', 'contributor', 'contributorAct', 'contributorAut', 'contributorCtb', 'contributorDkfig', 'abstract', 'pid', 'publisher', 'uri', 'coverUrl', 'type'])
            ->showEntityActionsInlined();
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
        yield IdField::new('id')->hideOnForm();
        yield AssociationField::new('categories')->hideOnIndex();
        yield TextField::new('titleFull');
        yield TextField::new('creatorFiltered')
            ->setHelp('The first non null value from "Creator", "CreatorAut", "CreatorCre", "Contributor", "ContributorAct", "ContributorAut", "ContributorCtb", "ContributorDkfig" and "Publisher" in that order');
        yield TextField::new('creator')->hideOnIndex();
        yield TextField::new('creatorAut', 'CreatorAut')->hideOnIndex();
        yield TextField::new('creatorCre', 'CreatorCre')->hideOnIndex();
        yield TextField::new('contributor')->hideOnIndex();
        yield TextField::new('contributorAct', 'ContributorAct')->hideOnIndex();
        yield TextField::new('contributorAut', 'ContributorAut')->hideOnIndex();
        yield TextField::new('contributorCtb', 'ContributorCtb')->hideOnIndex();
        yield TextField::new('contributorDkfig', 'ContributorDkfig')->hideOnIndex();
        yield TextareaField::new('abstract')->hideOnIndex();
        yield TextMonospaceField::new('pid');
        yield TextField::new('publisher')->hideOnIndex();
        yield DateField::new('date');
        yield UrlField::new('uri')->hideOnIndex();
        yield ImageField::new('coverUrl', 'Cover')->hideOnIndex();
        yield UrlField::new('coverUrl')->hideOnIndex();
        yield MaterialTypeField::new('type');
        yield AssociationField::new('categories', 'Cat.')->hideOnDetail();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('titleFull')
            ->add('creatorFiltered')
            ->add('pid')
            ->add('date')
            ->add('type')
            ->add('categories')
            ->add('coverUrl')
        ;
    }
}
