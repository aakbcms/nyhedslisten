<?php

/**
 * @file
 */

namespace App\Controller;

use App\Dto\HeyLoyaltyMaterial;
use App\Entity\Category;
use App\Entity\Material;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FeedController.
 *
 * Exposes endpoints for all json feeds
 */
class FeedController extends AbstractController
{
    private $entityManager;

    /**
     * FeedController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * HeyLoyalty feed: Get materials from last 7 days ordered by category, search, creator.
     *
     * @Route("/feed/heyloyalty", name="feed_heyloyalty", methods={"GET","HEAD"})
     */
    public function heyLoyalty(): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $categories = $this->entityManager->getRepository(Category::class)->findByMaterialDate($date);

        // HeyLoyalty doesn't support sorting on multiple values, like SQL does
        // ( E.g "ORDER BY author ASC, year DESC"). HeyLoyalty only allows sorting on one
        // key in the json feed. "Sortkey" is used the HeyLoyalty setup to force HeyLoyalty
        // to maintain the order the materiels have in the feed.
        // However HeyLoyalty does "text" sort, not "numeric" sort, so "15" comes after "149"
        // in their sorting. To guard against this we start the sortKey counter at 1000000
        // to avoid leading zeros and to avoid sortkeys of different str length.
        // (Depends on feed never having more than 999999 items)
        $sortKey = 100000;
        $data = [];
        foreach ($categories as $category) {
            foreach ($category->getMaterials() as $material) {
                $data[] = new HeyLoyaltyMaterial($sortKey, $category, $material);
                ++$sortKey;
            }
        }

        return $this->json($data, 200, []);
    }
}
