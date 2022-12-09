<?php

/**
 * @file
 */

namespace App\Exception;

use Throwable;

/**
 * Class MaterialTypeException.
 */
class MaterialTypeException extends \Exception
{
    private $materialType;

    /**
     * MaterialTypeException constructor.
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null, $materialType = 'Unknown')
    {
        parent::__construct($message, $code, $previous);

        $this->materialType = $materialType;
    }

    /**
     * Set the type of material.
     *
     * @param string $materialType
     */
    public function setMaterialType(string $materialType): void
    {
        $this->materialType = $materialType;
    }

    /**
     * Get the type of material.
     *
     * @return string
     */
    public function getMaterialType(): string
    {
        return $this->materialType;
    }
}
