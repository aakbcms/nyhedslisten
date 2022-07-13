<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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
            ->setEntityLabelInSingular('Category')
            ->setEntityLabelInPlural('Category')
            ->setSearchFields(['id', 'name', 'cqlSearch', 'createdBy', 'updatedBy']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $cqlSearch = TextareaField::new('cqlSearch', 'CQL search');
        $category = TextareaField::new('category');
        $searchRuns = Field::new('searchRuns')->setTemplatePath('@EasyAdminExtension/default/field_embedded_list.html.twig');
        $createdAt = DateTimeField::new('createdAt');
        $createdBy = TextField::new('createdBy');
        $updatedAt = DateTimeField::new('updatedAt');
        $updatedBy = TextField::new('updatedBy');
        $lastSearchRunAt = DateTimeField::new('lastSearchRunAt');
        $lastSearchRunSuccess = BooleanField::new('lastSearchRunSuccess');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$name, $cqlSearch, $lastSearchRunAt, $lastSearchRunSuccess];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$name, $cqlSearch, $category, $searchRuns, $createdAt, $createdBy, $updatedAt, $updatedBy];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $cqlSearch];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $cqlSearch];
        }
    }
}
