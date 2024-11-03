// ** Type import
import { HorizontalNavItemsType } from 'src/@core/layouts/types'

const navigation = (): HorizontalNavItemsType => [
  {
    title: 'Overview',
    path: '/home',
    icon: 'bx:home-circle'
  },
  {
    title: 'Historical',
    path: '/historical',
    icon: 'bx:chart'
  }
]

export default navigation
