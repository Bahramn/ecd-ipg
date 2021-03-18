<?php

namespace Bahramn\EcdIpg\Gateways\Ecd\Exceptions;

class EcdGatewayException extends \Exception
{
    private function errorsList(): array
    {
        return [
            100 => 'خطای سیستمی در متد',
            101 => 'فرمت داده های ورودی نا معتبر است.',
            102 => 'شماره ترمینال معتبر نمی باشد.',
            103 => 'مبلغ معتبر نمی باشد.',
            104 => 'تاریخ معتبر نمی باشد.',
            105 => 'زمان معتبر نمی باشد.',
            106 => 'آدرس صفحه بازگشت معتبر نمی باشد.',
            107 => 'زبان معتبر نمی باشد.',
            108 => 'مقدارامضای دیجیتال معتبرنمی باشد.',
            109 => 'ترمینال غیرفعال می باشد.',
            110 => 'امضای دیجیتال معتبر نمی باشد',
            111 => 'توکن یافت نشد',
            112 => 'مهلت زمان خرید به پایان رسیده است',
            113 => 'فرآیند به پایان رسیده است',
            114 => 'اصالحیه در گذشته انجام شده است.',
            115 => 'تاییدیه در گذشته انجام شده است.',
            116 => 'خرید انجام نشده است.',
            117 => 'انصراف از انجام تراکنش',
            118 => 'ای پی پذیرنده اشتباه می باشد.',
            120 => 'آی پی مشتری تغییر کرده است',
            122 => 'شماره خرید معتبرنمی باشد.',
            123 => 'تاریخ نماد الکترونیکی به پایان رسیده است',
            124 => 'آی پی در بالک لیست قرار دارد.',
            125 => 'شناسه خرید نا معتبر می باشد.',
        ];
    }
}
