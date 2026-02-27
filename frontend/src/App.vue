<!-- App.vue -->
<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import Login from './components/LoginForm.vue'
import Register from './components/RegisterForm.vue'
import AvailabilityPage from './components/pages/AvailabilityPage.vue'
import BookingsPage from './components/pages/BookingsPage.vue'
import CustomizationPage from './components/pages/CustomizationPage.vue'
import DashboardPage from './components/pages/DashboardPage.vue'
import MessagesPage from './components/pages/MessagesPage.vue'
import ProfilePage from './components/pages/ProfilePage.vue'
import VendorPage from './components/pages/VendorPage.vue'
import {
  eventTypeMap,
  eventTypeOptions,
  fallbackVendorLocation,
  packageCatalogByEventType,
  packageImageByEventType,
  reviews,
  serviceFeeRate,
  stats,
  vendorProfile,
} from './features/appData'
import { apiGet, apiPost } from './features/apiClient'
import { mapBooking as mapApiBooking, mapEvent as mapApiEvent } from './features/bookingMappers'
import { useAvailabilityFeature } from './features/useAvailabilityFeature'
import { useCustomizationFeature } from './features/useCustomizationFeature'
import { useMessagesFeature } from './features/useMessagesFeature'
import { useProfileFeature } from './features/useProfileFeature'

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const currentView = ref('login')
const loggedInUser = ref(null)

function getStoredUser() {
  const stored = localStorage.getItem(AUTH_USER_STORAGE_KEY)
  if (!stored) return null
  try {
    return JSON.parse(stored)
  } catch {
    localStorage.removeItem(AUTH_USER_STORAGE_KEY)
    return null
  }
}

loggedInUser.value = getStoredUser()

function toggleView() {
  currentView.value = currentView.value === 'register' ? 'login' : 'register'
}

function onLoginSuccess(user) {
  loggedInUser.value = user
  if (!customerName.value?.trim()) customerName.value = user?.name ?? ''
  if (!customerEmail.value?.trim()) customerEmail.value = user?.email ?? ''
}

function logout() {
  loggedInUser.value = null
  currentView.value = 'login'
  localStorage.removeItem(AUTH_USER_STORAGE_KEY)
}

const currentPage = ref('dashboard')
const activeVendorTab = ref('about')
const bookingFilter = ref('Upcoming')

const globalSearch = ref('')

const selectedEventType = ref('all')
const bookingEventTypeFilter = ref('all')

const vendorEvents = ref([])
const bookings = ref([])
const selectedQuantities = ref({})
const availabilityByEvent = ref({})
const checkingAvailabilityEventId = ref(null)
const brandLogoSrc = ref('/achar-logo.png')

const isLoadingEvents = ref(false)
const isLoadingBookings = ref(false)
const bookingSubmittingEventId = ref(null)
const notice = ref('')
const {
  customerName,
  customerEmail,
  userPhone,
  userLocation,
  userLatitude,
  userLongitude,
  isDetectingLocation,
  userProfileNotice,
  userProfileDraft,
  userAvatarInitial,
  userLocationMapUrl,
  goToProfile: openProfilePage,
  saveUserProfile,
  resetUserProfile,
  detectCurrentLocation,
} = useProfileFeature(notice)

const {
  conversationSearch,
  composerText,
  isSharingLocation,
  conversations,
  selectedConversationId,
  filteredConversations,
  activeConversation,
  recentConversations,
  goToMessages,
  selectConversation,
  sendMessage,
  sendFiles,
  sendLocation,
  saveDocument,
  deleteMessage,
} = useMessagesFeature(currentPage)
const {
  availabilityMonthCursor,
  selectedAvailabilityDate,
  selectedAvailabilitySlot,
  availabilityContext,
  monthLabel,
  selectedAvailabilityDateLabel,
  selectedAvailabilitySlotInfo,
  availabilityBaseRate,
  availabilityDeposit,
  availabilityApplicationFee,
  availabilityTotalEstimate,
  calendarCells,
  availabilitySlots,
  isCalendarCellSelected,
  selectAvailabilityDate,
  previousAvailabilityMonth,
  nextAvailabilityMonth,
  selectAvailabilitySlot,
  goToAvailability,
  confirmAvailabilityRequest,
} = useAvailabilityFeature({
  currentPage,
  notice,
  vendorName: vendorProfile.name,
  goToMessages,
})
const {
  customizationSearch,
  customizationEventType,
  selectedCustomizationPackageId,
  customizationQuantity,
  filteredCustomizationPackages,
  selectedCustomizationPackage,
  effectiveCustomizationEventType,
  filteredMatchingServices,
  selectedMatchingServices,
  selectedServicesSubtotal,
  customizationPackageSubtotal,
  serviceFeeAmount,
  customizationTotal,
  selectedServicesCount,
  goToPackageCustomization: openCustomizationPage,
  isPackageExpanded,
  togglePackageDetails,
  isServiceExpanded,
  toggleServiceDetails,
  selectCustomizationPackage,
  isServiceSelected,
  toggleMatchingService,
  confirmCustomization: submitCustomization,
} = useCustomizationFeature({
  vendorEvents,
  customerName,
  customerEmail,
  notice,
  bookingSubmittingEventId,
  checkEventAvailability,
  createBooking: (payload) => apiPost('bookings', payload),
  loadBookings,
  goToBookings,
  bookingFilter,
})
const vendorBindings = { activeVendorTab, customerName, customerEmail, selectedEventType }
const customizationBindings = {
  customizationEventType,
  customizationSearch,
  selectedCustomizationPackageId,
  customizationQuantity,
}
const bookingsBindings = { bookingFilter, bookingEventTypeFilter }
const profileBindings = { userProfileDraft }
const messagesBindings = { conversationSearch, selectedConversationId, composerText }
const availabilityBindings = { selectedAvailabilitySlot }

const navSearchPlaceholder = computed(() =>
  currentPage.value === 'bookings' ? 'Search bookings...' : 'Search services...',
)
const vendorLocationText = computed(() => {
  const firstWithLocation = vendorEvents.value.find((item) => item.location && item.location.trim())
  return firstWithLocation?.location || fallbackVendorLocation
})
const vendorMapEmbedUrl = computed(
  () => `https://www.google.com/maps?q=${encodeURIComponent(vendorLocationText.value)}&output=embed`,
)

const vendorFallbackPackages = computed(() => {
  const packages = []
  Object.entries(packageCatalogByEventType).forEach(([type, entries]) => {
    entries.forEach((entry) => {
      const price = Number(entry.basePrice || 0)
      packages.push({
        id: `preview-${type}-${entry.id}`,
        title: entry.title,
        eventType: type,
        eventTypeLabel: eventTypeMap[type] || 'Other',
        description: entry.description,
        location: fallbackVendorLocation,
        date: 'Date TBD',
        price,
        priceLabel: `From $${price.toLocaleString()}`,
        image: packageImageByEventType[type] || packageImageByEventType.other,
        isPreview: true,
      })
    })
  })
  return packages
})

const filteredPackages = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  const sourcePackages = vendorEvents.value.length ? vendorEvents.value : vendorFallbackPackages.value
  return sourcePackages.filter((item) => {
    const matchesType = selectedEventType.value === 'all' || item.eventType === selectedEventType.value
    const matchesSearch = !q || item.title.toLowerCase().includes(q) || item.description.toLowerCase().includes(q)
    return matchesType && matchesSearch
  })
})

const filteredBookings = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  return bookings.value.filter((item) => {
    const matchesFilter = item.type === bookingFilter.value
    const matchesType = bookingEventTypeFilter.value === 'all' || item.eventType === bookingEventTypeFilter.value
    const matchesSearch =
      !q || item.vendor.toLowerCase().includes(q) || item.service.toLowerCase().includes(q)
    return matchesFilter && matchesSearch && matchesType
  })
})

const dashboardStats = computed(() => {
  const totalBookings = bookings.value.length
  const upcomingBookings = bookings.value.filter((item) => item.type === 'Upcoming').length
  const completedBookings = bookings.value.filter((item) => item.type === 'Completed').length
  const unreadMessages = conversations.value.filter((item) => item.time === 'Just now').length
  return { totalBookings, upcomingBookings, completedBookings, unreadMessages }
})

const recentBookings = computed(() => bookings.value.slice(0, 3))

function onBrandLogoError() {
  brandLogoSrc.value = '/favicon.ico'
}

function getAvailability(item) {
  return availabilityByEvent.value[item.id] || null
}

function getAvailabilityTone(item) {
  const availability = getAvailability(item)
  if (!availability) return 'pending'
  if (!availability.service_available) return 'unavailable'
  if (!availability.vendor_available) return 'busy'
  return 'available'
}

function getAvailabilityLabel(item) {
  const availability = getAvailability(item)
  if (!availability) return 'Not checked'
  if (!availability.service_available) return 'Service Unavailable'
  if (!availability.vendor_available) return 'Vendor Busy'
  return 'Available'
}

async function loadEvents() {
  isLoadingEvents.value = true
  try {
    const result = await apiGet('events')
    const rows = Array.isArray(result.data) ? result.data : []
    vendorEvents.value = rows.map((row) => mapApiEvent(row, eventTypeMap))
    availabilityByEvent.value = {}

    const initialQuantities = {}
    vendorEvents.value.forEach((item) => {
      initialQuantities[item.id] = selectedQuantities.value[item.id] || 1
    })
    selectedQuantities.value = initialQuantities
  } catch (error) {
    notice.value = 'Could not load events from backend API. Please start backend server.'
  } finally {
    isLoadingEvents.value = false
  }
}

async function checkEventAvailability(item) {
  checkingAvailabilityEventId.value = item.id
  try {
    const result = await apiGet(`events/${item.id}/availability`)
    availabilityByEvent.value = {
      ...availabilityByEvent.value,
      [item.id]: result,
    }
    notice.value = result.message || 'Availability checked.'
    return result
  } catch (error) {
    notice.value = 'Could not check availability right now.'
    return null
  } finally {
    checkingAvailabilityEventId.value = null
  }
}

async function loadBookings() {
  if (!customerEmail.value.trim()) {
    bookings.value = []
    return
  }

  isLoadingBookings.value = true
  try {
    const result = await apiGet('bookings', { customer_email: customerEmail.value.trim() })
    const rows = Array.isArray(result.data) ? result.data : []
    bookings.value = rows.map((row) =>
      mapApiBooking(row, { vendorName: vendorProfile.name, eventTypeMap }),
    )
  } catch (error) {
    notice.value = 'Could not load bookings. Check backend API and database migrations.'
  } finally {
    isLoadingBookings.value = false
  }
}

function goToDashboard() {
  currentPage.value = 'dashboard'
}

function goToVendor(tab = 'about') {
  const normalizedTab = typeof tab === 'string' ? tab : 'about'
  const allowedTabs = ['about', 'services', 'reviews']
  currentPage.value = 'vendor'
  activeVendorTab.value = allowedTabs.includes(normalizedTab) ? normalizedTab : 'about'
}

function goToPackageCustomization(preferredEventType = 'all', preferredTitle = '') {
  openCustomizationPage(currentPage, preferredEventType, preferredTitle)
}

function goToProfile() {
  openProfilePage(currentPage)
}

function goToBookings() {
  currentPage.value = 'bookings'
}

function openUpcomingBookings() {
  bookingFilter.value = 'Upcoming'
  goToBookings()
}

async function bookPackage(item) {
  const name = customerName.value.trim()
  const email = customerEmail.value.trim()

  if (!name || !email) {
    notice.value = 'Please enter your name and email before booking.'
    return
  }

  const quantity = Number(selectedQuantities.value[item.id] || 1)
  if (!Number.isFinite(quantity) || quantity < 1) {
    notice.value = 'Please select a valid quantity.'
    return
  }

  const availability = getAvailability(item) || (await checkEventAvailability(item))
  if (!availability || !availability.service_available || !availability.vendor_available) {
    notice.value = availability?.message || 'This service is not available at the moment.'
    return
  }

  bookingSubmittingEventId.value = item.id
  try {
    await apiPost('bookings', {
      event_id: item.id,
      quantity,
      customer_name: name,
      customer_email: email,
    })

    notice.value = `Booking created for ${item.title}.`
    await loadBookings()
    goToBookings()
    bookingFilter.value = 'Upcoming'
  } catch (error) {
    notice.value = error.message || 'Booking failed.'
  } finally {
    bookingSubmittingEventId.value = null
  }
}

function bookingPrimaryAction(item) {
  if (item.primaryBtn === 'Message Vendor') {
    goToMessages(item.vendor)
    return
  }
  if (item.primaryBtn === 'View Details') {
    goToVendor()
  }
}

function bookingSecondaryAction(item) {
  item.note = 'Reschedule request sent. Waiting for confirmation.'
}

async function confirmCustomization() {
  await submitCustomization(getAvailability)
}

watch([customerName, customerEmail, userPhone, userLocation, userLatitude, userLongitude], () => {
  localStorage.setItem('achar_customer_name', customerName.value)
  localStorage.setItem('achar_customer_email', customerEmail.value)
  localStorage.setItem('achar_user_phone', userPhone.value)
  localStorage.setItem('achar_user_location', userLocation.value)
  localStorage.setItem('achar_user_lat', userLatitude.value === null ? '' : String(userLatitude.value))
  localStorage.setItem('achar_user_lng', userLongitude.value === null ? '' : String(userLongitude.value))
})

watch(customerEmail, () => {
  if (currentPage.value === 'bookings' || currentPage.value === 'dashboard') {
    loadBookings()
  }
})

watch(loggedInUser, (user) => {
  if (user) localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(user))
})

onMounted(async () => {
  if (!loggedInUser.value) return
  await loadEvents()
  await loadBookings()
})
</script>

<template>

  <div class="auth-root">
    <Register v-if="!loggedInUser && currentView === 'register'" @switch="toggleView" />
    <Login v-else-if="!loggedInUser" @switch="toggleView" @success="onLoginSuccess" />
    <div v-else class="page">
    <header class="topbar">
      <div class="shell topbar-inner">
        <div class="brand">
          <img class="brand-logo" :src="brandLogoSrc" alt="Achar logo" @error="onBrandLogoError" />
          <span class="brand-text">Achar</span>
        </div>

        <nav class="top-links">
          <a href="#" :class="{ active: currentPage === 'dashboard' }" @click.prevent="goToDashboard">Dashboard</a>
          <a href="#" :class="{ active: currentPage === 'vendor' || currentPage === 'customization' || currentPage === 'availability' }" @click.prevent="goToVendor()">View Vendors</a>
          <a href="#" :class="{ active: currentPage === 'bookings' }" @click.prevent="goToBookings">My Bookings</a>
          <a href="#" :class="{ active: currentPage === 'messages' }" @click.prevent="goToMessages()">Messages</a>
        </nav>

        <div class="top-actions">
          <input
            v-if="currentPage !== 'messages'"
            v-model="globalSearch"
            type="search"
            :placeholder="navSearchPlaceholder"
          />
          <button type="button" class="top-logout" @click="logout">Logout</button>
          <button type="button" class="avatar avatar-btn" @click="goToProfile">{{ userAvatarInitial }}</button>
        </div>
      </div>
    </header>

        <DashboardPage
      v-if="currentPage === 'dashboard'"
      :notice="notice"
      :customer-name="customerName"
      :dashboard-stats="dashboardStats"
      :recent-bookings="recentBookings"
      :recent-conversations="recentConversations"
      :go-to-vendor="goToVendor"
      :go-to-bookings="goToBookings"
      :go-to-messages="goToMessages"
      :go-to-package-customization="goToPackageCustomization"
      :open-upcoming-bookings="openUpcomingBookings"
    />

    <VendorPage
      v-else-if="currentPage === 'vendor'"
      :vendor-profile="vendorProfile"
      :bindings="vendorBindings"
      :stats="stats"
      :reviews="reviews"
      :event-type-options="eventTypeOptions"
      :filtered-packages="filteredPackages"
      :is-loading-events="isLoadingEvents"
      :selected-quantities="selectedQuantities"
      :booking-submitting-event-id="bookingSubmittingEventId"
      :checking-availability-event-id="checkingAvailabilityEventId"
      :vendor-location-text="vendorLocationText"
      :vendor-map-embed-url="vendorMapEmbedUrl"
      :load-bookings="loadBookings"
      :go-to-package-customization="goToPackageCustomization"
      :go-to-messages="goToMessages"
      :book-package="bookPackage"
      :go-to-availability="goToAvailability"
      :get-availability-tone="getAvailabilityTone"
      :get-availability-label="getAvailabilityLabel"
      :get-availability="getAvailability"
    />

    <CustomizationPage
      v-else-if="currentPage === 'customization'"
      :event-type-options="eventTypeOptions"
      :event-type-map="eventTypeMap"
      :service-fee-rate="serviceFeeRate"
      :vendor-profile="vendorProfile"
      :bindings="customizationBindings"
      :filtered-customization-packages="filteredCustomizationPackages"
      :selected-services-count="selectedServicesCount"
      :customization-total="customizationTotal"
      :selected-customization-package="selectedCustomizationPackage"
      :selected-matching-services="selectedMatchingServices"
      :selected-services-subtotal="selectedServicesSubtotal"
      :customization-package-subtotal="customizationPackageSubtotal"
      :service-fee-amount="serviceFeeAmount"
      :booking-submitting-event-id="bookingSubmittingEventId"
      :effective-customization-event-type="effectiveCustomizationEventType"
      :filtered-matching-services="filteredMatchingServices"
      :is-package-expanded="isPackageExpanded"
      :toggle-package-details="togglePackageDetails"
      :go-to-availability="goToAvailability"
      :go-to-messages="goToMessages"
      :select-customization-package="selectCustomizationPackage"
      :is-service-selected="isServiceSelected"
      :is-service-expanded="isServiceExpanded"
      :toggle-matching-service="toggleMatchingService"
      :toggle-service-details="toggleServiceDetails"
      :confirm-customization="confirmCustomization"
    />

    <AvailabilityPage
      v-else-if="currentPage === 'availability'"
      :bindings="availabilityBindings"
      :month-label="monthLabel"
      :calendar-cells="calendarCells"
      :selected-availability-date-label="selectedAvailabilityDateLabel"
      :availability-slots="availabilitySlots"
      :selected-availability-slot-info="selectedAvailabilitySlotInfo"
      :availability-context="availabilityContext"
      :availability-base-rate="availabilityBaseRate"
      :availability-deposit="availabilityDeposit"
      :availability-application-fee="availabilityApplicationFee"
      :availability-total-estimate="availabilityTotalEstimate"
      :previous-availability-month="previousAvailabilityMonth"
      :next-availability-month="nextAvailabilityMonth"
      :is-calendar-cell-selected="isCalendarCellSelected"
      :select-availability-date="selectAvailabilityDate"
      :select-availability-slot="selectAvailabilitySlot"
      :confirm-availability-request="confirmAvailabilityRequest"
    />

    <BookingsPage
      v-else-if="currentPage === 'bookings'"
      :bindings="bookingsBindings"
      :event-type-options="eventTypeOptions"
      :notice="notice"
      :is-loading-bookings="isLoadingBookings"
      :filtered-bookings="filteredBookings"
      :go-to-dashboard="goToDashboard"
      :go-to-vendor="goToVendor"
      :go-to-messages="goToMessages"
      :go-to-profile="goToProfile"
      :booking-secondary-action="bookingSecondaryAction"
      :booking-primary-action="bookingPrimaryAction"
    />

    <ProfilePage
      v-else-if="currentPage === 'profile'"
      :bindings="profileBindings"
      :user-profile-notice="userProfileNotice"
      :is-detecting-location="isDetectingLocation"
      :user-latitude="userLatitude"
      :user-longitude="userLongitude"
      :user-location-map-url="userLocationMapUrl"
      :detect-current-location="detectCurrentLocation"
      :reset-user-profile="resetUserProfile"
      :save-user-profile="saveUserProfile"
    />

    <MessagesPage
      v-else
      :bindings="messagesBindings"
      :filtered-conversations="filteredConversations"
      :active-conversation="activeConversation"
      :select-conversation="selectConversation"
      :send-message="sendMessage"
      :send-files="sendFiles"
      :send-location="sendLocation"
      :is-sharing-location="isSharingLocation"
      :save-document="saveDocument"
      :delete-message="deleteMessage"
    />
<footer v-if="currentPage !== 'messages'" class="footer">
      <div class="shell footer-grid">
        <div class="footer-brand-col">
          <div class="brand">
            <img class="brand-logo" :src="brandLogoSrc" alt="Achar logo" @error="onBrandLogoError" />
            <span class="brand-text">Achar</span>
          </div>
          <p>Making event planning effortless and elegant for everyone, everywhere.</p>
          <span class="footer-chip">Trusted by planners and vendors</span>
        </div>
        <div>
          <h4>For Planners</h4>
          <a href="#" @click.prevent="goToVendor()">View Vendors</a>
          <a href="#" @click.prevent="goToDashboard">Planning Dashboard</a>
          <a href="#" @click.prevent="goToBookings">My Bookings</a>
        </div>
        <div>
          <h4>For Vendors</h4>
          <a href="#" @click.prevent="goToVendor()">List Your Service</a>
          <a href="#" @click.prevent="goToMessages()">Vendor Inbox</a>
          <a href="#" @click.prevent="goToDashboard">Performance Snapshot</a>
        </div>
        <div>
          <h4>Support</h4>
          <a href="#">Help Center</a>
          <a href="#">Terms of Service</a>
          <a href="#">Contact Us</a>
        </div>
      </div>
      <div class="shell footer-bottom">
        <span>© {{ new Date().getFullYear() }} Achar Event Booking. All rights reserved.</span>
        <div>
          <a href="#">Privacy Policy</a>
          <a href="#">Cookie Policy</a>
          <a href="#">Sitemap</a>
        </div>
      </div>
    </footer>
  </div>
  </div>
</template>


