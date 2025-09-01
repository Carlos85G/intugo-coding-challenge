<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Patient;
use App\Models\Location;

class Appointment extends Model
{
    public function patient() : HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function location() : HasOne
    {
        return $this->hasOne(Location::class);
    }
}
