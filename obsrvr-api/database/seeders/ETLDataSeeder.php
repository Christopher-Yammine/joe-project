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

// class ETLDataSeeder extends Seeder
// {
//     public function run()
//     {
//         $footfalls = PersonType::all();
//         $metrics = Metric::all();
//         $demographics = Demographic::all();
//         $streams = Stream::all();
//         // $startDate = Carbon::yesterday()->setTime(0, 0, 0);
//         // $endDateHourly = $startDate->copy()->addHours(36);
//         // $endDateDaily = $startDate->copy()->addDays(30);
//         // $endDateWeekly = $startDate->copy()->addWeeks(12);
//         // $endDateMonthly = $startDate->copy()->addMonths(6);
//         // $endDateQuarterly = $startDate->copy()->addQuarters(4);
//         // $endDateYearly = $startDate->copy()->addYears(2);

//         $startDateHourly = Carbon::yesterday()->setTime(0, 0, 0);
//         $endDateHourly = Carbon::now();

//         $startDateDaily = $startDateHourly;
//         $endDateDaily = Carbon::now()->endOfDay();

//         $startDateWeekly = Carbon::now()->startOfWeek()->subWeeks(12);
//         $endDateWeekly = Carbon::now()->endOfWeek();

//         $startDateMonthly = Carbon::now()->startOfMonth()->subMonths(6);
//         $endDateMonthly = Carbon::now()->endOfMonth();

//         $startDateQuarterly = Carbon::now()->startOfQuarter()->subQuarters(4);
//         $endDateQuarterly = Carbon::now()->endOfQuarter();

//         $startDateYearly = Carbon::now()->startOfYear()->subYears(2);
//         $endDateYearly = Carbon::now()->endOfYear();

//         $this->seedHourlyData($footfalls, $demographics, $metrics, $startDateHourly, $endDateHourly, $streams);
//         $this->seedDailyData($footfalls, $demographics, $metrics, $startDateDaily, $endDateDaily, $streams);
//         $this->seedWeeklyData($footfalls, $demographics, $metrics, $startDateWeekly, $endDateWeekly, $streams);
//         $this->seedMonthlyData($footfalls, $demographics, $metrics, $startDateMonthly, $endDateMonthly, $streams);
//         $this->seedQuarterlyData($footfalls, $demographics, $metrics, $startDateQuarterly, $endDateQuarterly, $streams);
//         $this->seedYearlyData($footfalls, $demographics, $metrics, $startDateYearly, $endDateYearly, $streams);
//     }

//     protected function seedHourlyData($footfalls, $demographics, $metrics, $startTime, $endTime, $streams)
//     {
//         $demographics = $demographics->take(10);
//         while ($startTime < $endTime) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                         foreach ($metrics as $metric) {
//                             ETLDataHourly::create([
//                                 'stream_id' => $stream->id,
//                                 'person_type_id' => $footfall->id,
//                                 'demographics_id' => $demographic->id,
//                                 'metric_id' => $metric->id,
//                                 'date' => $startTime->format('Y-m-d H:i:s'),
//                                 'value' => rand(0, 10),
//                             ]);
//                         }
//                     }
//                 }
//             }
//             $startTime->addHour();
//         }
//     }

//     protected function seedDailyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
//     {
//         while ($startDate < $endDate) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                     foreach ($metrics as $metric) {
//                         ETLDataDaily::create([
//                             'stream_id' => $stream->id,
//                             'person_type_id' => $footfall->id,
//                             'demographics_id' => $demographic->id,
//                             'metric_id' => $metric->id,
//                             'date' => $startDate->format('Y-m-d H:i:s'),
//                             'value' => rand(0, 10),
//                         ]);
//                     }
//                 }
//                 }
//             }
//             $startDate->addDay();
//         }
//     }

//     protected function seedWeeklyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
//     {
//         while ($startDate < $endDate) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                         foreach ($metrics as $metric) {
//                             ETLDataWeekly::create([
//                                 'stream_id'=> $stream->id,
//                                 'person_type_id' => $footfall->id,
//                                 'demographics_id' => $demographic->id,
//                                 'metric_id' => $metric->id,
//                                 'date' => $startDate->format('Y-m-d H:i:s'),
//                                 'value' => rand(0, 10),
//                             ]);
//                         }
//                     }
//                 }
//             }
//             $startDate->addWeek();
//         }
//     }

//     protected function seedMonthlyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
//     {
//         while ($startDate < $endDate) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                     foreach ($metrics as $metric) {
//                         ETLDataMonthly::create([
//                             'stream_id'=> $stream->id,
//                             'person_type_id' => $footfall->id,
//                             'demographics_id' => $demographic->id,
//                             'metric_id' => $metric->id,
//                             'date' => $startDate->format('Y-m-d H:i:s'),
//                             'value' => rand(0, 10),
//                         ]);
//                     }
//                     }
//                 }
//             }
//             $startDate->addMonth();
//         }
//     }

//     protected function seedQuarterlyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
//     {
//         while ($startDate < $endDate) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                         foreach ($metrics as $metric) {
//                             ETLDataQuarterly::create([
//                                 'stream_id'=> $stream->id,
//                                 'person_type_id' => $footfall->id,
//                                 'demographics_id' => $demographic->id,
//                                 'metric_id' => $metric->id,
//                                 'date' => $startDate->format('Y-m-d H:i:s'),
//                                 'value' => rand(0, 10),
//                             ]);
//                         }
//                     }
//                 }
//             }
//             $startDate->addQuarter();
//         }
//     }

//     protected function seedYearlyData($footfalls, $demographics, $metrics, $startDate, $endDate, $streams)
//     {
//         while ($startDate < $endDate) {
//             foreach ($footfalls as $footfall) {
//                 foreach ($demographics as $demographic) {
//                     foreach ($streams as $stream) {
//                         foreach ($metrics as $metric) {
//                             ETLDataYearly::create([
//                                 'stream_id'=> $stream->id,
//                                 'person_type_id' => $footfall->id,
//                                 'demographics_id' => $demographic->id,
//                                 'metric_id' => $metric->id,
//                                 'date' => $startDate->format('Y-m-d H:i:s'),
//                                 'value' => rand(0, 10),
//                             ]);
//                         }
//                     }
//                 }
//             }
//             $startDate->addYear();
//         }
//     }
// }

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

            // Advance the date
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

        // Insert any remaining data
        if (!empty($data)) {
            $model::insert($data);
        }
    }
}
