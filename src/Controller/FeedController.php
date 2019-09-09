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
     * @param SerializerInterface    $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/feed", name="feed")
     */
    public function index()
    {
        $date = new \DateTimeImmutable('7 days ago');

        $categories = $this->entityManager->getRepository(Category::class)->findBySearchDate($date);
        $json = $this->serializer->normalize($categories, null, ['groups' => 'feed']);

        return new JsonResponse($json);
    }
}
