<?php

namespace Bahramn\EcdIpg\Traits;

use Illuminate\Support\Facades\Log;

trait Loggable
{
    protected function log(string $message, array $context = [], bool $enabled = true): void
    {
        if ($enabled) {
            Log::info($message, $context);
        }
    }
}
