<?php

/**
 * @file
 */

namespace App\Utils\OpenPlatform;

use App\Exception\MaterialTypeException;

/**
 * Class Material.
 */
class Material implements \Stringable
{
    private string $title = 'Unknown';
    private string $creator = 'Unknown';
    private string $date = 'Unknown';
    private string $publisher = 'Unknown';
    private array $identifiers = [];

    /**
     * Get string representation.
     */
    public function __toString(): string
    {
        $output = [];

        if (!$this->isEmpty()) {
            $output[] = str_repeat('-', 41);
            $output[] = 'Title: '.$this->title;
            $output[] = 'Creator: '.$this->creator;
            $output[] = 'Date: '.$this->date;
            $output[] = 'Publisher: '.$this->publisher;
            $output[] = str_repeat('-', 41);
            $output[] = '----'.str_repeat(' ', 11).'Identifiers'.str_repeat(' ', 11).'----';
            $output[] = str_repeat('-', 41);
            foreach ($this->identifiers as $identifier) {
                $output[] = $identifier->getType().': '.$identifier->getId();
            }
            $output[] = str_repeat('-', 42);
        } else {
            $output[] = 'No information found in the open platform.';
        }

        return implode("\n", $output);
    }

    /**
     * Get the material title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the material title.
     *
     * @param mixed $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the creator.
     */
    public function getCreator(): string
    {
        return $this->creator;
    }

    /**
     * Set the material creators name.
     *
     * @return $this
     */
    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get the date.
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Set the material date.
     *
     * @return $this
     */
    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get the publisher.
     */
    public function getPublisher(): string
    {
        return $this->publisher;
    }

    /**
     * Set the publisher.
     *
     * @return $this
     */
    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * The identifiers for this material.
     *
     * @return MaterialIdentifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * Get material identifier base on type.
     *
     * @param string $type
     *   The type of id (ISBN, ISSN, ISMN, ISRC, PID)
     *
     * @return MaterialIdentifier[]
     */
    public function getIdentifierByType(string $type): array
    {
        return array_filter($this->identifiers, static fn (MaterialIdentifier $identifier) => $identifier->getType() === $type);
    }

    /**
     * Check if identifier exists for this material.
     *
     * @param string $type
     *   The type of id (ISBN, ISSN, ISMN, ISRC, PID)
     * @param string $identifier
     *   The identifier to check for
     *
     * @return bool
     *   TRUE if it exists else FALSE
     */
    public function hasIdentifier(string $type, string $identifier): bool
    {
        $ids = $this->getIdentifierByType($type);
        foreach ($ids as $id) {
            if ($id->getId() === $identifier) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all identifiers for this material.
     *
     * @param MaterialIdentifier[] $identifiers
     *
     * @return $this
     */
    public function setIdentifiers(array $identifiers): self
    {
        $this->identifiers = $identifiers;

        return $this;
    }

    /**
     * Add single identifier.
     *
     * @param string $type
     *   The identifier type (ISBN, ISSN, ISMN, ISRC, PID)
     * @param string $id
     *   The identifier
     *
     * @return $this
     *
     * @throws \ReflectionException
     * @throws MaterialTypeException
     */
    public function addIdentifier(string $type, string $id): self
    {
        if (!$this->hasIdentifier($type, $id)) {
            $this->identifiers[] = new MaterialIdentifier($type, $id);
        }

        return $this;
    }

    /**
     * Check if this was a zero-hit-object.
     *
     * @return bool
     *   TRUE if no identifiers was found else FALSE
     */
    public function isEmpty(): bool
    {
        return empty($this->identifiers);
    }
}
