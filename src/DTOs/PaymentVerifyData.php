<?php

namespace Bahramn\EcdIpg\DTOs;

/**
 * @package Bahramn\EcdIpg\DTOs
 */
class PaymentVerifyData
{
    public float $amount;
    public string $uuid;
    public string $gateway;
    public string $currency;

    /**
     * @param float $amount
     * @return PaymentVerifyData
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return (int) $this->amount;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return PaymentVerifyData
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return PaymentVerifyData
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getGateway(): string
    {
        return $this->gateway;
    }

    /**
     * @param string $gateway
     * @return PaymentVerifyData
     */
    public function setGateway(string $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
    }
}
