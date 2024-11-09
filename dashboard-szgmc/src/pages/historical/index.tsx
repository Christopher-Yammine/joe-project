// ** MUI Imports
import Grid from '@mui/material/Grid'
import LineChart from 'src/components/CommonChart/CommonChart'

import 'chart.js/auto'
import HeatmapChart from 'src/components/Heatmap/HeatMap'
import VisitorsChart from 'src/components/VisitorsChart/VisitorsChart'
import MultiLineChart from 'src/components/MultiLineChart/MultiLineChart'

import dataJSON from '../../db/data.json'
import { useTranslation } from 'react-i18next'
import { useEffect, useState } from 'react'
import useStore from 'src/store/store'
import { useSettings } from 'src/@core/hooks/useSettings'
import { chartData, StaffChartHistorical } from './types'

const API_URL = process.env.NEXT_PUBLIC_BASE_URL

const HistoricalPage = () => {
  const { t } = useTranslation()
  const { settings } = useSettings()
  const isAR = settings.language === 'ar'
  const streams = useStore(state => state.streams)
  const setStreams = useStore(state => state.setStreams)
  const selectedStreams = useStore(state => state.selectedStreams)
  const fromDate = useStore(state => state.fromDate)
  const toDate = useStore(state => state.toDate)
  const durationSelect = useStore(state => state.durationSelect)

  const [totalNewReturningHistoricalVisitors, setTotalNewReturningHistoricalVisitors] = useState<chartData>([])
  const [totalGendersHistoricalVisitors, setTotalGendersHistoricalVisitors] = useState<chartData>([])
  const [totalSentimentsHistoricalVisitors, setTotalSentimentsHistoricalVisitors] = useState<chartData>([])
  const [totalMosqueSouqHistoricalVisitors, setTotalMosqueSouqHistoricalVisitors] = useState<chartData>([])
  const [staffChartSeriesHistorical, setStaffChartSeriesHistorical] = useState<StaffChartHistorical>({
    staffChartSeries: [],
    xAxis: []
  })

  const formatDate = (date: Date): string => {
    return date instanceof Date ? date.toISOString().split('T')[0] : ''
  }

  const getAllStreams = async () => {
    try {
      const response = await fetch(`${API_URL}/streams`)
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const streams = await response.json()
      setStreams(streams)
    } catch (error) {
      console.log(error)
    }
  }

  const fetchStatistics = async () => {
    try {
      let formattedFromDate
      let formattedToDate
      let response
      if (fromDate) {
        formattedFromDate = formatDate(fromDate)
      }
      if (toDate) {
        formattedToDate = formatDate(toDate)
      }

      if (streams.length > 0 && selectedStreams.length === 0) {
        const streamIds = streams
          .flatMap(stream => (stream.options ? stream.options.map(option => option.value) : [stream.value]))
          .join(',')

        response = await fetch(
          `${API_URL}/statistics/historical?stream_id=${streamIds}&from_date=${formattedFromDate}&to_date=${formattedToDate}&duration=${durationSelect}&isHistorical=true`
        )
      } else {
        const selectedStreamIds = selectedStreams.join(',')
        response = await fetch(
          `${API_URL}/statistics/historical?stream_id=${selectedStreamIds}&from_date=${formattedFromDate}&to_date=${formattedToDate}&duration=${durationSelect}&isHistorical=true`
        )
      }
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const data = await response.json()
      console.log('🚀 ~ fetchStatistics ~ data:', data)

      setTotalNewReturningHistoricalVisitors(data?.totalNewReturningHistoricalVisitors)
      setTotalGendersHistoricalVisitors(data?.totalGendersHistoricalVisitors)
      setTotalSentimentsHistoricalVisitors(data?.totalSentimentsHistoricalVisitors)
      setTotalMosqueSouqHistoricalVisitors(data?.totalMosqueSouqHistoricalVisitors)
      setStaffChartSeriesHistorical(data?.staffChartSeriesHistorical)
    } catch (error) {
      console.error('There was a problem with the fetch operation:', error)
    }
  }

  useEffect(() => {
    getAllStreams()
  }, [])

  useEffect(() => {
    if (streams.length > 0) {
      fetchStatistics()
    }
  }, [fromDate, toDate, durationSelect, streams, selectedStreams])

  return (
    <Grid container spacing={4}>
      <LineChart
        firstTitle={
          !isAR
            ? totalNewReturningHistoricalVisitors?.commonChartSeries?.[0]?.name ??
              totalNewReturningHistoricalVisitors?.firstTitle
            : totalNewReturningHistoricalVisitors?.commonChartSeries?.[0]?.name_ar ?? ''
        }
        secondTitle={
          !isAR
            ? totalNewReturningHistoricalVisitors?.commonChartSeries?.[1]?.name ??
              totalNewReturningHistoricalVisitors?.secondTitle
            : totalNewReturningHistoricalVisitors?.commonChartSeries?.[1]?.name_ar ?? ''
        }
        firstGeneralNumber={totalNewReturningHistoricalVisitors?.firstGeneralNumber}
        secondGeneralNumber={totalNewReturningHistoricalVisitors?.secondGeneralNumber}
        series={totalNewReturningHistoricalVisitors?.commonChartSeries || []}
        xAxis={totalNewReturningHistoricalVisitors?.xAxis || []}
        firstTrendNumber={totalNewReturningHistoricalVisitors?.firstTrendNumber}
        secondTrendNumber={totalNewReturningHistoricalVisitors?.secondTrendNumber}
      />

      <LineChart
        firstTitle={
          !isAR
            ? totalGendersHistoricalVisitors?.commonChartSeries?.[0]?.name ?? totalGendersHistoricalVisitors?.firstTitle
            : totalGendersHistoricalVisitors?.commonChartSeries?.[0]?.name_ar ?? ''
        }
        secondTitle={
          !isAR
            ? totalGendersHistoricalVisitors?.commonChartSeries?.[1]?.name ??
              totalGendersHistoricalVisitors?.secondTitle
            : totalGendersHistoricalVisitors?.commonChartSeries?.[1]?.name_ar ?? ''
        }
        firstGeneralNumber={totalGendersHistoricalVisitors?.firstGeneralNumber}
        secondGeneralNumber={totalGendersHistoricalVisitors?.secondGeneralNumber}
        series={totalGendersHistoricalVisitors?.commonChartSeries || []}
        xAxis={totalGendersHistoricalVisitors?.xAxis || []}
        firstTrendNumber={totalGendersHistoricalVisitors?.firstTrendNumber}
        secondTrendNumber={totalGendersHistoricalVisitors?.secondTrendNumber}
      />

      <LineChart
        firstTitle={
          !isAR
            ? totalMosqueSouqHistoricalVisitors?.commonChartSeries?.[0]?.name ??
              totalMosqueSouqHistoricalVisitors?.firstTitle
            : totalMosqueSouqHistoricalVisitors?.commonChartSeries?.[0]?.name_ar ?? ''
        }
        secondTitle={
          !isAR
            ? totalMosqueSouqHistoricalVisitors?.commonChartSeries?.[1]?.name ??
              totalMosqueSouqHistoricalVisitors?.secondTitle
            : totalMosqueSouqHistoricalVisitors?.commonChartSeries?.[1]?.name_ar ?? ''
        }
        firstGeneralNumber={totalMosqueSouqHistoricalVisitors?.firstGeneralNumber}
        secondGeneralNumber={totalMosqueSouqHistoricalVisitors?.secondGeneralNumber}
        series={totalMosqueSouqHistoricalVisitors?.commonChartSeries || []}
        xAxis={totalMosqueSouqHistoricalVisitors?.xAxis || []}
        firstTrendNumber={totalMosqueSouqHistoricalVisitors?.firstTrendNumber}
        secondTrendNumber={totalMosqueSouqHistoricalVisitors?.secondTrendNumber}
      />

      <LineChart
        firstTitle={
          !isAR
            ? totalSentimentsHistoricalVisitors?.commonChartSeries?.[0]?.name ??
              totalSentimentsHistoricalVisitors?.firstTitle
            : totalSentimentsHistoricalVisitors?.commonChartSeries?.[0]?.name_ar ?? ''
        }
        secondTitle={
          !isAR
            ? totalSentimentsHistoricalVisitors?.commonChartSeries?.[1]?.name ??
              totalSentimentsHistoricalVisitors?.secondTitle
            : totalSentimentsHistoricalVisitors?.commonChartSeries?.[1]?.name_ar ?? ''
        }
        firstGeneralNumber={totalSentimentsHistoricalVisitors?.firstGeneralNumber}
        secondGeneralNumber={totalSentimentsHistoricalVisitors?.secondGeneralNumber}
        series={totalSentimentsHistoricalVisitors?.commonChartSeries || []}
        xAxis={totalSentimentsHistoricalVisitors?.xAxis || []}
        firstTrendNumber={totalSentimentsHistoricalVisitors?.firstTrendNumber}
        secondTrendNumber={totalSentimentsHistoricalVisitors?.secondTrendNumber}
      />

      <HeatmapChart series={dataJSON?.heatMapSeries} />

      <VisitorsChart
        visitorsChartSeries1Daily={[]}
        visitorsChartSeries1Dailycomparisons={[]}
        visitorsChartSeries2Daily={[]}
        visitorsChartSeries2Dailycomparisons={[]}
        visitorsChartSeries3Daily={[]}
        visitorsChartSeries3Dailycomparisons={[]}
        visitorsChartSeries4Daily={[]}
        visitorsChartSeries4Dailycomparisons={[]}
      />
      <MultiLineChart
        title={t('staff')}
        staffChartSeries={staffChartSeriesHistorical.staffChartSeries}
        xAxis={staffChartSeriesHistorical.xAxis}
      />
    </Grid>
  )
}

export default HistoricalPage
