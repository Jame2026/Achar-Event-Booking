import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import LegacyAppShell from '../LegacyAppShell.vue'
import GuestPreview from '../components/GuestPreview.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/home',
    redirect: '/'
  },
  {
    path: '/booking',
    name: 'BookingForm',
    component: GuestPreview,
    props: { section: 'bookings' }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: GuestPreview,
    props: { section: 'dashboard' }
  },
  {
    path: '/services',
    name: 'Services',
    component: GuestPreview,
    props: { section: 'services' }
  },
  {
    path: '/legacy-app',
    name: 'LegacyApp',
    component: LegacyAppShell
  },
  {
    path: '/vendor',
    redirect: '/legacy-app?page=vendor'
  },
  {
    path: '/customization',
    name: 'Customization',
    component: GuestPreview,
    props: { section: 'customization' }
  },
  {
    path: '/availability',
    redirect: '/legacy-app?page=availability'
  },
  {
    path: '/my-bookings',
    redirect: '/legacy-app?page=bookings'
  },
  {
    path: '/messages',
    redirect: '/legacy-app?page=messages'
  },
  {
    path: '/profile',
    redirect: '/legacy-app?page=profile'
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
