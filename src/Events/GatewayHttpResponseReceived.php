<?php

namespace Bahramn\EcdIpg\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Psr\Http\Message\ResponseInterface;

/**
 * @package Bahramn\EcdIpg\Events
 */
class GatewayHttpResponseReceived
{
    use Dispatchable, SerializesModels;

    public string $paymentUuid;
    public ResponseInterface $result;
    public ?string $message;

    public function __construct(string $paymentUuid, ResponseInterface $result, ?string $message)
    {
        $this->paymentUuid = $paymentUuid;
        $this->result = $result;
        $this->message = $message;
    }

}
