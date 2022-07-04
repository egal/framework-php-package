<?php

if (!function_exists('ddl')) {

    function ddl(...$vars): never
    {
        $backtraceLog = debug_backtrace()[0];
        $pathFromRoot = str_replace(base_path() . '/', '', $backtraceLog['file']);
        $line = $backtraceLog['line'];

        dd(['file' => $pathFromRoot, 'line' => $line, 'count_vars' => count($vars)], ...$vars);
    }

}
