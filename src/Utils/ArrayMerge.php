<?php

/**
 * @file
 */

namespace App\Utils;

/**
 * Class ArrayMerge.
 *
 * PHPs array_merge() performance is not always optimal:
 * https://stackoverflow.com/questions/23348339/optimizing-array-merge-operation
 */
class ArrayMerge
{
    /**
     * Merge from one array into another by reference.
     *
     * @param array $mergeTo
     *                         The array to merge to
     * @param array $mergeFrom
     *                         The array to merge from
     */
    public static function mergeArraysByReference(array &$mergeTo, array &$mergeFrom): void
    {
        foreach ($mergeFrom as $i) {
            $mergeTo[] = $i;
        }
    }
}
