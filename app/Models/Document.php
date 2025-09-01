<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTransitionalStates;

class Document extends Model
{
    use HasTransitionalStates;
}
