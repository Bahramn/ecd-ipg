<?php

namespace Bahramn\EcdIpg;

use Bahramn\EcdIpg\Payment\Payment;
use Bahramn\EcdIpg\Payment\PaymentManager;
use Bahramn\EcdIpg\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

/**
 * @package Bahramn\EcdIpg
 */
class EcdIpgServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Payment::class, PaymentManager::class);
        $this->app->singleton(TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishAbles();
        $this->bootUnitTestDependencies();
    }

    private function registerPublishAbles(): void
    {
        $this->publishes([
            __DIR__. '/../config/ecd-ipg.php' => config_path('ecd-ipg.php'),
            __DIR__. '/../resources/lang' => resource_path('lang/vendor/ecd-gateway')
        ]);
    }

    private function bootUnitTestDependencies(): void
    {
        if ($this->app->runningUnitTests()) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->mergeConfigFrom(__DIR__.'/../config/ecd-ipg.php', 'ecd-ipg');
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'ecd-ipg');
            $this->loadTranslationsFrom(__DIR__. '/../resources/lang', 'ecd-ipg');
        }
    }

    private function registerMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            if (! class_exists('CreateTransactionsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/create_transactions_table.php.stub' =>
                        database_path('migrations/' . date('Y_m_d_His', time()) . '_create_transactions_table.php'),
                ], 'migrations');
            }
        }
    }
}
