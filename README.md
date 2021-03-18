# ECD IPG Laravel Package
## پکیج لاراول جهت اتصال و استفاده آسان از درگاه پرداخت الکترونیک کارت دماومند 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bahramn/ecd-ipg.svg?style=flat-square)](https://packagist.org/packages/bahramn/ecd-ipg)
[![Build Status](https://img.shields.io/travis/bahramn/ecd-ipg/master.svg?style=flat-square)](https://travis-ci.org/bahramn/ecd-ipg)
[![Quality Score](https://img.shields.io/scrutinizer/g/bahramn/ecd-ipg.svg?style=flat-square)](https://scrutinizer-ci.com/g/bahramn/ecd-ipg)
[![Total Downloads](https://img.shields.io/packagist/dt/bahramn/ecd-ipg.svg?style=flat-square)](https://packagist.org/packages/bahramn/ecd-ipg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require bahramn/ecd-ipg
```

## Usage
You can use this package in two-way:
1. Use Payment Manager of package that could handle payment with configured gateways and
    will save transactions in the database.
2. Just use EcdClient as an implemented Guzzle client for ECD gateway and
   handle payment process in your application

### Configuration
After installing package you need to update config file `ecd-ipg.php` and environment config 
to set your credentials which has obtained from ECD

```dotenv
DEFAULT_PAYMENT_TIMEOUT=30
DEFAULT_PAYMENT_GATEWAY=ecd
DEFAULT_CURRENCY=IRR
ECD_GATEWAY_ACTIVE=true
ECD_TERMINAL_ID=123
ECD_GATEWAY_KEY=secret
TEST_GATEWAY_ACTIVE=false
```

### Payment
To start a payment we need a payable model in your application that could be an invoice model
or something like that, to make it payable you need to add Payable trait in model and implement
three abstracted methods.

``` php
    // The amount of transaction
    public abstract function amount(): float;
    // Currency of transaction 
    public abstract function currency(): string;
    // The model unique idententifire
    public abstract function uniqueId(): string;
```
Every payment has two steps:
1. Initializing a payment with eligible gateway by defined `PaymentInitData` data and 
    get response from the gateway that could be a view with POST form or redirecting.
   In a success payment initializing a transaction record related to payable model has created.
2. Callback from the gateway that has transaction data,
   in this package callback has implemented in route path of `payment/gateways/{gateway}/callback`
   but you can also override it yourself. 
   If you want to use package callback handler we need to change config file `ecd-ipg.php` 
   to choose the query params key of transaction and payable unique id after success or failed 
   payment to redirect them to your application payment result page.
   
To start we need `PaymentInitData` to determine a payment for initializing:
```php
    $initPaymentData = (new PaymentInitData)
        ->setAmount($invoice->amount())
        ->setCurrency($invoice->currency())
        ->setMobile('MOBILE')
        ->setNid('NATIONAL_ID')
        ->setDescription('DESCRIPTION');
```
After making `PaymentInitData` you can use payment manager to start a payment
```php
$this->paymentManager->setPayable($invoice)
                ->readyInitialize($initPaymentData)
                ->initialize()
                ->getResponse();
```

For example in your Controller you can use it like this:
```php
    /**
     * @param Request $request
     * @return Response|View|mixed
     */
    public function store(Request $request)
    {
        // Make a payable Model
        $invoice = $this->createInvoice($request);
        try {
            $initPaymentData = (new PaymentInitData)
                ->setAmount($invoice->amount())
                ->setDescription('Test payment')
                ->setCurrency($invoice->currency())
                ->setMobile($request->input('mobile'))
                ->setNid($request->input('nid'));

            return $this->paymentManager->setPayable($invoice)
                ->readyInitialize($initPaymentData)
                ->initialize()
                ->getResponse();
        } catch (PaymentGatewayException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }
    }
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email bahramnedaei@gmail.com instead of using the issue tracker.

## Credits

- [Bahram Nedaei](https://github.com/bahramn)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
