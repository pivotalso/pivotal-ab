<?php

use Illuminate\Support\Facades\Request;
use pivotalso\LaravelAb\Models\Experiments;
use pivotalso\LaravelAb\Models\Goal;
use pivotalso\LaravelAb\Models\Instance;

putenv("LARAVEL_AB_ALLOW_PARAM=true");

test('Saving experiments', function () {
    $ab = app()->make('Ab');

    $ab::initUser();
    $instance = $ab->experiment('Test');
    $instance->condition('one');
    $instance->condition('two');
    $instance->track('goal');

    $ab::goal('goal');
    $ab::saveSession();

    $experiments = Experiments::where(['experiment'=>'Test'])->get();

    $goals = Goal::where(['goal'=>'goal'])->get();

    $experiment = $experiments->first();

    $this->assertEquals($experiments->count(),1);
    $this->assertEquals($experiment->events()->count(), 1);
    $this->assertEquals($goals->count(),1);
});

test('Selection option', function () {
    $ab = app()->make('Ab');

    $ab::initUser();
    $instance = $ab->experiment('select option');
    $instance->saveCondition('one', 'one content');
    $instance->saveCondition('two', 'two content');
    $instance->selectOption('one');
    $ab::saveSession();

    $experiments = Experiments::where(['experiment'=>'select option'])->first();
    $event = $experiments->events()->first();

    $this->assertEquals($event->name, 'select option');
    $this->assertEquals($event->value, 'one');
});

test('Selection set abid', function () {
    $ab = app()->make('Ab');
    $mockRequest = Request::create('http://localhost:8000', 'GET', ['abid' => 'TESTING1234']);
    $ab::resetSession();
    $ab::initUser($mockRequest);
    $instance = $ab->experiment('select option 2');
    $instance->saveCondition('three', 'one content');
    $instance->saveCondition('four', 'two content');
    $instance->selectOption('three');
    $ab::saveSession();

    $instances = Instance::where(['instance'=>'TESTING1234'])->get();
    $experiments = Experiments::where(['experiment'=>'select option 2'])->first();
    $event = $experiments->events()->first();

    $this->assertEquals($event->name, 'select option 2');
    $this->assertEquals($event->value, 'three');
    $this->assertEquals($instances->count(),1);
});
