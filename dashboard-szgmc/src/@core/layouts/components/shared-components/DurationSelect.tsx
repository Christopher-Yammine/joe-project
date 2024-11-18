import { useRouter } from 'next/router'
import React, { Fragment, SyntheticEvent, useState } from 'react'
import { useSettings } from 'src/@core/hooks/useSettings'

import Box from '@mui/material/Box'
import Menu from '@mui/material/Menu'
import Divider from '@mui/material/Divider'
import MenuItem from '@mui/material/MenuItem'

import { useTheme } from '@mui/material/styles'

// ** Icon Imports
import { Badge } from '@mui/material'
import { t } from 'i18next'
import useStore from 'src/store/store'

export const DurationSelect = () => {
  const { settings } = useSettings()

  const isAR = settings.language === 'ar'

  const theme = useTheme()

  // ** States
  const [anchorEl, setAnchorEl] = useState<Element | null>(null)

  const [isOpen, setIsOpen] = useState<boolean>(false)

  // ** Hooks
  const router = useRouter()

  // ** Vars
  const { direction } = settings

  const handleDropdownOpen = (event: SyntheticEvent) => {
    if (!anchorEl) {
      setAnchorEl(event.currentTarget)
    }

    setIsOpen(true)
  }

  const handleDropdownClose = (url?: string) => {
    if (url) {
      router.push(url)
    }

    setAnchorEl(null)

    setIsOpen(false)
  }

  const [value, setValue] = useState(t('Daily'))
  const setDurationSelect = useStore(state => state.setDurationSelect)

  if (window.location.pathname.includes('/home')) return null

  const styles = {
    py: 2,
    px: 4,
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    color: 'text.secondary',
    textDecoration: 'none',
    '& svg': {
      mr: 2,
      fontSize: '1.25rem',
      color: 'text.secondary'
    }
  }

  return (
    <Fragment>
      <Badge
        overlap='circular'
        onClick={handleDropdownOpen}
        sx={{ cursor: 'pointer' }}
        anchorOrigin={{
          vertical: 'bottom',
          horizontal: 'right'
        }}
      >
        <button
          className='custom-button'
          style={{
            backgroundColor: theme.palette.background.paper,
            color: theme.palette.text.primary,
            borderColor: theme.palette.divider
          }}
        >
          <span className='button-text'>{t(value ?? '')}</span>
          <span className={isAR ? 'button-arrow-rtl' : 'button-arrow'}></span>
        </button>
      </Badge>
      <Menu
        anchorEl={anchorEl}
        open={isOpen}
        onClose={() => handleDropdownClose()}
        sx={{ '& .MuiMenu-paper': { width: 230, mt: 4 } }}
        anchorOrigin={{ vertical: 'bottom', horizontal: direction === 'ltr' ? 'right' : 'left' }}
        transformOrigin={{ vertical: 'top', horizontal: direction === 'ltr' ? 'right' : 'left' }}
      >
        <MenuItem
          sx={{ p: 0 }}
          onClick={() => {
            setValue('Daily')
            setDurationSelect('Daily')
            handleDropdownClose()
          }}
        >
          <Box sx={{ ...styles, color: theme.palette.customColors.main }}>{t('Daily')}</Box>
        </MenuItem>
        <MenuItem
          sx={{ p: 0 }}
          onClick={() => {
            setValue('Weekly')
            setDurationSelect('Weekly')
            handleDropdownClose()
          }}
        >
          <Box sx={{ ...styles, color: theme.palette.customColors.main }}>{t('Weekly')}</Box>
        </MenuItem>
        <MenuItem
          sx={{ p: 0 }}
          onClick={() => {
            setValue('Monthly')
            setDurationSelect('Monthly')
            handleDropdownClose()
          }}
        >
          <Box sx={{ ...styles, color: theme.palette.customColors.main }}>{t('Monthly')}</Box>
        </MenuItem>
        <MenuItem
          sx={{ p: 0 }}
          onClick={() => {
            setValue('Yearly')
            setDurationSelect('Yearly')
            handleDropdownClose()
          }}
        >
          <Box sx={{ ...styles, color: theme.palette.customColors.main }}>{t('Yearly')}</Box>
        </MenuItem>
        <Divider />
      </Menu>
    </Fragment>
  )
}
