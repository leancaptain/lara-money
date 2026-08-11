<?php

declare(strict_types=1);

it('boots a Laravel application', function (): void {
    expect($this->app)->not->toBeNull();
});