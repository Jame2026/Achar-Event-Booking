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
import { mapBooking as mapApiBooking, mapEvent as mapApiEvent } from './features/bookingMappers'
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
    enterNameEmail: 'Please enter your name and email before booking.',
    selectValidQuantity: 'Please select a valid quantity.',
    serviceUnavailable: 'This service is not available at the moment.',
    bookingCreatedFor: 'Booking created for',
    bookingFailed: 'Booking failed.',
    rescheduleRequested: 'Reschedule request sent. Waiting for confirmation.',
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
    enterNameEmail: 'សូមបញ្ចូលឈ្មោះ និងអ៊ីមែលរបស់អ្នកមុនពេលកក់។',
    selectValidQuantity: 'សូមជ្រើសរើសចំនួនត្រឹមត្រូវ។',
    serviceUnavailable: 'សេវាកម្មនេះមិនមានទំនេរនៅពេលនេះទេ។',
    bookingCreatedFor: 'ការកក់ត្រូវបានបង្កើតសម្រាប់',
    bookingFailed: 'ការកក់បានបរាជ័យ។',
    rescheduleRequested: 'សំណើកំណត់ពេលឡើងវិញត្រូវបានផ្ញើ។ កំពុងរង់ចាំការបញ្ជាក់។',
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
    enterNameEmail: '请在预订前输入您的姓名和邮箱。',
    selectValidQuantity: '请选择有效数量。',
    serviceUnavailable: '该服务当前不可用。',
    bookingCreatedFor: '已为以下项目创建预订：',
    bookingFailed: '预订失败。',
    rescheduleRequested: '改期请求已发送，正在等待确认。',
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

function toggleView() {
  currentView.value = currentView.value === 'register' ? 'login' : 'register'
}

function onLoginSuccess(user) {
  loggedInUser.value = user
  if (!customerName.value?.trim()) customerName.value = user?.name ?? ''
  if (!customerEmail.value?.trim()) customerEmail.value = user?.email ?? ''
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
const allowedPages = ['dashboard', 'vendor', 'customization', 'availability', 'bookings', 'profile', 'messages']
const allowedVendorTabs = ['about', 'services', 'reviews']
const allowedVendorDashboardTabs = ['overview', 'services', 'bookings', 'messages', 'income', 'settings']
const isPlannerUser = computed(() => String(loggedInUser.value?.role || 'user') === 'user')
const isVendorAccount = computed(() =>
  ['vendor', 'admin'].includes(String(loggedInUser.value?.role || '').trim().toLowerCase()),
)
const defaultLegacyPage = computed(() => (isVendorAccount.value ? 'dashboard' : 'bookings'))

function firstQueryValue(value) {
  return Array.isArray(value) ? value[0] : value
}

function normalizePage(value) {
  const page = firstQueryValue(value)
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
  const nextPage = normalizePage(query.page)
  currentPage.value = nextPage
  if (!loggedInUser.value) {
    const authView = normalizeAuthView(firstQueryValue(query.view))
    if (authView) currentView.value = authView
  }
  if (nextPage === 'vendor') activeVendorTab.value = normalizeVendorTab(query.tab)
  if (isVendorAccount.value && nextPage === 'dashboard') {
    vendorDashboardTab.value = normalizeVendorDashboardTab(query.vtab)
  }
}

function syncRouteQueryFromState() {
  const nextQuery = {}
  if (currentPage.value !== defaultLegacyPage.value) nextQuery.page = currentPage.value
  if (currentPage.value === 'vendor') nextQuery.tab = activeVendorTab.value
  if (isVendorAccount.value && currentPage.value === 'dashboard') nextQuery.vtab = vendorDashboardTab.value

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
<<<<<<< HEAD
const vendorDashboardNotice = ref('')
=======
const vendorSettings = ref(null)
const vendorSettingsServiceId = ref('all')
const isLoadingVendorSettings = ref(false)
const isSavingVendorSettings = ref(false)
const vendorSettingsNotice = ref('')
>>>>>>> 39a94620dc4cb932416d37f60b423ad64f3209c4
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
        service: row.service || uiText.value.serviceBooking,
        date: row.dateLabel || uiText.value.dateTbd,
        metaLabel: 'Event Type',
        metaValue: eventTypeMap[row.eventType] || 'Other',
        placeLabel: 'Total',
        placeValue: `$${Number(row.total || 0).toLocaleString()}`,
        status: row.status || 'Confirmed',
        statusClass: row.statusClass || 'confirmed',
        type: row.type || 'Upcoming',
        eventType: row.eventType || 'other',
        eventId: row.eventId || null,
        image:
          row.image ||
          'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=760&q=80',
        primaryBtn: 'View Details',
        secondaryBtn: 'Reschedule',
        note: `${row.customerName || 'Guest User'} | ${row.customerEmail || normalizedEmail}`,
      }))
  } catch {
    return []
  }
}

function getBookingMatchKey(row) {
  const eventId = row?.eventId || row?.event_id || row?.event?.id || null
  const date =
    row?.date ||
    row?.dateLabel ||
    row?.requested_event_date ||
    row?.event?.starts_at ||
    ''
  if (eventId) {
    return `event:${eventId}|${String(date).trim().toLowerCase()}`
  }
  const service =
    row?.service ||
    row?.service_name ||
    row?.name ||
    ''
  const total =
    row?.total ||
    row?.total_amount ||
    row?.servicePrice ||
    0
  return `service:${String(service).trim().toLowerCase()}|${Number(total || 0)}|${String(date).trim().toLowerCase()}`
}

function clearLocalBookingsByEmail(email) {
  if (!email) return

  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY)
    if (!raw) return

    const rows = JSON.parse(raw)
    if (!Array.isArray(rows)) return

    const normalizedEmail = email.trim().toLowerCase()
    const remainingRows = rows.filter(
      (row) => String(row?.customerEmail || '').trim().toLowerCase() !== normalizedEmail,
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
  }
}

async function loadVendorSettings({ eventId = null, silent = false } = {}) {
  if (!isVendorAccount.value) return
  isLoadingVendorSettings.value = true
  if (!silent) vendorSettingsNotice.value = ''
  try {
    const targetEventId =
      eventId !== null && eventId !== undefined
        ? eventId
        : vendorSettingsServiceId.value === 'all'
          ? null
          : Number(vendorSettingsServiceId.value)

    const result = await apiGet('vendor/settings', targetEventId ? { event_id: targetEventId } : {})
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
    const targetEventId =
      payload?.event_id !== undefined
        ? payload.event_id
        : vendorSettingsServiceId.value === 'all'
          ? null
          : Number(vendorSettingsServiceId.value)
    const normalizedPayload = { ...payload, event_id: targetEventId }
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
    price: Number(vendorServiceForm.value.price || 0),
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
  try {
    const serviceId = Number(vendorServiceForm.value.id || 0)
    const payload = imageFile
      ? (() => {
          const formData = new FormData()
          Object.entries(normalizedPayload).forEach(([key, value]) => {
            if (key === 'image_url') {
              return
            }
            if (key === 'qr_code_url' && qrCodeFile) {
              return
            }
            if (key === 'packages') {
              if (Array.isArray(value) && value.length > 0) {
                formData.append(key, JSON.stringify(value))
              }
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
          formData.append('image', imageFile)
          if (qrCodeFile) {
            formData.append('qr_code', qrCodeFile)
          }
          return formData
        })()
      : normalizedPayload

    await apiPost('vendor/services', payload)
    await loadEvents()
    clearGuestEventsCache()
    selectedEventType.value = normalizedPayload.event_type
    resetVendorServiceForm()
  } catch (error) {
    vendorServiceNotice.value =
      error?.message ||
      (vendorServiceForm.value.id ? uiText.value.couldNotUpdateServiceDetails : uiText.value.couldNotCreateService)
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
    total_amount: Number(row.total_amount || 0),
    income_date: row.requested_event_date || row.created_at || event.starts_at || row.updated_at || null,
  }
}

async function loadVendorBookings() {
  if (!isVendorAccount.value) return

  const vendorUserId = Number(loggedInUser.value?.id || 0)
  if (!Number.isFinite(vendorUserId) || vendorUserId < 1) {
    vendorBookings.value = []
    vendorDashboardNotice.value = uiText.value.vendorAccountMissing
    return
  }

  isLoadingVendorBookings.value = true
  vendorDashboardNotice.value = ''
  try {
    const result = await apiGet('vendor/bookings', { vendor_user_id: vendorUserId })
    const rows = Array.isArray(result.data) ? result.data : []
    vendorBookings.value = rows.map(mapVendorBookingRow)
  } catch (error) {
    vendorBookings.value = []
    vendorDashboardNotice.value = error?.message || uiText.value.couldNotLoadVendorBookings
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

async function loadBookings() {
  const typedEmail = customerEmail.value.trim()
  const loggedEmail = String(loggedInUser.value?.email || '').trim()
  const lastEmail = String(localStorage.getItem(LAST_BOOKING_EMAIL_KEY) || '').trim()
  const primaryEmail = typedEmail || loggedEmail || lastEmail
  const fallbackEmail = lastEmail && lastEmail !== primaryEmail ? lastEmail : ''
  const email = primaryEmail
  if (!email) {
    bookings.value = []
    return
  }

  isLoadingBookings.value = true
  try {
    const result = await apiGet('bookings', { customer_email: email })
    const rows = Array.isArray(result.data) ? result.data : []
    let apiRows = rows
    if (!apiRows.length && fallbackEmail) {
      const fallbackResult = await apiGet('bookings', { customer_email: fallbackEmail })
      apiRows = Array.isArray(fallbackResult.data) ? fallbackResult.data : []
    }
    const apiMappedRows = apiRows.map((row) =>
      mapApiBooking(row, { vendorName: vendorProfile.name, eventTypeMap }),
    )
    const localRows = [
      ...getLocalBookingsByEmail(email),
      ...(fallbackEmail ? getLocalBookingsByEmail(fallbackEmail) : []),
    ]
    const apiKeys = new Set(apiMappedRows.map((row) => getBookingMatchKey(row)))
    const extraLocalRows = localRows.filter((row) => !apiKeys.has(getBookingMatchKey(row)))
    bookings.value = [...apiMappedRows, ...extraLocalRows]
    if (extraLocalRows.length === 0) {
      clearLocalBookingsByEmail(email)
    }
  } catch (error) {
    const localRows = getLocalBookingsByEmail(email)
    bookings.value = localRows
    notice.value = localRows.length
      ? uiText.value.loadedLatestBooking
      : uiText.value.couldNotLoadBookings
  } finally {
    isLoadingBookings.value = false
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
    if (!isVendorAccount.value && customerEmail.value.trim()) tasks.push(loadBookings())
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
  const email = customerEmail.value.trim()

  if (!name || !email) {
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
    await apiPost('bookings', {
      event_id: item.id,
      quantity,
      customer_name: name,
      customer_email: email,
    })

    notice.value = `${uiText.value.bookingCreatedFor} ${item.title}.`
    await loadBookings()
    await loadNotifications({ silent: true })
    goToBookings()
    bookingFilter.value = 'Upcoming'
  } catch (error) {
    notice.value = error.message || uiText.value.bookingFailed
  } finally {
    bookingSubmittingEventId.value = null
  }
}

function bookingPrimaryAction(item) {
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
  item.note = uiText.value.rescheduleRequested
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

watch(customerEmail, () => {
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
  window.dispatchEvent(new Event('achar:auth-updated'))
})
watch(
  () => route.query,
  (query) => {
    applyRouteStateFromQuery(query)
  },
  { deep: true },
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
  consumeMessageVendorTarget()
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
      <PublicNavbar />
      <VendorDashboardPage
        v-if="isVendorAccount && currentPage === 'dashboard'"
        :app-logo-src="brandLogoSrc"
        :vendor-display-name="vendorDisplayName"
        v-model:active-tab="vendorDashboardTab"
        :event-type-options="eventTypeOptions"
        :vendor-events="vendorEvents"
        :vendor-bookings="vendorBookings"
        :is-loading-events="isLoadingEvents"
        :is-loading-vendor-bookings="isLoadingVendorBookings"
        :notice="vendorDashboardNotice"
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
        :refresh-vendor-settings="loadVendorSettings"
        :select-vendor-settings-service="selectVendorSettingsService"
        :logout-user="logout"
      />
      <DashboardPage
      v-else-if="currentPage === 'dashboard'"
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
      :logout-user="logout"
    />

      <VendorPage
      v-else-if="currentPage === 'vendor'"
      :vendor-profile="vendorProfile"
      :bindings="vendorBindings"
      :is-vendor-account="isVendorAccount"
      :vendor-service-form="vendorServiceForm"
      :is-submitting-vendor-service="isSubmittingVendorService"
      :vendor-service-notice="vendorServiceNotice"
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







