<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Helpers\ModelJsonFilter;
use App\Http\Middleware\EnsureActionAllowed; 
use App\Models\Appointment;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/check-sql', function (Request $request) {
    $result = true;
    $sql = "";
    $bindings = [];

    try {
        $query = Appointment::query();

        ModelJsonFilter::makeBuilderFromJson(
            $request->input("filters"),
            $query
        );

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        DB::select($sql, $bindings);
    } catch (QueryException $qe) {
        $result = false;
    }

    return response()->json([
        "result" => true,
        "bindings" => $bindings,
        "sql" => $sql
    ], 200);
});

Route::middleware(['auth:api', EnsureActionAllowed::class.':submit_form'])->group(function() {
    Route::post('/submit-form', function (Request $request) {
        return response()->json(
            ["message" => "Form submitted by {$request->user()->name}"],
            200
        );
    });
});