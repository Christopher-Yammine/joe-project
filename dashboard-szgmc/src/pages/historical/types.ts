export type ChartSeries = {
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
  commonChartSeries: ChartSeries[]
}

export type StaffChartSeriesData = {
  name: string
  name_ar: string
  data: number[]
}[]

export type StaffChartHistorical = {
  staffChartSeries: StaffChartSeriesData
  xAxis: string[]
}
