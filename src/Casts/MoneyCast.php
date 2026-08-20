<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\Money\Contracts\CurrencyContract;
use LeanCaptain\Money\Money;
use LeanCaptain\Money\MoneyFactory;

final readonly class MoneyCast implements CastsAttributes
{
    public function __construct(
        private ?string $currencyAttribute = null,
    ) {}

    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): ?Money {
        if ($value === null) {
            return null;
        }

        return Money::fromMinor(
            (int) $value,
            $this->expectedCurrency($key, $attributes),
        );
    }

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): ?int {
        if ($value === null) {
            return null;
        }

        return $this->normalizeMoney(
            $key,
            $value,
            $attributes,
        )->minorAmount();
    }

    /*
     *
     * ======== Private Methods ========
     *
     */
    private function normalizeMoney(
        string $key,
        mixed $value,
        array $attributes,
    ): Money {
        if ($value instanceof Money) {
            $money = $value;
        } elseif (is_int($value) || is_string($value)) {
            $money = $this->moneyFactory($key, $attributes)->of($value);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Money attribute [%s] must be null, Money, int, or string.',
                    $key,
                ),
            );
        }

        $expectedCurrency = $this->expectedCurrency($key, $attributes);

        if ($money->currency()->code() !== $expectedCurrency->code()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Currency mismatch for money attribute [%s]: expected [%s], got [%s].',
                    $key,
                    $expectedCurrency->code(),
                    $money->currency()->code(),
                ),
            );
        }

        return $money;
    }

    private function moneyFactory(
        string $key,
        array $attributes,
    ): MoneyFactory {
        $factory = app(MoneyFactory::class);
        $currencyAttribute = $this->currencyAttribute($attributes);

        if ($currencyAttribute === null) {
            return $factory;
        }

        return new MoneyFactory(
            currency: $this->resolveCurrency(
                $currencyAttribute,
                $key,
                $attributes,
            ),
            roundingMode: $factory->roundingMode(),
        );
    }

    private function expectedCurrency(
        string $key,
        array $attributes,
    ): CurrencyContract {
        $currencyAttribute = $this->currencyAttribute($attributes);

        if ($currencyAttribute === null) {
            return app(MoneyFactory::class)->currency();
        }

        return $this->resolveCurrency(
            $currencyAttribute,
            $key,
            $attributes,
        );
    }

    private function currencyAttribute(array $attributes): ?string
    {
        if ($this->currencyAttribute !== null) {
            return $this->currencyAttribute;
        }

        if (array_key_exists('currency', $attributes)) {
            return 'currency';
        }

        return null;
    }

    private function resolveCurrency(
        string $currencyAttribute,
        string $key,
        array $attributes,
    ): CurrencyContract {
        $currencyCode = $attributes[$currencyAttribute] ?? null;

        if (! is_string($currencyCode) || $currencyCode === '') {
            throw new \InvalidArgumentException(
                sprintf(
                    'Currency attribute [%s] is missing or invalid for money attribute [%s].',
                    $currencyAttribute,
                    $key,
                ),
            );
        }

        return app(CurrencyResolver::class)->resolve($currencyCode);
    }
}