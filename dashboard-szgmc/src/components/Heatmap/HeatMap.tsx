// ** MUI Imports
import Card from '@mui/material/Card'
import { useTheme } from '@mui/material/styles'
import CardHeader from '@mui/material/CardHeader'
import CardContent from '@mui/material/CardContent'

// ** Third Party Imports
import { ApexOptions } from 'apexcharts'

// ** Component Import
import ReactApexcharts from 'src/@core/components/react-apexcharts'

// ** Custom Components Imports
import { Grid, Theme, Typography, useMediaQuery } from '@mui/material'
import { useSettings } from 'src/@core/hooks/useSettings'
import { Box } from '@mui/system'

import { FC, useEffect } from 'react'
import { useTranslation } from 'react-i18next'

type HeatmapChartProps = {
  series: {
    name: string
    name_ar: string
    data: {
      x: string
      y: number
    }[]
  }[]
  topHourlyData: any
}

const HeatmapChart: FC<HeatmapChartProps> = ({ series, topHourlyData }) => {
  // ** Hook
  const theme = useTheme()

  const { settings } = useSettings()

  const { t } = useTranslation()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

  const getDynamicRanges = () => {
    const allYValues = series.flatMap(item => item.data.map(d => d.y))

    const minY = Math.min(...allYValues)
    const maxY = Math.max(...allYValues)

    const rangeCount = 6
    const step = (maxY - minY) / rangeCount

    const staticColors = [
      'rgba(174, 158, 133, 0)',
      'rgba(174, 158, 133, 0.25)',
      'rgba(174, 158, 133, 0.40)',
      'rgba(174, 158, 133, 0.55)',
      'rgba(174, 158, 133, 0.70)',
      'rgba(174, 158, 133, 0.85)'
    ]

    const dynamicRanges = []
    for (let i = 0; i < rangeCount; i++) {
      let from = minY + i * step
      let to = from + step

      from = Math.floor(from)

      if (i === rangeCount - 1) {
        to = Math.ceil(maxY)
      } else {
        to = Math.floor(to)
      }

      const name = `${from}-${to}`
      const color = staticColors[i]

      dynamicRanges.push({ from, to, name, color })
    }
    return dynamicRanges
  }

  const dynamicRanges = getDynamicRanges()

  const options: ApexOptions = {
    chart: {
      parentHeightOffset: 0,

      toolbar: { show: false }
    },

    dataLabels: { enabled: false },
    stroke: {
      colors: [theme.palette.mode === 'light' ? theme.palette.background.paper : theme.palette.customColors.bodyBg]
    },
    legend: {
      position: 'bottom',
      labels: {
        colors: theme.palette.text.secondary
      },
      markers: {
        offsetY: 0,
        offsetX: -3
      },
      itemMargin: {
        vertical: 3,
        horizontal: 10
      }
    },
    plotOptions: {
      heatmap: {
        enableShades: false,
        // colorScale: {
        //   ranges: [
        //     { to: 10, from: 0, name: '0-10', color: 'rgba(174, 158, 133, 0.19)' },
        //     { to: 20, from: 11, name: '10-20', color: 'rgba(174, 158, 133, 0.33)' },
        //     { to: 30, from: 21, name: '20-30', color: 'rgba(174, 158, 133, 0.52)' },
        //     { to: 40, from: 31, name: '30-40', color: 'rgba(174, 158, 133, 0.65)' },
        //     { to: 50, from: 41, name: '40-50', color: 'rgba(174, 158, 133, 0.78)' },
        //     { to: 60, from: 51, name: '50-60', color: '#ae9e85' }
        //   ]
        // }
        colorScale: {
          ranges: dynamicRanges
        }
      }
    },
    grid: {
      padding: { top: -20 }
    },
    tooltip: {
      custom: function ({ series, seriesIndex, dataPointIndex, w }) {
        const day = w.globals.seriesNames[seriesIndex]
        const hour = dataPointIndex
        const value = series[seriesIndex][dataPointIndex]
        const hourFormatted = hour > 12 ? `${hour - 12}PM` : `${hour}AM`

        const ranges = options.plotOptions?.heatmap?.colorScale?.ranges || []

        const matchingRange = ranges.find(
          range => range?.from && value >= range?.from && range?.to && value <= range?.to
        )
        const cellColor = matchingRange?.color || '#4C4B56'

        const date = `${day}, ${hourFormatted}`

        return `<div class='heatmap-tooltip'>
                  <div class='date'>${date}</div>
                  <div class='label'>Viewers</div>
                  <div class='value'>
                    <div style='background-color: ${cellColor}'></div>
                    <span>${value}</span>
                  </div>
                </div>
              `
      },
      theme: settings.mode
    },
    yaxis: {
      opposite: isRTL,
      labels: {
        padding: isRTL ? 4 : 20,
        style: {
          colors: theme.palette.text.disabled
        },
        formatter: (value: any) => {
          return !isAR ? String(value)?.slice(0, 3).toUpperCase() : `${value}`
        }
      }
    },
    xaxis: {
      labels: {
        show: true,
        style: {
          colors: theme.palette.text.disabled
        }
      },
      tooltip: {
        enabled: false
      },
      crosshairs: {
        show: false
      },
      axisTicks: { show: false },
      axisBorder: { show: false },
      tickAmount: isMobile ? 8 : undefined
    }
  }

  const data: any = [
    {
      title: `${t('saturday')}, 2 PM`,
      stats: '60',
      trendNumber: 1.16
    },
    {
      title: `${t('friday')}, 1 PM`,
      stats: '60',
      trend: 'negative',
      trendNumber: 1.15
    },
    {
      title: `${t('thursday')}, 4 PM`,
      stats: '60',
      trendNumber: 11.53
    },
    {
      title: `${t('wednesday')}, 3 PM`,
      stats: '60',
      trendNumber: 1.53
    }
  ]

  const mappedSeries = series?.map(item => ({
    name: isAR ? item?.name_ar : item?.name,
    data: isRTL ? item.data?.reverse() : item?.data
  }))

  useEffect(() => {
    console.log(dynamicRanges)
  }, [dynamicRanges])

  return (
    dynamicRanges && (
      <Grid item xs={12}>
        <Card>
          <CardHeader title={t('avgPeakEngagement')} sx={{ borderBottom: '1px solid #cacccf' }} />
          <Box
            sx={{
              display: 'flex',
              alignItems: 'start',
              flexDirection: { xs: 'column', md: 'row' }
            }}
          >
            <Box sx={{ width: '100%', pb: 0, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <CardContent
                sx={{ width: '100%', padding: '0', paddingTop: '12px !important', paddingBottom: '0px !important' }}
              >
                <ReactApexcharts type='heatmap' height={330} options={options} series={mappedSeries} />
              </CardContent>
            </Box>

            <Box
              sx={{
                display: 'flex',
                flexDirection: 'column',
                justifyContent: 'space-between',
                borderLeft: {
                  xs: 'none',
                  md: '1px solid #cacccf'
                },
                height: '100%',
                width: { xs: '100%', md: '50%' }
              }}
            >
              <CardContent sx={{ minWidth: { md: '400px', xs: 'none' }, height: '412px' }}>
                {topHourlyData.map((item: any, index: number) => (
                  <Box
                    key={index}
                    sx={{
                      py: 2,
                      px: 3,
                      display: 'flex',
                      width: '100%',
                      borderRadius: 1,
                      alignItems: 'center',
                      backgroundColor: 'background.default',
                      mb: index !== topHourlyData.length - 1 ? 4 : undefined
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
                    </Box>
                  </Box>
                ))}
              </CardContent>
            </Box>
          </Box>
        </Card>
      </Grid>
    )
  )
}

export default HeatmapChart
