// ** Types
import { NavLink, NavGroup, HorizontalNavItemsType, NavTraslationType } from 'src/@core/layouts/types'

// ** Custom Navigation Components
import HorizontalNavLink from './HorizontalNavLink'
import HorizontalNavGroup from './HorizontalNavGroup'
import { useTranslation } from 'react-i18next'

interface Props {
  hasParent?: boolean
  horizontalNavItems?: HorizontalNavItemsType
}
const resolveComponent = (item: NavGroup | NavLink) => {
  if ((item as NavGroup).children) return HorizontalNavGroup

  return HorizontalNavLink
}

const HorizontalNavItems = (props: Props) => {
  const { t } = useTranslation()
  const RenderMenuItems = props.horizontalNavItems?.map((item: NavGroup | NavLink, index: number) => {
    const translatedItem = { ...item, title: t(item.path as NavTraslationType) }
    const TagName: any = resolveComponent(translatedItem)

    return <TagName {...props} key={index} item={translatedItem} />
  })

  return <>{RenderMenuItems}</>
}

export default HorizontalNavItems
