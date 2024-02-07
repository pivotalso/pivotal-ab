<?php

namespace pivotalso\LaravelAb\Jobs;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use pivotalso\LaravelAb\EventQueue;
use ReflectionClass;

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
        $host = config('laravel-ab.api_url');
        $events = [];
        $queue = EventQueue::getEvents();
        Log::debug('Sending events to API');
        Log::debug('key');
        Log::debug($key);
        Log::debug('host');
        Log::debug($host);
        Log::debug('queue');
        Log::debug(json_encode($queue));
        if (! empty($key) && ! empty($host) && count($queue) > 0) {
            Log::debug('sneding');
            foreach ($queue as $event) {
                $reflect = new ReflectionClass($event->model);
                $data = $event->model->toArray();
                $events[] = [
                    'type' => $reflect->getShortName(),
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
                $response = $client->request('POST', $url, [
                    'body' => json_encode($events),
                ]);
                Log::debug('API response');
                Log::debug($response->getBody());
                EventQueue::clearEvents();
            } catch (\Exception $e) {
                \Log::debug('Unable to send AB test data to API, please check the following erro');
                \Log::error($e->getMessage());
            }

        }
    }
}
