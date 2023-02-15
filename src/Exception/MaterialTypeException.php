<?php

/**
 * @file
 */

namespace App\Exception;

/**
 * Class MaterialTypeException.
 */
class MaterialTypeException extends \Exception
{
    /**
     * MaterialTypeException constructor.
     */
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null, private $materialType = 'Unknown')
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Set the type of material.
     */
    public function setMaterialType(string $materialType): void
    {
        $this->materialType = $materialType;
    }

    /**
     * Get the type of material.
     */
    public function getMaterialType(): string
    {
        return $this->materialType;
    }
}
