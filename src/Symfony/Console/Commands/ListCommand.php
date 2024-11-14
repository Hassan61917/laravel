<?php

namespace Src\Symfony\Console\Commands;

use Src\Symfony\Console\Descriptors\DescriptorFinder;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputItem;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class ListCommand extends Command
{
    public function __construct()
    {
        parent::__construct("List");
    }
    protected function configure(): void
    {
        $this
            ->setItem($this->createItem())
            ->setDescription('List commands')
            ->setHelp($this->getHelpText());
    }
    protected function execute(IConsoleInput $input, IConsoleOutput $output): int
    {
        $helper = new DescriptorFinder();

        $helper->describe(
            $this->getApplication(),
            $output,
            [
                'namespace' => $input->getArgument('namespace'),
                'format' => $input->getOption('format')
            ]
        );

        return 0;
    }
    protected function createItem(): InputItem
    {
        return new InputItem(
            [new InputArgument('namespace', 'The namespace name', InputMode::Optional)],
            [new InputOption('format', "f", 'The output format', InputMode::Optional, 'txt')]
        );
    }
    protected function getHelpText(): string
    {
        return "The %command.name% command lists all commands:
        
          %command.full_name%";
    }
}
