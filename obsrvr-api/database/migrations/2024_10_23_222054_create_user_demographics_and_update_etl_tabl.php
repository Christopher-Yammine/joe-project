<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('etl_data_yearly', function (Blueprint $table) {
            $table->integer('stream_id')->default(1);
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
        });

        Schema::table('etl_data_weekly', function (Blueprint $table) {
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_daily', function (Blueprint $table) {
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_hourly', function (Blueprint $table) {
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_monthly', function (Blueprint $table) {
            //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });

        Schema::table('etl_data_quarterly', function (Blueprint $table) {
            // //$table->foreignId('stream_id')->constrained('streams')->onDelete('cascade');
            $table->integer('stream_id')->default(1);
        });
    }

    public function down()
    {

        Schema::table('etl_data_yearly', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_yearly', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });

        Schema::table('etl_data_weekly', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_weekly', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });

        Schema::table('etl_data_daily', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_daily', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });

        Schema::table('etl_data_hourly', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_hourly', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });

        Schema::table('etl_data_monthly', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_monthly', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });

        Schema::table('etl_data_quarterly', function (Blueprint $table) {
            if (Schema::hasColumn('etl_data_quarterly', 'stream_id')) {
                $table->dropColumn(['stream_id']);
            }
        });
    }
};
