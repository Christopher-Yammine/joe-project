<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Metric;


class MetricsSeeder extends Seeder
{
    public function run()
    {
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
