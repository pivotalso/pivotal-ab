<?php

use pivotalso\PivotalAb\PivotalAb;

beforeEach(function () {
    PivotalAb::resetSession();
});

test('ab:report with --list shows experiments', function () {
    // Create some data first
    PivotalAb::initUser();
    $ab = PivotalAb::choice('cmd-test', ['a', 'b']);
    $ab->track('goal');
    PivotalAb::saveSession();

    $this->artisan('ab:report', ['--list' => true])
        ->assertSuccessful();
});

test('ab:report with experiment name shows report', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('cmd-report', ['x', 'y']);
    $ab->selectOption('x');
    $ab->track('goal');
    PivotalAb::saveSession();

    $this->artisan('ab:report', ['experiment' => 'cmd-report'])
        ->assertSuccessful();
});

test('ab:report with no args shows all reports', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('cmd-all', ['a', 'b']);
    $ab->track('goal');
    PivotalAb::saveSession();

    $this->artisan('ab:report')
        ->assertSuccessful();
});

test('ab:export creates json file', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('export-test', ['a', 'b']);
    $ab->track('goal');
    PivotalAb::saveSession();
    PivotalAb::goal('goal');

    $this->artisan('ab:export')
        ->assertSuccessful();

    // Clean up any exported files
    $files = glob('ab_export_*.json');
    foreach ($files as $file) {
        $contents = json_decode(file_get_contents($file), true);
        expect($contents)->toHaveKeys(['experiments', 'goals', 'instances', 'events']);
        unlink($file);
    }
});
