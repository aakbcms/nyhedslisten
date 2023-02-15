<?php

/**
 * @file
 * Service to persist new Materials or update existing Materials in local database.
 */

namespace App\Service;

use App\Entity\Category;
use App\Entity\Material;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class MaterialPersistService.
 *
 * Service to persist new Materials or update existing Materials.
 */
class MaterialPersistService
{
    private readonly PropertyAccessor $propertyAccessor;

    /**
     * MaterialPersistService constructor.
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly DdbUriService $ddbUriService, private readonly CoverServiceService $coverServiceService)
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Save result set either updating existing Material or persisting new Materials.
     *
     * @param array    $results
     *                           Array of Materials to save
     * @param Category $category
     *                           The Search that generated the result set
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function saveResults(array $results, Category $category): void
    {
        $existingMaterials = $this->getExistingMaterials($results);

        // Try to get covers for the materials.
        $pids = array_map(fn ($item) => $item['pid'][0], $results);
        $covers = $this->coverServiceService->getCovers($pids);

        foreach ($results as $result) {
            $pid = reset($result['pid']);
            $material = \array_key_exists($pid, $existingMaterials) ? $existingMaterials[$pid] : new Material();

            $this->parseResultItem($material, $result);

            $uri = $this->ddbUriService->getUri($material->getPid());
            $material->setUri($uri);
            $material->addCategory($category);

            // Try to get cover for the material.
            $material->setCoverUrl($covers[$pid] ?? $this->coverServiceService->getGenericCoverUrl($material));

            if (!$this->entityManager->contains($material)) {
                $this->entityManager->persist($material);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Search for the Materials in $results that already exist in the database.
     *
     * @param array $results
     *                       The Materials to check for existing Materials for
     *
     * @return Material[]
     *                    Array of material entities
     */
    private function getExistingMaterials(array $results): array
    {
        $pidArray = array_map(static fn ($result) => reset($result['pid']), $results);

        return $this->entityManager->getRepository(Material::class)->findByPidList($pidArray);
    }

    /**
     * Parse the search result from the data well.
     *
     * @param Material $material
     *                           The material to parse the results to
     * @param array    $result
     *                           The results from the data well
     *
     * @throws \Exception
     */
    private function parseResultItem(Material $material, array $result): void
    {
        foreach ($result as $key => $items) {
            switch ($key) {
                case 'identifierISMN':
                case 'identifierISR':
                case 'identifierISBN':
                case 'identifierISSN':
                case 'date':
                    break;

                default:
                    $this->setValue($material, $key, $items);
                    break;
            }
        }

        $material->setDate(new \DateTime());
    }

    /**
     * Set value for field.
     *
     * @param Material $material
     *                           The material to set value on
     * @param string   $key
     *                           The field to set on the Material
     * @param array    $items
     *                           Array of open platform 'items' to set as value
     */
    private function setValue(Material $material, string $key, array $items): void
    {
        if ($this->propertyAccessor->isWritable($material, $key)) {
            $value = implode(', ', $items);
            $value = mb_strlen($value) <= 255 ? $value : mb_substr($value, 0, 251).'...';
            $this->propertyAccessor->setValue($material, $key, $value);
        }
    }
}
