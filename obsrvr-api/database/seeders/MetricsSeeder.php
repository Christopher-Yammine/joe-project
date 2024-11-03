<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Metric;
use App\Models\ETLDataDaily;

class MetricsSeeder extends Seeder
{
    public function run()
    {
        // // Delete related records in ETLDataDaily first
        // ETLDataDaily::query()->delete(); // Ensure that you delete dependent records

        // // Now delete all records from the metrics table
        Metric::query()->delete();

        $metrics = [
            'Current',
            'Unique',
            'Occupancy',
        ];

        foreach ($metrics as $metric) {
            Metric::firstOrCreate(['name' => $metric]);
        }
    }
}
