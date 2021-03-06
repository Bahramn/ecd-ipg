<?php

namespace Bahramn\EcdIpg\Exceptions;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Log\Logger;

class PaymentInitializeFailedException extends \Exception
{
    private string $errorMessage;
    private array $context = [];
    private Logger $logger;

    /**
     * PaymentInitializeFailedException constructor.
     *
     * @param string $message
     * @param array $context
     * @throws BindingResolutionException
     */
    public function __construct($message = '', array $context = [])
    {
        $this->errorMessage = $message;
        $this->context = $context;
        $this->logger = app()->make(Logger::class);
        parent::__construct($message);
    }

    public function report(): void
    {
        $this->logger->debug($this->errorMessage, $this->context);
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
