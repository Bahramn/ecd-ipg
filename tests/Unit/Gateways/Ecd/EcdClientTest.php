<?php

namespace Bahramn\EcdIpg\Tests\Unit\Gateways\Ecd;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\Events\GatewayHttpRequestSent;
use Bahramn\EcdIpg\Events\GatewayHttpResponseReceived;
use Bahramn\EcdIpg\Exceptions\InvalidApiResponseException;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdConfirmResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeRequestData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdInitializeResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdReverseResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdTransactionData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdTransactionsParamsData;
use Bahramn\EcdIpg\Gateways\Ecd\DTOs\EcdTransactionsResponseData;
use Bahramn\EcdIpg\Gateways\Ecd\EcdClient;
use Bahramn\EcdIpg\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;

class EcdClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /**
     * @test
     * @throws InvalidApiResponseException
     */
    public function it_should_have_success_response_with_token_in_initial_payment_request()
    {
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);
        $data = (new EcdInitializeRequestData)
            ->setConfig($this->getEcdGatewayConfigData()->attributes)
            ->setInitPaymentData($this->makePaymentInitData())
            ->make();

        $initialResponse = $client->initialPayment($data);

        $this->assertInstanceOf(EcdInitializeResponseData::class, $initialResponse);
        $this->assertEquals(true, $initialResponse->isSuccess());
        $this->assertNotEmpty($initialResponse->getToken());
        $this->assertIsString($initialResponse->getToken());
        Event::assertDispatched(GatewayHttpRequestSent::class, 1);
        Event::assertDispatched(GatewayHttpResponseReceived::class, 1);
        Event::assertDispatched(
            fn (GatewayHttpRequestSent $event) => $event->paymentUuid == $data->getPaymentUuid() &&
            $event->requestData->body == $data->getInitializeRequestBody()
        );
        Event::assertDispatched(fn (GatewayHttpResponseReceived $event) => $event->paymentUuid == $data->getPaymentUuid());
    }

    /**
     * @test
     * @throws InvalidApiResponseException|BindingResolutionException
     */
    public function it_should_not_be_success_in_initial_request_with_invalid_data()
    {
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);
        $initPaymentData = $this->makePaymentInitData();
        $initPaymentData->setDescription('error');
        $data = (new EcdInitializeRequestData)
            ->setConfig($this->getEcdGatewayConfigData()->attributes)
            ->setInitPaymentData($initPaymentData)
            ->make();

        $initialResponse = $client->initialPayment($data);

        $this->assertInstanceOf(EcdInitializeResponseData::class, $initialResponse);
        $this->assertFalse($initialResponse->isSuccess());
        $this->assertEquals(Lang::get('ecd-ipg::messages.ecd_error_codes.101'), $initialResponse->getMessage());
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_throw_invalid_api_exception_when_getting_invalid_response_in_initial_payment_request()
    {
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);
        $initPaymentData = $this->makePaymentInitData();
        $initPaymentData->setDescription('invalid');
        $data = (new EcdInitializeRequestData)
            ->setConfig($this->getEcdGatewayConfigData()->attributes)
            ->setInitPaymentData($initPaymentData)
            ->make();

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('Invalid response received from ECD-Gateway API');
        $client->initialPayment($data);
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_get_success_response_in_confirm_request()
    {
        $token = '8F8D4271609BDCD3B7B67F4BC45419A6076E9D75';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $confirmData = $client->confirm($token, $paymentUuid);

        $this->assertInstanceOf(EcdConfirmResponseData::class, $confirmData);
        $this->assertTrue($confirmData->isConfirmed());
        $this->assertEquals(Lang::get('ecd-ipg::messages.success'), $confirmData->getMessage());
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_by_invalid_response_in_confirm_request()
    {
        $token = 'invalid';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('Invalid response received from ECD-Gateway API');

        $client->confirm($token, $paymentUuid);
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_not_confirmed_when_unknown_token_requested_in_confirm_response()
    {
        $token = 'error';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $result = $client->confirm($token, $paymentUuid);

        $this->assertFalse($result->isConfirmed());
        $this->assertEquals(Lang::get('ecd-ipg::messages.ecd_error_codes.111'), $result->getMessage());
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_have_success_response_with_valid_token_in_revere_request()
    {
        $token = '8F8D4271609BDCD3B7B67F4BC45419A6076E9D75';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $result = $client->reverse($token, $paymentUuid);

        $this->assertInstanceOf(EcdReverseResponseData::class, $result);
        $this->assertTrue($result->hasReversed());
        $this->assertEquals(Lang::get('ecd-ipg::messages.reversed'), $result->getMessage());
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_has_not_revered_with_invalid_token_in_reverse_request()
    {
        $token = 'error';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $result = $client->reverse($token, $paymentUuid);

        $this->assertInstanceOf(EcdReverseResponseData::class, $result);
        $this->assertFalse($result->hasReversed());
        $this->assertEquals(Lang::get('ecd-ipg::messages.ecd_error_codes.113'), $result->getMessage());
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_by_invalid_response_in_reveres_request()
    {
        $token = 'invalid';
        $paymentUuid = $this->faker->uuid;
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('Invalid response received from ECD-Gateway API');

        $client->reverse($token, $paymentUuid);
        Event::assertDispatched(GatewayHttpRequestSent::class);
        Event::assertDispatched(GatewayHttpResponseReceived::class);
    }

    /**
     * @test
     */
    public function it_should_get_transactions_without_any_specific_params()
    {
        $client = $this->app->make(EcdClient::class, ['handler' => new EcdMockHandler()]);

        $data = new EcdTransactionsParamsData();
        $result = $client->transactions($data);

        $this->assertArrayHasKey('TerminalNumber', $data->getParams());
        $this->assertArrayHasKey('Key', $data->getParams());
        $this->assertInstanceOf(EcdTransactionsResponseData::class, $result);
        $this->assertInstanceOf(Collection::class, $result->getTransactions());
        $this->assertTrue($result->isSucceed());
        $this->assertNotEmpty($result->getTransactions());
        $result->getTransactions()->each(fn ($item) => $this->assertInstanceOf(EcdTransactionData::class, $item));
    }

    /**
     * @test
     */
    public function it_should_make_proper_params_to_get_transactions()
    {
        $paymentUuid = $this->faker->uuid;
        $token = $this->faker->sha1;
        $rrn = $this->faker->numerify('########');
        $status = $this->faker->numberBetween(0, 1);

        $data = new EcdTransactionsParamsData();
        $data->setPaymentUuid($paymentUuid);
        $data->setToken($token);
        $data->setRrn($rrn);
        $data->setStatus($status);

        $this->assertArrayHasKey('TerminalNumber', $data->getParams());
        $this->assertArrayHasKey('Key', $data->getParams());
        $this->assertArrayHasKey('BuyID', $data->getParams());
        $this->assertContains($paymentUuid, $data->getParams());
        $this->assertArrayHasKey('Token', $data->getParams());
        $this->assertContains($token, $data->getParams());
        $this->assertArrayHasKey('ReferenceNumber', $data->getParams());
        $this->assertContains($rrn, $data->getParams());
        $this->assertArrayHasKey('Status', $data->getParams());
        $this->assertContains($status, $data->getParams());
    }

    private function getEcdGatewayConfigData(): GatewayConfigData
    {
        return new GatewayConfigData(config('ecd-ipg.gateways.ecd'));
    }
}
