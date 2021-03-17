<?php

namespace Bahramn\EcdIpg\Tests;

use Faker\Provider\Base as BaseProvider;

/**
 * @property-read $mobileNumber
 * @property-read $nationalId
 * @package Tests
 */
class IranCustomFakerProvider extends BaseProvider
{
    const MOBILE_PREFIXES = [
        912 , 938 , 935 , 921 , 901 , 921 , 919
    ];

    public function mobileNumber(): string
    {
        $prefix = $this->randomElement(self::MOBILE_PREFIXES);
        $rand = $this->generator->randomNumber(7);

        return "0{$prefix}{$rand}";
    }

    public function nationalId(): string
    {
        return $this->generator->numerify('##########');
    }
}
