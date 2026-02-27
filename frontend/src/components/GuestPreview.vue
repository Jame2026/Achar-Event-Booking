<script setup>
import { computed, ref } from 'vue'
import DashboardPage from './pages/DashboardPage.vue'
import VendorPage from './pages/VendorPage.vue'
import CustomizationPage from './pages/CustomizationPage.vue'
import BookingsPage from './pages/BookingsPage.vue'
import { eventTypeMap, eventTypeOptions, reviews, serviceFeeRate, stats, vendorProfile } from '../features/appData'
import { useRouter } from 'vue-router'

const props = defineProps({
  section: {
    type: String,
    default: 'dashboard',
  },
})
const section = computed(() => props.section)

const pageContent = computed(() => {
  if (props.section === 'bookings') {
    return {
      title: 'My Booking',
      subtitle: 'No booking data yet.',
      text: 'Sign in to view your booking history, upcoming events, and confirmations.',
    }
  }

  if (props.section === 'services') {
    return {
      title: 'Services',
      subtitle: 'No service data yet.',
      text: 'Sign in to browse full service listings, packages, and availability.',
    }
  }

  if (props.section === 'customization') {
    return {
      title: 'Customization',
      subtitle: 'No customization data yet.',
      text: 'Sign in to customize your package and save your selected services.',
    }
  }

  return {
    title: 'Dashboard',
    subtitle: 'No dashboard data yet.',
    text: 'Sign in to view your dashboard, activity, and quick actions.',
  }
})

const activeVendorTab = ref('about')
const customerName = ref('')
const customerEmail = ref('')
const selectedEventType = ref('all')
const bookingFilter = ref('Upcoming')
const bookingEventTypeFilter = ref('all')
const customizationEventType = ref('all')
const customizationSearch = ref('')
const selectedCustomizationPackageId = ref(null)
const customizationQuantity = ref(1)

const vendorBindings = { activeVendorTab, customerName, customerEmail, selectedEventType }
const bookingBindings = { bookingFilter, bookingEventTypeFilter }
const customizationBindings = {
  customizationEventType,
  customizationSearch,
  selectedCustomizationPackageId,
  customizationQuantity,
}
const router = useRouter()

const emptyDashboardStats = {
  totalBookings: 0,
  upcomingBookings: 0,
  completedBookings: 0,
  unreadMessages: 0,
}

function goToSignIn() {
  router.push('/legacy-app')
}

function goToSection(nextSection) {
  if (nextSection === 'bookings') router.push('/booking')
  if (nextSection === 'services') router.push('/services')
  if (nextSection === 'dashboard') router.push('/dashboard')
  if (nextSection === 'customization') router.push('/customization')
}

function noop() {}
</script>

<template>
  <div class="guest-page">
    <header class="topbar">
      <div class="shell topbar-inner">
        <router-link class="brand" to="/">
          <img class="brand-logo" src="/achar-logo.png" alt="Achar logo" />
          <span class="brand-text">Achar</span>
        </router-link>

        <nav class="top-links">
          <router-link to="/" :class="{ active: false }">Home</router-link>
          <router-link to="/dashboard" :class="{ active: section === 'dashboard' }">Dashboard</router-link>
          <router-link to="/services" :class="{ active: section === 'services' }">View Vendors</router-link>
          <router-link to="/booking" :class="{ active: section === 'bookings' }">My Bookings</router-link>
          <router-link to="/legacy-app">Messages</router-link>
        </nav>

        <div class="top-actions">
          <input type="search" placeholder="Search services..." disabled />
          <router-link class="top-logout top-signin" to="/legacy-app">Sign in</router-link>
        </div>
      </div>
    </header>

    <main class="shell guest-content">
      <section class="guest-panel">
        <h1>{{ pageContent.title }}</h1>
        <p class="guest-subtitle">{{ pageContent.subtitle }}</p>
        <p class="guest-text">{{ pageContent.text }}</p>
      </section>

      <DashboardPage
        v-if="section === 'dashboard'"
        :notice="'Sign in to load your dashboard data.'"
        :customer-name="''"
        :dashboard-stats="emptyDashboardStats"
        :recent-bookings="[]"
        :recent-conversations="[]"
        :go-to-vendor="() => goToSection('services')"
        :go-to-bookings="() => goToSection('bookings')"
        :go-to-messages="goToSignIn"
        :go-to-package-customization="() => goToSection('customization')"
        :open-upcoming-bookings="() => goToSection('bookings')"
      />

      <VendorPage
        v-else-if="section === 'services'"
        :vendor-profile="vendorProfile"
        :bindings="vendorBindings"
        :stats="stats"
        :reviews="reviews"
        :event-type-options="eventTypeOptions"
        :filtered-packages="[]"
        :is-loading-events="false"
        :selected-quantities="{}"
        :booking-submitting-event-id="null"
        :checking-availability-event-id="null"
        :vendor-location-text="'Sign in to see vendor locations.'"
        :vendor-map-embed-url="'about:blank'"
        :load-bookings="noop"
        :go-to-package-customization="() => goToSection('customization')"
        :go-to-messages="goToSignIn"
        :book-package="goToSignIn"
        :go-to-availability="goToSignIn"
        :get-availability-tone="() => 'pending'"
        :get-availability-label="() => 'Not checked'"
        :get-availability="() => null"
      />

      <CustomizationPage
        v-else-if="section === 'customization'"
        :event-type-options="eventTypeOptions"
        :event-type-map="eventTypeMap"
        :service-fee-rate="serviceFeeRate"
        :vendor-profile="vendorProfile"
        :bindings="customizationBindings"
        :filtered-customization-packages="[]"
        :selected-services-count="0"
        :customization-total="0"
        :selected-customization-package="null"
        :selected-matching-services="[]"
        :selected-services-subtotal="0"
        :customization-package-subtotal="0"
        :service-fee-amount="0"
        :booking-submitting-event-id="null"
        :effective-customization-event-type="'all'"
        :filtered-matching-services="[]"
        :is-package-expanded="() => false"
        :toggle-package-details="noop"
        :go-to-availability="goToSignIn"
        :go-to-messages="goToSignIn"
        :select-customization-package="noop"
        :is-service-selected="() => false"
        :is-service-expanded="() => false"
        :toggle-matching-service="noop"
        :toggle-service-details="noop"
        :confirm-customization="goToSignIn"
      />

      <BookingsPage
        v-else
        :bindings="bookingBindings"
        :event-type-options="eventTypeOptions"
        :notice="'Sign in to load your bookings.'"
        :is-loading-bookings="false"
        :filtered-bookings="[]"
        :go-to-dashboard="() => goToSection('dashboard')"
        :go-to-vendor="() => goToSection('services')"
        :go-to-messages="goToSignIn"
        :go-to-profile="goToSignIn"
        :booking-secondary-action="noop"
        :booking-primary-action="noop"
      />
    </main>
  </div>
</template>

<style scoped>
.guest-page {
  min-height: 100vh;
}

.guest-content {
  padding-top: 14px;
}

.guest-panel {
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: #fff;
  padding: 18px 22px;
  margin-bottom: 14px;
}

.guest-panel h1 {
  margin: 0;
  font-size: 30px;
}

.guest-subtitle {
  margin: 10px 0 0;
  color: #6b7280;
  font-weight: 700;
}

.guest-text {
  margin: 8px 0 0;
  color: #6b7280;
}

.top-signin {
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

@media (max-width: 720px) {
  .top-signin {
    justify-content: center;
  }
}
</style>
