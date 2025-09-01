<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Events\ModelTransitioned as ModelTransitionedEvent;


class ModelTransitioned
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
    public function handle(ModelTransitionedEvent $event): void
    {
        Log::info("Model transitioned to a new state", [
            "model" => get_class($event->model),
            "id" => $event->model->id,
            "new_state" => $event->newState,
        ]);
    }
}
