<?php

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Material;

class HeyLoyaltyMaterial
{
    /**
     * HeyLoyaltyMaterial constructor.
     */
    public function __construct(
        private readonly int $sortKey,
        private readonly Category $category,
        private readonly Material $material
    ) {}

    public function getSortKey(): int
    {
        return $this->sortKey;
    }

    public function getMaterialId(): int
    {
        return $this->material->getId();
    }

    public function getTitle(): ?string
    {
        return $this->material->getTitleFull();
    }

    public function getCreator(): ?string
    {
        return $this->material->getCreatorFiltered();
    }

    public function getAbstract(): ?string
    {
        return $this->material->getAbstract();
    }

    public function getPid(): ?string
    {
        return $this->material->getPid();
    }

    public function getPublisher(): ?string
    {
        return $this->material->getPublisher();
    }

    public function getCategoryId(): int
    {
        return $this->category->getId();
    }

    public function getCategoryName(): ?string
    {
        return $this->category->getName();
    }

    public function getUri(): ?string
    {
        return $this->material->getUri();
    }

    public function getCoverUrl(): ?string
    {
        return $this->material->getCoverUrl();
    }

    public function getType(): ?string
    {
        return $this->material->getType();
    }
}
