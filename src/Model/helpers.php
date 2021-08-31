<?php

declare(strict_types=1);

if (!function_exists('array_is_multidimensional')) {

    /**
     * Проверяет является ли массив многомерным
     *
     * @param mixed[] $array
     */
    function array_is_multidimensional(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }

}

if (!function_exists('is_array_of_classes')) {

    /**
     * @param mixed[] $array
     */
    function is_array_of_classes(array $array, string $class): bool
    {
        return count(array_filter(
            $array,
            static fn ($entry) => $entry instanceof $class
        )) === count($array);
    }

}

if (!function_exists('str_replace_first')) {

    /**
     * Replaces the first occurrence of a substring in a string with the specified string.
     */
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        return preg_replace('/' . preg_quote($search, '/') . '/', $replace, $subject, 1);
    }

}

if (!function_exists('is_sequential_array')) {

    /**
     * Checks an array is sequential.
     *
     * @param mixed[] $array
     */
    function is_sequential_array(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

}

if (!function_exists('is_associative_array')) {

    /**
     * Checks an array is associative.
     *
     * @param mixed[] $array
     */
    function is_associative_array(array $array): bool
    {
        return !is_sequential_array($array);
    }

}
