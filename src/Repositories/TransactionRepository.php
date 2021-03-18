<?php

namespace Bahramn\EcdIpg\Repositories;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\Models\Transaction;
use Bahramn\EcdIpg\Support\Interfaces\ConfirmationResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\ReverseResultInterface;
use Bahramn\EcdIpg\Support\Repositories\BaseRepository;
use Bahramn\EcdIpg\Traits\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class TransactionRepository extends BaseRepository
{
    /**
     * @param string $transactionUuid
     * @return Transaction|Model
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $transactionUuid): Transaction
    {
        return $this->getModel()->where('uuid', $transactionUuid)->firstOrFail();
    }

    /**
     * @param Model|Payable $model
     * @param PaymentInitData $initPaymentData
     * @return Transaction
     */
    public function createPayableTransaction(Model $model, PaymentInitData $initPaymentData): Transaction
    {
        return $model->transactions()->create([
            'status' => Transaction::STATUS_NEW,
            'uuid' => Str::uuid()->toString(),
            'amount' => $initPaymentData->getAmount(),
            'payer_nid' => $initPaymentData->getNid(),
            'gateway' => $initPaymentData->getGateway(),
            'currency' => $initPaymentData->getCurrency(),
            'payer_mobile' => $initPaymentData->getMobile(),
            'description' => $initPaymentData->getDescription(),
        ]);
    }

    public function updateConfirmation(Transaction $transaction, ConfirmationResultInterface $result): Transaction
    {
        $transaction->status = $result->isSucceed() ? Transaction::STATUS_PAID : Transaction::STATUS_VERIFICATION_FAILED;
        $transaction->message = $result->getMessage();
        $transaction->stan = $result->getStan();
        $transaction->rrn = $result->getRrn();
        $transaction->save();

        return $transaction;
    }

    public function markInitialFailed(Transaction $transaction, string $message, array $context): Transaction
    {
        $transaction->status = Transaction::STATUS_INITIALIZATION_FAILED;
        $transaction->message = $message;
        $transaction->requests = $context;
        $transaction->save();

        return $transaction;
    }

    public function markConfirmFailed(Transaction $transaction, string $message, array $context): Transaction
    {
        $transaction->status = Transaction::STATUS_INITIALIZATION_FAILED;
        $transaction->message = $message;
        $transaction->requests = $context;
        $transaction->save();

        return $transaction;
    }

    public function updateStatusPending(Transaction $transaction, string $token): Transaction
    {
        $transaction->status = Transaction::STATUS_PENDING;
        $transaction->token = $token;
        $transaction->save();

        return $transaction;
    }

    public function updateReverseStatus(Transaction $transaction, ReverseResultInterface $result): Transaction
    {
        $transaction->status = $result->hasReversed() ? Transaction::REVERSED : $transaction->status;
        $transaction->message = $result->getMessage();
        $transaction->save();

        return $transaction;
    }

    protected function getModelClass(): string
    {
        return Transaction::class;
    }
}
