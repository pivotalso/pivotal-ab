<?php

use pivotalso\LaravelAb\Models\Experiments;
use pivotalso\LaravelAb\Models\Goal;

test('Saving experiments', function () {
    $ab = app()->make('Ab');

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
