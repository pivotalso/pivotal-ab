<?php

namespace pivotalso\PivotalAb\Commands;

use Illuminate\Console\Command;
use pivotalso\PivotalAb\Jobs\GetLists;
use pivotalso\PivotalAb\Jobs\GetReport;

class AbReport extends Command
{
    protected $signature = 'ab:report
    {experiment? : Name of the experiment to report on}
    {--list : list experiments in database}';

    protected $description = 'Provides statistics on experiments';

    public function handle(): void
    {
        $experiment = $this->argument('experiment');
        $list = $this->option('list');

        if ($list) {
            $this->prettyPrint(dispatch_sync(new GetLists));

            return;
        }

        if (! empty($experiment)) {
            $this->prettyPrint(dispatch_sync(new GetReport($experiment)));
        } else {
            $reports = dispatch_sync(new GetLists);
            $info = [];
            foreach ($reports as $report) {
                $info[$report->experiment] = dispatch_sync(new GetReport($report->experiment));
            }
            $this->prettyPrint($info);
        }
    }

    protected function prettyPrint(mixed $info): void
    {
        $this->info(json_encode($info, JSON_PRETTY_PRINT));
    }
}
