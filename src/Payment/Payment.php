<?php

namespace Bahramn\EcdIpg\Payment;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentGatewayException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyFailedException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyPaidException;
use Bahramn\EcdIpg\Models\Transaction;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Bahramn\EcdIpg\Traits\Payable;
use Illuminate\Database\Eloquent\Model;

interface Payment
{
    /**
     * Set the gateway name, if not set the default gateway in config will instantiate
     * will throws exception if not exists or active.
     *
     * @param string $gatewayName
     * @return $this
     * @throws PaymentGatewayException
     */
    public function setGatewayName(string $gatewayName): self;

    /**
     * Set model which has Payable trait.
     *
     * @param Model|Payable $model
     * @return $this
     */
    public function setPayable(Model $model): self;

    /**
     * Create transaction and.
     *
     * @param PaymentInitData $paymentInitData
     * @return Payment
     */
    public function readyInitialize(PaymentInitData $paymentInitData): self;

    /**
     * @return InitializeResultInterface
     * @throws PaymentGatewayException
     */
    public function initialize(): InitializeResultInterface;

    /**
     * @param string $transactionUuid
     * @throws PaymentGatewayException|PaymentConfirmationFailedException
     * @throws TransactionHasBeenAlreadyPaidException|TransactionHasBeenAlreadyFailedException
     * @return PaymentManager
     */
    public function readyConfirmation(string $transactionUuid): self;

    /**
     * @return Transaction
     */
    public function confirm(): Transaction;
}
