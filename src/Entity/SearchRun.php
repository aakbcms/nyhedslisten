<?php

/**
 * @file
 */

namespace App\Entity;

use App\Repository\SearchRunRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SearchRunRepository::class)]
class SearchRun implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isSuccess = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    /**
     * SearchRun constructor.
     */
    public function __construct(
        #[ORM\ManyToOne(targetEntity: 'Category', inversedBy: 'searchRuns')]
        #[ORM\JoinColumn(nullable: false)]
        private readonly Category $category,
        #[ORM\Column(type: 'datetime_immutable')]
        private readonly \DateTimeImmutable $runAt
    ) {}

    public function __toString(): string
    {
        $result = $this->isSuccess ? 'OK' : 'ERROR';
        $date = $this->getRunAt()->format(DATE_ATOM);
        $message = $this->isSuccess ? '' : ' | '.$this->getErrorMessage();

        return $result.' | '.$date.$message;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRunAt(): ?\DateTimeImmutable
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
