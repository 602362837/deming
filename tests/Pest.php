<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Disable rate limiting for API tests
uses()->beforeEach(function () {
    $this->withoutMiddleware(ThrottleRequests::class);
})->in('Feature/API');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
