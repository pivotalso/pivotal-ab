<?php

use Illuminate\Support\Facades\Route;
use pivotalso\PivotalAb\Http\Middleware\PivotalAbMiddleware;
use pivotalso\PivotalAb\PivotalAb;

beforeEach(function () {
    PivotalAb::resetSession();
});

test('middleware initializes session and sets cookie', function () {
    Route::middleware(PivotalAbMiddleware::class)->get('/middleware-test', function () {
        return 'ok';
    });

    $response = $this->get('/middleware-test');

    $response->assertStatus(200);
    $response->assertCookie(config('laravel-ab.cache_key'));
});

test('middleware preserves session across requests', function () {
    Route::middleware(PivotalAbMiddleware::class)->get('/mw-session', function () {
        return PivotalAb::getSession()->instance;
    });

    $response1 = $this->get('/mw-session');
    $uid = $response1->getContent();

    expect($uid)->toBeString();
    expect(strlen($uid))->toBeGreaterThan(0);
});
