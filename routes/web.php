<?php

use Illuminate\Support\Facades\Route;
use pivotalso\PivotalAb\Jobs\GetLists;
use pivotalso\PivotalAb\Jobs\GetReport;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/ab/report', function () {
    $reports = dispatch_sync(new GetLists());
    $experiments = [];
    foreach ($reports as $report) {
        $experiments[] = [
            'name' => $report->experiment,
            'conditions' => dispatch_sync(new GetReport($report->experiment)),
        ];
    }

    return view('laravel-ab::report', compact('experiments'));
});
