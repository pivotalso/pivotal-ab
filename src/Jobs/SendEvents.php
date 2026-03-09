<?php

namespace pivotalso\PivotalAb\Jobs;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use pivotalso\PivotalAb\EventQueue;
use ReflectionClass;

class SendEvents implements ShouldQueue
{
    private string $url = 'api/events/track';

    public function handle(): void
    {
        $key = config('laravel-ab.api_key');
        $host = config('laravel-ab.api_url', 'https://ab.pivotal.so');
        $queue = EventQueue::getEvents();

        if (empty($key) || empty($host) || count($queue) === 0) {
            return;
        }

        $events = [];
        foreach ($queue as $event) {
            $reflect = new ReflectionClass($event->model);
            $events[] = [
                'type' => ! empty($event->type) ? $event->type : $reflect->getShortName(),
                'payload' => $event->model->toExport(),
            ];
        }

        try {
            $client = new Client([
                'base_uri' => $host,
                'connect_timeout' => 2.0,
                'timeout' => 2.0,
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
        } catch (\Exception $e) {
            Log::debug('Unable to send AB test data to API, please check the following error');
            Log::error($e->getMessage());
        }
    }
}
