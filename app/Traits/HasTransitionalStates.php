<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use App\Events\ModelTransitioning;
use App\Events\ModelTransitioned;

trait HasTransitionalStates
{
    public static $states = [
        'draft' => ['submitted'],
        'submitted' => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => ['draft'],
    ];

    public function transitionTo(string $newState) : bool
    {
        /**
         * Current model, to keep context
         * 
         * @var Model
         */
        $self = $this;

        $currentState = $this->state;

        if (!$this->isTransitionAllowed($currentState, $newState)) {
            return false;
        }

        event(new ModelTransitioning($this, $currentState, $newState));

        $this->state = $newState;
        $currentState = $newState;

        Event::defer(function () use ($self, $currentState) : void {
            $self->save();

            event(new ModelTransitioned($this, $currentState));
        }, ["eloquent.updated: ".get_class($this)]);

        return true;
    }

    protected function isTransitionAllowed($current, $new) : bool
    {
        return in_array($new, self::$states[$current]);
    }
}