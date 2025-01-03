// ** React Import
import { useEffect } from 'react'

// ** Icon Imports
import Icon from 'src/@core/components/icon'

// ** Third Party Import
import { useTranslation } from 'react-i18next'

// ** Custom Components Imports
import OptionsMenu from 'src/@core/components/option-menu'

// ** Type Import
import { Settings } from 'src/@core/context/settingsContext'
import generalConfig from 'src/configs/general.config.json'

interface Props {
  settings: Settings
  saveSettings: (values: Settings) => void
}

const LanguageDropdown = ({ settings, saveSettings }: Props) => {
  // ** Hook
  const { i18n } = useTranslation()

  // ** Vars
  const { layout } = settings

  const handleLangItemClick = (lang: 'en' | 'ar') => {
    i18n.changeLanguage(lang)
  }

  // ** Change html `lang` attribute when changing locale
  useEffect(() => {
    document.documentElement.setAttribute('lang', i18n.language)
  }, [i18n.language])

  const options = (
    generalConfig?.Languages && generalConfig.Languages.length > 0 ? generalConfig.Languages : ['English', 'Arabic']
  )
    .map(language => {
      if (language === 'English') {
        return {
          text: 'English',
          menuItemProps: {
            sx: { py: 2 },
            selected: i18n.language === 'en',
            onClick: () => {
              handleLangItemClick('en')
              saveSettings({ ...settings, direction: 'ltr', language: 'en' })
            }
          }
        }
      } else if (language === 'Arabic') {
        return {
          text: 'Arabic',
          menuItemProps: {
            sx: { py: 2 },
            selected: i18n.language === 'ar',
            onClick: () => {
              handleLangItemClick('ar')
              saveSettings({ ...settings, direction: 'rtl', language: 'ar' })
            }
          }
        }
      }

      return null
    })
    .filter((option): option is Exclude<typeof option, null> => option !== null) // Explicit type narrowing

  return (
    <OptionsMenu
      icon={<Icon icon='mdi:translate' />}
      iconButtonProps={{ color: 'inherit', sx: { ...(layout === 'vertical' ? { mr: 0.75 } : { mx: 0.75 }) } }}
      menuProps={{
        sx: {
          '& .MuiMenu-paper': { mt: 4, minWidth: 130 },
          '& .MuiMenuItem-root:not(.Mui-selected)': { color: 'text.secondary' }
        }
      }}
      options={options}
    />
  )
}

export default LanguageDropdown
