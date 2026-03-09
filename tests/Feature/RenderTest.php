<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

it('nested routes', function () {
    Route::get('/nested', function () {
        $content = file_get_contents(__DIR__.'/templates/nested.blade.php');

        return Blade::render($content);
    });
    $response = $this->get('/nested');
    $expect = expect($response->getContent());
    $response->assertStatus(200);
    $expect->toContain('Test-');
    $expect->toContain('Test-3');
    $expect->not->toContain('@ab');
});

it('weighted routes', function () {
    Route::get('/weighted', function () {
        $content = file_get_contents(__DIR__.'/templates/weighted.blade.php');

        return Blade::render($content);
    });
    $response = $this->get('/weighted');
    $expect = expect($response->getContent());
    $response->assertStatus(200);
    $expect->toContain('YES-SEE-THIS');
    $expect->not->toContain('DONT-SEE-THIS');
});
