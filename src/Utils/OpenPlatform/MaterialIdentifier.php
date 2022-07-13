<?php

/**
 * @file
 */

namespace App\Utils\OpenPlatform;

use App\Exception\MaterialTypeException;
use App\Utils\Types\IdentifierType;

/**
 * Class MaterialIdentifier.
 */
class MaterialIdentifier
{
    private readonly string $type;

    // The valid IS types in the data well.
    private array $types = [];

    /**
     * MaterialIdentifier constructor.
     *
     * @param string $type
     *   The material type
     * @param string $id
     *   The identifier for this material
     *
     * @throws MaterialTypeException
     * @throws \ReflectionException
     */
    public function __construct(string $type, private readonly string $id)
    {
        // Build types array.
        $obj = new \ReflectionClass(IdentifierType::class);
        $this->types = array_values($obj->getConstants());

        // Validate type.
        if (!\in_array($type, $this->types)) {
            throw new MaterialTypeException('Unknown material type: '.$type, 0, null, $type);
        }

        $this->type = $type;
    }

    /**
     * Get the identifier.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the type of identifier.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
