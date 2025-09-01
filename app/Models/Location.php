<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Appointment;

class Location extends Model
{
    /**
     * Public function that returns the appointments where this location is used
     * @return BelongsToMany
     */
    public function appointments () : BelongsToMany
    {
        return $this->belongsToMany(Appointment::class);
    }
}
