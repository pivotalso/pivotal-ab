<?php

namespace pivotalso\PivotalAb;

use Illuminate\Support\Facades\Blade;
use pivotalso\PivotalAb\Commands\AbExport;
use pivotalso\PivotalAb\Commands\AbReport;
use pivotalso\PivotalAb\Http\Middleware\PivotalAbMiddleware;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PivotalAbServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
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

    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/ab.php', 'laravel-ab');
        $this->app->bind('Ab', PivotalAb::class);

        $this->registerBladeDirectives();
    }

    public function boot(): void
    {
        parent::boot();

        $router = $this->app->make('router');
        $router->pushMiddlewareToGroup('web', PivotalAbMiddleware::class);
    }

    protected function registerBladeDirectives(): void
    {
        Blade::extend(function (string $view): string {
            // Process @ab...@track blocks, handling nested experiments
            while (preg_match_all('/@ab(?:.(?!@track|@ab))+.@track\([^\)]+\)+/si', $view, $matches)) {
                foreach ($matches[0] as $block) {
                    $view = str_replace($block, $this->compileAbBlock($block), $view);
                }
            }

            // Process standalone @goal directives
            $view = preg_replace(
                '/@goal\(([^\)]+)\)/i',
                "<?php App::make('Ab')::goal($1); ?>",
                $view
            );

            return $view;
        });
    }

    protected function compileAbBlock(string $block): string
    {
        if (! preg_match("/@ab\(([^\)]+)\)/", $block, $match)) {
            throw new \RuntimeException('Experiment with no name not allowed');
        }

        $experimentName = preg_replace('/[^a-z0-9\_]/i', '', $match[1]);
        $instanceVar = $experimentName.'_'.preg_replace('/[^0-9]/', '', microtime().rand(100000, 999999));

        $compiled = preg_replace(
            '/@ab\(.([^\)]+).\)/i',
            "<?php \${$instanceVar} = App::make('Ab')->experiment('{$experimentName}'); ?>",
            $block
        );

        $compiled = preg_replace(
            '/@condition\(([^\)]+)\)/i',
            "<?php \${$instanceVar}->condition($1); ?>",
            $compiled
        );

        $compiled = preg_replace(
            '/@track\(([^\)]+)\)/i',
            "<?php echo \${$instanceVar}->track($1); ?>",
            $compiled
        );

        return $compiled;
    }
}
