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
        Log::debug("sending events");

        $key = config('laravel-ab.api_key');
        $host = env('LARAVEL_AB_API_URL', 'https://ab.yosc.xyz'); // TODO - change before launch
        $events = [];
        $queue = EventQueue::getEvents();

        Log::debug("send event fired");
        Log::debug(json_encode($queue));

        if (! empty($key) && ! empty($host) && count($queue) > 0) {
            Log::debug('a');
            foreach ($queue as $event) {
                Log::debug('b');
                $reflect = new ReflectionClass($event->model);
                Log::debug('c');

                $data = $event->model->toArray();
                $type = !empty($event->type) ? $event->type : $reflect->getShortName();
                $events[] = [
                    'type' => $type,
                    'payload' => $data,
                ];
                Log::debug('d');
            }
            try {
                Log::debug('e');
                if (!empty($events)) {
                    Log::debug('f');
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
                    Log::debug($this->url);

                    $client->request('POST', $this->url, [
                        'body' => json_encode($events),
                    ]);
                    Log::info('Event sent to API successfully');
                    EventQueue::clearEvents();
                }

            } catch (\Exception $e) {
                Log::debug('Unable to send AB test data to API, please check the following error');
                Log::error($e->getMessage());
            }

        }
    }
}
