<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Login from './components/LoginForm.vue'
import Register from './components/RegisterForm.vue'
import AvailabilityPage from './components/pages/AvailabilityPage.vue'
import BookingsPage from './components/pages/BookingsPage.vue'
import CustomizationPage from './components/pages/CustomizationPage.vue'
import DashboardPage from './components/pages/DashboardPage.vue'
import MessagesPage from './components/pages/MessagesPage.vue'
import ProfilePage from './components/pages/ProfilePage.vue'
import PublicNavbar from './components/PublicNavbar.vue'
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
import { apiGet, apiPatch, apiPost, apiDelete } from './features/apiClient'
import { mapBooking as mapApiBooking, mapEvent as mapApiEvent } from './features/bookingMappers'
import { useAvailabilityFeature } from './features/useAvailabilityFeature'
import { useCustomizationFeature } from './features/useCustomizationFeature'
import { useMessagesFeature } from './features/useMessagesFeature'
import { useProfileFeature } from './features/useProfileFeature'

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const POST_AUTH_REDIRECT_KEY = 'achar_post_auth_redirect'
const POST_AUTH_REDIRECT_AT_KEY = 'achar_post_auth_redirect_at'
const POST_AUTH_REDIRECT_TTL_MS = 5 * 60 * 1000
const LOCAL_BOOKINGS_STORAGE_KEY = 'achar_local_bookings'
const GLOBAL_SEARCH_SESSION_KEY = 'achar_global_search'
const router = useRouter()
const route = useRoute()
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
  const accountName = String(user?.name || '').trim()
  const accountEmail = normalizeEmail(user?.email)
  if (accountName) customerName.value = accountName
  if (accountEmail) customerEmail.value = accountEmail
  void bootstrapAuthenticatedShell()
}

function handlePostAuthRedirect() {
  const redirectPath = sessionStorage.getItem(POST_AUTH_REDIRECT_KEY)
  const redirectAtRaw = sessionStorage.getItem(POST_AUTH_REDIRECT_AT_KEY)
  sessionStorage.removeItem(POST_AUTH_REDIRECT_KEY)
  sessionStorage.removeItem(POST_AUTH_REDIRECT_AT_KEY)
  if (!redirectPath) return false
  const redirectAt = Number(redirectAtRaw || 0)
  const isFresh = Number.isFinite(redirectAt) && Date.now() - redirectAt <= POST_AUTH_REDIRECT_TTL_MS
  const isSafePath = typeof redirectPath === 'string' && redirectPath.startsWith('/')
  if (!isFresh || !isSafePath) return false
  router.push(redirectPath).catch(() => {})
  return true
}

function requireLogin(message = 'Please sign in to continue booking.') {
  if (loggedInUser.value) return true
  notice.value = message
  currentView.value = 'login'
  router.replace({ path: '/legacy-app' }).catch(() => {})
  return false
}

function logout() {
  loggedInUser.value = null
  currentView.value = 'login'
  notifications.value = []
  notificationsUnreadCount.value = 0
  notificationsError.value = ''
  notificationDropdownOpen.value = false
  stopNotificationPolling()
  localStorage.removeItem(AUTH_USER_STORAGE_KEY)
}

const currentPage = ref('dashboard')
const activeVendorTab = ref('about')
const bookingFilter = ref('Upcoming')
const allowedPages = ['dashboard', 'vendor', 'customization', 'availability', 'bookings', 'profile', 'messages']
const allowedVendorTabs = ['about', 'services', 'reviews']
const isPlannerUser = computed(() => String(loggedInUser.value?.role || 'user') === 'user')
const defaultLegacyPage = computed(() => 'bookings')

function firstQueryValue(value) {
  return Array.isArray(value) ? value[0] : value
}

function normalizeEmail(value) {
  return String(value || '').trim().toLowerCase()
}

function normalizePage(value) {
  const page = firstQueryValue(value)
  if (!allowedPages.includes(page)) return defaultLegacyPage.value
  if (page === 'dashboard') return 'bookings'
  return page
}

function normalizeVendorTab(value) {
  const tab = firstQueryValue(value)
  return allowedVendorTabs.includes(tab) ? tab : 'about'
}

function applyRouteStateFromQuery(query) {
  const nextPage = normalizePage(query.page)
  currentPage.value = nextPage
  if (nextPage === 'vendor') activeVendorTab.value = normalizeVendorTab(query.tab)
}

function syncRouteQueryFromState() {
  const nextQuery = {}
  if (currentPage.value !== defaultLegacyPage.value) nextQuery.page = currentPage.value
  if (currentPage.value === 'vendor') nextQuery.tab = activeVendorTab.value

  const currentPageQuery = firstQueryValue(route.query.page)
  const currentTabQuery = firstQueryValue(route.query.tab)

  const samePage = (currentPageQuery || undefined) === (nextQuery.page || undefined)
  const sameTab = (currentTabQuery || undefined) === (nextQuery.tab || undefined)

  if (!samePage || !sameTab) {
    router.replace({ path: '/legacy-app', query: nextQuery }).catch(() => {})
  }
}

function clearSocialQueryFromRoute() {
  const nextQuery = { ...route.query }
  delete nextQuery.social
  delete nextQuery.message
  delete nextQuery.id
  delete nextQuery.name
  delete nextQuery.email
  delete nextQuery.role
  router.replace({ path: '/legacy-app', query: nextQuery }).catch(() => {})
}

function handleSocialQueryResult() {
  const socialStatus = firstQueryValue(route.query.social)
  if (!socialStatus) return

  if (socialStatus === 'error') {
    const message = firstQueryValue(route.query.message)
    localStorage.setItem('achar_social_error', String(message || 'Social login failed.'))
    currentView.value = 'login'
    clearSocialQueryFromRoute()
    return
  }

  if (socialStatus === 'success') {
    const idValue = Number(firstQueryValue(route.query.id) || 0)
    const name = String(firstQueryValue(route.query.name) || '').trim()
    const email = String(firstQueryValue(route.query.email) || '').trim()
    const role = String(firstQueryValue(route.query.role) || 'user').trim() || 'user'

    if (name && email) {
      onLoginSuccess({
        id: Number.isFinite(idValue) && idValue > 0 ? idValue : Date.now(),
        name,
        email,
        role,
      })
    } else {
      localStorage.setItem('achar_social_error', 'Social login did not return valid user info.')
    }

    currentView.value = 'login'
    clearSocialQueryFromRoute()
  }
}

const globalSearch = ref('')

function applyGlobalSearchFromSession() {
  const nextSearch = sessionStorage.getItem(GLOBAL_SEARCH_SESSION_KEY)
  globalSearch.value = typeof nextSearch === 'string' ? nextSearch : ''
}

function handleGlobalSearchUpdated() {
  applyGlobalSearchFromSession()
}

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
const isLoadingNotifications = ref(false)
const bookingSubmittingEventId = ref(null)
const notice = ref('')
const notificationsError = ref('')
const notifications = ref([])
const notificationsUnreadCount = ref(0)
const notificationDropdownOpen = ref(false)
const notificationMenuRef = ref(null)
const isBootstrappingAuth = ref(false)
let notificationPollTimer = null
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
  userLocationMapEmbedUrl,
  userLocationMapLinkUrl,
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
  goToAvailability: openAvailabilityPage,
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
const notificationItems = computed(() =>
  notifications.value.map((item) => {
    const booking = item.booking || {}
    const event = booking.event || {}
    return {
      ...item,
      eventTitle: event.title || 'Service Booking',
      eventDate: formatNotificationDate(event.starts_at),
      createdLabel: formatNotificationTime(item.created_at),
    }
  }),
)
const unreadNotificationCount = computed(
  () => notificationsUnreadCount.value || notifications.value.filter((item) => !item.is_read).length,
)

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

function getLocalBookingsByEmail(email) {
  if (!email) return []
  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY)
    if (!raw) return []
    const rows = JSON.parse(raw)
    if (!Array.isArray(rows)) return []
    const normalizedEmail = email.trim().toLowerCase()
    return rows
      .filter((row) => String(row?.customerEmail || '').trim().toLowerCase() === normalizedEmail)
      .map((row, index) => ({
        id: row.id || `local-${index}`,
        vendor: row.vendor || vendorProfile.name,
        service: row.service || 'Service Booking',
        date: row.dateLabel || 'Date TBD',
        metaLabel: 'Event Type',
        metaValue: eventTypeMap[row.eventType] || 'Other',
        placeLabel: 'Total',
        placeValue: `$${Number(row.total || 0).toLocaleString()}`,
        status: row.status || 'Confirmed',
        statusClass: row.statusClass || 'confirmed',
        type: row.type || 'Upcoming',
        eventType: row.eventType || 'other',
        eventId: null,
        image:
          'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=760&q=80',
        primaryBtn: 'View Details',
        secondaryBtn: 'Reschedule',
        note: `${row.customerName || 'Guest User'} | ${row.customerEmail || normalizedEmail}`,
      }))
  } catch {
    return []
  }
}

function mergeBookingsWithLocal(apiMappedRows, email) {
  const localRows = getLocalBookingsByEmail(email)
  if (!localRows.length) return apiMappedRows
  const apiIds = new Set(apiMappedRows.map((row) => String(row.id)))
  const localOnlyRows = localRows.filter((row) => !apiIds.has(String(row.id)))
  return [...localOnlyRows, ...apiMappedRows]
}

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

function formatNotificationDate(value) {
  if (!value) return 'Date TBD'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return 'Date TBD'
  return parsed.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  })
}

function formatNotificationTime(value) {
  if (!value) return 'Just now'
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return 'Just now'

  const diffMinutes = Math.floor((Date.now() - parsed.getTime()) / 60000)
  if (diffMinutes < 1) return 'Just now'
  if (diffMinutes < 60) return `${diffMinutes}m ago`
  if (diffMinutes < 24 * 60) return `${Math.floor(diffMinutes / 60)}h ago`

  return parsed.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
  })
}

function notificationRole(role) {
  return role === 'vendor' ? 'vendor' : 'user'
}

function buildNotificationQuery() {
  const user = loggedInUser.value || {}
  const query = {
    role: notificationRole(user.role),
    limit: 20,
  }

  const userId = Number(user.id)
  if (Number.isFinite(userId) && userId > 0) query.user_id = userId

  const profileEmail = normalizeEmail(customerEmail.value)
  const accountEmail = normalizeEmail(user.email)
  const email = profileEmail || accountEmail
  if (email) query.email = email

  if (!query.user_id && !query.email) return null
  return query
}

function stopNotificationPolling() {
  if (!notificationPollTimer) return
  clearInterval(notificationPollTimer)
  notificationPollTimer = null
}

function startNotificationPolling() {
  stopNotificationPolling()
  notificationPollTimer = setInterval(() => {
    loadNotifications({ silent: true })
  }, 30000)
}

function closeNotificationDropdown() {
  notificationDropdownOpen.value = false
}

function handleDocumentClick(event) {
  if (!notificationDropdownOpen.value) return
  if (!notificationMenuRef.value) return
  if (!notificationMenuRef.value.contains(event.target)) {
    closeNotificationDropdown()
  }
}

async function loadNotifications(options = {}) {
  const { silent = false } = options
  const query = buildNotificationQuery()

  if (!query) {
    notifications.value = []
    notificationsUnreadCount.value = 0
    return
  }

  if (!silent) isLoadingNotifications.value = true
  notificationsError.value = ''

  try {
    const result = await apiGet('notifications/bookings', query)
    const rows = Array.isArray(result.data) ? result.data : []
    notifications.value = rows
    notificationsUnreadCount.value = Number(result.unread_count || 0)
  } catch (error) {
    notificationsError.value = 'Could not load notifications right now.'
  } finally {
    if (!silent) isLoadingNotifications.value = false
  }
}

async function toggleNotificationDropdown() {
  notificationDropdownOpen.value = !notificationDropdownOpen.value
  if (!notificationDropdownOpen.value) return
  await loadNotifications()
}

async function markNotificationAsRead(notification, options = {}) {
  const { silent = false } = options
  if (!notification || notification.is_read) return

  const query = buildNotificationQuery()
  if (!query) return

  notification.is_read = true
  notificationsUnreadCount.value = Math.max(0, unreadNotificationCount.value - 1)

  try {
    await apiPatch(`notifications/bookings/${notification.id}/read`, query)
  } catch (error) {
    if (!silent) notificationsError.value = 'Could not mark notification as read.'
    await loadNotifications({ silent: true })
  }
}

async function markAllNotificationsAsRead() {
  if (unreadNotificationCount.value < 1) return

  const query = buildNotificationQuery()
  if (!query) return

  try {
    await apiPatch('notifications/bookings/read-all', query)
    notifications.value = notifications.value.map((item) => ({ ...item, is_read: true }))
    notificationsUnreadCount.value = 0
  } catch (error) {
    notificationsError.value = 'Could not mark all notifications as read.'
    await loadNotifications({ silent: true })
  }
}

async function openNotification(notification) {
  await markNotificationAsRead(notification, { silent: true })
  closeNotificationDropdown()
  goToBookings()
}

async function toggleNotificationRead(notification) {
  if (!notification) return

  const query = buildNotificationQuery()
  if (!query) return

  const wasRead = notification.is_read
  notification.is_read = !wasRead
  if (!wasRead) {
    notificationsUnreadCount.value = Math.max(0, unreadNotificationCount.value - 1)
  } else {
    notificationsUnreadCount.value = (unreadNotificationCount.value || 0) + 1
  }

  try {
    const endpoint = wasRead ? 'unread' : 'read'
    await apiPatch(`notifications/bookings/${notification.id}/${endpoint}`, query)
  } catch (error) {
    notification.is_read = wasRead
    notificationsError.value = `Could not update notification status. ${error?.message || ''}`
    await loadNotifications({ silent: true })
  }
}

async function archiveNotification(notification) {
  if (!notification) return

  const query = buildNotificationQuery()
  if (!query) return

  const notificationIndex = notifications.value.findIndex((n) => n.id === notification.id)

  try {
    await apiPatch(`notifications/bookings/${notification.id}/archive`, query)
    if (notificationIndex >= 0) {
      notifications.value.splice(notificationIndex, 1)
    }
    if (!notification.is_read) {
      notificationsUnreadCount.value = Math.max(0, unreadNotificationCount.value - 1)
    }
  } catch (error) {
    notificationsError.value = 'Could not archive notification.'
    await loadNotifications({ silent: true })
  }
}

async function deleteNotification(notification) {
  if (!notification) return
  if (!confirm('Are you sure you want to delete this notification?')) return

  const query = buildNotificationQuery()
  if (!query) return

  const notificationIndex = notifications.value.findIndex((n) => n.id === notification.id)

  try {
    await apiDelete(`notifications/bookings/${notification.id}?${new URLSearchParams(query).toString()}`)
    if (notificationIndex >= 0) {
      notifications.value.splice(notificationIndex, 1)
    }
    if (!notification.is_read) {
      notificationsUnreadCount.value = Math.max(0, unreadNotificationCount.value - 1)
    }
  } catch (error) {
    notificationsError.value = 'Could not delete notification.'
    await loadNotifications({ silent: true })
  }
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
  if (!requireLogin('Please sign in to check service availability.')) {
    return null
  }

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
  const email = normalizeEmail(customerEmail.value)
  if (!email) {
    bookings.value = []
    return
  }

  isLoadingBookings.value = true
  try {
    const result = await apiGet('bookings', { customer_email: email })
    const rows = Array.isArray(result.data) ? result.data : []
    const apiMappedRows = rows.map((row) =>
      mapApiBooking(row, { vendorName: vendorProfile.name, eventTypeMap }),
    )
    bookings.value = mergeBookingsWithLocal(apiMappedRows, email)
    await loadNotifications({ silent: true })
  } catch (error) {
    const localRows = getLocalBookingsByEmail(email)
    bookings.value = localRows
    notice.value = localRows.length
      ? 'Loaded your latest booking from this device.'
      : 'Could not load bookings. Check backend API and database migrations.'
  } finally {
    isLoadingBookings.value = false
  }
}

async function bootstrapAuthenticatedShell() {
  if (!loggedInUser.value) return

  isBootstrappingAuth.value = true
  try {
    const tasks = [loadEvents(), loadNotifications({ silent: true })]
    if (customerEmail.value.trim()) tasks.push(loadBookings())
    await Promise.all(tasks)
    startNotificationPolling()
  } finally {
    isBootstrappingAuth.value = false
  }
}

function goToDashboard() {
  goToBookings()
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

function goToAvailability(item = null) {
  if (!requireLogin('Please sign in to check service availability.')) {
    return
  }
  openAvailabilityPage(item)
}

function goToProfile() {
  openProfilePage(currentPage)
}

function goToBookings() {
  currentPage.value = 'bookings'
}

function goToHomePage() {
  router.push('/').catch(() => {})
}

function goToAboutPage() {
  router.push('/about').catch(() => {})
}

function goToServicePage() {
  router.push('/services/packages').catch(() => {})
}

function goToFavoritePage() {
  router.push('/favorite').catch(() => {})
}

function goToContactPage() {
  router.push('/contact').catch(() => {})
}

function goToMyBookingPage() {
  router.push('/booking').catch(() => {})
}

function openUpcomingBookings() {
  bookingFilter.value = 'Upcoming'
  goToBookings()
}

async function bookPackage(item) {
  if (!requireLogin('Please sign in before checking availability and booking.')) {
    return
  }

  const name = customerName.value.trim()
  const email = normalizeEmail(customerEmail.value)

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
    await loadNotifications({ silent: true })
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
  if (!requireLogin('Please sign in before confirming your package booking.')) {
    return
  }
  await submitCustomization(getAvailability)
  await loadNotifications({ silent: true })
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
  if (!loggedInUser.value || isBootstrappingAuth.value) return

  if (currentPage.value === 'bookings' || currentPage.value === 'dashboard') {
    loadBookings()
  }
  loadNotifications({ silent: true })
})

watch(loggedInUser, (user) => {
  if (user) {
    localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(user))
    window.dispatchEvent(new Event('achar:auth-updated'))
    return
  }

  stopNotificationPolling()
  notifications.value = []
  notificationsUnreadCount.value = 0
  notificationsError.value = ''
  notificationDropdownOpen.value = false
  window.dispatchEvent(new Event('achar:auth-updated'))
})
watch(
  () => route.query,
  (query) => {
    applyRouteStateFromQuery(query)
  },
  { deep: true },
)

watch([currentPage, activeVendorTab], () => {
  closeNotificationDropdown()
  syncRouteQueryFromState()
})

onMounted(async () => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('achar:global-search-updated', handleGlobalSearchUpdated)
  applyRouteStateFromQuery(route.query)
  handleSocialQueryResult()
  if (!loggedInUser.value) return
  const pendingSearch = sessionStorage.getItem(GLOBAL_SEARCH_SESSION_KEY)
  if (pendingSearch) {
    globalSearch.value = pendingSearch
    sessionStorage.removeItem(GLOBAL_SEARCH_SESSION_KEY)
  }
  if (!customerName.value.trim()) customerName.value = loggedInUser.value?.name || ''
  if (!customerEmail.value.trim()) customerEmail.value = loggedInUser.value?.email || ''
  handlePostAuthRedirect()
  await bootstrapAuthenticatedShell()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('achar:global-search-updated', handleGlobalSearchUpdated)
  stopNotificationPolling()
})
</script>

<template>
  <div class="auth-root">
    <Register v-if="!loggedInUser && currentView === 'register'" @switch="toggleView" @success="onLoginSuccess" />
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
          <div ref="notificationMenuRef" class="notification-wrap">
            <button
              type="button"
              class="notification-btn"
              aria-label="Open booking notifications"
              :aria-expanded="notificationDropdownOpen ? 'true' : 'false'"
              @click.stop="toggleNotificationDropdown"
            >
              <span class="notification-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M18 8a6 6 0 0 0-12 0c0 7-3 8-3 8h18s-3-1-3-8" />
                  <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                </svg>
              </span>
              <span v-if="unreadNotificationCount > 0" class="notification-badge">
                {{ unreadNotificationCount > 99 ? '99+' : unreadNotificationCount }}
              </span>
            </button>

            <section v-if="notificationDropdownOpen" class="notification-panel card" @click.stop>
              <div class="notification-head">
                <div>
                  <h3>Booking Notifications</h3>
                  <p>{{ unreadNotificationCount }} unread</p>
                </div>
                <button
                  v-if="unreadNotificationCount > 0"
                  type="button"
                  class="notification-mark-all"
                  @click="markAllNotificationsAsRead"
                >
                  Mark all read
                </button>
              </div>

              <p v-if="isLoadingNotifications" class="notification-empty">Loading notifications...</p>
              <p v-else-if="notificationsError" class="notification-empty notification-empty-error">
                {{ notificationsError }}
              </p>
              <p v-else-if="notificationItems.length === 0" class="notification-empty">
                No booking notifications yet.
              </p>

              <ul v-else class="notification-list">
                <li v-for="item in notificationItems" :key="item.id">
                  <article class="notification-item" :class="{ unread: !item.is_read }">
                    <div class="notification-item-top">
                      <strong>
                        {{ item.title }}
                        <span v-if="!item.is_read" class="notification-item-dot" aria-hidden="true"></span>
                      </strong>
                      <span>{{ item.createdLabel }}</span>
                    </div>
                    <p>{{ item.message }}</p>
                    <div class="notification-item-info">
                      <small>{{ item.eventTitle }} - {{ item.eventDate }}</small>
                    </div>
                    <div class="notification-item-bottom">
                      <div class="notification-item-actions">
                        <button
                          type="button"
                          class="notification-action-btn"
                          :class="{ secondary: item.is_read }"
                          :title="item.is_read ? 'Mark as unread' : 'Mark as read'"
                          @click="toggleNotificationRead(item)"
                        >
                          {{ item.is_read ? '✗ Mark unread' : '✓ Mark read' }}
                        </button>
                        <button
                          type="button"
                          class="notification-action-btn"
                          title="Archive notification"
                          @click="archiveNotification(item)"
                        >
                          Archive
                        </button>
                        <button
                          type="button"
                          class="notification-action-btn danger"
                          title="Delete notification"
                          @click="deleteNotification(item)"
                        >
                          Delete
                        </button>
                      </div>
                      <button type="button" class="notification-item-link" @click="openNotification(item)">
                        View booking
                      </button>
                    </div>
                  </article>
                </li>
              </ul>
            </section>
          </div>
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
      v-if="currentPage === 'vendor'"
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
      :user-location-map-embed-url="userLocationMapEmbedUrl"
      :user-location-map-link-url="userLocationMapLinkUrl"
      :detect-current-location="detectCurrentLocation"
      :reset-user-profile="resetUserProfile"
      :save-user-profile="saveUserProfile"
      :logout-user="logout"
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
  </div>
  </div>
</template>
