// ** MUI Imports
import Grid from '@mui/material/Grid'
import LineChart from 'src/components/CommonChart/CommonChart'

import 'chart.js/auto'
import HeatmapChart from 'src/components/Heatmap/HeatMap'
import VisitorsChart from 'src/components/VisitorsChart/VisitorsChart'
import MultiLineChart from 'src/components/MultiLineChart/MultiLineChart'

import dataJSON from '../../db/data.json'
import { useTranslation } from 'react-i18next'

const HistoricalPage = () => {
  const { t } = useTranslation()

  return (
    <Grid container spacing={4}>
      <LineChart
        firstTitle={t('new')}
        secondTitle={t('returning')}
        firstGeneralNumber='260,158'
        secondGeneralNumber='65,791'
        series={dataJSON?.commonChartSeries1}
      />

      <LineChart
        firstTitle={t('female')}
        secondTitle={t('male')}
        firstGeneralNumber='162,755'
        secondGeneralNumber='163,194'
        series={dataJSON?.commonChartSeries2}
      />

      <LineChart
        firstTitle={t('mosqueVisitors')}
        secondTitle={t('souqVisitors')}
        firstGeneralNumber='260,158'
        secondGeneralNumber='65,791'
        series={dataJSON?.commonChartSeries3}
      />

      <LineChart
        firstTitle={t('happyVisitors')}
        secondTitle={t('unhappyVisitors')}
        firstGeneralNumber='162,755'
        secondGeneralNumber='163,194'
        isReversed={true}
        series={dataJSON?.commonChartSeries4}
      />

      <HeatmapChart series={dataJSON?.heatMapSeries} />

      <VisitorsChart />
      <MultiLineChart title={t('staff')} />
    </Grid>
  )
}

export default HistoricalPage
