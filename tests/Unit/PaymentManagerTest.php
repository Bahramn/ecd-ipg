<?php

namespace Bahramn\EcdIpg\Tests\Unit;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\Exceptions\PaymentGatewayException;
use Bahramn\EcdIpg\Models\Transaction;
use Bahramn\EcdIpg\Payment\PaymentManager;
use Bahramn\EcdIpg\Payment\Payment;
use Bahramn\EcdIpg\Support\InitializePostFormResult;
use Bahramn\EcdIpg\Support\Interfaces\InitializeResultInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Bahramn\EcdIpg\Tests\TestCase;

class PaymentManagerTest extends TestCase
{
    private PaymentManager $paymentManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentManager = $this->app->make(PaymentManager::class);
    }

    /**
     * @test
     */
    public function it_should_set_default_gateway_without_determining_gateway()
    {
        $payable = $this->createPayable();
        $paymentInitData = $this->makePaymentInitData($payable);
        $defaultGatewayName = config('ecd-ipg.default_gateway');

        $this->paymentManager->setPayable($payable)->readyInitialize($paymentInitData);

        $this->assertEquals($this->paymentManager->getGatewayName(), $defaultGatewayName);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_when_gateway_is_not_active()
    {
        $payable = $this->createPayable();
        $paymentInitData = $this->makePaymentInitData($payable);
        Config::set('ecd-ipg.gateways.ecd.active', false);

        $this->expectException(PaymentGatewayException::class);

        $this->paymentManager->setPayable($payable)->readyInitialize($paymentInitData);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_when_invalid_gateway_has_been_set()
    {
        $payable = $this->createPayable();
        $paymentInitData = $this->makePaymentInitData($payable);

        $this->expectException(PaymentGatewayException::class);

        $this->paymentManager->setGatewayName($this->faker->name)
            ->setPayable($payable)
            ->readyInitialize($paymentInitData);
    }

    /**
     * @test
     */
    public function it_should_create_transaction_in_payment_initialize()
    {
        $payable = $this->createPayable();
        $paymentInitData = $this->makePaymentInitData($payable);
        $paymentManager = $this->app->make(Payment::class);
        $paymentManager->setPayable($payable)
            ->readyInitialize($paymentInitData);

        $this->assertDatabaseHas('transactions', [
            'uuid' => $paymentInitData->getUuid(),
            'status' => Transaction::STATUS_NEW,
            'amount' => $paymentInitData->getAmount(),
            'gateway' => $paymentInitData->getGateway(),
            'payer_mobile' => $paymentInitData->getMobile(),
            'payer_nid' => $paymentInitData->getNid(),
            'description' => $paymentInitData->getDescription()
        ]);
    }

    /**
     * @test
     */
    public function return_ecd_view_response_with_valid_data_in_initialize_payment_gateway_ecd()
    {
        $paymentManager = \Mockery::mock(Payment::class);
        $this->app->instance(Payment::class, $paymentManager);
        $token = $this->faker->sha1;
        $ecdInitializeResult = new InitializePostFormResult($token, "action-sample", [
            'token' => $token
        ]);

        $paymentManager->shouldReceive('setPayable->readyInitialize->initialize->getResponse')
            ->andReturn($ecdInitializeResult->getResponse());

        $result = $this->initializeFakePayment();
        $this->assertInstanceOf(View::class, $result);
        $this->assertArrayHasKey('data', $result->getData());
        $this->assertInstanceOf(InitializeResultInterface::class, $result->getData()['data']);
        $this->assertEquals('action-sample', $result->getData()['data']->getURL());
        $this->assertArrayHasKey('token', $result->getData()['data']->getFormData());
        $this->assertEquals($token, $result->getData()['data']->getFormData()['token']);
    }

    /**
     * @test
     */
    public function create_payment_initialize_data_from_payable()
    {
        $payable = $this->createPayable();
        $mobile = $this->faker->mobileNumber;
        $nid = $this->faker->nationalId;
        $description = $this->faker->sentence;

        $initPaymentData = (new PaymentInitData)
            ->setNid($nid)
            ->setMobile($mobile)
            ->setDescription($description)
            ->setAmount($payable->amount())
            ->setCurrency($payable->currency());

        $this->assertEquals($nid, $initPaymentData->getNid());
        $this->assertEquals($mobile, $initPaymentData->getMobile());
        $this->assertEquals($description, $initPaymentData->getDescription());
        $this->assertEquals($payable->amount(), $initPaymentData->getAmount());
        $this->assertEquals($payable->currency(), $initPaymentData->getCurrency());
    }

    private function initializeFakePayment()
    {
        $payable = $this->createPayable();
        $paymentInitData = $this->makePaymentInitData($payable);
        $paymentManager = $this->app->make(Payment::class);

        return $paymentManager->setPayable($payable)
            ->readyInitialize($paymentInitData)
            ->initialize()
            ->getResponse();
    }
}
