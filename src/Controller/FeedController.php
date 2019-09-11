<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class FeedController.
 *
 * Exposes endpoints for all json feeds
 */
class FeedController extends AbstractController
{
    private $entityManager;
    private $serializer;

    /**
     * FeedController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface    $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * Main feed endpoint.
     *
     * @Route("/feed", name="feed")
     */
    public function index(): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $data = [];
        $data['categories'] = $this->entityManager->getRepository(Category::class)->findBySearchMaterialDate($date);
        $json = $this->serializer->serialize($data, 'json', ['groups' => 'feed']);

        return JsonResponse::fromJsonString($json);
    }
}
