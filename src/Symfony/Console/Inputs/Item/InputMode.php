<?php

namespace Src\Symfony\Console\Inputs\Item;

enum InputMode
{
    case None;
    case Required;
    case Optional;
}
