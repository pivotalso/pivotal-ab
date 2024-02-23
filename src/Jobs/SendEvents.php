<?php

namespace pivotalso\LaravelAb\Jobs;

use \Illuminate\Support\Facades\Log;
use ReflectionClass;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;

use pivotalso\LaravelAb\EventQueue;

class SendEvents implements ShouldQueue
{
    /**
     * Execute the job.
     * Return a list of experiments and their hits
     */
    private $url = 'api/events/track';

    public function handle()
    {

        $key = config('laravel-ab.api_key');
        $host = env('LARAVEL_AB_API_URL', 'https://ab.yosc.xyz'); // TODO - change before launch
        $events = [];
        $queue = EventQueue::getEvents();


        if (! empty($key) && ! empty($host) && count($queue) > 0) {
            foreach ($queue as $event) {
                $reflect = new ReflectionClass($event->model);

                $data = $event->model->toArray();
                $type = !empty($event->type) ? $event->type : $reflect->getShortName();
                $events[] = [
                    'type' => $type,
                    'payload' => $data,
                ];
            }
            try {
                if (!empty($events)) {
                    $client = new Client([
                        'base_uri' => $host,
                        'connect_timeout' => true,
                        'timeout'         => 2.0,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Authorization' => sprintf('Bearer %s', $key),
                        ],
                    ]);

                    $client->request('POST', $this->url, [
                        'body' => json_encode($events),
                    ]);
                    EventQueue::clearEvents();
                }

            } catch (\Exception $e) {
                Log::debug('Unable to send AB test data to API, please check the following error');
                Log::error($e->getMessage());
            }

        }
    }
}
