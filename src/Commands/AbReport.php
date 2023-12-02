<?php

namespace eighttworules\LaravelAb\Commands;

use eighttworules\LaravelAb\Jobs\GetLists;
use eighttworules\LaravelAb\Jobs\GetReport;
use Illuminate\Console\Command;

class AbReport extends Command
{
    protected $signature = 'ab:report
    {experiment? : Name of the experiment to report on}
    {--list : list experiments in database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'provides statistic on experiments';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $experiment = $this->argument('experiment', false);
        $list = $this->option('list', false);

        if ($list == true) {
            $this->prettyPrint(dispatch_sync(new GetLists()));

            return true;
        }

        if (! empty($experiment)) {
            $this->prettyPrint(dispatch_sync(new GetReport($experiment)));
        } else {
            $reports = dispatch_sync(new GetLists());
            $info = [];
            foreach ($reports as $report) {
                $info[$report->experiment] = dispatch_sync(new GetReport($report->experiment));
            }
            $this->prettyPrint($info);
        }
    }

    public function prettyPrint($info)
    {
        $this->info(json_encode($info, JSON_PRETTY_PRINT));
    }
}
