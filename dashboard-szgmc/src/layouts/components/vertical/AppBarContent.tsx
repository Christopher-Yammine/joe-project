// ** MUI Imports
import { styled } from '@mui/material/styles'
import Box, { BoxProps } from '@mui/material/Box'
import IconButton from '@mui/material/IconButton'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Type Import
import { Settings } from 'src/@core/context/settingsContext'
import CameraSelect from 'src/@core/layouts/components/shared-components/CameraSelect'
import DatePickerRange from 'src/@core/layouts/components/shared-components/DateRangePicker'
import { DurationSelect } from 'src/@core/layouts/components/shared-components/DurationSelect'
import LanguageDropdown from 'src/@core/layouts/components/shared-components/LanguageDropdown'

// ** Components
import ModeToggler from 'src/@core/layouts/components/shared-components/ModeToggler'
import NotificationDropdown, {
  NotificationsType
} from 'src/@core/layouts/components/shared-components/NotificationDropdown'
import UserDropdown from 'src/@core/layouts/components/shared-components/UserDropdown'

interface Props {
  hidden: boolean
  settings: Settings
  toggleNavVisibility: () => void
  saveSettings: (values: Settings) => void
}

const SelectWrapper = styled(Box)<BoxProps>(({ theme }) => ({
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column',
    alignItems: 'stretch'
  }
}))

// TODO: Refactor this notifications
const notifications = [
  {
    meta: 'Yesterday',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 3:13 AM'
  },
  {
    meta: 'Today',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 8:00 PM'
  },
  {
    meta: '27 Dec',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 1:07 PM'
  }
]

const AppBarContent = (props: Props) => {
  // ** Props
  const { hidden, settings, saveSettings, toggleNavVisibility } = props

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', width: '100%', zIndex: '9999999' }}>
      <Box sx={{ width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between', mb: 4 }}>
        <Box className='actions-left' sx={{ mr: 2, display: 'flex', alignItems: 'center' }}>
          {hidden ? (
            <IconButton color='inherit' sx={{ ml: -2.75 }} onClick={toggleNavVisibility}>
              <Icon icon='bx:menu' />
            </IconButton>
          ) : null}
        </Box>
        <Box className='actions-right' sx={{ display: 'flex', alignItems: 'center' }}>
          <LanguageDropdown settings={settings} saveSettings={saveSettings} />
          <NotificationDropdown settings={settings} notifications={notifications as NotificationsType[]} />
          <ModeToggler settings={settings} saveSettings={saveSettings} />
          <UserDropdown settings={settings} />
        </Box>
      </Box>

      <Box sx={{ width: '100%', height: '0.5px', backgroundColor: '#cacccf' }} />

      <SelectWrapper
        sx={{
          display: 'flex',
          gap: 6,
          alignItems: 'center',
          flexWrap: 'wrap',
          py: 4,
          borderTop: '1px solid #cacccf'
        }}
      >
        <DurationSelect />
        <DatePickerRange />
        <CameraSelect />
      </SelectWrapper>
    </Box>
  )
}

export default AppBarContent
