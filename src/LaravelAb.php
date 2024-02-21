<?php

namespace pivotalso\LaravelAb;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use pivotalso\LaravelAb\Jobs\SendEvents;
use pivotalso\LaravelAb\Models\Events;
use pivotalso\LaravelAb\Models\Experiments;
use pivotalso\LaravelAb\Models\Goal;
use pivotalso\LaravelAb\Models\Instance;

class LaravelAb
{
    /**
     * @var static
     *             Instance Object to identify user's session
     */
    protected static $session;

    /**
     * @var
     *
     * Tracks every $experiment->fired condition the view is initiating
     * and event key->value pais for the instance
     */
    protected static $instance = [];

    protected static $events = [];

    /*
     * Individual Test Parameters
     */
    protected $name;

    protected $conditions = [];

    protected $fired;

    protected $goal;


    public static function initUser(Request $request = null)
    {
        $key = config('laravel-ab.cache_key');
        if (empty(self::$session)) {
            $uid = session()->get($key);
            $client = Str::random(12);
            if (!empty($request)) {
                $client = $request->getClientIp();
            }
            if (empty($uid)) {
                $uid = Cookie::get($key);
            }
            if (empty($uid)) {
                $uid = md5(uniqid().$client);
            }
            session()->put($key, $uid);
            self::$session = Instance::firstOrCreate(
                [
                    'instance' => $uid
                ],
                [
                'instance' => $uid,
                'identifier' => $client,
                'metadata' => (function_exists('laravel_ab_meta') ? call_user_func('laravel_ab_meta') : null),
                ]
            );
            self::$events =  self::$session->events()->get();
        }
    }

    /**
     * When the view is rendered, this funciton saves all event->firing pairing to storage.
     */
    public static function saveSession()
    {
        if (! empty(self::$instance)) {
            foreach (self::$instance as $event) {
                $experiment = Experiments::firstOrCreate([
                    'experiment' => $event->name,
                    'goal' => $event->goal,
                ]);
                $event = Events::firstOrCreate([
                    'instance_id' => self::$session->id,
                    'experiments_id' => $experiment->id,
                    'name' => $event->name,
                    'value' => $event->fired,
                ]);
                self::$session->events()->save($event);
            }
        }
        dispatch(new SendEvents());
        return session()->get(config('laravel-ab.cache_key'));
    }

    /**
     * @return $this
     *
     * Used to track the name of the experiment
     */
    public function experiment($experiment)
    {
        $this->name = $experiment;
        self::$instance[$experiment] = $this;
        return $this;
    }

    /**
     * @return string
     *
     * Sets the tracking target for the experiment, and returns one of the conditional elements for display
     */
    public function track($goal)
    {
        $this->goal = $goal;

        ob_end_clean();

        $conditions = [];
        foreach ($this->conditions as $key => $condition) {
            if (preg_match('/\[(\d+)\]/', $key, $matches)) {
                foreach (range(1, $matches[1]) as $index) {
                    $conditions[] = $key;
                }
            }
        }
        if (empty($conditions)) {
            $conditions = array_keys($this->conditions);
        }
        /// has the user fired this particular experiment yet?
        $fired = $this->hasExperiment($this->name);
        if (!empty($fired) && !empty($this->conditions[$fired])) {
            $this->fired = $fired;
        } else {
            shuffle($conditions);
            $this->fired = current($conditions);
        }

        return $this->conditions[$this->fired];
    }

    /**
     * @param  goal  $value
     *
     * Insert a simple goal tracker to know if user has reach a milestone
     */
    public static function goal($goal, $value = null)
    {
        if (!empty(self::$session)) {
            $goal = Goal::create(['instance_id' => self::$session->id, 'goal' => $goal, 'value' => $value]);

            self::$session->goals()->save($goal);

            return $goal;
        }
    }

    /**
     * @returns void
     *
     * Captures the HTML between AB condtions  and tracks them to their condition name.
     * One of these conditions will be randomized to some ratio for display and tracked
     */
    public function condition($condition)
    {
        $reference = $this;

        if (count($this->conditions) !== 0) {
            ob_end_clean();
        }

        $reference->saveCondition($condition, ''); /// so above count fires after first pass

        ob_start(function ($data) use ($condition, $reference) {
            $reference->saveCondition($condition, $data);
        });
    }


    public static function getSession()
    {
        return self::$session;
    }

    /**
     * @returns void
     *
     * A setter for the condition key=>value pairing.
     */
    public function saveCondition($condition, $data)
    {
        $this->conditions[$condition] = $data;
    }

    /**
     * @param  $experiment
     * @param  $condition
     *
     * Tracks at an instance level which event was selected for the session
     */
    public function instanceEvent()
    {
        self::$instance[$this->name] = $this;
    }

    /**
     * @return bool
     *
     * Determines if a user has a particular event already in this session
     */
    public function hasExperiment($experiment)
    {
        foreach(self::$events as $session_events) {
            if ($session_events->name == $experiment) {
                return $session_events->value;
            }
        }
        return false;
    }

    /**
     * Simple method for resetting the session variable for development purposes.
     */
    public function forceReset()
    {
        self::resetSession();
        $this->ensureUser(true);
    }

    public function toArray()
    {
        return [$this->name => $this->fired];
    }

    public function getEvents()
    {
        return self::$instance;
    }

    public static function resetSession()
    {
        self::$session = false;
    }
}
