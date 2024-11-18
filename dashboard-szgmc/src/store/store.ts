import { StreamStore } from 'src/configs/types'
import { create } from 'zustand'

const now = new Date()
const oneWeekAgo = new Date()
oneWeekAgo.setDate(now.getDate() - 7)

/* eslint-disable */
const useStore = create<StreamStore>()(set => ({
  streams: [],
  selectedStreams: [],
  setStreams: (index: any) => set(_state => ({ streams: index })),
  setSelectedStream: (index: any) => set(state => ({ selectedStreams: index })),
  fromDate: oneWeekAgo,
  setFromDate: (index: any) => set(state => ({ fromDate: index })),
  toDate: new Date(),
  setToDate: (index: any) => set(state => ({ toDate: index })),
  durationSelect: 'Daily',
  setDurationSelect: (index: any) => set(state => ({ durationSelect: index }))
}))
/* eslint-disable */

export default useStore
