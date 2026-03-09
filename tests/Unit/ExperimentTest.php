<?php

use Illuminate\Support\Facades\Request;
use pivotalso\PivotalAb\Models\Experiments;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\Models\Instance;
use pivotalso\PivotalAb\PivotalAb;

putenv('LARAVEL_AB_ALLOW_PARAM=true');

beforeEach(function () {
    PivotalAb::resetSession();
});

test('Saving experiments', function () {
    $ab = app()->make('Ab');

    $ab::initUser();
    $instance = $ab->experiment('Test');
    $instance->condition('one');
    $instance->condition('two');
    $instance->track('goal');

    $ab::goal('goal');
    $ab::saveSession();

    $experiments = Experiments::where(['experiment' => 'Test'])->get();
    $goals = Goal::where(['goal' => 'goal'])->get();
    $experiment = $experiments->first();

    expect($experiments)->toHaveCount(1);
    expect($experiment->events()->count())->toBe(1);
    expect($goals)->toHaveCount(1);
});

test('Selection option', function () {
    $ab = app()->make('Ab');

    $ab::initUser();
    $instance = $ab->experiment('select option');
    $instance->saveCondition('one', 'one content');
    $instance->saveCondition('two', 'two content');
    $instance->selectOption('one');
    $ab::saveSession();

    $experiments = Experiments::where(['experiment' => 'select option'])->first();
    $event = $experiments->events()->first();

    expect($event->name)->toBe('select option');
    expect($event->value)->toBe('one');
});

test('Selection set abid', function () {
    $ab = app()->make('Ab');
    $mockRequest = Request::create('http://localhost:8000', 'GET', ['abid' => 'TESTING1234']);
    $ab::initUser($mockRequest);
    $instance = $ab->experiment('select option 2');
    $instance->saveCondition('three', 'one content');
    $instance->saveCondition('four', 'two content');
    $instance->selectOption('three');
    $ab::saveSession();

    $instances = Instance::where(['instance' => 'TESTING1234'])->get();
    $experiments = Experiments::where(['experiment' => 'select option 2'])->first();
    $event = $experiments->events()->first();

    expect($event->name)->toBe('select option 2');
    expect($event->value)->toBe('three');
    expect($instances)->toHaveCount(1);
});

test('choice API selects from given conditions', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('color-test', ['red', 'blue', 'green']);
    $result = $ab->track('conversion');

    expect($result)->toBeIn(['red', 'blue', 'green']);
});

test('choice API persists to database', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('button-test', ['primary', 'secondary']);
    $ab->track('click');
    PivotalAb::saveSession();

    $experiment = Experiments::where('experiment', 'button-test')->first();
    expect($experiment)->not->toBeNull();
    expect($experiment->goal)->toBe('click');
    expect($experiment->events()->count())->toBe(1);
});

test('goal returns null when no session exists', function () {
    expect(PivotalAb::goal('some-goal'))->toBeNull();
});

test('goal creates record when session exists', function () {
    PivotalAb::initUser();
    $goal = PivotalAb::goal('purchase', ['amount' => 99]);

    expect($goal)->toBeInstanceOf(Goal::class);
    expect($goal->goal)->toBe('purchase');
    expect($goal->value)->toBe(['amount' => 99]);
});

test('goal without value stores null', function () {
    PivotalAb::initUser();
    $goal = PivotalAb::goal('pageview');

    expect($goal)->toBeInstanceOf(Goal::class);
    expect($goal->value)->toBeNull();
});

test('getSession returns null before init', function () {
    expect(PivotalAb::getSession())->toBeNull();
});

test('getSession returns instance after init', function () {
    PivotalAb::initUser();

    expect(PivotalAb::getSession())->toBeInstanceOf(Instance::class);
    expect(PivotalAb::getSession()->instance)->toBeString();
});

test('resetSession clears all state', function () {
    PivotalAb::initUser();
    expect(PivotalAb::getSession())->not->toBeNull();

    PivotalAb::resetSession();
    expect(PivotalAb::getSession())->toBeNull();
});

test('initUser reuses existing session on second call', function () {
    PivotalAb::initUser();
    $first = PivotalAb::getSession();

    PivotalAb::initUser();
    $second = PivotalAb::getSession();

    expect($first->id)->toBe($second->id);
});

test('hasExperiment returns false when no prior experiment', function () {
    PivotalAb::initUser();
    $ab = new PivotalAb;

    expect($ab->hasExperiment('nonexistent'))->toBeFalse();
});

test('hasExperiment returns existing variant', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('sticky-test', ['a', 'b']);
    $ab->track('goal');
    PivotalAb::saveSession();

    $uid = PivotalAb::getSession()->instance;
    PivotalAb::resetSession();

    $mockRequest = Request::create('http://localhost:8000', 'GET', ['abid' => $uid]);
    PivotalAb::initUser($mockRequest);

    $ab2 = new PivotalAb;
    expect($ab2->hasExperiment('sticky-test'))->toBeIn(['a', 'b']);
});

test('track returns same variant for returning user', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('consistent-test', ['x', 'y']);
    $first = $ab->track('goal');
    PivotalAb::saveSession();

    $uid = PivotalAb::getSession()->instance;
    PivotalAb::resetSession();

    $mockRequest = Request::create('http://localhost:8000', 'GET', ['abid' => $uid]);
    PivotalAb::initUser($mockRequest);

    $ab2 = PivotalAb::choice('consistent-test', ['x', 'y']);
    $second = $ab2->track('goal');

    expect($second)->toBe($first);
});

test('selectOption forces a specific variant', function () {
    PivotalAb::initUser();
    $ab = new PivotalAb;
    $ab->experiment('forced-test');
    $ab->saveCondition('a', 'content-a');
    $ab->saveCondition('b', 'content-b');
    $ab->selectOption('b');

    expect($ab->track('goal'))->toBe('content-b');
});

test('toArray returns experiment name and fired value', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('array-test', ['one', 'two']);
    $ab->track('goal');

    $array = $ab->toArray();
    expect($array)->toHaveKey('array-test');
    expect($array['array-test'])->toBeIn(['one', 'two']);
});

test('getEvents returns tracked experiments', function () {
    PivotalAb::initUser();
    $ab = PivotalAb::choice('event-test', ['a', 'b']);
    $ab->track('goal');

    $events = $ab->getEvents();
    expect($events)->toHaveKey('event-test');
    expect($events['event-test'])->toBeInstanceOf(PivotalAb::class);
});

test('multiple experiments tracked independently', function () {
    PivotalAb::initUser();

    $ab1 = PivotalAb::choice('exp-1', ['a', 'b']);
    $ab1->track('goal-1');

    $ab2 = PivotalAb::choice('exp-2', ['x', 'y']);
    $ab2->track('goal-2');

    PivotalAb::saveSession();

    expect(Experiments::where('experiment', 'exp-1')->count())->toBe(1);
    expect(Experiments::where('experiment', 'exp-2')->count())->toBe(1);
});

test('initUser captures metadata from request', function () {
    $mockRequest = Request::create(
        'http://localhost:8000/test?abid=META123',
        'GET',
        ['abid' => 'META123'],
        [],
        [],
        ['HTTP_USER_AGENT' => 'TestBrowser/1.0', 'HTTP_REFERER' => 'http://google.com']
    );

    PivotalAb::initUser($mockRequest);
    $session = PivotalAb::getSession();

    expect($session->instance)->toBe('META123');
});

test('saveSession returns session key', function () {
    PivotalAb::initUser();
    $result = PivotalAb::saveSession();

    expect($result)->toBeString();
});

test('saveSession with no experiments still returns key', function () {
    PivotalAb::initUser();
    $result = PivotalAb::saveSession();

    expect($result)->toBeString();
    expect(Experiments::count())->toBe(0);
});
