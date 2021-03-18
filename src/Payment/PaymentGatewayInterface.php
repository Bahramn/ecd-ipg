<?php

namespace Bahramn\EcdIpg\Payment;

use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Support\Interfaces\ConfirmationResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\ReverseResultInterface;

interface PaymentGatewayInterface
{
    /**
     * Determine the gateway is active or not.
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return InitializeResultInterface
     * @throws PaymentInitializeFailedException
     */
    public function initPayment(): InitializeResultInterface;

    /**
     * @return ConfirmationResultInterface
     * @throws PaymentConfirmationFailedException
     */
    public function confirm(): ConfirmationResultInterface;

    /**
     * @param string $uuid
     * @param string $token
     * @return ReverseResultInterface
     */
    public function reverse(string $uuid, string $token): ReverseResultInterface;
}
