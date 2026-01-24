<?php

namespace App\Enum;

interface BaseEnumInterface
{
    public static function fromLabel(string $label): self;
}
