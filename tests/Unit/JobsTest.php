<?php

use pivotalso\PivotalAb\Jobs\GetLists;
use pivotalso\PivotalAb\Jobs\GetReport;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\PivotalAb;

beforeEach(function () {
    PivotalAb::resetSession();
});

test('GetLists returns empty when no experiments', function () {
    $result = (new GetLists)->handle();

    expect($result)->toHaveCount(0);
});

test('GetLists returns experiments with hit counts', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('list-test', ['a', 'b']);
    $ab->track('goal');
    PivotalAb::saveSession();

    $result = (new GetLists)->handle();

    expect($result)->toHaveCount(1);
    expect($result->first()->experiment)->toBe('list-test');
    expect($result->first()->hits)->toBe(1);
});

test('GetReport returns empty for nonexistent experiment', function () {
    $result = (new GetReport('nonexistent'))->handle();

    expect($result)->toBe([]);
});

test('GetReport returns condition breakdown', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('report-test', ['alpha', 'beta']);
    $ab->selectOption('alpha');
    $ab->track('goal');
    PivotalAb::saveSession();

    $result = (new GetReport('report-test'))->handle();

    expect($result)->toHaveCount(1);
    expect($result[0]['condition'])->toBe('alpha');
    expect($result[0]['hits'])->toBe(1);
});

test('GetReport calculates conversion rate', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('convert-test', ['a', 'b']);
    $ab->selectOption('a');
    $ab->track('goal');
    PivotalAb::saveSession();

    PivotalAb::goal('goal');

    $result = (new GetReport('convert-test'))->handle();

    expect($result[0]['condition'])->toBe('a');
    expect($result[0]['goals'])->toBeGreaterThan(0);
    expect($result[0]['conversion'])->toBeGreaterThan(0);
});

test('GetReport sorts by conversion descending', function () {
    // Create two users with different conditions
    PivotalAb::initUser();
    $ab = PivotalAb::choice('sort-test', ['winner', 'loser']);
    $ab->selectOption('winner');
    $ab->track('goal');
    PivotalAb::saveSession();
    PivotalAb::goal('goal');

    $uid1 = PivotalAb::getSession()->instance;
    PivotalAb::resetSession();

    // Second user gets 'loser', no goal
    $mockRequest = \Illuminate\Support\Facades\Request::create('/', 'GET', ['abid' => 'SORT_USER_2']);
    PivotalAb::initUser($mockRequest);
    $ab2 = PivotalAb::choice('sort-test', ['winner', 'loser']);
    $ab2->selectOption('loser');
    $ab2->track('goal');
    PivotalAb::saveSession();

    $result = (new GetReport('sort-test'))->handle();

    expect($result)->toHaveCount(2);
    expect($result[0]['conversion'])->toBeGreaterThanOrEqual($result[1]['conversion']);
});
