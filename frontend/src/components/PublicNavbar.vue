<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { apiGet, apiPatch } from '../features/apiClient'
import { useLanguage } from '../features/language'

const route = useRoute()
const router = useRouter()
const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const GLOBAL_SEARCH_SESSION_KEY = 'achar_global_search'
const FAVORITES_STORAGE_KEY = 'achar_guest_favorites'
const isLoggedIn = ref(false)
const currentUser = ref(null)
const navSearch = ref('')
const favoriteCount = ref(0)
const isLoadingNotifications = ref(false)
const notificationsError = ref('')
const notifications = ref([])
const notificationsUnreadCount = ref(0)
const notificationDropdownOpen = ref(false)
const notificationMenuRef = ref(null)
const { language, updateLanguage } = useLanguage()
let notificationPollTimer = null
const NOTIFICATION_POLL_INTERVAL_MS = 60000

const copyByLanguage = {
  en: {
    home: 'Home',
    about: 'About',
    service: 'Service',
    packages: 'Packages',
    overallService: 'Overall Service',
    dashboard: 'Dashboard',
    myBooking: 'My Booking',
    favorite: 'Favorite',
    contact: 'Contact',
    searchPlaceholder: 'Search bookings...',
    notificationsAria: 'Open booking notifications',
    bookingNotifications: 'Booking Notifications',
    markAll: 'Mark all',
    refresh: 'Refresh',
    close: 'Close',
    loadingNotifications: 'Loading notifications...',
    notificationsError: 'Could not load notifications right now.',
    noNotifications: 'No notifications yet.',
    open: 'Open',
    markRead: 'Mark read',
    signIn: 'Sign in',
    justNow: 'Just now',
    minAgo: 'm ago',
    hourAgo: 'h ago',
  },
  km: {
    home: 'ទំព័រដើម',
    about: 'អំពីយើង',
    service: 'សេវាកម្ម',
    packages: 'កញ្ចប់សេវាកម្ម',
    overallService: 'សេវាកម្មទូទៅ',
    dashboard: 'ផ្ទាំងគ្រប់គ្រង',
    myBooking: 'ការកក់របស់ខ្ញុំ',
    favorite: 'ចូលចិត្ត',
    contact: 'ទំនាក់ទំនង',
    searchPlaceholder: 'ស្វែងរកការកក់...',
    notificationsAria: 'បើកការជូនដំណឹងការកក់',
    bookingNotifications: 'ការជូនដំណឹងការកក់',
    markAll: 'សម្គាល់អានទាំងអស់',
    loadingNotifications: 'កំពុងផ្ទុកការជូនដំណឹង...',
    notificationsError: 'មិនអាចផ្ទុកការជូនដំណឹងឥឡូវនេះបានទេ។',
    noNotifications: 'មិនទាន់មានការជូនដំណឹងទេ។',
    signIn: 'ចូលប្រព័ន្ធ',
    justNow: 'ឥឡូវនេះ',
    minAgo: 'នាទីមុន',
    hourAgo: 'ម៉ោងមុន',
  },
  zh: {
    home: '首页',
    about: '关于',
    service: '服务',
    packages: '套餐',
    overallService: '综合服务',
    dashboard: '控制台',
    myBooking: '我的预订',
    favorite: '收藏',
    contact: '联系',
    searchPlaceholder: '搜索预订...',
    notificationsAria: '打开预订通知',
    bookingNotifications: '预订通知',
    markAll: '全部已读',
    loadingNotifications: '正在加载通知...',
    notificationsError: '暂时无法加载通知。',
    noNotifications: '暂无通知。',
    signIn: '登录',
    justNow: '刚刚',
    minAgo: '分钟前',
    hourAgo: '小时前',
  },
}

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en)

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

function refreshAuthState() {
  try {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY)
    isLoggedIn.value = Boolean(raw)
    currentUser.value = raw ? JSON.parse(raw) : null
  } catch {
    isLoggedIn.value = false
    currentUser.value = null
  }
}

function runSearch() {
  const query = navSearch.value.trim()
  if (query) {
    sessionStorage.setItem(GLOBAL_SEARCH_SESSION_KEY, query)
  } else {
    sessionStorage.removeItem(GLOBAL_SEARCH_SESSION_KEY)
  }
  window.dispatchEvent(new Event('achar:global-search-updated'))
  if (route.path !== '/legacy-app') {
    const role = String(currentUser.value?.role || '').trim().toLowerCase()
    router.push(role === 'vendor' ? '/legacy-app?page=dashboard' : '/legacy-app?page=bookings').catch(() => {})
  }
}

function notificationRole(role) {
  return role === 'vendor' ? 'vendor' : 'user'
}

function buildNotificationQuery() {
  const user = currentUser.value || {}
  const query = {
    role: notificationRole(user.role),
    limit: 20,
  }

  const userId = Number(user.id)
  if (Number.isFinite(userId) && userId > 0) query.user_id = userId

  const email = String(user.email || '').trim().toLowerCase()
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
  if (typeof document !== 'undefined' && document.visibilityState === 'hidden') return
  notificationPollTimer = setInterval(() => {
    loadNotifications({ silent: true })
  }, NOTIFICATION_POLL_INTERVAL_MS)
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

function handleVisibilityChange() {
  if (typeof document === 'undefined') return
  if (document.visibilityState === 'visible') {
    if (isLoggedIn.value) {
      loadNotifications({ silent: true })
      startNotificationPolling()
    }
    return
  }

  stopNotificationPolling()
}

function openProfile() {
  router.push('/legacy-app?page=profile').catch(() => {})
}

function refreshFavoriteCount() {
  try {
    const raw = localStorage.getItem(FAVORITES_STORAGE_KEY)
    if (!raw) {
      favoriteCount.value = 0
      return
    }

    const parsed = JSON.parse(raw)
    const packageIds = Array.isArray(parsed?.packageIds) ? parsed.packageIds : []
    const serviceIds = Array.isArray(parsed?.serviceIds) ? parsed.serviceIds : []
    favoriteCount.value = new Set([...packageIds, ...serviceIds]).size
  } catch {
    favoriteCount.value = 0
  }
}

function formatNotificationTime(value) {
  if (!value) return uiText.value.justNow
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return uiText.value.justNow

  const diffMinutes = Math.floor((Date.now() - parsed.getTime()) / 60000)
  if (diffMinutes < 1) return uiText.value.justNow
  if (diffMinutes < 60) return `${diffMinutes}${uiText.value.minAgo}`
  if (diffMinutes < 24 * 60) return `${Math.floor(diffMinutes / 60)}${uiText.value.hourAgo}`

  return parsed.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
  })
}

const notificationItems = computed(() =>
  notifications.value.map((item) => ({
    ...item,
    createdLabel: formatNotificationTime(item.created_at),
  })),
)

const unreadNotificationCount = computed(
  () => notificationsUnreadCount.value || notifications.value.filter((item) => !item.is_read).length,
)

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
    notificationsError.value = uiText.value.notificationsError
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
    if (!silent) notificationsError.value = uiText.value.notificationsError
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
    notificationsError.value = uiText.value.notificationsError
    await loadNotifications({ silent: true })
  }
}

function refreshNotifications() {
  loadNotifications()
}

async function openNotification(notification) {
  await markNotificationAsRead(notification, { silent: true })
  closeNotificationDropdown()
  const role = String(currentUser.value?.role || '').trim().toLowerCase()
  router.push(role === 'vendor' ? '/legacy-app?page=dashboard' : '/legacy-app?page=bookings').catch(() => {})
}

const isHomeActive = computed(() => route.path === '/' || route.path === '/home')
const isAboutActive = computed(() => route.path === '/about')
const isServiceActive = computed(() => route.path.startsWith('/services'))
const isServicePackagesActive = computed(() => route.path === '/services/packages')
const isServiceOverallActive = computed(() => route.path === '/services/overall')
const isVendorRole = computed(() => String(currentUser.value?.role || '').trim().toLowerCase() === 'vendor')
const legacyPage = computed(() => {
  const page = route.query?.page
  return typeof page === 'string' ? page : 'bookings'
})
const isVendorDashboardActive = computed(
  () => route.path === '/legacy-app' && legacyPage.value === 'dashboard' && isVendorRole.value,
)
const isProfileActive = computed(() => route.path === '/legacy-app' && legacyPage.value === 'profile')
const isBookingActive = computed(
  () =>
    route.path === '/booking' ||
    (route.path === '/legacy-app' && !isProfileActive.value && legacyPage.value === 'bookings'),
)
const isFavoriteActive = computed(() => route.path === '/favorite')
const isContactActive = computed(() => route.path === '/contact')
const isRegisterActive = computed(
  () => route.path === '/register' || (route.path === '/legacy-app' && route.query?.auth === 'register'),
)

onMounted(() => {
  refreshAuthState()
  refreshFavoriteCount()
  if (isLoggedIn.value) {
    loadNotifications({ silent: true })
    startNotificationPolling()
  }
  document.addEventListener('click', handleDocumentClick)
  document.addEventListener('visibilitychange', handleVisibilityChange)
  window.addEventListener('storage', refreshFavoriteCount)
  window.addEventListener('achar:favorites-updated', refreshFavoriteCount)
})

onBeforeUnmount(() => {
  stopNotificationPolling()
  document.removeEventListener('click', handleDocumentClick)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  window.removeEventListener('storage', refreshFavoriteCount)
  window.removeEventListener('achar:favorites-updated', refreshFavoriteCount)
})
const bookingLink = computed(() => {
  if (!isLoggedIn.value) return '/booking'
  const role = String(currentUser.value?.role || '').trim().toLowerCase()
  return role === 'vendor' ? '/legacy-app?page=dashboard&vtab=bookings' : '/legacy-app?page=bookings'
})
</script>

<template>
  <header class="public-nav-wrap">
    <div class="nav-shell">
      <RouterLink class="brand" to="/">
        <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
        <span class="brand-name">Achar</span>
      </RouterLink>

      <nav class="nav-links">
        <RouterLink to="/" :class="{ active: isHomeActive }">{{ uiText.home }}</RouterLink>
        <RouterLink to="/about" :class="{ active: isAboutActive }">{{ uiText.about }}</RouterLink>

        <div class="nav-dropdown">
          <RouterLink class="nav-drop-trigger" to="/services/packages" :class="{ active: isServiceActive }">
            {{ uiText.service }}
          </RouterLink>
          <div class="nav-drop-menu">
            <RouterLink to="/services/packages" :class="{ active: isServicePackagesActive }">{{ uiText.packages }}</RouterLink>
            <RouterLink to="/services/overall" :class="{ active: isServiceOverallActive }">{{ uiText.overallService }}</RouterLink>
          </div>
        </div>

        <RouterLink
          v-if="isLoggedIn && isVendorRole"
          to="/legacy-app?page=dashboard"
          :class="{ active: isVendorDashboardActive }"
        >
          {{ uiText.dashboard }}
        </RouterLink>
        <RouterLink v-if="!isVendorRole" :to="bookingLink" :class="{ active: isBookingActive }">{{ uiText.myBooking }}</RouterLink>
        <RouterLink to="/favorite" :class="{ active: isFavoriteActive }" class="favorite-link">
          <span>{{ uiText.favorite }}</span>
          <span v-if="favoriteCount > 0" class="fav-badge">
            {{ favoriteCount > 99 ? '99+' : favoriteCount }}
          </span>
        </RouterLink>
        <RouterLink to="/contact" :class="{ active: isContactActive }">{{ uiText.contact }}</RouterLink>
      </nav>

      <div class="nav-actions">
        <label class="lang-switch" for="language-select">
          <select
            id="language-select"
            class="lang-select"
            :value="language"
            @change="updateLanguage($event.target.value)"
          >
            <option value="en">English</option>
            <option value="km">Khmer</option>
            <option value="zh">中文</option>
          </select>
        </label>
        <input
          v-if="isLoggedIn"
          v-model="navSearch"
          type="search"
          class="nav-search"
          :placeholder="uiText.searchPlaceholder"
          @keyup.enter="runSearch"
        />
        <div v-if="isLoggedIn" ref="notificationMenuRef" class="notification-wrap">
          <button
            type="button"
            class="notification-btn"
            :aria-label="uiText.notificationsAria"
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

          <section v-if="notificationDropdownOpen" class="notification-panel" @click.stop>
            <div class="notification-head">
              <strong>{{ uiText.bookingNotifications }}</strong>
              <div class="notification-actions">
                <button type="button" class="notification-action-btn" @click="refreshNotifications">
                  {{ uiText.refresh || 'Refresh' }}
                </button>
                <button
                  v-if="unreadNotificationCount > 0"
                  type="button"
                  class="notification-action-btn"
                  @click="markAllNotificationsAsRead"
                >
                  {{ uiText.markAll }}
                </button>
                <button type="button" class="notification-action-btn is-muted" @click="closeNotificationDropdown">
                  {{ uiText.close || 'Close' }}
                </button>
              </div>
            </div>
            <p v-if="isLoadingNotifications" class="notification-empty">{{ uiText.loadingNotifications }}</p>
            <p v-else-if="notificationsError" class="notification-empty">{{ notificationsError }}</p>
            <p v-else-if="notificationItems.length === 0" class="notification-empty">{{ uiText.noNotifications }}</p>
            <ul v-else class="notification-list">
              <li v-for="item in notificationItems" :key="item.id">
                <article class="notification-item" :class="{ unread: !item.is_read }">
                  <div class="notification-item-top">
                    <span>{{ item.title }}</span>
                    <small>{{ item.createdLabel }}</small>
                  </div>
                  <p>{{ item.message }}</p>
                  <div class="notification-item-actions">
                    <button
                      v-if="!item.is_read"
                      type="button"
                      class="notification-inline-btn is-muted"
                      @click="markNotificationAsRead(item)"
                    >
                      {{ uiText.markRead || 'Mark read' }}
                    </button>
                    <button type="button" class="notification-inline-btn" @click="openNotification(item)">
                      {{ uiText.open || 'Open' }}
                    </button>
                  </div>
                </article>
              </li>
            </ul>
          </section>
        </div>
        <button
          v-if="isLoggedIn"
          type="button"
          class="profile-btn"
          :title="String(currentUser?.name || 'Profile')"
          @click="openProfile"
        >
          <img
            v-if="String(currentUser?.profile_image_url || '').trim()"
            class="profile-btn-img"
            :src="currentUser.profile_image_url"
            :alt="String(currentUser?.name || 'Profile image')"
          />
          <span v-else>
            {{ String(currentUser?.name || 'U').trim().charAt(0).toUpperCase() || 'U' }}
          </span>
        </button>
        <RouterLink v-else class="signin-btn" to="/legacy-app">{{ uiText.signIn }}</RouterLink>
      </div>
    </div>
  </header>
</template>

<style scoped>
.public-nav-wrap {
  position: sticky;
  top: 0;
  z-index: 80;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(8px);
  border-top: 3px solid #2f333c;
  border-bottom: 1px solid #e3e7ef;
  padding: 0.72rem 0;
  box-shadow: 0 8px 24px rgba(10, 28, 34, 0.06);
}

.nav-shell {
  width: min(1240px, calc(100% - 2rem));
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: nowrap;
  align-items: center;
  gap: 0.9rem;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.72rem;
  text-decoration: none;
}

.brand-logo {
  width: 60px;
  height: 60px;
  border-radius: 14px;
  border: 1px solid #f2d2bb;
  background: #fff;
  object-fit: contain;
  padding: 0.3rem;
  box-shadow: 0 8px 20px rgba(212, 102, 19, 0.14);
}

.brand-name {
  font-size: clamp(1.85rem, 2.2vw, 2.7rem);
  font-weight: 800;
  line-height: 1;
  color: #d46613;
}

.nav-links {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 0.25rem;
  flex-wrap: nowrap;
  white-space: nowrap;
  min-width: 0;
  margin: 0 auto;
}

.nav-links a,
.nav-drop-trigger {
  border: 1px solid transparent;
  border-radius: 999px;
  background: transparent;
  color: #334155;
  padding: 0.52rem 0.9rem;
  font: inherit;
  font-size: 1.01rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
}

.nav-links a:hover,
.nav-drop-trigger:hover {
  background: #f8fafc;
}

.nav-links a.active,
.nav-drop-trigger.active {
  border-color: #f3c29d;
  background: #fff3e8;
  color: #c25c13;
}

.favorite-link {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.nav-link {
  position: relative;
}

.nav-icon {
  display: inline-flex;
  width: 20px;
  height: 20px;
  align-items: center;
  justify-content: center;
}

.nav-icon svg {
  width: 100%;
  height: 100%;
}

.nav-label {
  line-height: 1.1;
  white-space: nowrap;
}

.fav-badge {
  min-width: 20px;
  height: 20px;
  border-radius: 999px;
  background: #e11d48;
  color: #fff;
  font-size: 0.7rem;
  font-weight: 800;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.35rem;
}

.nav-dropdown {
  position: relative;
}

.nav-drop-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  min-width: 180px;
  border: 1px solid #dde3ee;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 14px 34px rgba(10, 28, 34, 0.1);
  padding: 6px;
  display: none;
  z-index: 50;
}

.nav-dropdown:hover .nav-drop-menu,
.nav-dropdown:focus-within .nav-drop-menu {
  display: grid;
}

.nav-drop-menu a {
  border-radius: 9px;
  padding: 0.52rem 0.7rem;
  font-size: 0.95rem;
}

.nav-actions {
  display: inline-flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
}

.lang-switch {
  display: inline-flex;
}

.lang-select {
  border: 1px solid #d6dde9;
  border-radius: 12px;
  background: #f9fafc;
  color: #334155;
  font: inherit;
  font-size: 0.92rem;
  font-weight: 700;
  padding: 0.48rem 0.7rem;
  cursor: pointer;
}

.nav-search {
  width: clamp(120px, 24vw, 160px);
  min-width: 220px;
  max-width: 100%;
  border: 1px solid #d6dde9;
  border-radius: 14px;
  background: #f9fafc;
  color: #111827;
  font: inherit;
  font-size: 1rem;
  padding: 0.62rem 0.9rem;
}

.notification-wrap {
  position: relative;
}

.notification-btn {
  position: relative;
  width: 42px;
  height: 42px;
  border: 1px solid #f1c9a8;
  border-radius: 14px;
  background: linear-gradient(145deg, #fff7ef 0%, #ffeeda 100%);
  color: #b45309;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-btn:hover {
  border-color: #e4b892;
  background: linear-gradient(145deg, #ffffff 0%, #fff0df 100%);
  box-shadow: 0 12px 26px rgba(212, 102, 19, 0.2);
}

.notification-icon svg {
  width: 20px;
  height: 20px;
}

.notification-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  min-width: 20px;
  height: 20px;
  border-radius: 999px;
  background: linear-gradient(135deg, #ef4444 0%, #be123c 100%);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 800;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.35rem;
}

.notification-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: min(420px, 92vw);
  max-height: 520px;
  overflow: auto;
  border: 1px solid #f1d0b5;
  border-radius: 16px;
  background:
    radial-gradient(circle at 100% 0%, rgba(212, 102, 19, 0.14) 0%, rgba(212, 102, 19, 0) 44%),
    linear-gradient(180deg, #ffffff 0%, #fffaf5 100%);
  box-shadow: 0 18px 42px rgba(146, 64, 14, 0.18);
  padding: 10px;
  z-index: 90;
}

.notification-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
  padding: 4px 4px 10px;
  border-bottom: 1px solid #f3dcc8;
  margin-bottom: 4px;
}

.notification-head strong {
  color: #b45309;
  font-size: 0.95rem;
}

.notification-actions {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.notification-action-btn {
  border: 1px solid #f0cfb3;
  border-radius: 10px;
  background: linear-gradient(180deg, #ffffff 0%, #fff3e6 100%);
  color: #9a3412;
  font-size: 0.8rem;
  font-weight: 800;
  padding: 5px 9px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-action-btn:hover {
  border-color: #e2b88f;
  background: linear-gradient(180deg, #ffffff 0%, #ffe9d2 100%);
}

.notification-action-btn.is-muted {
  color: #7c5b40;
}

.notification-empty {
  margin: 0;
  color: #7c5f46;
  font-size: 0.9rem;
  padding: 8px 6px;
}

.notification-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 8px;
}

.notification-item {
  border: 1px solid #f1deca;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #fffaf5 100%);
  text-align: left;
  padding: 10px;
  box-shadow: 0 8px 18px rgba(146, 64, 14, 0.08);
}

.notification-item.unread {
  border-color: #e7b88f;
  background:
    linear-gradient(90deg, rgba(212, 102, 19, 0.2) 0 4px, transparent 4px),
    linear-gradient(180deg, #fff8f2 0%, #ffffff 100%);
}

.notification-item-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.notification-item p {
  margin: 5px 0 0;
  font-size: 0.86rem;
  color: #6c5a48;
}

.notification-item-actions {
  margin-top: 8px;
  display: flex;
  gap: 6px;
  justify-content: flex-end;
}

.notification-inline-btn {
  border: 1px solid #efcfb2;
  border-radius: 9px;
  background: linear-gradient(180deg, #fff8f1 0%, #ffefd9 100%);
  color: #9a3412;
  font-size: 0.78rem;
  font-weight: 800;
  padding: 5px 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-inline-btn:hover {
  border-color: #e2b98f;
  background: linear-gradient(180deg, #ffffff 0%, #ffe7ce 100%);
}

.notification-inline-btn.is-muted {
  background: #ffffff;
  color: #7c5b40;
}

.profile-btn {
  width: 46px;
  height: 46px;
  border: 2px solid #efc7a8;
  border-radius: 999px;
  background: linear-gradient(180deg, #fff8f1 0%, #ffe9d2 100%);
  color: #fff;
  font: inherit;
  font-size: 1.15rem;
  font-weight: 800;
  cursor: pointer;
  padding: 0;
  overflow: hidden;
  box-shadow: 0 8px 20px rgba(146, 64, 14, 0.18);
  transition: all 0.2s ease;
}

.profile-btn:hover {
  transform: translateY(-1px);
  border-color: #e2ae82;
  box-shadow: 0 12px 26px rgba(146, 64, 14, 0.24);
}

.profile-btn-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.signin-btn {
  border: 1px solid #d6dde9;
  border-radius: 14px;
  background: #f9fafc;
  color: #111827;
  font-size: 1rem;
  font-weight: 700;
  text-decoration: none;
  padding: 0.58rem 1rem;
}

.register-btn {
  border: 1px solid #f3c29d;
  border-radius: 14px;
  background: #fff3e8;
  color: #c25c13;
  font-size: 1rem;
  font-weight: 700;
  text-decoration: none;
  padding: 0.58rem 1rem;
}

.register-btn.active {
  background: #ffe9d6;
}

@media (max-width: 980px) {
  .nav-shell {
    display: grid;
    grid-template-columns: 1fr;
    justify-items: start;
    gap: 0.75rem;
  }

  .nav-links {
    justify-content: flex-start;
    overflow-x: auto;
    white-space: nowrap;
    flex-wrap: nowrap;
    width: 100%;
    padding-bottom: 0.2rem;
  }

  .nav-actions {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }

  .nav-search {
    width: min(100%, 520px);
  }

}
</style>
