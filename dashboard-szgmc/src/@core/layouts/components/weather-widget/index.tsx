// import { Box, Typography, useTheme } from '@mui/material'
// import { useTranslation } from 'react-i18next'

// const WeatherWidget = () => {
//   const { palette } = useTheme()

//   const { t } = useTranslation()
//   return (
//     <>
//       <Box>
//         <Typography sx={{ fontSize: '.75rem', fontWeight: '700', lineHeight: '1' }}>{t('ABU DHABI')}</Typography>
//         <Typography sx={{ fontSize: 'inherit' }}>{t('Weather')}</Typography>
//       </Box>
//       <Box sx={{ width: '100px', height: '61px' }}>
//         <svg
//           xmlns='http://www.w3.org/2000/svg'
//           xmlnsXlink='http://www.w3.org/1999/xlink'
//           fill={palette.action.active}
//           height='100%'
//           width='100%'
//           version='1.1'
//           id='Layer_1'
//           viewBox='0 0 512.001 512.001'
//           xmlSpace='preserve'
//         >
//           <g>
//             <g>
//               <path d='M344.381,143.771C254.765,56.017,102.37,103.776,79.825,227.7c-31.849,4.598-59.138,25.445-72.018,55.076    c-0.016,0.035-0.032,0.07-0.047,0.107c-26.687,61.602,18.784,130.232,85.51,130.232h282.267    c75.246,0,136.463-61.216,136.463-136.462C512,189.241,430.314,123.682,344.381,143.771z M375.537,381.12H93.271    c-69.246,0-84.534-98.263-18.714-119.456c14.753-4.65,43.01-7.348,74.38,21.892c6.464,6.024,16.586,5.667,22.61-0.794    c6.024-6.464,5.668-16.586-0.794-22.61c-17.93-16.712-38.071-27.33-58.484-31.453c22.034-99.077,147.374-131.851,215.247-56.305    c4.189,4.661,10.714,6.451,16.693,4.57c67.272-21.117,135.795,29.374,135.795,99.69    C480.005,334.256,433.141,381.12,375.537,381.12z' />
//             </g>
//           </g>
//         </svg>
//       </Box>
//       <Box>
//         <Typography sx={{ fontSize: '2rem', lineHeight: '1' }}>27&#8451;</Typography>
//         <Typography sx={{ fontSize: '.75rem' }}>{t('light rain')}</Typography>
//       </Box>
//     </>
//   )
// }

// export default WeatherWidget

import React, { useEffect, useState } from 'react'
import { Box, Typography, useTheme } from '@mui/material'
import { useTranslation } from 'react-i18next'

const WeatherWidget = () => {
  const { palette } = useTheme()
  const { t } = useTranslation()

  const [weatherData, setWeatherData] = useState(null)
  const [loading, setLoading] = useState(true)

  const getWeatherImage = code => {
    switch (code) {
      case 0:
        return '☀️' // Clear sky
      case 1:
      case 2:
      case 3:
        return '⛅️' // Partly cloudy
      case 45:
      case 48:
        return '🌫️' // Fog
      case 51:
      case 53:
      case 55:
        return '🌦️' // Drizzle
      case 61:
      case 63:
      case 65:
        return '🌧️' // Rain
      case 71:
      case 73:
      case 75:
        return '🌨️' // Snow
      default:
        return '🌤️' // Default: partly sunny
    }
  }

  useEffect(() => {
    const fetchWeather = async () => {
      setLoading(true)
      try {
        const response = await fetch(
          'https://api.open-meteo.com/v1/forecast?latitude=24.4539&longitude=54.3773&daily=temperature_2m_max,temperature_2m_min,weathercode&forecast_days=1&timezone=auto'
        )
        const data = await response.json()
        setWeatherData(data.daily)
      } catch (error) {
        console.error('Error fetching weather data:', error)
      }
      setLoading(false)
    }

    fetchWeather()
  }, [])

  if (loading) return <Typography>Loading...</Typography>
  if (!weatherData) return <Typography>No weather data available.</Typography>

  const { temperature_2m_max, temperature_2m_min, weathercode } = weatherData
  const todayTempMax = temperature_2m_max[0]
  const todayTempMin = temperature_2m_min[0]
  const todayWeatherCode = weathercode[0]
  const weatherIcon = getWeatherImage(todayWeatherCode)

  return (
    <Box sx={{ display: 'flex', flexDirection: 'row', alignItems: 'center', textAlign: 'center' }}>
      <Box>
        <Typography sx={{ fontSize: '.75rem', fontWeight: '700', lineHeight: '1' }}>{t('ABU DHABI')}</Typography>
        <Typography sx={{ fontSize: 'inherit' }}>{t('Weather')}</Typography>
      </Box>
      <Box sx={{ width: '100px', height: '61px' }}>
        <span style={{ fontSize: '3rem' }}>{weatherIcon}</span>
      </Box>
      <Box>
        <Typography sx={{ fontSize: '2rem', lineHeight: '1' }}>{todayTempMax}&#8451;</Typography>
        <Typography sx={{ fontSize: '.75rem' }}>
          {t(todayWeatherCode === 0 ? 'Sunny' : todayWeatherCode === 1 ? 'Partly Cloudy' : 'Rainy')}
        </Typography>
      </Box>
    </Box>
  )
}

export default WeatherWidget
