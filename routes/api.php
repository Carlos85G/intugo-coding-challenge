<?php

use Illuminate\Database\QueryException;
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
    $message = "Success";
    $data = [];

    try {
        $query = Appointment::query();

        ModelJsonFilter::makeBuilderFromJson(
            $request->input("filters"),
            $query
        );

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        DB::select($sql, $bindings);

        $data = [
            "sql" => $sql,
            "bindings" => $bindings,
        ];
    } catch (QueryException $qe) {
        $result = false;
        $message = $qe->getMessage();
    }

    return response()->json([
        "result" => $result,
        "message" => $message,
        "data" => $data,
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