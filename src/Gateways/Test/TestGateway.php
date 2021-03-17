<?php

namespace Bahramn\EcdIpg\Gateways\Test;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Gateways\AbstractGateway;
use Bahramn\EcdIpg\Payment\PaymentGatewayInterface;
use Bahramn\EcdIpg\Support\Interfaces\ConfirmationResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\ReverseResultInterface;

/**
 * @package Bahramn\EcdIpg\Gateways\Test
 */
class TestGateway extends AbstractGateway
{

    public function initPayment(): InitializeResultInterface
    {
        // TODO: Implement initPayment() method.
    }

    public function confirm(): ConfirmationResultInterface
    {
        // TODO: Implement confirm() method.
    }

    public function reverse(string $uuid, string $token): ReverseResultInterface
    {
        // TODO: Implement reverse() method.
    }
}
