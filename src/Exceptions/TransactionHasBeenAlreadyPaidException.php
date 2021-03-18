<?php

namespace Bahramn\EcdIpg\Exceptions;

use Bahramn\EcdIpg\Models\Transaction;

class TransactionHasBeenAlreadyPaidException extends \Exception
{
    private Transaction $transaction;

    /**
     * TransactionHasBeenAlreadyPaid constructor.
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
