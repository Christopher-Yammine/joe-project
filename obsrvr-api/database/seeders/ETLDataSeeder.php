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
        $footfalls = PersonType::all();
        $metrics = Metric::all();
        $demographics = Demographic::all();
        $streams = Stream::all();
        $startDate = Carbon::yesterday()->setTime(0, 0, 0);
        $endDateHourly = $startDate->copy()->addHours(48);
        $endDateDaily = $startDate->copy()->addDays(30);
        $endDateWeekly = $startDate->copy()->addWeeks(12);
        $endDateMonthly = $startDate->copy()->addMonths(6);
        $endDateQuarterly = $startDate->copy()->addQuarters(4);
        $endDateYearly = $startDate->copy()->addYears(2);

        $this->seedHourlyData($footfalls, $demographics, $metrics, $startDate, $endDateHourly, $streams);
        $this->seedDailyData($footfalls, $demographics, $metrics, $startDate, $endDateDaily, $streams);
        $this->seedWeeklyData($footfalls, $demographics, $metrics, $startDate, $endDateWeekly, $streams);
        $this->seedMonthlyData($footfalls, $demographics, $metrics, $startDate, $endDateMonthly, $streams);
        $this->seedQuarterlyData($footfalls, $demographics, $metrics, $startDate, $endDateQuarterly, $streams);
        $this->seedYearlyData($footfalls, $demographics, $metrics, $startDate, $endDateYearly, $streams);
    }

    protected function seedHourlyData($footfalls, $demographics, $metrics, $startTime, $endTime, $streams)
    {
        while ($startTime < $endTime) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($streams as $stream) {


                        foreach ($metrics as $metric) {
                            ETLDataHourly::create([
                                'stream_id' => $stream->id,
                                'person_type_id' => $footfall->id,
                                'demographics_id' => $demographic->id,
                                'metric_id' => $metric->id,
                                'date' => $startTime->format('Y-m-d H:i:s'),
                                'value' => rand(0, 10),
                            ]);
                        }
                    }
                }
            }
            $startTime->addHour();
        }
    }

    protected function seedDailyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
    {
        while ($startDate < $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($metrics as $metric) {
                        ETLDataDaily::create([
                            'person_type_id' => $footfall->id,
                            'demographics_id' => $demographic->id,
                            'metric_id' => $metric->id,
                            'date' => $startDate->format('Y-m-d H:i:s'),
                            'value' => rand(0, 10),
                        ]);
                    }
                }
            }
            $startDate->addDay();
        }
    }

    protected function seedWeeklyData($footfalls, $demographics, $metrics, $startDate, $endDate)
    {
        while ($startDate < $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($metrics as $metric) {
                        ETLDataWeekly::create([
                            'person_type_id' => $footfall->id,
                            'demographics_id' => $demographic->id,
                            'metric_id' => $metric->id,
                            'date' => $startDate->format('Y-m-d H:i:s'),
                            'value' => rand(0, 10),
                        ]);
                    }
                }
            }
            $startDate->addWeek();
        }
    }

    protected function seedMonthlyData($footfalls, $demographics, $metrics, $startDate, $endDate)
    {
        while ($startDate < $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($metrics as $metric) {
                        ETLDataMonthly::create([
                            'person_type_id' => $footfall->id,
                            'demographics_id' => $demographic->id,
                            'metric_id' => $metric->id,
                            'date' => $startDate->format('Y-m-d H:i:s'),
                            'value' => rand(0, 10),
                        ]);
                    }
                }
            }
            $startDate->addMonth();
        }
    }

    protected function seedQuarterlyData($footfalls, $demographics, $metrics, $startDate, $endDate)
    {
        while ($startDate < $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($metrics as $metric) {
                        ETLDataQuarterly::create([
                            'person_type_id' => $footfall->id,
                            'demographics_id' => $demographic->id,
                            'metric_id' => $metric->id,
                            'date' => $startDate->format('Y-m-d H:i:s'),
                            'value' => rand(0, 10),
                        ]);
                    }
                }
            }
            $startDate->addQuarter();
        }
    }

    protected function seedYearlyData($footfalls, $demographics, $metrics, $startDate, $endDate)
    {
        while ($startDate < $endDate) {
            foreach ($footfalls as $footfall) {
                foreach ($demographics as $demographic) {
                    foreach ($metrics as $metric) {
                        ETLDataYearly::create([
                            'person_type_id' => $footfall->id,
                            'demographics_id' => $demographic->id,
                            'metric_id' => $metric->id,
                            'date' => $startDate->format('Y-m-d H:i:s'),
                            'value' => rand(0, 10),
                        ]);
                    }
                }
            }
            $startDate->addYear();
        }
    }
}
