<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    /**
     * Public function that returns the appointments given to the user
     * @return BelongsToMany
     */
    public function appointments () : BelongsToMany
    {
        return $this->belongsToMany(Appointment::class);
    }
}
