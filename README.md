# Lara Money

A thin Laravel integration for [`leancaptain/money`](https://github.com/leancaptain/money).

`leancaptain/lara-money` brings the framework-agnostic Money value object into Laravel with Eloquent casting, container integration, configurable defaults, and support for application-defined currencies.

The core money implementation remains in `leancaptain/money`. This package only provides the Laravel integration layer.

## Requirements

* PHP 8.4+
* Laravel 13+
* `leancaptain/money` 0.0.x

## Installation

Install the package via Composer:

```bash
composer require leancaptain/lara-money
```

The service provider is discovered automatically by Laravel.

No configuration needs to be published for the package to work.

## Configuration

The package provides a sensible default configuration:

```php
use LeanCaptain\Money\Currency;
use LeanCaptain\Money\RoundingMode;

return [
    'currency' => Currency::USD,
    'rounding_mode' => RoundingMode::REJECT,
];
```

The default currency is used whenever a money cast does not specify a currency attribute.

The default rounding mode is used when monetary input needs to be parsed through the configured `MoneyFactory`.

### Publishing the configuration

Publishing is optional.

If you want to customize the package configuration:

```bash
php artisan vendor:publish --tag=lara-money-config
```

This publishes:

```text
config/money.php
```

## MoneyFactory

The package registers `LeanCaptain\Money\MoneyFactory` as a singleton in Laravel's service container.

You can resolve it normally:

```php
use LeanCaptain\Money\MoneyFactory;

$factory = app(MoneyFactory::class);

$money = $factory->of('125.50');
```

The factory uses the currency and rounding mode configured in `config/money.php`.

Constructor injection works as expected:

```php
use LeanCaptain\Money\MoneyFactory;

final class InvoiceService
{
    public function __construct(
        private MoneyFactory $money,
    ) {}
}
```

## Eloquent Money Cast

`MoneyCast` stores monetary values as integer minor units while exposing them as immutable `Money` value objects in your application.

For a currency with two minor-unit decimal places:

```text
125.50 → 12550
```

No floating-point representation is used for monetary storage or parsing.

### Currency resolution

`MoneyCast` resolves the currency for a money attribute using a three-layer strategy, checked in this order:

1. **Explicit currency attribute** — A column name passed to the cast constructor.
2. **Auto-detected `currency` column** — A column named `currency` on the model, used automatically.
3. **Application default** — The default currency from `config('money.php')`.

The first match wins. This means a model with a `currency` column will use it automatically without any explicit configuration.

### Using the default currency

If your model does not have a `currency` column and does not need a per-record currency, use the cast directly:

```php
use LeanCaptain\LaraMoney\Casts\MoneyCast;

protected function casts(): array
{
    return [
        'amount' => MoneyCast::class,
    ];
}
```

If the database contains:

```text
amount = 12550
```

and the configured currency is `USD`, reading:

```php
$model->amount;
```

returns a `Money` representing:

```text
USD 125.50
```

No currency column is required.

This is useful for applications or models that operate in a single known currency.

### Auto-detecting a currency column

If your model has a column named `currency`, `MoneyCast` detects it automatically:

```php
protected function casts(): array
{
    return [
        'amount' => MoneyCast::class,
    ];
}
```

Given:

```text
amount   = 12550
currency = EUR
```

the cast returns a `Money` using `Currency::EUR` without any explicit currency attribute configuration.

This works because `MoneyCast` checks for the presence of a `currency` key in the model's attributes and uses it when no explicit attribute is specified.

### Explicit currency attribute

For multi-currency models where the column is not named `currency`, pass the attribute name to the cast:

```php
protected function casts(): array
{
    return [
        'amount' => MoneyCast::class.':currency_code',
    ];
}
```

For example:

```text
amount        = 12550
currency_code = EUR
```

the cast returns a `Money` using `Currency::EUR`.

When a currency attribute is explicitly configured, it must contain a valid currency code. The cast does not silently fall back to the application's default currency when the explicit currency is missing or invalid.

### Currency resolution flow

```text
MoneyCast constructor
        │
        ├── Explicit attribute provided? ── YES ──► Use that column
        │                                              │
        NO                                              │
        │                                              ▼
        ├── 'currency' key in attributes? ── YES ──► Use 'currency' column
        │                                              │
        NO                                              │
        │                                              ▼
        ▼                                    Resolve via CurrencyResolver
Use app default currency                          (e.g. 'EUR' → Currency::EUR)
```

## Assigning monetary values

The cast accepts:

* `Money`
* integer major-unit values
* decimal strings
* `null`

### Decimal strings

```php
$model->amount = '125.50';
```

For a two-decimal currency, the database stores:

```text
12550
```

### Integers

Integers follow the semantics of `leancaptain/money` and represent **major units**:

```php
$model->amount = 125;
```

For a two-decimal currency, this stores:

```text
12500
```

If you need to work explicitly with minor units, construct a `Money` value first:

```php
use LeanCaptain\Money\Currency;
use LeanCaptain\Money\Money;

$model->amount = Money::fromMinor(
    12550,
    Currency::BDT,
);
```

### Money values

You can assign a `Money` object directly:

```php
use LeanCaptain\Money\Currency;
use LeanCaptain\Money\Money;

$model->amount = Money::of(
    '125.50',
    Currency::BDT,
);
```

The cast persists its integer minor-unit representation.

### Null

Nullable monetary attributes remain nullable:

```php
$model->amount = null;
```

is stored as:

```text
NULL
```

`null` is not converted to zero money.

### Floats

Floating-point monetary input is intentionally not supported:

```php
$model->amount = 125.50; // rejected
```

Use a decimal string instead:

```php
$model->amount = '125.50';
```

This preserves the strict monetary semantics of `leancaptain/money` and avoids floating-point ambiguity.

## Currency safety

The cast does not perform currency conversion.

If a model uses the default currency, assigning a `Money` object with another currency is rejected.

For example, if the configured currency is `BDT`:

```php
$model->amount = Money::of('100', Currency::USD);
```

is invalid.

Likewise, for a multi-currency model:

```text
currency = USD
```

assigning EUR money is rejected rather than silently changing the currency or treating EUR minor units as USD.

Currency conversion and foreign-exchange behavior are intentionally outside the scope of this package.

## Custom currencies

`leancaptain/money` supports application-defined currencies through `CurrencyContract`.

`lara-money` preserves that extensibility.

For a single/default custom currency, configure any implementation of:

```php
use LeanCaptain\Money\Contracts\CurrencyContract;

interface CurrencyContract
{
    public function code(): string;

    public function minorUnit(): int;
}
```

as the application's default currency.

For multi-currency models using stored currency codes, `lara-money` provides a `CurrencyResolver` extension point.

The default resolver supports the currencies built into `leancaptain/money`.

If your application stores additional currencies, provide your own resolver:

```php
use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\Money\Contracts\CurrencyContract;

final class AppCurrencyResolver implements CurrencyResolver
{
    public function resolve(string $code): CurrencyContract
    {
        return AppCurrency::from($code);
    }
}
```

Then replace the default binding in your application's service provider:

```php
use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;

$this->app->singleton(
    CurrencyResolver::class,
    AppCurrencyResolver::class,
);
```

This allows applications to define their own currency domain without requiring changes to either `leancaptain/money` or `leancaptain/lara-money`.

## Built-in currencies

The built-in currencies come from `leancaptain/money`:

```text
AED
AUD
BDT
CAD
CNY
EUR
GBP
INR
JPY
SAR
SGD
USD
```

The built-in list is deliberately curated.

Applications that need additional currencies can implement `CurrencyContract` rather than requiring the core package to maintain an exhaustive currency catalog.

## Rounding

Rounding behavior comes directly from `leancaptain/money`.

Supported modes include:

```php
RoundingMode::REJECT
RoundingMode::HALF_UP
RoundingMode::DOWN
```

The default is:

```php
RoundingMode::REJECT
```

This package does not introduce separate Laravel-specific rounding semantics.

## Design Philosophy

`lara-money` is intentionally a thin adapter.

The dependency direction is:

```text
Laravel application
        ↓
leancaptain/lara-money
        ↓
leancaptain/money
```

The core package remains completely framework-agnostic.

This package aims to:

* store money using integer minor units
* avoid floating-point monetary input
* preserve explicit rounding behavior
* prevent silent currency mismatches
* support single and multi-currency applications
* support consumer-defined currencies
* keep Laravel-specific concerns out of the core package
* avoid unnecessary facades, helpers, and abstractions

For the underlying Money API, arithmetic, comparisons, rounding behavior, exceptions, and currency contracts, see `leancaptain/money`.

## Testing

Run the package test suite with:

```bash
composer test
```

## License

Lara Money is open-source software licensed under the MIT License.
