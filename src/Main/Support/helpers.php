<?php

use Carbon\Carbon;

if (! function_exists('class_basename')) {
    function class_basename($class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}
if (! function_exists('class_uses_recursive')) {
    function class_uses_recursive(string $class): array
    {
        $results = [];

        $classes = array_reverse(class_parents($class) ?: []) + [$class => $class];

        foreach ($classes as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}
if (! function_exists('trait_uses_recursive')) {
    function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}
if (! function_exists('now')) {
    function now(): Carbon
    {
        return Carbon::now();
    }
}
