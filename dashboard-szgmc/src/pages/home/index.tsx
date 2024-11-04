import Grid from '@mui/material/Grid'
import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { AgeDemographics } from 'src/components/AgeDemographics/AgeDemographics'
import MultiLineChart from 'src/components/MultiLineChart/MultiLineChart'
import { StatisticBlock } from 'src/components/StatisticBlock'
import VerseCard from 'src/components/VerseCard'
import VisitorsChart from 'src/components/VisitorsChart/VisitorsChart'

const API_URL = process.env.NEXT_PUBLIC_BASE_URL

const Home = () => {
  const { t } = useTranslation()
  const [totalStatistics, setTotalStatistics] = useState({
    number: '0',
    percent: '0%',
    cumulativeSeriesData: []
  })
  const [uniqueStatistics, setUniqueStatistics] = useState({
    number: '0',
    percent: '0%',
    cumulativeSeriesData: []
  })
  const [occupancyStatistics, setOccupancyStatistics] = useState({
    number: '0',
    percent: '0%',
    seriesData: []
  })
  const [ageBarChartSeries, setAgeBarChartSeries] = useState([])
  const [happyFacesRangeChartSeries, setHappyFacesRangeChartSeries] = useState([])
  const [visitorsChartSeries1Daily, setVisitorsChartSeries1Daily] = useState([])
  const [visitorsChartSeries1Dailycomparisons, setVisitorsChartSeries1Dailycomparisons] = useState<any[]>([])

  const [ageMinValue, setAgeMinValue] = useState(0)
  const [ageMaxValue, setAgeMaxValue] = useState(0)
  const [sentimentMinVaue, setSentimentMinValue] = useState(0)
  const [sentimentMaxVaue, setSentimentMaxValue] = useState(0)

  useEffect(() => {
    const fetchStatistics = async () => {
      try {
        const response = await fetch(`${API_URL}/statistics/hourly?stream_id=1`)
        if (!response.ok) {
          throw new Error('Network response was not ok')
        }
        const data = await response.json()
        console.log('🚀 ~ fetchStatistics ~ data:', data)
        const visitorsData = data.totalVisitorsCard
        const uniqueVisitors = data.totalUniqueVisitorsCard
        const occupancy = data.totalOccupancyCard
        setVisitorsChartSeries1Dailycomparisons(
          (data?.visitorsChartSeries1Dailycomparisons || []).map(item => ({
            ...item,
            title: t(item.title)
          }))
        )

        setVisitorsChartSeries1Daily(data.visitorsChartSeries1Daily)
        setHappyFacesRangeChartSeries(data.ageSentimentBarChartSeries)
        setAgeBarChartSeries(data.ageBarChartSeries)

        const ageValues = data.ageBarChartSeries
          ? data.ageBarChartSeries.map(item => item.maxWithIncrease).filter(value => value !== undefined)
          : []

        const minAge = ageValues.length > 0 ? Math.min(...ageValues) : 0
        const maxAge = ageValues.length > 0 ? Math.max(...ageValues) : 0
        setAgeMinValue(minAge)
        setAgeMaxValue(maxAge)

        // Handling Happy and Sad Sentiment Data
        const sentimentValues = data.happyFacesRangeChartSeries
          ? data.happyFacesRangeChartSeries.map(item => item.maxWithIncrease).filter(value => value !== undefined)
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

        setTotalStatistics({
          number: visitorsData.number,
          percent: visitorsData.percent,
          cumulativeSeriesData: visitorsData.cumulativeSeriesData
        })
        setUniqueStatistics({
          number: uniqueVisitors.number,
          percent: uniqueVisitors.percent,
          cumulativeSeriesData: uniqueVisitors.cumulativeSeriesData
        })
        setOccupancyStatistics({
          number: occupancy.number,
          percent: occupancy.percent,
          seriesData: occupancy.seriesData
        })
      } catch (error) {
        console.error('There was a problem with the fetch operation:', error)
      }
    }

    fetchStatistics()
    getAllStreams()
  }, [])

  const getAllStreams = async () => {
    try {
      const response = await fetch(`${API_URL}/streams`)
      if (!response.ok) {
        throw new Error('Network response was not ok')
      }
      const streams = await response.json()
      console.log('🚀 ~ fetchStatistics ~ data:', streams)
    } catch (error) {
      console.log(error)
    }
  }
  useEffect(() => {
    console.log(ageBarChartSeries)
  })
  return (
    <Grid container spacing={4}>
      <VerseCard verseCardTextKey={'verseCardTextKey'} />
      <StatisticBlock
        number={totalStatistics.number}
        percent={totalStatistics.percent}
        title={t('totalVisitors')}
        seriesData={totalStatistics.cumulativeSeriesData}
      />
      <StatisticBlock
        number={uniqueStatistics.number}
        percent={uniqueStatistics.percent}
        title={t('uniqueVisitors')}
        seriesData={uniqueStatistics.cumulativeSeriesData}
      />
      <StatisticBlock
        number={occupancyStatistics.number}
        seriesData={occupancyStatistics.seriesData}
        percent={occupancyStatistics.percent}
        title={t('occupancy')}
      />
      <AgeDemographics
        series={ageBarChartSeries}
        title={t('ageGenderDemographic')}
        minValue={ageMinValue}
        maxValue={ageMaxValue}
      />
      <AgeDemographics
        series={happyFacesRangeChartSeries}
        title={t('ageSentimentDemographic')}
        minValue={sentimentMinVaue}
        maxValue={sentimentMaxVaue}
      />
      <VisitorsChart
        isDaily={true}
        visitorsChartSeries1Daily={visitorsChartSeries1Daily}
        visitorsChartSeries1Dailycomparisons={visitorsChartSeries1Dailycomparisons}
      />
      <MultiLineChart title={t('staffToday')} isDaily={true} />
    </Grid>
  )
}

export default Home
