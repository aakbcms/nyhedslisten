<?php

/**
 * @file
 */

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category implements \Stringable
{
    use BlameableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text')]
    private ?string $cqlSearch = null;

    #[ORM\ManyToMany(targetEntity: Material::class, mappedBy: 'categories', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['creatorFiltered' => 'ASC'])]
    private Collection $materials;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: SearchRun::class, fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['runAt' => 'DESC'])]
    private Collection $searchRuns;

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

    /**
     * Extra Getter for name.
     *
     * EasyAdmin doesn't play nice when displaying the
     * same filed twice so must have an extra getter
     * for the name filed to fool EasyAdmin.
     *
     * @return string|null
     */
    public function getHlName(): ?string
    {
        return $this->getName();
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
    public function getLastSearchRunAt(): ?\DateTimeImmutable
    {
        /** @var SearchRun|false $searchRun */
        $searchRun = $this->searchRuns->first();

        return $searchRun ? $searchRun->getRunAt() : null;
    }

    /**
     * Get if the last search run was a success.
     */
    public function getLastSearchRunSuccess(): ?bool
    {
        /** @var SearchRun|false $searchRun */
        $searchRun = $this->searchRuns->first();

        return $searchRun ? $searchRun->getIsSuccess() : null;
    }
}
