<?php

declare(strict_types=1);

namespace LeanCaptain\LaraMoney\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class Product extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}