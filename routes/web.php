<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use pivotalso\PivotalAb\Http\Middleware\PivotalBasicAuthMiddleware;
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
if(config('laravel-ab.report_url') && config('laravel-ab.report_username') && config('laravel-ab.report_password')) {
    Route::middleware(PivotalBasicAuthMiddleware::class)->group(function(){
        $path = config('laravel-ab.report_url');
        $logout = sprintf('%s/logout', $path);
        Route::get($logout, function () {
            $_SERVER['PHP_AUTH_USER'] = "";
            $_SERVER['PHP_AUTH_PW'] = "";
            return redirect('/')->withHeaders([
                'HTTP/1.1 401 Authorization Required',
                'WWW-Authenticate: Basic realm="Access denied"',
            ]);
        })->name('logout');

        $url = sprintf('%s/{id?}', $path);
        Route::get($url, function (Request $request) use ($path) {
            $id = $request->get('id', null);
            $reports = dispatch_sync(new GetLists());
            $experiments = [];
            foreach ($reports as $report) {
                $experiments[] = [
                    'id'=> $report->id,
                    'name' => $report->experiment,
                    'conditions' => dispatch_sync(new GetReport($report->experiment)),
                ];
            }
            if ($id) {
                $experiment = current(array_filter($experiments, function ($experiment) use ($id) {
                    return $experiment['id'] == $id;
                }));
            } else {
                $experiment = $experiments[0];
            }
            return view('laravel-ab::report', compact('experiments', 'experiment', 'path', 'id'));
        });
    });
}
