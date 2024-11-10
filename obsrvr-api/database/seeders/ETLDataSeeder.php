<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Footfall;
use App\Models\Metric;
use App\Models\Demographic;
use App\Models\ETLDataHourly;
use App\Models\ETLDataDaily;
use App\Models\ETLDataWeekly;
use App\Models\ETLDataMonthly;
use App\Models\ETLDataQuarterly;
use App\Models\ETLDataYearly;
use App\Models\PersonType;
use App\Models\Stream;
use Carbon\Carbon;

class ETLDataSeeder extends Seeder
{
    public function run()
    {
        $footfalls = PersonType::all()->pluck('id')->toArray();
        $metrics = Metric::all()->pluck('id')->toArray();
        $demographics = Demographic::all()->take(10)->pluck('id')->toArray();
        $streams = Stream::all()->pluck('id')->toArray();

        $startDateHourly = Carbon::yesterday()->setTime(0, 0, 0);
        $endDateHourly = Carbon::now();

        $startDateDaily = Carbon::now()->startOfDay()->subDays(30);
        $endDateDaily = Carbon::now()->endOfDay();

        $startDateWeekly = Carbon::now()->startOfWeek()->subWeeks(30);
        $endDateWeekly = Carbon::now()->endOfWeek();

        $startDateMonthly = Carbon::now()->startOfMonth()->subMonths(20);
        $endDateMonthly = Carbon::now()->endOfMonth();

        $startDateQuarterly = Carbon::now()->startOfQuarter()->subQuarters(16);
        $endDateQuarterly = Carbon::now()->endOfQuarter();

        $startDateYearly = Carbon::now()->startOfYear()->subYears(10);
        $endDateYearly = Carbon::now()->endOfYear();

        $this->seedData(ETLDataHourly::class, $footfalls, $demographics, $metrics, $startDateHourly, $endDateHourly, $streams, 'hour');
        $this->seedData(ETLDataDaily::class, $footfalls, $demographics, $metrics, $startDateDaily, $endDateDaily, $streams, 'day');
        $this->seedData(ETLDataWeekly::class, $footfalls, $demographics, $metrics, $startDateWeekly, $endDateWeekly, $streams, 'week');
        $this->seedData(ETLDataMonthly::class, $footfalls, $demographics, $metrics, $startDateMonthly, $endDateMonthly, $streams, 'month');
        $this->seedData(ETLDataQuarterly::class, $footfalls, $demographics, $metrics, $startDateQuarterly, $endDateQuarterly, $streams, 'quarter');
        $this->seedData(ETLDataYearly::class, $footfalls, $demographics, $metrics, $startDateYearly, $endDateYearly, $streams, 'year');
    }

    protected function seedData($model, $footfalls, $demographics, $metrics, $startDate, $endDate, $streams, $interval)
    {
        $data = [];
        $date = $startDate->copy();

        while ($date <= $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($streams as $stream) {
                        foreach ($metrics as $metric) {
                            $data[] = [
                                'stream_id' => $stream,
                                'person_type_id' => $footfall,
                                'demographics_id' => $demographic,
                                'metric_id' => $metric,
                                'date' => $date->format('Y-m-d H:i:s'),
                                'value' => rand(0, 10),
                            ];

                            if (count($data) >= 1000) {
                                $model::insert($data);
                                $data = [];
                            }
                        }
                    }
                }
            }

            switch ($interval) {
                case 'hour':
                    $date->addHour();
                    break;
                case 'day':
                    $date->addDay();
                    break;
                case 'week':
                    $date->addWeek();
                    break;
                case 'month':
                    $date->addMonth();
                    break;
                case 'quarter':
                    $date->addQuarter();
                    break;
                case 'year':
                    $date->addYear();
                    break;
            }
        }

        if (!empty($data)) {
            $model::insert($data);
        }
    }
}
