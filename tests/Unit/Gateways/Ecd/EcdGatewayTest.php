<?php

namespace Bahramn\EcdIpg\Tests\Unit\Gateways\Ecd;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\DTOs\PaymentVerifyData;
use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Bahramn\EcdIpg\Exceptions\PaymentConfirmationFailedException;
use Bahramn\EcdIpg\Exceptions\PaymentInitializeFailedException;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdConfirmResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeRequestData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdPaymentCallbackRequestData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdReverseResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\EcdClient;
use Bahramn\EcdIpg\Gateways\Ecd\EcdGateway;
use Bahramn\EcdIpg\Support\InitializePostFormResult;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Bahramn\EcdIpg\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EcdGatewayTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_make_proper_ecd_init_request_data()
    {
        $initPaymentData = $this->makePaymentInitData();
        $ecdConfig = $this->getEcdGatewayConfigData();
        $now = Carbon::now();
        Carbon::setTestNow($now);
        $callbackUrl = $this->getEcdCallbackUrl($initPaymentData->getUuid());
        $checkSum = sha1(
            $ecdConfig->attributes['terminal_id'] .
            $initPaymentData->getUuid() .
            $initPaymentData->getAmount() .
            $now->format('Y/m/d') .
            $now->format('H:d') .
            $callbackUrl .
            $ecdConfig->attributes['key']
        );

        $ecdInitRequest = (new EcdInitializeRequestData)
            ->setConfig($ecdConfig->attributes)
            ->setInitPaymentData($initPaymentData)
            ->make();

        $this->assertArrayHasKey('BuyID', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('TerminalNumber', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('Amount', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('Date', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('Time', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('RedirectURL', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('Language', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('CheckSum', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('NationalCode', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('Mobile', $ecdInitRequest->getInitializeRequestBody());
        $this->assertArrayHasKey('AdditionalData', $ecdInitRequest->getInitializeRequestBody());
        $this->assertEquals($ecdInitRequest->getInitializeRequestBody()['Amount'], $initPaymentData->getAmount());
        $this->assertEquals($ecdInitRequest->getInitializeRequestBody()['BuyID'], $initPaymentData->getUuid());
        $this->assertEquals($ecdInitRequest->getInitializeRequestBody()['CheckSum'], $checkSum);
    }

    /**
     * @test
     */
    public function it_should_initialize_payment_and_return_post_form_initialize_result()
    {
        $paymentInitData = $this->makePaymentInitData();
        $initEcdResponseData = EcdInitializeResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/initial-success.json')
        );
        $ecdClient = \Mockery::mock(EcdClient::class);
        $ecdClient->shouldReceive('initialPayment')
            ->andReturn($initEcdResponseData);
        $ecdGateway = $this->instantiateEcdGateway($ecdClient);
        $ecdGateway->setPaymentInitData($paymentInitData);
        $formActionUrl = env('ECD_FORM_ACTION_URL', 'https://ecd.shaparak.ir/ipg_ecd/PayStart');

        $result = $ecdGateway->initPayment();

        $this->assertInstanceOf(InitializeResultInterface::class, $result);
        $this->assertInstanceOf(InitializePostFormResult::class, $result);
        $this->assertEquals('8F8D4271609BDCD3B7B67F4BC45419A6076E9D75', $result->getGateWayTransactionToken());
        $this->assertEquals($formActionUrl, $result->getURL());
        $this->assertArrayHasKey('token', $result->getFormData());
        $this->assertInstanceOf(View::class, $result->getResponse());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_on_ecd_invalid_response()
    {
        $paymentInitData = $this->makePaymentInitData();
        $initEcdResponseData = EcdInitializeResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/initial-error.json')
        );
        $ecdClient = \Mockery::mock(EcdClient::class);
        $ecdClient->shouldReceive('initialPayment')
            ->andReturn($initEcdResponseData);
        $ecdGateway = $this->instantiateEcdGateway($ecdClient);
        $ecdGateway->setPaymentInitData($paymentInitData);
        $this->expectException(PaymentInitializeFailedException::class);
        $ecdGateway->initPayment();
    }

    /**
     * @test
     */
    public function it_should_throw_validation_exception_on_invalid_callback_request()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            ['invalid' => 'request-data']
        );

        $this->expectException(ValidationException::class);
        EcdPaymentCallbackRequestData::createFromRequest($request);
    }

    /**
     * @test
     */
    public function it_should_return_success_confirm_result_on_valid_callback_request()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();

        $ecdClient = \Mockery::mock(EcdClient::class);
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            json_decode(file_get_contents(__DIR__ . '/responses/callback-request-success.json'), true)
        );
        $ecdConformResult = EcdConfirmResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/confirm-success.json')
        );
        $ecdGateway = $this->instantiateEcdGateway($ecdClient, $request);
        $ecdGateway->setPaymentVerifyData($paymentVerifyData);
        $ecdClient->shouldReceive('confirm')
            ->andReturn($ecdConformResult);

        $confirmResult = $ecdGateway->confirm();

        $this->assertTrue($confirmResult->isSucceed());
        $this->assertIsNumeric($confirmResult->getRrn());
        $this->assertIsNumeric($confirmResult->getStan());
    }

    /**
     * @test
     */
    public function it_should_return_failed_payment_confirm_result_on_error_confirm_response()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();

        $ecdClient = \Mockery::mock(EcdClient::class);
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            json_decode(file_get_contents(__DIR__ . '/responses/callback-request-success.json'), true)
        );
        $ecdConformResult = EcdConfirmResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/confirm-error.json')
        );
        $ecdGateway = $this->instantiateEcdGateway($ecdClient, $request);
        $ecdGateway->setPaymentVerifyData($paymentVerifyData);
        $ecdClient->shouldReceive('confirm')
            ->andReturn($ecdConformResult);

        $confirmResult = $ecdGateway->confirm();

        $this->assertFalse($confirmResult->isSucceed());
        $this->assertIsNumeric($confirmResult->getRrn());
        $this->assertIsNumeric($confirmResult->getStan());
    }

    /**
     * @test
     */
    public function it_should_throw_payment_conformation_failed_on_invalid_confirm_response()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();

        $ecdClient = \Mockery::mock(EcdClient::class);
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            json_decode(file_get_contents(__DIR__ . '/responses/callback-request-success.json'), true)
        );
        $ecdGateway = $this->instantiateEcdGateway($ecdClient, $request);
        $ecdGateway->setPaymentVerifyData($paymentVerifyData);
        $ecdClient->shouldReceive('confirm')
            ->andThrow(new InvalidApiResponseException('ecd'));

        $this->expectException(PaymentConfirmationFailedException::class);

        $ecdGateway->confirm();
    }

    /**
     * @test
     */
    public function it_should_throw_payment_confirmation_failed_on_invalid_amount_on_call_back()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();
        $paymentVerifyData->setAmount(999999);
        $ecdClient = \Mockery::mock(EcdClient::class);
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            json_decode(file_get_contents(__DIR__ . '/responses/callback-request-success.json'), true)
        );
        $ecdGateway = $this->instantiateEcdGateway($ecdClient, $request);
        $ecdGateway->setPaymentVerifyData($paymentVerifyData);

        $this->expectException(PaymentConfirmationFailedException::class);

        $ecdGateway->confirm();
    }

    /**
     * @test
     */
    public function it_should_throw_payment_confirmation_failed_when_callback_is_not_success()
    {
        $paymentVerifyData = $this->makePaymentVerifyData();

        $ecdClient = \Mockery::mock(EcdClient::class);
        $request = Request::create(
            $this->getEcdCallbackUrl($paymentVerifyData->getUuid()),
            'POST',
            json_decode(file_get_contents(__DIR__ . '/responses/callback-request-failed.json'), true)
        );
        $ecdGateway = $this->instantiateEcdGateway($ecdClient, $request);
        $ecdGateway->setPaymentVerifyData($paymentVerifyData);

        $this->expectException(PaymentConfirmationFailedException::class);
        $ecdGateway->confirm();
    }

    /**
     * @test
     */
    public function it_should_reverse_transaction_with_valid_reverse_result_data()
    {
        $ecdClient = \Mockery::mock(EcdClient::class);
        $reverseResponseData = EcdReverseResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/reverse-success.json')
        );
        $transactionUid = $this->faker->uuid;
        $transactionToken = $this->faker->sha1;
        $ecdClient->shouldReceive('reverse')
            ->with($transactionToken, $transactionUid)
            ->andReturn($reverseResponseData);
        $ecdGateway = $this->instantiateEcdGateway($ecdClient);

        $result = $ecdGateway->reverse($transactionUid, $transactionToken);

        $this->assertTrue($result->hasReversed());
        $this->assertNotEmpty($result->getMessage());
    }

    /**
     * @test
     */
    public function it_should_have_failed_reverse_result_in_ecd_error_response()
    {
        $ecdClient = \Mockery::mock(EcdClient::class);
        $reverseResponseData = EcdReverseResponseData::createFromResponse(
            file_get_contents(__DIR__ . '/responses/reverse-error.json')
        );
        $transactionUid = $this->faker->uuid;
        $transactionToken = $this->faker->sha1;
        $ecdClient->shouldReceive('reverse')
            ->with($transactionToken, $transactionUid)
            ->andReturn($reverseResponseData);
        $ecdGateway = $this->instantiateEcdGateway($ecdClient);

        $result = $ecdGateway->reverse($transactionUid, $transactionToken);

        $this->assertFalse($result->hasReversed());
        $this->assertNotEmpty($result->getMessage());
    }

    private function instantiateEcdGateway($ecdClient, Request $request = null): EcdGateway
    {
        $args = ['ecdClient' => $ecdClient];
        if ($request) {
            $args += ['request' => $request];
        }
        $ecdGateway = $this->app->make(EcdGateway::class, $args);
        $ecdGateway->setConfig($this->getEcdGatewayConfigData());

        return $ecdGateway;
    }

    private function getEcdGatewayConfigData(): GatewayConfigData
    {
        return new GatewayConfigData(config('ecd-ipg.gateways.ecd'));
    }

    private function getEcdCallbackUrl(string $uuid): string
    {
        return route('payment.callback', [
            'gateway' => $this->getEcdGatewayConfigData()->name,
            'transaction_id' => $uuid,
        ]);
    }

    private function makePaymentVerifyData(): PaymentVerifyData
    {
        return (new PaymentVerifyData)
            ->setAmount(1000)
            ->setGateway('ecd')
            ->setCurrency('IRR')
            ->setUuid('53f9de79-2311-42ed-9c1a-aa7c12f4da43');
    }
}
