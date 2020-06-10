<?php

/**
 * @file
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Material;
use App\Entity\Search;
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
     * Get materials from last 7 days ordered by category, search.
     *
     * @Route("/feed", name="feed", methods={"GET","HEAD"})
     */
    public function index(): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $data = [];
        $data['received_since'] = $date->format(DATE_ATOM);
        $data['categories'] = $this->entityManager->getRepository(Category::class)->findBySearchMaterialDate($date);

        return $this->json($data, 200, [], ['groups' => ['category', 'material']]);
    }

    /**
     * Get all categories.
     *
     * @Route("/feed/categories", name="feed_categories", methods={"GET","HEAD"})
     */
    public function categories(): JsonResponse
    {
        $data = $this->entityManager->getRepository(Category::class)->findAll();

        return $this->json($data, 200, [], ['groups' => 'category']);
    }

    /**
     * Get all searches.
     *
     * @Route("/feed/searches", name="feed_searches", methods={"GET","HEAD"})
     */
    public function searches(): JsonResponse
    {
        $data = $this->entityManager->getRepository(Search::class)->findAll();

        return $this->json($data, 200, [], ['groups' => ['search']]);
    }

    /**
     * Get all materials for last 7 days.
     *
     * @Route("/feed/materials", name="feed_materials", methods={"GET","HEAD"})
     */
    public function materials(): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $data = $this->entityManager->getRepository(Material::class)->findLatest($date);

        return $this->json($data, 200, [], ['groups' => ['search', 'feed_materials']]);
    }

    /**
     * Get search.
     *
     * @Route("/feed/searches/{id}", name="feed_search", methods={"GET","HEAD"}, requirements={"page"="\d+"})
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function search(int $id): JsonResponse
    {
        $data = $this->entityManager->getRepository(Search::class)->findBy(['id' => $id]);

        return $this->json($data, 200, [], ['groups' => ['search']]);
    }

    /**
     * Get materials for a search from the last 7 days.
     *
     * @Route("/feed/searches/{searchId}/materials", name="feed_search_materials", methods={"GET","HEAD"}, requirements={"page"="\d+"})
     *
     * @param int $searchId
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function searchMaterials(int $searchId): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $data = $this->entityManager->getRepository(Material::class)->findLatestBySearch($date, $searchId);

        return $this->json($data, 200, [], ['groups' => ['material']]);
    }
}
