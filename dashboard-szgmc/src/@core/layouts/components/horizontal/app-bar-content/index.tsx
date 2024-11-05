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
                  {t(`Time: ${currentTime}`)}
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
              <Box>
                <Typography sx={{ fontSize: '.75rem', fontWeight: '700', lineHeight: '1' }}>
                  {t('ABU DHABI')}
                </Typography>
                <Typography sx={{ fontSize: 'inherit' }}>{t('Weather')}</Typography>
              </Box>
              <Box sx={{ width: '100px', height: '61px' }}>
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  xmlnsXlink='http://www.w3.org/1999/xlink'
                  fill={palette.action.active}
                  height='100%'
                  width='100%'
                  version='1.1'
                  id='Layer_1'
                  viewBox='0 0 512.001 512.001'
                  xmlSpace='preserve'
                >
                  <g>
                    <g>
                      <path d='M344.381,143.771C254.765,56.017,102.37,103.776,79.825,227.7c-31.849,4.598-59.138,25.445-72.018,55.076    c-0.016,0.035-0.032,0.07-0.047,0.107c-26.687,61.602,18.784,130.232,85.51,130.232h282.267    c75.246,0,136.463-61.216,136.463-136.462C512,189.241,430.314,123.682,344.381,143.771z M375.537,381.12H93.271    c-69.246,0-84.534-98.263-18.714-119.456c14.753-4.65,43.01-7.348,74.38,21.892c6.464,6.024,16.586,5.667,22.61-0.794    c6.024-6.464,5.668-16.586-0.794-22.61c-17.93-16.712-38.071-27.33-58.484-31.453c22.034-99.077,147.374-131.851,215.247-56.305    c4.189,4.661,10.714,6.451,16.693,4.57c67.272-21.117,135.795,29.374,135.795,99.69    C480.005,334.256,433.141,381.12,375.537,381.12z' />
                    </g>
                  </g>
                </svg>
              </Box>
              <Box>
                <Typography sx={{ fontSize: '2rem', lineHeight: '1' }}>27&#8451;</Typography>
                <Typography sx={{ fontSize: '.75rem' }}>{t('light rain')}</Typography>
              </Box>
            </Box>

            {userAppBarContent ? userAppBarContent(props) : null}
          </Box>
        </>
      )}
    </Box>
  )
}

export default AppBarContent
