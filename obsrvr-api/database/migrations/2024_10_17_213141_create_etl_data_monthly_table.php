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
        Schema::create('etl_data_monthly', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id')->constrained('metrics')->onDelete('cascade');
            $table->foreignId('person_type_id')->constrained('person_types')->onDelete('cascade');
            $table->dateTime('date');
            $table->float('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etl_data_monthly');
    }
};
