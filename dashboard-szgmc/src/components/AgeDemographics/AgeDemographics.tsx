import { Card, CardHeader, Grid, Theme, useMediaQuery, useTheme } from '@mui/material'
import React, { FC, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import ReactApexcharts from 'src/@core/components/react-apexcharts'
import { useSettings } from 'src/@core/hooks/useSettings'

type AgeDemographicsProps = {
  series: {
    name: string
    name_ar?: string
    data: number[]
    minValue?: number
    maxValue?: number
  }[]
  title: string
  minValue: number
  maxValue: number
  yAxis?: string[]
}

const sharedColors = ['#CFE0C3', '#ae9e85']

export const AgeDemographics: FC<AgeDemographicsProps> = ({ series, title, maxValue, minValue, yAxis }) => {
  const theme = useTheme()

  const { t } = useTranslation()

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const mappedSeries = series?.map(item => ({
    name: isAR ? item?.name_ar : item?.name,
    data: isRTL ? item.data?.reverse() : item?.data
  }))

  const options = {
    chart: {
      type: 'bar',
      height: 440,
      stacked: true,
      toolbar: { show: false }
    },
    colors: sharedColors,
    plotOptions: {
      bar: {
        horizontal: true,
        barHeight: '80%'
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      width: 1,
      colors: ['#fff']
    },
    grid: {
      xaxis: {
        lines: {
          show: false
        }
      }
    },
    yaxis: {
      labels: {
        style: {
          colors: theme?.palette?.text?.primary
        }
      },
      min: minValue ?? 0,
      max: maxValue ?? 0,

      title: {
        text: 'Age Group',
        style: {
          color: theme?.palette?.text?.primary
        }
      },
      opposite: isRTL,
      style: {
        colors: theme?.palette?.text?.primary
      }
    },
    tooltip: {
      shared: false,
      x: {
        formatter: function (val: any) {
          return val
        }
      },
      y: {
        formatter: function (val: any) {
          return Math.abs(val)
        }
      },
      theme: settings?.mode
    },
    xaxis: {
      categories: yAxis,
      title: {
        text: t('totalVisitors'),
        style: {
          color: theme?.palette?.text?.primary
        }
      },
      labels: {
        style: {
          colors: theme?.palette?.text?.primary
        },
        formatter: function (val: any) {
          return Math.abs(Math.round(val))
        }
      },
      tickAmount: isMobile ? 3 : undefined
    },
    legend: {
      labels: {
        colors: theme?.palette?.text?.primary
      },
      markers: {
        offsetX: isRTL ? 2 : -2
      }
    }
  }

  return (
    <Grid item xs={12} md={6} width={'100%'}>
      <Card sx={{ p: 6 }}>
        <CardHeader
          title={title}
          sx={{
            flexDirection: ['column', 'row'],
            alignItems: ['flex-start', 'center'],
            '& .MuiCardHeader-action': { mb: 0 },
            '& .MuiCardHeader-content': { mb: [2, 0] },
            p: 0
          }}
        />
        <ReactApexcharts series={mappedSeries} options={options as any} type='bar' height={'400'} />
      </Card>
    </Grid>
  )
}
