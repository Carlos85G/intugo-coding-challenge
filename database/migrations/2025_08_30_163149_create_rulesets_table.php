<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rulesets', function (Blueprint $table) {
            $table->id();
            $table->json('data')->notNull();
            $table->timestamps();

            /* Create index from JSON column */
            $table->string('action')
                    ->storedAs('JSON_UNQUOTE(data->>"$.action")')
                    ->unique();

            /* Create index from JSON column */
            $table->json('rules')
                    ->storedAs('JSON_UNQUOTE(data->>"$.rules")');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rulesets');
    }
};
