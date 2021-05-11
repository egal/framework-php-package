<?php

if (!function_exists('words_to_dot_case')) {

    /**
     * Преобразования слов в Dot case
     *
     * Пример:
     * $res = words_to_dot_case(1, 2, 3, 4);
     * var_dump($res);
     * Вывод:
     * string(1.2.3.4)
     *
     * @param mixed ...$words
     * @return false|string
     */
    function words_to_dot_case(...$words)
    {
        $result = '';
        foreach ($words as $word) {
            $result .= $word;
            $result .= '.';
        }
        return substr($result, 0, -1);
    }

}

if (!function_exists('words_to_snake_case')) {

    /**
     * Преобразования слов в Snake case
     *
     * Пример:
     * $res = words_to_snake_case(1, 2, 3, 4);
     * var_dump($res);
     * Вывод:
     * string(1_2_3_4)
     *
     * @param mixed ...$words
     * @return false|string
     */
    function words_to_snake_case(...$words)
    {
        $result = '';
        foreach ($words as $word) {
            $result .= $word;
            $result .= '_';
        }
        return substr($result, 0, -1);
    }

}

if (!function_exists('words_to_separated_lover_case')) {

    /**
     * Преобразования слов в Snake case
     *
     * Пример:
     * $res = words_to_snake_case(1, 2, 3, 4);
     * var_dump($res);
     * Вывод:
     * string(1_2_3_4)
     *
     * @param mixed ...$words
     * @return false|string
     */
    function words_to_separated_lover_case(...$words)
    {
        $result = '';
        foreach ($words as $word) {
            $result .= strtolower($word);
            $result .= ' ';
        }
        return substr($result, 0, -1);
    }

}

if (!function_exists('process_exists')) {

    /**
     * @param string|null $file
     * @return bool
     */
    function process_exists(string $file = null): bool
    {
        $exists = false;
        $file = $file ?: __FILE__;

        // Проверьте, находится ли файл в списке процессов
        exec("ps -C $file -o pid=", $pids);
        if (count($pids) > 1) {
            $exists = true;
        }
        return $exists;
    }

}

if (!function_exists('cli_process_title_like')) {

    /**
     * @param $string
     * @return bool
     */
    function cli_process_title_like($string): bool
    {
        return str_contains(cli_get_process_title(), $string);
    }

}

if (!function_exists('get_class_short_name')) {

    /**
     * @param $class
     * @return string
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


