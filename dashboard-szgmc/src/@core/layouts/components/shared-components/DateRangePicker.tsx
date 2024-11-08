'use client'

import React, { useEffect, useState } from 'react'
import DateRangePicker from 'react-bootstrap-daterangepicker'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-daterangepicker/daterangepicker.css'
import moment from 'moment'
import { useTheme } from '@mui/material/styles'
import { useSettings } from 'src/@core/hooks/useSettings'
import useStore from 'src/store/store'

export default function DatePickerRange() {
  const [fromDate, setFromDate] = useState(new Date())
  const [toDate, setToDate] = useState(new Date())
  const setFromDateStore = useStore(state => state.setFromDate)
  const setToDateStore = useStore(state => state.setToDate)

  const { settings } = useSettings()

  const theme = useTheme()

  const isAR = settings.language === 'ar'

  const range = {
    Today: [moment(), moment()],
    Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    'This Month': [moment().startOf('month'), moment().endOf('month')],
    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
  }

  const rangeRtl = {
    اليوم: [moment(), moment()],
    أمس: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'آخر 7 أيام': [moment().subtract(6, 'days'), moment()],
    'آخر 30 يوما': [moment().subtract(29, 'days'), moment()],
    'هذا الشهر': [moment().startOf('month'), moment().endOf('month')],
    'الشهر الماضي': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    'العام الماضي': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
  }

  useEffect(() => {
    const rangePickerClassList = document.querySelector('.daterangepicker')?.classList

    const isDarkModeApplied = rangePickerClassList?.contains('dark-mode')

    if ((isDarkModeApplied && settings.mode !== 'dark') || (!isDarkModeApplied && settings.mode === 'dark')) {
      rangePickerClassList?.toggle('dark-mode')
    }
  }, [settings.mode, settings?.language, window.location.pathname])

  // @ts-ignore
  const handleEvent = (event, picker: any) => {
    setFromDate(picker.startDate?._d)
    setToDate(picker.endDate?._d)
    setFromDateStore(picker.startDate?._d)
    setToDateStore(picker.endDate?._d)
  }

  if (window.location.pathname.includes('/home')) return null

  return (
    <DateRangePicker
      onEvent={handleEvent}
      initialSettings={{
        ranges: isAR ? rangeRtl : range,
        startDate: new Date(),
        endDate: new Date(),
        alwaysShowCalendars: true,
        locale: 'ar'
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
        <span className={isAR ? 'button-text-rtl' : 'button-text'}>
          {moment(fromDate).format('LL')} to {moment(toDate).format('LL')}
        </span>
        <span className={isAR ? 'button-arrow-rtl' : 'button-arrow'}></span>
      </button>
    </DateRangePicker>
  )
}
