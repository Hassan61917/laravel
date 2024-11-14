<?php

namespace Src\Symfony\Console\Helpers;

interface IHelper
{
    public function setHelperSet(?HelperSet $helperSet): void;
    public function getHelperSet(): ?HelperSet;
    public function getName(): string;
}
