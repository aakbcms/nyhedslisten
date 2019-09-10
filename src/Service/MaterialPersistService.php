<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Material;
use App\Entity\Search;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class MaterialPersistService.
 *
 * @TODO: MISSING DOCUMENTATION.
 */
class MaterialPersistService
{
    private $entityManager;
    private $propertyAccessor;

    /**
     * MaterialPersistService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @TODO: MISSING DOCUMENTATION.
     *
     * @param array  $results
     * @param Search $search
     *
     * @throws QueryException
     */
    public function persistResults(array $results, Search $search): void
    {
        $existingMaterials = $this->getExistingMaterials($results);

        foreach ($results as $result) {
            $pid = reset($result['pid']);

            if (\array_key_exists($pid, $existingMaterials)) {
                $material = $existingMaterials[$pid];
            } else {
                $material = $this->parseResultItem($result);
                $this->entityManager->persist($material);
            }

            $material->addSearch($search);
        }

        $this->entityManager->flush();
    }

    /**
     * @TODO: MISSING DOCUMENTATION.
     *
     * @param array $results
     *
     * @return array
     *
     * @throws QueryException
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
     *                      The results from the data well
     *
     * @return material
     *                  Material with all the information collected
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
