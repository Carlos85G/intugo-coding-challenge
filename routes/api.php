<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureActionAllowed; 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::middleware(['auth:api', EnsureActionAllowed::class.':submit_form'])->group(function() {
    Route::post('/submit-form', function (Request $request) {
        return response()->json(
            ["message" => "Form submitted by {$request->user()->name}"],
            200
        );
    });
});