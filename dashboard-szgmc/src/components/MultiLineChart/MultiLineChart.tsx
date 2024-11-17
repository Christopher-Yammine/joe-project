// ** MUI Imports
import Card from '@mui/material/Card'

// import { useTheme } from '@mui/material/styles'
import CardHeader from '@mui/material/CardHeader'
import CardContent from '@mui/material/CardContent'

import { ApexOptions } from 'apexcharts'

// ** Component Import
import ReactApexcharts from 'src/@core/components/react-apexcharts'
import { Grid, Theme, useMediaQuery } from '@mui/material'
import { useTheme } from '@mui/system'
import { useSettings } from 'src/@core/hooks/useSettings'

import { useEffect, useState } from 'react'
import { StaffChartSeriesData } from 'src/pages/historical/types'

interface Props {
  title: string
  isDaily?: boolean
  staffChartSeries: StaffChartSeriesData
  xAxis?: string[]
}

const MultiLineChart: React.FC<Props> = ({ title, isDaily = false, staffChartSeries, xAxis }) => {
  const theme = useTheme()

  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const [isChartLoaded, setIsChartLoaded] = useState(false)

  const series = staffChartSeries?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? item?.name_ar : item?.name
  }))

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

  const maxYValue = Math.max(...staffChartSeries.flatMap(series => series.data))
  const adjustedMax = Math.ceil((maxYValue * 1.2) / 20) * 20

  const options: ApexOptions = {
    colors: [theme.palette.primary.main, '#70A9A1', '#9EC1A3', '#CFE0C3'],
    chart: {
      parentHeightOffset: 0,
      toolbar: { show: false }
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
      categories: xAxis
        ? isRTL
          ? xAxis.reverse()
          : xAxis
        : isRTL
        ? [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23].reverse()
        : [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
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

          // fontFamily: theme.typography.fontFamily
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
      min: 0,
      max: adjustedMax,
      labels: {
        padding: isRTL ? 4 : 4,
        formatter: value => `${Math.ceil(value / 20) * 20}`,
        style: {
          fontSize: '14px',
          colors: theme.palette.text.disabled
        }
      },
      tickAmount: 5
    },
    tooltip: {
      theme: settings?.mode,
      marker: {
        show: true
      }
    }
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
  }, [settings.mode])

  return (
    <Grid item xs={12}>
      <Card>
        <CardHeader
          title={title}
          sx={{
            flexDirection: ['column', 'row'],
            alignItems: ['flex-start', 'center'],
            '& .MuiCardHeader-action': { mb: 0 },
            '& .MuiCardHeader-content': { mb: [2, 0] }
          }}
        />
        <CardContent>
          {isChartLoaded && <ReactApexcharts type='line' height={300} options={options} series={series} />}
        </CardContent>
      </Card>
    </Grid>
  )
}

export default MultiLineChart
