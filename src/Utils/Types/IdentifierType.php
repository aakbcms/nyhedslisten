<?php

/**
 * This file is part of aakbcms/nyhedslisten.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Utils\Types;

class IdentifierType
{
    final public const PID = 'pid';
    final public const ISBN = 'isbn';
    final public const ISSN = 'issn';
    final public const ISMN = 'ismn';
    final public const ISRC = 'isrc';
    final public const FAUST = 'faust';

    /**
     * Get array of all defined identifier types.
     *
     * @return array
     *   An array of known identifiers. Uppercase identifier name in key, lower case identifier in value.
     */
    public static function getTypeList(): array
    {
        $oClass = new \ReflectionClass(self::class);

        return $oClass->getConstants();
    }
}
