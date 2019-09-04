<?php

namespace App\Service\OpenPlatform;

use App\Entity\Search;
use App\Entity\SearchRun;
use App\Service\MaterialPersistService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

    private $searchService;
    private $materialPersistService;
    private $entityManager;

    private $agencyId;
    private $excludedBranches;
    private $excludedCirculationRules;

    /**
     * NewMaterialService constructor.
     *
     * @param SearchService $searchService
     * @param MaterialPersistService $materialPersistService
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface $params
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
     * Get and persists new materials received since date.
     *
     * @param Search $search
     * @param DateTimeImmutable $since
     *
     * @return array
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function persistNewMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $newMaterials = $this->getMaterialsSinceDate($search, $since);
        $this->materialPersistService->persistResults($newMaterials, $search);

        return $newMaterials;
    }

    /**
     * Get new materials received since date.
     *
     * @param Search $search
     * @param DateTimeImmutable $since
     *
     * @return array
     *
     * @throws Exception
     */
    public function getMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $searchRun = new SearchRun($search, new DateTimeImmutable());

        try {
            $allMaterials = $this->getAllMaterialsSinceDate($search, $since);
            $newMaterials = $this->excludeMaterialsWithExistingCopy($allMaterials, $since);

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
     * Get all materials received since given date.
     *
     * Note: This includes materials where there is already an exiting copy in the collection
     *
     * @param Search $search
     * @param DateTimeImmutable $since
     *
     * @return array
     */
    private function getAllMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $query = $search->getCqlSearch();

        $query .= sprintf(self::BASE_QUERY, $this->agencyId, $this->buildExcludeSearchString($this->excludedBranches), $this->buildExcludeSearchString($this->excludedCirculationRules));
        $query .= sprintf(self::DATE_QUERY_AFTER, $since->format(self::DATAWELL_DATE_FORMAT));

        return $this->searchService->query($query);
    }

    /**
     * Exclude materials with exiting materials from result set.
     *
     * @param array $list
     * @param DateTimeImmutable $before
     *
     * @return array
     */
    private function excludeMaterialsWithExistingCopy(array $list, DateTimeImmutable $before): array
    {
        $count = count($list);
        $offset = 0;
        $new = [];

        while ($offset < $count) {
            $listSlice = array_slice($list, $offset, self::SEARCH_LIMIT);

            $q = 'rec.id any "'.$this->buildPidIncludeString($listSlice).'"';
            $q .= sprintf(self::BASE_QUERY, $this->agencyId, $this->buildExcludeSearchString($this->excludedBranches), $this->buildExcludeSearchString($this->excludedCirculationRules));
            $q .= sprintf(self::DATE_QUERY_BEFORE, $before->format(self::DATAWELL_DATE_FORMAT));

            $existingCopy = $this->searchService->query($q);

            $diff = $this->getResultDiffByPid($listSlice, $existingCopy);
            $this->mergeArraysByReference($new, $diff);

            $offset += self::SEARCH_LIMIT;
        }

        return $new;
    }

    /**
     * Find all items in '$total' not present in '$exclude' compared by 'pid'.
     *
     * @param array $total
     * @param array $exclude
     *
     * @return array
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
     *
     * @return string
     */
    private function buildPidIncludeString(array $results): string
    {
        $pidArray = array_map(static function ($element) {
            return implode(' ', $element['pid']);
        }, $results);
        $included = implode(' ', $pidArray);

        return $included;
    }

    /**
     * Build the 'exclude' part for a CQL query string.
     *
     * @param array $excluded
     *
     * @return string
     */
    private function buildExcludeSearchString(array $excluded): string
    {
        $excluded = array_map(static function ($element) {
            return sprintf('"%s"', $element);
        }, $excluded);

        return 'not '.implode(' not ', $excluded);
    }

    /**
     * Merge from one array into another by reference.
     *
     * PHPs array_merge() performance is not always optimal:
     * https://stackoverflow.com/questions/23348339/optimizing-array-merge-operation
     *
     * @param array $mergeTo
     * @param array $mergeFrom
     */
    private function mergeArraysByReference(array &$mergeTo, array &$mergeFrom): void
    {
        foreach ($mergeFrom as $i) {
            $mergeTo[] = $i;
        }
    }
}
