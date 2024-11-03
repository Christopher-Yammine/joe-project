// ** Next Import
import Link from 'next/link'

// ** MUI Imports
import IconButton from '@mui/material/IconButton'
import Typography from '@mui/material/Typography'
import Box, { BoxProps } from '@mui/material/Box'
import { styled, useTheme } from '@mui/material/styles'

// ** Type Import
import { LayoutProps } from 'src/@core/layouts/types'

// ** Custom Icon Import
import Icon from 'src/@core/components/icon'

// ** Configs
// import themeConfig from 'src/configs/themeConfig'
import { useTranslation } from 'react-i18next'

// @ts-ignore
import logo from 'public/images/logo.png'

interface Props {
  navHover: boolean
  collapsedNavWidth: number
  hidden: LayoutProps['hidden']
  navigationBorderWidth: number
  toggleNavVisibility: () => void
  settings: LayoutProps['settings']
  saveSettings: LayoutProps['saveSettings']
  navMenuBranding?: LayoutProps['verticalLayoutProps']['navMenu']['branding']
  menuLockedIcon?: LayoutProps['verticalLayoutProps']['navMenu']['lockedIcon']
  menuUnlockedIcon?: LayoutProps['verticalLayoutProps']['navMenu']['unlockedIcon']
}

// ** Styled Components
const MenuHeaderWrapper = styled(Box)<BoxProps>(({ theme }) => ({
  display: 'flex',
  overflow: 'hidden',
  alignItems: 'center',
  marginTop: theme.spacing(3),
  paddingRight: theme.spacing(5),
  justifyContent: 'space-between',
  transition: 'padding .25s ease-in-out',
  minHeight: theme.mixins.toolbar.minHeight
}))

const LinkStyled = styled(Link)({
  display: 'flex',
  alignItems: 'center',
  textDecoration: 'none'
})

const VerticalNavHeader = (props: Props) => {
  // ** Props
  const {
    hidden,
    navHover,
    settings,
    saveSettings,
    collapsedNavWidth,
    toggleNavVisibility,
    navigationBorderWidth,
    menuLockedIcon: userMenuLockedIcon,
    navMenuBranding: userNavMenuBranding,
    menuUnlockedIcon: userMenuUnlockedIcon
  } = props

  // ** Hooks & Vars
  const theme = useTheme()
  const { skin, direction, navCollapsed } = settings

  // const menuCollapsedStyles = navCollapsed && !navHover ? { opacity: 0 } : { opacity: 1 }

  const { t } = useTranslation()

  const handleButtonClick = () => {
    if (hidden) {
      toggleNavVisibility()
    } else {
      saveSettings({ ...settings, navCollapsed: !navCollapsed })
    }
  }

  const menuHeaderPaddingLeft = () => {
    if (navCollapsed && !navHover) {
      if (userNavMenuBranding) {
        return 0
      } else {
        return (collapsedNavWidth - navigationBorderWidth - 22) / 8
      }
    } else {
      return 8
    }
  }

  const svgRotationDeg = () => {
    if (navCollapsed) {
      if (direction === 'rtl') {
        if (navHover) {
          return 0
        } else {
          return 180
        }
      } else {
        if (navHover) {
          return 180
        } else {
          return 0
        }
      }
    } else {
      if (direction === 'rtl') {
        return 180
      } else {
        return 0
      }
    }
  }

  return (
    <MenuHeaderWrapper className='nav-header' sx={{ pl: menuHeaderPaddingLeft() }}>
      {userNavMenuBranding ? (
        userNavMenuBranding(props)
      ) : (
        <LinkStyled href='/' sx={{ display: 'flex', flexDirection: 'column', width: '100%', alignItems: 'center' }}>
          <img src={logo.src} alt='logo' style={{ width: 'auto', height: '100px' }} />

          <Box sx={{ display: 'flex', flexDirection: 'column', justifyContent: 'flex-end', gap: '10px' }}>
            <Typography
              variant='h5'
              sx={{
                lineHeight: 1,
                fontWeight: 500,
                letterSpacing: '-0.45px',
                fontSize: '1.25rem !important',
                color: '#ae9e85',
                textAlign: 'center',
                mt: 2
              }}
            >
              {t('header_title')}
            </Typography>

            <Box sx={{ display: 'flex', flexDirection: 'column', gap: '4px', pb: '10px' }}>
              <Typography
                sx={{
                  lineHeight: 1,
                  fontWeight: 500,
                  letterSpacing: '-0.45px',
                  fontSize: '.75rem',
                  color: '#ae9e85',
                  textAlign: 'center'
                }}
              >
                Wed, 10, Apr 2024
              </Typography>
              <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
              <Typography
                sx={{
                  lineHeight: 1,
                  fontWeight: 500,
                  letterSpacing: '-0.45px',
                  fontSize: '.75rem',
                  color: '#ae9e85',
                  textAlign: 'center'
                }}
              >
                Shawwal 1, 1445
              </Typography>
              <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
              <Typography
                sx={{
                  lineHeight: 1,
                  fontWeight: 500,
                  letterSpacing: '-0.45px',
                  fontSize: '.75rem',
                  color: '#ae9e85',
                  textAlign: 'center'
                }}
              >
                Time: 02:23 AM
              </Typography>
              <Box sx={{ width: '1px', height: '100%', bgcolor: '#ae9e85' }} />
              <Typography
                sx={{
                  lineHeight: 1,
                  fontWeight: 500,
                  letterSpacing: '-0.45px',
                  fontSize: '.75rem',
                  color: '#ae9e85',
                  textAlign: 'center'
                }}
              >
                Fair prayer in 2hrs 24 mins
              </Typography>
            </Box>
          </Box>
        </LinkStyled>
      )}

      {userMenuLockedIcon === null && userMenuUnlockedIcon === null ? null : (
        <IconButton
          disableRipple
          disableFocusRipple
          onClick={handleButtonClick}
          sx={{
            p: 1.75,
            right: -19,
            position: 'absolute',
            color: 'text.primary',
            '& svg': { color: 'common.white' },
            transition: 'right .25s ease-in-out',
            backgroundColor: hidden ? 'background.paper' : 'customColors.collapseTogglerBg',
            ...(navCollapsed && !navHover && { display: 'none' }),
            ...(!hidden &&
              skin === 'bordered' && {
                '&:before': {
                  zIndex: -1,
                  content: '""',
                  width: '105%',
                  height: '105%',
                  borderRadius: '50%',
                  position: 'absolute',
                  border: `1px solid ${theme.palette.divider}`,
                  clipPath: direction === 'rtl' ? 'circle(71% at 100% 50%)' : 'circle(71% at 0% 50%)'
                }
              })
          }}
        >
          <Box sx={{ display: 'flex', borderRadius: 5, backgroundColor: 'primary.main' }}>
            {userMenuLockedIcon && userMenuUnlockedIcon ? (
              navCollapsed ? (
                userMenuUnlockedIcon
              ) : (
                userMenuLockedIcon
              )
            ) : (
              <Box
                sx={{
                  display: 'flex',
                  '& svg': {
                    transform: `rotate(${svgRotationDeg()}deg)`,
                    transition: 'transform .25s ease-in-out .35s'
                  }
                }}
              >
                <Icon icon='bx:chevron-left' />
              </Box>
            )}
          </Box>
        </IconButton>
      )}
    </MenuHeaderWrapper>
  )
}

export default VerticalNavHeader
