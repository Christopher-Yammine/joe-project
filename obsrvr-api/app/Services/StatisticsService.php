<?php

namespace App\Services;

use App\Models\EtlDataHourly;
use App\Models\Stream;

use Illuminate\Support\Facades\DB;

class StatisticsService
{

    public function getTotalVisitorsCard(array $streamIds)
{
    $startOfToday = now()->startOfDay();
    $endOfToday = now()->endOfDay();
    $startOfYesterday = now()->subDay()->startOfDay();
    $endOfYesterday = now()->subDay()->endOfDay();


    $todayData = EtlDataHourly::whereIn('stream_id', $streamIds)
    ->whereBetween('date', [$startOfToday, $endOfToday])
    ->select(
        DB::raw('DATE(date) as day'),
        DB::raw('HOUR(date) as hour'),
        DB::raw('SUM(value) as total')
    )
    ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
    ->get();

    $yesterdayData = EtlDataHourly::whereIn('stream_id', $streamIds)
    ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
    ->select(
        DB::raw('DATE(date) as day'),
        DB::raw('HOUR(date) as hour'),
        DB::raw('SUM(value) as total')
    )
    ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
    ->get();

    $todaySeriesData = array_fill(0, 24, 0);
    $yesterdaySeriesData = array_fill(0, 24, 0);
    $totalVisitorsToday = 0;
    $totalVisitorsYesterday = 0;

    $latestHourWithData = 0;
    foreach ($todayData as $entry) {
        if ($entry->hour > $latestHourWithData) {
            $latestHourWithData = $entry->hour;
        }
    }

    foreach ($todayData as $entry) {
        if ($entry->hour <= $latestHourWithData) {
            $todaySeriesData[$entry->hour] = $entry->total;
            $totalVisitorsToday += $entry->total;
        }
    }

    foreach ($yesterdayData as $entry) {
        if ($entry->hour <= $latestHourWithData) {
            $yesterdaySeriesData[$entry->hour] = $entry->total;
            $totalVisitorsYesterday += $entry->total;
        }
    }
    // foreach ($todayData as $entry) {
    //     $todaySeriesData[$entry->hour] = $entry->total;
    //     $totalVisitorsToday += $entry->total;
    // }

    // foreach ($yesterdayData as $entry) {
    //     $yesterdaySeriesData[$entry->hour] = $entry->total;
    //     $totalVisitorsYesterday += $entry->total;
    // }

    $todayCumulativeSeriesData = $this->calculateCumulativeSeries($todaySeriesData);

    $percentChange = $this->calculatePercentChange($totalVisitorsToday, $totalVisitorsYesterday);
    $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

    return [
        'number' => number_format($totalVisitorsToday),
        'percent' => $percentFormatted,
        'seriesData' => $todaySeriesData,
        'cumulativeSeriesData' => $todayCumulativeSeriesData,
    ];
}

    public function getTotalUniqueVisitorsCard(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfToday, $endOfToday])
        ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->where('metrics.name', 'Unique')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw('SUM(value) as total')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();

        $yesterdayData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
        ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->where('metrics.name', 'Unique')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw('SUM(value) as total')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();


        $todaySeriesData = array_fill(0, 24, 0);
        $yesterdaySeriesData = array_fill(0, 24, 0);
        $totalVisitorsToday = 0;
        $totalVisitorsYesterday = 0;

        $latestHourWithData = 0;
        foreach ($todayData as $entry) {
            if ($entry->hour > $latestHourWithData) {
                $latestHourWithData = $entry->hour;
            }
        }

        foreach ($todayData as $entry) {
            if ($entry->day == $startOfToday->toDateString() && $entry->hour <= $latestHourWithData) {
                $todaySeriesData[$entry->hour] = $entry->total;
                $totalVisitorsToday += $entry->total;
            }
        }

        foreach ($yesterdayData as $entry) {
            if ($entry->day == $startOfYesterday->toDateString() && $entry->hour <= $latestHourWithData) {
                $yesterdaySeriesData[$entry->hour] = $entry->total;
                $totalVisitorsYesterday += $entry->total;
            }
        }

        // foreach ($todayData as $entry) {
        //     if ($entry->day == $startOfToday->toDateString()) {
        //         $todaySeriesData[$entry->hour] = $entry->total;
        //         $totalVisitorsToday += $entry->total;
        //     }
        // }

        // foreach ($yesterdayData as $entry) {
        //     if ($entry->day == $startOfYesterday->toDateString()) {
        //         $yesterdaySeriesData[$entry->hour] = $entry->total;
        //         $totalVisitorsYesterday += $entry->total;
        //     }
        // }

        $todayCumulativeSeriesData = $this->calculateCumulativeSeries($todaySeriesData);

        $percentChange = $this->calculatePercentChange($totalVisitorsToday, $totalVisitorsYesterday);
        $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

        return [
            'number' => number_format($totalVisitorsToday),
            'percent' => $percentFormatted,
            'seriesData' => $todaySeriesData,
            'cumulativeSeriesData' => $todayCumulativeSeriesData,
        ];
    }

    public function getTotalOccupancyCard(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayOccupancyData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfToday, $endOfToday])
        ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->where('metrics.name', 'Occupancy')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw('SUM(value) as total')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();

        $yesterdayOccupancyData = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
            ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
            ->where('metrics.name', 'Occupancy')
            ->select(
                DB::raw('DATE(date) as day'),
                DB::raw('HOUR(date) as hour'),
                DB::raw('SUM(value) as total')
            )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();


        $todaySeriesData = array_fill(0, 24, 0);
        $yesterdaySeriesData = array_fill(0, 24, 0);
        $totalOccupancyToday = 0;
        $totalOccupancyYesterday = 0;

        $latestHourWithData = 0;
        foreach ($todayOccupancyData as $entry) {
            if ($entry->hour > $latestHourWithData) {
                $latestHourWithData = $entry->hour;
            }
        }

        foreach ($todayOccupancyData as $entry) {
            if ($entry->hour <= $latestHourWithData) {
                $todaySeriesData[$entry->hour] = $entry->total;
                $totalOccupancyToday += $entry->total;
            }
        }

        foreach ($yesterdayOccupancyData as $entry) {
            if ($entry->hour <= $latestHourWithData) {
                $yesterdaySeriesData[$entry->hour] = $entry->total;
                $totalOccupancyYesterday += $entry->total;
            }
        }

        // foreach ($todayOccupancyData as $entry) {
        //     if ($entry->day == $startOfToday->toDateString()) {
        //         $todaySeriesData[$entry->hour] = $entry->total;
        //         $totalOccupancyToday += $entry->total;
        //     }
        // }

        // foreach ($yesterdayOccupancyData as $entry) {
        //     if ($entry->day == $startOfYesterday->toDateString()) {
        //         $yesterdaySeriesData[$entry->hour] = $entry->total;
        //         $totalOccupancyYesterday += $entry->total;
        //     }
        // }

        $percentChange = $this->calculatePercentChange($totalOccupancyToday, $totalOccupancyYesterday);
        $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

        return [
            'number' => number_format($totalOccupancyToday),
            'percent' => $percentFormatted,
            'seriesData' => $todaySeriesData,
        ];
    }

    public function getAgeGenderSentimentBarChartData(array $streamIds) {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayData = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfToday, $endOfToday])
            ->join('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
            ->join('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->select(
                'genders.gender',
                'sentiments.sentiment',
                'age_groups.group_name',
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy('genders.gender', 'sentiments.sentiment', 'age_groups.group_name')
            ->get();

        $yesterdayData = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
            ->join('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
            ->join('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->select(
                'genders.gender',
                'sentiments.sentiment',
                'age_groups.group_name',
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy('genders.gender', 'sentiments.sentiment', 'age_groups.group_name')
            ->get();

            $ageBarChartSeries = [];
            $ageSentimentBarChartSeries = [];
            $maleMax = 0;
            $femaleMax = 0;
            $happyMax = 0;
            $sadMax = 0;

            foreach ($todayData as $entry) {
                if ($entry->gender === 'Male') {
                    $totalValue = -abs($entry->total);
                    $ageBarChartSeries['Males'][$entry->group_name] = $totalValue;
                    $maleMax = max($maleMax, abs($entry->total));
                } elseif ($entry->gender === 'Female') {
                    $totalValue = abs($entry->total);
                    $ageBarChartSeries['Females'][$entry->group_name] = $totalValue;
                    $femaleMax = max($femaleMax, $entry->total);
                }

                if ($entry->sentiment === 'Happy') {
                    $ageSentimentBarChartSeries['Happy Visitors'][$entry->group_name] = $entry->total;
                    $happyMax = max($happyMax, $entry->total);
                } elseif ($entry->sentiment === 'Sad' || $entry->sentiment === 'Neutral') {
                    if (!isset($ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name])) {
                        $ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name] = 0;
                    }
                    $ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name] -= abs($entry->total);
                    $sadMax = max($sadMax, abs($entry->total));
                }
            }

        $maleMaxWithIncrease = -abs(round($maleMax * 1.1));
        $femaleMaxWithIncrease = round($femaleMax * 1.1);
        $happyMaxWithIncrease = round($happyMax * 1.1);
        $sadMaxWithDecrease = -abs(round($sadMax * 1.1));

        $ageBarChartSeriesFormatted = [];
        foreach ($ageBarChartSeries as $gender => $data) {
            $total = array_sum(array_values($data));
            $ageBarChartSeriesFormatted[] = [
                'name' => "{$gender} [total = " . abs($total) . "]",
                'name_ar' => "{$gender} [المجموع = " . abs($total) . "]",
                'data' => array_reverse(array_values($data)),
                'maxWithIncrease' => $gender === 'Males' ? $maleMaxWithIncrease : $femaleMaxWithIncrease
            ];
        }

        $ageSentimentBarChartSeriesFormatted = [];
        foreach ($ageSentimentBarChartSeries as $sentiment => $data) {
            $total = array_sum(array_values($data));
            $maxWithIncrease = $sentiment === 'Happy Visitors' ? $happyMaxWithIncrease : $sadMaxWithDecrease;
            $ageSentimentBarChartSeriesFormatted[] = [
                'name' => "{$sentiment} [total = " . abs($total) . "]",
                'name_ar' => "{$sentiment} [المجموع = " . abs($total) . "]",
                'data' => array_reverse(array_values($data)),
                'maxWithIncrease' => $maxWithIncrease
            ];
        }
        return [
            'ageBarChartSeries' => $ageBarChartSeriesFormatted,
            'ageSentimentBarChartSeries' => $ageSentimentBarChartSeriesFormatted,
        ];
    }

    public function getVisitorsData(array $streamIds) {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayResults = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $firstReturnTitle = 'avgFootfall';
        $fourthReturnTitle = 'totalFootfall';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, false, null, $firstReturnTitle, $fourthReturnTitle);

        return [
            'visitorsChartSeries1Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries1Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getUniqueVisitorsData(array $streamIds) {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayResults = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('metrics', 'etl.metric_id', '=', 'metrics.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('metrics.name', 'Unique')
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $firstReturnTitle = 'avgUniqueVisitors';
        $fourthReturnTitle = 'totalUniqueVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, true, false, null, $firstReturnTitle, $fourthReturnTitle);

        return [
            'visitorsChartSeries2Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries2Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getRepeatedVisitorsData(array $streamIds) {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayResults = DB::table('etl_data_hourly as etl')
        ->select(
            'streams.name',
            DB::raw('HOUR(etl.date) as hour'),
            DB::raw('SUM(etl.value) as total_value')
        )
        ->join('streams', 'etl.stream_id', '=', 'streams.id')
        ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
        ->whereIn('etl.stream_id', $streamIds)
        ->where('person_types.name', 'Returning')
        ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
        ->groupBy('streams.id', 'hour', 'streams.name')
        ->orderBy('hour')
        ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $personType = 'returning';
        $firstReturnTitle = 'avgRepeatedVisitors';
        $fourthReturnTitle = 'totalRepeatedVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, false, $personType, $firstReturnTitle, $fourthReturnTitle);

        return [
            'visitorsChartSeries3Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries3Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getOccupancyVisitorsData(array $streamIds) {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayResults = DB::table('etl_data_hourly as etl')
        ->select(
            'streams.name',
            DB::raw('HOUR(etl.date) as hour'),
            DB::raw('SUM(etl.value) as total_value')
        )
        ->join('streams', 'etl.stream_id', '=', 'streams.id')
        ->join('metrics', 'etl.metric_id', '=', 'metrics.id')
        ->whereIn('etl.stream_id', $streamIds)
        ->where('metrics.name', 'Occupancy')
        ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
        ->groupBy('streams.id', 'hour', 'streams.name')
        ->orderBy('hour')
        ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $firstReturnTitle = 'avgOccupancyVisitors';
        $fourthReturnTitle = 'totalOccupancy';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, true, null, $firstReturnTitle, $fourthReturnTitle);

        return [
            'visitorsChartSeries4Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries4Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getTotalStaffDaily(array $streamIds) {
        $today = now()->format('Y-m-d');

        $todayResults = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('person_types.name', 'staff')
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $visitorsChartSeries = array_values($visitorsChartSeries);

        return [
            'staffChartSeries' => $visitorsChartSeries
        ];
    }

    public function getTotalStaffDailyHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null, $isHistorical = false) {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";
        $dataPoints = $this->calculateDataPoints($fromDate, $toDate, $duration);


        $results = DB::table("$etlDataTable as etl")
            ->select(
                'streams.name',
                DB::raw("$groupByFormat as hour"),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('person_types.name', 'staff')
            ->whereBetween('etl.date', [$startDate, $endDate])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        $visitorsChartSeries = [];

        foreach ($results as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => array_fill(0, $dataPoints, 0),  // Initialize the data array with appropriate points
                ];
            }


            if (is_numeric($row->hour)) {
                // Hourly data - store in hourly slots (0-23)
                if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                    $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
                }
                $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
            } else {
                // Date-based data (assuming the hour is actually a date in this case)
                if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                    $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
                }
                $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
            }
        }


        $visitorsChartSeries = array_values($visitorsChartSeries);

        return [
            'staffChartSeries' => $visitorsChartSeries
        ];
    }

    public function getNewReturningHistoricalVisitors (array $streamIds, $fromDate, $toDate, $duration) {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);

        $groupByFormat = $this->getGroupByFormat($duration);

        $newVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('metrics', $etlDataTable . '.metric_id', '=', 'metrics.id')
            ->where('metrics.name', '=', 'Unique')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $returningVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '=', 'Returning')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalNewVisitors = $newVisitors->sum('total');
        $totalReturningVisitors = $returningVisitors->sum('total');

        $daysRange = (new \DateTime($toDate))->diff(new \DateTime($fromDate))->days;

        $previousToDate = (new \DateTime($fromDate))->modify('-1 day')->format('Y-m-d');
        $previousFromDate = (new \DateTime($previousToDate))->modify("-{$daysRange} days")->format('Y-m-d');

        $previousNewVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('metrics', $etlDataTable . '.metric_id', '=', 'metrics.id')
            ->where('metrics.name', '=', 'Unique')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $previousReturningVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '=', 'Returning')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalPreviousNewVisitors = $previousNewVisitors->sum('total');
        $totalPreviousReturningVisitors = $previousReturningVisitors->sum('total');

        $newVisitorsPercent = $this->calculatePercentChange($totalNewVisitors, $totalPreviousNewVisitors);
        $formattedNewVisitorsPercent = $newVisitorsPercent > 0 ? "+$newVisitorsPercent%" : "$newVisitorsPercent%";

        $newReturningPercent = $this->calculatePercentChange($totalReturningVisitors, $totalPreviousReturningVisitors);
        $formattedNewReturningPercent = $newReturningPercent > 0 ? "+$newReturningPercent%" : "$newReturningPercent%";

        $response = [
            'firstTitle' => 'New',
            'firstGeneralNumber' => strval($totalNewVisitors),
            'firstTrendNumber' => strval($formattedNewVisitorsPercent),
            'secondTitle' => 'Returning',
            'secondGeneralNumber' => strval($totalReturningVisitors),
            'secondTrendNumber' => strval($formattedNewReturningPercent),
            'xAxis' => $newVisitors->pluck('period')->toArray(),
            'commonChartSeries' => [
                [
                    'name' => 'New',
                    'name_ar' => 'جديد',
                    'data' => $newVisitors->pluck('total')->toArray(),
                ],
                [
                    'name' => 'Returning',
                    'name_ar' => 'عودة',
                    'data' => $returningVisitors->pluck('total')->toArray(),
                ]
            ]
        ];

        return $response;
    }

    public function getGenderHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration) {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $maleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->where('genders.gender', '=', 'Male')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $femaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->where('genders.gender', '=', 'Female')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalMaleVisitors = $maleVisitors->sum('total');
        $totalFemaleVisitors = $femaleVisitors->sum('total');

        $daysRange = (new \DateTime($toDate))->diff(new \DateTime($fromDate))->days;
        $previousToDate = (new \DateTime($fromDate))->modify('-1 day')->format('Y-m-d');
        $previousFromDate = (new \DateTime($previousToDate))->modify("-{$daysRange} days")->format('Y-m-d');

        $previousMaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->where('genders.gender', '=', 'Male')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $previousFemaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->where('genders.gender', '=', 'Female')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();


        $totalPreviousMaleVisitors = $previousMaleVisitors->sum('total');
        $totalPreviousFemaleVisitors = $previousFemaleVisitors->sum('total');

        $maleVisitorsPercent = $this->calculatePercentChange($totalMaleVisitors, $totalPreviousMaleVisitors);
        $formattedMaleVisitorsPercent = $maleVisitorsPercent > 0 ? "+$maleVisitorsPercent%" : "$maleVisitorsPercent%";

        $femaleVisitorsPercent = $this->calculatePercentChange($totalFemaleVisitors, $totalPreviousFemaleVisitors);
        $formattedFemaleVisitorsPercent = $femaleVisitorsPercent > 0 ? "+$femaleVisitorsPercent%" : "$femaleVisitorsPercent%";

        $response = [
            'firstTitle' => 'Male',
            'firstGeneralNumber' => strval($totalMaleVisitors),
            'firstTrendNumber' => strval($formattedMaleVisitorsPercent),
            'secondTitle' => 'Female',
            'secondGeneralNumber' => strval($totalFemaleVisitors),
            'secondTrendNumber' => strval($formattedFemaleVisitorsPercent),
            'xAxis' => $maleVisitors->pluck('period')->toArray(),
            'commonChartSeries' => [
                [
                    'name' => 'Male',
                    'name_ar' => 'ذكر',
                    'data' => $maleVisitors->pluck('total')->toArray(),
                ],
                [
                    'name' => 'Female',
                    'name_ar' => 'أنثى',
                    'data' => $femaleVisitors->pluck('total')->toArray(),
                ]
            ]
        ];

        return $response;
    }

    public function getSentimentsHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration) {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $happyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->where('sentiments.sentiment', '=', 'Happy')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $unhappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->whereIn('sentiments.sentiment', ['Sad', 'Neutral'])
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalHappyVisitors = $happyVisitors->sum('total');
        $totalUnhappyVisitors = $unhappyVisitors->sum('total');

        $daysRange = (new \DateTime($toDate))->diff(new \DateTime($fromDate))->days;
        $previousToDate = (new \DateTime($fromDate))->modify('-1 day')->format('Y-m-d');
        $previousFromDate = (new \DateTime($previousToDate))->modify("-{$daysRange} days")->format('Y-m-d');

        $previousHappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->where('sentiments.sentiment', '=', 'Happy')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $previousUnhappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->whereIn('sentiments.sentiment', ['Sad', 'Neutral'])
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalPreviousHappyVisitors = $previousHappyVisitors->sum('total');
        $totalPreviousUnhappyVisitors = $previousUnhappyVisitors->sum('total');

        $happyVisitorsPercent = $this->calculatePercentChange($totalHappyVisitors, $totalPreviousHappyVisitors);
        $formattedHappyVisitorsPercent = $happyVisitorsPercent > 0 ? "+$happyVisitorsPercent%" : "$happyVisitorsPercent%";

        $unhappyVisitorsPercent = $this->calculatePercentChange($totalUnhappyVisitors, $totalPreviousUnhappyVisitors);
        $formattedUnhappyVisitorsPercent = $unhappyVisitorsPercent > 0 ? "+$unhappyVisitorsPercent%" : "$unhappyVisitorsPercent%";

        $response = [
            'firstTitle' => 'Happy',
            'firstGeneralNumber' => strval($totalHappyVisitors),
            'firstTrendNumber' => strval($formattedHappyVisitorsPercent),
            'secondTitle' => 'Unhappy',
            'secondGeneralNumber' => strval($totalUnhappyVisitors),
            'secondTrendNumber' => strval($formattedUnhappyVisitorsPercent),
            'xAxis' => $happyVisitors->pluck('period')->toArray(),
            'commonChartSeries' => [
                [
                    'name' => 'Happy Visitors',
                    'name_ar' => 'سعيد',
                    'data' => $happyVisitors->pluck('total')->toArray(),
                ],
                [
                    'name' => 'Unhappy Visitors',
                    'name_ar' => 'غير سعيد',
                    'data' => $unhappyVisitors->pluck('total')->toArray(),
                ]
            ]
        ];

        return $response;
    }

    public function getMosqueSouqHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration) {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $mosqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->where('streams.name', 'like', '%Mosque%') // Filter by Mosque stream name
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $souqVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->where('streams.name', 'like', '%Souq%') // Filter by Souq stream name
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalMosqueVisitors = $mosqueVisitors->sum('total');
        $totalSouqVisitors = $souqVisitors->sum('total');

        $daysRange = (new \DateTime($toDate))->diff(new \DateTime($fromDate))->days;

        $previousToDate = (new \DateTime($fromDate))->modify('-1 day')->format('Y-m-d');
        $previousFromDate = (new \DateTime($previousToDate))->modify("-{$daysRange} days")->format('Y-m-d');

        $previousMosqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->where('streams.name', 'like', '%Mosque%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $previousSouqVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->where('streams.name', 'like', '%Souq%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($groupByFormat . ' as period'))
            ->groupBy(DB::raw($groupByFormat))
            ->orderBy(DB::raw($groupByFormat))
            ->get();

        $totalPreviousMosqueVisitors = $previousMosqueVisitors->sum('total');
        $totalPreviousSouqVisitors = $previousSouqVisitors->sum('total');

        $mosqueVisitorsPercent = $this->calculatePercentChange($totalMosqueVisitors, $totalPreviousMosqueVisitors);
        $formattedMosqueVisitorsPercent = $mosqueVisitorsPercent > 0 ? "+$mosqueVisitorsPercent%" : "$mosqueVisitorsPercent%";

        $souqVisitorsPercent = $this->calculatePercentChange($totalSouqVisitors, $totalPreviousSouqVisitors);
        $formattedSouqVisitorsPercent = $souqVisitorsPercent > 0 ? "+$souqVisitorsPercent%" : "$souqVisitorsPercent%";

        $response = [
            'firstTitle' => 'Mosque Visitors',
            'firstGeneralNumber' => strval($totalMosqueVisitors),
            'firstTrendNumber' => strval($formattedMosqueVisitorsPercent),
            'secondTitle' => 'Souq Visitors',
            'secondGeneralNumber' => strval($totalSouqVisitors),
            'secondTrendNumber' => strval($formattedSouqVisitorsPercent),
            'xAxis' => $mosqueVisitors->pluck('period')->toArray(),
            'commonChartSeries' => [
                [
                    'name' => 'Mosque Visitors',
                    'name_ar' => 'زوار المسجد',
                    'data' => $mosqueVisitors->pluck('total')->toArray(),
                ],
                [
                    'name' => 'Souq Visitors',
                    'name_ar' => 'زوار السوق',
                    'data' => $souqVisitors->pluck('total')->toArray(),
                ]
            ]
        ];

        return $response;
    }

    private function getEtlDataTableByDuration($duration) {
        return match ($duration) {
            'Daily' => 'etl_data_daily',
            'Weekly' => 'etl_data_weekly',
            'Monthly' => 'etl_data_monthly',
            'Quarterly' => 'etl_data_quarterly',
            'Yearly' => 'etl_data_yearly',
            default => 'etl_data_daily',
        };
    }

    private function getGroupByFormat($duration) {
        return match ($duration) {
            'Daily' => 'DATE_FORMAT(date, "%Y-%m-%d")',
            'Weekly' => 'DATE_FORMAT(date, "%Y-%u")',
            'Monthly' => 'DATE_FORMAT(date, "%Y-%m")',
            'Quarterly' => 'CONCAT(YEAR(date), "-", QUARTER(date))',
            'Yearly' => 'DATE_FORMAT(date, "%Y")',
            default => 'DATE_FORMAT(date, "%Y-%m-%d")',
        };
    }

    // private function calculateDataPoints($fromDate, $toDate, $duration) {
    //     $start = new \DateTime($fromDate);
    //     $end = new \DateTime($toDate);

    //     if ($duration === 'daily') {
    //         return $start->diff($end)->days + 1; // +1 to include the end date
    //     } elseif ($duration === 'weekly') {
    //         return ceil($start->diff($end)->days / 7); // Calculate weeks
    //     } elseif ($duration === 'monthly') {
    //         return ($end->format('Y') - $start->format('Y')) * 12 + ($end->format('n') - $start->format('n')) + 1;
    //     } elseif ($duration === 'quarterly') {
    //         return ceil((($end->format('Y') - $start->format('Y')) * 12 + ($end->format('n') - $start->format('n')) + 1) / 3); // Calculate quarters
    //     } elseif ($duration === 'yearly') {
    //         return $end->format('Y') - $start->format('Y') + 1; // Calculate years
    //     }

    //     return 24; // Default to 24 data points for hourly if no match
    // }

    // public function calculateDataPoints($fromDate, $toDate, $duration)
    // {
    //     return match ($duration) {
    //         'daily' => 1,  // One data point per day
    //         'hourly' => 24, // One data point for each hour
    //         'weekly' => 7,  // One data point for each day in a week
    //         default => 24,  // Default to hourly if no duration is provided
    //     };
    // }

    public function calculateDataPoints($fromDate, $toDate, $duration)
{
    return match ($duration) {
        'daily' => 1,    // 1 data point per day
        'hourly' => 24,  // 24 data points per day (1 per hour)
        'weekly' => 7,   // 7 data points per week (1 per day)
        'monthly' => $this->calculateMonths($fromDate, $toDate),  // Calculate months
        'quarterly' => $this->calculateQuarters($fromDate, $toDate),  // Calculate quarters
        'yearly' => $this->calculateYears($fromDate, $toDate),  // Calculate years
        default => 24,    // Default to 24 (hourly)
    };
}

// Additional helper functions to calculate months, quarters, and years
    private function calculateMonths($fromDate, $toDate)
    {
        $start = new \DateTime($fromDate);
        $end = new \DateTime($toDate);
        return ($end->format('Y') - $start->format('Y')) * 12 + ($end->format('n') - $start->format('n')) + 1;
    }

    private function calculateQuarters($fromDate, $toDate)
    {
        $start = new \DateTime($fromDate);
        $end = new \DateTime($toDate);
        return ceil((($end->format('Y') - $start->format('Y')) * 12 + ($end->format('n') - $start->format('n')) + 1) / 3);
    }

    private function calculateYears($fromDate, $toDate)
    {
        $start = new \DateTime($fromDate);
        $end = new \DateTime($toDate);
        return $end->format('Y') - $start->format('Y') + 1;
    }

    protected function calculateCumulativeSeries($seriesData)
    {
        $cumulativeSeries = [];
        $cumulativeTotal = 0;

        foreach ($seriesData as $value) {
            $cumulativeTotal += $value;
            $cumulativeSeries[] = $cumulativeTotal;
        }

        return $cumulativeSeries;
    }

    protected function calculatePercentChange($today, $yesterday)
    {
        return $yesterday == 0 ? ($today > 0 ? 100 : 0) : round((($today - $yesterday) / $yesterday) * 100, 2);
    }

    private function calculateMetricsComparison(
        $today,
        $yesterday,
        array $streamIds,
        $isUniqueMetric = false,
        $isOccupancyMetric = false,
        $personType = null,
        $firstReturnTitle,
        $fourthReturnTitle
    ) {
        $lastHourWithData = DB::table('etl_data_hourly')
            ->where('date', '>=', "$today 00:00:00")
            ->where('date', '<=', "$today 23:59:59")
            ->max(DB::raw('HOUR(date)'));

        $currentHour = $lastHourWithData !== null ? $lastHourWithData : date('G');

        $todayStart = "$today 00:00:00";
        $todayEnd = "$today " . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ":59:59";
        $yesterdayStart = "$yesterday 00:00:00";
        $yesterdayEnd = "$yesterday " . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ":59:59";


        $query = DB::table('etl_data_hourly as etl')
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->leftJoin('streams', 'etl.stream_id', '=', 'streams.id')
            ->selectRaw("
                SUM(CASE WHEN etl.date >= '$todayStart' AND etl.date <= '$todayEnd' THEN etl.value ELSE 0 END) AS today_total_value,
                COUNT(CASE WHEN etl.date >= '$todayStart' AND etl.date <= '$todayEnd' THEN 1 END) AS today_total_entries,
                SUM(CASE WHEN etl.date >= '$yesterdayStart' AND etl.date <= '$yesterdayEnd' THEN etl.value ELSE 0 END) AS yesterday_total_value,
                COUNT(CASE WHEN etl.date >= '$yesterdayStart' AND etl.date <= '$yesterdayEnd' THEN 1 END) AS yesterday_total_entries,
                SUM(CASE WHEN person_types.name = 'New' AND etl.date >= '$todayStart' AND etl.date <= '$todayEnd' THEN etl.value ELSE 0 END) AS today_new_visitors,
                SUM(CASE WHEN person_types.name = 'New' AND etl.date >= '$yesterdayStart' AND etl.date <= '$yesterdayEnd' THEN etl.value ELSE 0 END) AS yesterday_new_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$todayStart' AND etl.date <= '$todayEnd' THEN etl.value ELSE 0 END) AS today_souq_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$yesterdayStart' AND etl.date <= '$yesterdayEnd' THEN etl.value ELSE 0 END) AS yesterday_souq_visitors
            ");


        if ($isUniqueMetric) {
            $results = $query->leftJoin('metrics', 'etl.metric_id', '=', 'metrics.id')
            ->where('metrics.name', 'Unique')
            ->whereIn('etl.stream_id', $streamIds)
            ->first();
        } else if ($isOccupancyMetric){
            $results = $query->leftJoin('metrics', 'etl.metric_id', '=', 'metrics.id')
            ->where('metrics.name', 'Occupancy')
            ->whereIn('etl.stream_id', $streamIds)
            ->first();
        } else {
            $results = $query->whereIn('etl.stream_id', $streamIds)
            ->first();
        }

        if ($personType) {
            $results->today_new_visitors = DB::table('etl_data_hourly as etl')
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', $personType)
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
            ->whereIn('etl.stream_id', $streamIds)
            ->sum('etl.value');

            $results->yesterday_new_visitors = DB::table('etl_data_hourly as etl')
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', $personType)
            ->whereBetween('etl.date', ["$yesterday 00:00:00", "$yesterday 23:59:59"])
            ->whereIn('etl.stream_id', $streamIds)
            ->sum('etl.value');
        }

        $todayAverageFootfall = $results->today_total_value / 24;
        $yesterdayAverageFootfall = $results->yesterday_total_value / 24;

        $footfallPercentageDifference = $yesterdayAverageFootfall > 0
        ? (($todayAverageFootfall - $yesterdayAverageFootfall) / $yesterdayAverageFootfall) * 100
        : 0;

        $souqVisitorsPercentageDifference = $results->yesterday_souq_visitors > 0
        ? (($results->today_souq_visitors - $results->yesterday_souq_visitors) / $results->yesterday_souq_visitors) * 100
        : 0;

        $totalEntriesPercentageDifference = $results->yesterday_total_value > 0
        ? (($results->today_total_value - $results->yesterday_total_value) / $results->yesterday_total_value) * 100
        : 0;

        $isSouqStreamPresent = Stream::whereIn('id', $streamIds)
            ->where('name', 'like', 'Souq%')
            ->exists();

        $metrics = [
        'averageFootfall' => [
            'title' => $firstReturnTitle,
            'stats' => round($todayAverageFootfall),
            'trend' => $footfallPercentageDifference < 0 ? 'negative' : 'positive',
            'trendNumber' => round(abs($footfallPercentageDifference), 2),
        ],
        'totalEntries' => [
            'title' => $fourthReturnTitle,
            'stats' => $results->today_total_value,
            'trend' => $totalEntriesPercentageDifference < 0 ? 'negative' : 'positive',
            'trendNumber' => round(abs($totalEntriesPercentageDifference), 2),
        ],
        ];

        if ($isSouqStreamPresent) {
            $metrics['souqVisitors'] = [
                'title' => 'visitorsToSouq',
                'stats' => $results->today_souq_visitors,
                'trend' => $souqVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($souqVisitorsPercentageDifference), 2),
            ];
        }

        return $metrics;
    }

    private function getArabicName($name) {
        $arabicNames = [
            'Souq' => 'سوق',
            'Mosque Entry 1' => 'دخول المسجد 1',
            'Mosque Entry 2' => 'دخول المسجد 2',
            'Mosque Entry 3' => 'دخول المسجد 3',
        ];

        return $arabicNames[$name] ?? $name;
    }


}
