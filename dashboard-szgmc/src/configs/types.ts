export type ChartSeriesData = {
  name: string
  name_ar: string
  data: number[]
}

export type chartData = {
  firstTitle: string
  firstGeneralNumber: string
  firstTrendNumber: string
  secondTitle: string
  secondGeneralNumber: string
  secondTrendNumber: string
  xAxis: string[]
  commonChartSeries: ChartSeriesData[]
}

export type StaffChartSeriesData = {
  name: string
  name_ar: string
  data: number[]
}[]

export type StaffChartHistoricalData = {
  staffChartSeries: StaffChartSeriesData
  xAxis: string[]
}

export type StreamStore = {
  streams: Stream[]
  selectedStreams: Stream[]
  setStreams: (streams: Stream[]) => void
  setSelectedStream: (selectedStream: Stream[]) => void
  fromDate: Date | null
  setFromDate: (date: Date) => void
  toDate: Date | null
  setToDate: (date: Date) => void
  durationSelect: string
  setDurationSelect: (duration: string) => void
}

export type Option = {
  label: string
  value: string
}

export type Stream = {
  label: string
  value: string
  options: Option[]
}
