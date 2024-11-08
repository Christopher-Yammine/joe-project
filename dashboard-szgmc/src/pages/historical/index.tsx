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

const API_URL = process.env.NEXT_PUBLIC_BASE_URL

type ChartSeries = {
  name: string
  name_ar: string
  data: number[]
}

type chartData = {
  firstTitle: string
  firstGeneralNumber: string
  firstTrendNumber: string
  secondTitle: string
  secondGeneralNumber: string
  secondTrendNumber: string
  xAxis: string[]
  commonChartSeries: ChartSeries[]
}

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
  const [totalGendersHistoricalVisitors, settotalGendersHistoricalVisitors] = useState<chartData>([])

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
          `${API_URL}/statistics/historical?stream_id=${streamIds}&from_date=${formattedFromDate}&to_date=${formattedToDate}&duration=${durationSelect}`
        )
      } else {
        const selectedStreamIds = selectedStreams.join(',')
        response = await fetch(
          `${API_URL}/statistics/historical?stream_id=${selectedStreamIds}&from_date=${formattedFromDate}&to_date=${formattedToDate}&duration=${durationSelect}`
        )
      }
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const data = await response.json()
      console.log('🚀 ~ fetchStatistics ~ data:', data)

      setTotalNewReturningHistoricalVisitors(data?.totalNewReturningHistoricalVisitors)
      settotalGendersHistoricalVisitors(data?.totalGendersHistoricalVisitors)
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
        firstTitle={t('mosqueVisitors')}
        secondTitle={t('souqVisitors')}
        firstGeneralNumber='260,158'
        secondGeneralNumber='65,791'
        series={dataJSON?.commonChartSeries3}
        xAxis={[
          'Oct 2023 (W40)',
          'Oct 2023 (W41)',
          'Oct 2023 (W42)',
          'Oct 2023 (W43)',
          'Oct 2023 (W44)',
          'Nov 2023 (W45)',
          'Nov 2023 (W46)',
          'Nov 2023 (W47)',
          'Nov 2023 (W48)',
          'Dec 2023 (W49)',
          'Dec 2023 (W50)',
          'Dec 2023 (W51)'
        ]}
      />

      <LineChart
        firstTitle={t('happyVisitors')}
        secondTitle={t('unhappyVisitors')}
        firstGeneralNumber='162,755'
        secondGeneralNumber='163,194'
        isReversed={true}
        series={dataJSON?.commonChartSeries4}
        xAxis={[
          'Oct 2023 (W40)',
          'Oct 2023 (W41)',
          'Oct 2023 (W42)',
          'Oct 2023 (W43)',
          'Oct 2023 (W44)',
          'Nov 2023 (W45)',
          'Nov 2023 (W46)',
          'Nov 2023 (W47)',
          'Nov 2023 (W48)',
          'Dec 2023 (W49)',
          'Dec 2023 (W50)',
          'Dec 2023 (W51)'
        ]}
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
      <MultiLineChart title={t('staff')} staffChartSeries={[]} />
    </Grid>
  )
}

export default HistoricalPage
