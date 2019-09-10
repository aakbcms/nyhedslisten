<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class FeedController extends AbstractController
{
    private $entityManager;
    private $serializer;

    /**
     * FeedController constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/feed", name="feed")
     */
    public function index(): JsonResponse
    {
        $date = new \DateTimeImmutable('7 days ago');

        $data = [];
        $data['categories'] = $this->entityManager->getRepository(Category::class)->findBySearchDate($date);
        $json = $this->serializer->serialize($data, 'json', ['groups' => 'feed']);

        return JsonResponse::fromJsonString($json);
    }
}
