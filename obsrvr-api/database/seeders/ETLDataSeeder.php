<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Footfall;
use App\Models\Metric;
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
        $streams = Stream::all()->pluck('id')->toArray();
        $genders = ['Male', 'Female'];
        $ageGroups = ['70+', '50-69', '35-49', '25-34', '19-24'];
        $metrics = ['Current', 'Unique', 'Occupancy'];
        $personTypes = ['New', 'Returning', 'Staff'];
        $sentiments = ['Happy', 'Neutral', 'Sad'];

        $startDateHourly = Carbon::now()->subDays(15)->setTime(0, 0, 0);
        $endDateHourly = Carbon::now()->endOfDay()->addDays(5);

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

        $this->seedData(ETLDataHourly::class, $personTypes, $metrics, $startDateHourly, $endDateHourly, $streams, 'hour', $genders, $ageGroups, $sentiments);
        $this->seedData(ETLDataDaily::class, $personTypes, $metrics, $startDateDaily, $endDateDaily, $streams, 'day', $genders, $ageGroups, $sentiments);
        $this->seedData(ETLDataWeekly::class, $personTypes, $metrics, $startDateWeekly, $endDateWeekly, $streams, 'week', $genders, $ageGroups, $sentiments);
        $this->seedData(ETLDataMonthly::class, $personTypes, $metrics, $startDateMonthly, $endDateMonthly, $streams, 'month', $genders, $ageGroups, $sentiments);
        $this->seedData(ETLDataQuarterly::class, $personTypes, $metrics, $startDateQuarterly, $endDateQuarterly, $streams, 'quarter', $genders, $ageGroups, $sentiments);
        $this->seedData(ETLDataYearly::class, $personTypes, $metrics, $startDateYearly, $endDateYearly, $streams, 'year', $genders, $ageGroups, $sentiments);
    }

    protected function seedData($model, $personTypes, $metrics, $startDate, $endDate, $streams, $interval, $genders, $ageGroups, $sentiments)
    {
        $data = [];
        $date = $startDate->copy();

        while ($date <= $endDate) {
            if ($interval === 'hour') {
                $startHour = 9;
                $endHour = ($date->isSaturday()) ? 14 : (rand(21, 22));

                if ($date->hour < $startHour || $date->hour > $endHour) {
                    $date->addHour();
                    continue;
                }
            }

            foreach ($personTypes as $personType) {
                foreach ($streams as $stream) {
                    foreach ($metrics as $metric) {
                        foreach ($genders as $gender) {
                            foreach ($ageGroups as $ageGroup) {
                                foreach($sentiments as $sentiment)
                                $baseValue = match ($interval) {
                                    'hour' => rand(1, 5),
                                    'day' => rand(10, 50),
                                    'week' => rand(100, 300),
                                    'month' => rand(500, 1500),
                                    'quarter' => rand(2000, 5000),
                                    'year' => rand(5000, 20000),
                                    default => rand(0, 10),
                                };

                                $variation = rand(-2, 2);
                                $finalValue = max(0, $baseValue + $variation);

                                $data[] = [
                                    'stream_id' => $stream,
                                    'person_type' => $personType,
                                    'metric' => $metric,
                                    'gender' => $gender,
                                    'age_group' => $ageGroup,
                                    'sentiment' => $sentiment,
                                    'date' => $date->format('Y-m-d H:i:s'),
                                    'value' => $finalValue,
                                ];

                                if (count($data) >= 1000) {
                                    $model::insert($data);
                                    $data = [];
                                }
                            }
                        }
                    }
                }
            }

            $this->incrementDate($date, $interval);
        }

        if (!empty($data)) {
            $model::insert($data);
        }
    }

    protected function incrementDate(&$date, $interval)
    {
        match ($interval) {
            'hour' => $date->addHour(),
            'day' => $date->addDay(),
            'week' => $date->addWeek(),
            'month' => $date->addMonth(),
            'quarter' => $date->addQuarter(),
            'year' => $date->addYear(),
        };
    }
}
