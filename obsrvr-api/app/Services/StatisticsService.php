<?php

namespace App\Services;

use App\Models\DemographicView;
use App\Models\EtlDataHourly;
use App\Models\HourlyDemographicView;
use App\Models\PersonType;
use Carbon\Carbon;
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

    foreach ($todayData as $entry) {
        $todaySeriesData[$entry->hour] = $entry->total;
        $totalVisitorsToday += $entry->total;
    }

    foreach ($yesterdayData as $entry) {
        $yesterdaySeriesData[$entry->hour] = $entry->total;
        $totalVisitorsYesterday += $entry->total;
    }

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

        foreach ($todayData as $entry) {
            if ($entry->day == $startOfToday->toDateString()) {
                $todaySeriesData[$entry->hour] = $entry->total;
                $totalVisitorsToday += $entry->total;
            }
        }

        foreach ($yesterdayData as $entry) {
            if ($entry->day == $startOfYesterday->toDateString()) {
                $yesterdaySeriesData[$entry->hour] = $entry->total;
                $totalVisitorsYesterday += $entry->total;
            }
        }

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

        foreach ($todayOccupancyData as $entry) {
            if ($entry->day == $startOfToday->toDateString()) {
                $todaySeriesData[$entry->hour] = $entry->total;
                $totalOccupancyToday += $entry->total;
            }
        }

        foreach ($yesterdayOccupancyData as $entry) {
            if ($entry->day == $startOfYesterday->toDateString()) {
                $yesterdaySeriesData[$entry->hour] = $entry->total;
                $totalOccupancyYesterday += $entry->total;
            }
        }

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

    public function getAgeGenderBarChartData(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        $data = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfToday, $endOfToday])
            ->join('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
            ->join('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->select('genders.gender', 'age_groups.group_name', DB::raw('SUM(etl_data_hourly.value) as total'))
            ->groupBy('genders.gender', 'age_groups.group_name')
            ->get();


        $femaleData = [];
        $maleData = [];
        $totalMales = 0;
        $totalFemales = 0;

        foreach ($data as $entry) {
        if ($entry->gender === 'Male') {
                $maleData[$entry->group_name] = -$entry->total;
            $totalMales += abs($entry->total);
        } elseif ($entry->gender === 'Female') {
                $femaleData[$entry->group_name] = $entry->total;
                $totalFemales += $entry->total;
        }
        }

        $series = [];

        if (!empty($maleData)) {
            $maxMale = max($maleData);
            $maxMaleWithIncrease = $maxMale + ($maxMale * 0.10);

            $series[] = [
                'name' => 'Males [total = ' . number_format($totalMales) . ']',
                'name_ar' => 'الذكور [المجموع = ' . number_format($totalMales) . ']',
                'data' => array_reverse(array_values($maleData)),
                'maxMaleWithIncrease' => 'test'
            ];
        }


        if (!empty($femaleData)) {
            $maxFemale = max($femaleData);
            $maxFemaleWithIncrease = $maxFemale + ($maxFemale * 0.10);

            $series[] = [
                'name' => 'Females [total = ' . number_format($totalFemales) . ']',
                'name_ar' => 'الإناث [المجموع = ' . number_format($totalFemales) . ']',
                'data' => array_reverse(array_values($femaleData)),
                'maxFemaleWithIncrease' => number_format($maxFemaleWithIncrease)
            ];
        }

        return $series;
    }

    public function getAgeSentimentBarChartData(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        $data = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfToday, $endOfToday])
            ->join('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
            ->join('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->select('sentiments.sentiment', 'age_groups.group_name', DB::raw('SUM(etl_data_hourly.value) as total'))
            ->groupBy('sentiments.sentiment', 'age_groups.group_name')
            ->get();


        $happyData = [];
        $unhappyData = [];
        $totalHappy = 0;
        $totalUnhappy = 0;

        foreach ($data as $entry) {
            if ($entry->sentiment === 'Happy') {
                $happyData[$entry->group_name] = -$entry->total;
                $totalHappy += abs($entry->total);
            } elseif ($entry->sentiment === 'Unhappy') {
                $unhappyData[$entry->group_name] = $entry->total;
                $totalUnhappy += $entry->total;
            }
        }

        $series = [];

        if (!empty($happyData)) {
            $series[] = [
                'name' => 'Happy Visitors [total = ' . number_format($totalHappy) . ']',
                'name_ar' => 'زوار سعداء [المجموع = ' . number_format($totalHappy) . ']',
                'data' => array_reverse(array_values($happyData))
            ];
        }

        if (!empty($unhappyData)) {
            $series[] = [
                'name' => 'Unhappy Visitors [total = ' . number_format($totalUnhappy) . ']',
                'name_ar' => 'الزوار غير الراضين [المجموع = ' . number_format($totalUnhappy) . ']',
                'data' => array_reverse(array_values($unhappyData))
            ];
        }

        return $series;
    }

    public function getCombinedMetricsCardWithDemographics(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayData = EtlDataHourly::whereIn('stream_id', $streamIds) 
        ->whereBetween('date', [$startOfToday, $endOfToday])
        ->leftJoin('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->leftJoin('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
        ->leftJoin('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
        ->leftJoin('genders', 'demographics.gender_id', '=', 'genders.id')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw("SUM(CASE WHEN metrics.name = 'Unique' THEN value ELSE 0 END) as unique_visitors"),
            DB::raw("SUM(CASE WHEN metrics.name = 'Occupancy' THEN value ELSE 0 END) as occupancy"),
            DB::raw("SUM(value) as total_visitors"),
            'genders.gender',
            'age_groups.group_name',
            DB::raw('SUM(etl_data_hourly.value) as demographic_total')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'), 'genders.gender', 'age_groups.group_name')
        ->get();

        $yesterdayData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
        ->leftJoin('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->leftJoin('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
        ->leftJoin('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
        ->leftJoin('genders', 'demographics.gender_id', '=', 'genders.id')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw("SUM(CASE WHEN metrics.name = 'Unique' THEN value ELSE 0 END) as unique_visitors"),
            DB::raw("SUM(CASE WHEN metrics.name = 'Occupancy' THEN value ELSE 0 END) as occupancy"),
            DB::raw("SUM(value) as total_visitors"),
            'genders.gender',
            'age_groups.group_name',
            DB::raw('SUM(etl_data_hourly.value) as demographic_total')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'), 'genders.gender', 'age_groups.group_name')
        ->get();

        $todayData = [
            'unique_visitors' => array_fill(0, 24, 0),
            'occupancy' => array_fill(0, 24, 0),
            'total_visitors' => array_fill(0, 24, 0)
        ];
        $demographicsData = [
            'male' => [],
            'female' => []
        ];
        $totalsToday = ['unique_visitors' => 0, 'occupancy' => 0, 'total_visitors' => 0];
        $totalsYesterday = ['unique_visitors' => 0, 'occupancy' => 0, 'total_visitors' => 0];

        foreach ($data as $entry) {
            if ($entry->day == $startOfToday->toDateString()) {

                $todayData['unique_visitors'][$entry->hour] = $entry->unique_visitors;
                $todayData['occupancy'][$entry->hour] = $entry->occupancy;
                $todayData['total_visitors'][$entry->hour] = $entry->total_visitors;


                $totalsToday['unique_visitors'] += $entry->unique_visitors;
                $totalsToday['occupancy'] += $entry->occupancy;
                $totalsToday['total_visitors'] += $entry->total_visitors;

                if ($entry->gender === 'Male') {
                    $demographicsData['male'][$entry->group_name] = -$entry->demographic_total;
                } elseif ($entry->gender === 'Female') {
                    $demographicsData['female'][$entry->group_name] = $entry->demographic_total;
                }
            } elseif ($entry->day == $startOfYesterday->toDateString()) {
                $totalsYesterday['unique_visitors'] += $entry->unique_visitors;
                $totalsYesterday['occupancy'] += $entry->occupancy;
                $totalsYesterday['total_visitors'] += $entry->total_visitors;
            }
        }
        $percentChanges = [
            'unique_visitors' => $this->calculatePercentChange($totalsToday['unique_visitors'], $totalsYesterday['unique_visitors']),
            'occupancy' => $this->calculatePercentChange($totalsToday['occupancy'], $totalsYesterday['occupancy']),
            'total_visitors' => $this->calculatePercentChange($totalsToday['total_visitors'], $totalsYesterday['total_visitors'])
        ];

        $formattedResponse = [
            'unique_visitors' => [
                'number' => number_format($totalsToday['unique_visitors']),
                'percent' => $percentChanges['unique_visitors'] > 0 ? "+{$percentChanges['unique_visitors']}%" : "{$percentChanges['unique_visitors']}%",
                'seriesData' => $todayData['unique_visitors']
            ],
            'occupancy' => [
                'number' => number_format($totalsToday['occupancy']),
                'percent' => $percentChanges['occupancy'] > 0 ? "+{$percentChanges['occupancy']}%" : "{$percentChanges['occupancy']}%",
                'seriesData' => $todayData['occupancy']
            ],
            'total_visitors' => [
                'number' => number_format($totalsToday['total_visitors']),
                'percent' => $percentChanges['total_visitors'] > 0 ? "+{$percentChanges['total_visitors']}%" : "{$percentChanges['total_visitors']}%",
                'seriesData' => $todayData['total_visitors']
            ]
        ];

        $demographicSeries = [];
        if (!empty($demographicsData['male'])) {
            $totalMales = array_sum(array_map('abs', $demographicsData['male']));
            $demographicSeries[] = [
                'name' => 'Males [total = ' . number_format($totalMales) . ']',
                'name_ar' => 'الذكور [المجموع = ' . number_format($totalMales) . ']',
                'data' => array_reverse(array_values($demographicsData['male'])),
            ];
        }
        if (!empty($demographicsData['female'])) {
            $totalFemales = array_sum($demographicsData['female']);
            $demographicSeries[] = [
                'name' => 'Females [total = ' . number_format($totalFemales) . ']',
                'name_ar' => 'الإناث [المجموع = ' . number_format($totalFemales) . ']',
                'data' => array_reverse(array_values($demographicsData['female']))
            ];
        }


        $formattedResponse['demographics'] = $demographicSeries;

        return $formattedResponse;
    }

    public function getAgeGenderSentimentBarChartData(array $streamIds)
    {
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
                'name' => "{$gender} [total = " . number_format($total) . "]",
                'name_ar' => "{$gender} [المجموع = " . number_format($total) . "]",
                'data' => array_reverse(array_values($data)),
                'maxWithIncrease' => $gender === 'Males' ? $maleMaxWithIncrease : $femaleMaxWithIncrease
            ];
        }

        $ageSentimentBarChartSeriesFormatted = [];
        foreach ($ageSentimentBarChartSeries as $sentiment => $data) {
            $total = array_sum(array_values($data));
            $maxWithIncrease = $sentiment === 'Happy Visitors' ? $happyMaxWithIncrease : $sadMaxWithDecrease;
            $ageSentimentBarChartSeriesFormatted[] = [
                'name' => "{$sentiment} [total = " . number_format($total) . "]",
                'name_ar' => "{$sentiment} [المجموع = " . number_format($total) . "]",
                'data' => array_reverse(array_values($data)),
                'maxWithIncrease' => $maxWithIncrease
            ];
        }
        return [
            'ageBarChartSeries' => $ageBarChartSeriesFormatted,
            'ageSentimentBarChartSeries' => $ageSentimentBarChartSeriesFormatted,
        ];
    }

    public function getTotalUniqueVisitorsAndOccupancyCard(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();
        
        $todayData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfToday, $endOfToday])
        ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw('SUM(CASE WHEN metrics.name = "Unique" THEN value ELSE 0 END) as totalUniqueVisitors'),
            DB::raw('SUM(CASE WHEN metrics.name = "Occupancy" THEN value ELSE 0 END) as totalOccupancy')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();

        $yesterdayData = EtlDataHourly::whereIn('stream_id', $streamIds)
        ->whereBetween('date', [$startOfYesterday, $endOfYesterday])
        ->join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
        ->select(
            DB::raw('DATE(date) as day'),
            DB::raw('HOUR(date) as hour'),
            DB::raw('SUM(CASE WHEN metrics.name = "Unique" THEN value ELSE 0 END) as totalUniqueVisitors'),
            DB::raw('SUM(CASE WHEN metrics.name = "Occupancy" THEN value ELSE 0 END) as totalOccupancy')
        )
        ->groupBy(DB::raw('DATE(date)'), DB::raw('HOUR(date)'))
        ->get();



        $todaySeriesDataUniqueVisitors = array_fill(0, 24, 0);
        $todaySeriesDataOccupancy = array_fill(0, 24, 0);

        $totalUniqueVisitorsToday = 0;
        $totalOccupancyToday = 0;
        $totalUniqueVisitorsYesterday = 0;
        $totalOccupancyYesterday = 0;

        foreach ($data as $entry) {
            if ($entry->day == $startOfToday->toDateString()) {
                $todaySeriesDataUniqueVisitors[$entry->hour] = $entry->totalUniqueVisitors;
                $todaySeriesDataOccupancy[$entry->hour] = $entry->totalOccupancy;

                $totalUniqueVisitorsToday += $entry->totalUniqueVisitors;
                $totalOccupancyToday += $entry->totalOccupancy;
            } elseif ($entry->day == $startOfYesterday->toDateString()) {
                $totalUniqueVisitorsYesterday += $entry->totalUniqueVisitors;
                $totalOccupancyYesterday += $entry->totalOccupancy;
            }
        }
        $percentChangeUniqueVisitors = $this->calculatePercentChange($totalUniqueVisitorsToday, $totalUniqueVisitorsYesterday);
        $percentChangeOccupancy = $this->calculatePercentChange($totalOccupancyToday, $totalOccupancyYesterday);

        return [
            'totalUniqueVisitors' => [
                'number' => number_format($totalUniqueVisitorsToday),
                'percent' => $percentChangeUniqueVisitors > 0 ? "+$percentChangeUniqueVisitors%" : "$percentChangeUniqueVisitors%",
                'seriesData' => $todaySeriesDataUniqueVisitors,
            ],
            'totalOccupancy' => [
                'number' => number_format($totalOccupancyToday),
                'percent' => $percentChangeOccupancy > 0 ? "+$percentChangeOccupancy%" : "$percentChangeOccupancy%",
                'seriesData' => $todaySeriesDataOccupancy,
            ],
        ];
    }
    public function getVisitorsData()
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        // Query for today's data
        $todayResults = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        // Query for yesterday's data
        $yesterdayResults = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->whereBetween('etl.date', ["$yesterday 00:00:00", "$yesterday 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderBy('hour')
            ->get();

        // Initialize data arrays
        $visitorsChartSeries = [];

        // Process today's results
        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        // Process yesterday's results
        foreach ($yesterdayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'data' => array_fill(0, 24, 0),
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        // Calculate metrics comparison between today and yesterday
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday);

        return [
            'visitorsChartSeries1Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries1Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    private function calculateMetricsComparison($today, $yesterday)
    {
        $results = DB::table('etl_data_hourly as etl')
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->leftJoin('streams', 'etl.stream_id', '=', 'streams.id')
            ->selectRaw("
                SUM(CASE WHEN etl.date >= '$today 00:00:00' AND etl.date <= '$today 23:59:59' THEN etl.value ELSE 0 END) AS today_total_value,
                COUNT(CASE WHEN etl.date >= '$today 00:00:00' AND etl.date <= '$today 23:59:59' THEN 1 END) AS today_total_entries,
                SUM(CASE WHEN etl.date >= '$yesterday 00:00:00' AND etl.date <= '$yesterday 23:59:59' THEN etl.value ELSE 0 END) AS yesterday_total_value,
                COUNT(CASE WHEN etl.date >= '$yesterday 00:00:00' AND etl.date <= '$yesterday 23:59:59' THEN 1 END) AS yesterday_total_entries,
                SUM(CASE WHEN person_types.name = 'New' AND etl.date >= '$today 00:00:00' AND etl.date <= '$today 23:59:59' THEN etl.value ELSE 0 END) AS today_new_visitors,
                SUM(CASE WHEN person_types.name = 'New' AND etl.date >= '$yesterday 00:00:00' AND etl.date <= '$yesterday 23:59:59' THEN etl.value ELSE 0 END) AS yesterday_new_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$today 00:00:00' AND etl.date <= '$today 23:59:59' THEN etl.value ELSE 0 END) AS today_souq_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$yesterday 00:00:00' AND etl.date <= '$yesterday 23:59:59' THEN etl.value ELSE 0 END) AS yesterday_souq_visitors
            ")
            ->first();


        $todayAverageFootfall = $results->today_total_value / 24;
        $yesterdayAverageFootfall = $results->yesterday_total_value / 24;

        $footfallPercentageDifference = $yesterdayAverageFootfall > 0
            ? (($todayAverageFootfall - $yesterdayAverageFootfall) / $yesterdayAverageFootfall) * 100
            : 0;

        $newVisitorsPercentageDifference = $results->yesterday_new_visitors > 0
            ? (($results->today_new_visitors - $results->yesterday_new_visitors) / $results->yesterday_new_visitors) * 100
            : 0;

        $souqVisitorsPercentageDifference = $results->yesterday_souq_visitors > 0
            ? (($results->today_souq_visitors - $results->yesterday_souq_visitors) / $results->yesterday_souq_visitors) * 100
            : 0;

        $totalEntriesPercentageDifference = $results->yesterday_total_value > 0
            ? (($results->today_total_value - $results->yesterday_total_value) / $results->yesterday_total_value) * 100
            : 0;

        return [
            'averageFootfall' => [
                'title' => 'avgFootfall',
                'stats' => round($todayAverageFootfall),
                'trend' => $footfallPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($footfallPercentageDifference), 2),
            ],
            'newVisitors' => [
                'title' => 'newVisitors',
                'stats' => $results->today_new_visitors,
                'trend' => $newVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($newVisitorsPercentageDifference), 2),
            ],
            'souqVisitors' => [
                'title' => 'visitorsToSouq',
                'stats' => $results->today_souq_visitors,
                'trend' => $souqVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($souqVisitorsPercentageDifference), 2),
            ],
            'totalEntries' => [
                'title' => 'totalFootfall',
                'stats' => $results->today_total_value,
                'trend' => $totalEntriesPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($totalEntriesPercentageDifference), 2),
            ],
        ];
    }
}