<?php

namespace Bahramn\EcdIpg\Tests;

use Bahramn\EcdIpg\Database\Factories\TestEcdInvoiceFactory;
use Bahramn\EcdIpg\DTOs\PaymentInitData;
use Bahramn\EcdIpg\EcdIpgServiceProvider;
use Dotenv\Dotenv;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Bahramn\EcdIpg\Tests\TestSupport\TestEcdInvoice;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * @var Generator|IranCustomFakerProvider
     */
    protected Generator $faker;

    protected function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();
        $this->setUpFaker();
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [EcdIpgServiceProvider::class];
    }

    protected function loadEnvironmentVariables(): void
    {
        if (! file_exists(__DIR__.'/../.env.example')) {
            return;
        }

        $dotEnv = Dotenv::createImmutable(__DIR__.'/../', '.env.example');

        $dotEnv->load();
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../database/migrations/create_transactions_table.php.stub';
        (new \CreateTransactionsTable)->up();
    }

    protected function createPayable(): TestEcdInvoice
    {
        return TestEcdInvoiceFactory::new()->statusNew()->create();
    }

    protected function makePaymentInitData(TestEcdInvoice $payable = null): PaymentInitData
    {
        return (new PaymentInitData)
            ->setNid($this->faker->nationalId)
            ->setMobile($this->faker->mobileNumber)
            ->setDescription($this->faker->sentence)
            ->setUniqueId($payable ? $payable->getUniqueId() : $this->faker->uuid)
            ->setAmount($payable ? $payable->amount() : $this->faker->randomNumber())
            ->setCurrency($payable ? $payable->currency() : $this->faker->currencyCode);
    }

    private function setUpFaker(): void
    {
        $this->faker = Factory::create();
        $this->faker->addProvider(new IranCustomFakerProvider($this->faker));
    }

    private function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('test_ecd_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('status');
            $table->decimal('total_amount', 16, 0);
            $table->timestamps();
        });
    }
}
