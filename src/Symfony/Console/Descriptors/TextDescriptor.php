<?php

namespace Src\Symfony\Console\Descriptors;

use Src\Symfony\Console\Application;
use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Formatters\OutputFormatter;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class TextDescriptor implements IDescriptor
{
    protected IConsoleOutput $output;
    public function describeCommand(Command $command, IConsoleOutput $output, array $options = []): void
    {
        $this->output = $output;

        $description = $command->getDescription();

        if ($description) {
            $this->writeText('Description:', $options);
            $this->writeText('  ' . $description);
            $this->writeText("\n\n");
        }

        $this->writeText('Usage:', $options);

        foreach (array_merge([$command->getSynopsis()], $command->getAliases()) as $usage) {
            $this->writeText("\n");
            $this->writeText('  ' . OutputFormatter::escape($usage), $options);
        }

        $this->writeText("\n");

        $help = $command->getProcessedHelp();

        if ($help && $help !== $description) {
            $this->writeText("\n");
            $this->writeText('Help:', $options);
            $this->writeText("\n");
            $this->writeText('  ' . str_replace("\n", "\n  ", $help), $options);
            $this->writeText("\n");
        }
    }
    public function describeApplication(Application $application, IConsoleOutput $output, array $options = []): void
    {
        $this->output = $output;

        $this->writeText("Available commands:\n\n", $options);

        $commands = collect($application->allCommands($options["namespace"]))
            ->sortBy(fn($command) => $command->getName(), \SORT_STRING)
            ->all();

        foreach ($commands as $command) {
            $name = $command->getName();
            $this->writeText($command->getName() . str_repeat(" ", 25 - strlen($name)));
            $this->writeText($command->getDescription());
            $this->writeText("\n");
        }

        $this->writeText("\n");
    }
    private function writeText(string $content, array $options = []): void
    {
        $content = isset($options["raw_text"]) && $options['raw_text'] ? strip_tags($content) : $content;

        $this->output->write($content);
    }
}
