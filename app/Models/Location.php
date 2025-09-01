<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Appointment;

class Location extends Model
{
    /**
     * Public function that returns the appointments where this location is used
     * @return HasMany
     */
    public function appointments () : HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
