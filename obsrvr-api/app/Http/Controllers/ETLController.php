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
    public function processSingleData(Request $request)
    {
        $record = $request->all();
        $timestamp = Carbon::parse($record['Datetime']);
        $demographicId = $this->storeDemographics($record);

        $this->processHourly($record, $timestamp, $demographicId);
        $this->processDaily($record, $timestamp, $demographicId);
        $this->processWeekly($record, $timestamp, $demographicId);
        $this->processMonthly($record, $timestamp, $demographicId);
        $this->processQuarterly($record, $timestamp, $demographicId);
        $this->processYearly($record, $timestamp, $demographicId);

        return response()->json(['message' => 'Data processed successfully'], 200);
    }

    protected function storeDemographics($record)
    {
        return Demographic::updateOrCreate(
            [

                'gender_id' => $this->getGenderId($record['Gender']),
                'age_group_id' => $this->getAgeGroupId($record['AgeGroup']),
                'sentiment_id' => $this->getSentimentId($record['Sentiment'])
            ]
        )->id;
    }

    protected function processHourly($record, $timestamp, $demographicId)
    {

        $date = $timestamp->format('Y-m-d');

        EtlDataHourly::updateOrCreate(
            [
                'date' => $date,
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function processDaily($record, $timestamp, $demographicId)
    {

        $date = $timestamp->format('Y-m-d');

        EtlDataDaily::updateOrCreate(
            [
                'date' => $date,
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function processWeekly($record, $timestamp, $demographicId)
    {

        $date = $timestamp->startOfWeek()->format('Y-m-d');

        EtlDataWeekly::updateOrCreate(
            [
                'date' => $date,
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function processMonthly($record, $timestamp, $demographicId)
    {

        $date = $timestamp->format('Y-m-d');

        EtlDataMonthly::updateOrCreate(
            [
                'date' => $date,
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function processQuarterly($record, $timestamp, $demographicId)
    {

        $quarter = $timestamp->quarter;
        $year = $timestamp->year;

        switch ($quarter) {
            case 1:
                $comparedDate = Carbon::create($year, 1, 1);
                break;
            case 2:
                $comparedDate = Carbon::create($year, 4, 1);
                break;
            case 3:
                $comparedDate = Carbon::create($year, 7, 1);
                break;
            case 4:
                $comparedDate = Carbon::create($year, 10, 1);
                break;
        }

        EtlDataQuarterly::updateOrCreate(
            [
                'date' => $comparedDate->format('Y-m-d'),
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function processYearly($record, $timestamp, $demographicId)
    {

        $date = Carbon::create($timestamp->year, 1, 1);

        EtlDataYearly::updateOrCreate(
            [
                'date' => $date->format('Y-m-d'),
                'demographic_id' => $demographicId
            ],
            ['value' => $record['Value']]
        );
    }

    protected function getPersonTypeId($personType)
    {
        return PersonType::firstOrCreate(['person_type' => $personType])->id;
    }

    protected function getGenderId($gender)
    {
        return Gender::firstOrCreate(['gender' => $gender])->id;
    }

    protected function getAgeGroupId($ageGroup)
    {
        return AgeGroup::firstOrCreate(['group_name' => $ageGroup])->id;
    }

    protected function getSentimentId($sentiment)
    {
        return Sentiment::firstOrCreate(['sentiment' => $sentiment])->id;
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
            ...$staffChartSeries
        ], 200);
    }

    public function getHistoricalStatistics(Request $request) {
        $streamIds = $request->input('stream_id');
        $streamIdsArray = explode(',', $streamIds);

        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $duration = $request->input('duration');

        // $totalNewReturningHistoricalVisitors = $this->statisticsService->getNewReturningHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalGendersHistoricVisitors = $this->statisticsService->getGenderHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalSentimentsHistoricalVisitors = $this->statisticsService->getSentimentsHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalMosqueSouqHistoricalVisitors = $this->statisticsService->getMosqueSouqHistoricalVisitors($streamIdsArray, $fromDate, $toDate, $duration);
        $heatMapData = $this->statisticsService->getHeatMapChartData($streamIdsArray, $fromDate, $toDate);
        // $totalVisitorsHistorical = $this->statisticsService->getVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalUniqueVisitorsHistorical = $this->statisticsService->getUniqueVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalRepeatedVisitorsHistorical = $this->statisticsService->getRepeatedVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        // $totalOccupancyVisitorsHistorical = $this->statisticsService->getOccupancyVisitorsDataHistorical($streamIdsArray, $fromDate, $toDate, $duration);
        // $staffChartSeriesHistorical = $this->statisticsService->getTotalStaffDailyHistorical($streamIdsArray, $fromDate, $toDate, $duration);


        return response()->json([
            // 'totalNewReturningHistoricalVisitors' => $totalNewReturningHistoricalVisitors,
            // 'totalGendersHistoricalVisitors' => $totalGendersHistoricVisitors,
            // 'totalSentimentsHistoricalVisitors' => $totalSentimentsHistoricalVisitors,
            // 'totalMosqueSouqHistoricalVisitors' => $totalMosqueSouqHistoricalVisitors,
            ...$heatMapData,
            // ...$totalVisitorsHistorical,
            // ...$totalUniqueVisitorsHistorical,
            // ...$totalRepeatedVisitorsHistorical,
            // ...$totalOccupancyVisitorsHistorical,
            // 'staffChartSeriesHistorical' => $staffChartSeriesHistorical
        ], 200);
    }
}
