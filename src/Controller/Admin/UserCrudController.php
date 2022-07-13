<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('User')
            ->setSearchFields(['username', 'usernameCanonical', 'email', 'emailCanonical', 'confirmationToken', 'roles', 'id', 'name', 'createdBy', 'updatedBy']);
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $username = TextField::new('username');
        $email = TextField::new('email');
        $enabled = Field::new('enabled');
        $plainPassword = Field::new('plainPassword', 'password');
        $roles = ArrayField::new('roles');
        $lastLogin = DateTimeField::new('lastLogin');
        $createdAt = DateTimeField::new('createdAt');
        $createdBy = TextField::new('createdBy');
        $updatedAt = DateTimeField::new('updatedAt');
        $updatedBy = TextField::new('updatedBy');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$username, $email, $enabled, $roles];
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            return [$name, $username, $email, $enabled, $lastLogin, $roles, $createdAt, $createdBy, $updatedAt, $updatedBy];
        } elseif (Crud::PAGE_NEW === $pageName) {
            return [$name, $username, $email, $enabled, $plainPassword, $roles];
        } elseif (Crud::PAGE_EDIT === $pageName) {
            return [$name, $username, $email, $enabled, $plainPassword, $roles];
        }
    }
}
