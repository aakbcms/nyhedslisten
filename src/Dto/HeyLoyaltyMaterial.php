<?php


namespace App\Dto;


use App\Entity\Material;
use App\Entity\Search;

class HeyLoyaltyMaterial
{
    private $sortKey;
    private $search;
    private $material;

    /**
     * HeyLoyaltyMaterial constructor.
     *
     * @param int $sortKey
     * @param Search $search
     * @param Material $material
     */
    public function __construct(int $sortKey, Search $search, Material $material)
    {
        $this->sortKey = $sortKey;
        $this->search = $search;
        $this->material = $material;
    }

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
        return $this->search->getId();
    }

    public function getCategoryName(): ?string
    {
        return $this->search->getCategory()->getName().': '.$this->search->getName();
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