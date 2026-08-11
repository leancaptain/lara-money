<?php

namespace LeanCaptain\LaraMoney\Contracts;

use LeanCaptain\Money\Contracts\CurrencyContract;

interface CurrencyResolver
{
    public function resolve(string $code): CurrencyContract;
}