<?php

/**
 * @file
 */

namespace App\Controller;

use App\Entity\Category;
use App\Service\Heyloyalty\HeyloyaltyService;
use App\Service\OpenPlatform\NewMaterialService;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController.
 *
 * Used to integrate FOSUserBundle and EasyAdminBundle
 * https://symfony.com/doc/master/bundles/EasyAdminBundle/integration/fosuserbundle.html
 */
class AdminController
{
    /**
     * AdminController constructor.
     *
     * @param NewMaterialService $newMaterialService
     *   The service to query for new materials
     * @param heyloyaltyService $heyloyaltyService
     *   Integration with HL
     */
    public function __construct(
        private readonly NewMaterialService $newMaterialService,
        private readonly HeyloyaltyService $heyloyaltyService
    ) {}

    /**
     * Custom batch action to update materials for Searches.
     *
     * @param array $ids
     *   The ids of the entities selected in the UI
     *
     * @throws GuzzleException
     *   If the Search query is malformed or the Open Search calls fails
     * @throws InvalidArgumentException
     */
    public function queryBatchAction(array $ids): void
    {
        $ids = array_map(static fn ($id) => (int) $id, $ids);
        $searches = $this->em->getRepository(Category::class)->findBy(['id' => $ids]);

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
                (is_countable($searches) ? \count($searches) : 0).' CQL queries performed, '.$materialCount.' materials fetched'
            );
        }
    }

    /**
     * Custom action to test CQL Query.
     *
     * @throws GuzzleException
     *    If the Search query is malformed or the Open Search calls fails
     * @throws InvalidArgumentException
     */
    public function queryAction(): Response
    {
        $id = $this->request->query->get('id');
        $search = $this->em->getRepository(Category::class)->find($id);

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

    protected function updateCategoryEntity($entity)
    {
        $uof = $this->em->getUnitOfWork();
        $originalEntity = $uof->getOriginalEntityData($entity);

        try {
            $this->heyloyaltyService->updateOption($originalEntity['name'], $entity->getName());
        } catch (\Exception $exception) {
            if ('Option not found' == $exception->getCode()) {
                $this->heyloyaltyService->addOption($entity->getName());
            }
        }
    }

    protected function persistCategoryEntity($entity)
    {
        $this->heyloyaltyService->addOption($entity->getName());
    }
}
