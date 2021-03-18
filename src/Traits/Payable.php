<?php

namespace Bahramn\EcdIpg\Traits;

use Bahramn\EcdIpg\Models\Transaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Payable
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }

    abstract public function amount(): float;

    abstract public function currency(): string;

    abstract public function uniqueId(): string;
}
