<?php

namespace Bahramn\EcdIpg\Events;

use Bahramn\EcdIpg\DTOs\HTTPRequestData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @package Bahramn\EcdIpg\Events
 */
class GatewayHttpRequestSent
{
    use Dispatchable, SerializesModels;

    public string $paymentUuid;
    public HTTPRequestData $requestData;
    public ?string $message;

    public function __construct(string $paymentUuid, HTTPRequestData $requestData, ?string $message = null)
    {
        $this->paymentUuid = $paymentUuid;
        $this->requestData = $requestData;
        $this->message = $message;
    }
}
