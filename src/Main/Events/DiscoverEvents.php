<?php

namespace Src\Main\Events;

use SplFileInfo;
use Src\Main\Utils\ReflectionHelper;
use Src\Main\Utils\Str;
use Src\Symfony\Finder\Finder;

class DiscoverEvents
{
    public static function within(string $path, string $basePath): array
    {
        $listeners = self::findListeners($path, $basePath);

        return self::collectEvents($listeners);
    }
    protected static function getListenerEvents(iterable $listeners, string $basePath): array
    {
        $listenerEvents = [];

        foreach ($listeners as $listener) {
            try {
                $classFile = static::classFromFile($listener, $basePath);
                $listener = new \ReflectionClass($classFile);
                if (! $listener->isInstantiable()) {
                    continue;
                }
                foreach ($listener->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->name == "handle" && isset($method->getParameters()[0])) {
                        $listenerEvents[$listener->name . '@' . $method->name] =
                            ReflectionHelper::getParameterClassName($method->getParameters()[0]);
                    }
                }
            } catch (\Exception $exception) {
                continue;
            }
        }

        return array_filter($listenerEvents);
    }
    protected static function classFromFile(SplFileInfo $file, string $basePath): string
    {
        $class = trim(Str::replaceFirst($basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        return str_replace(
            [DIRECTORY_SEPARATOR, basename(app()->path())],
            ['\\', app()->getNamespace()],
            str_replace(".php", "", $class)
        );
    }
    protected static function findListeners(string $path, string $basePath): array
    {
        return static::getListenerEvents(
            Finder::create()->in($path)->name('.php'),
            $basePath
        );
    }
    protected static function collectEvents(array $listeners): array
    {
        $discoveredEvents = [];

        foreach ($listeners as $listener => $event) {
            if (!isset($discoveredEvents[$event])) {
                $discoveredEvents[$event] = [];
            }

            $discoveredEvents[$event][] = $listener;
        }

        return $discoveredEvents;
    }
}
