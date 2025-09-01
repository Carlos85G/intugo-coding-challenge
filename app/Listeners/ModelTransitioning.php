<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Events\ModelTransitioning as ModelTransitioningEvent;

class ModelTransitioning
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ModelTransitioningEvent $event): void
    {
        Log::info("Model is transitioning to a new state", [
            "model" => get_class($event->model),
            "id" => $event->model->id,
            "current_state" => $event->currentState,
            "new_state" => $event->newState,
        ]);
    }
}
