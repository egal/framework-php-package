<?php

if (!function_exists('array_is_multidimensional')) {

    /**
     * Проверяет является ли массив многомерным
     *
     * @param array $array
     * @return bool
     */
    function array_is_multidimensional(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }

}

if (!function_exists('is_array_of_classes')) {

    function is_array_of_classes(array $array, string $class): bool
    {
        return count(array_filter($array, function ($entry) use ($class) {
                return $entry instanceof $class;
            })) === count($array);
    }

}

if (!function_exists('str_replace_first')) {

    /**
     * Заменяет первое вхождение подстроки в строке на указанную строку
     *
     * @param $search
     * @param $replace
     * @param $subject
     * @return string|string[]|null
     */
    function str_replace_first($search, $replace, $subject)
    {
        $search = '/' . preg_quote($search, '/') . '/';
        return preg_replace($search, $replace, $subject, 1);
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
