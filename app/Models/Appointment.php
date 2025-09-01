<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Patient;
use App\Models\Location;

class Appointment extends Model
{
    public function patient() : BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function location() : BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
