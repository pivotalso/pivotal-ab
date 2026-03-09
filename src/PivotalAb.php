<?php

namespace pivotalso\PivotalAb;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use pivotalso\PivotalAb\Jobs\SendEvents;
use pivotalso\PivotalAb\Models\Events;
use pivotalso\PivotalAb\Models\Experiments;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\Models\Instance;

class PivotalAb
{
    protected static ?Instance $session = null;

    /** @var array<string, PivotalAb> */
    protected static array $instance = [];

    /** @var Collection<int, Events> */
    protected static Collection $events;

    protected string $name = '';

    /** @var array<string, string> */
    protected array $conditions = [];

    protected ?string $fired = null;

    protected ?string $goal = null;

    protected bool $usesOutputBuffering = false;

    public static function initUser(?Request $request = null): void
    {
        $key = config('laravel-ab.cache_key');
        $param_key = config('laravel-ab.request_param');

        if (self::$session !== null) {
            return;
        }

        $client = $request?->getClientIp() ?? Str::random(12);

        $uid = null;
        if (config('laravel-ab.allow_param') && $request?->has($param_key)) {
            $uid = $request->input($param_key);
        }
        $uid = $uid ?: session()->get($key);
        $uid = $uid ?: Cookie::get($key);
        $uid = $uid ?: md5(uniqid().$client);

        session()->put($key, $uid);

        /** @var array<string, mixed> $metadata */
        $metadata = function_exists('laravel_ab_meta') ? call_user_func('laravel_ab_meta') : []; // @phpstan-ignore argument.type
        $captured_data = [];
        if ($request) {
            $captured_data = [
                'user_agent' => $request->header('User-Agent'),
                'ip' => $client,
                'referrer' => $request->header('referer'),
                'url' => $request->fullUrl(),
            ];
        }

        self::$session = Instance::firstOrCreate(
            ['instance' => $uid],
            [
                'instance' => $uid,
                'identifier' => $client,
                'metadata' => array_merge($metadata, $captured_data),
            ]
        );
        self::$events = self::$session->events()->get();
    }

    /**
     * Persist all experiment selections to storage and dispatch API sync.
     */
    public static function saveSession(): ?string
    {
        if (! empty(self::$instance) && self::$session) {
            foreach (self::$instance as $ab) {
                $experiment = Experiments::firstOrCreate([
                    'experiment' => $ab->name,
                    'goal' => $ab->goal,
                ]);
                $event = Events::firstOrCreate([
                    'instance_id' => self::$session->id,
                    'experiments_id' => $experiment->id,
                    'name' => $ab->name,
                    'value' => $ab->fired,
                ]);
                self::$session->events()->save($event);
            }
        }

        dispatch(new SendEvents);

        return session()->get(config('laravel-ab.cache_key'));
    }

    public function selectOption(string $option): self
    {
        $this->fired = $option;

        return $this;
    }

    public function experiment(string $experiment): self
    {
        $this->name = $experiment;
        self::$instance[$experiment] = $this;

        return $this;
    }

    /**
     * Set the goal, close output buffering, and return the selected condition's content.
     */
    public function track(string $goal): string
    {
        $this->goal = $goal;

        if ($this->usesOutputBuffering && ob_get_level()) {
            ob_end_clean();
        }

        $conditions = $this->buildWeightedConditions();

        if (empty($this->fired) || ! isset($this->conditions[$this->fired])) {
            $fired = $this->hasExperiment($this->name);
            if ($fired !== false && isset($this->conditions[$fired])) {
                $this->fired = $fired;
            } else {
                shuffle($conditions);
                $this->fired = current($conditions) ?: array_key_first($this->conditions);
            }
        }

        return $this->conditions[$this->fired] ?? '';
    }

    /**
     * Track a goal completion for the current session.
     */
    public static function goal(string $goal, mixed $value = null): ?Goal
    {
        if (self::$session === null) {
            return null;
        }

        $goalModel = Goal::create([
            'instance_id' => self::$session->id,
            'goal' => $goal,
            'value' => $value,
        ]);
        self::$session->goals()->save($goalModel);

        return $goalModel;
    }

    /**
     * Capture HTML between conditions using output buffering.
     */
    public function condition(string $condition): void
    {
        $this->usesOutputBuffering = true;

        if (count($this->conditions) !== 0) {
            ob_end_clean();
        }

        $this->saveCondition($condition, '');

        ob_start(function (string $data) use ($condition): string {
            $this->saveCondition($condition, $data);

            return '';
        });
    }

    public static function getSession(): ?Instance
    {
        return self::$session;
    }

    public function saveCondition(string $condition, string $data): void
    {
        $this->conditions[$condition] = $data;
    }

    /**
     * Check if a user already has a variant assigned for this experiment.
     */
    public function hasExperiment(string $experiment): string|false
    {
        if (! isset(self::$events)) {
            return false;
        }

        foreach (self::$events as $event) {
            if ($event->name === $experiment) {
                return $event->value;
            }
        }

        return false;
    }

    /**
     * Programmatic API for creating an experiment with named conditions.
     *
     * @param  array<int, string>  $conditions
     */
    public static function choice(string $experiment, array $conditions): self
    {
        $ab = new self;
        $ab->experiment($experiment);
        foreach ($conditions as $condition) {
            $ab->conditions[$condition] = $condition;
        }

        return $ab;
    }

    public static function resetSession(): void
    {
        self::$session = null;
        self::$instance = [];
        self::$events = new Collection;
    }

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return [$this->name => $this->fired];
    }

    /** @return array<string, PivotalAb> */
    public function getEvents(): array
    {
        return self::$instance;
    }

    public static function sendEvent(string $event, mixed $payload): void
    {
        if (self::$session === null) {
            return;
        }

        $model = new Goal;
        $model->goal = $event;
        $model->value = $payload;
        $model->instance_id = self::$session->id;

        $track = new \pivotalso\PivotalAb\Events\Track($model);
        EventQueue::addEvent($track);
    }

    /**
     * Expand weighted conditions into a flat list for random selection.
     *
     * @return array<int, string>
     */
    protected function buildWeightedConditions(): array
    {
        $weighted = [];
        foreach ($this->conditions as $key => $content) {
            if (preg_match('/\[(\d+)\]/', $key, $matches)) {
                for ($i = 0; $i < (int) $matches[1]; $i++) {
                    $weighted[] = $key;
                }
            }
        }

        return $weighted ?: array_keys($this->conditions);
    }
}
