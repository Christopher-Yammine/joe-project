import { create } from 'zustand'

interface StreamStore {
  streams: Stream[]
  selectedStreams: Stream[]
  setStreams: (streams: Stream[]) => void
  setSelectedStream: (selectedStream: Stream[]) => void
}

export interface Option {
  label: string
  value: string
}

export interface Stream {
  label: string
  value: string
  options: Option[]
}

const useStore = create<StreamStore>()(set => ({
  streams: [],
  selectedStreams: [],
  setStreams: (index: any) => set(state => ({ streams: index })),
  setSelectedStream: (index: any) => set(state => ({ selectedStreams: index }))
}))

export default useStore
