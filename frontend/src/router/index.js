import { createRouter, createWebHistory } from 'vue-router'
import BookingForm from '../components/BookingForm.vue'

const routes = [
  {
    path: '/',
    name: 'BookingForm',
    component: BookingForm
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../components/Dashboard.vue')
  },
  {
    path: '/services',
    name: 'Services',
    component: () => import('../components/Services.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
