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

import dataJSON from '../../db/data.json'
import { useEffect, useState } from 'react'

interface Props {
  title: string
  isDaily?: boolean
}

const MultiLineChart: React.FC<Props> = ({ title, isDaily = false }) => {
  const theme = useTheme()

  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const [isChartLoaded, setIsChartLoaded] = useState(false)

  const series = dataJSON[`staffChartSeries${isDaily ? 'Daily' : 'Total'}`]?.map(item => ({
    data: isRTL ? item.data?.reverse() : item.data,
    name: isAR ? item?.name_ar : item?.name
  }))

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

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
        right: 6,
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
      categories: !isDaily
        ? [
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
          ]
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
      labels: {
        padding: isRTL ? 4 : 0,
        formatter: value => `${value}`,
        style: {
          fontSize: '14px',
          colors: theme.palette.text.disabled
        }
      }
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
