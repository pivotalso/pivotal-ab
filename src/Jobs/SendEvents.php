<?php

namespace pivotalso\LaravelAb\Jobs;

use Log;
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
                $client = new Client([
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => sprintf('Bearer %s', $key),
                    ],
                ]);
                $url = sprintf('%s/%s', $host, $this->url);
                $client->request('POST', $url, [
                    'body' => json_encode($events),
                ]);
                Log::info('Event sent to API successfully');
                EventQueue::clearEvents();
            } catch (\Exception $e) {
                Log::debug('Unable to send AB test data to API, please check the following error');
                Log::error($e->getMessage());
            }

        }
    }
}
