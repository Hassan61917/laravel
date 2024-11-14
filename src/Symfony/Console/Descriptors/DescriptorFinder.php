<?php

namespace Src\Symfony\Console\Descriptors;

use Src\Symfony\Console\Outputs\IConsoleOutput;

class DescriptorFinder
{
    protected array $descriptors = [];
    public function __construct()
    {
        $this->bootDescriptors();
    }
    public function addDescriptor(string $format, IDescriptor $descriptor): void
    {
        if ($this->hasDescriptor($format)) {
            return;
        }

        $this->descriptors[$format] = $descriptor;
    }
    public function describe(Describable $describable, IConsoleOutput $output, array $options = []): void
    {
        $options = array_merge([
            'raw_text' => false,
            "format" => "txt"
        ], $options);

        $describable->describe(
            $this->find($options["format"]),
            $output,
            $options
        );
    }
    protected function find(string $format): IDescriptor
    {
        return $this->descriptors[$format];
    }
    protected function bootDescriptors(): void
    {
        $this->addDescriptor("txt", new TextDescriptor());
    }
    protected function hasDescriptor(string $format): bool
    {
        return isset($this->descriptors[$format]);
    }
}
