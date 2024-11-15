import { Card, CardContent, Grid, Typography } from '@mui/material'
import { Box, useTheme } from '@mui/system'
import { ApexOptions } from 'apexcharts'
import React, { useEffect, useState } from 'react'
import ReactApexcharts from 'src/@core/components/react-apexcharts'
import { useSettings } from 'src/@core/hooks/useSettings'

interface IStatisticBlock {
  number?: string
  percent?: string
  title: string
  seriesData?: any[]
  xAxis?: any[]
}

export const StatisticBlock: React.FC<IStatisticBlock> = ({
  number,
  percent,
  title,
  seriesData = [
    0, 20, 30, 35, 40, 45, 50, 100, 100, 100, 110, 120, 130, 140, 190, 200, 212, 222, 232, 245, 246, 246, 261, 281, 300
  ],
  xAxis
}: IStatisticBlock) => {
  const theme = useTheme()
  const [isChartLoaded, setIsChartLoaded] = useState(false)

  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const series = [
    {
      name: title,
      data: isRTL ? seriesData.reverse() : seriesData
    }
  ]

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
      show: false,
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

      tickAmount: 12,
      categories: xAxis
        ? xAxis
        : [
            '00:00',
            '01:00',
            '02:00',
            '03:00',
            '04:00',
            '05:00',
            '06:00',
            '07:00',
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00',
            '19:00',
            '20:00',
            '21:00',
            '22:00',
            '23:00',
            '24:00'
          ],

      crosshairs: {
        stroke: { color: `rgba(${theme.palette.customColors.main}, 0.2)` }
      },
      tooltip: {
        enabled: false
      },
      labels: {
        show: false,
        style: {
          fontSize: '8px',
          colors: theme.palette.text.disabled

          // fontFamily: theme.typography.fontFamily
        }
      }
    },
    yaxis: {
      show: false,
      labels: {
        formatter: value => `${value}`,
        style: {
          fontSize: '14px',
          colors: theme.palette.text.disabled

          // fontFamily: theme.typography.fontFamily
        }
      }
    },
    tooltip: {
      theme: settings?.mode
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
    <Grid item xs={12} md={4} width={'100%'}>
      <Card sx={{ border: '1px solid rgba(0, 0, 0, 0.1)' }}>
        <CardContent sx={{ p: 0, pb: 0 }}>
          <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', px: 4, pt: 2 }}>
            <Box>
              {number && <Typography sx={{ fontSize: '2rem', fontWeight: '300' }}>{number}</Typography>}
              <Typography sx={{ fontWeight: '800' }}>{title}</Typography>
            </Box>
            {percent && (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                <Typography sx={{ fontSize: '0.75rem', color: percent?.[0] === '-' ? 'red' : 'green' }}>
                  {percent.slice(1, percent.length)}
                </Typography>
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  width={'10px'}
                  height={'10px'}
                  fill={percent?.[0] === '-' ? 'red' : 'green'}
                  viewBox='0 0 384 512'
                  style={percent?.[0] === '+' ? { transform: 'scale(1, -1)' } : {}}
                >
                  <path d='M32 64C14.3 64 0 49.7 0 32S14.3 0 32 0l96 0c53 0 96 43 96 96l0 306.7 73.4-73.4c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-128 128c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 402.7 160 96c0-17.7-14.3-32-32-32L32 64z' />
                </svg>
              </Box>
            )}
          </Box>
          {isChartLoaded && <ReactApexcharts series={series} options={options} type='line' height={130} />}
        </CardContent>
      </Card>
    </Grid>
  )
}
