<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney\Tests;

use LeanCaptain\LaraMoney\LaraMoneyServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    public function getPackageProviders($app): array
    {
        return [
            LaraMoneyServiceProvider::class,
        ];
    }
}
