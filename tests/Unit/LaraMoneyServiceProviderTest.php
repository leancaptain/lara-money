<?php

declare(strict_types=1);

use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\LaraMoney\DefaultCurrencyResolver;
use LeanCaptain\LaraMoney\LaraMoneyServiceProvider;
use LeanCaptain\Money\Currency;
use LeanCaptain\Money\MoneyFactory;
use LeanCaptain\Money\RoundingMode;

it('binds the money factory with default configuration', function (): void {
    $factory = $this->app->make(MoneyFactory::class);

    expect($factory)
        ->toBeInstanceOf(MoneyFactory::class)
        ->and($factory->currency())->toBe(Currency::USD)
        ->and($factory->roundingMode())->toBe(RoundingMode::REJECT);
});

it('binds the money factory as a singleton', function (): void {
    $first = $this->app->make(MoneyFactory::class);
    $second = $this->app->make(MoneyFactory::class);

    expect($second)->toBe($first);
});

it('uses customized money configuration', function (): void {
    $this->app['config']->set('money.currency', Currency::BDT);
    $this->app['config']->set('money.rounding_mode', RoundingMode::HALF_UP);

    $factory = $this->app->make(MoneyFactory::class);

    expect($factory->currency())->toBe(Currency::BDT)
        ->and($factory->roundingMode())->toBe(RoundingMode::HALF_UP);
});

it('registers the money config for publishing', function (): void {
    $paths = LaraMoneyServiceProvider::pathsToPublish(
        LaraMoneyServiceProvider::class,
        'lara-money-config',
    );

    expect($paths)
        ->toHaveCount(1)
        ->and(array_values($paths)[0])
        ->toEndWith('config/money.php');
});

it('binds the default currency resolver', function (): void {
    $resolver = $this->app->make(CurrencyResolver::class);

    expect($resolver)
        ->toBeInstanceOf(DefaultCurrencyResolver::class)
        ->and($resolver->resolve('BDT'))->toBe(Currency::BDT);
});