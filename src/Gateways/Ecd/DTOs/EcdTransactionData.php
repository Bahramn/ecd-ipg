<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\DTOs;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd\DTOs
 */
class EcdTransactionData
{
    public string $amount;
    public string $rrn;
    public string $stan;
    public string $paymentUuid;
    public string $token;
    public string $cardNumber;
    public string $code;
    public string $status;
    public ?string $mobile;
    public ?string $nid;
    public string $time;
    public string $date;
    public ?string $description;
    public string $callbackUrl;

    public static function createFromArray(array $trn): self
    {
        $instance = new static;

        $instance->amount = $trn['Amount'];
        $instance->rrn = $trn['ReferenceNumber'];
        $instance->stan = $trn['TrackingNumber'];
        $instance->paymentUuid = $trn['BuyID'];
        $instance->token = $trn['Token'];
        $instance->cardNumber = $trn['cardNumber'];
        $instance->code = $trn['ResponseCode'];
        $instance->status = $trn['Status'];
        $instance->mobile = $trn['Mobile'];
        $instance->nid = $trn['NationalCode'];
        $instance->time = $trn['Time'];
        $instance->date = $trn['Date'];
        $instance->description = $trn['AdditionalData'];
        $instance->callbackUrl = $trn['CallBackURL'];

        return $instance;
    }

}
