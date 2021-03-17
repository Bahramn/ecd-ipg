<?php

namespace Bahramn\EcdIpg\Database\Factories;

use Bahramn\EcdIpg\Tests\TestSupport\TestEcdInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestEcdInvoiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TestEcdInvoice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'status' => $this->faker->randomElement(TestEcdInvoice::STATUSES),
            'total_amount' => $this->faker->randomNumber(4, true),
            'uuid' => $this->faker->uuid
        ];
    }

    public function statusNew(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TestEcdInvoice::STATUS_PENDING,
            ];
        });
    }
}
