<?php

namespace Bahramn\EcdIpg\DTOs;


/**
 * Data to initializing a payment
 * @package Bahramn\EcdIpg\DTOs
 */
class PaymentInitData
{
    private float $amount;
    private string $uuid;
    private string $gateway;
    private string $currency;
    private ?string $mobile = null;
    private ?string $nid = null;
    private ?string $description = null;

    /**
     * @param float $amount
     * @return PaymentInitData
     */
    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $uuid
     * @return PaymentInitData
     */
    public function setUniqueId(string $uuid): self
    {
        $this->uuid = $uuid;
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
     * @return string
     */
    public function getGateway(): string
    {
        return $this->gateway;
    }

    /**
     * @param string $gateway
     * @return PaymentInitData
     */
    public function setGateway(string $gateway): self
    {
        $this->gateway = $gateway;
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
     * @return PaymentInitData
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param string|null $mobile
     * @return PaymentInitData
     */
    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNid(): ?string
    {
        return $this->nid;
    }

    /**
     * @param string|null $nid
     * @return PaymentInitData
     */
    public function setNid(?string $nid): self
    {
        $this->nid = $nid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return PaymentInitData
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
