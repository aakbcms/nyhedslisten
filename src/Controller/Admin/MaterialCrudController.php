<?php

namespace App\Controller\Admin;

use App\Entity\Material;
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
            ->disable('new', 'edit', 'delete');
    }

    public function configureFields(string $pageName): iterable
    {
        $titleFull = TextField::new('titleFull');
        $creatorFiltered = TextField::new('creatorFiltered');
        $creator = TextField::new('creator');
        $creatorAut = TextField::new('creatorAut', 'CreatorAut');
        $creatorCre = TextField::new('creatorCre', 'CreatorCre');
        $contributor = TextField::new('contributor');
        $contributorAct = TextField::new('contributorAct', 'ContributorAct');
        $contributorAut = TextField::new('contributorAut', 'ContributorAut');
        $contributorCtb = TextField::new('contributorCtb', 'ContributorCtb');
        $contributorDkfig = TextField::new('contributorDkfig', 'ContributorDkfig');
        $abstract = TextareaField::new('abstract');
        $pid = TextField::new('pid');
        $publisher = TextField::new('publisher');
        $date = DateField::new('date');
        $uri = TextField::new('uri');
        $coverUrl = ImageField::new('coverUrl');
        $type = TextField::new('type');
        $categories = AssociationField::new('categories');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$titleFull, $creatorFiltered, $pid, $type, $date, $categories];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$titleFull, $creatorFiltered, $creator, $creatorAut, $creatorCre, $contributor, $contributorAct, $contributorAut, $contributorCtb, $contributorDkfig, $abstract, $type, $publisher, $pid, $date, $coverUrl, $categories];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$titleFull, $creatorFiltered, $creator, $creatorAut, $creatorCre, $contributor, $contributorAct, $contributorAut, $contributorCtb, $contributorDkfig, $abstract, $pid, $publisher, $date, $uri, $coverUrl, $type, $categories];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$titleFull, $creatorFiltered, $creator, $creatorAut, $creatorCre, $contributor, $contributorAct, $contributorAut, $contributorCtb, $contributorDkfig, $abstract, $pid, $publisher, $date, $uri, $coverUrl, $type, $categories];
        }
    }
}
