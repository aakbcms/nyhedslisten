<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Class AdminController.
 *
 * Used to integrate FOSUserBundle and EasyAdminBundle
 * https://symfony.com/doc/master/bundles/EasyAdminBundle/integration/fosuserbundle.html
 */
class AdminController extends EasyAdminController
{
    private $userManager;

    /**
     * AdminController constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Create new User entity.
     *
     * EasyAdmin custom action to integrate FOSUserBundle and EasyAdminBundle
     *
     * @return UserInterface
     */
    public function createNewUserEntity(): UserInterface
    {
        return $this->userManager->createUser();
    }

    /**
     * Persist User entity.
     *
     * EasyAdmin custom action to integrate FOSUserBundle and EasyAdminBundle
     *
     * @param $user
     */
    public function persistUserEntity(User $user): void
    {
        $this->userManager->updateUser($user, false);
        parent::persistEntity($user);
    }

    /**
     * Update User entity.
     *
     * EasyAdmin custom action to integrate FOSUserBundle and EasyAdminBundle
     *
     * @param $user
     */
    public function updateUserEntity(User $user): void
    {
        $this->userManager->updateUser($user, false);
        parent::updateEntity($user);
    }
}
