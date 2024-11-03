// ** MUI Imports
import Box from '@mui/material/Box'

// ** Type Import
import { Settings } from 'src/@core/context/settingsContext'
import LanguageDropdown from 'src/@core/layouts/components/shared-components/LanguageDropdown'

// ** Components
import ModeToggler from 'src/@core/layouts/components/shared-components/ModeToggler'
import NotificationDropdown, {
  NotificationsType
} from 'src/@core/layouts/components/shared-components/NotificationDropdown'
import UserDropdown from 'src/@core/layouts/components/shared-components/UserDropdown'

const notifications: NotificationsType[] = [
  {
    meta: 'Yesterday',
    meta_ar: 'ميتا: سلسلة',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    title_ar: 'مدخل المسجد 2 مخرج مغلق',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 3:13 AM'
  },
  {
    meta: 'Today',
    meta_ar: 'اليوم',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    title_ar: 'مدخل المسجد 2 مخرج مغلق',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 8:00 PM'
  },
  {
    meta: '27 Dec',
    meta_ar: '27 ديسمبر',
    avatarAlt: 'Flora',
    title: 'Mosque Entry 2 Exit turned off',
    title_ar: 'مدخل المسجد 2 مخرج مغلق',
    avatarImg: '/images/avatars/4.png',
    subtitle: 'At 1:07 PM'
  }
]

interface Props {
  settings: Settings
  saveSettings: (values: Settings) => void
}
const AppBarContent = (props: Props) => {
  // ** Props
  const { settings, saveSettings } = props

  return (
    <Box sx={{ display: 'flex', alignItems: 'center' }}>
      <LanguageDropdown settings={settings} saveSettings={saveSettings} />
      <ModeToggler settings={settings} saveSettings={saveSettings} />
      <NotificationDropdown settings={settings} notifications={notifications} />
      <UserDropdown settings={settings} />
    </Box>
  )
}

export default AppBarContent
