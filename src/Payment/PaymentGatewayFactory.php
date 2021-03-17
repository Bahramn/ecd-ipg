<?php
namespace Bahramn\EcdIpg\Payment;

use Bahramn\EcdIpg\DTOs\GatewayConfigData;
use Bahramn\EcdIpg\Gateways\AbstractGateway;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

/**
 * @package Bahramn\EcdIpg\Payment
 */
class PaymentGatewayFactory
{
    /**
     * @param string            $gateway
     * @return AbstractGateway
     * @throws BindingResolutionException|Exception
     */
    public function getInstance(string $gateway): AbstractGateway
    {
        /* @var GatewayConfigData $configData */
        $configData = $this->collectGateways()
            ->filter(fn (GatewayConfigData $configData) => $configData->name == $gateway)
            ->first();

        if (!is_null($configData)) {
            return app()->make($configData->class)->setConfig($configData);
        }
        throw new Exception($gateway . " gateway is not available");
    }

    /**
     * @return string
     */
    public function getDefaultGatewayName(): string
    {
        return config('ecd-ipg.default_gateway');
    }

    /**
     * @return array
     */
    public function getGatewayNames(): array
    {
        return $this->collectGateways()
            ->map(fn (GatewayConfigData $configData) => $configData->name)
            ->toArray();
    }


    /**
     * @return Collection
     */
    private function collectGateways(): Collection
    {
        return (new Collection(config('ecd-ipg.gateways')))
            ->map(fn (array $config) => new GatewayConfigData($config));
    }
}
