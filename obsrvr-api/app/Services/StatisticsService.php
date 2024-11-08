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

        // $newVisitorsPercentageDifference = $results->yesterday_new_visitors > 0
        // ? (($results->today_new_visitors - $results->yesterday_new_visitors) / $results->yesterday_new_visitors) * 100
        // : 0;

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

        // return [
        //     'averageFootfall' => [
        //         'title' => $firstReturnTitle,
        //         'stats' => round($todayAverageFootfall),
        //         'trend' => $footfallPercentageDifference < 0 ? 'negative' : 'positive',
        //         'trendNumber' => round(abs($footfallPercentageDifference), 2),
        //     ],
        //     // 'newVisitors' => [
        //     //     'title' => 'newVisitors',
        //     //     'stats' => $results->today_new_visitors,
        //     //     'trend' => $newVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
        //     //     'trendNumber' => round(abs($newVisitorsPercentageDifference), 2),
        //     // ],
        //     'souqVisitors' => [
        //         'title' => 'visitorsToSouq',
        //         'stats' => $results->today_souq_visitors,
        //         'trend' => $souqVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
        //         'trendNumber' => round(abs($souqVisitorsPercentageDifference), 2),
        //     ],
        //     'totalEntries' => [
        //         'title' =>  $fourthReturnTitle,
        //         'stats' => $results->today_total_value,
        //         'trend' => $totalEntriesPercentageDifference < 0 ? 'negative' : 'positive',
        //         'trendNumber' => round(abs($totalEntriesPercentageDifference), 2),
        //     ],
        // ];
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

    private function getArabicName($name) {
        $arabicNames = [
            'Souq' => 'سوق',
            'Mosque Entry 1' => 'دخول المسجد 1',
            'Mosque Entry 2' => 'دخول المسجد 2',
            'Mosque Entry 3' => 'دخول المسجد 3',
        ];

        return $arabicNames[$name] ?? $name;
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

        $response = [
            'firstTitle' => 'New',
            'firstGeneralNumber' => strval($totalNewVisitors),
            'secondTitle' => 'Returning',
            'secondGeneralNumber' => strval($totalReturningVisitors),
            'xAxis' => $newVisitors->pluck('period')->toArray(),
            'commonChartSeries1' => [
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

    private function formatDataWithPeriod($data) {
        return $data->map(function($item) {
            return [
                'period' => $item->period,
                'total' => $item->total    
            ];
        })->toArray();
    }

}
