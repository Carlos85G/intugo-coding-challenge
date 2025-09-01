<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    /**
     * Public function that returns the appointments given to the user
     * @return HasMany
     */
    public function appointments () : HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
