// ** Type import
import { VerticalNavItemsType } from 'src/@core/layouts/types'

const navigation = (): VerticalNavItemsType => {
  return [
    {
      title: 'overview',
      path: '/home',
      icon: 'bx:home-circle'
    },
    {
      title: 'historical',
      path: '/historical',
      icon: 'bx:chart'
    }
  ]
}

export default navigation
