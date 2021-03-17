<?php

namespace Bahramn\EcdIpg\DTOs;

use Bahramn\EcdIpg\Support\Interfaces\ReverseResultInterface;

/**
 * @package Bahramn\EcdIpg\DTOs
 */
class PaymentReverseResultData implements ReverseResultInterface
{
    private bool $success;
    private string $message;


    public function hasReversed(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): self
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

}
