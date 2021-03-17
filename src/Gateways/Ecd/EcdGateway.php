<?php

namespace Bahramn\EcdIpg\Gateways\Ecd;

use Bahramn\EcdIpg\DTOs\PaymentConfirmResultData;
use Bahramn\EcdIpg\DTOs\PaymentReverseResultData;
use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Gateways\AbstractGateway;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdPaymentCallbackRequestData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeRequestData;
use Bahramn\EcdIpg\Support\InitializePostFormResult;
use Bahramn\EcdIpg\Support\Interfaces\ConfirmationResultInterface;
use Bahramn\EcdIpg\Support\Interfaces\ReverseResultInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @package Bahramn\EcdIpg\Gateways\Ecd
 */
class EcdGateway extends AbstractGateway
{
    private EcdClient $ecdClient;
    private Request $request;

    public function __construct(EcdClient $ecdClient, Request $request)
    {
        $this->ecdClient = $ecdClient;
        $this->request = $request;
    }

    /**
     * @return InitializePostFormResult
     * @throws PaymentInitializeFailedException
     */
    public function initPayment(): InitializePostFormResult
    {
        $initRequest = (new EcdInitializeRequestData)
            ->setConfig($this->config->attributes)
            ->setInitPaymentData($this->paymentInitData)
            ->make();

        try {
            $initResponse = $this->ecdClient->initialPayment($initRequest);
            if ($initResponse->isSuccess()) {
                return new InitializePostFormResult($initResponse->getToken(), $this->getFormActionUrl(), [
                    'token' => $initResponse->getToken()
                ]);
            }
            throw new PaymentInitializeFailedException($initResponse->getMessage());
        } catch (InvalidApiResponseException $exception) {
            throw new PaymentInitializeFailedException($exception->getMessage(), $exception->toArray());
        }
    }


    /**
     * @return ConfirmationResultInterface
     * @throws PaymentConfirmationFailedException
     */
    public function confirm(): ConfirmationResultInterface
    {
        try {
            $callbackData = EcdPaymentCallbackRequestData::createFromRequest($this->request);
            $confirmResultData = (new PaymentConfirmResultData)
                ->setRrn($callbackData->getRrn())
                ->setStan($callbackData->getStan());

            $this->validateCallbackData($callbackData);
            $confirmResult = $this->ecdClient->confirm($callbackData->getToken(), $callbackData->getPaymentUuid());
            $confirmResultData->setStatus($confirmResult->isConfirmed())
                ->setMessage($confirmResult->getMessage());

            return $confirmResultData;
        } catch (ValidationException $exception) {
            throw new PaymentConfirmationFailedException(
                "data received from ECD callback is not valid.",
                $this->request->all()
            );
        } catch (InvalidApiResponseException $exception) {
            throw new PaymentConfirmationFailedException(
                "Ecd payment confirmation failed.",
                $exception->toArray()
            );
        } catch (\Exception $exception) {
            throw new PaymentConfirmationFailedException("Ecd confirmation has unexpected error.");
        }
    }


    /**
     * @param string $uuid
     * @param string $token
     * @return ReverseResultInterface
     */
    public function reverse(string $uuid, string $token): ReverseResultInterface
    {
        $reverseData = new PaymentReverseResultData();
        try {
            $reverseResult = $this->ecdClient->reverse($token, $uuid);

            return $reverseData
                ->setSuccess($reverseResult->hasReversed())
                ->setMessage($reverseResult->getMessage());
        }catch (\Exception $exception) {
            return $reverseData->setSuccess(false)
                ->setMessage($exception->getMessage());
        }
    }

    /**
     * @param EcdPaymentCallbackRequestData $callbackData
     * @return void
     * @throws PaymentConfirmationFailedException
     */
    private function validateCallbackData(EcdPaymentCallbackRequestData $callbackData): void
    {
        $context = [
            'transaction' => $this->paymentVerifyData->getUuid(),
            'transactionAmount' => $this->paymentVerifyData->getAmount(),
            'callbackAmount' => $callbackData->getAmount(),
            'token' => $callbackData->getToken()
        ];
        if ($this->paymentVerifyData->getAmount() != $callbackData->getAmount()) {
            throw new PaymentConfirmationFailedException("transaction amount is not equal to callback.", $context);
        }
        if (! $callbackData->isSucceed()) {
            throw new PaymentConfirmationFailedException("payment verification failed by ECD", $context);
        }
    }

    private function getFormActionUrl(): string
    {
        return env('ECD_FORM_ACTION_URL', 'https://ecd.shaparak.ir/ipg_ecd/PayStart');
    }
}
