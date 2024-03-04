<?php

namespace pivotalso\LaravelAb\Commands;

use Illuminate\Console\Command;
use pivotalso\LaravelAb\Jobs\GetLists;
use pivotalso\LaravelAb\Jobs\GetReport;
use pivotalso\LaravelAb\Models\Events;
use pivotalso\LaravelAb\Models\Experiments;
use pivotalso\LaravelAb\Models\Goal;
use pivotalso\LaravelAb\Models\Instance;

class AbExport extends Command
{
    protected $signature = 'ab:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'provides a way to send experiment data to pivotal.so';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $experiments = Experiments::orderBy('created_at')->get()->map(function($experiment) {
            return [
                'experiment' => $experiment->experiment,
                'goal' => $experiment->goal,
                'created_at' => $experiment->created_at->toDateTimeString(),
            ];
        });
        $goals = Goal::with('instance')->orderBy('created_at')->get()->map(function($goal) {
            return [
                'goal' => $goal->goal,
                'value' => $goal->value,
                'instance' => $goal->instance->instance,
                'created_at' => $goal->created_at->toDateTimeString(),
            ];
        });
        $instances = Instance::orderBy('created_at')->get()->map(function($instance) {
            return [
                'instance' => $instance->instance,
                'identifier' => $instance->identifier,
                'created_at' => $instance->created_at->toDateTimeString(),
            ];
        });
        $events = Events::with('instance', 'experiment')->orderBy('created_at')->get()->map(function($event) {
            return [
                'experiment' => $event->experiment->experiment,
                'name' => $event->name,
                'value' => $event->value,
                'instance' => $event->instance->instance,
                'created_at' => $event->created_at->toDateTimeString()
            ];
        });

        $data = [
            'experiments' => $experiments,
            'goals' => $goals,
            'instances' => $instances,
            'events' => $events,
        ];

        $data = [
            'experiments' => $experiments,
            'goals' => $goals,
            'instances' => $instances,
            'events' => $events,
        ];

        $filename = sprintf('ab_export_%s.json', date('Y-m-d_H-i-s'));


        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

        $this->info(sprintf('Exported to %s', $filename));
    }

}
