// ** MUI Imports
import Card from '@mui/material/Card'
import CardContent from '@mui/material/CardContent'

// ** Third Party Imports
import { Box, Grid, Theme, Typography, useMediaQuery } from '@mui/material'
import ReactApexcharts from 'src/@core/components/react-apexcharts'
import { useTheme } from '@mui/system'
import { ApexOptions } from 'apexcharts'
import { useSettings } from 'src/@core/hooks/useSettings'
import { useEffect, useState } from 'react'

interface Props {
  firstTitle: string
  secondTitle: string
  firstGeneralNumber: string
  secondGeneralNumber: string
  firstTrendNumber?: string
  secondTrendNumber?: string
  isReversed?: boolean
  series: {
    name: string
    name_ar: string
    data: number[]
  }[]
  xAxis: string[]
}

const LineChart: React.FC<Props> = ({
  firstTitle,
  secondTitle,
  firstGeneralNumber,
  secondGeneralNumber,
  firstTrendNumber,
  secondTrendNumber,
  isReversed = false,
  series,
  xAxis
}) => {
  const theme = useTheme()

  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const isAR = settings.language === 'ar'

  const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('sm'))

  const [isChartLoaded, setIsChartLoaded] = useState(false)

  const options: ApexOptions = {
    colors: [theme.palette.primary.main, '#70A9A1'],
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
      tickAmount: isMobile ? 2 : 6,
      categories: xAxis,
      // categories: [
      //   'Oct 2023 (W40)',
      //   'Oct 2023 (W41)',
      //   'Oct 2023 (W42)',
      //   'Oct 2023 (W43)',
      //   'Oct 2023 (W44)',
      //   'Nov 2023 (W45)',
      //   'Nov 2023 (W46)',
      //   'Nov 2023 (W47)',
      //   'Nov 2023 (W48)',
      //   'Dec 2023 (W49)',
      //   'Dec 2023 (W50)',
      //   'Dec 2023 (W51)'
      // ],

      crosshairs: {
        stroke: { color: `rgba(${theme.palette.customColors.main}, 0.2)` }
      },
      tooltip: {
        enabled: false
      },
      labels: {
        rotate: 0,
        hideOverlappingLabels: true,
        style: {
          fontSize: '8px',
          colors: theme.palette.text.disabled
        }
      }
    },
    yaxis: {
      labels: {
        formatter: value => `${value}`,
        padding: isRTL ? -16 : 4,
        style: {
          fontSize: '14px',
          colors: theme.palette.text.disabled

          // fontFamily: theme.typography.fontFamily
        }
      },
      opposite: isRTL
    },
    legend: {
      labels: {
        colors: theme?.palette?.text?.primary
      }
    },
    tooltip: {
      theme: settings.mode
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

  const data = [
    {
      title: firstTitle,
      stats: firstGeneralNumber,
      trendNumber: firstTrendNumber
    },
    {
      title: secondTitle,
      stats: secondGeneralNumber,
      trendNumber: secondTrendNumber,
      isReversed: isReversed
    }
  ]

  const mappedSeries = series?.map(item => ({
    name: isAR ? item?.name_ar : item?.name,
    data: isRTL ? item.data?.reverse() : item?.data
  }))

  return (
    <Grid item xs={12} md={6}>
      <Card>
        <Box sx={{ p: '1.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '20px' }}>
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
                height: '100%',
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
                      color: `${item.trend === 'negative' || item?.isReversed ? 'red' : 'green'}`
                    }}
                  >
                    {`${item.trendNumber}%`}
                  </Typography>

                  {item.trend !== 'negative' ? (
                    <svg
                      xmlns='http://www.w3.org/2000/svg'
                      width='10px'
                      height='10px'
                      fill={item.trend === 'negative' || item?.isReversed ? 'red' : 'green'}
                      viewBox='0 0 384 512'
                    >
                      <path d='M32 448C14.3 448 0 462.3 0 480S14.3 512 32 512h96c53 0 96-43 96-96V109.3L297.4 182.7c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L214.7 9.4C202.2-3.1 181.8-3.1 169.3 9.4L41.3 137.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 109.3V416c0 17.7 14.3 32 32 32h96z' />
                    </svg>
                  ) : (
                    <svg
                      xmlns='http://www.w3.org/2000/svg'
                      width={'10px'}
                      height={'10px'}
                      fill={item.trend === 'negative' || item?.isReversed ? 'red' : 'green'}
                      viewBox='0 0 384 512'
                    >
                      <path d='M32 64C14.3 64 0 49.7 0 32S14.3 0 32 0l96 0c53 0 96 43 96 96l0 306.7 73.4-73.4c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3l-128 128c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 402.7 160 96c0-17.7-14.3-32-32-32L32 64z' />
                    </svg>
                  )}

                  <Typography
                    variant='body2'
                    sx={{
                      mb: 0.5,
                      fontWeight: 500,
                      color: `${item.trend === 'negative' || item?.isReversed ? 'red' : 'green'}`
                    }}
                  >
                    PW
                  </Typography>
                </Box>
              </Box>
            </Box>
          ))}
        </Box>
        <CardContent>
          {isChartLoaded && <ReactApexcharts type='line' height={300} options={options} series={mappedSeries} />}
        </CardContent>
      </Card>
    </Grid>
  )
}

export default LineChart
