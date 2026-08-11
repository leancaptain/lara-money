<?php

namespace LeanCaptain\LaraMoney;

use LeanCaptain\LaraMoney\Contracts\CurrencyResolver;
use LeanCaptain\Money\Contracts\CurrencyContract;
use LeanCaptain\Money\Currency;

class DefaultCurrencyResolver implements CurrencyResolver
{
    public function resolve(string $code): CurrencyContract
    {
        return Currency::from($code);
    }
}