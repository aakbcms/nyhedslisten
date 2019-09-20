<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service\OpenPlatform;

use App\Entity\Search;
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

    private $searchService;
    private $materialPersistService;
    private $entityManager;

    private $agencyId;
    private $excludedBranches;
    private $excludedCirculationRules;

    /**
     * NewMaterialService constructor.
     *
     * @param SearchService          $searchService          Service to query the Open Platform
     * @param MaterialPersistService $materialPersistService Service to persist or update new materials
     * @param EntityManagerInterface $entityManager          Doctrine Entitymanager
     * @param ParameterBagInterface  $params                 Application configuration
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
     * @param Search            $search The Search to check for new materials to
     * @param DateTimeImmutable $since  The date since when materials should be received
     *
     * @return array Array of new Materials
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function updateNewMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $searchRun = new SearchRun($search, new DateTimeImmutable());

        try {
            $newMaterials = $this->getNewMaterialsSinceDate($search, $since);
            $this->materialPersistService->saveResults($newMaterials, $search);

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
     * @param Search            $search The Search to check for new materials to
     * @param DateTimeImmutable $since  The date since when materials should be received
     *
     * @return array Array of new Materials
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException'
     * @throws PlatformAuthException
     */
    public function getNewMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $allMaterials = $this->getAllMaterialsSinceDate($search, $since);
        $newMaterials = $this->excludeMaterialsWithExistingCopy($allMaterials, $since);

        return $newMaterials;
    }

    /**
     * Get the complete CQL query thar will be preformed against OPen Search for the given Search and Date.
     *
     * @param Search            $search
     * @param DateTimeImmutable $since
     *
     * @return string
     */
    public function getCompleteCqlQuery(Search $search, DateTimeImmutable $since): string
    {
        $query = $search->getCqlSearch();

        $query .= sprintf(self::BASE_QUERY, $this->agencyId, $this->buildExcludeSearchString($this->excludedBranches), $this->buildExcludeSearchString($this->excludedCirculationRules));
        $query .= sprintf(self::DATE_QUERY_AFTER, $since->format(self::DATAWELL_DATE_FORMAT));

        return $query;
    }

    /**
     * Get all materials received since given date.
     *
     * Note: This includes materials where there is already an exiting copy in the collection
     *
     * @param Search            $search The Search to check for new materials to
     * @param DateTimeImmutable $since  The date since when materials should be received
     *
     * @return array Array of Materials
     *
     * @throws PlatformAuthException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function getAllMaterialsSinceDate(Search $search, DateTimeImmutable $since): array
    {
        $query = $this->getCompleteCqlQuery($search, $since);

        return $this->searchService->query($query);
    }

    /**
     * Exclude materials with exiting copies from the result set.
     *
     * @param array             $list   Array of Materials to exclude from
     * @param DateTimeImmutable $before The date before which materials should be excluded
     *
     * @return array Array of Materials
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
     * @param array $total   The array of items to exclude from
     * @param array $exclude The array of items to exclude
     *
     * @return array An array with unique items
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
     * @param array $results Array of elements to get PID's from
     *
     * @return string String of all PID's concatenated with commas
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
     * @param array $excluded Array of strings to build exclude from
     *
     * @return string String of all elements with "not" prepended
     */
    private function buildExcludeSearchString(array $excluded): string
    {
        $excluded = array_map(static function ($element) {
            return sprintf('"%s"', $element);
        }, $excluded);

        return 'not '.implode(' not ', $excluded);
    }
}
