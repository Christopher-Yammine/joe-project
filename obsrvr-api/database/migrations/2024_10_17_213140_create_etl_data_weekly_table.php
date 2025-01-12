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
        Schema::create('etl_data_weekly', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->float('value');
            $table->string('person_type');
            $table->string('metric');
            $table->string('gender');
            $table->string('sentiment');
            $table->string('age_group');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etl_data_weekly');
    }
};
