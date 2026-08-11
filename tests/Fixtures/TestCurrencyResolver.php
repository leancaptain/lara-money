<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney\Tests\Fixtures;

use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\Money\Contracts\CurrencyContract;

final class TestCurrencyResolver implements CurrencyResolver
{
    public function resolve(string $code): CurrencyContract
    {
        return TestCurrency::from($code);
    }
}