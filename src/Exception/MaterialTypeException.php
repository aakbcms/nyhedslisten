<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
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
     *
     * @param string         $message      [optional] The Exception message to throw
     * @param int            $code         [optional] The Exception code
     * @param Throwable|null $previous     [optional] The previous throwable used for the exception chaining
     * @param string         $materialType [optional] The type of material
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
    public function getMaterialType()
    {
        return $this->materialType;
    }
}
