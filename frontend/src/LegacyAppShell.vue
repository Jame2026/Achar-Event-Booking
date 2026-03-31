<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Login from './components/LoginForm.vue'
import Register from './components/RegisterForm.vue'
import AvailabilityPage from './components/pages/AvailabilityPage.vue'
import BookingsPage from './components/pages/BookingsPage.vue'
import CustomizationPage from './components/pages/CustomizationPage.vue'
import AdminDashboardPage from './components/pages/AdminDashboardPage.vue'
import AdminBookingsPage from './components/pages/AdminBookingsPage.vue'
import AdminCustomersPage from './components/pages/AdminCustomersPage.vue'
import AdminEventsPage from './components/pages/AdminEventsPage.vue'
import AdminRevenuePage from './components/pages/AdminRevenuePage.vue'
import AdminVendorsPage from './components/pages/AdminVendorsPage.vue'
import DashboardPage from './components/pages/DashboardPage.vue'
import MessagesPage from './components/pages/MessagesPage.vue'
import ProfilePage from './components/pages/ProfilePage.vue'
import PublicNavbar from './components/PublicNavbar.vue'
import VendorDashboardPage from './components/pages/VendorDashboardPage.vue'
import VendorPage from './components/pages/VendorPage.vue'
import {
  eventTypeMap,
  eventTypeOptions,
  fallbackVendorLocation,
  reviews,
  serviceFeeRate,
  stats,
  vendorProfile,
} from './features/appData'
import { apiDelete, apiGet, apiPatch, apiPost } from './features/apiClient'
import {
  deriveBookingType,
  formatDateTime,
  getBookingMatchKey,
  mapBooking as mapApiBooking,
  mapEvent as mapApiEvent,
  normalizeBookingEventTypeKey,
  summarizeBookedServices,
} from './features/bookingMappers'
import { sumPackageServicePrices } from './features/packagePricing'
import { useAvailabilityFeature } from './features/useAvailabilityFeature'
import { useCustomizationFeature } from './features/useCustomizationFeature'
import { useVendorMessagesFeature } from './features/useVendorMessagesFeature'
import { useProfileFeature } from './features/useProfileFeature'
import { useLanguageCopy } from './features/language'

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const POST_AUTH_REDIRECT_KEY = 'achar_post_auth_redirect'
const POST_AUTH_REDIRECT_AT_KEY = 'achar_post_auth_redirect_at'
const POST_AUTH_REDIRECT_TTL_MS = 5 * 60 * 1000
const MESSAGE_VENDOR_TARGET_KEY = 'achar_message_vendor_target'
const LOCAL_BOOKINGS_STORAGE_KEY = 'achar_local_bookings'
const LAST_BOOKING_EMAIL_KEY = 'achar_last_booking_email'
const LAST_BOOKING_PHONE_KEY = 'achar_last_booking_phone'
const GLOBAL_SEARCH_SESSION_KEY = 'achar_global_search'
const EVENTS_CACHE_KEY = 'achar_guest_events_cache_v1'
const router = useRouter()
const route = useRoute()
const currentView = ref('login')
const loggedInUser = ref(null)
const copyByLanguage = {
  en: {
    signInToContinue: 'Please sign in to continue booking.',
    socialLoginFailed: 'Social login failed.',
    socialLoginInvalid: 'Social login did not return valid user info.',
    searchBookings: 'Search bookings...',
    searchServices: 'Search services...',
    serviceBooking: 'Service Booking',
    vendor: 'Vendor',
    customer: 'Customer',
    dateTbd: 'Date TBD',
    loadEventsFailed: 'Could not load events from backend API. Please start backend server.',
    vendorAccountMissing: 'Vendor account is missing.',
    fillTitleLocationStart: 'Please fill title, location, and start date.',
    qrCodeRequired: 'Please upload a payment QR code.',
    serviceCreated: 'Service created successfully and is now visible to users.',
    serviceUpdated: 'Service updated successfully.',
    serviceDeleted: 'Service deleted successfully.',
    couldNotCreateService: 'Could not create service.',
    couldNotUpdateServiceDetails: 'Could not update service.',
    couldNotUpdateService: 'Could not update service status.',
    couldNotDeleteService: 'Could not delete service.',
    bookingDeleted: 'Booking deleted successfully.',
    couldNotLoadVendorBookings: 'Could not load vendor bookings right now.',
    couldNotUpdateBookingStatus: 'Could not update booking status.',
    couldNotDeleteBooking: 'Could not delete booking.',
    signInCheckAvailability: 'Please sign in to check service availability.',
    availabilityChecked: 'Availability checked.',
    couldNotCheckAvailability: 'Could not check availability right now.',
    loadedLatestBooking: 'Loaded your latest booking from this device.',
    couldNotLoadBookings: 'Could not load bookings. Check backend API and database migrations.',
    signInBeforeBooking: 'Please sign in before checking availability and booking.',
    enterNameEmail: 'Please enter your name and email or phone before booking.',
    selectValidQuantity: 'Please select a valid quantity.',
    serviceUnavailable: 'This service is not available at the moment.',
    bookingCreatedFor: 'Booking created for',
    bookingFailed: 'Booking failed.',
    rescheduleRequested: 'Reschedule request sent. Waiting for confirmation.',
    deleteBooking: 'Delete',
    confirmDeleteCompletedBooking: 'Delete this completed booking?',
    signInConfirmPackage: 'Please sign in before confirming your package booking.',
    justNow: 'Just now',
    last7Days: 'Last 7 days',
    last30Days: 'Last 30 days',
    last12Months: 'Last 12 months',
    janToDec: 'Jan - Dec',
    lastAndCurrentYear: 'Last & current year',
  },
  km: {
    signInToContinue: 'សូមចូលគណនីដើម្បីបន្តការកក់។',
    socialLoginFailed: 'ការចូលតាមបណ្តាញសង្គមបានបរាជ័យ។',
    socialLoginInvalid: 'ការចូលតាមបណ្តាញសង្គមមិនបានផ្តល់ព័ត៌មានអ្នកប្រើត្រឹមត្រូវទេ។',
    searchBookings: 'ស្វែងរកការកក់...',
    searchServices: 'ស្វែងរកសេវាកម្ម...',
    serviceBooking: 'ការកក់សេវាកម្ម',
    vendor: 'អ្នកផ្គត់ផ្គង់',
    customer: 'អតិថិជន',
    dateTbd: 'មិនទាន់កំណត់កាលបរិច្ឆេទ',
    loadEventsFailed: 'មិនអាចផ្ទុកព្រឹត្តិការណ៍ពី backend API បានទេ។ សូមបើកម៉ាស៊ីនមេ backend។',
    vendorAccountMissing: 'គណនីអ្នកផ្គត់ផ្គង់បាត់។',
    fillTitleLocationStart: 'សូមបំពេញចំណងជើង ទីតាំង និងថ្ងៃចាប់ផ្តើម។',
    qrCodeRequired: 'Please upload a payment QR code.',
    serviceCreated: 'សេវាកម្មត្រូវបានបង្កើតដោយជោគជ័យ ហើយអាចមើលឃើញដោយអ្នកប្រើហើយ។',
    serviceUpdated: 'បានធ្វើបច្ចុប្បន្នភាពសេវាកម្មដោយជោគជ័យ។',
    serviceDeleted: 'បានលុបសេវាកម្មដោយជោគជ័យ។',
    couldNotCreateService: 'មិនអាចបង្កើតសេវាកម្មបានទេ។',
    couldNotUpdateServiceDetails: 'មិនអាចធ្វើបច្ចុប្បន្នភាពសេវាកម្មបានទេ។',
    couldNotUpdateService: 'មិនអាចធ្វើបច្ចុប្បន្នភាពស្ថានភាពសេវាកម្មបានទេ។',
    couldNotDeleteService: 'មិនអាចលុបសេវាកម្មបានទេ។',
    bookingDeleted: 'បានលុបការកក់ដោយជោគជ័យ។',
    couldNotLoadVendorBookings: 'មិនអាចផ្ទុកការកក់របស់អ្នកផ្គត់ផ្គង់បានទេ។',
    couldNotUpdateBookingStatus: 'មិនអាចធ្វើបច្ចុប្បន្នភាពស្ថានភាពការកក់បានទេ។',
    couldNotDeleteBooking: 'មិនអាចលុបការកក់បានទេ។',
    signInCheckAvailability: 'សូមចូលគណនីដើម្បីពិនិត្យមើលពេលទំនេរ។',
    availabilityChecked: 'បានពិនិត្យពេលទំនេរហើយ។',
    couldNotCheckAvailability: 'មិនអាចពិនិត្យមើលពេលទំនេរឥឡូវនេះបានទេ។',
    loadedLatestBooking: 'បានផ្ទុកការកក់ចុងក្រោយរបស់អ្នកពីឧបករណ៍នេះ។',
    couldNotLoadBookings: 'មិនអាចផ្ទុកការកក់បានទេ។ សូមពិនិត្យ backend API និង migrations មូលដ្ឋានទិន្នន័យ។',
    signInBeforeBooking: 'សូមចូលគណនីមុនពិនិត្យពេលទំនេរ និងធ្វើការកក់។',
    enterNameEmail: 'សូមបញ្ចូលឈ្មោះ និងអ៊ីមែល ឬ លេខទូរស័ព្ទរបស់អ្នកមុនពេលកក់។',
    selectValidQuantity: 'សូមជ្រើសរើសចំនួនត្រឹមត្រូវ។',
    serviceUnavailable: 'សេវាកម្មនេះមិនមានទំនេរនៅពេលនេះទេ។',
    bookingCreatedFor: 'ការកក់ត្រូវបានបង្កើតសម្រាប់',
    bookingFailed: 'ការកក់បានបរាជ័យ។',
    rescheduleRequested: 'សំណើកំណត់ពេលឡើងវិញត្រូវបានផ្ញើ។ កំពុងរង់ចាំការបញ្ជាក់។',
    deleteBooking: 'លុប',
    confirmDeleteCompletedBooking: 'លុបការកក់ដែលបានបញ្ចប់នេះមែនទេ?',
    signInConfirmPackage: 'សូមចូលគណនីមុនពេលបញ្ជាក់ការកក់កញ្ចប់របស់អ្នក។',
    justNow: 'ឥឡូវនេះ',
    last7Days: '7 ថ្ងៃចុងក្រោយ',
    last30Days: '30 ថ្ងៃចុងក្រោយ',
    last12Months: '12 ខែចុងក្រោយ',
    janToDec: 'មករា - ធ្នូ',
    lastAndCurrentYear: 'ឆ្នាំមុន និងឆ្នាំនេះ',
  },
  zh: {
    signInToContinue: '请先登录再继续预订。',
    socialLoginFailed: '社交登录失败。',
    socialLoginInvalid: '社交登录未返回有效的用户信息。',
    searchBookings: '搜索预订...',
    searchServices: '搜索服务...',
    serviceBooking: '服务预订',
    vendor: '商家',
    customer: '客户',
    dateTbd: '日期待定',
    loadEventsFailed: '无法从后端 API 加载活动。请启动后端服务。',
    vendorAccountMissing: '缺少商家账户。',
    fillTitleLocationStart: '请填写标题、地点和开始日期。',
    qrCodeRequired: 'Please upload a payment QR code.',
    serviceCreated: '服务已成功创建，并且用户现在可以看到它。',
    serviceUpdated: '服务已成功更新。',
    serviceDeleted: '服务已成功删除。',
    couldNotCreateService: '无法创建服务。',
    couldNotUpdateServiceDetails: '无法更新服务。',
    couldNotUpdateService: '无法更新服务状态。',
    couldNotDeleteService: '无法删除服务。',
    bookingDeleted: '预订已成功删除。',
    couldNotLoadVendorBookings: '暂时无法加载商家预订。',
    couldNotUpdateBookingStatus: '无法更新预订状态。',
    couldNotDeleteBooking: '无法删除预订。',
    signInCheckAvailability: '请先登录再查看档期。',
    availabilityChecked: '档期已检查。',
    couldNotCheckAvailability: '暂时无法检查档期。',
    loadedLatestBooking: '已从此设备加载您最近的预订。',
    couldNotLoadBookings: '无法加载预订。请检查后端 API 和数据库迁移。',
    signInBeforeBooking: '请先登录再检查档期并预订。',
    enterNameEmail: '请在预订前输入您的姓名和邮箱或手机号。',
    selectValidQuantity: '请选择有效数量。',
    serviceUnavailable: '该服务当前不可用。',
    bookingCreatedFor: '已为以下项目创建预订：',
    bookingFailed: '预订失败。',
    rescheduleRequested: '改期请求已发送，正在等待确认。',
    deleteBooking: '删除',
    confirmDeleteCompletedBooking: '要删除这条已完成的预订吗？',
    signInConfirmPackage: '请先登录再确认您的套餐预订。',
    justNow: '刚刚',
    last7Days: '最近 7 天',
    last30Days: '最近 30 天',
    last12Months: '最近 12 个月',
    janToDec: '1月 - 12月',
    lastAndCurrentYear: '去年与今年',
  },
}
const { uiText } = useLanguageCopy(copyByLanguage)

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

function refreshLoggedInUserFromStorage() {
  const storedUser = getStoredUser()
  if (!storedUser) {
    if (loggedInUser.value) {
      loggedInUser.value = null
    }
    return
  }

  const currentSerialized = JSON.stringify(loggedInUser.value || null)
  const storedSerialized = JSON.stringify(storedUser)
  if (currentSerialized !== storedSerialized) {
    loggedInUser.value = storedUser
  }
}

function toggleView() {
  currentView.value = currentView.value === 'register' ? 'login' : 'register'
}

function onLoginSuccess(user) {
  loggedInUser.value = user
  if (!customerName.value?.trim()) customerName.value = user?.name ?? ''
  if (!customerEmail.value?.trim()) customerEmail.value = user?.email ?? ''
  if (!userPhone.value?.trim()) userPhone.value = user?.phone ?? ''
  const redirected = handlePostAuthRedirect()
  if (!redirected) {
    const role = String(user?.role || '').trim().toLowerCase()
    const target = role === 'vendor' || role === 'admin' ? '/legacy-app?page=dashboard' : '/legacy-app?page=bookings'
    router.push(target).catch(() => {})
  }
  void bootstrapAuthenticatedShell().then(() => {
    consumeMessageVendorTarget()
  })
}

function syncAuthenticatedUser(nextUser) {
  if (!nextUser || typeof nextUser !== 'object') return
  loggedInUser.value = {
    ...(loggedInUser.value || {}),
    ...nextUser,
  }
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

function consumeMessageVendorTarget() {
  const raw = sessionStorage.getItem(MESSAGE_VENDOR_TARGET_KEY)
  if (!raw) return
  sessionStorage.removeItem(MESSAGE_VENDOR_TARGET_KEY)

  let payload = null
  try {
    payload = JSON.parse(raw)
  } catch {
    return
  }

  if (!payload || typeof payload !== 'object') return

  const vendorIdRaw = Number(payload.vendorId || 0)
  const vendorId = Number.isFinite(vendorIdRaw) && vendorIdRaw > 0 ? vendorIdRaw : null
  const vendorEmail = String(payload.vendorEmail || '').trim()
  const eventIdRaw = Number(payload.eventId || 0)
  const eventId = Number.isFinite(eventIdRaw) && eventIdRaw > 0 ? eventIdRaw : null
  if (!vendorId && !vendorEmail && !eventId) return

  goToMessages({
    vendorId,
    vendorEmail: vendorEmail || undefined,
    vendorName: String(payload.vendorName || '').trim() || undefined,
    serviceName: String(payload.serviceName || '').trim() || undefined,
    eventId: eventId || undefined,
  })
}

function requireLogin(message = uiText.value.signInToContinue) {
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
const vendorDashboardTab = ref('overview')
const bookingFilter = ref('Upcoming')
const adminLegacyPages = ['dashboard', 'events', 'admin-bookings', 'vendors', 'customers', 'revenue', 'settings']
const allowedPages = ['dashboard', 'vendor', 'customization', 'availability', 'bookings', 'profile', 'messages', ...adminLegacyPages]
const allowedVendorTabs = ['about', 'services', 'reviews']
const allowedVendorDashboardTabs = ['overview', 'services', 'bookings', 'messages', 'income', 'settings']
const currentRole = computed(() => {
  const liveRole = String(loggedInUser.value?.role || '').trim().toLowerCase()
  if (liveRole) return liveRole
  return String(getStoredUser()?.role || '').trim().toLowerCase()
})
const isPlannerUser = computed(() => currentRole.value === 'user')
const isAdminAccount = computed(
  () => currentRole.value === 'admin',
)
const isVendorDashboardAccount = computed(
  () => currentRole.value === 'vendor',
)
const isVendorAccount = computed(() =>
  ['vendor', 'admin'].includes(currentRole.value),
)
const defaultLegacyPage = computed(() => (isVendorAccount.value ? 'dashboard' : 'bookings'))
const resolvedCurrentPage = computed(() => {
  const requestedPage = firstQueryValue(route.query.page)
  return normalizePage(requestedPage === undefined ? currentPage.value : requestedPage)
})

function firstQueryValue(value) {
  return Array.isArray(value) ? value[0] : value
}

function normalizePage(value) {
  const page = firstQueryValue(value)
  if (page === 'overview') return 'dashboard'
  if (page === 'users') return 'customers'
  if (adminLegacyPages.includes(page) && !isAdminAccount.value) return defaultLegacyPage.value
  if (!allowedPages.includes(page)) return defaultLegacyPage.value
  if (page === 'dashboard' && !isVendorAccount.value) return 'bookings'
  return page
}

function normalizeAuthView(value) {
  const view = String(value || '').trim().toLowerCase()
  if (view === 'register') return 'register'
  if (view === 'login') return 'login'
  return ''
}

function normalizeVendorTab(value) {
  const tab = firstQueryValue(value)
  return allowedVendorTabs.includes(tab) ? tab : 'about'
}

function normalizeVendorDashboardTab(value) {
  const tab = firstQueryValue(value)
  return allowedVendorDashboardTabs.includes(tab) ? tab : 'overview'
}

function applyRouteStateFromQuery(query) {
  const requestedPage = firstQueryValue(query.page)
  const nextPage = normalizePage(query.page)
  currentPage.value = nextPage
  if (!loggedInUser.value) {
    const authView = normalizeAuthView(firstQueryValue(query.view))
    if (authView) currentView.value = authView
  }
  if (nextPage === 'vendor') activeVendorTab.value = normalizeVendorTab(query.tab)
  if (isVendorDashboardAccount.value && nextPage === 'dashboard') {
    vendorDashboardTab.value = normalizeVendorDashboardTab(query.vtab)
  }

  if (loggedInUser.value && requestedPage === 'overview') {
    const nextQuery = { ...route.query }
    delete nextQuery.page
    if (isVendorDashboardAccount.value) {
      nextQuery.vtab = 'overview'
    } else {
      delete nextQuery.vtab
    }
    router.replace({ path: '/legacy-app', query: nextQuery }).catch(() => {})
  }
}

function syncRouteQueryFromState() {
  const nextQuery = {}
  if (currentPage.value !== defaultLegacyPage.value || (currentPage.value === 'dashboard' && isVendorAccount.value)) {
    nextQuery.page = currentPage.value
  }
  if (currentPage.value === 'vendor') nextQuery.tab = activeVendorTab.value
  if (isVendorDashboardAccount.value && currentPage.value === 'dashboard') nextQuery.vtab = vendorDashboardTab.value

  const currentPageQuery = firstQueryValue(route.query.page)
  const currentTabQuery = firstQueryValue(route.query.tab)
  const currentVendorTabQuery = firstQueryValue(route.query.vtab)

  const samePage = (currentPageQuery || undefined) === (nextQuery.page || undefined)
  const sameTab = (currentTabQuery || undefined) === (nextQuery.tab || undefined)
  const sameVendorTab = (currentVendorTabQuery || undefined) === (nextQuery.vtab || undefined)

  if (!samePage || !sameTab || !sameVendorTab) {
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
    localStorage.setItem('achar_social_error', String(message || uiText.value.socialLoginFailed))
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
      localStorage.setItem('achar_social_error', uiText.value.socialLoginInvalid)
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
const isSubmittingVendorService = ref(false)
const vendorServiceNotice = ref('')
const vendorServiceForm = ref({
  title: '',
  event_type: 'wedding',
  description: '',
  packages: [],
  qr_code_url: '',
  qr_code_file: null,
  service_mode: 'overall',
  image_url: '',
  image_file: null,
  location: '',
  location_latitude: null,
  location_longitude: null,
  starts_at: '',
  ends_at: '',
  price: 0,
  capacity: 1,
  is_active: true,
})

const vendorEvents = ref([])
const vendorBookings = ref([])
const vendorSettings = ref(null)
const vendorSettingsServiceId = ref('all')
const isLoadingVendorSettings = ref(false)
const isSavingVendorSettings = ref(false)
const isSubmittingVendorSubscriptionPayment = ref(false)
const vendorSettingsNotice = ref('')
const bookings = ref([])
const selectedQuantities = ref({})
const availabilityByEvent = ref({})
const checkingAvailabilityEventId = ref(null)
const brandLogoSrc = ref('/achar-logo.png')

const isLoadingEvents = ref(false)
const isLoadingVendorBookings = ref(false)
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
let latestCustomerBookingStatusNotificationId = null
let hasInitializedCustomerBookingStatusNotifications = false
let latestVendorCustomerCancellationNotificationId = null
let hasInitializedVendorCancellationAlerts = false
const {
  customerName,
  customerEmail,
  userPhone,
  userLocation,
  userProfileImageUrl,
  isSavingProfile,
  userLatitude,
  userLongitude,
  isDetectingLocation,
  userProfileNotice,
  userProfileDraft,
  userAvatarInitial,
  userLocationMapUrl,
  userLocationMapEmbedUrl,
  userLocationMapLinkUrl,
  loadUserProfile,
  updateProfileImageFile,
  removeProfileImage,
  goToProfile: openProfilePage,
  saveUserProfile,
  resetUserProfile,
  detectCurrentLocation,
} = useProfileFeature(notice, loggedInUser)

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
  isLoadingMessages,
  messagesError,
} = useVendorMessagesFeature(currentPage, loggedInUser, notice, vendorDashboardTab)
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
  userPhone,
  notice,
  bookingSubmittingEventId,
  checkEventAvailability,
  openCheckout: openServiceCheckout,
})
const vendorBindings = { activeVendorTab, customerName, customerEmail, selectedEventType }
const customizationBindings = {
  customizationEventType,
  customizationSearch,
  selectedCustomizationPackageId,
  customizationQuantity,
}
const bookingsBindings = { bookingFilter, bookingEventTypeFilter }
const profileBindings = { userProfileDraft, userProfileImageUrl, isSavingProfile, updateProfileImageFile, removeProfileImage }
const messagesBindings = { conversationSearch, selectedConversationId, composerText }
const availabilityBindings = { selectedAvailabilitySlot }
const notificationItems = computed(() =>
  notifications.value.map((item) => {
    const booking = item.booking || {}
    const event = booking.event || {}
    return {
      ...item,
      eventTitle: event.title || uiText.value.serviceBooking,
      eventDate: formatNotificationDate(event.starts_at),
      createdLabel: formatNotificationTime(item.created_at),
    }
  }),
)
const unreadNotificationCount = computed(
  () => notificationsUnreadCount.value || notifications.value.filter((item) => !item.is_read).length,
)

const navSearchPlaceholder = computed(() =>
  currentPage.value === 'bookings' ? uiText.value.searchBookings : uiText.value.searchServices,
)
const vendorLocationText = computed(() => {
  const firstWithLocation = vendorEvents.value.find((item) => item.location && item.location.trim())
  return firstWithLocation?.location || fallbackVendorLocation
})
const vendorMapEmbedUrl = computed(
  () => `https://www.google.com/maps?q=${encodeURIComponent(vendorLocationText.value)}&output=embed`,
)

const filteredPackages = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  return vendorEvents.value.filter((item) => {
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
  const unreadMessages = conversations.value.filter((item) => item.time === uiText.value.justNow).length
  return { totalBookings, upcomingBookings, completedBookings, unreadMessages }
})

const recentBookings = computed(() => bookings.value.slice(0, 3))
const vendorDisplayName = computed(
  () => String(loggedInUser.value?.name || vendorProfile.name || 'Vendor').trim() || 'Vendor',
)
const adminDisplayName = computed(
  () => String(loggedInUser.value?.name || 'Admin').trim() || 'Admin',
)
const messagesSummary = computed(() =>
  conversations.value.filter((item) => {
    if (item?.unread) return true
    const messages = Array.isArray(item?.messages) ? item.messages : []
    const lastMessage = messages[messages.length - 1]
    return lastMessage?.from === 'them'
  }).length,
)
function getIncomeDateParts(value) {
  const date = value ? new Date(value) : null
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return null
  return {
    date,
    year: date.getFullYear(),
    month: date.getMonth(),
    day: date.getDate(),
  }
}

function createDailyIncomeSeries(bookings, days) {
  const today = new Date()
  const buckets = Array.from({ length: days }, (_, index) => {
    const date = new Date(today)
    date.setHours(0, 0, 0, 0)
    date.setDate(today.getDate() - (days - index - 1))
    return {
      key: `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`,
      label: date.toLocaleDateString('en-US', days <= 7 ? { weekday: 'short' } : { month: 'short', day: 'numeric' }),
      fullLabel: date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
      value: 0,
    }
  })

  const bucketMap = new Map(buckets.map((item) => [item.key, item]))
  bookings.forEach((booking) => {
    const parts = getIncomeDateParts(booking.income_date)
    if (!parts) return
    const key = `${parts.year}-${parts.month}-${parts.day}`
    const target = bucketMap.get(key)
    if (target) target.value += Number(booking.total_amount || 0)
  })

  return buckets
}

function getWeekStartMonday(date) {
  const start = new Date(date)
  const day = start.getDay()
  const diff = (day + 6) % 7
  start.setDate(start.getDate() - diff)
  start.setHours(0, 0, 0, 0)
  return start
}

function createWeeklyIncomeSeries(bookings) {
  const weekStart = getWeekStartMonday(new Date())
  const buckets = Array.from({ length: 7 }, (_, index) => {
    const date = new Date(weekStart)
    date.setDate(weekStart.getDate() + index)
    return {
      key: `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}`,
      label: date.toLocaleDateString('en-US', { weekday: 'short' }),
      fullLabel: date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      }),
      value: 0,
    }
  })

  const bucketMap = new Map(buckets.map((item) => [item.key, item]))
  bookings.forEach((booking) => {
    const parts = getIncomeDateParts(booking.income_date)
    if (!parts) return
    const key = `${parts.year}-${parts.month}-${parts.day}`
    const target = bucketMap.get(key)
    if (target) target.value += Number(booking.total_amount || 0)
  })

  return buckets
}

function createCalendarYearIncomeSeries(bookings, year = new Date().getFullYear()) {
  const buckets = Array.from({ length: 12 }, (_, index) => {
    const date = new Date(year, index, 1)
    return {
      key: `${date.getFullYear()}-${date.getMonth()}`,
      label: date.toLocaleDateString('en-US', { month: 'short' }),
      fullLabel: date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
      value: 0,
    }
  })

  const bucketMap = new Map(buckets.map((item) => [item.key, item]))
  bookings.forEach((booking) => {
    const parts = getIncomeDateParts(booking.income_date)
    if (!parts) return
    const key = `${parts.year}-${parts.month}`
    const target = bucketMap.get(key)
    if (target) target.value += Number(booking.total_amount || 0)
  })

  return buckets
}

function createYearComparisonSeries(bookings, year = new Date().getFullYear()) {
  const years = [year - 1, year]
  const buckets = years.map((targetYear) => {
    const date = new Date(targetYear, 0, 1)
    return {
      key: `${targetYear}`,
      label: date.toLocaleDateString('en-US', { year: 'numeric' }),
      fullLabel: `Calendar year ${targetYear}`,
      value: 0,
    }
  })

  const bucketMap = new Map(buckets.map((item) => [item.key, item]))
  bookings.forEach((booking) => {
    const parts = getIncomeDateParts(booking.income_date)
    if (!parts) return
    const target = bucketMap.get(String(parts.year))
    if (target) target.value += Number(booking.total_amount || 0)
  })

  return buckets
}

function createMonthlyIncomeSeries(bookings, months = 12) {
  const today = new Date()
  const buckets = Array.from({ length: months }, (_, index) => {
    const date = new Date(today.getFullYear(), today.getMonth() - (months - index - 1), 1)
    return {
      key: `${date.getFullYear()}-${date.getMonth()}`,
      label: date.toLocaleDateString('en-US', { month: 'short' }),
      fullLabel: date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
      value: 0,
    }
  })

  const bucketMap = new Map(buckets.map((item) => [item.key, item]))
  bookings.forEach((booking) => {
    const parts = getIncomeDateParts(booking.income_date)
    if (!parts) return
    const key = `${parts.year}-${parts.month}`
    const target = bucketMap.get(key)
    if (target) target.value += Number(booking.total_amount || 0)
  })

  return buckets
}

const vendorIncome = computed(() => {
  const confirmedBookings = vendorBookings.value.filter(
    (item) => String(item.status || '').toLowerCase() === 'confirmed',
  )
  const total = confirmedBookings.reduce((sum, item) => sum + Number(item.total_amount || 0), 0)
  const confirmedCount = confirmedBookings.length
  const newBookings = vendorBookings.value.filter(
    (item) => String(item.status || '').toLowerCase() === 'pending',
  ).length
  const activeServices = vendorEvents.value.filter((item) => item.isActive).length
  const now = new Date()
  const thisMonth = confirmedBookings.reduce((sum, item) => {
    const parts = getIncomeDateParts(item.income_date)
    if (!parts) return sum
    return parts.year === now.getFullYear() && parts.month === now.getMonth()
      ? sum + Number(item.total_amount || 0)
      : sum
  }, 0)
  const thisYear = confirmedBookings.reduce((sum, item) => {
    const parts = getIncomeDateParts(item.income_date)
    if (!parts) return sum
    return parts.year === now.getFullYear() ? sum + Number(item.total_amount || 0) : sum
  }, 0)
  const weekSeries = createWeeklyIncomeSeries(confirmedBookings)
  const monthSeries = createCalendarYearIncomeSeries(confirmedBookings, now.getFullYear())
  const yearSeries = createYearComparisonSeries(confirmedBookings, now.getFullYear())
  const periodTotal = (series) => series.reduce((sum, item) => sum + Number(item.value || 0), 0)
  return {
    total,
    confirmedCount,
    newBookings,
    thisMonth,
    thisYear,
    activeServices,
    periods: {
      week: {
        label: uiText.value.last7Days,
        points: weekSeries,
        total: periodTotal(weekSeries),
      },
      month: {
        label: uiText.value.janToDec,
        points: monthSeries,
        total: periodTotal(monthSeries),
      },
      year: {
        label: uiText.value.lastAndCurrentYear,
        points: yearSeries,
        total: periodTotal(yearSeries),
      },
    },
  }
})

function normalizeBookingEmail(value) {
  return String(value || '').trim().toLowerCase()
}

function normalizeBookingPhone(value) {
  return String(value || '').replace(/\D+/g, '')
}

function splitBookingContact(value) {
  const trimmed = String(value || '').trim()
  if (!trimmed) {
    return { email: '', phone: '' }
  }

  const looksLikeEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)
  return {
    email: looksLikeEmail ? trimmed.toLowerCase() : '',
    phone: looksLikeEmail ? '' : trimmed,
  }
}

function formatBookingIdentityNote(row, fallbackEmail = '', fallbackPhone = '') {
  const contact = row?.customerEmail || row?.customerPhone || fallbackEmail || fallbackPhone || ''
  return [row?.customerName || 'Guest User', contact].filter(Boolean).join(' | ')
}

function decorateCustomerBookingActions(row) {
  const canDelete = Boolean(row?.canDelete)
  const canCancel = Boolean(row?.canCancel)

  return {
    ...row,
    canDelete,
    canCancel,
    secondaryBtn: canCancel ? 'Cancel Booking' : (canDelete ? uiText.value.deleteBooking : row?.secondaryBtn || 'Reschedule'),
  }
}

function getLocalBookingsByIdentity({ email = '', phone = '' } = {}) {
  const normalizedEmail = normalizeBookingEmail(email)
  const normalizedPhone = normalizeBookingPhone(phone)
  if (!normalizedEmail && !normalizedPhone) return []

  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY)
    if (!raw) return []
    const rows = JSON.parse(raw)
    if (!Array.isArray(rows)) return []

    return rows
      .filter((row) => {
        const matchesEmail =
          normalizedEmail && normalizeBookingEmail(row?.customerEmail) === normalizedEmail
        const matchesPhone =
          normalizedPhone && normalizeBookingPhone(row?.customerPhone) === normalizedPhone
        return matchesEmail || matchesPhone
      })
      .map((row, index) => {
        const bookingDate =
          row?.requestedEventDate ||
          row?.requested_event_date ||
          row?.dateLabel ||
          row?.eventDate ||
          ''
        const eventType = normalizeBookingEventTypeKey(
          row?.eventType,
          row?.requestedEventType,
          row?.requested_event_type,
        )
        const bookedItems = Array.isArray(row?.booked_items) ? row.booked_items : []
        const bookingStatus = String(row?.status || 'confirmed').trim().toLowerCase()
        const type = deriveBookingType(bookingStatus, bookingDate)
        const canDelete = type === 'Completed'
        const canCancel = type === 'Upcoming' && ['pending', 'confirmed'].includes(bookingStatus)

        return decorateCustomerBookingActions({
          id: row.id || `local-${index}`,
          vendor: row.vendor || vendorProfile.name,
          service: summarizeBookedServices(bookedItems, row.service || uiText.value.serviceBooking),
          date: formatDateTime(bookingDate),
          metaLabel: 'Event Type',
          metaValue: eventTypeMap[eventType] || 'Other',
          placeLabel: 'Total',
          placeValue: `$${Number(row.total || 0).toLocaleString()}`,
          status: row.status || 'Confirmed',
          statusClass: row.statusClass || 'confirmed',
          type,
          eventType,
          eventId: row.eventId || null,
          image:
            row.image ||
            'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=760&q=80',
          bookedItems,
          canDelete,
          canCancel,
          primaryBtn: 'View Details',
          secondaryBtn: 'Reschedule',
          note: formatBookingIdentityNote(row, normalizedEmail, phone),
        })
      })
  } catch {
    return []
  }
}

function deleteLocalBookingEntry(target) {
  if (!target) return

  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY)
    if (!raw) return

    const rows = JSON.parse(raw)
    if (!Array.isArray(rows)) return

    const targetId = String(target?.id || '').trim()
    const targetKey = getBookingMatchKey(target)
    const remainingRows = rows.filter((row) => {
      const rowId = String(row?.id || '').trim()
      if (targetId && rowId && rowId === targetId) {
        return false
      }

      return getBookingMatchKey(row) !== targetKey
    })

    if (remainingRows.length === rows.length) return

    if (remainingRows.length === 0) {
      localStorage.removeItem(LOCAL_BOOKINGS_STORAGE_KEY)
      return
    }

    localStorage.setItem(LOCAL_BOOKINGS_STORAGE_KEY, JSON.stringify(remainingRows))
  } catch {
    // Ignore local-storage cleanup failures.
  }
}

function buildCheckoutItemsFromBooking(item) {
  const bookedItems = Array.isArray(item?.bookedItems) ? item.bookedItems : []
  if (bookedItems.length) {
    return bookedItems.map((entry) => ({
      type: entry?.type || 'service',
      name: entry?.name || item?.service || uiText.value.serviceBooking,
      description: entry?.description || '',
      qty: Math.max(1, Number(entry?.qty || item?.quantity || 1)),
      unitPrice: Number(entry?.unitPrice || 0),
      totalPrice: Number(entry?.totalPrice || 0),
    }))
  }

  const quantity = Math.max(1, Number(item?.quantity || 1))
  const totalPrice = Number(item?.servicePrice || 0)

  return [
    {
      type: 'service',
      name: item?.service || uiText.value.serviceBooking,
      description: '',
      qty: quantity,
      unitPrice: quantity > 0 ? Number((totalPrice / quantity).toFixed(2)) : totalPrice,
      totalPrice,
    },
  ]
}

function openServiceCheckout(selection = {}) {
  const quantity = Math.max(1, Number(selection.quantity || 1))
  const bookedItems = Array.isArray(selection.booked_items) ? selection.booked_items : []
  const items = bookedItems.length
    ? bookedItems.map((entry) => ({
        type: entry?.type || 'service',
        name: entry?.name || selection.service_name || selection.vendorTitle || uiText.value.serviceBooking,
        description: entry?.description || '',
        qty: Math.max(1, Number(entry?.qty || quantity)),
        unitPrice: Number(entry?.unitPrice || entry?.price || 0),
        totalPrice: Number(entry?.totalPrice || 0),
      }))
    : buildCheckoutItemsFromBooking({
        service: selection.service_name || selection.vendorTitle || uiText.value.serviceBooking,
        quantity,
        servicePrice: Number(selection.total_amount || 0),
      })
  const requestedEventDate = String(
    selection.requested_event_date || selection.eventDate || selection.startsAt || '',
  )
    .trim()
    .slice(0, 10)
  const payload = {
    bookingId: selection.booking_id || selection.bookingId || null,
    vendorTitle: selection.service_name || selection.vendorTitle || uiText.value.serviceBooking,
    vendorName: selection.vendor_name || selection.vendorName || vendorProfile.name,
    vendorEmail: selection.vendor_email || selection.vendorEmail || '',
    eventId: selection.event_id || selection.eventId || null,
    image: selection.image_url || selection.image || '',
    fullName: customerName.value || loggedInUser.value?.name || '',
    email: customerEmail.value || loggedInUser.value?.email || '',
    phone: userPhone.value || loggedInUser.value?.phone || '',
    location: userLocation.value || loggedInUser.value?.location || selection.location || '',
    eventDate: requestedEventDate,
    guests: quantity,
    notes: selection.notes || '',
    requestedEventType: selection.requested_event_type || selection.eventType || 'other',
    items,
    qrCodeUrl: selection.qr_code_url || selection.qrCodeUrl || '',
    existingBookingStatus: selection.existingBookingStatus || selection.status || 'pending',
    existingPaymentStatus: selection.existingPaymentStatus || selection.payment_status || 'pending',
    paymentToken: selection.payment_token || selection.paymentToken || '',
    totalAmount:
      Number(selection.total_amount || 0) ||
      Number(items.reduce((sum, item) => sum + Number(item.totalPrice || 0), 0).toFixed(2)),
    depositAmount: Number(selection.deposit_amount || 0),
    serviceFeeAmount: Number(selection.service_fee_amount || 0),
    balanceDueAmount: Number(selection.balance_due_amount || 0),
    vendorCancellationDeadlineAt: selection.vendor_cancellation_deadline_at || null,
    refundAmount: Number(selection.refund_amount || 0),
    customerCompensationAmount: Number(selection.customer_compensation_amount || 0),
  }

  sessionStorage.setItem('achar_prebook_checkout', JSON.stringify(payload))
  router.push('/checkout').catch(() => {})
}

function openApprovedBookingPayment(item) {
  if (!item?.id) return

  openServiceCheckout({
    booking_id: item.id,
    service_name: item.service || uiText.value.serviceBooking,
    vendor_name: item.vendor || vendorProfile.name,
    vendor_email: item.vendorEmail || '',
    event_id: item.eventId || null,
    image_url: item.image || '',
    requested_event_date: item.requestedEventDate || '',
    quantity: Math.max(1, Number(item.quantity || 1)),
    requested_event_type: item.eventType || 'other',
    booked_items: buildCheckoutItemsFromBooking(item),
    qr_code_url: item.qrCodeUrl || '',
    status: item.bookingStatus || 'pending',
    payment_status: item.paymentStatus || 'pending',
    total_amount: Number(item.servicePrice || 0),
    deposit_amount: Number(item.depositAmount || 0),
    service_fee_amount: Number(item.serviceFeeAmount || 0),
    balance_due_amount: Number(item.balanceDueAmount || 0),
    refund_amount: Number(item.refundAmount || 0),
    customer_compensation_amount: Number(item.customerCompensationAmount || 0),
    vendor_cancellation_deadline_at: item.vendorCancellationDeadlineAt || null,
  })
}

function clearLocalBookingsByIdentity({ email = '', phone = '' } = {}) {
  const normalizedEmail = normalizeBookingEmail(email)
  const normalizedPhone = normalizeBookingPhone(phone)
  if (!normalizedEmail && !normalizedPhone) return

  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY)
    if (!raw) return

    const rows = JSON.parse(raw)
    if (!Array.isArray(rows)) return

    const remainingRows = rows.filter(
      (row) =>
        !(
          (normalizedEmail && normalizeBookingEmail(row?.customerEmail) === normalizedEmail) ||
          (normalizedPhone && normalizeBookingPhone(row?.customerPhone) === normalizedPhone)
        ),
    )

    if (remainingRows.length === rows.length) return

    if (remainingRows.length === 0) {
      localStorage.removeItem(LOCAL_BOOKINGS_STORAGE_KEY)
      return
    }

    localStorage.setItem(LOCAL_BOOKINGS_STORAGE_KEY, JSON.stringify(remainingRows))
  } catch {
    // Ignore local-storage cleanup failures.
  }
}

function resolveBookingLookup() {
  const userId = Number(loggedInUser.value?.id || 0)
  const typedContact = splitBookingContact(customerEmail.value)
  const typedEmail = normalizeBookingEmail(typedContact.email)
  const loggedEmail = normalizeBookingEmail(loggedInUser.value?.email)
  const lastEmail = normalizeBookingEmail(localStorage.getItem(LAST_BOOKING_EMAIL_KEY) || '')
  const primaryEmail = typedEmail || loggedEmail || lastEmail
  const fallbackEmail = lastEmail && lastEmail !== primaryEmail ? lastEmail : ''
  const primaryPhone =
    String(typedContact.phone || userPhone.value || loggedInUser.value?.phone || localStorage.getItem(LAST_BOOKING_PHONE_KEY) || '').trim()

  return {
    userId: userId > 0 ? userId : null,
    email: primaryEmail,
    fallbackEmail,
    phone: primaryPhone,
    hasIdentity: Boolean((userId > 0) || primaryEmail || primaryPhone),
  }
}

function normalizeBookingRequestPayload(payload = {}) {
  const contact = splitBookingContact(payload?.customer_email)
  return {
    ...payload,
    customer_email: contact.email || undefined,
    customer_phone: payload?.customer_phone || contact.phone || userPhone.value.trim() || undefined,
  }
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
  if (!value) return uiText.value.dateTbd
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return uiText.value.dateTbd
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

  const email = String(user.email || customerEmail.value || '').trim().toLowerCase()
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

function getLatestCustomerBookingStatusNotificationId(rows = []) {
  if (isVendorAccount.value || !Array.isArray(rows)) return null

  const match = rows.find(
    (item) =>
      String(item?.kind || '').trim().toLowerCase() === 'booking_status_changed' &&
      String(item?.recipient_type || '').trim().toLowerCase() === 'user',
  )

  return Number(match?.id || 0) || null
}

function getLatestVendorCustomerCancellationNotification(rows = []) {
  if (!isVendorAccount.value || !Array.isArray(rows)) return null

  return (
    rows.find((item) => {
      const kind = String(item?.kind || '').trim().toLowerCase()
      const recipientType = String(item?.recipient_type || '').trim().toLowerCase()
      const message = String(item?.message || '').trim().toLowerCase()

      return (
        kind === 'booking_status_changed' &&
        recipientType === 'vendor' &&
        message.includes('cancelled by the customer')
      )
    }) || null
  )
}

async function syncCustomerBookingsFromNotifications(rows) {
  if (isVendorAccount.value || !resolveBookingLookup().hasIdentity) return

  const latestNotificationId = getLatestCustomerBookingStatusNotificationId(rows)

  if (!hasInitializedCustomerBookingStatusNotifications) {
    latestCustomerBookingStatusNotificationId = latestNotificationId
    hasInitializedCustomerBookingStatusNotifications = true
    return
  }

  if (!latestNotificationId || latestNotificationId === latestCustomerBookingStatusNotificationId) {
    return
  }

  latestCustomerBookingStatusNotificationId = latestNotificationId
  await loadBookings({ silent: true })
}

async function syncVendorCancellationAlertsFromNotifications(rows) {
  if (!isVendorAccount.value) return

  const latestNotification = getLatestVendorCustomerCancellationNotification(rows)
  const latestNotificationId = Number(latestNotification?.id || 0) || null

  if (!hasInitializedVendorCancellationAlerts) {
    latestVendorCustomerCancellationNotificationId = latestNotificationId
    hasInitializedVendorCancellationAlerts = true
    return
  }

  if (
    !latestNotificationId ||
    latestNotificationId === latestVendorCustomerCancellationNotificationId
  ) {
    return
  }

  latestVendorCustomerCancellationNotificationId = latestNotificationId
  await loadVendorBookings()

  const warningMessage =
    String(latestNotification?.message || '').trim() ||
    'Warning: a customer cancelled their booking.'

  vendorServiceNotice.value = warningMessage

  if (typeof window !== 'undefined' && typeof window.alert === 'function') {
    window.alert(`Warning: Customer cancelled a booking.\n\n${warningMessage}`)
  }
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
    await syncCustomerBookingsFromNotifications(rows)
    await syncVendorCancellationAlertsFromNotifications(rows)
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

async function loadEvents(options = {}) {
  const { silent = false } = options
  if (!silent) {
    isLoadingEvents.value = true
  }
  try {
    const vendorUserId = Number(loggedInUser.value?.id || 0)
    const result =
      isVendorAccount.value && Number.isFinite(vendorUserId) && vendorUserId > 0
        ? await apiGet('vendor/services', { vendor_user_id: vendorUserId })
        : await apiGet('events')
    const rows = Array.isArray(result.data) ? result.data : []
    vendorEvents.value = rows.map((row) => mapApiEvent(row, eventTypeMap))
    availabilityByEvent.value = {}

    const initialQuantities = {}
    vendorEvents.value.forEach((item) => {
      initialQuantities[item.id] = selectedQuantities.value[item.id] || 1
    })
    selectedQuantities.value = initialQuantities
  } catch (error) {
    notice.value = uiText.value.loadEventsFailed
  } finally {
    if (!silent) {
      isLoadingEvents.value = false
    }
  }
}

function upsertVendorEvent(row) {
  if (!row) return
  const mapped = mapApiEvent(row, eventTypeMap)
  const index = vendorEvents.value.findIndex((item) => item.id === mapped.id)
  if (index >= 0) {
    vendorEvents.value.splice(index, 1, mapped)
  } else {
    vendorEvents.value = [mapped, ...vendorEvents.value]
  }
  selectedQuantities.value = {
    ...selectedQuantities.value,
    [mapped.id]: selectedQuantities.value[mapped.id] || 1,
  }
}

const weekDayOrder = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']

function defaultVendorSettings() {
  const timezone =
    Intl.DateTimeFormat().resolvedOptions().timeZone ||
    String(import.meta.env.VITE_APP_TIMEZONE || 'UTC')

  return {
    timezone,
    weekly_schedule: weekDayOrder.map((day) => ({
      day,
      closed: false,
      slots: [{ start: '09:00', end: '17:00' }],
    })),
    blocked_dates: [],
    blocked_ranges: [],
    subscription_plan_code: null,
    subscription_plan_name: '',
    subscription_price_amount: 0,
    subscription_duration_months: null,
    subscription_service_limit: null,
    subscription_package_limit: null,
    subscription_status: 'inactive',
    subscription_started_at: null,
    subscription_paid_at: null,
    subscription_expires_at: null,
    subscription_is_active: false,
    subscription_payment_qr_url: '',
    subscription_payment_note: '',
  }
}

function normalizeVendorSettings(payload = {}) {
  const base = defaultVendorSettings()
  const incomingSchedule = Array.isArray(payload.weekly_schedule) ? payload.weekly_schedule : []
  const normalizedSchedule = weekDayOrder.map((day) => {
    const entry = incomingSchedule.find((item) => item?.day === day) || {}
    const slot = Array.isArray(entry.slots) && entry.slots[0] ? entry.slots[0] : { start: '09:00', end: '17:00' }
    return {
      day,
      closed: Boolean(entry.closed),
      slots: entry.closed ? [] : [{ start: slot.start || '09:00', end: slot.end || '17:00' }],
    }
  })

  const blockedDates = Array.isArray(payload.blocked_dates) ? payload.blocked_dates.filter(Boolean) : []
  const blockedRanges = Array.isArray(payload.blocked_ranges) ? payload.blocked_ranges : []

  return {
    timezone: payload.timezone || base.timezone,
    weekly_schedule: normalizedSchedule,
    blocked_dates: blockedDates,
    blocked_ranges: blockedRanges,
    subscription_plan_code: payload.subscription_plan_code || base.subscription_plan_code,
    subscription_plan_name: payload.subscription_plan_name || base.subscription_plan_name,
    subscription_price_amount: Number(payload.subscription_price_amount || 0),
    subscription_duration_months:
      payload.subscription_duration_months === null || payload.subscription_duration_months === undefined
        ? base.subscription_duration_months
        : Number(payload.subscription_duration_months),
    subscription_service_limit:
      payload.subscription_service_limit === null || payload.subscription_service_limit === undefined
        ? base.subscription_service_limit
        : Number(payload.subscription_service_limit),
    subscription_package_limit:
      payload.subscription_package_limit === null || payload.subscription_package_limit === undefined
        ? base.subscription_package_limit
        : Number(payload.subscription_package_limit),
    subscription_status: payload.subscription_status || base.subscription_status,
    subscription_started_at: payload.subscription_started_at || base.subscription_started_at,
    subscription_paid_at: payload.subscription_paid_at || base.subscription_paid_at,
    subscription_expires_at: payload.subscription_expires_at || base.subscription_expires_at,
    subscription_is_active: Boolean(payload.subscription_is_active),
    subscription_payment_qr_url: payload.subscription_payment_qr_url || base.subscription_payment_qr_url,
    subscription_payment_note: payload.subscription_payment_note || base.subscription_payment_note,
  }
}

async function loadVendorSettings({ eventId = null, silent = false } = {}) {
  if (!isVendorAccount.value) return
  isLoadingVendorSettings.value = true
  if (!silent) vendorSettingsNotice.value = ''
  try {
    const vendorUserId = Number(loggedInUser.value?.id || 0)
    if (!Number.isFinite(vendorUserId) || vendorUserId <= 0) {
      throw new Error('Vendor account could not be identified.')
    }

    const targetEventId =
      eventId !== null && eventId !== undefined
        ? eventId
        : vendorSettingsServiceId.value === 'all'
          ? null
          : Number(vendorSettingsServiceId.value)

    const result = await apiGet('vendor/settings', {
      vendor_user_id: vendorUserId,
      ...(targetEventId ? { event_id: targetEventId } : {}),
    })
    const settings = result?.settings || result?.data || result
    vendorSettings.value = normalizeVendorSettings(settings)
    if (targetEventId !== null && targetEventId !== undefined) {
      vendorSettingsServiceId.value = targetEventId
    }
  } catch (error) {
    vendorSettingsNotice.value = error?.message || 'Could not load vendor settings.'
    if (!vendorSettings.value) {
      vendorSettings.value = defaultVendorSettings()
    }
  } finally {
    isLoadingVendorSettings.value = false
  }
}

async function saveVendorSettings(payload) {
  if (!isVendorAccount.value) return
  isSavingVendorSettings.value = true
  vendorSettingsNotice.value = ''
  try {
    const vendorUserId = Number(loggedInUser.value?.id || 0)
    if (!Number.isFinite(vendorUserId) || vendorUserId <= 0) {
      throw new Error('Vendor account could not be identified.')
    }

    const targetEventId =
      payload?.event_id !== undefined
        ? payload.event_id
        : vendorSettingsServiceId.value === 'all'
          ? null
          : Number(vendorSettingsServiceId.value)
    const normalizedPayload = {
      ...payload,
      vendor_user_id: vendorUserId,
      event_id: targetEventId,
    }
    const result = await apiPatch('vendor/settings', normalizedPayload)
    const settings = result?.settings || result?.data || result
    vendorSettings.value = normalizeVendorSettings(settings)
    vendorSettingsNotice.value = uiText.value.settingsSaved || 'Settings saved.'
  } catch (error) {
    vendorSettingsNotice.value = error?.message || 'Could not save settings.'
    throw error
  } finally {
    isSavingVendorSettings.value = false
  }
}

async function submitVendorSubscriptionPayment() {
  if (!isVendorAccount.value) return
  isSubmittingVendorSubscriptionPayment.value = true
  vendorSettingsNotice.value = ''
  try {
    const vendorUserId = Number(loggedInUser.value?.id || 0)
    if (!Number.isFinite(vendorUserId) || vendorUserId <= 0) {
      throw new Error('Vendor account could not be identified.')
    }

    const result = await apiPost('vendor/settings/subscription/complete-payment', {
      vendor_user_id: vendorUserId,
    })
    const settings = result?.settings || result?.data || result
    vendorSettings.value = normalizeVendorSettings(settings)
    vendorSettingsNotice.value =
      result?.message || 'Payment submitted. An admin will verify the bank transfer and approve your vendor account soon.'
  } catch (error) {
    vendorSettingsNotice.value = error?.message || 'Could not submit your vendor payment confirmation.'
    throw error
  } finally {
    isSubmittingVendorSubscriptionPayment.value = false
  }
}

function resetVendorServiceForm() {
  vendorServiceForm.value = {
    id: null,
    title: '',
    event_type: 'wedding',
    description: '',
    packages: [],
    qr_code_url: '',
    qr_code_file: null,
    service_mode: 'overall',
    image_url: '',
    image_file: null,
    location: '',
    location_latitude: null,
    location_longitude: null,
    starts_at: '',
    ends_at: '',
    price: 0,
    capacity: 1,
    is_active: true,
  }
}

function clearGuestEventsCache() {
  try {
    sessionStorage.removeItem(EVENTS_CACHE_KEY)
  } catch {
    // ignore storage errors
  }
}

async function submitVendorService() {
  if (!isVendorAccount.value) return

  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!Number.isFinite(vendorUserId) || vendorUserId < 1) {
    vendorServiceNotice.value = uiText.value.vendorAccountMissing
    return
  }

  const subscriptionStatus = String(vendorSettings.value?.subscription_status || 'inactive').trim().toLowerCase()
  if (subscriptionStatus !== 'active') {
    vendorServiceNotice.value =
      subscriptionStatus === 'pending_payment'
        ? 'Your vendor account was created, but you cannot add services or packages until you complete the payment and the admin approves you.'
        : subscriptionStatus === 'pending_approval'
          ? 'Your payment was submitted. You still cannot add services or packages until the admin approves your vendor account.'
          : 'Your vendor account cannot add services or packages until the admin approves your vendor plan.'
    return
  }

  const imageUrl = String(vendorServiceForm.value.image_url || '').trim()
  const imageFile = vendorServiceForm.value.image_file instanceof File ? vendorServiceForm.value.image_file : null
  const qrCodeUrl = String(vendorServiceForm.value.qr_code_url || '').trim()
  const qrCodeFile = vendorServiceForm.value.qr_code_file instanceof File ? vendorServiceForm.value.qr_code_file : null
  const normalizedPackages = Array.isArray(vendorServiceForm.value.packages)
    ? vendorServiceForm.value.packages
        .map((pkg) => ({
          name: String(pkg?.name || '').trim(),
          price: Number(pkg?.price || 0),
          details: String(pkg?.details || '').trim(),
        }))
        .filter((pkg) => pkg.name || pkg.details)
    : []

  const normalizedPayload = {
    vendor_user_id: vendorUserId,
    title: String(vendorServiceForm.value.title || '').trim(),
    event_type: String(vendorServiceForm.value.event_type || 'other'),
    description: String(vendorServiceForm.value.description || '').trim(),
    packages: normalizedPackages,
    qr_code_url: qrCodeUrl,
    service_mode: String(vendorServiceForm.value.service_mode || 'overall'),
    image_url: imageUrl,
    location: String(vendorServiceForm.value.location || '').trim(),
    starts_at: vendorServiceForm.value.starts_at || null,
    ends_at: vendorServiceForm.value.ends_at || null,
    price:
      String(vendorServiceForm.value.service_mode || 'overall') === 'package'
        ? sumPackageServicePrices(normalizedPackages)
        : Number(vendorServiceForm.value.price || 0),
    capacity: Number(vendorServiceForm.value.capacity || 0),
    is_active: Boolean(vendorServiceForm.value.is_active),
  }

  if (!normalizedPayload.title || !normalizedPayload.location || !normalizedPayload.starts_at) {
    vendorServiceNotice.value = uiText.value.fillTitleLocationStart
    return
  }

  if (!qrCodeFile && !qrCodeUrl) {
    vendorServiceNotice.value = uiText.value.qrCodeRequired
    return
  }

  isSubmittingVendorService.value = true
  vendorServiceNotice.value = ''
  const serviceId = Number(vendorServiceForm.value.id || 0)
  try {
    const isEditingService = Number.isFinite(serviceId) && serviceId > 0
    const hasBinaryUpload = Boolean(imageFile || qrCodeFile)
    const payload = hasBinaryUpload
      ? (() => {
          const formData = new FormData()
          Object.entries(normalizedPayload).forEach(([key, value]) => {
            if (key === 'image_url' && imageFile) {
              return
            }
            if (key === 'qr_code_url' && qrCodeFile) {
              return
            }
            if (key === 'packages') {
              formData.append(key, JSON.stringify(Array.isArray(value) ? value : []))
              return
            }
            if (value !== null && value !== undefined) {
              if (key === 'is_active') {
                formData.append(key, value ? '1' : '0')
                return
              }
              formData.append(key, value)
            }
          })
          if (imageFile) {
            formData.append('image', imageFile)
          }
          if (qrCodeFile) {
            formData.append('qr_code', qrCodeFile)
          }
          return formData
        })()
      : normalizedPayload

    if (Number.isFinite(serviceId) && serviceId > 0) {
      const result = await apiPatch(`vendor/services/${serviceId}`, payload)
      upsertVendorEvent(result?.data || result)
    } else {
      const result = await apiPost('vendor/services', payload)
      upsertVendorEvent(result?.data || result)
    }
    await loadEvents()
    clearGuestEventsCache()
    selectedEventType.value = normalizedPayload.event_type
    resetVendorServiceForm()
    vendorServiceNotice.value = `${normalizedPayload.title || uiText.value.serviceBooking} - ${
      isEditingService ? uiText.value.serviceUpdated : uiText.value.serviceCreated
    }`
  } catch (error) {
    vendorServiceNotice.value =
      error?.message ||
      (serviceId ? uiText.value.couldNotUpdateServiceDetails : uiText.value.couldNotCreateService)
  } finally {
    isSubmittingVendorService.value = false
  }
}

async function toggleVendorServiceActive(item) {
  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!item?.id || !Number.isFinite(vendorUserId) || vendorUserId < 1) return

  try {
    await apiPatch(`vendor/services/${item.id}`, {
      vendor_user_id: vendorUserId,
      is_active: !item.isActive,
    })
    await loadEvents()
    clearGuestEventsCache()
  } catch (error) {
    vendorServiceNotice.value = error?.message || uiText.value.couldNotUpdateService
  }
}

async function deleteVendorService(item) {
  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!item?.id || !Number.isFinite(vendorUserId) || vendorUserId < 1) return

  try {
    await apiDelete(`vendor/services/${item.id}`, { vendor_user_id: vendorUserId })
    if (Number(vendorServiceForm.value?.id || 0) === Number(item.id)) {
      resetVendorServiceForm()
    }
    if (vendorSettingsServiceId.value === Number(item.id)) {
      vendorSettingsServiceId.value = 'all'
      if (isVendorAccount.value) {
        await loadVendorSettings({ eventId: null, silent: true })
      }
    }
    await loadEvents()
    clearGuestEventsCache()
    vendorServiceNotice.value = `${item.title || uiText.value.serviceBooking} - ${uiText.value.serviceDeleted}`
  } catch (error) {
    vendorServiceNotice.value = error?.message || uiText.value.couldNotDeleteService
  }
}

function getLocalDateKey(date = new Date()) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

function normalizeDateKey(value) {
  if (!value) return ''
  if (value instanceof Date && !Number.isNaN(value.getTime())) {
    return getLocalDateKey(value)
  }

  const text = String(value).trim()
  const dateMatch = text.match(/^(\d{4}-\d{2}-\d{2})/)
  if (dateMatch) return dateMatch[1]

  const parsed = new Date(text)
  if (Number.isNaN(parsed.getTime())) return ''

  return getLocalDateKey(parsed)
}

function isPastBookingDate(value) {
  const bookingDateKey = normalizeDateKey(value)
  if (!bookingDateKey) return false
  return bookingDateKey < getLocalDateKey()
}

function mapVendorBookingRow(row) {
  const event = row.event || {}
  const user = row.user || {}
  const bookingDate = row.requested_event_date || event.starts_at
  const customerEmail = row.customer_email || user.email || ''
  return {
    id: row.id,
    service_name: row.service_name || event.title || uiText.value.serviceBooking,
    customer_name: row.customer_name || row.user?.name || uiText.value.customer,
    customer_email: row.customer_email || row.user?.email || '',
    customer_id: user.id || null,
    customer_name: row.customer_name || user.name || uiText.value.customer,
    customer_email: customerEmail,
    customer_phone: row.customer_phone || user.phone || '',
    customer_location: row.customer_location || user.location || '',
    customer_avatar: user.profile_image_url || '',
    event_location: row.event_location || event.location || '',
    event_type: event.event_type || row.requested_event_type || '',
    event_image: event.image_url || '',
    requested_event_type: row.requested_event_type || event.event_type || '',
    quantity: Number(row.quantity || 0),
    booked_items: Array.isArray(row.booked_items) ? row.booked_items : [],
    booking_date: bookingDate || null,
    can_delete: isPastBookingDate(bookingDate),
    date_label: bookingDate
      ? new Date(bookingDate).toLocaleString('en-US', {
          month: 'short',
          day: '2-digit',
          year: 'numeric',
        })
      : uiText.value.dateTbd,
    status: String(row.status || 'pending'),
    payment_status: String(row.payment_status || 'pending'),
    cancellation_actor: String(row.cancellation_actor || ''),
    total_amount: Number(row.total_amount || 0),
    deposit_amount: Number(row.deposit_amount || 0),
    service_fee_amount: Number(row.service_fee_amount || 0),
    balance_due_amount: Number(row.balance_due_amount || 0),
    refund_amount: Number(row.refund_amount || 0),
    customer_compensation_amount: Number(row.customer_compensation_amount || 0),
    admin_compensation_amount: Number(row.admin_compensation_amount || 0),
    vendor_penalty_amount: Number(row.vendor_penalty_amount || 0),
    vendor_cancellation_deadline_at: row.vendor_cancellation_deadline_at || null,
    income_date: row.requested_event_date || row.created_at || event.starts_at || row.updated_at || null,
  }
}

async function loadVendorBookings() {
  if (!isVendorAccount.value) return

  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!Number.isFinite(vendorUserId) || vendorUserId < 1) {
    vendorBookings.value = []
    return
  }

  isLoadingVendorBookings.value = true
  try {
    const result = await apiGet('vendor/bookings', { vendor_user_id: vendorUserId })
    const rows = Array.isArray(result.data) ? result.data : []
    vendorBookings.value = rows.map(mapVendorBookingRow)
  } catch (error) {
    vendorBookings.value = []
    notice.value = uiText.value.couldNotLoadVendorBookings
  } finally {
    isLoadingVendorBookings.value = false
  }
}

async function updateVendorBookingStatus(item, status) {
  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!item?.id || !Number.isFinite(vendorUserId) || vendorUserId < 1) return

  try {
    await apiPatch(`vendor/bookings/${item.id}/status`, {
      vendor_user_id: vendorUserId,
      status,
    })
    await loadVendorBookings()
    await loadNotifications({ silent: true })
  } catch (error) {
    notice.value = error?.message || uiText.value.couldNotUpdateBookingStatus
  }
}

async function deleteVendorBooking(item) {
  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!item?.id || !Number.isFinite(vendorUserId) || vendorUserId < 1) return

  try {
    await apiDelete(`vendor/bookings/${item.id}`, { vendor_user_id: vendorUserId })
    await loadVendorBookings()
    await loadNotifications({ silent: true })
    notice.value = `${item.service_name || uiText.value.serviceBooking} - ${uiText.value.bookingDeleted}`
  } catch (error) {
    notice.value = error?.message || uiText.value.couldNotDeleteBooking
  }
}

async function checkEventAvailability(item) {
  if (!requireLogin(uiText.value.signInCheckAvailability)) {
    return null
  }

  checkingAvailabilityEventId.value = item.id
  try {
    const result = await apiGet(`events/${item.id}/availability`)
    availabilityByEvent.value = {
      ...availabilityByEvent.value,
      [item.id]: result,
    }
    notice.value = result.message || uiText.value.availabilityChecked
    return result
  } catch (error) {
    notice.value = uiText.value.couldNotCheckAvailability
    return null
  } finally {
    checkingAvailabilityEventId.value = null
  }
}

async function loadBookings(options = {}) {
  const { silent = false } = options
  const lookup = resolveBookingLookup()
  if (!lookup.hasIdentity) {
    bookings.value = []
    return
  }

  if (!silent) {
    isLoadingBookings.value = true
  }
  try {
    const result = await apiGet('bookings', {
      user_id: lookup.userId || undefined,
      customer_email: lookup.email || undefined,
      customer_phone: lookup.phone || undefined,
    })
    const rows = Array.isArray(result.data) ? result.data : []
    let apiRows = rows
    if (!apiRows.length && lookup.fallbackEmail) {
      const fallbackResult = await apiGet('bookings', {
        user_id: lookup.userId || undefined,
        customer_email: lookup.fallbackEmail,
        customer_phone: lookup.phone || undefined,
      })
      apiRows = Array.isArray(fallbackResult.data) ? fallbackResult.data : []
    }
    const apiMappedRows = apiRows.map((row) =>
      decorateCustomerBookingActions(
        mapApiBooking(row, { vendorName: vendorProfile.name, eventTypeMap }),
      ),
    )
    const localRows = [
      ...getLocalBookingsByIdentity({ email: lookup.email, phone: lookup.phone }),
      ...(lookup.fallbackEmail ? getLocalBookingsByIdentity({ email: lookup.fallbackEmail, phone: lookup.phone }) : []),
    ]
    const uniqueLocalRows = localRows.filter(
      (row, index, items) =>
        index === items.findIndex((candidate) => getBookingMatchKey(candidate) === getBookingMatchKey(row)),
    )
    const apiKeys = new Set(apiMappedRows.map((row) => getBookingMatchKey(row)))
    const extraLocalRows = uniqueLocalRows.filter((row) => !apiKeys.has(getBookingMatchKey(row)))
    bookings.value = [...apiMappedRows, ...extraLocalRows]
    if (extraLocalRows.length === 0) {
      clearLocalBookingsByIdentity({ email: lookup.email, phone: lookup.phone })
      if (lookup.fallbackEmail) {
        clearLocalBookingsByIdentity({ email: lookup.fallbackEmail, phone: lookup.phone })
      }
    }
  } catch (error) {
    const localRows = getLocalBookingsByIdentity({ email: lookup.email, phone: lookup.phone })
    bookings.value = localRows
    if (!silent) {
      notice.value = localRows.length
        ? uiText.value.loadedLatestBooking
        : uiText.value.couldNotLoadBookings
    }
  } finally {
    if (!silent) {
      isLoadingBookings.value = false
    }
  }
}

async function bootstrapAuthenticatedShell() {
  if (!loggedInUser.value) return

  isBootstrappingAuth.value = true
  try {
    const tasks = [loadUserProfile(), loadEvents(), loadNotifications({ silent: true })]
    if (isVendorAccount.value) {
      tasks.push(loadVendorBookings())
      tasks.push(loadVendorSettings({ silent: true }))
    }
    if (!isVendorAccount.value && resolveBookingLookup().hasIdentity) tasks.push(loadBookings())
    await Promise.all(tasks)
    startNotificationPolling()
  } finally {
    isBootstrappingAuth.value = false
  }
}

function goToDashboard() {
  currentPage.value = isVendorAccount.value ? 'dashboard' : 'bookings'
}

function setVendorDashboardTab(tab) {
  vendorDashboardTab.value = normalizeVendorDashboardTab(tab)
}

function selectVendorSettingsService(serviceId = 'all') {
  const normalized =
    serviceId === 'all' || serviceId === null || serviceId === undefined
      ? 'all'
      : Number(serviceId)
  vendorSettingsServiceId.value =
    normalized === 'all' || Number.isFinite(normalized) ? normalized : 'all'
  loadVendorSettings({
    eventId: vendorSettingsServiceId.value === 'all' ? null : vendorSettingsServiceId.value,
  })
}

function goToVendor(tab = 'about') {
  const normalizedTab = typeof tab === 'string' ? tab : 'about'
  const allowedTabs = ['about', 'services', 'reviews']
  currentPage.value = 'vendor'
  activeVendorTab.value = allowedTabs.includes(normalizedTab) ? normalizedTab : 'about'
}

function openBookedServiceDetails(item) {
  const eventType = typeof item?.eventType === 'string' && item.eventType ? item.eventType : 'all'
  const serviceName = typeof item?.service === 'string' ? item.service.trim() : ''

  selectedEventType.value = eventType
  globalSearch.value = serviceName
  sessionStorage.setItem(GLOBAL_SEARCH_SESSION_KEY, serviceName)
  currentPage.value = 'vendor'
  activeVendorTab.value = 'services'
}

function goToPackageCustomization(preferredEventType = 'all', preferredTitle = '') {
  openCustomizationPage(currentPage, preferredEventType, preferredTitle)
}

function goToAvailability(item = null) {
  if (!requireLogin(uiText.value.signInCheckAvailability)) {
    return
  }
  openAvailabilityPage(item)
}

function goToProfile() {
  openProfilePage(currentPage)
}

function goToBookings() {
  currentPage.value = 'bookings'
  if (!isVendorAccount.value && resolveBookingLookup().hasIdentity) {
    loadBookings({ silent: true })
  }
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
  if (!requireLogin(uiText.value.signInBeforeBooking)) {
    return
  }

  const name = customerName.value.trim()
  const contact = splitBookingContact(customerEmail.value)
  const email = contact.email
  const phone = contact.phone || userPhone.value.trim()

  if (!name || (!email && !phone)) {
    notice.value = uiText.value.enterNameEmail
    return
  }

  const quantity = Number(selectedQuantities.value[item.id] || 1)
  if (!Number.isFinite(quantity) || quantity < 1) {
    notice.value = uiText.value.selectValidQuantity
    return
  }

  const availability = getAvailability(item) || (await checkEventAvailability(item))
  if (!availability || !availability.service_available || !availability.vendor_available) {
    notice.value = availability?.message || uiText.value.serviceUnavailable
    return
  }

  bookingSubmittingEventId.value = item.id
  try {
    const totalAmount = Number((Number(item.price || 0) * quantity).toFixed(2))
    openServiceCheckout({
      event_id: item.id,
      quantity,
      service_name: item.title,
      requested_event_type: item.eventType || 'other',
      requested_event_date: item.startsAt || '',
      vendor_name: item.vendorName || vendorProfile.name,
      vendor_email: item.vendorEmail || '',
      qr_code_url: item.qrCodeUrl || '',
      image_url: item.image || '',
      location: item.location || '',
      total_amount: totalAmount,
      booked_items: [
        {
          type: item.serviceMode === 'package' ? 'package' : 'service',
          name: item.title,
          description: item.description || '',
          qty: quantity,
          unitPrice: Number(item.price || 0),
          totalPrice: totalAmount,
        },
      ],
    })
  } catch (error) {
    notice.value = error.message || uiText.value.bookingFailed
  } finally {
    bookingSubmittingEventId.value = null
  }
}

function bookingPrimaryAction(item) {
  if (item?.primaryAction === 'pay') {
    openApprovedBookingPayment(item)
    return
  }

  if (item.primaryBtn === 'Message Vendor') {
    goToMessages({
      vendorId: item.vendorId,
      vendorName: item.vendor,
      vendorEmail: item.vendorEmail,
      serviceName: item.service,
    })
    return
  }
  if (item.primaryBtn === 'View Details') {
    openBookedServiceDetails(item)
  }
}

function bookingSecondaryAction(item) {
  if (item?.canCancel) {
    void cancelCustomerBooking(item)
    return
  }

  if (item?.canDelete) {
    void deleteCustomerBooking(item)
    return
  }

  item.note = uiText.value.rescheduleRequested
}

function buildCustomerCancellationConfirmMessage(item) {
  const serviceName = item?.service || uiText.value.serviceBooking
  const fullRefund = Number(item?.initialPaymentAmount || 0)
  const lateRefund = Number(item?.lateCancellationRefundAmount || 0)

  if (item?.isWithinFreeCancellationWindow) {
    return `Cancel ${serviceName}? You are still within the first 3 days, so your first payment will be fully refunded ($${fullRefund.toLocaleString()}). This cancellation will also be sent directly to the vendor chat.`
  }

  return `Cancel ${serviceName}? The 3-day refund window has passed, so you will receive only 15% of your first payment ($${lateRefund.toLocaleString()}). This cancellation will also be sent directly to the vendor chat.`
}

async function cancelCustomerBooking(item) {
  if (!item) return

  const shouldCancel =
    typeof window === 'undefined'
      ? true
      : window.confirm(buildCustomerCancellationConfirmMessage(item))

  if (!shouldCancel) return

  const bookingId = Number(item.id || 0)
  const lookup = resolveBookingLookup()

  try {
    if (!Number.isFinite(bookingId) || bookingId < 1) {
      notice.value = uiText.value.bookingFailed
      return
    }

    if (!lookup.hasIdentity) {
      notice.value = uiText.value.signInToContinue
      return
    }

    const updatedBooking = await apiPatch(`user/bookings/${bookingId}/cancel`, {
      user_id: lookup.userId || undefined,
      customer_email: lookup.email || lookup.fallbackEmail || undefined,
      customer_phone: lookup.phone || undefined,
    })

    await loadBookings()
    await loadNotifications({ silent: true })
    await goToMessages({
      vendorId: item.vendorId,
      vendorName: item.vendor,
      vendorEmail: item.vendorEmail,
      serviceName: item.service,
      eventId: item.eventId,
    })

    const refundAmount = Number(updatedBooking?.refund_amount || 0)
    notice.value = `Booking cancelled. Refund due: $${refundAmount.toLocaleString()}. Cancellation details were sent to vendor chat.`
  } catch (error) {
    notice.value = error?.message || 'Could not cancel this booking.'
  }
}

async function deleteCustomerBooking(item) {
  if (!item) return

  const shouldDelete =
    typeof window === 'undefined'
      ? true
      : window.confirm(uiText.value.confirmDeleteCompletedBooking || 'Delete this completed booking?')

  if (!shouldDelete) return

  const bookingId = Number(item.id || 0)
  const lookup = resolveBookingLookup()

  try {
    if (Number.isFinite(bookingId) && bookingId > 0) {
      if (!lookup.hasIdentity) {
        notice.value = uiText.value.signInToContinue
        return
      }

      await apiDelete(`user/bookings/${bookingId}`, {
        user_id: lookup.userId || undefined,
        customer_email: lookup.email || lookup.fallbackEmail || undefined,
        customer_phone: lookup.phone || undefined,
      })
    }

    deleteLocalBookingEntry(item)
    bookings.value = bookings.value.filter(
      (candidate) => getBookingMatchKey(candidate) !== getBookingMatchKey(item),
    )
    await loadNotifications({ silent: true })
    notice.value = `${item.service || uiText.value.serviceBooking} - ${uiText.value.bookingDeleted}`
  } catch (error) {
    notice.value = error?.message || uiText.value.couldNotDeleteBooking
  }
}

async function confirmCustomization() {
  if (!requireLogin(uiText.value.signInConfirmPackage)) {
    return
  }
  await submitCustomization(getAvailability)
  await loadNotifications({ silent: true })
}

watch([customerName, customerEmail, userPhone, userLocation, userProfileImageUrl, userLatitude, userLongitude], () => {
  localStorage.setItem('achar_customer_name', customerName.value)
  localStorage.setItem('achar_customer_email', customerEmail.value)
  localStorage.setItem('achar_user_phone', userPhone.value)
  localStorage.setItem('achar_user_location', userLocation.value)
  localStorage.setItem('achar_user_lat', userLatitude.value === null ? '' : String(userLatitude.value))
  localStorage.setItem('achar_user_lng', userLongitude.value === null ? '' : String(userLongitude.value))
  localStorage.setItem('achar_user_profile_image', userProfileImageUrl.value || '')
})

watch([customerEmail, userPhone], () => {
  if (!loggedInUser.value || isBootstrappingAuth.value) return

  if (!isVendorAccount.value && (currentPage.value === 'bookings' || currentPage.value === 'dashboard')) {
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
  latestCustomerBookingStatusNotificationId = null
  hasInitializedCustomerBookingStatusNotifications = false
  latestVendorCustomerCancellationNotificationId = null
  hasInitializedVendorCancellationAlerts = false
  window.dispatchEvent(new Event('achar:auth-updated'))
})
watch(
  () => route.query,
  (query) => {
    applyRouteStateFromQuery(query)
  },
  { deep: true },
)

watch(
  () => String(loggedInUser.value?.role || '').trim().toLowerCase(),
  () => {
    if (!loggedInUser.value) return
    applyRouteStateFromQuery(route.query)
  },
)

watch([currentPage, activeVendorTab, vendorDashboardTab], () => {
  closeNotificationDropdown()
  syncRouteQueryFromState()
})

watch(
  vendorDashboardTab,
  async (tab) => {
    if (tab === 'services') {
      await loadEvents()
    }
    if (tab === 'messages') {
      await loadVendorConversations({ preserveSelection: true })
    }
  },
  { flush: 'post', immediate: true },
)

onMounted(async () => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('achar:auth-updated', refreshLoggedInUserFromStorage)
  window.addEventListener('storage', refreshLoggedInUserFromStorage)
  window.addEventListener('achar:global-search-updated', handleGlobalSearchUpdated)
  refreshLoggedInUserFromStorage()
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
  if (!userPhone.value.trim()) userPhone.value = loggedInUser.value?.phone || ''
  await bootstrapAuthenticatedShell()
  consumeMessageVendorTarget()
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('achar:auth-updated', refreshLoggedInUserFromStorage)
  window.removeEventListener('storage', refreshLoggedInUserFromStorage)
  window.removeEventListener('achar:global-search-updated', handleGlobalSearchUpdated)
  stopNotificationPolling()
})
</script>

<template>
  <div class="auth-root">
    <Register v-if="!loggedInUser && currentView === 'register'" @switch="toggleView" @success="onLoginSuccess" />
    <Login v-else-if="!loggedInUser" @switch="toggleView" @success="onLoginSuccess" />
    <div v-else class="page">
      <PublicNavbar />
      <AdminDashboardPage
        v-if="isAdminAccount && (resolvedCurrentPage === 'dashboard' || resolvedCurrentPage === 'settings')"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :admin-user="loggedInUser"
        :active-page="resolvedCurrentPage"
        :update-admin-user="syncAuthenticatedUser"
        :logout-user="logout"
      />
      <AdminBookingsPage
        v-else-if="isAdminAccount && resolvedCurrentPage === 'admin-bookings'"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :logout-user="logout"
      />
      <AdminEventsPage
        v-else-if="isAdminAccount && resolvedCurrentPage === 'events'"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :logout-user="logout"
      />
      <AdminRevenuePage
        v-else-if="isAdminAccount && resolvedCurrentPage === 'revenue'"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :logout-user="logout"
      />
      <AdminVendorsPage
        v-else-if="isAdminAccount && resolvedCurrentPage === 'vendors'"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :admin-user-id="loggedInUser?.id"
        :logout-user="logout"
      />
      <AdminCustomersPage
        v-else-if="isAdminAccount && resolvedCurrentPage === 'customers'"
        :app-logo-src="brandLogoSrc"
        :admin-display-name="adminDisplayName"
        :admin-user-id="loggedInUser?.id"
        :logout-user="logout"
      />
      <VendorDashboardPage
        v-else-if="isVendorAccount && resolvedCurrentPage === 'dashboard'"
        :app-logo-src="brandLogoSrc"
        :vendor-display-name="vendorDisplayName"
        v-model:active-tab="vendorDashboardTab"
        :event-type-options="eventTypeOptions"
        :vendor-events="vendorEvents"
        :vendor-bookings="vendorBookings"
        :is-loading-events="isLoadingEvents"
        :is-loading-vendor-bookings="isLoadingVendorBookings"
        :vendor-service-form="vendorServiceForm"
        :is-submitting-vendor-service="isSubmittingVendorService"
        :vendor-service-notice="vendorServiceNotice"
        :vendor-income="vendorIncome"
        :vendor-settings="vendorSettings"
        :vendor-settings-service-id="vendorSettingsServiceId"
        :is-loading-vendor-settings="isLoadingVendorSettings"
        :is-saving-vendor-settings="isSavingVendorSettings"
        :vendor-settings-notice="vendorSettingsNotice"
        :messages-summary="messagesSummary"
        :messages-bindings="messagesBindings"
        :filtered-conversations="filteredConversations"
        :active-conversation="activeConversation"
        :select-conversation="selectConversation"
        :send-message="sendMessage"
        :send-files="sendFiles"
        :send-location="sendLocation"
        :is-sharing-location="isSharingLocation"
        :save-document="saveDocument"
        :delete-message="deleteMessage"
        :is-loading-messages="isLoadingMessages"
        :messages-error="messagesError"
        :load-vendor-conversations="loadVendorConversations"
        :submit-vendor-service="submitVendorService"
        :toggle-vendor-service-active="toggleVendorServiceActive"
        :delete-vendor-service="deleteVendorService"
        :update-vendor-booking-status="updateVendorBookingStatus"
        :delete-vendor-booking="deleteVendorBooking"
        :save-vendor-settings="saveVendorSettings"
        :submit-vendor-subscription-payment="submitVendorSubscriptionPayment"
        :is-submitting-vendor-subscription-payment="isSubmittingVendorSubscriptionPayment"
        :refresh-vendor-settings="loadVendorSettings"
        :select-vendor-settings-service="selectVendorSettingsService"
        :logout-user="logout"
      />
      <DashboardPage
      v-else-if="resolvedCurrentPage === 'dashboard'"
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
      v-else-if="resolvedCurrentPage === 'vendor'"
      :vendor-profile="vendorProfile"
      :bindings="vendorBindings"
      :is-vendor-account="isVendorAccount"
      :vendor-service-form="vendorServiceForm"
      :is-submitting-vendor-service="isSubmittingVendorService"
      :vendor-service-notice="vendorServiceNotice"
      :vendor-settings="vendorSettings"
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
      :submit-vendor-service="submitVendorService"
    />

      <CustomizationPage
      v-else-if="resolvedCurrentPage === 'customization'"
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
      v-else-if="resolvedCurrentPage === 'availability'"
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
      v-else-if="resolvedCurrentPage === 'bookings'"
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
      v-else-if="resolvedCurrentPage === 'profile'"
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
      :is-saving-profile="isSavingProfile"
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
      :is-loading-messages="isLoadingMessages"
      :messages-error="messagesError"
      />
    </div>
  </div>
</template>







