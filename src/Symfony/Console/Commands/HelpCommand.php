<?php

namespace Src\Symfony\Console\Commands;

use Src\Symfony\Console\Descriptors\DescriptorFinder;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputItem;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class HelpCommand extends Command
{
    protected Command $command;
    public function __construct()
    {
        parent::__construct("Help");
    }
    public function setCommand(Command $command): static
    {
        $this->command = $command;

        return $this;
    }
    protected function execute(IConsoleInput $input, IConsoleOutput $output): int
    {
        $commandName = $input->getArgument('command_name');

        $this->command ??= $this->getApplication()->findCommand($commandName);

        $helper = new DescriptorFinder();

        $helper->describe($this->command, $output, ['format' => $input->getOption('format')]);

        unset($this->command);

        return 0;
    }
    protected function configure(): void
    {
        $this
            ->setItem($this->createItem())
            ->setDescription("Display help for a command")
            ->setHelp($this->getHelpText());
    }
    protected function createItem(): InputItem
    {
        return new InputItem(
            [new InputArgument('command_name', 'The command name', InputMode::Optional, 'help')],
            [new InputOption('format', "f", 'The output format', InputMode::Optional, 'txt')]
        );
    }
    protected function getHelpText(): string
    {
        return 'The %command.name% command displays help for a given command:

  %command.full_name% list

You can also output the help in other formats by using the --format option:

To display the list of available commands, please use the list command.';
    }
}
