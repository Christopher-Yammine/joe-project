<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sentiments', function (Blueprint $table) {
            $table->id();
            $table->string('sentiment')->unique();
            $table->timestamps();
        });

        Schema::create('age_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name')->unique();
            $table->timestamps();
        });

        Schema::create('genders', function (Blueprint $table) {
            $table->id();
            $table->string('gender')->unique();
            $table->timestamps();
        });



        Schema::create('demographics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gender_id')->constrained('genders')->onDelete('cascade');
            $table->foreignId('age_group_id')->constrained('age_groups')->onDelete('cascade');
            $table->foreignId('sentiment_id')->constrained('sentiments')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('etl_data_yearly', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            $table->integer('stream_id')->default(1);
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
        });

        Schema::table('etl_data_weekly', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_daily', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_hourly', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_monthly', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_quarterly', function (Blueprint $table) {
            $table->foreignId('demographics_id')->nullable()->constrained('demographics')->onDelete('set null');
            // //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });
    }

    public function down()
    {
        Schema::table('etl_data_yearly', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::table('etl_data_weekly', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::table('etl_data_daily', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::table('etl_data_hourly', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::table('etl_data_monthly', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::table('etl_data_quarterly', function (Blueprint $table) {
            $table->dropForeign(['demographics_id']);
            $table->dropColumn(['demographics_id']);
        });

        Schema::dropIfExists('demographics');
        Schema::dropIfExists('person_types');
        Schema::dropIfExists('genders');
        Schema::dropIfExists('age_groups');
        Schema::dropIfExists('sentiments');
    }
};
