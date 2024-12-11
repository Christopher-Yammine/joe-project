<?php

namespace App\Services;

use App\Models\EtlDataHourly;
use App\Models\Stream;
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


        $todayData = EtlDataHourly::join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('person_types.name', '!=', '')
            ->whereBetween('etl_data_hourly.date', [$startOfToday, $endOfToday])
            ->select(DB::raw('HOUR(etl_data_hourly.date) as hour'), DB::raw('SUM(etl_data_hourly.value) as total'))
            ->groupBy(DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $yesterdayData = EtlDataHourly::join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl_data_hourly.date', [$startOfYesterday, $endOfYesterday])
            ->select(DB::raw('HOUR(etl_data_hourly.date) as hour'), DB::raw('SUM(etl_data_hourly.value) as total'))
            ->groupBy(DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $originalTotalVisitorsToday = $originalTotalVisitorsYesterday = 0;
        $todaySeriesData = [];
        $xAxisCategories = [];

        foreach ($todayData as $entry) {
            if ($entry->total > 0) {
                $hourLabel = str_pad($entry->hour, 2, '0', STR_PAD_LEFT) . ':00';
                $todaySeriesData[] = $entry->total;
                $xAxisCategories[] = $hourLabel;
                $originalTotalVisitorsToday += $entry->total;
            }
        }

        foreach ($yesterdayData as $entry) {
            if ($entry->total > 0) {
                $originalTotalVisitorsYesterday += $entry->total;
            }
        }

        $percentChange = $this->calculatePercentChange($originalTotalVisitorsToday, $originalTotalVisitorsYesterday);
        $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

        $todayCumulativeSeriesData = $this->calculateCumulativeSeries($todaySeriesData);

        return [
            'number' => number_format($originalTotalVisitorsToday),
            'percent' => $percentFormatted,
            'seriesData' => $todaySeriesData,
            'cumulativeSeriesData' => $todayCumulativeSeriesData,
            'xAxis' => $xAxisCategories,
        ];
    }


    public function getTotalUniqueVisitorsCard(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayData = EtlDataHourly::join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('metrics.name', 'Unique')
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl_data_hourly.date', [$startOfToday, $endOfToday])
            ->select(
                DB::raw('DATE(etl_data_hourly.date) as day'),
                DB::raw('HOUR(etl_data_hourly.date) as hour'),
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy(DB::raw('DATE(etl_data_hourly.date)'), DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $yesterdayData = EtlDataHourly::join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('metrics.name', 'Unique')
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl_data_hourly.date', [$startOfYesterday, $endOfYesterday])
            ->select(
                DB::raw('DATE(etl_data_hourly.date) as day'),
                DB::raw('HOUR(etl_data_hourly.date) as hour'),
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy(DB::raw('DATE(etl_data_hourly.date)'), DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $originalTotalVisitorsToday = $originalTotalVisitorsYesterday = 0;
        $todaySeriesData = [];
        $xAxisCategories = [];

        foreach ($todayData as $entry) {
            if ($entry->total > 0) {
                $hourLabel = str_pad($entry->hour, 2, '0', STR_PAD_LEFT) . ':00';
                $todaySeriesData[] = $entry->total;
                $xAxisCategories[] = $hourLabel;
                $originalTotalVisitorsToday += $entry->total;
            }
        }

        foreach ($yesterdayData as $entry) {
            if ($entry->total > 0) {
                $originalTotalVisitorsYesterday += $entry->total;
            }
        }

        $percentChange = $this->calculatePercentChange($originalTotalVisitorsToday, $originalTotalVisitorsYesterday);
        $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

        $todayCumulativeSeriesData = $this->calculateCumulativeSeries($todaySeriesData);

        return [
            'number' => number_format($originalTotalVisitorsToday),
            'percent' => $percentFormatted,
            'seriesData' => $todaySeriesData,
            'cumulativeSeriesData' => $todayCumulativeSeriesData,
            'xAxis' => $xAxisCategories,
        ];
    }


    public function getTotalOccupancyCard(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();
        $startOfYesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        $todayData = EtlDataHourly::join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('metrics.name', 'Occupancy')
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl_data_hourly.date', [$startOfToday, $endOfToday])
            ->select(
                DB::raw('DATE(etl_data_hourly.date) as day'),
                DB::raw('HOUR(etl_data_hourly.date) as hour'),
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy(DB::raw('DATE(etl_data_hourly.date)'), DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $yesterdayData = EtlDataHourly::join('metrics', 'etl_data_hourly.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->whereIn('etl_data_hourly.stream_id', $streamIds)
            ->where('metrics.name', 'Occupancy')
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl_data_hourly.date', [$startOfYesterday, $endOfYesterday])
            ->select(
                DB::raw('DATE(etl_data_hourly.date) as day'),
                DB::raw('HOUR(etl_data_hourly.date) as hour'),
                DB::raw('SUM(etl_data_hourly.value) as total')
            )
            ->groupBy(DB::raw('DATE(etl_data_hourly.date)'), DB::raw('HOUR(etl_data_hourly.date)'))
            ->get();

        $originalTotalVisitorsToday = $originalTotalVisitorsYesterday = 0;
        $todaySeriesData = [];
        $xAxisCategories = [];

        foreach ($todayData as $entry) {
            if ($entry->total > 0) {
                $hourLabel = str_pad($entry->hour, 2, '0', STR_PAD_LEFT) . ':00';
                $todaySeriesData[] = $entry->total;
                $xAxisCategories[] = $hourLabel;
                $originalTotalVisitorsToday += $entry->total;
            }
        }

        foreach ($yesterdayData as $entry) {
            if ($entry->total > 0) {
                $originalTotalVisitorsYesterday += $entry->total;
            }
        }

        $percentChange = $this->calculatePercentChange($originalTotalVisitorsToday, $originalTotalVisitorsYesterday);
        $percentFormatted = $percentChange > 0 ? "+$percentChange%" : "$percentChange%";

        return [
            'number' => number_format(end($todaySeriesData)),
            'percent' => $percentFormatted,
            'seriesData' => $todaySeriesData,
            'xAxis' => $xAxisCategories,
        ];
    }



    public function getAgeGenderSentimentBarChartData(array $streamIds)
    {
        $startOfToday = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        $todayData = EtlDataHourly::whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$startOfToday, $endOfToday])
            ->join('demographics', 'etl_data_hourly.demographics_id', '=', 'demographics.id')
            ->join('person_types', 'etl_data_hourly.person_type_id', '=', 'person_types.id')
            ->join('age_groups', 'demographics.age_group_id', '=', 'age_groups.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->where('person_types.name', '!=', 'Staff')
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

        $ageGroups = $todayData->pluck('group_name')->unique()->sort()->toArray();
        $yAxis = array_reverse(array_values($ageGroups));

        foreach ($todayData as $entry) {
            if ($entry->gender === 'Female') {
                $totalValue = abs($entry->total);
                $ageBarChartSeries['Females'][$entry->group_name] = $totalValue;
                $femaleMax = max($femaleMax, $entry->total);
            } elseif ($entry->gender === 'Male') {
                $totalValue = -abs($entry->total);
                $ageBarChartSeries['Males'][$entry->group_name] = $totalValue;
                $maleMax = max($maleMax, abs($entry->total));
            }

            if ($entry->sentiment === 'Happy') {
                $ageSentimentBarChartSeries['Happy Visitors'][$entry->group_name] = -abs($entry->total);
                $happyMax = max($happyMax, $entry->total);
            } elseif ($entry->sentiment === 'Sad' || $entry->sentiment === 'Neutral') {
                if (!isset($ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name])) {
                    $ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name] = 0;
                }
                $ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name] += abs($entry->total);
                $sadMax = max($sadMax, abs($ageSentimentBarChartSeries['Unhappy Visitors'][$entry->group_name]));
            }
        }

        $maxOverall = max($maleMax, $femaleMax);
        $maxWithIncrease = ceil($maxOverall * 1.1 / 100) * 100;
        $maleMaxWithIncrease = -abs($maxWithIncrease);
        $femaleMaxWithIncrease = abs($maxWithIncrease);

        $sentimentMaxOverall = max($happyMax, $sadMax);
        $sentimentMaxWithIncrease = ceil($sentimentMaxOverall * 1.1 / 100) * 100;
        $happyMaxWithIncrease = -abs($sentimentMaxWithIncrease);
        $sadMaxWithDecrease = abs($sentimentMaxWithIncrease);

        $ageBarChartSeriesFormatted = [];
        foreach (['Males', 'Females'] as $gender) {
            if (!isset($ageBarChartSeries[$gender])) {
                $ageBarChartSeries[$gender] = array_fill_keys($yAxis, 0);
            }

            $data = array_fill_keys($yAxis, 0);
            foreach ($ageBarChartSeries[$gender] as $group => $value) {
                $data[$group] = $value;
            }

            $total = array_sum(array_values($data));
            $ageBarChartSeriesFormatted[] = [
                'name' => "{$gender} [" . number_format(abs($total)) . "]",
                'name_ar' => $this->getArabicName($gender) . " [" . number_format(abs($total)) . "]",
                'data' => array_values($data),
                'maxWithIncrease' => $gender === 'Males' ? $maleMaxWithIncrease : $femaleMaxWithIncrease
            ];
        }


        $ageSentimentBarChartSeriesFormatted = [];
        foreach (['Happy Visitors', 'Unhappy Visitors'] as $sentiment) {
            if (!isset($ageSentimentBarChartSeries[$sentiment])) {
                $ageSentimentBarChartSeries[$sentiment] = array_fill_keys($yAxis, 0);
            }

            $data = array_fill_keys($yAxis, 0);
            foreach ($ageSentimentBarChartSeries[$sentiment] as $group => $value) {
                $data[$group] = $value;
            }

            $total = array_sum(array_values($data));
            $maxWithIncrease = $sentiment === 'Happy Visitors' ? $happyMaxWithIncrease : $sadMaxWithDecrease;
            $ageSentimentBarChartSeriesFormatted[] = [
                'name' => "{$sentiment} [" . number_format(abs($total)) . "]",
                'name_ar' => $this->getArabicName($sentiment) . " [" . number_format(abs($total)) . "]",
                'data' => array_values($data),
                'maxWithIncrease' => $maxWithIncrease
            ];
        }

        return [
            'ageBarChartSeries' => $ageBarChartSeriesFormatted,
            'ageSentimentBarChartSeries' => $ageSentimentBarChartSeriesFormatted,
            'yAxis' => $yAxis
        ];
    }

    public function getVisitorsData(array $streamIds)
    {
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
            ->whereBetween('etl.date', ["$today 09:00:00", "$today 23:59:59"])
            ->where('person_types.name', '!=', 'Staff')
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];
        $uniqueHours = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                    'data' => [],
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] = $row->total_value;
            $uniqueHours[$row->hour] = true;
        }

        $uniqueHours = array_keys($uniqueHours);
        sort($uniqueHours);
        $xAxis = array_map(function ($hour) {
            return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
        }, $uniqueHours);

        foreach ($visitorsChartSeries as &$series) {
            ksort($series['data']);
            $series['data'] = array_values($series['data']);
        }

        $firstReturnTitle = 'avgFootfall';
        $fourthReturnTitle = 'totalFootfall';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, false, null, $firstReturnTitle, $fourthReturnTitle);

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries1Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries1Dailycomparisons' => array_values($calculateMetricsComparison),
            'xAxis' => $xAxis,
        ];
    }

    public function getUniqueVisitorsData(array $streamIds)
    {
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
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('metrics.name', 'Unique')
            ->where('person_types.name', '!=', 'Staff')
            ->whereBetween('etl.date', ["$today 09:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] = number_format($row->total_value);
        }

        foreach ($visitorsChartSeries as &$series) {
            ksort($series['data']);
            $series['data'] = array_values($series['data']);
        }

        $firstReturnTitle = 'avgUniqueVisitors';
        $fourthReturnTitle = 'totalUniqueVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, true, false, null, $firstReturnTitle, $fourthReturnTitle);

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries2Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries2Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }


    public function getRepeatedVisitorsData(array $streamIds)
    {
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
            ->whereBetween('etl.date', ["$today 09:00:00", "$today 23:59:59"])
            ->where('person_types.name', '!=', 'Staff')
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] = $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            ksort($series['data']);
            $series['data'] = array_values($series['data']);
        }

        $personType = 'Returning';
        $firstReturnTitle = 'avgRepeatedVisitors';
        $fourthReturnTitle = 'totalRepeatedVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, false, $personType, $firstReturnTitle, $fourthReturnTitle);

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries3Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries3Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getOccupancyVisitorsData(array $streamIds)
    {
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
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('metrics.name', 'Occupancy')
            ->whereBetween('etl.date', ["$today 09:00:00", "$today 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] = $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            ksort($series['data']);
            $series['data'] = array_values($series['data']);
        }


        $firstReturnTitle = 'avgOccupancyVisitors';
        $fourthReturnTitle = 'totalOccupancy';
        $calculateMetricsComparison = $this->calculateMetricsComparison($today, $yesterday, $streamIds, false, true, null, $firstReturnTitle, $fourthReturnTitle);

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries4Daily' => array_values($visitorsChartSeries),
            'visitorsChartSeries4Dailycomparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getTotalStaffDaily(array $streamIds)
    {
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
            ->where('person_types.name', 'Staff')
            ->whereBetween('etl.date', ["$today 00:00:00", "$today 23:59:59"]) // Full day range
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];
        $earliestHourWithData = 24; // Initialize to max hour
        $latestHourWithData = 0;

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }
            $visitorsChartSeries[$row->name]['data'][$row->hour] = $row->total_value;
            $earliestHourWithData = min($earliestHourWithData, $row->hour);
            $latestHourWithData = max($latestHourWithData, $row->hour);
        }

        // Fill missing hours with null and sort data
        foreach ($visitorsChartSeries as &$series) {
            $filledData = [];
            for ($hour = $earliestHourWithData; $hour <= $latestHourWithData; $hour++) {
                $filledData[] = $series['data'][$hour] ?? null;
            }
            $series['data'] = $filledData;
        }

        // Sort series by name
        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Generate xAxis labels dynamically
        $xAxis = [];
        for ($hour = $earliestHourWithData; $hour <= $latestHourWithData + 1; $hour++) {
            $xAxis[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
        }

        return [
            'staffMultilineChartData' => $visitorsChartSeries,
            'xAxis' => $xAxis,
        ];
    }


    public function getNewReturningHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);
        $selectFields = $this->getSelectFieldsByDuration($duration);

        $uniqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('metrics', $etlDataTable . '.metric_id', '=', 'metrics.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('metrics.name', '=', 'Unique')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $returningVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '=', 'Returning')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalNewVisitors = $uniqueVisitors->sum('total');
        $totalReturningVisitors = $returningVisitors->sum('total');

        $daysRange = $this->getDateRange($fromDate, $toDate, $duration);

        $previousFromDate = $daysRange['fromDatePrevious'];
        $previousToDate = $daysRange['toDatePrevious'];

        $previousUniqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('metrics', $etlDataTable . '.metric_id', '=', 'metrics.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('metrics.name', '=', 'Unique')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $previousReturningVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '=', 'Returning')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalPreviousNewVisitors = $previousUniqueVisitors->sum('total');
        $totalPreviousReturningVisitors = $previousReturningVisitors->sum('total');

        $newVisitorsPercent = $this->calculatePercentChange($totalNewVisitors, $totalPreviousNewVisitors);
        $formattedNewVisitorsPercent = $newVisitorsPercent > 0 ? "+$newVisitorsPercent%" : "$newVisitorsPercent%";

        $returningVisitorsPercent = $this->calculatePercentChange($totalReturningVisitors, $totalPreviousReturningVisitors);
        $formattedReturningVisitorsPercent = $returningVisitorsPercent > 0 ? "+$returningVisitorsPercent%" : "$returningVisitorsPercent%";

        $xAxis = $uniqueVisitors->isEmpty() ? $returningVisitors->pluck('period')->toArray() : $uniqueVisitors->pluck('period')->toArray();
        $uniqueData = $uniqueVisitors->pluck('total')->toArray();
        $returningData = $returningVisitors->pluck('total')->toArray();

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        if (count($uniqueData) === 1) {
            $uniqueData[] = $uniqueData[0];
        }

        if (count($returningData) === 1) {
            $returningData[] = $returningData[0];
        }

        $response = [
            'firstTitle' => 'New',
            'firstGeneralNumber' => strval(number_format($totalNewVisitors)),
            'firstTrendNumber' => strval($formattedNewVisitorsPercent),
            'secondTitle' => 'Returning',
            'secondGeneralNumber' => strval(number_format($totalReturningVisitors)),
            'secondTrendNumber' => strval($formattedReturningVisitorsPercent),
            'xAxis' => $xAxis,
            'commonChartSeries' => [
                [
                    'name' => 'New',
                    'name_ar' => 'جديد',
                    'data' => $uniqueData,
                ],
                [
                    'name' => 'Returning',
                    'name_ar' => 'عودة',
                    'data' => $returningData,
                ]
            ]
        ];

        return $response;
    }

    public function getGenderHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);
        $selectFields = $this->getSelectFieldsByDuration($duration);

        $maleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('genders.gender', '=', 'Male')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $femaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('genders.gender', '=', 'Female')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'),  DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalMaleVisitors = $maleVisitors->sum('total');
        $totalFemaleVisitors = $femaleVisitors->sum('total');

        $daysRange = $this->getDateRange($fromDate, $toDate, $duration);

        $previousFromDate = $daysRange['fromDatePrevious'];
        $previousToDate = $daysRange['toDatePrevious'];

        $previousMaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('genders.gender', '=', 'Male')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $previousFemaleVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('genders', 'demographics.gender_id', '=', 'genders.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('genders.gender', '=', 'Female')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();


        $totalPreviousMaleVisitors = $previousMaleVisitors->sum('total');
        $totalPreviousFemaleVisitors = $previousFemaleVisitors->sum('total');

        $maleVisitorsPercent = $this->calculatePercentChange($totalMaleVisitors, $totalPreviousMaleVisitors);
        $formattedMaleVisitorsPercent = $maleVisitorsPercent > 0 ? "+$maleVisitorsPercent%" : "$maleVisitorsPercent%";

        $femaleVisitorsPercent = $this->calculatePercentChange($totalFemaleVisitors, $totalPreviousFemaleVisitors);
        $formattedFemaleVisitorsPercent = $femaleVisitorsPercent > 0 ? "+$femaleVisitorsPercent%" : "$femaleVisitorsPercent%";

        $xAxis = $maleVisitors->isEmpty() ? $femaleVisitors->pluck('period')->toArray() : $maleVisitors->pluck('period')->toArray();
        $maleData = $maleVisitors->pluck('total')->toArray();
        $femaleData = $femaleVisitors->pluck('total')->toArray();

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        if (count($maleData) === 1) {
            $maleData[] = $maleData[0];
        }

        if (count($femaleData) === 1) {
            $femaleData[] = $femaleData[0];
        }

        $response = [
            'firstTitle' => 'Male',
            'firstGeneralNumber' => strval(number_format($totalMaleVisitors)),
            'firstTrendNumber' => strval($formattedMaleVisitorsPercent),
            'secondTitle' => 'Female',
            'secondGeneralNumber' => strval(number_format($totalFemaleVisitors)),
            'secondTrendNumber' => strval($formattedFemaleVisitorsPercent),
            'xAxis' => $xAxis,
            'commonChartSeries' => [
                [
                    'name' => 'Male',
                    'name_ar' => 'ذكر',
                    'data' => $maleData
                ],
                [
                    'name' => 'Female',
                    'name_ar' => 'أنثى',
                    'data' => $femaleData
                ]
            ]
        ];

        return $response;
    }

    public function getSentimentsHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);
        $selectFields = $this->getSelectFieldsByDuration($duration);

        $happyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('sentiments.sentiment', '=', 'Happy')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $unhappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('sentiments.sentiment', ['Sad', 'Neutral'])
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalHappyVisitors = $happyVisitors->sum('total');
        $totalUnhappyVisitors = $unhappyVisitors->sum('total');

        $daysRange = $this->getDateRange($fromDate, $toDate, $duration);

        $previousFromDate = $daysRange['fromDatePrevious'];
        $previousToDate = $daysRange['toDatePrevious'];

        $previousHappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('sentiments.sentiment', '=', 'Happy')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $previousUnhappyVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('demographics', $etlDataTable . '.demographics_id', '=', 'demographics.id')
            ->join('sentiments', 'demographics.sentiment_id', '=', 'sentiments.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('sentiments.sentiment', ['Sad', 'Neutral'])
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalPreviousHappyVisitors = $previousHappyVisitors->sum('total');
        $totalPreviousUnhappyVisitors = $previousUnhappyVisitors->sum('total');

        $happyVisitorsPercent = $this->calculatePercentChange($totalHappyVisitors, $totalPreviousHappyVisitors);
        $formattedHappyVisitorsPercent = $happyVisitorsPercent > 0 ? "+$happyVisitorsPercent%" : "$happyVisitorsPercent%";

        $unhappyVisitorsPercent = $this->calculatePercentChange($totalUnhappyVisitors, $totalPreviousUnhappyVisitors);
        $formattedUnhappyVisitorsPercent = $unhappyVisitorsPercent > 0 ? "+$unhappyVisitorsPercent%" : "$unhappyVisitorsPercent%";

        $xAxis = $happyVisitors->isEmpty() ? $unhappyVisitors->pluck('period')->toArray() : $happyVisitors->pluck('period')->toArray();
        $happyData = $happyVisitors->pluck('total')->toArray();
        $unhappyData = $unhappyVisitors->pluck('total')->toArray();

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        if (count($happyData) === 1) {
            $happyData[] = $happyData[0];
        }

        if (count($unhappyData) === 1) {
            $unhappyData[] = $unhappyData[0];
        }

        $response = [
            'firstTitle' => 'Happy',
            'firstGeneralNumber' => strval(number_format($totalHappyVisitors)),
            'firstTrendNumber' => strval($formattedHappyVisitorsPercent),
            'secondTitle' => 'Unhappy',
            'secondGeneralNumber' => strval(number_format($totalUnhappyVisitors)),
            'secondTrendNumber' => strval($formattedUnhappyVisitorsPercent),
            'xAxis' => $xAxis,
            'commonChartSeries' => [
                [
                    'name' => 'Happy Visitors',
                    'name_ar' => 'سعيد',
                    'data' => $happyData,
                ],
                [
                    'name' => 'Unhappy Visitors',
                    'name_ar' => 'غير سعيد',
                    'data' => $unhappyData,
                ]
            ]
        ];

        return $response;
    }

    public function getMosqueSouqHistoricalVisitors(array $streamIds, $fromDate, $toDate, $duration)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);
        $selectFields = $this->getSelectFieldsByDuration($duration);

        $mosqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('streams.name', 'like', '%Mosque%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $souqVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$fromDate, $toDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('streams.name', 'like', '%Souq%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalMosqueVisitors = $mosqueVisitors->sum('total');
        $totalSouqVisitors = $souqVisitors->sum('total');

        $daysRange = $this->getDateRange($fromDate, $toDate, $duration);

        $previousFromDate = $daysRange['fromDatePrevious'];
        $previousToDate = $daysRange['toDatePrevious'];

        $previousMosqueVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('streams.name', 'like', '%Mosque%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $previousSouqVisitors = DB::table($etlDataTable)
            ->whereIn('stream_id', $streamIds)
            ->whereBetween('date', [$previousFromDate, $previousToDate])
            ->join('streams', $etlDataTable . '.stream_id', '=', 'streams.id')
            ->join('person_types', $etlDataTable . '.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->where('streams.name', 'like', '%Souq%')
            ->select(DB::raw('SUM(' . $etlDataTable . '.value) as total'), DB::raw($selectFields))
            ->groupBy(DB::raw($groupByFormat))
            ->orderByRaw('2')
            ->get();

        $totalPreviousMosqueVisitors = $previousMosqueVisitors->sum('total');
        $totalPreviousSouqVisitors = $previousSouqVisitors->sum('total');

        $mosqueVisitorsPercent = $this->calculatePercentChange($totalMosqueVisitors, $totalPreviousMosqueVisitors);
        $formattedMosqueVisitorsPercent = $mosqueVisitorsPercent > 0 ? "+$mosqueVisitorsPercent%" : "$mosqueVisitorsPercent%";

        $souqVisitorsPercent = $this->calculatePercentChange($totalSouqVisitors, $totalPreviousSouqVisitors);
        $formattedSouqVisitorsPercent = $souqVisitorsPercent > 0 ? "+$souqVisitorsPercent%" : "$souqVisitorsPercent%";

        $xAxis = $mosqueVisitors->isEmpty() ? $souqVisitors->pluck('period')->toArray() : $mosqueVisitors->pluck('period')->toArray();
        $mosqueVisitors = $mosqueVisitors->pluck('total')->toArray();
        $souqVisitors = $souqVisitors->pluck('total')->toArray();

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        if (count($mosqueVisitors) === 1) {
            $mosqueVisitors[] = $mosqueVisitors[0];
        }

        if (count($souqVisitors) === 1) {
            $souqVisitors[] = $souqVisitors[0];
        }

        $response = [
            'firstTitle' => 'Mosque Visitors',
            'firstGeneralNumber' => strval(number_format($totalMosqueVisitors)),
            'firstTrendNumber' => strval($formattedMosqueVisitorsPercent),
            'secondTitle' => 'Souq Visitors',
            'secondGeneralNumber' => strval(number_format($totalSouqVisitors)),
            'secondTrendNumber' => strval($formattedSouqVisitorsPercent),
            'xAxis' => $xAxis,
            'commonChartSeries' => [
                [
                    'name' => 'Mosque Visitors',
                    'name_ar' => 'زوار المسجد',
                    'data' => $mosqueVisitors,
                ],
                [
                    'name' => 'Souq Visitors',
                    'name_ar' => 'زوار السوق',
                    'data' => $souqVisitors,
                ]
            ]
        ];

        return $response;
    }

    public function getHeatMapChartData(array $streamIds, $fromDate = null, $toDate = null)
    {
        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

        $results = DB::table('etl_data_hourly as etl')
            ->select(
                'streams.name',
                DB::raw('WEEK(etl.date) as week_number'),
                DB::raw('DAYOFWEEK(etl.date) as day_of_week'),
                DB::raw('HOUR(etl.date) as hour'),
                DB::raw('SUM(etl.value) as total_value'),
                DB::raw('ROUND(AVG(SUM(etl.value)) OVER (PARTITION BY streams.name, DAYOFWEEK(etl.date)), 0) as average_total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('etl.stream_id', $streamIds)
            ->whereBetween('etl.date', [$startDate, $endDate])
            ->groupBy(
                'streams.name',
                DB::raw('WEEK(etl.date)'),
                DB::raw('DAYOFWEEK(etl.date)'),
                DB::raw('HOUR(etl.date)')
            )
            ->orderBy('week_number', 'asc')
            ->orderBy('day_of_week', 'asc')
            ->orderBy('hour', 'asc')
            ->get();

        $dayNamesAr = [
            1 => 'الأحد',
            2 => 'الإثنين',
            3 => 'الثلاثاء',
            4 => 'الأربعاء',
            5 => 'الخميس',
            6 => 'الجمعة',
            7 => 'السبت',
        ];

        $dayNames = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        $heatMapData = [];
        $seen = [];

        foreach (range(1, 7) as $dayOfWeek) {
            $heatMapData[$dayOfWeek] = [];
        }

        foreach ($results as $result) {
            $dayOfWeek = $result->day_of_week;
            $hour = $result->hour;
            $totalValue = $result->total_value;
            $averageTotalValue = $result->average_total_value;

            if ($averageTotalValue > 0) {
                $heatMapData[$dayOfWeek][$hour] = $averageTotalValue;
            }

            $key = "{$dayOfWeek}_{$hour}";
            if (!isset($seen[$key]) || $seen[$key]['value'] < $totalValue) {
                $seen[$key] = [
                    'day_of_week' => $dayOfWeek,
                    'hour' => $hour,
                    'value' => $totalValue,
                    'title' => "{$dayNames[$dayOfWeek]}, " . $this->formatHour($hour),
                ];
            }
        }

        $formattedData = [];
        foreach ($heatMapData as $dayOfWeek => $hoursData) {
            $dayName = $dayNames[$dayOfWeek];
            $dayNameAr = $dayNamesAr[$dayOfWeek];

            $dayData = [
                'name' => $dayName,
                'name_ar' => $dayNameAr,
                'data' => [],
            ];

            foreach (range(0, 23) as $hour) {
                if (isset($hoursData[$hour]) && $hoursData[$hour] > 0) {
                    $dayData['data'][] = [
                        'x' => (string) $hour,
                        'y' => $hoursData[$hour],
                    ];
                }
            }

            if (!empty($dayData['data'])) {
                $formattedData[] = $dayData;
            }
        }

        $allHourlyValues = [];
        foreach ($formattedData as $dayData) {
            foreach ($dayData['data'] as $hourData) {
                $allHourlyValues[] = [
                    'title' => $dayData['name'] . ', ' . $this->formatHour($hourData['x']),
                    'value' => $hourData['y'],
                    'hour' => $hourData['x'],
                    'day' => $dayData['name'],
                ];
            }
        }

        usort($allHourlyValues, function ($a, $b) {
            return $b['value'] - $a['value'];
        });

        $topHourlyData = array_slice($allHourlyValues, 0, 4);

        return [
            'series' => array_reverse($formattedData),
            'topHourlyData' => $this->formatTopHourlyData($topHourlyData),
        ];
    }


    private function formatHour($hour)
    {
        if ($hour == 0) {
            return "12 AM";
        } elseif ($hour < 12) {
            return "{$hour} AM";
        } elseif ($hour == 12) {
            return "12 PM";
        } else {
            return ($hour - 12) . " PM";
        }
    }

    private function formatTopHourlyData($topHourlyData)
    {
        $formattedTopData = [];
        foreach ($topHourlyData as $data) {
            $formattedTopData[] = [
                'title' => $data['title'],
                'stats' => (string) $data['value'],
            ];
        }
        return $formattedTopData;
    }


    public function getVisitorsDataHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

        $todayResults = DB::table("$etlDataTable as etl")
            ->select(
                'streams.name',
                DB::raw("$groupByFormat as hour"),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('etl.stream_id', $streamIds)
            ->whereBetween('etl.date', ["$startDate 00:00:00", "$endDate 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];
        $xAxis = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }

            if (!in_array($row->hour, $xAxis)) {
                $xAxis[] = $row->hour;
            }

            if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
            }

            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
            if (count($series['data']) === 1) {
                $series['data'][] = $series['data'][0];
            }
        }

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        $firstReturnTitle = 'avgFootfall';
        $fourthReturnTitle = 'totalFootfall';
        $calculateMetricsComparison = $this->calculateMetricsComparison(
            $startDate,
            $endDate,
            $streamIds,
            false,
            false,
            null,
            $firstReturnTitle,
            $fourthReturnTitle,
            $etlDataTable,
            $duration
        );

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries1' => array_values($visitorsChartSeries),
            'visitorsChartSeries1Comparisons' => array_values($calculateMetricsComparison),
            'xAxis' => $xAxis,
        ];
    }

    public function getUniqueVisitorsDataHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

        $todayResults = DB::table("$etlDataTable as etl")
            ->select(
                'streams.name',
                DB::raw("$groupByFormat as hour"),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('metrics', 'etl.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('metrics.name', 'Unique')
            ->whereBetween('etl.date', ["$startDate 00:00:00", "$endDate 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }

            if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
            }

            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
            if (count($series['data']) === 1) {
                $series['data'][] = $series['data'][0];
            }
        }

        $firstReturnTitle = 'avgUniqueVisitors';
        $fourthReturnTitle = 'totalUniqueVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison(
            $startDate,
            $endDate,
            $streamIds,
            true,
            false,
            null,
            $firstReturnTitle,
            $fourthReturnTitle,
            $etlDataTable,
            $duration
        );

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries2' => array_values($visitorsChartSeries),
            'visitorsChartSeries2Comparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getRepeatedVisitorsDataHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

        $todayResults = DB::table("$etlDataTable as etl")
            ->select(
                'streams.name',
                DB::raw("$groupByFormat as hour"),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('person_types.name', 'Returning')
            ->whereBetween('etl.date', ["$startDate 00:00:00", "$endDate 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }

            if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
            }

            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
            if (count($series['data']) === 1) {
                $series['data'][] = $series['data'][0];
            }
        }


        $personType = 'returning';
        $firstReturnTitle = 'avgRepeatedVisitors';
        $fourthReturnTitle = 'totalRepeatedVisitors';
        $calculateMetricsComparison = $this->calculateMetricsComparison(
            $startDate,
            $endDate,
            $streamIds,
            false,
            false,
            $personType,
            $firstReturnTitle,
            $fourthReturnTitle,
            $etlDataTable,
            $duration
        );

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries3' => array_values($visitorsChartSeries),
            'visitorsChartSeries3Comparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getOccupancyVisitorsDataHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

        $todayResults = DB::table("$etlDataTable as etl")
            ->select(
                'streams.name',
                DB::raw("$groupByFormat as hour"),
                DB::raw('SUM(etl.value) as total_value')
            )
            ->join('streams', 'etl.stream_id', '=', 'streams.id')
            ->join('metrics', 'etl.metric_id', '=', 'metrics.id')
            ->join('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->where('person_types.name', '!=', 'Staff')
            ->whereIn('etl.stream_id', $streamIds)
            ->where('metrics.name', 'Occupancy')
            ->whereBetween('etl.date', ["$startDate 00:00:00", "$endDate 23:59:59"])
            ->groupBy('streams.id', 'hour', 'streams.name')
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($todayResults as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }

            if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
            }

            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
        }

        foreach ($visitorsChartSeries as &$series) {
            $series['data'] = array_values($series['data']);
            if (count($series['data']) === 1) {
                $series['data'][] = $series['data'][0];
            }
        }

        $firstReturnTitle = 'avgOccupancyVisitors';
        $fourthReturnTitle = 'totalOccupancy';
        $calculateMetricsComparison = $this->calculateMetricsComparison(
            $startDate,
            $endDate,
            $streamIds,
            false,
            true,
            null,
            $firstReturnTitle,
            $fourthReturnTitle,
            $etlDataTable,
            $duration
        );

        usort($visitorsChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return [
            'visitorsChartSeries4' => array_values($visitorsChartSeries),
            'visitorsChartSeries4Comparisons' => array_values($calculateMetricsComparison),
        ];
    }

    public function getTotalStaffDailyHistorical(array $streamIds, $fromDate = null, $toDate = null, $duration = null)
    {
        $etlDataTable = $this->getEtlDataTableByDuration($duration);
        $groupByFormat = $this->getGroupByFormat($duration);

        $startDate = "$fromDate 00:00:00";
        $endDate = "$toDate 23:59:59";

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
            ->orderByRaw('2')
            ->get();

        $visitorsChartSeries = [];

        foreach ($results as $row) {
            if (!isset($visitorsChartSeries[$row->name])) {
                $visitorsChartSeries[$row->name] = [
                    'name' => $row->name,
                    'name_ar' => $this->getArabicName($row->name),
                    'data' => [],
                ];
            }

            if (!isset($visitorsChartSeries[$row->name]['data'][$row->hour])) {
                $visitorsChartSeries[$row->name]['data'][$row->hour] = 0;
            }

            $visitorsChartSeries[$row->name]['data'][$row->hour] += $row->total_value;
        }

        $staffChartSeries = [];
        $xAxis = [];

        foreach ($visitorsChartSeries as $series) {
            $staffChartSeries[] = [
                'name' => $series['name'],
                'name_ar' => $series['name_ar'],
                'data' => array_values($series['data']),
            ];
            $xAxis = array_merge($xAxis, array_keys($series['data']));
        }

        foreach ($staffChartSeries as &$series) {
            if (count($series['data']) === 1) {
                $series['data'][] = $series['data'][0];
            }
        }

        if (count($xAxis) === 1) {
            $xAxis[] = $xAxis[0];
        }

        usort($staffChartSeries, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $xAxis = array_values(array_unique($xAxis));

        return [
            'staffMultilineChartData' => $staffChartSeries,
            'xAxis' => $xAxis,
        ];
    }

    private function getEtlDataTableByDuration($duration)
    {
        return match (strtolower($duration)) {
            'daily' => 'etl_data_daily',
            'weekly' => 'etl_data_daily',
            'monthly' => 'etl_data_weekly',
            'yearly' => 'etl_data_monthly',
            default => 'etl_data_daily',
        };
    }
    private function getGroupByFormat($duration)
    {
        return match (strtolower($duration)) {
            'daily' => 'DATE_FORMAT(date, "%b %d, %Y")',
            'weekly' => 'CONCAT(DATE_FORMAT(date, "%b %Y"), " (W", WEEK(date, 4), ")")',
            'monthly' => 'CONCAT(DATE_FORMAT(date, "%b %Y"), " (M", MONTH(date), ")")',
            'yearly' => 'YEAR(date)',
            default => 'DATE_FORMAT(date, "%b %d, %Y")',
        };
    }

    private function getSelectFieldsByDuration($duration)
    {
        return match (strtolower($duration)) {
            'daily' => 'DATE_FORMAT(date, "%b %d, %Y") as period',
            'weekly' => 'CONCAT(DATE_FORMAT(date, "%b %Y"), " (W", WEEK(date, 4), ")") as period',
            'monthly' => 'CONCAT(DATE_FORMAT(date, "%b %Y"), " (M", MONTH(date), ")") as period',
            'yearly' => 'YEAR(date) AS period',
            default => 'DATE_FORMAT(date, "%b %d, %Y") as period',
        };
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
        return strval($yesterday == 0 ? ($today > 0 ? 100 : 0) : round((($today - $yesterday) / $yesterday) * 100));
    }

    private function calculateMetricsComparison(
        $fromDate,
        $toDate,
        array $streamIds,
        $isUniqueMetric = false,
        $isOccupancyMetric = false,
        $personType = null,
        $firstReturnTitle,
        $fourthReturnTitle,
        $etlDataTable = 'etl_data_hourly',
        $duration = 'hourly',
    ) {
        if ($duration === 'hourly') {
            $lastHourWithData = DB::table("$etlDataTable")
                ->where('date', '>=', "$fromDate 00:00:00")
                ->where('date', '<=', "$fromDate 23:59:59")
                ->max(DB::raw('HOUR(date)'));

            $currentHour = $lastHourWithData !== null ? $lastHourWithData : date('G');

            $fromDateStart = "$fromDate 00:00:00";
            $fromDateEnd = "$fromDate " . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ":59:59";
            $toDateStart = "$toDate 00:00:00";
            $toDateEnd = "$toDate " . str_pad($currentHour, 2, '0', STR_PAD_LEFT) . ":59:59";

            $query = $this->getDailyQuery($etlDataTable, $streamIds, $fromDateStart, $fromDateEnd, $toDateStart, $toDateEnd);
        } else {
            $dateRanges = $this->getDateRange($fromDate, $toDate, $duration);

            $fromDateCurrent = $dateRanges['fromDateCurrent'];
            $toDateCurrent = $dateRanges['toDateCurrent'];
            $fromDatePrevious = $dateRanges['fromDatePrevious'];
            $toDatePrevious = $dateRanges['toDatePrevious'];

            $query = $this->getNonDailyQuery($etlDataTable, $streamIds, $fromDateCurrent, $toDateCurrent, $fromDatePrevious, $toDatePrevious);
        }

        if ($personType) {
            $query->where('person_types.name', $personType)
                ->whereIn('etl.stream_id', $streamIds)
                ->first();
        }

        if ($isUniqueMetric) {
            $results = $query->leftJoin('metrics', 'etl.metric_id', '=', 'metrics.id')
                ->where('metrics.name', 'Unique')
                ->whereIn('etl.stream_id', $streamIds)
                ->first();
        } else if ($isOccupancyMetric) {
            $results = $query->leftJoin('metrics', 'etl.metric_id', '=', 'metrics.id')
                ->where('metrics.name', 'Occupancy')
                ->whereIn('etl.stream_id', $streamIds)
                ->first();
        } else {
            $results = $query->whereIn('etl.stream_id', $streamIds)
                ->first();
        }

        $todayAverageFootfall = $results->current_total_value / $this->calculateDurationAverage($duration, $fromDate, $toDate);
        $yesterdayAverageFootfall = $results->previous_total_value / $this->calculateDurationAverage($duration, $fromDate, $toDate);

        $footfallPercentageDifference = $yesterdayAverageFootfall > 0
            ? (($todayAverageFootfall - $yesterdayAverageFootfall) / $yesterdayAverageFootfall) * 100
            : 0;

        $souqVisitorsPercentageDifference = $results->previous_souq_visitors > 0
            ? (($results->current_souq_visitors - $results->previous_souq_visitors) / $results->previous_souq_visitors) * 100
            : 0;

        $totalEntriesPercentageDifference = $results->previous_total_value > 0
            ? (($results->current_total_value - $results->previous_total_value) / $results->previous_total_value) * 100
            : 0;

        $isSouqStreamPresent = Stream::whereIn('id', $streamIds)
            ->where('name', 'like', 'Souq%')
            ->exists();

        $metrics = [
            'averageFootfall' => [
                'title' => $firstReturnTitle,
                'stats' => number_format(round($todayAverageFootfall)),
                'trend' => $footfallPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($footfallPercentageDifference), 2),
            ],
            'totalEntries' => [
                'title' => $fourthReturnTitle,
                'stats' => number_format($results->current_total_value),
                'trend' => $totalEntriesPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($totalEntriesPercentageDifference), 2),
            ],
        ];

        if ($isSouqStreamPresent) {
            $metrics['souqVisitors'] = [
                'title' => 'visitorsToSouq',
                'stats' => number_format($results->current_souq_visitors),
                'trend' => $souqVisitorsPercentageDifference < 0 ? 'negative' : 'positive',
                'trendNumber' => round(abs($souqVisitorsPercentageDifference), 2),
            ];
        }

        return $metrics;
    }

    private function getArabicName($name)
    {
        $arabicNames = [
            'Souq' => 'سوق',
            'Souq Entry 1' => 'دخول السوق 1',
            'Mosque Entry 1' => 'دخول المسجد 1',
            'Mosque Entry 2' => 'دخول المسجد 2',
            'Mosque Entry 3' => 'دخول المسجد 3',
            'Males' => 'الذكور',
            'Females' => 'الإناث',
            'Happy Visitors' => 'الزوار السعداء',
            'Unhappy Visitors' => 'الزوار غير السعداء',
        ];

        return $arabicNames[$name] ?? $name;
    }


    function calculateDurationAverage($duration, $fromDate, $toDate)
    {
        $duration = $duration ?? 'hourly';
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        $totalDays = max(1, $from->diffInDays($to));

        $numberOfPeriods = 1;

        switch (strtolower($duration)) {
            case 'hourly':
                $numberOfPeriods = 24;
                break;
            case 'daily':
                $numberOfPeriods = $totalDays;
                break;
            case 'weekly':
                $numberOfPeriods = ceil($totalDays / 7);
                break;
            case 'monthly':
                $numberOfPeriods = max(1, $from->diffInMonths($to));
                break;
            case 'quarterly':
                $numberOfPeriods = ceil($from->diffInMonths($to) / 3);
                break;
            case 'yearly':
                $numberOfPeriods = max(1, $from->diffInYears($to));
                break;
            default:
                throw new \InvalidArgumentException("Invalid duration specified.");
        }

        return $numberOfPeriods;
    }

    private function getDateRange($fromDate, $toDate, $duration)
    {
        $fromDateCurrent = $toDateCurrent = $fromDatePrevious = $toDatePrevious = null;
        switch (strtolower(($duration))) {
            case 'weekly':
                $fromDateCurrent = (new \DateTime($fromDate))->format('Y-m-d 00:00:00');
                $toDateCurrent = (new \DateTime($toDate))->format('Y-m-d 23:59:59');

                $dateInterval = (new \DateTime($fromDate))->diff(new \DateTime($toDate))->days + 1;

                $fromDatePrevious = (new \DateTime($toDateCurrent))->modify("-$dateInterval days")->format('Y-m-d 00:00:00');
                $toDatePrevious = (new \DateTime($toDateCurrent))->modify("-1 day")->format('Y-m-d 23:59:59');

                break;

            case 'monthly':
                $fromDateCurrent = (new \DateTime($fromDate))->modify('first day of this month')->format('Y-m-d 00:00:00');
                $toDateCurrent = (new \DateTime($fromDate))->modify('last day of this month')->format('Y-m-d 23:59:59');

                $dateInterval = (new \DateTime($fromDate))->diff(new \DateTime($toDate))->days + 1;

                $fromDatePrevious = (new \DateTime($fromDateCurrent))->modify("-$dateInterval days")->format('Y-m-d 00:00:00');
                $toDatePrevious = (new \DateTime($toDateCurrent))->modify("-1 day")->format('Y-m-d 23:59:59');
                break;

            case 'yearly':
                $fromDateCurrent = (new \DateTime($fromDate))->modify('first day of January')->format('Y-m-d 00:00:00');
                $toDateCurrent = (new \DateTime($toDate))->modify('last day of December')->format('Y-m-d 23:59:59');

                $yearDifference = (new \DateTime($toDate))->format('Y') - (new \DateTime($fromDate))->format('Y') + 1;

                $fromDatePrevious = (new \DateTime($fromDateCurrent))->modify("-$yearDifference years")->format('Y-m-d 00:00:00');
                $toDatePrevious = (new \DateTime($toDateCurrent))->modify("-$yearDifference years")->format('Y-m-d 23:59:59');

                break;

            default:
                $fromDateCurrent = "$fromDate 00:00:00";
                $toDateCurrent = "$toDate 23:59:59";

                $dateInterval = (new \DateTime($fromDate))->diff(new \DateTime($toDate))->days + 1;

                $fromDatePrevious = (new \DateTime($fromDate))->modify("-$dateInterval days")->format('Y-m-d 00:00:00');
                $toDatePrevious = (new \DateTime($fromDate))->modify("-1 day")->format('Y-m-d 23:59:59');

                break;
        }

        return [
            'fromDateCurrent' => $fromDateCurrent,
            'toDateCurrent' => $toDateCurrent,
            'fromDatePrevious' => $fromDatePrevious,
            'toDatePrevious' => $toDatePrevious,
        ];
    }

    private function getDailyQuery($etlDataTable, $streamIds, $fromDateStart, $fromDateEnd, $toDateStart, $toDateEnd)
    {
        return DB::table("$etlDataTable as etl")
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->leftJoin('streams', 'etl.stream_id', '=', 'streams.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->selectRaw("
                SUM(CASE WHEN etl.date >= '$fromDateStart' AND etl.date <= '$fromDateEnd' THEN etl.value ELSE 0 END) AS current_total_value,
                COUNT(CASE WHEN etl.date >= '$fromDateStart' AND etl.date <= '$fromDateEnd' THEN 1 END) AS current_total_entries,
                SUM(CASE WHEN etl.date >= '$toDateStart' AND etl.date <= '$toDateEnd' THEN etl.value ELSE 0 END) AS previous_total_value,
                COUNT(CASE WHEN etl.date >= '$toDateStart' AND etl.date <= '$toDateEnd' THEN 1 END) AS previous_total_entries,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$fromDateStart' AND etl.date <= '$fromDateEnd' THEN etl.value ELSE 0 END) AS current_souq_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$toDateStart' AND etl.date <= '$toDateEnd' THEN etl.value ELSE 0 END) AS previous_souq_visitors
            ");
    }

    private function getNonDailyQuery($etlDataTable, $streamIds, $fromDateCurrent, $toDateCurrent, $toDateStart, $toDatePrevious)
    {
        return DB::table("$etlDataTable as etl")
            ->leftJoin('person_types', 'etl.person_type_id', '=', 'person_types.id')
            ->leftJoin('streams', 'etl.stream_id', '=', 'streams.id')
            ->whereIn('etl.stream_id', $streamIds)
            ->selectRaw("
                SUM(CASE WHEN etl.date >= '$fromDateCurrent' AND etl.date <= '$toDateCurrent' THEN etl.value ELSE 0 END) AS current_total_value,
                COUNT(CASE WHEN etl.date >= '$fromDateCurrent' AND etl.date <= '$toDateCurrent' THEN 1 END) AS current_total_entries,
                SUM(CASE WHEN etl.date >= '$toDateStart' AND etl.date <= '$toDatePrevious' THEN etl.value ELSE 0 END) AS previous_total_value,
                COUNT(CASE WHEN etl.date >= '$toDateStart' AND etl.date <= '$toDatePrevious' THEN 1 END) AS previous_total_entries,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$fromDateCurrent' AND etl.date <= '$toDateCurrent' THEN etl.value ELSE 0 END) AS current_souq_visitors,
                SUM(CASE WHEN streams.name = 'Souq Entry 1' AND etl.date >= '$toDateStart' AND etl.date <= '$toDatePrevious' THEN etl.value ELSE 0 END) AS previous_souq_visitors
            ");
    }
}
