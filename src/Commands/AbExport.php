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
        $experiments = Experiments::orderBy('created_at')->get();
        $goals = Goal::orderBy('created_at')->get();
        $instances = Instance::orderBy('created_at')->get();
        $events = Events::orderBy('created_at')->get();

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
