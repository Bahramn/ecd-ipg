<?php

namespace Bahramn\EcdIpg\Tests\Unit;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\Gateways\AbstractGateway;
use Bahramn\EcdIpg\Payment\PaymentGatewayFactory;
use Bahramn\EcdIpg\Tests\TestCase;

class PaymentGatewayFactoryTest extends TestCase
{

    /**
     * @test
     */
    public function it_should_get_all_available_gateway_names()
    {
        $names = array_map(fn (array $gateway) => $gateway['name'], config('ecd-ipg.gateways'));
        $gatewayFactory = app()->make(PaymentGatewayFactory::class);

        $this->assertEquals($names, $gatewayFactory->getGatewayNames());
    }


    /**
     * @test
     */
    public function it_should_get_the_default_gateway_name()
    {
        $defaultGatewayName = config('ecd-ipg.default_gateway');
        $gatewayFactory = app()->make(PaymentGatewayFactory::class);

        $this->assertEquals($defaultGatewayName, $gatewayFactory->getDefaultGatewayName());
    }

    /**
     * @test
     */
    public function it_should_instantiate_gateway_by_name()
    {
        $randomGateway = $this->faker->randomElement(config('ecd-ipg.gateways'));
        $gatewayFactory = app()->make(PaymentGatewayFactory::class);

        $gateway = $gatewayFactory->getInstance($randomGateway['name']);

        $this->assertInstanceOf($randomGateway['class'], $gateway);
        $this->assertInstanceOf(AbstractGateway::class, $gateway);
    }

    /**
     * @test
     */
    public function it_should_instantiate_gateway_which_has_config_attribute()
    {
        $randomGateway = $this->faker->randomElement(config('ecd-ipg.gateways'));
        $gatewayFactory = app()->make(PaymentGatewayFactory::class);

        $gateway = $gatewayFactory->getInstance($randomGateway['name']);

        $this->assertObjectHasAttribute('config', $gateway);
        $this->assertInstanceOf(GatewayConfigData::class, $gateway->getConfig());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_to_get_instance_of_invalid_gateway_name()
    {
        $gatewayFactory = app()->make(PaymentGatewayFactory::class);

        $this->expectException(\Exception::class);

        $gatewayFactory->getInstance($this->faker->name);
    }

}
