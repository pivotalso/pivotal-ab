<?php

namespace pivotalso\PivotalAb;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;

use pivotalso\PivotalAb\Commands\AbReport;
use pivotalso\PivotalAb\Commands\AbExport;
use pivotalso\PivotalAb\Http\Middleware\PivotalAbMiddleware;


class PivotalAbServiceProvider extends PackageServiceProvider
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
            ->hasCommands(AbReport::class, AbExport::class)
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
        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddlewareToGroup('web', PivotalAbMiddleware::class);
        $this->app->bind('Ab', PivotalAb::class);
        $this->registerCompiler();
    }

    //    public function boot(): void
    //    {
    //        parent::boot();
    //        Blade::directive('ab', function (string $expression) {
    //            return sprintf("<H1>hi i'm an ab test %s after</H1>", $expression);
    //        });
    //    }


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

            $view = preg_replace('/@goal\(([^\)]+)\)/i', "<?php App::make('Ab')::goal($1); ?>", $view);

            return $view;
        });
    }
}
