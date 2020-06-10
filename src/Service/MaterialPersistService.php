<?php

/**
 * @file
 * Service to persist new Materials or update existing Materials in local database.
 */

namespace App\Service;

use App\Entity\Material;
use App\Entity\Search;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class MaterialPersistService.
 *
 * Service to persist new Materials or update existing Materials.
 */
class MaterialPersistService
{
    private $entityManager;
    private $propertyAccessor;
    private $ddbUriService;
    private $coverServiceService;

    /**
     * MaterialPersistService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param DdbUriService $ddbUriService
     * @param CoverServiceService $coverServiceService
     */
    public function __construct(EntityManagerInterface $entityManager, DdbUriService $ddbUriService, CoverServiceService $coverServiceService)
    {
        $this->entityManager = $entityManager;
        $this->ddbUriService = $ddbUriService;
        $this->coverServiceService = $coverServiceService;

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Save result set either updating existing Material or persisting new Materials.
     *
     * @param array  $results
     *   Array of Materials to save
     * @param Search $search
     *   The Search that generated the result set
     *
     * @throws Exception
     */
    public function saveResults(array $results, Search $search): void
    {
        $existingMaterials = $this->getExistingMaterials($results);

        // Try to get covers for the materials.
        $pids = array_map(function($item) {
            return $item['pid'][0];
        }, $results);
        $covers = $this->coverServiceService->getCovers($pids);

        foreach ($results as $result) {
            $pid = reset($result['pid']);

            if (\array_key_exists($pid, $existingMaterials)) {
                $material = $existingMaterials[$pid];
            } else {
                $material = $this->parseResultItem($result);
                $this->entityManager->persist($material);
            }

            $uri = $this->ddbUriService->getUri($material->getPid());
            $material->setUri($uri);
            $material->addSearch($search);

            // Try to get cover for the material.
            $material->setCoverUrl($covers[$pid] ?? '');
        }

        $this->entityManager->flush();
    }

    /**
     * Search for the Materials in $results that already exist in the database.
     *
     * @param array $results
     *   The Materials to check for existing Materials for
     *
     * @return Material[]
     *   Array of material entities
     */
    private function getExistingMaterials(array $results): array
    {
        $pidArray = array_map(static function ($result) {
            return reset($result['pid']);
        }, $results);

        return $this->entityManager->getRepository(Material::class)->findByPidList($pidArray);
    }

    /**
     * Parse the search result from the data well.
     *
     * @param array $result
     *   The results from the data well
     *
     * @return Material
     *   Material entity with all the information collected
     *
     * @throws Exception
     */
    private function parseResultItem(array $result): Material
    {
        $material = new Material();
        foreach ($result as $key => $items) {
            switch ($key) {
                case 'identifierISMN':
                case 'identifierISR':
                case 'identifierISBN':
                case 'identifierISSN':
                    break;

                case 'date':
                    $year = reset($items);
                    $material->setDate(new \DateTime());
                    break;

                default:
                    if ($this->propertyAccessor->isWritable($material, $key)) {
                        $this->propertyAccessor->setValue($material, $key, reset($items));
                    }
                    break;
            }
        }

        return $material;
    }
}
