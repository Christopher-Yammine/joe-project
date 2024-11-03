import { Box } from '@mui/system'
import { useState } from 'react'

import Select, { OptionProps, components } from 'react-select'
import { useTheme } from '@mui/material/styles'
import { useTranslation } from 'react-i18next'

const CameraSelect = () => {
  const { t } = useTranslation()
  const options = [
    {
      label: t('Mosque'),
      value: 'mosque',
      options: [
        {
          label: t('Mosque Entry 1'),
          value: 'Mosque Entry 1'
        },
        {
          label: t('Mosque Entry 2'),
          value: 'Mosque Entry 2'
        },
        {
          label: t('Mosque Entry 3'),
          value: 'Mosque Entry 3'
        }
      ]
    },
    {
      label: t('Souq'),
      value: 'souq',
      options: [
        {
          label: t('Souq Entry 1'),
          value: 'Souq Entry 1'
        }
      ]
    }
  ]

  const theme = useTheme()

  const [selected, setSelected] = useState([])

  const GroupLabel = (data: any) => {
    const handleClick = () => {
      // @ts-ignore
      const uniqueItems = []
      if (data.options) {
        data.options.forEach((option: any) => {
          // @ts-ignore
          if (!selected.find(item => item.label === option.label)) {
            uniqueItems.push(option)
          }
        })
      }

      setSelected(prevSelected => {
        if (!uniqueItems.length) {
          // @ts-ignore
          return prevSelected.filter(item => !data.options.find(option => option.label === item.label))
        }

        // @ts-ignore
        return prevSelected.concat(uniqueItems)
      })
    }

    return (
      <Box
        onClick={handleClick}
        sx={{
          display: 'flex',
          gap: '10px',
          alignItems: 'center',
          pl: 1,
          backgroundColor: theme.palette.background.paper,
          color: theme.palette.text.primary
        }}
      >
        <svg xmlns='http://www.w3.org/2000/svg' height={'10px'} viewBox='0 0 512 512'>
          <path d='M0 96C0 60.7 28.7 32 64 32H196.1c19.1 0 37.4 7.6 50.9 21.1L289.9 96H448c35.3 0 64 28.7 64 64V416c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V96zM64 80c-8.8 0-16 7.2-16 16V416c0 8.8 7.2 16 16 16H448c8.8 0 16-7.2 16-16V160c0-8.8-7.2-16-16-16H286.6c-10.6 0-20.8-4.2-28.3-11.7L213.1 87c-4.5-4.5-10.6-7-17-7H64z' />
        </svg>
        <span>{data.label}</span>
      </Box>
    )
  }

  const Option = (props: OptionProps) => {
    const {
      // @ts-ignore
      data: { label }
    } = props

    return (
      <components.Option {...props}>
        <Box
          sx={{
            display: 'flex',
            alignItems: 'center',
            gap: '5px',
            pl: 5
          }}
        >
          <svg xmlns='http://www.w3.org/2000/svg' width='15px' height='15px' viewBox='0 0 48 48'>
            <g fill='none' stroke='currentColor' stroke-linecap='round' stroke-linejoin='round' stroke-width='4'>
              <path d='M19.006 26.276V37H5m37.62-15.785l-3.863-1.035l-4.003 7.21l5.796 1.553z' />
              <path d='m38.757 20.18l-4.003 7.21l-1.742 2.639L5 22.523L8.623 9L40.5 17.541z' />
            </g>
          </svg>
          {label}
        </Box>
      </components.Option>
    )
  }

  const CustomValueContainer = ({ children, ...props }: any) => {
    const selectedOptions = props.selectProps.value || []
    const groupedOptions = options.reduce((acc, group) => {
      const groupOptions = group.options.map(option => option.value)
      const groupSelected = groupOptions.every(optionValue =>
        selectedOptions.some((selectedOption: any) => selectedOption.value === optionValue)
      )
      if (groupSelected) {
        acc.push(group.label as never)
      } else {
        const selectedGroupOptions = selectedOptions.filter((option: any) => groupOptions.includes(option.value))
        acc = acc.concat(selectedGroupOptions.map((option: any) => option.label))
      }

      return acc
    }, [])

    return (
      <components.ValueContainer {...props}>
        {groupedOptions.length > 0 ? <div style={{ width: '200px' }}>{groupedOptions.join(', ')}</div> : children}
      </components.ValueContainer>
    )
  }

  return (
    <Box
      style={{
        minWidth: '200px',
        backgroundColor: theme.palette.background.paper,
        color: theme.palette.text.primary
      }}
    >
      <Select
        onChange={option => {
          // @ts-ignore
          return setSelected(option)
        }}
        closeMenuOnSelect={false}
        isMulti
        components={{ Option, ValueContainer: CustomValueContainer }}
        hideSelectedOptions={false}
        formatGroupLabel={GroupLabel}
        options={options}
        value={selected}
        maxMenuHeight={500}
        styles={{
          control: (baseStyles, state) => ({
            display: 'flex',
            borderRadius: '4px',
            border: `1px solid ${state.isFocused ? theme.palette.divider : theme.palette.divider}`,
            backgroundColor: theme.palette.background.paper
          }),
          placeholder: baseStyles => ({
            ...baseStyles,
            color: theme.palette.text.primary
          }),
          menu: baseStyles => ({
            ...baseStyles,
            backgroundColor: theme.palette.background.paper
          }),
          option: (baseStyles, state) => ({
            ...baseStyles,
            backgroundColor: state.isFocused
              ? `rgba(${theme.palette.customColors.main}, 0.03)`
              : theme.palette.background.paper,
            cursor: 'pointer'
          })
        }}
        placeholder={t('Select')}
      />
    </Box>
  )
}
export default CameraSelect
