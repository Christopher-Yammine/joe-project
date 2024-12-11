import { Box } from '@mui/system'
import { useState, useRef, useEffect } from 'react'
import Select from 'react-select'
import { useTheme } from '@mui/material/styles'
import { useTranslation } from 'react-i18next'
import useStore from 'src/store/store'

const CustomMultiValue = (props: any) => {
  const { value } = props.selectProps
  const isLast = props.index === value.length - 1

  if (!isLast) {
    return null
  }

  const allLabels = value.map((v: any) => v.label).join(', ')
  const maxLength = 20
  const finalText = allLabels.length > maxLength ? allLabels.slice(0, maxLength) + '...' : allLabels

  return <>{finalText}</>
}

type MyOption = {
  label: string
  value: string
}

// No need to specify generics at first, let's keep it simple and correct the types step-by-step
const CameraSelect = () => {
  const { t } = useTranslation()
  const theme = useTheme()

  const streams = useStore(state => state.streams)
  const setSelectedStreams = useStore(state => state.setSelectedStream)

  const [selected, setSelected] = useState<MyOption[] | any>([])
  const [menuIsOpen, setMenuIsOpen] = useState(false)
  const [lastIds, setLastIds] = useState<string[]>([])

  const containerRef = useRef<HTMLDivElement | null>(null)

  const options = (streams ?? []).map(stream => ({
    label: t(stream.label) ?? '',
    options: stream.options.map(entry => ({
      label: t(entry.label) ?? '',
      value: entry.value
    })) as MyOption[]
  }))

  const handleMenuClose = () => {
    const ids = selected.flatMap((item: any) => (item.options ? item.options.map((opt: any) => opt.value) : item.value))

    const idsHaveChanged = ids.length !== lastIds.length || ids.some((id: any, index: any) => id !== lastIds[index])

    if (idsHaveChanged) {
      setSelectedStreams(ids)
      setLastIds(ids)
    }

    setMenuIsOpen(false)
  }

  const handleMenuOpen = () => {
    setMenuIsOpen(true)
  }

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (!menuIsOpen) return
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setMenuIsOpen(false)
      }
    }

    document.addEventListener('mousedown', handleClickOutside)

    return () => {
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [menuIsOpen])

  return (
    <Box
      ref={containerRef}
      style={{
        minWidth: '200px',
        backgroundColor: theme.palette.background.paper,
        color: theme.palette.text.primary
      }}
    >
      <Select<MyOption, true>
        onFocus={() => {
          if (!menuIsOpen) setMenuIsOpen(true)
        }}
        onChange={(option: any) => setSelected(option ?? [])}
        onMenuClose={handleMenuClose}
        onMenuOpen={handleMenuOpen}
        menuIsOpen={menuIsOpen}
        closeMenuOnSelect={false}
        isMulti
        hideSelectedOptions={false}
        options={options}
        value={selected}
        components={{
          MultiValue: CustomMultiValue
        }}
        maxMenuHeight={500}
        styles={{
          control: base => ({
            ...base,
            display: 'flex',
            borderRadius: '4px',
            border: `1px solid ${theme.palette.divider}`,
            backgroundColor: theme.palette.background.paper,
            minHeight: '40px'
          }),
          placeholder: base => ({
            ...base,
            color: theme.palette.text.primary,
            whiteSpace: 'nowrap'
          }),
          menu: base => ({
            ...base,
            backgroundColor: theme.palette.background.paper
          }),
          option: (base, state) => ({
            ...base,
            backgroundColor: state.isFocused
              ? `rgba(${theme.palette.customColors.main}, 0.03)`
              : theme.palette.background.paper,
            cursor: 'pointer'
          }),
          valueContainer: base => ({
            ...base,
            display: 'flex',
            flexWrap: 'nowrap',
            overflow: 'hidden',
            whiteSpace: 'nowrap',
            textOverflow: 'ellipsis',
            maxWidth: '250px'
          }),
          multiValue: base => ({
            ...base,
            padding: 0,
            margin: 0,
            backgroundColor: 'transparent'
          }),
          multiValueLabel: base => ({
            ...base,
            padding: 0,
            margin: 0,
            display: 'inline',
            whiteSpace: 'nowrap'
          }),
          multiValueRemove: base => ({
            ...base,
            display: 'none'
          })
        }}
        placeholder={t('Select') ?? ''}
      />
    </Box>
  )
}

export default CameraSelect
