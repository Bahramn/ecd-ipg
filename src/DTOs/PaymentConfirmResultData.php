<?php

namespace Bahramn\EcdIpg\DTOs;

use Bahramn\EcdIpg\Support\Interfaces\ConfirmationResultInterface;

/**
 * @package Bahramn\EcdIpg\DTOs
 */
class PaymentConfirmResultData implements ConfirmationResultInterface
{
    private bool $status;
    private string $message;
    private ?string $rrn;
    private ?string $stan;
    private bool $shouldConfirmService;


    /**
     * @param bool $shouldConfirmService
     */
    public function setShouldConfirmService(bool $shouldConfirmService): void
    {
        $this->shouldConfirmService = $shouldConfirmService;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getRrn(): string
    {
        return $this->rrn;
    }


    /**
     * @param string|null $rrn
     * @return $this
     */
    public function setRrn(?string $rrn): self
    {
        $this->rrn = $rrn;
        return $this;
    }

    /**
     * @return string
     */
    public function getStan(): string
    {
        return $this->stan;
    }


    /**
     * @param string|null $stan
     * @return $this
     */
    public function setStan(?string $stan): self
    {
        $this->stan = $stan;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }


    public function isSucceed(): bool
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->isSucceed() ? "success" : $this->message;
    }

    public function shouldConfirmService(): bool
    {
        return $this->isSucceed() && $this->shouldConfirmService;
    }
}
