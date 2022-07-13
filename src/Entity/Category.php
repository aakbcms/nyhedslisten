<?php

/**
 * @file
 */

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category implements \Stringable
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $cqlSearch;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Material", mappedBy="categories", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"creatorFiltered" = "ASC"})
     */
    private $materials;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SearchRun", mappedBy="category", orphanRemoval=true, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"id" = "DESC"})
     */
    private $searchRuns;

    public function __construct()
    {
        $this->materials = new ArrayCollection();
        $this->searchRuns = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCqlSearch(): ?string
    {
        return $this->cqlSearch;
    }

    public function setCqlSearch(string $cqlSearch): self
    {
        $this->cqlSearch = $cqlSearch;

        return $this;
    }

    /**
     * @return Collection|Material[]
     */
    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(Material $material): self
    {
        if (!$this->materials->contains($material)) {
            $this->materials[] = $material;
            $material->addCategory($this);
        }

        return $this;
    }

    public function removeMaterial(Material $material): self
    {
        if ($this->materials->contains($material)) {
            $this->materials->removeElement($material);
            $material->removeCategory($this);
        }

        return $this;
    }

    /**
     * Get search runs.
     *
     * @return Collection|SearchRun[]
     */
    public function getSearchRuns(): Collection
    {
        return $this->searchRuns;
    }

    /**
     * Add search run.
     *
     *
     */
    public function addSearchRun(SearchRun $searchRun): self
    {
        if (!$this->searchRuns->contains($searchRun)) {
            $this->searchRuns[] = $searchRun;
            $searchRun->setSearch($this);
        }

        return $this;
    }

    /**
     * Remove search run.
     *
     *
     */
    public function removeSearchRun(SearchRun $searchRun): self
    {
        if ($this->searchRuns->contains($searchRun)) {
            $this->searchRuns->removeElement($searchRun);
            // set the owning side to null (unless already changed)
            if ($searchRun->getCategory() === $this) {
                $searchRun->setSearch(null);
            }
        }

        return $this;
    }

    /**
     * Get the datetime of the latest search run.
     */
    public function getLastSearchRunAt(): ?DateTimeInterface
    {
        $searchRun = $this->searchRuns->first();

        return $searchRun ? $searchRun->getRunAt() : null;
    }

    /**
     * Get if the last search run was a success.
     */
    public function getLastSearchRunSuccess(): ?bool
    {
        $searchRun = $this->searchRuns->first();

        return $searchRun ? $searchRun->getIsSuccess() : null;
    }
}
