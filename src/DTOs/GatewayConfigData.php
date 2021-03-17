<?php

namespace Bahramn\EcdIpg\DTOs;

/**
 * @package Bahramn\EcdIpg\DTOs
 */
class GatewayConfigData
{
    public string $name;
    public string $class;
    public bool $active;
    public array $attributes;


    /**
     * GatewayConfigData constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (isset($config['name'], $config['class'], $config['active'])) {
            $this->name = $config['name'];
            $this->class = $config['class'];
            $this->active = $config['active'];
            $this->attributes = $config;
            return;
        }

        throw new \InvalidArgumentException("Gateway config should include name, class, active");
    }
}
