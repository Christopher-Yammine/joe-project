import React, { useEffect, useState } from 'react'
import { Box, Typography, useTheme } from '@mui/material'
import { useTranslation } from 'react-i18next'
import { useSettings } from 'src/@core/hooks/useSettings'

const WeatherWidget = () => {
  const { palette } = useTheme()
  const { t } = useTranslation()

  const [weatherData, setWeatherData] = useState(null)
  const [loading, setLoading] = useState(true)
  const { settings } = useSettings()

  const isRTL = settings.direction === 'rtl'

  const getWeatherImage = (code, isDaytime) => {
    if (!isDaytime) return '🌙' // Moon icon for night
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
        return '🌤️' // Default partly sunny for other cases
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
  const averageTemp = ((todayTempMax + todayTempMin) / 2).toFixed(1)

  const currentHour = new Date().getHours()
  const isDaytime = currentHour >= 6 && currentHour < 18

  const weatherIcon = getWeatherImage(todayWeatherCode, isDaytime)

  return (
    <Box
      sx={{ display: 'flex', flexDirection: isRTL ? 'row-reverse' : 'row', alignItems: 'center', textAlign: 'center' }}
    >
      <Box>
        <Typography sx={{ fontSize: '.75rem', fontWeight: '700', lineHeight: '1' }}>{t('ABU DHABI')}</Typography>
        <Typography sx={{ fontSize: 'inherit' }}>{t('Weather')}</Typography>
      </Box>
      <Box sx={{ width: '100px', height: '61px' }}>
        <span style={{ fontSize: '2.5rem' }}>{weatherIcon}</span>
      </Box>
      <Box>
        <Typography sx={{ fontSize: '2rem', lineHeight: '1' }}>{averageTemp}&#8451;</Typography>
        <Typography sx={{ fontSize: '.75rem' }}>
          {t(todayWeatherCode === 0 ? 'Sunny' : todayWeatherCode === 1 ? 'Partly Cloudy' : 'Rainy')}
        </Typography>
      </Box>
    </Box>
  )
}

export default WeatherWidget
