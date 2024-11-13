import { create } from 'zustand'

type StreamStore = {
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

const now = new Date()
const oneWeekAgo = new Date()
oneWeekAgo.setDate(now.getDate() - 7)

const useStore = create<StreamStore>()(set => ({
  streams: [],
  selectedStreams: [],
  setStreams: (index: any) => set(state => ({ streams: index })),
  setSelectedStream: (index: any) => set(state => ({ selectedStreams: index })),
  fromDate: oneWeekAgo,
  setFromDate: (index: any) => set(state => ({ fromDate: index })),
  toDate: new Date(),
  setToDate: (index: any) => set(state => ({ toDate: index })),
  durationSelect: 'Daily',
  setDurationSelect: (index: any) => set(state => ({ durationSelect: index }))
}))

export default useStore
