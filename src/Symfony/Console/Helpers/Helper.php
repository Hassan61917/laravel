<?php

namespace Src\Symfony\Console\Helpers;

abstract class Helper implements IHelper
{
    protected ?HelperSet $helperSet = null;
    public function setHelperSet(?HelperSet $helperSet): void
    {
        $this->helperSet = $helperSet;
    }
    public function getHelperSet(): ?HelperSet
    {
        return $this->helperSet;
    }
}
