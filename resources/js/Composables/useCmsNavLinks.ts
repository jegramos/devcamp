import { ref } from 'vue'
import type { Page } from '@inertiajs/core'
import type { SharedPage } from '@/Types/shared-page.ts'

export type NavLink = {
  name: string
  uri: string
  icon: string
}

export type NavItem = {
  group: string
  links: NavLink[]
}

/**
 * @description The central store of nav links for the different components in the app.
 */
export const useCmsNavLinks = function (page: Page<SharedPage>) {
  const navItems = ref<NavItem[]>([
    {
      group: 'Portfolio',
      links: [
        { name: 'Resume', uri: page.props.pageUris['portfolio.resume'], icon: 'pi pi-briefcase' },
        { name: 'Blogs', uri: '/blogs', icon: 'pi pi-book' },
        { name: 'Calendar', uri: '/calendar', icon: 'pi pi-calendar' },
      ],
    },
    {
      group: 'Account',
      links: [
        { name: 'Profile', uri: page.props.pageUris['account.profile'], icon: 'pi pi-user' },
        { name: 'Settings', uri: page.props.pageUris['account.settings'], icon: 'pi pi-cog' },
      ],
    },
    {
      group: 'Admin',
      links: [{ name: 'User Management', uri: page.props.pageUris['admin.userManagement'], icon: 'pi pi-users' }],
    },
    {
      group: 'Misc',
      links: [{ name: 'About', uri: page.props.pageUris['misc.about'], icon: 'pi pi-info-circle' }],
    },
  ])

  if (!page.props.auth.can.view_users) {
    navItems.value = navItems.value.filter((item: NavItem) => item.group !== 'Admin')
  }

  return {
    navItems,
  }
}
