<?php

namespace Src\Main\Filesystem;

use ErrorException;
use FilesystemIterator;
use SplFileObject;
use Illuminate\Support\Collection;
use Src\Symfony\Finder\Finder;

class Filesystem
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }
    public function missing(string $path): bool
    {
        return ! $this->exists($path);
    }
    public function get(string $path): ?string
    {
        if ($this->isFile($path)) {
            $content = file_get_contents($path);
            return $content ?: null;
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }
    public function json(string $path, int $flags = 0): array
    {
        return json_decode($this->get($path), true, 512, $flags);
    }
    public function requireOnce(string $path, array $data = []): mixed
    {
        if ($this->isFile($path)) {
            $__path = $path;
            $__data = $data;

            return (static function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require_once $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }
    public function lines(string $path): Collection
    {
        if (! $this->isFile($path)) {
            throw new FileNotFoundException(
                "File does not exist at path {$path}."
            );
        }

        return Collection::make(function () use ($path) {
            $file = new SplFileObject($path);

            $file->setFlags(SplFileObject::DROP_NEW_LINE);

            while (! $file->eof()) {
                yield $file->fgets();
            }
        });
    }
    public function hash(string $path, string $algorithm = 'md5'): string
    {
        return hash_file($algorithm, $path);
    }
    public function replace(string $path, string $content, int $mode = null): void
    {
        clearstatcache(true, $path);

        $path = realpath($path) ?: $path;

        $tempPath = tempnam(dirname($path), basename($path));

        if (! is_null($mode)) {
            chmod($tempPath, $mode);
        } else {
            chmod($tempPath, 0777 - umask());
        }

        file_put_contents($tempPath, $content);

        rename($tempPath, $path);
    }
    public function prepend(string $path, string $data): bool
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }
    public function append(string $path, string $data): bool
    {
        return file_put_contents($path, $data, FILE_APPEND) != false;
    }
    public function chmod(string $path, int $mode = null): bool
    {
        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }
    public function move(string $path, string $target): string
    {
        return rename($path, $target);
    }
    public function copy(string $path, string $target): bool
    {
        return copy($path, $target);
    }
    public function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
    public function basename(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }
    public function dirname(string $path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }
    public function extension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }
    public function getRequire(string $path, array $data = []): mixed
    {
        if ($this->isFile($path)) {
            $__path = $path;
            $__data = $data;

            return (static function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false): bool
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }
    public function put(string $path, string $contents): bool
    {
        return file_put_contents($path, $contents) != false;
    }
    public function delete(string ...$paths): bool
    {
        $success = true;

        foreach ($paths as $path) {
            try {
                if (@unlink($path)) {
                    clearstatcache(false, $path);
                } else {
                    $success = false;
                }
            } catch (ErrorException) {
                $success = false;
            }
        }

        return $success;
    }
    public function deleteDirectory(string $directory, bool $preserve = false): bool
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }
        $items = new FilesystemIterator($directory);
        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            } else {
                $this->delete($item->getPathname());
            }
        }
        unset($items);
        if (! $preserve) {
            @rmdir($directory);
        }
        return true;
    }
    public function isDirectory(string $directory): bool
    {
        return is_dir($directory);
    }
    public function directories(string $directory): array
    {
        $result = [];
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                if (is_dir($directory . '/' . $item)) {
                    $result[] = $directory . '/' . $item;
                }
            }
        }
        return $result;
    }
    public function lastModified(string $path): int
    {
        return filemtime($path);
    }
    public function size(string $path): int
    {
        return filesize($path);
    }
    public function sharedGet(string $path): string
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, $this->size($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }
    public function type(string $path): string
    {
        return filetype($path);
    }
    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }
    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }
    public function isFile(string $file): bool
    {
        return is_file($file);
    }
    public function glob(string $pattern, int $flags = 0): array
    {
        return glob($pattern, $flags);
    }
    public function ensureDirectoryExists(string $path, int $mode = 0755, bool $recursive = true): void
    {
        if (! $this->isDirectory($path)) {
            $this->makeDirectory($path, $mode, $recursive);
        }
    }
    public function moveDirectory(string $from, string $to, bool $overwrite = false): bool
    {
        if ($overwrite && $this->isDirectory($to) && ! $this->deleteDirectory($to)) {
            return false;
        }

        return @rename($from, $to) === true;
    }
    public function copyDirectory(string $directory, string $destination, int $options = null): bool
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        $this->ensureDirectoryExists($destination, 0777);

        $items = new FilesystemIterator($directory, $options);

        foreach ($items as $item) {

            $target = $destination . '/' . $item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (! $this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            } elseif (! $this->copy($item->getPathname(), $target)) {
                return false;
            }
        }

        return true;
    }
    public function deleteDirectories(string $directory): bool
    {
        $allDirectories = $this->directories($directory);

        if (! empty($allDirectories)) {
            foreach ($allDirectories as $directoryName) {
                $this->deleteDirectory($directoryName);
            }

            return true;
        }

        return false;
    }
    public function cleanDirectory(string $directory): bool
    {
        return $this->deleteDirectory($directory, true);
    }
    public function files(string $directory): array
    {
        $finder = Finder::create()
            ->in($directory)
            ->depth(0);

        return iterator_to_array($finder, false);
    }
    public function allFiles(string $directory): array
    {
        $finder = Finder::create()
            ->in($directory);

        return iterator_to_array($finder, false);
    }
}
