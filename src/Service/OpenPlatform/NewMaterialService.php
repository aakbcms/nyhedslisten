<?php

/**
 * @file
 * New materials service.
 */

namespace App\Service\OpenPlatform;

use App\Entity\Category;
use App\Entity\SearchRun;
use App\Exception\PlatformAuthException;
use App\Service\MaterialPersistService;
use App\Utils\ArrayMerge;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class NewMaterialService.
 *
 * Service to query the Open Platform for all new materials for a given CQL search. The
 * strategy used to exclude materials where a new copy of an existing title have been
 * received is to first query for all materials with an accession date greater than
 * the given date (Set 1). Then query again for materials with an ID from Set 1 and an
 * accession date smaller than the given date (Set 2). Materials in Set 1, not present
 * in Set 2 are considered new.
 */
class NewMaterialService
{
    private const SEARCH_LIMIT = 50;
    private const BASE_QUERY = '
        and holdingsitem.agencyid = %s 
        and holdingsitem.branch = ( * %s ) 
        and holdingsitem.circulationRule = (* %s )';

    private const DATE_QUERY_AFTER = ' and holdingsitem.accessionDate >= %s ';
    private const DATE_QUERY_BEFORE = ' and holdingsitem.accessionDate <= %s ';

    private const DATAWELL_DATE_FORMAT = 'Y-m-d\T00:00:00\Z';

    private SearchService $searchService;
    private MaterialPersistService $materialPersistService;
    private EntityManagerInterface $entityManager;

    private string $agencyId;
    private array $excludedBranches;
    private array $excludedCirculationRules;

    /**
     * NewMaterialService constructor.
     *
     * @param SearchService $searchService
     * @param MaterialPersistService $materialPersistService
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface  $params
     */
    public function __construct(SearchService $searchService, MaterialPersistService $materialPersistService, EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->searchService = $searchService;
        $this->materialPersistService = $materialPersistService;
        $this->entityManager = $entityManager;

        $this->agencyId = $params->get('datawell.vendor.agency');
        $this->excludedBranches = explode(',', $params->get('datawell.vendor.excluded.branches'));
        $this->excludedCirculationRules = explode(',', $params->get('datawell.vendor.excluded.circulationRules'));
    }

    /**
     * Update materials received since date.
     *
     * @param Category $category
     *   The Category to check for new materials to
     * @param DateTimeImmutable $since
     *   The date since when materials should be received
     *
     * @return array
     *   Array of new Materials
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function updateNewMaterialsSinceDate(Category $category, DateTimeImmutable $since): array
    {
        $searchRun = new SearchRun($category, new DateTimeImmutable());

        try {
            $newMaterials = $this->getNewMaterialsSinceDate($category, $since);
            $this->materialPersistService->saveResults($newMaterials, $category);

            $searchRun->setIsSuccess(true);
        } catch (Exception $exception) {
            $searchRun->setIsSuccess(false);
            $searchRun->setErrorMessage($exception->getMessage());
        }

        $this->entityManager->persist($searchRun);
        $this->entityManager->flush();

        return $newMaterials ?? [];
    }

    /**
     * Get new materials received since date.
     *
     * @param Category $category
     *   The Category to check for new materials to
     * @param DateTimeImmutable $since
     *   The date since when materials should be received
     *
     * @return array
     *   Array of new Materials
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException'
     * @throws PlatformAuthException
     */
    public function getNewMaterialsSinceDate(Category $category, DateTimeImmutable $since): array
    {
        $allMaterials = $this->getAllMaterialsSinceDate($category, $since);

        return $this->excludeMaterialsWithExistingCopy($allMaterials, $since);
    }

    /**
     * Get the complete CQL query thar will be preformed against Open Search for the given Category and Date.
     *
     * @param Category $category
     * @param DateTimeImmutable $since
     *
     * @return string
     *   CQL query string
     */
    public function getCompleteCqlQuery(Category $category, DateTimeImmutable $since): string
    {
        $query = $category->getCqlSearch();

        $query .= sprintf(self::BASE_QUERY, $this->agencyId, $this->buildExcludeSearchString($this->excludedBranches), $this->buildExcludeSearchString($this->excludedCirculationRules));
        $query .= sprintf(self::DATE_QUERY_AFTER, $since->format(self::DATAWELL_DATE_FORMAT));

        return $query;
    }

    /**
     * Get all materials received since given date.
     *
     * Note: This includes materials where there is already an exiting copy in the collection
     *
     * @param Category $category
     *   The Category to check for new materials to
     * @param DateTimeImmutable $since
     *   The date since when materials should be received
     *
     * @return array
     *   Array of Materials
     *
     * @throws PlatformAuthException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function getAllMaterialsSinceDate(Category $category, DateTimeImmutable $since): array
    {
        $query = $this->getCompleteCqlQuery($category, $since);

        return $this->searchService->query($query);
    }

    /**
     * Exclude materials with exiting copies from the result set.
     *
     * @param array $list
     *   Array of Materials to exclude from
     * @param DateTimeImmutable $before
     *   The date before which materials should be excluded
     *
     * @return array
     *   Array of Materials
     *
     * @throws PlatformAuthException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function excludeMaterialsWithExistingCopy(array $list, DateTimeImmutable $before): array
    {
        $count = \count($list);
        $offset = 0;
        $new = [];

        while ($offset < $count) {
            $listSlice = \array_slice($list, $offset, self::SEARCH_LIMIT);

            $q = 'rec.id any "'.$this->buildPidIncludeString($listSlice).'"';
            $q .= sprintf(self::BASE_QUERY, $this->agencyId, $this->buildExcludeSearchString($this->excludedBranches), $this->buildExcludeSearchString($this->excludedCirculationRules));
            $q .= sprintf(self::DATE_QUERY_BEFORE, $before->format(self::DATAWELL_DATE_FORMAT));

            $existingCopy = $this->searchService->query($q);

            $diff = $this->getResultDiffByPid($listSlice, $existingCopy);
            ArrayMerge::mergeArraysByReference($new, $diff);

            $offset += self::SEARCH_LIMIT;
        }

        return $new;
    }

    /**
     * Find all items in '$total' not present in '$exclude' compared by 'pid'.
     *
     * @param array $total
     *   The array of items to exclude from
     * @param array $exclude
     *   The array of items to exclude
     *
     * @return array
     *   An array with unique items
     */
    private function getResultDiffByPid(array $total, array $exclude): array
    {
        $diffResult = [];

        foreach ($total as $totalItem) {
            $found = false;

            foreach ($exclude as $excludeItem) {
                $found = empty(array_diff($totalItem['pid'], $excludeItem['pid']));
                if ($found) {
                    break;
                }
            }

            if (!$found) {
                $diffResult[] = $totalItem;
            }
        }

        return $diffResult;
    }

    /**
     * Build CQL string of PID's from result set.
     *
     * @param array $results
     *   Array of elements to get PID's from
     *
     * @return string
     *   String of all PID's concatenated with commas
     */
    private function buildPidIncludeString(array $results): string
    {
        $pidArray = array_map(static fn($element) => implode(' ', $element['pid']), $results);

        return implode(' ', $pidArray);
    }

    /**
     * Build the 'exclude' part for a CQL query string.
     *
     * @param array $excluded
     *   Array of strings to build exclude from
     *
     * @return string
     *   String of all elements with "not" prepended
     */
    private function buildExcludeSearchString(array $excluded): string
    {
        $excluded = array_map(static fn($element) => sprintf('"%s"', $element), $excluded);

        return 'not '.implode(' not ', $excluded);
    }
}
