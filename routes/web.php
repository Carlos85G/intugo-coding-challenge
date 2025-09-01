<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response("No front view, but it works!", 200);
});
