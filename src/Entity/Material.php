<?php

/**
 * @file
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Table(name="material",
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="pid_unique",
 *            columns={"pid"})
 *    },
 *    indexes={
 *        @ORM\Index(name="pid_material_idx", columns={"pid"}),
 *        @ORM\Index(name="title_idx", columns={"title"}),
 *        @ORM\Index(name="creator_idx", columns={"creator"}),
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\MaterialRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Material
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"material", "feed_materials"})
     * @SerializedName("material_id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"material", "feed_materials"})
     * @SerializedName("title")
     */
    private $titleFull;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"material", "feed_materials"})
     * @SerializedName("creator")
     */
    private $creatorFiltered;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $creatorAut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $creatorCre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contributorAct;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contributorAut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contributorCtb;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contributorDkfig;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $abstract;

    /**
     * @ORM\Column(type="string", length=25)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $pid;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $publisher;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Search", inversedBy="materials")
     *
     * @Groups("search")
     */
    private $searches;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $uri;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $coverUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups({"material", "feed_materials"})
     */
    private $type;

    public function __construct()
    {
        $this->searches = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->titleFull ?? '';
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersistCallback(): void
    {
        $this->updateCreatorFiltered();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdateCallback(): void
    {
        $this->updateCreatorFiltered();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleFull(): ?string
    {
        return $this->titleFull;
    }

    public function setTitleFull(string $titleFull): self
    {
        $this->titleFull = $titleFull;

        return $this;
    }

    public function getCreatorFiltered(): ?string
    {
        return $this->creatorFiltered;
    }

    public function getCreator(): ?string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    public function setAbstract(?string $abstract): self
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function getPid(): ?string
    {
        return $this->pid;
    }

    public function setPid(string $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * @return Collection|Search[]
     */
    public function getSearches(): Collection
    {
        return $this->searches;
    }

    public function addSearch(Search $search): self
    {
        if (!$this->searches->contains($search)) {
            $this->searches[] = $search;
        }

        return $this;
    }

    public function removeSearch(Search $search): self
    {
        if ($this->searches->contains($search)) {
            $this->searches->removeElement($search);
        }

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getCoverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function setCoverUrl(string $coverUrl): self
    {
        $this->coverUrl = $coverUrl;

        return $this;
    }

    public function getCreatorAut(): ?string
    {
        return $this->creatorAut;
    }

    public function setCreatorAut(?string $creatorAut): self
    {
        $this->creatorAut = $creatorAut;

        return $this;
    }

    public function getCreatorCre(): ?string
    {
        return $this->creatorCre;
    }

    public function setCreatorCre(?string $creatorCre): self
    {
        $this->creatorCre = $creatorCre;

        return $this;
    }

    public function getContributor(): ?string
    {
        return $this->contributor;
    }

    public function setContributor(?string $contributor): self
    {
        $this->contributor = $contributor;

        return $this;
    }

    public function getContributorAct(): ?string
    {
        return $this->contributorAct;
    }

    public function setContributorAct(?string $contributorAct): self
    {
        $this->contributorAct = $contributorAct;

        return $this;
    }

    public function getContributorAut(): ?string
    {
        return $this->contributorAut;
    }

    public function setContributorAut(?string $contributorAut): self
    {
        $this->contributorAut = $contributorAut;

        return $this;
    }

    public function getContributorCtb(): ?string
    {
        return $this->contributorCtb;
    }

    public function setContributorCtb(?string $contributorCtb): self
    {
        $this->contributorCtb = $contributorCtb;

        return $this;
    }

    public function getContributorDkfig(): ?string
    {
        return $this->contributorDkfig;
    }

    public function setContributorDkfig(?string $contributorDkfig): self
    {
        $this->contributorDkfig = $contributorDkfig;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Update the "filtered" creator to ensure a value is given.
     *
     * Return the first non null value from "creator", "creatorAut", "creatorCre",
     * "contributor", "contributorAct", "contributorAut", "contributorCtb",
     * "contributorDkfig" and "publisher" in that order
     */
    private function updateCreatorFiltered(): void
    {
        $creatorFiltered = $this->creator ?? $this->creatorAut ?? $this->creatorCre ?? $this->contributor ?? $this->contributorAct ?? $this->contributorAut ?? $this->contributorCtb ?? $this->contributorDkfig ?? $this->publisher;
        $this->creatorFiltered = $creatorFiltered;
    }
}
