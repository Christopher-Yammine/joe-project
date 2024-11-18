import Grid from '@mui/material/Grid'
import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { AgeDemographics } from 'src/components/AgeDemographics/AgeDemographics'
import MultiLineChart from 'src/components/MultiLineChart/MultiLineChart'
import { StatisticBlock } from 'src/components/StatisticBlock'
import VerseCard from 'src/components/VerseCard'
import VisitorsChart from 'src/components/VisitorsChart/VisitorsChart'
import useStore from 'src/store/store'
import dataJSON from '../../db/data.json'
import SkeletonLoading from 'src/@core/layouts/components/skeleton-loading'
import { config } from 'src/configs/config'

const API_URL = config.NEXT_PUBLIC_BASE_URL

const Home = () => {
  const { t } = useTranslation()
  const streams = useStore(state => state.streams)
  const setStreams = useStore(state => state.setStreams)
  const selectedStreams = useStore(state => state.selectedStreams)
  const [totalStatistics, setTotalStatistics] = useState({
    number: '',
    percent: '',
    cumulativeSeriesData: dataJSON?.totalVisitorsCard?.cumulativeSeriesData,
    xAxis: []
  })
  const [uniqueStatistics, setUniqueStatistics] = useState({
    number: '',
    percent: '',
    cumulativeSeriesData: [],
    xAxis: []
  })
  const [occupancyStatistics, setOccupancyStatistics] = useState({
    number: '',
    percent: '',
    seriesData: [],
    xAxis: []
  })
  const [ageBarChartSeries, setAgeBarChartSeries] = useState([])
  const [happyFacesRangeChartSeries, setHappyFacesRangeChartSeries] = useState([])
  const [demographicsYAxis, setDemographicsYAxis] = useState([])
  const [visitorsChartSeries1Daily, setVisitorsChartSeries1Daily] = useState([])
  const [visitorsChartSeries1Dailycomparisons, setVisitorsChartSeries1Dailycomparisons] = useState<any[]>([])
  const [visitorsChartXAxis, setVisitorChartXAxis] = useState([])
  const [visitorsChartSeries2Daily, setVisitorsChartSeries2Daily] = useState([])
  const [visitorsChartSeries2Dailycomparisons, setVisitorsChartSeries2Dailycomparisons] = useState<any[]>([])
  const [visitorsChartSeries3Daily, setVisitorsChartSeries3Daily] = useState([])
  const [visitorsChartSeries3Dailycomparisons, setVisitorsChartSeries3Dailycomparisons] = useState<any[]>([])
  const [visitorsChartSeries4Daily, setVisitorsChartSeries4Daily] = useState([])
  const [visitorsChartSeries4Dailycomparisons, setVisitorsChartSeries4Dailycomparisons] = useState<any[]>([])
  const [staffMultilineChartData, setStaffMultilineChartData] = useState([])
  const [staffMultilineChartXAxis, setStaffMultilineChartXAxis] = useState([])
  const [ageMinValue, setAgeMinValue] = useState(-1000)
  const [ageMaxValue, setAgeMaxValue] = useState(1000)
  const [sentimentMinValue, setSentimentMinValue] = useState(-2000)
  const [sentimentMaxValue, setSentimentMaxValue] = useState(2000)
  const [loading, setLoading] = useState(false)

  const fetchStatistics = async () => {
    try {
      setLoading(true)
      let response

      if (streams.length > 0 && selectedStreams.length === 0) {
        const streamIds = streams
          .flatMap(stream => (stream.options ? stream.options.map(option => option.value) : [stream.value]))
          .join(',')

        response = await fetch(`${API_URL}/statistics/hourly?stream_id=${streamIds}`)
      } else {
        const selectedStreamIds = selectedStreams.join(',')
        response = await fetch(`${API_URL}/statistics/hourly?stream_id=${selectedStreamIds}`)
      }
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const data = await response.json()
      const visitorsData = data.totalVisitorsCard
      const uniqueVisitors = data.totalUniqueVisitorsCard
      const occupancy = data.totalOccupancyCard
      setVisitorsChartSeries1Dailycomparisons(
        (data?.visitorsChartSeries1Dailycomparisons || []).map((item: any) => ({
          ...item,
          title: t(item.title)
        }))
      )

      setVisitorsChartSeries1Daily(data.visitorsChartSeries1Daily)
      setVisitorChartXAxis(data?.xAxis)
      setHappyFacesRangeChartSeries(data.ageSentimentBarChartSeries)
      setAgeBarChartSeries(data.ageBarChartSeries)

      const ageValues = data.ageBarChartSeries
        ? data.ageBarChartSeries.map((item: any) => item.maxWithIncrease).filter((value: any) => value !== undefined)
        : []

      const minAge = ageValues.length > 0 ? Math.min(...ageValues) : 0
      const maxAge = ageValues.length > 0 ? Math.max(...ageValues) : 0
      setAgeMinValue(minAge)
      setAgeMaxValue(maxAge)

      const sentimentValues = data.ageSentimentBarChartSeries
        ? data.ageSentimentBarChartSeries
            .map((item: any) => item.maxWithIncrease)
            .filter((value: any) => value !== undefined)
        : []

      const minSentiment = sentimentValues.length > 0 ? Math.min(...sentimentValues) : 0
      const maxSentiment = sentimentValues.length > 0 ? Math.max(...sentimentValues) : 0

      if (sentimentValues.length === 0) {
        setSentimentMinValue(0)
        setSentimentMaxValue(0)
      } else {
        setSentimentMinValue(minSentiment)
        setSentimentMaxValue(maxSentiment)
      }
      setDemographicsYAxis(data.yAxis)

      setTotalStatistics({
        number: visitorsData.number,
        percent: visitorsData.percent,
        cumulativeSeriesData: visitorsData.cumulativeSeriesData,
        xAxis: visitorsData.xAxis
      })
      setUniqueStatistics({
        number: uniqueVisitors.number,
        percent: uniqueVisitors.percent,
        cumulativeSeriesData: uniqueVisitors.cumulativeSeriesData,
        xAxis: uniqueVisitors.xAxis
      })
      setOccupancyStatistics({
        number: occupancy.number,
        percent: occupancy.percent,
        seriesData: occupancy.seriesData,
        xAxis: occupancy.xAxis
      })
      setVisitorsChartSeries2Dailycomparisons(
        (data?.visitorsChartSeries2Dailycomparisons || []).map((item: any) => ({
          ...item,
          title: t(item.title)
        }))
      )

      setVisitorsChartSeries2Daily(data.visitorsChartSeries2Daily)
      setVisitorsChartSeries3Dailycomparisons(
        (data?.visitorsChartSeries3Dailycomparisons || []).map((item: any) => ({
          ...item,
          title: t(item.title)
        }))
      )

      setVisitorsChartSeries3Daily(data.visitorsChartSeries3Daily)
      setVisitorsChartSeries4Dailycomparisons(
        (data?.visitorsChartSeries4Dailycomparisons || []).map((item: any) => ({
          ...item,
          title: t(item.title)
        }))
      )

      setVisitorsChartSeries4Daily(data.visitorsChartSeries4Daily)
      setStaffMultilineChartData(data.staffChartDaily.staffMultilineChartData)
      setStaffMultilineChartXAxis(data.staffChartDaily.xAxis)
    } catch (error) {
      console.error('There was a problem with the fetch operation:', error)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    getAllStreams()
  }, [])

  useEffect(() => {
    if (streams.length > 0) {
      fetchStatistics()
    }
  }, [streams, selectedStreams])

  const getAllStreams = async () => {
    try {
      setLoading(true)
      const response = await fetch(`${API_URL}/streams`)
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const streams = await response.json()
      setStreams(streams)
    } catch (error) {
      console.log(error)
    } finally {
      setLoading(false)
    }
  }

  return loading ? (
    <SkeletonLoading pageType='overview' />
  ) : (
    <Grid container spacing={4}>
      <VerseCard verseCardTextKey={'verseCardTextKey'} />
      <StatisticBlock
        number={totalStatistics.number}
        percent={totalStatistics.percent}
        title={t('totalVisitors')}
        seriesData={totalStatistics.cumulativeSeriesData}
        xAxis={totalStatistics.xAxis}
      />
      <StatisticBlock
        number={uniqueStatistics.number}
        percent={uniqueStatistics.percent}
        title={t('uniqueVisitors')}
        seriesData={uniqueStatistics.cumulativeSeriesData}
        xAxis={uniqueStatistics.xAxis}
      />
      <StatisticBlock
        number={occupancyStatistics.number}
        seriesData={occupancyStatistics.seriesData}
        percent={occupancyStatistics.percent}
        title={t('occupancy')}
        xAxis={occupancyStatistics.xAxis}
      />
      <AgeDemographics
        series={ageBarChartSeries}
        title={t('ageGenderDemographic')}
        minValue={ageMinValue}
        maxValue={ageMaxValue}
        yAxis={demographicsYAxis}
      />
      <AgeDemographics
        series={happyFacesRangeChartSeries}
        title={t('ageSentimentDemographic')}
        minValue={sentimentMinValue}
        maxValue={sentimentMaxValue}
        yAxis={demographicsYAxis}
      />
      <VisitorsChart
        isDaily={true}
        visitorsChartSeries1={visitorsChartSeries1Daily}
        visitorsChartSeries1Comparisons={visitorsChartSeries1Dailycomparisons}
        visitorsChartSeries2={visitorsChartSeries2Daily}
        visitorsChartSeries2Comparisons={visitorsChartSeries2Dailycomparisons}
        visitorsChartSeries3={visitorsChartSeries3Daily}
        visitorsChartSeries3Comparisons={visitorsChartSeries3Dailycomparisons}
        visitorsChartSeries4={visitorsChartSeries4Daily}
        visitorsChartSeries4Comparisons={visitorsChartSeries4Dailycomparisons}
        xAxis={visitorsChartXAxis}
      />
      <MultiLineChart
        title={t('staffToday')}
        isDaily={true}
        staffMultilineChartData={staffMultilineChartData}
        xAxis={staffMultilineChartXAxis}
      />
    </Grid>
  )
}

export default Home
