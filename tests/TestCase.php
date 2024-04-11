<?php

namespace pivotalso\PivotalAb\Tests;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;
use pivotalso\PivotalAb\PivotalAbServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'pivotalso\\PivotalAb\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

    }

    protected function getPackageProviders($app)
    {
        return [
            PivotalAbServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        $dbPath = __DIR__.'/db.sqlite';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);
        config()->set('database.connections.sqlite.database', $dbPath);
        foreach([
            'create_laravel_ab_events_table.php',
            'create_laravel_ab_experiments_table.php',
            'create_laravel_ab_goal_table.php',
            'create_laravel_ab_instance_table.php'] as $filepath) {
            $migration = include sprintf(__DIR__.'/../database/migrations/%s.stub', $filepath);
            $migration->up();
        }

    }
}
