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
use App\Entity\Search;
use App\Entity\User;
use App\Service\Heyloyalty\HeyloyaltyService;
use App\Service\OpenPlatform\NewMaterialService;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * Used to integrate FOSUserBundle and EasyAdminBundle
 * https://symfony.com/doc/master/bundles/EasyAdminBundle/integration/fosuserbundle.html
 */
class AdminController extends EasyAdminController
{
    private $userManager;
    private $newMaterialService;
    private $heyloyaltyService;

    /**
     * AdminController constructor.
     *
     * @param UserManagerInterface $userManager        The FOS user manager
     * @param NewMaterialService   $newMaterialService The service to query for new materials
     * @param HeyloyaltyService $heyloyaltyService     Integration with HL.
     */
    public function __construct(UserManagerInterface $userManager, NewMaterialService $newMaterialService, HeyloyaltyService $heyloyaltyService)
    {
        $this->userManager = $userManager;
        $this->newMaterialService = $newMaterialService;
        $this->heyloyaltyService = $heyloyaltyService;
    }

    /**
     * Custom batch action to update materials for Searches.
     *
     * @param array $ids the ids of the entities selected in the UI
     *
     * @throws GuzzleException          if the Search query is malformed or the Open Search calls fails
     * @throws InvalidArgumentException
     */
    public function queryBatchAction(array $ids): void
    {
        $ids = array_map(static function ($id) {
            return (int) $id;
        }, $ids);
        $searches = $this->em->getRepository(Search::class)->findBy(['id' => $ids]);

        // @TODO Move time interval to config
        $date = new \DateTimeImmutable('7 days ago');

        $materialCount = 0;
        foreach ($searches as $search) {
            $result = $this->newMaterialService->updateNewMaterialsSinceDate($search, $date);

            $materialCount += \count($result);
        }

        if (!empty($searches)) {
            $this->addFlash(
                'info',
                \count($searches).' CQL queries performed, '.$materialCount.' materials fetched'
            );
        }
    }

    /**
     * Custom action to test CQL Query.
     *
     * @return Response
     *
     * @throws GuzzleException          if the Search query is malformed or the Open Search calls fails
     * @throws InvalidArgumentException
     */
    public function queryAction(): Response
    {
        $id = $this->request->query->get('id');
        $search = $this->em->getRepository(Search::class)->find($id);

        // @TODO Move time interval to config
        $date = new \DateTimeImmutable('7 days ago');
        try {
            $result = $this->newMaterialService->getNewMaterialsSinceDate($search, $date);

            $this->addFlash(
                'success',
                'CQL query successful'
            );
            $success = true;
        } catch (\Exception $exception) {
            $this->addFlash(
                'danger',
                'Error testing CQL query: '.$exception->getMessage()
            );
            $success = false;
        }
        $query = $this->newMaterialService->getCompleteCqlQuery($search, $date);

        $templateData = [
            'id' => $id,
            'entity' => $search,
            'cqlQuery' => $query,
            'result' => $result ?? null,
            'success' => $success,
        ];

        return $this->render('actions/showQueryResult.html.twig', $templateData);
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


    /**
     * @inheritDoc
     */
    protected function removeEntity($entity) {
        parent::removeEntity($entity);
    }

    /**
     * @inheritDoc
     */
    protected function updateEntity($entity) {
        $uof = $this->em->getUnitOfWork();
        $originalEntity = $uof->getOriginalEntityData($entity);

        parent::updateEntity($entity);

        try {
            $this->heyloyaltyService->updateOption($originalEntity['name'], $entity->getName());
        }
        catch (\Exception $exception) {
            if ($exception->getCode() == 'Option not found') {
                $this->heyloyaltyService->addOption($entity->getName());
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function persistEntity($entity) {
        parent::persistEntity($entity);

        $this->heyloyaltyService->addOption($entity->getName());
    }
}
