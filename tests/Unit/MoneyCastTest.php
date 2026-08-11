<?php

declare(strict_types=1);

use LeanCaptain\LaraMoney\Casts\MoneyCast;
use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\LaraMoney\Tests\Fixtures\Product;
use LeanCaptain\LaraMoney\Tests\Fixtures\TestCurrency;
use LeanCaptain\LaraMoney\Tests\Fixtures\TestCurrencyResolver;
use LeanCaptain\Money\Currency;
use LeanCaptain\Money\Money;

it('casts minor units using the default currency', function (): void {
    config()->set('money.currency', Currency::BDT);

    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    expect($product->price)
        ->toBeInstanceOf(Money::class)
        ->and($product->price->currency())->toBe(Currency::BDT)
        ->and($product->price->toDecimal())->toBe('125.50');
});

it('casts minor units using an explicit currency attribute', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
        'currency' => 'USD',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    expect($product->price->currency())->toBe(Currency::USD)
        ->and($product->price->toDecimal())->toBe('125.50');
});

it('supports a custom currency attribute name', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
        'currency_code' => 'EUR',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency_code',
    ]);

    expect($product->price->currency())->toBe(Currency::EUR);
});

it('preserves null money values', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => null,
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    expect($product->price)->toBeNull();
});

it('preserves the currency minor unit semantics', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
        'currency' => 'JPY',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    expect($product->price->currency())->toBe(Currency::JPY)
        ->and($product->price->toDecimal())->toBe('12550');
});

it('rejects a missing explicit currency attribute', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    $product->price;
})->throws(
    InvalidArgumentException::class,
    'Currency attribute [currency] is missing or invalid for money attribute [price].',
);

it('rejects a null explicit currency attribute', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
        'currency' => null,
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    $product->price;
})->throws(InvalidArgumentException::class);

it('supports consumer currencies through a custom resolver', function (): void {
    $this->app->singleton(
        CurrencyResolver::class,
        TestCurrencyResolver::class,
    );

    $product = new Product();

    $product->setRawAttributes([
        'price' => 12550,
        'currency' => 'CAD',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    expect($product->price->currency())->toBe(TestCurrency::CAD)
        ->and($product->price->toDecimal())->toBe('125.50');
});

it('stores decimal input as integer minor units', function (): void {
    config()->set('money.currency', Currency::BDT);

    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = '125.50';

    expect($product->getAttributes()['price'])->toBe(12550);
});

it('treats integer input as major units', function (): void {
    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = 125;

    expect($product->getAttributes()['price'])->toBe(12500);
});

it('stores a money value as minor units', function (): void {
    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = Money::of(
        '125.50',
        Currency::USD,
    );

    expect($product->getAttributes()['price'])->toBe(12550);
});

it('stores money using the default currency', function (): void {
    config()->set('money.currency', Currency::BDT);

    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = Money::of(
        '125.50',
        Currency::BDT,
    );

    expect($product->getAttributes()['price'])->toBe(12550);
});

it('rejects money with a different default currency', function (): void {
    config()->set('money.currency', Currency::BDT);

    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = Money::of(
        '125.50',
        Currency::USD,
    );
})->throws(InvalidArgumentException::class);

it('stores input using the explicit currency attribute', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'currency' => 'USD',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    $product->price = '125.50';

    expect($product->getAttributes()['price'])->toBe(12550);
});

it('stores values according to the explicit currency minor unit', function (): void {
    $product = new Product();

    $product->setRawAttributes([
        'currency' => 'JPY',
    ]);

    $product->mergeCasts([
        'price' => MoneyCast::class.':currency',
    ]);

    $product->price = '125';

    expect($product->getAttributes()['price'])->toBe(125);
});

it('stores null as null', function (): void {
    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = null;

    expect($product->getAttributes()['price'])->toBeNull();
});

it('rejects float input', function (): void {
    $product = new Product();

    $product->mergeCasts([
        'price' => MoneyCast::class,
    ]);

    $product->price = 125.50;
})->throws(
    InvalidArgumentException::class,
    'Money attribute [price] must be null, Money, int, or string.',
);
