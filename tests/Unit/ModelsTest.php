<?php

use pivotalso\PivotalAb\Models\Events;
use pivotalso\PivotalAb\Models\Experiments;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\Models\Instance;

test('Instance serializes and deserializes metadata', function () {
    $instance = Instance::create([
        'instance' => 'meta-test-123',
        'identifier' => '127.0.0.1',
        'metadata' => ['user_agent' => 'Test', 'ip' => '127.0.0.1'],
    ]);

    $fresh = Instance::find($instance->id);
    expect($fresh->metadata)->toBe(['user_agent' => 'Test', 'ip' => '127.0.0.1']);
});

test('Instance handles null metadata', function () {
    $instance = Instance::create([
        'instance' => 'null-meta-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);

    $fresh = Instance::find($instance->id);
    expect($fresh->metadata)->toBeNull();
});

test('Instance has events relationship', function () {
    $instance = Instance::create([
        'instance' => 'rel-test-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);

    expect($instance->events)->toBeEmpty();
    expect($instance->goals)->toBeEmpty();
});

test('Events toExport includes related data', function () {
    $instance = Instance::create([
        'instance' => 'export-test-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);
    $experiment = Experiments::firstOrCreate([
        'experiment' => 'export-exp',
        'goal' => 'export-goal',
    ]);
    $event = Events::create([
        'instance_id' => $instance->id,
        'experiments_id' => $experiment->id,
        'name' => 'export-exp',
        'value' => 'variant-a',
    ]);

    $export = $event->toExport();
    expect($export['instance'])->toBe('export-test-123');
    expect($export['experiment'])->toBe('export-exp');
    expect($export['name'])->toBe('export-exp');
});

test('Goal toExport includes instance', function () {
    $instance = Instance::create([
        'instance' => 'goal-export-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);
    $goal = Goal::create([
        'instance_id' => $instance->id,
        'goal' => 'test-goal',
        'value' => ['foo' => 'bar'],
    ]);

    $export = $goal->toExport();
    expect($export['instance'])->toBe('goal-export-123');
});

test('Experiments toExport returns array', function () {
    $experiment = Experiments::firstOrCreate([
        'experiment' => 'toexport-test',
        'goal' => 'toexport-goal',
    ]);

    $export = $experiment->toExport();
    expect($export)->toBeArray();
    expect($export['experiment'])->toBe('toexport-test');
});

test('Events appends instance attribute', function () {
    $instance = Instance::create([
        'instance' => 'append-test-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);
    $experiment = Experiments::firstOrCreate([
        'experiment' => 'append-exp',
        'goal' => 'goal',
    ]);
    $event = Events::create([
        'instance_id' => $instance->id,
        'experiments_id' => $experiment->id,
        'name' => 'append-exp',
        'value' => 'v1',
    ]);

    // The 'instance' accessor should return the instance string
    expect($event->getInstanceAttribute())->toBe('append-test-123');
});

test('Goal appends instance attribute', function () {
    $instance = Instance::create([
        'instance' => 'goal-append-123',
        'identifier' => '127.0.0.1',
        'metadata' => null,
    ]);
    $goal = Goal::create([
        'instance_id' => $instance->id,
        'goal' => 'append-goal',
    ]);

    expect($goal->getInstanceAttribute())->toBe('goal-append-123');
});
