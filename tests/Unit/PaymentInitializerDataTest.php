<?php

namespace Bahramn\EcdIpg\Tests\Unit;

use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\Tests\TestCase;

/**
 * @package Tests\Unit
 */
class PaymentInitializerDataTest extends TestCase
{
    public function test_it_could_create_payment_initialize_dto_with_correct_data()
    {
        $mobile = $this->faker->mobileNumber;
        $nid = $this->faker->nationalId;
        $description = $this->faker->sentence;
        $currency = $this->faker->currencyCode;
        $amount = $this->faker->randomNumber();

        $initPaymentData = (new PaymentInitData)
            ->setNid($nid)
            ->setAmount($amount)
            ->setMobile($mobile)
            ->setCurrency($currency)
            ->setDescription($description);

        $this->assertEquals($nid, $initPaymentData->getNid());
        $this->assertEquals($amount, $initPaymentData->getAmount());
        $this->assertEquals($mobile, $initPaymentData->getMobile());
        $this->assertEquals($currency, $initPaymentData->getCurrency());
        $this->assertEquals($description, $initPaymentData->getDescription());
    }
}
