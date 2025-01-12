<?php

namespace App\Http\Controllers;

use App\Models\AgeGroup;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\EtlDataHourly;
use App\Models\EtlDataDaily;
use App\Models\EtlDataWeekly;
use App\Models\EtlDataMonthly;
use App\Models\EtlDataQuarterly;
use App\Models\EtlDataYearly;
use App\Models\Demographic;
use App\Models\Gender;
use App\Models\PersonType;
use App\Models\Sentiment;
use App\Services\StatisticsService;

class ETLController extends Controller
{
    protected $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function getHourlyStatistics(Request $request)
    {
        $streamIds = $request->input('stream_id');
        $streamIdsArray = explode(',', $streamIds);

        $totalVisitorsCard = $this->statisticsService->getTotalVisitorsCard($streamIdsArray);
        $totalUniqueVisitorsCard = $this->statisticsService->getTotalUniqueVisitorsCard($streamIdsArray);
        $totalOccupancyCard = $this->statisticsService->getTotalOccupancyCard($streamIdsArray);
        $ageSentimentGenderBarchart = $this->statisticsService->getAgeGenderSentimentBarChartData($streamIdsArray);
        $totalVisitorsperStream = $this->statisticsService->getVisitorsData($streamIdsArray);
        $totalUniqueVisitorsPerStream = $this->statisticsService->getUniqueVisitorsData($streamIdsArray);
        $totalRepeatedVisitorsPerStream = $this->statisticsService->getRepeatedVisitorsData($streamIdsArray);
        $totalOccupancyVisitorsPerStream = $this->statisticsService->getOccupancyVisitorsData($streamIdsArray);
        $staffChartSeries = $this->statisticsService->getTotalStaffDaily($streamIdsArray);
        return response()->json([
            'totalVisitorsCard' => $totalVisitorsCard,
            'totalUniqueVisitorsCard' => $totalUniqueVisitorsCard,
            'totalOccupancyCard' => $totalOccupancyCard,
            ...$ageSentimentGenderBarchart,
            ...$totalVisitorsperStream,
            ...$totalUniqueVisitorsPerStream,
            ...$totalRepeatedVisitorsPerStream,
            ...$totalOccupancyVisitorsPerStream,
            'staffChartDaily' => $staffChartSeries
        ], 200);
    }

    public function getHistoricalStatistics(Request $request) {
        $streamIds = $request->input('stream_id');
        $streamIdsArray = explode(',', $streamIds);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $duration = $request->input('duration');

        $totalNewReturningHistoricalVisitors = $this->statisticsService->getNewReturningHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        $totalGendersHistoricVisitors = $this->statisticsService->getGenderHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        $totalSentimentsHistoricalVisitors = $this->statisticsService->getSentimentsHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        $totalMosqueSouqHistoricalVisitors = $this->statisticsService->getMosqueSouqHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        $heatMapData = $this->statisticsService->getHeatMapChartData($streamIdsArray, $fromDate, $toDate);
        $totalVisitorsHistorical = $this->statisticsService->getVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        $totalUniqueVisitorsHistorical = $this->statisticsService->getUniqueVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        $totalRepeatedVisitorsHistorical = $this->statisticsService->getRepeatedVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        $totalOccupancyVisitorsHistorical = $this->statisticsService->getOccupancyVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        $staffChartSeriesHistorical = $this->statisticsService->getTotalStaffDailyHistorical($streamIdsArray, $fromDate, $toDate, $duration);


        return response()->json([
            'totalNewReturningHistoricalVisitors' => $totalNewReturningHistoricalVisitors,
            'totalGendersHistoricalVisitors' => $totalGendersHistoricVisitors,
            'totalSentimentsHistoricalVisitors' => $totalSentimentsHistoricalVisitors,
            'totalMosqueSouqHistoricalVisitors' => $totalMosqueSouqHistoricalVisitors,
            ...$heatMapData,
            ...$totalVisitorsHistorical,
            ...$totalUniqueVisitorsHistorical,
            ...$totalRepeatedVisitorsHistorical,
            ...$totalOccupancyVisitorsHistorical,
            'staffChartSeriesHistorical' => $staffChartSeriesHistorical
        ], 200);
    }
}
