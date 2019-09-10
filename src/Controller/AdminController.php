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
     * @TODO: MISSING DOCUMENTATION.
     *
     * @return UserInterface
     */
    public function createNewUserEntity(): UserInterface
    {
        return $this->userManager->createUser();
    }

    /**
     * @TODO: MISSING DOCUMENTATION.
     *
     * @param $user
     */
    public function persistUserEntity($user): void
    {
        $this->userManager->updateUser($user, false);
        parent::persistEntity($user);
    }

    /**
     * @TODO: MISSING DOCUMENTATION.
     *
     * @param $user
     */
    public function updateUserEntity($user): void
    {
        $this->userManager->updateUser($user, false);
        parent::updateEntity($user);
    }
}
