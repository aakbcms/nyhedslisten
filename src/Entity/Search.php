<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRepository")
 */
class Search
{
    use BlameableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups("feed")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups("feed")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $cqlSearch;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="searches")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Material", mappedBy="searches")
     *
     * @Groups("feed")
     */
    private $materials;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SearchRun", mappedBy="search", orphanRemoval=true)
     */
    private $searchRuns;

    public function __construct()
    {
        $this->materials = new ArrayCollection();
        $this->searchRuns = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
            $material->addSearch($this);
        }

        return $this;
    }

    public function removeMaterial(Material $material): self
    {
        if ($this->materials->contains($material)) {
            $this->materials->removeElement($material);
            $material->removeSearch($this);
        }

        return $this;
    }

    /**
     * @return Collection|SearchRun[]
     */
    public function getSearchRuns(): Collection
    {
        return $this->searchRuns;
    }

    public function addSearchRun(SearchRun $searchRun): self
    {
        if (!$this->searchRuns->contains($searchRun)) {
            $this->searchRuns[] = $searchRun;
            $searchRun->setSearch($this);
        }

        return $this;
    }

    public function removeSearchRun(SearchRun $searchRun): self
    {
        if ($this->searchRuns->contains($searchRun)) {
            $this->searchRuns->removeElement($searchRun);
            // set the owning side to null (unless already changed)
            if ($searchRun->getSearch() === $this) {
                $searchRun->setSearch(null);
            }
        }

        return $this;
    }
}
