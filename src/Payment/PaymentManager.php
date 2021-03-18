<?php

namespace Bahramn\EcdIpg\Payment;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\DTOs\PaymentVerifyData;
use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentGatewayException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyFailedException;
use Bahramn\EcdIpg\Exceptions\TransactionHasBeenAlreadyPaidException;
use Bahramn\EcdIpg\Gateways\AbstractGateway;
use Bahramn\EcdIpg\Models\Transaction;
use Bahramn\EcdIpg\Repositories\TransactionRepository;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Bahramn\EcdIpg\Traits\Payable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentManager implements Payment
{
    /**
     * @var Model|Payable
     */
    private Model $payable;
    private ?string $gatewayName = null;
    private PaymentGatewayFactory $gatewayFactory;
    private TransactionRepository $repository;
    private AbstractGateway $gateway;
    private Transaction $transaction;

    public function __construct(PaymentGatewayFactory $gatewayFactory, TransactionRepository $repository)
    {
        $this->gatewayFactory = $gatewayFactory;
        $this->repository = $repository;
    }

    /**
     * @param string $gatewayName
     * @return $this
     * @throws PaymentGatewayException
     */
    public function setGatewayName(string $gatewayName): self
    {
        if (!in_array($gatewayName, $this->gatewayFactory->getGatewayNames())) {
            throw new PaymentGatewayException(__('ecd-ipg::messages.invalid_gateway'));
        }
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    /**
     * @param Model|Payable $model
     * @return $this
     */
    public function setPayable(Model $model): self
    {
        $this->payable = $model;

        return $this;
    }

    /**
     * @param PaymentInitData $paymentInitData
     * @return PaymentManager
     * @throws PaymentGatewayException
     */
    public function readyInitialize(PaymentInitData $paymentInitData): self
    {
        $this->prepareGateway();
        $paymentInitData->setGateway($this->gatewayName);
        $this->transaction = $this->repository->createPayableTransaction($this->payable, $paymentInitData);
        $paymentInitData->setUniqueId($this->transaction->uuid);
        $this->gateway->setPaymentInitData($paymentInitData);

        return $this;
    }

    /**
     * @return InitializeResultInterface
     * @throws PaymentGatewayException
     */
    public function initialize(): InitializeResultInterface
    {
        try {
            $initialized = $this->gateway->initPayment();
            $this->repository->updateStatusPending($this->transaction, $initialized->getGateWayTransactionToken());

            return $initialized;
        } catch (PaymentInitializeFailedException $exception) {
            $this->repository->markInitialFailed(
                $this->transaction,
                $exception->getErrorMessage(),
                $exception->getContext()
            );
            throw new PaymentGatewayException($exception->getErrorMessage());
        }
    }

    /**
     * @param string $transactionUuid
     * @return PaymentManager
     * @throws PaymentGatewayException|PaymentConfirmationFailedException
     * @throws TransactionHasBeenAlreadyPaidException|TransactionHasBeenAlreadyFailedException
     */
    public function readyConfirmation(string $transactionUuid): self
    {
        $this->prepareGateway();
        try {
            $this->transaction = $this->repository->getByUuid($transactionUuid);
            $this->validateTransactionBeforeConfirm();
            $verifyData = (new PaymentVerifyData)
                ->setUuid($this->transaction->uuid)
                ->setAmount($this->transaction->amount)
                ->setGateway($this->transaction->gateway)
                ->setCurrency($this->transaction->currency);
            $this->gateway->setPaymentVerifyData($verifyData);

            return $this;
        } catch (ModelNotFoundException $exception) {
            throw new PaymentConfirmationFailedException(__('ecd-ipg::messages.transaction_not_found'));
        }
    }

    /**
     * @return Transaction
     */
    public function confirm(): Transaction
    {
        try {
            $confirmResult = $this->gateway->confirm();
            $this->repository->updateConfirmation($this->transaction, $confirmResult);
        } catch (PaymentConfirmationFailedException $exception) {
            $this->repository->markConfirmFailed(
                $this->transaction,
                $exception->getErrorMessage(),
                $exception->getContext()
            );
            if ($this->transaction->token) {
                $reverseData = $this->gateway->reverse($this->transaction->uuid, $this->transaction->token);
                $this->repository->updateReverseStatus($this->transaction, $reverseData);
            }
        }

        return $this->transaction;
    }

    /**
     * @throws PaymentGatewayException
     */
    private function prepareGateway(): void
    {
        if (is_null($this->gatewayName)) {
            $this->gatewayName = $this->gatewayFactory->getDefaultGatewayName();
        }
        try {
            $this->gateway = $this->gatewayFactory->getInstance($this->gatewayName);
        } catch (\Exception $exception) {
            throw new PaymentGatewayException(__('ecd-ipg::messages.gateway_instantiate_failed'));
        }
        if (!$this->gateway->isActive()) {
            throw new PaymentGatewayException(__('ecd-ipg::messages.inactive_gateway'));
        }
    }

    /**
     * @throws PaymentConfirmationFailedException
     * @throws TransactionHasBeenAlreadyPaidException
     * @throws TransactionHasBeenAlreadyFailedException
     */
    private function validateTransactionBeforeConfirm(): void
    {
        if ($this->transaction->status == Transaction::STATUS_NEW) {
            throw new PaymentConfirmationFailedException(__('ecd-ipg::messages.transaction_not_initialized'));
        }
        if ($this->transaction->status == Transaction::STATUS_PAID) {
            throw new TransactionHasBeenAlreadyPaidException($this->transaction);
        }
        if (in_array($this->transaction->status, [
            Transaction::STATUS_INITIALIZATION_FAILED,
            Transaction::STATUS_VERIFICATION_FAILED,
            Transaction::STATUS_CANCELED,
        ])) {
            throw new TransactionHasBeenAlreadyFailedException($this->transaction);
        }
    }
}
