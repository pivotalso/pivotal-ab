<?php

use pivotalso\PivotalAb\EventQueue;

beforeEach(function () {
    EventQueue::clearEvents();
});

test('starts empty', function () {
    expect(EventQueue::getEvents())->toBe([]);
});

test('addEvent appends to queue', function () {
    $event = (object) ['type' => 'test'];
    EventQueue::addEvent($event);

    expect(EventQueue::getEvents())->toHaveCount(1);
    expect(EventQueue::getEvents()[0])->toBe($event);
});

test('addEvent accumulates multiple events', function () {
    EventQueue::addEvent((object) ['type' => 'a']);
    EventQueue::addEvent((object) ['type' => 'b']);
    EventQueue::addEvent((object) ['type' => 'c']);

    expect(EventQueue::getEvents())->toHaveCount(3);
});

test('clearEvents empties the queue', function () {
    EventQueue::addEvent((object) ['type' => 'test']);
    expect(EventQueue::getEvents())->toHaveCount(1);

    EventQueue::clearEvents();
    expect(EventQueue::getEvents())->toBe([]);
});
