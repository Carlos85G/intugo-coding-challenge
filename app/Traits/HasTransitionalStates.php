<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use App\Events\ModelTransitioning;
use App\Events\ModelTransitioned;

/**
 * Helper trait to add a state to a model
 * 
 * @author Carlos González
 */
trait HasTransitionalStates
{
    public static $states = [
        'draft' => ['submitted'],
        'submitted' => ['approved', 'rejected'],
        'approved' => [],
        'rejected' => ['draft'],
    ];

    /**
     * Public function to transition to a state
     * 
     * @param   string  $newState   The state to transition to.
     * @return  bool                The result of the action.
     */
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

    /**
     * Protected function to check for correct transition path.
     * 
     * @param   string  $newState   The state to transition to.
     * @return  bool                The result of the action.
     */
    protected function isTransitionAllowed($current, $new) : bool
    {
        return in_array($new, self::$states[$current]);
    }
}