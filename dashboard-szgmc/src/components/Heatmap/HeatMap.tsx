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

import React, { FC, useMemo } from 'react'
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

const HeatmapChart: FC<HeatmapChartProps> = React.memo(({ series, topHourlyData }) => {
  // ** Hook
  const theme = useTheme()

  const { settings } = useSettings()

  const { t } = useTranslation()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

  const getDynamicRanges = useMemo(() => {
    const allYValues = series.flatMap(item => item.data.map(d => d.y))

    const minY = Math.min(...allYValues)
    const maxY = Math.max(...allYValues)

    const valueSpread = maxY - minY

    const calculateRangeCount = (spread: any) => {
      if (spread === 0) return 1
      const percentage = spread / maxY
      if (percentage <= 0.1) return 2
      if (percentage <= 0.2) return 3
      if (percentage <= 0.4) return 4
      if (percentage <= 0.6) return 5

      return 6
    }

    const rangeCount = calculateRangeCount(valueSpread)
    const step = (maxY - minY) / rangeCount

    const staticColors = [
      'rgba(174, 158, 133, 0.10)',
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

      from = Math.floor(from / 5) * 5

      if (i === rangeCount - 1) {
        to = Math.ceil(maxY / 5) * 5
      } else {
        to = Math.floor(to / 5) * 5
      }

      const name = `${from}-${to}`
      const color = staticColors[i]

      dynamicRanges.push({ from, to, name, color })
    }

    return dynamicRanges
  }, [series])

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
        colorScale: {
          ranges: getDynamicRanges
        }
      }
    },
    grid: {
      padding: { top: -20 }
    },
    tooltip: {
      custom: function ({ series, seriesIndex, dataPointIndex, w }) {
        const day = w.globals.seriesNames[seriesIndex]

        const hour = parseInt(w.globals.initialSeries[seriesIndex].data[dataPointIndex].x, 10)
        const value = series[seriesIndex][dataPointIndex]

        let hourFormatted
        if (hour === 0) {
          hourFormatted = '12AM'
        } else if (hour < 12) {
          hourFormatted = `${hour}AM`
        } else if (hour === 12) {
          hourFormatted = '12PM'
        } else {
          hourFormatted = `${hour - 12}PM`
        }

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
                </div>`
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
      categories: [...new Set(series.flatMap(s => s.data.map(dataPoint => dataPoint.x)))],
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

  const mappedSeries = series?.map(item => ({
    name: isAR ? item?.name_ar : item?.name,
    data: isRTL ? item.data?.reverse() : item?.data
  }))

  return (
    getDynamicRanges && (
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
})

export default HeatmapChart
