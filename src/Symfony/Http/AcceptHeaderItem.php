<?php

namespace Src\Symfony\Http;

class AcceptHeaderItem
{
    protected float $quality = 1.0;
    protected int $index = 0;
    protected array $attributes = [];
    public function __construct(
        protected string $value,
        array $attributes = []
    ) {
        $this->setAttributes($attributes);
    }
    public static function fromString(?string $itemValue): static
    {
        $parts = HeaderUtils::split($itemValue ?? '', ';=');

        $part = array_shift($parts);
        $attributes = HeaderUtils::combine($parts);

        return new self($part[0], $attributes);
    }
    public function __toString(): string
    {
        $string = $this->value . ($this->quality < 1 ? ';q=' . $this->quality : '');
        if (\count($this->attributes) > 0) {
            $string .= '; ' . HeaderUtils::toString($this->attributes, ';');
        }

        return $string;
    }
    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
    public function getValue(): string
    {
        return $this->value;
    }
    public function setQuality(float $quality): static
    {
        $this->quality = $quality;

        return $this;
    }
    public function getQuality(): float
    {
        return $this->quality;
    }
    public function setIndex(int $index): static
    {
        $this->index = $index;

        return $this;
    }
    public function getIndex(): int
    {
        return $this->index;
    }
    public function hasAttribute(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
    }
    public function setAttribute(string $name, string $value): static
    {
        if ($name == "q") {
            $this->quality = (float) $value;
        } else {
            $this->attributes[$name] = $value;
        }

        return $this;
    }
}
