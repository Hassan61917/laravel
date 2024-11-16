<?php

namespace Src\Main\Support;

interface IMessageProvider
{
    public function getMessageBag(): MessageBag;
}
