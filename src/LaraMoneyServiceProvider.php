<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\Money\MoneyFactory;

final class LaraMoneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/money.php',
            'money',
        );

        $this->app->singleton(
            MoneyFactory::class,
            fn(Application $app): MoneyFactory => new MoneyFactory(
                currency: $app['config']->get('money.currency'),
                roundingMode: $app['config']->get('money.rounding_mode'),
            ),
        );

        $this->app->singleton(
            CurrencyResolver::class,
            DefaultCurrencyResolver::class,
        );
    }

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__.'/../config/money.php' => config_path('money.php'),
            ],
            'lara-money-config',
        );
    }

}