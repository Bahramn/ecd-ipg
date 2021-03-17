<?php

namespace Bahramn\EcdIpg\Exceptions;

use Bahramn\EcdIpg\Models\Transaction;

/**
 * @package Bahramn\EcdIpg\Exceptions
 */
class TransactionHasBeenAlreadyFailedException extends \Exception
{
    private Transaction $transaction;

    /**
     * TransactionHasBeenAlreadyFailedException constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        parent::__construct();
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }
}
