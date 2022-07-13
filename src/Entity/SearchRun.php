<?php

/**
 * @file
 */

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SearchRunRepository")
 */
class SearchRun
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSuccess;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $errorMessage;

    /**
     * SearchRun constructor.
     *
     * @param Category $category
     * @param DateTimeImmutable $runAt
     */
    public function __construct(
        /**
         * @ORM\ManyToOne(targetEntity="Category", inversedBy="searchRuns")
         * @ORM\JoinColumn(nullable=false)
         */
        private Category $category,
        /**
         * @ORM\Column(type="datetime")
         */
        private DateTimeImmutable $runAt
    )
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRunAt(): ?\DateTimeInterface
    {
        return $this->runAt;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getIsSuccess(): ?bool
    {
        return $this->isSuccess;
    }

    public function setIsSuccess(bool $isSuccess): self
    {
        $this->isSuccess = $isSuccess;

        return $this;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }
}
