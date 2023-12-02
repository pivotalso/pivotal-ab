<?php

namespace eighttworules\LaravelAb;

use eighttworules\LaravelAb\Commands\AbReport;
use eighttworules\LaravelAb\Http\Middleware\LaravelAbMiddleware;
use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelAbServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-ab')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_laravel_ab_events_table',
                'create_laravel_ab_experiments_table',
                'create_laravel_ab_goal_table',
                'create_laravel_ab_instance_table',
            ])
            ->hasViews('laravel-ab')
            ->hasRoute('web')
            ->hasCommand(AbReport::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });

    }

    public function register()
    {
        parent::register();
        $this->mergeConfigFrom(
            __DIR__.'/../config/ab.php', 'laravel-ab'
        );
        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware(LaravelAbMiddleware::class);
        $this->app->bind('Ab', LaravelAb::class);
        $this->registerCompiler();
        $this->registerCommands();
    }

    //    public function boot(): void
    //    {
    //        parent::boot();
    //        Blade::directive('ab', function (string $expression) {
    //            return sprintf("<H1>hi i'm an ab test %s after</H1>", $expression);
    //        });
    //    }

    public function registerCommands()
    {

        //        $this->app->singleton('command.ab.report', function ($app) {
        //            return new AbReport();
        //        });
        //
        //        $this->commands('command.ab.report');
    }

    public function registerCompiler()
    {
        Blade::extend(function ($view, $compiler) {

            while (preg_match_all('/@ab(?:.(?!@track|@ab))+.@track\([^\)]+\)+/si', $view, $sections_matches)) {
                $sections = current($sections_matches);
                foreach ($sections as $block) {
                    $instance_id = preg_replace('/[^0-9]/', '', microtime().rand(100000, 999999));

                    if (preg_match("/@ab\(([^\)]+)\)/", $block, $match)) {
                        $experiment_name = preg_replace('/[^a-z0-9\_]/i', '', $match[1]);
                        $instance = $experiment_name.'_'.$instance_id;
                    } else {
                        throw new \Exception('Experiment with no name not allowed');
                    }
                    $copy = preg_replace('/@ab\(.([^\)]+).\)/i', "<?php \${$instance} = App::make('Ab')->experiment('{$experiment_name}'); ?>", $block);

                    $copy = preg_replace('/@condition\(([^\)]+)\)/i', "<?php \${$instance}->condition($1); ?>", $copy);

                    $copy = preg_replace('/@track\(([^\)]+)\)/i', "<?php echo \${$instance}->track($1); ?>", $copy);

                    $view = str_replace($block, $copy, $view);
                }
            }

            $view = preg_replace('/@goal\(([^\)]+)\)/i', "<?php App::make('Ab')->goal($1); ?>", $view);

            return $view;
        });
    }
}
