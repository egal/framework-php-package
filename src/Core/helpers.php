<?php

declare(strict_types=1);

if (!function_exists('get_class_short_name')) {

    /**
     * @param string|object $class
     */
    function get_class_short_name($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $path = explode('\\', $class);

        return array_pop($path);
    }

}
