<?php

namespace Src\Main\Routing;

class RouteGroup
{
    public static function merge(array $old, array $new): array
    {
        if (isset($new['controller'])) {
            unset($old['controller']);
        }

        $result = [];

        $result["middleware"] = array_merge($old["middleware"], $new["middleware"]);
        $result["name"] = static::formatName($old, $new);
        $result["prefix"] = static::formatPrefix($old, $new);

        return $result;
    }
    protected static function formatPrefix(array $old, array $new): string
    {
        $oldPrefix = $old["prefix"] ?? "";

        $newPrefix = $new["prefix"] ?? "";

        $prefix = self::trimPrefix($oldPrefix, $newPrefix);

        return $prefix == "/" ? "" : $prefix;
    }
    protected static function formatName(array $old, array $new): string
    {
        $oldName = $old["name"] ?? "";

        $newName = $new["name"] ?? "";

        return $oldName . $newName;
    }
    protected static function trimPrefix(string $oldPrefix, string $newPrefix): string
    {
        return trim($oldPrefix, '/') . "/" . trim($newPrefix, '/');
    }
}
