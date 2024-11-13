// ** Next Import
import Link from 'next/link'

// ** MUI Imports
import Box from '@mui/material/Box'
import Typography from '@mui/material/Typography'
import { styled, useTheme } from '@mui/material/styles'

// ** Type Import
import { LayoutProps } from 'src/@core/layouts/types'

// @ts-ignore
import logo from 'public/images/logo.png'
import { useTranslation } from 'react-i18next'
import { useEffect, useState } from 'react'
import WeatherWidget from '../../weather-widget'

interface Props {
  hidden: LayoutProps['hidden']
  settings: LayoutProps['settings']
  saveSettings: LayoutProps['saveSettings']
  appBarContent: NonNullable<NonNullable<LayoutProps['horizontalLayoutProps']>['appBar']>['content']
  appBarBranding: NonNullable<NonNullable<LayoutProps['horizontalLayoutProps']>['appBar']>['branding']
}

const LinkStyled = styled(Link)(({ theme }) => ({
  display: 'flex',
  gap: '10px',
  textDecoration: 'none',
  marginRight: theme.spacing(8)
}))

const AppBarContent = (props: Props) => {
  const { palette } = useTheme()

  const { t } = useTranslation()

  // ** Props
  const { appBarContent: userAppBarContent, appBarBranding: userAppBarBranding } = props

  const date = new Date()
  const options = {
    weekday: 'short',
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  }
  const formattedDate = date.toLocaleDateString('en-US', options)

  const [currentTime, setCurrentTime] = useState('')

  // Update the time every minute
  useEffect(() => {
    const updateTime = () => {
      const now = new Date()
      const timeString = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) // Exclude seconds
      setCurrentTime(timeString)
    }

    updateTime()
    const interval = setInterval(updateTime, 60000)

    return () => clearInterval(interval)
  }, [])

  return (
    <Box sx={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
      {userAppBarBranding ? (
        userAppBarBranding(props)
      ) : (
        <>
          <LinkStyled href='/'>
            <img src={logo.src} alt='logo' style={{ width: 'auto', height: '100px' }} />

            <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'flex-end', gap: '10px' }}>
              <Typography
                variant='h5'
                sx={{
                  lineHeight: 1,
                  fontWeight: 500,
                  letterSpacing: '-0.45px',
                  fontSize: '1.75rem !important',
                  color: '#ae9e85'
                }}
              >
                {t('header_title')}
              </Typography>

              <Box sx={{ display: 'flex', gap: '15px', pb: '10px' }}>
                <Typography
                  sx={{
                    lineHeight: 1,
                    fontWeight: 500,
                    letterSpacing: '-0.45px',
                    fontSize: '.75rem',
                    color: '#ae9e85'
                  }}
                >
                  {t(formattedDate)}
                </Typography>
                <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
                <Typography
                  sx={{
                    lineHeight: 1,
                    fontWeight: 500,
                    letterSpacing: '-0.45px',
                    fontSize: '.75rem',
                    color: '#ae9e85'
                  }}
                >
                  {t('Shawwal 1, 1445')}
                </Typography>
                <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
                <Typography
                  sx={{
                    lineHeight: 1,
                    fontWeight: 500,
                    letterSpacing: '-0.45px',
                    fontSize: '.75rem',
                    color: '#ae9e85'
                  }}
                >
                  {t(`${currentTime}`)}
                </Typography>
                <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
                <Typography
                  sx={{
                    lineHeight: 1,
                    fontWeight: 500,
                    letterSpacing: '-0.45px',
                    fontSize: '.75rem',
                    color: '#ae9e85'
                  }}
                >
                  {t('Fair prayer in 2hrs 24 mins')}
                </Typography>
              </Box>
            </Box>
          </LinkStyled>

          <Box sx={{ display: 'flex', alignItems: 'center', gap: '40px' }}>
            <Box sx={{ display: 'flex', alignItems: 'center', gap: '20px', textAlign: 'center' }}>
              <WeatherWidget />
            </Box>

            {userAppBarContent ? userAppBarContent(props) : null}
          </Box>
        </>
      )}
    </Box>
  )
}

export default AppBarContent
