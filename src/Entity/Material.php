<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

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
 */
class Material
{
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
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Groups("feed")
     */
    private $creator;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups("feed")
     */
    private $abstract;

    /**
     * @ORM\Column(type="string", length=25)
     *
     * @Groups("feed")
     */
    private $pid;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups("feed")
     */
    private $publisher;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Search", inversedBy="materials")
     */
    private $searches;

    public function __construct()
    {
        $this->searches = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
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
}
