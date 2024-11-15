// ** MUI Imports
import Card from '@mui/material/Card'

// import { useTheme } from '@mui/material/styles'
import CardContent from '@mui/material/CardContent'
import MuiTabList, { TabListProps } from '@mui/lab/TabList'
import Tab from '@mui/material/Tab'
import TabContext from '@mui/lab/TabContext'

import { ApexOptions } from 'apexcharts'

// ** Component Import
import ReactApexcharts from 'src/@core/components/react-apexcharts'
import { Box, Grid, Typography, useMediaQuery } from '@mui/material'
import { styled, useTheme } from '@mui/system'
import React, { SyntheticEvent, useState, useEffect } from 'react'
import { useSettings } from 'src/@core/hooks/useSettings'

import dataJSON from '../../db/data.json'
import { useTranslation } from 'react-i18next'

const TabList = styled(MuiTabList)<TabListProps>(({ theme }) => ({
  minHeight: 40,
  '& .MuiTabs-indicator': {
    display: 'none'
  },
  '& .MuiTab-root': {
    minHeight: 40,
    paddingTop: theme.spacing(2.5),
    paddingBottom: theme.spacing(2.5),
    borderRadius: theme.shape.borderRadius,
    '&.Mui-selected': {
      color: theme.palette.common.white,
      backgroundColor: theme.palette.primary.main
    }
  }
}))

interface Props {
  isDaily?: boolean
  visitorsChartSeries1: any
  visitorsChartSeries1Comparisons: any
  visitorsChartSeries2: any
  visitorsChartSeries2Comparisons: any
  visitorsChartSeries3: any
  visitorsChartSeries3Comparisons: any
  visitorsChartSeries4: any
  visitorsChartSeries4Comparisons: any
  xAxis?: any
}

const VisitorsChart: React.FC<Props> = ({
  isDaily,
  visitorsChartSeries1,
  visitorsChartSeries1Comparisons,
  visitorsChartSeries2,
  visitorsChartSeries2Comparisons,
  visitorsChartSeries3,
  visitorsChartSeries3Comparisons,
  visitorsChartSeries4,
  visitorsChartSeries4Comparisons,
  xAxis
}) => {
  const theme = useTheme()
  const { settings } = useSettings()
  const isMobile = useMediaQuery((theme: any) => theme.breakpoints.down('sm'))
  const isRTL = settings.direction === 'rtl'
  const isAR = settings.language === 'ar'
  const { t } = useTranslation()

  const [value, setValue] = useState<string>('FOOTFALL')
  const [isChartLoaded, setIsChartLoaded] = useState(false)

  const options: ApexOptions = {
    colors: [theme.palette.primary.main, '#70A9A1', '#9EC1A3', '#CFE0C3'],
    chart: {
      parentHeightOffset: 0,
      toolbar: { show: false },
      offsetX: 0
    },
    dataLabels: { enabled: false },
    stroke: {
      width: 4,
      curve: 'straight'
    },
    grid: {
      borderColor: theme.palette.divider,
      padding: {
        top: 5,
        right: 15,
        bottom: 7
      }
    },
    theme: {
      monochrome: {
        enabled: false,
        shadeTo: 'light',
        shadeIntensity: 1,
        color: theme.palette.primary.main
      }
    },
    xaxis: {
      axisTicks: { show: false },
      axisBorder: { show: false },
      tickAmount: isMobile ? 12 : isDaily ? 24 : 12,
      categories: xAxis,
      crosshairs: {
        stroke: { color: `rgba(${theme.palette.customColors.main}, 0.2)` }
      },
      tooltip: {
        enabled: false
      },
      labels: {
        style: {
          fontSize: isDaily ? '12px' : '8px',
          colors: theme.palette.text.disabled
        }
      }
    },
    legend: {
      labels: {
        colors: theme?.palette?.text?.primary
      },
      markers: {
        offsetX: isRTL ? 2 : -2
      }
    },
    yaxis: {
      opposite: isRTL,
      labels: {
        formatter: value => `${value}`,
        padding: isRTL ? 6 : 6,
        style: {
          fontSize: '14px',
          colors: theme.palette.text.disabled
        }
      }
    },
    tooltip: {
      theme: settings.mode
    }
  }

  const handleTabChange = (event: SyntheticEvent, newValue: string) => {
    setValue(newValue)
  }

  const visitorsData = visitorsChartSeries1
  const visitorsChartSeries1Data = visitorsData?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? t(item?.name_ar) : item?.name
  }))

  const uniqueVisitorsData = visitorsChartSeries2
  const visitorsChartSeries2Data = uniqueVisitorsData?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? item?.name_ar : item?.name
  }))

  const repeatedVisitorsData = visitorsChartSeries3
  const visitorsChartSeries3Data = repeatedVisitorsData?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? item?.name_ar : item?.name
  }))

  const occupancyVisitorsData = visitorsChartSeries4
  const visitorsChartSeries4Data = occupancyVisitorsData?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? item?.name_ar : item?.name
  }))

  const chartValues = {
    ['FOOTFALL']: visitorsChartSeries1Data,
    ['UNIQUE VISITORS']: visitorsChartSeries2Data,
    ['REPEATED VISITORS']: visitorsChartSeries3Data,
    ['OCCUPANCY']: visitorsChartSeries4Data
  }

  // @ts-ignore
  const series = chartValues?.[value]
  const data1: any = [
    {
      title: t('totalFootfall'),
      stats: '12,598',
      trendNumber: 1.16
    },
    {
      title: t('avgFootfall'),
      stats: '663.05',
      trend: 'negative',
      trendNumber: 1.15
    },
    {
      title: t('newVisitors'),
      stats: '1519',
      trendNumber: 11.53
    },
    {
      title: t('visitorsToSouq'),
      stats: '257',
      trendNumber: 1.53
    }
  ]

  const data2: any = [
    {
      title: t('totalUniqueVisitors'),
      stats: '2,558',
      trendNumber: 5.99
    },
    {
      title: t('avgUniqueVisitors'),
      stats: '110',
      trend: 'negative',
      trendNumber: 0.33
    },
    {
      title: t('newVisitors'),
      stats: '1519',
      trendNumber: 11.53
    },
    {
      title: t('visitorsToSouq'),
      stats: '257',
      trendNumber: 1.53
    }
  ]

  const data3: any = [
    {
      title: t('totalRepeatedVisitors'),
      stats: '12,598',
      trendNumber: 1.16
    },
    {
      title: t('avgRepeatedVisitors'),
      stats: '663.05',
      trend: 'negative',
      trendNumber: 3.15
    },
    {
      title: t('newVisitors'),
      stats: '1519',
      trendNumber: 11.53
    },
    {
      title: t('visitorsToSouq'),
      stats: '257',
      trendNumber: 1.53
    }
  ]

  const data4: any = [
    {
      title: t('totalOccupancy'),
      stats: '12,598',
      trendNumber: 1.16
    },
    {
      title: t('avgOccupancyVisitors'),
      stats: '663.05',
      trend: 'negative',
      trendNumber: 1.15
    },
    {
      title: t('newVisitors'),
      stats: '1519',
      trendNumber: 11.53
    },
    {
      title: t('visitorsToSouq'),
      stats: '257',
      trendNumber: 1.53
    }
  ]

  const statsValues = {
    ['FOOTFALL']: visitorsChartSeries1Comparisons,
    ['UNIQUE VISITORS']: visitorsChartSeries2Comparisons,
    ['REPEATED VISITORS']: visitorsChartSeries3Comparisons,
    ['OCCUPANCY']: visitorsChartSeries4Comparisons
  }

  useEffect(() => {
    let isMounted = true
    if (isMounted) {
      setIsChartLoaded(false)
      setTimeout(() => {
        setIsChartLoaded(true)
      }, 10)
    }

    return () => {
      isMounted = false
    }
  }, [settings.mode, value])

  useEffect(() => {
    console.log('visitorsChartXAxis', xAxis)
  }, [xAxis])
  // @ts-ignore
  const data = statsValues?.[value]

  return (
    <Grid item xs={12}>
      <Card sx={{ width: '100%' }}>
        <Box sx={{ p: 6, width: '100%', borderBottom: '1px solid #cacccf' }}>
          <TabContext value={value}>
            <TabList variant='scrollable' scrollButtons='auto' onChange={handleTabChange} aria-label='tab widget card'>
              <Tab value='FOOTFALL' label={t('FOOTFALL')} />
              <Tab value='UNIQUE VISITORS' label={t('UNIQUE VISITORS')} />
              <Tab value='REPEATED VISITORS' label={t('REPEATED VISITORS')} />
              <Tab value='OCCUPANCY' label={t('OCCUPANCY')} />
            </TabList>
          </TabContext>
        </Box>

        <Box
          sx={{
            display: 'flex',
            alignItems: 'start',
            flexDirection: { xs: 'column', md: 'row' },
            width: '100%'
          }}
        >
          <CardContent sx={{ flexGrow: '1', width: '100%' }}>
            {isChartLoaded && <ReactApexcharts type='line' height={300} options={options} series={series} />}
          </CardContent>

          <Box
            sx={{
              display: 'flex',
              flexDirection: 'column',
              justifyContent: 'space-between',
              borderLeft: {
                xs: 'none',
                md: '1px solid #cacccf'
              },
              width: { xs: '100%', md: '50%' }
            }}
          >
            <CardContent
              sx={{
                minWidth: { md: '400px', xs: 'none' },
                width: { xs: '100%', md: 'none' },
                minHeight: '350px',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center'
              }}
            >
              <Box
                sx={{
                  display: 'flex',
                  flexDirection: 'column',
                  justifyContent: 'space-between',
                  gap: 4,
                  width: '100%'
                }}
              >
                {data.map((item: any, index: number) => (
                  <Box
                    key={index}
                    sx={{
                      py: 2,
                      px: 3,
                      display: 'flex',
                      borderRadius: 1,
                      alignItems: 'center',
                      backgroundColor: 'background.default',
                      mb: index !== data.length - 1 ? 4 : undefined,
                      width: '100%'
                    }}
                  >
                    <Box
                      sx={{
                        width: '100%',
                        display: 'flex',
                        flexWrap: 'wrap',
                        alignItems: 'flex-end',
                        justifyContent: 'space-between'
                      }}
                    >
                      <Box sx={{ mr: 2, display: 'flex', flexDirection: 'column' }}>
                        <Typography sx={{ color: 'text.secondary' }}>{item.title}</Typography>
                        <Typography sx={{ fontWeight: 500, fontSize: '1.125rem' }}>{item.stats}</Typography>
                      </Box>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                        <Typography
                          variant='body2'
                          sx={{
                            mb: 0.5,
                            fontWeight: 500,
                            color: item.trend === 'negative' ? 'red' : 'green'
                          }}
                        >
                          {`${item.trendNumber}%`}
                        </Typography>

                        <svg
                          xmlns='http://www.w3.org/2000/svg'
                          width={'10px'}
                          height={'10px'}
                          fill={item.trend === 'negative' ? 'red' : 'green'}
                          viewBox='0 0 384 512'
                          style={item.trend !== 'negative' ? { transform: 'scale(1, -1)' } : {}}
                        >
                          <path d='M32 64C14.3 64 0 49.7 0 32S14.3 0 32 0l96 0c53 0 96 43 96 96l0 306.7 73.4-73.4c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-128 128c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 402.7 160 96c0-17.7-14.3-32-32-32L32 64z' />
                        </svg>
                      </Box>
                    </Box>
                  </Box>
                ))}
              </Box>
            </CardContent>
          </Box>
        </Box>
      </Card>
    </Grid>
  )
}

export default VisitorsChart
