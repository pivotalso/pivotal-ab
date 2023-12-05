<?php

namespace eighttworules\LaravelAb\Listeners;

use eighttworules\LaravelAb\Events\Track;
use GuzzleHttp\Client;
use ReflectionClass;

class TrackerLogger
{
    /**
     * Create the event listener.
     *
     * @param  Track  $track
     */
    private $url = 'api/events/track';

    /**
     * Handle the event.
     */
    public function handle(Track $event): void
    {
        $key = config('laravel-ab.api_key');
        $host = config('laravel-ab.api_url');
        if (! empty($key) && ! empty($host)) {
            try {
                $client = new Client([
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => sprintf('Bearer %s', $key),
                    ],
                ]);
                $reflect = new ReflectionClass($event->model);
                $data = $event->model->toArray();
                $url = sprintf('%s/%s', $host, $this->url);
                $client->request('POST', $url, [
                    'body' => json_encode(
                        [
                            'payload' => $data,
                            'type' => $reflect->getShortName(),
                        ]
                    ),
                ]);
            } catch (\Exception $e) {
                \Log::debug('Unable to send AB test data to API, please check the following erro');
                \Log::error($e->getMessage());
            }

        }
    }
}
