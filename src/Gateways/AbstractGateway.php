<?php

namespace Bahramn\EcdIpg\Gateways;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\DTOs\PaymentVerifyData;
use Bahramn\EcdIpg\Payment\PaymentGatewayInterface;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    protected PaymentInitData $paymentInitData;
    protected PaymentVerifyData $paymentVerifyData;
    protected GatewayConfigData $config;

    /**
     * Get Concrete gateway activation.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->config->active;
    }

    /**
     * @param PaymentInitData $paymentInitData
     * @return AbstractGateway
     */
    public function setPaymentInitData(PaymentInitData $paymentInitData): self
    {
        $this->paymentInitData = $paymentInitData;

        return $this;
    }

    /**
     * @param PaymentVerifyData $paymentVerifyData
     * @return $this
     */
    public function setPaymentVerifyData(PaymentVerifyData $paymentVerifyData): self
    {
        $this->paymentVerifyData = $paymentVerifyData;

        return $this;
    }

    /**
     * @param GatewayConfigData $configData
     * @return $this
     */
    public function setConfig(GatewayConfigData $configData): self
    {
        $this->config = $configData;

        return $this;
    }

    /**
     * @return GatewayConfigData
     */
    public function getConfig(): GatewayConfigData
    {
        return $this->config;
    }
}
