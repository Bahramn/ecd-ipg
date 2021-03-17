<?php

namespace Bahramn\EcdIpg\Support\Interfaces;

interface ReverseResultInterface
{
    public function hasReversed(): bool;

    public function getMessage(): string;
}
