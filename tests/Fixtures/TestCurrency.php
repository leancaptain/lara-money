<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney\Tests\Fixtures;

use LeanCaptain\Money\Contracts\CurrencyContract;

enum TestCurrency: string implements CurrencyContract
{
    case CAD = 'CAD';

    public function code(): string
    {
        return $this->value;
    }

    public function minorUnit(): int
    {
        return 2;
    }
}