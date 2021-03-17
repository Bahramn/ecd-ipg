<?php

namespace Bahramn\EcdIpg\Support\Interfaces;

interface ConfirmationResultInterface
{
    public function isSucceed(): bool;

    public function getRrn(): string;

    public function getStan(): string;

    public function getMessage(): string;
}
