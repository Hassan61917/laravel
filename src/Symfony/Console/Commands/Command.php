<?php

namespace Src\Symfony\Console\Commands;

use InvalidArgumentException;
use LogicException;
use Src\Symfony\Console\Application;
use Src\Symfony\Console\Descriptors\Describable;
use Src\Symfony\Console\Descriptors\IDescriptor;
use Src\Symfony\Console\Helpers\HelperSet;
use Src\Symfony\Console\Helpers\IHelper;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputItem;
use Src\Symfony\Console\Inputs\Item\InputOption;
use Src\Symfony\Console\Outputs\IConsoleOutput;

abstract class Command implements Describable
{
    protected string $name;
    protected string $description;
    protected string $help = "";
    protected bool $enable = true;
    protected bool $hidden = false;
    protected array $aliases = [];
    protected InputItem $item;
    protected ?Application $app;
    protected HelperSet $helperSet;
    public function __construct(
        string $name,
    ) {
        $this->item = new InputItem();
        $this->setName($name);
        $this->configure();
    }
    public function setName(?string $name): static
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
    public function setHelp(string $help): static
    {
        $this->help = $help;

        return $this;
    }
    public function enable(): static
    {
        $this->enable = true;

        return $this;
    }
    public function disable(): static
    {
        $this->enable = false;

        return $this;
    }
    public function show(): static
    {
        $this->hidden = true;

        return $this;
    }
    public function hide(): static
    {
        $this->hidden = false;

        return $this;
    }
    public function setItem(InputItem $item): static
    {
        $this->item = $item;

        return $this;
    }
    public function setApplication(?Application $app): static
    {
        $this->app = $app;

        $this->setHelperSet($app->getHelperSet());

        return $this;
    }
    public function setHelperSet(HelperSet $helperSet): static
    {
        $this->helperSet = $helperSet;

        return $this;
    }
    public function addArgument(InputArgument $argument): static
    {
        $this->item->addArgument($argument);

        return $this;
    }
    public function addOption(InputOption $option): static
    {
        $this->item->addOption($option);

        return $this;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function getHelp(): string
    {
        return $this->help;
    }
    public function isEnable(): bool
    {
        return $this->enable;
    }
    public function isHidden(): bool
    {
        return $this->hidden;
    }
    public function getAliases(): array
    {
        return $this->aliases;
    }
    public function getItem(): InputItem
    {
        return $this->item;
    }
    public function getApplication(): ?Application
    {
        return $this->app;
    }
    public function getHelperSet(): HelperSet
    {
        return $this->helperSet;
    }
    public function getHelper(string $name): IHelper
    {
        if (!isset($this->helperSet)) {
            throw new LogicException("Cannot retrieve helper {$name} because there is no HelperSet defined.");
        }

        return $this->helperSet->get($name);
    }
    public function run(IConsoleInput $input, IConsoleOutput $output): int
    {
        $this->mergeWithAppItem();

        $input->bind($this->getItem());

        return $this->execute($input, $output);
    }
    public function getSynopsis(): string
    {
        return sprintf('%s %s', $this->name, $this->item->getSynopsis());
    }
    public function getProcessedHelp(): string
    {
        $name = $this->name;

        $placeholders = [
            '%command.name%',
            '%command.full_name%',
        ];

        $replacements = [$name, $_SERVER['PHP_SELF'] . ' ' . $name,];

        return str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }
    public function describe(IDescriptor $descriptor, IConsoleOutput $output, array $options = []): void
    {
        $descriptor->describeCommand($this, $output, $options);
    }
    protected function mergeWithAppItem(): void
    {
        if (!$this->app) {
            return;
        }
        $item = $this->app->getInputItem();
        $this->item->addArguments(...$item->getArguments());
        $this->item->addOptions(...$item->getOptions());
    }
    protected function validateName(string $name): void
    {
        if (!preg_match('/^[^:]++(:[^:]++)*$/', $name)) {
            throw new InvalidArgumentException("Command name $name is invalid.");
        }
    }
    protected function configure(): void {}
    protected abstract function execute(IConsoleInput $input, IConsoleOutput $output): int;
}
