<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { apiGet, apiPatch } from '../features/apiClient'

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
let notificationPollTimer = null

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
    router.push('/legacy-app?page=bookings').catch(() => {})
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
  router.push('/legacy-app?page=bookings').catch(() => {})
}

const isHomeActive = computed(() => route.path === '/' || route.path === '/home')
const isAboutActive = computed(() => route.path === '/about')
const isServiceActive = computed(() => route.path.startsWith('/services'))
const isServicePackagesActive = computed(() => route.path === '/services/packages')
const isServiceOverallActive = computed(() => route.path === '/services/overall')
const legacyPage = computed(() => {
  const page = route.query?.page
  return typeof page === 'string' ? page : 'bookings'
})
const isProfileActive = computed(() => route.path === '/legacy-app' && legacyPage.value === 'profile')
const isBookingActive = computed(
  () =>
    route.path === '/booking' ||
    (route.path === '/legacy-app' && !isProfileActive.value && legacyPage.value === 'bookings'),
)
const isFavoriteActive = computed(() => route.path === '/favorite')
const isContactActive = computed(() => route.path === '/contact')

onMounted(() => {
  refreshAuthState()
  refreshFavoriteCount()
  if (isLoggedIn.value) {
    loadNotifications({ silent: true })
    startNotificationPolling()
  }
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('storage', refreshFavoriteCount)
  window.addEventListener('achar:favorites-updated', refreshFavoriteCount)
})

onBeforeUnmount(() => {
  stopNotificationPolling()
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('storage', refreshFavoriteCount)
  window.removeEventListener('achar:favorites-updated', refreshFavoriteCount)
})
const bookingLink = computed(() => (isLoggedIn.value ? '/legacy-app?page=bookings' : '/booking'))
</script>

<template>
  <header class="public-nav-wrap">
    <div class="nav-shell">
      <RouterLink class="brand" to="/">
        <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
        <span class="brand-name">Achar</span>
      </RouterLink>

      <nav class="nav-links">
        <RouterLink to="/" :class="{ active: isHomeActive }">Home</RouterLink>
        <RouterLink to="/about" :class="{ active: isAboutActive }">About</RouterLink>

        <div class="nav-dropdown">
          <RouterLink class="nav-drop-trigger" to="/services/packages" :class="{ active: isServiceActive }">
            Service
          </RouterLink>
          <div class="nav-drop-menu">
            <RouterLink to="/services/packages" :class="{ active: isServicePackagesActive }">Packages</RouterLink>
            <RouterLink to="/services/overall" :class="{ active: isServiceOverallActive }">Overall Service</RouterLink>
          </div>
        </div>

        <RouterLink :to="bookingLink" :class="{ active: isBookingActive }">My Booking</RouterLink>
        <RouterLink to="/favorite" :class="{ active: isFavoriteActive }" class="favorite-link">
          <span>Favorite</span>
          <span v-if="favoriteCount > 0" class="fav-badge">
            {{ favoriteCount > 99 ? '99+' : favoriteCount }}
          </span>
        </RouterLink>
        <RouterLink to="/contact" :class="{ active: isContactActive }">Contact</RouterLink>
      </nav>

      <div class="nav-actions">
        <input
          v-if="isLoggedIn"
          v-model="navSearch"
          type="search"
          class="nav-search"
          placeholder="Search bookings..."
          @keyup.enter="runSearch"
        />
        <div v-if="isLoggedIn" ref="notificationMenuRef" class="notification-wrap">
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

          <section v-if="notificationDropdownOpen" class="notification-panel" @click.stop>
            <div class="notification-head">
              <strong>Booking Notifications</strong>
              <button
                v-if="unreadNotificationCount > 0"
                type="button"
                class="notification-mark-all"
                @click="markAllNotificationsAsRead"
              >
                Mark all
              </button>
            </div>
            <p v-if="isLoadingNotifications" class="notification-empty">Loading notifications...</p>
            <p v-else-if="notificationsError" class="notification-empty">{{ notificationsError }}</p>
            <p v-else-if="notificationItems.length === 0" class="notification-empty">No notifications yet.</p>
            <ul v-else class="notification-list">
              <li v-for="item in notificationItems" :key="item.id">
                <button
                  type="button"
                  class="notification-item"
                  :class="{ unread: !item.is_read }"
                  @click="openNotification(item)"
                >
                  <div class="notification-item-top">
                    <span>{{ item.title }}</span>
                    <small>{{ item.createdLabel }}</small>
                  </div>
                  <p>{{ item.message }}</p>
                </button>
              </li>
            </ul>
          </section>
        </div>
        <button v-if="isLoggedIn" type="button" class="profile-btn" @click="openProfile">
          {{ String(currentUser?.name || 'U').trim().charAt(0).toUpperCase() || 'U' }}
        </button>
        <RouterLink v-else class="signin-btn" to="/legacy-app">Sign in</RouterLink>
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
  border: 1px solid #d6dde9;
  border-radius: 14px;
  background: #f9fafc;
  color: #334155;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
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

.notification-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: min(360px, 90vw);
  max-height: 420px;
  overflow: auto;
  border: 1px solid #dde3ee;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 14px 34px rgba(10, 28, 34, 0.1);
  padding: 8px;
  z-index: 90;
}

.notification-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 4px 4px 8px;
}

.notification-mark-all {
  border: 1px solid #d6dde9;
  border-radius: 8px;
  background: #fff;
  color: #334155;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 4px 8px;
  cursor: pointer;
}

.notification-empty {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
  padding: 8px 6px;
}

.notification-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 6px;
}

.notification-item {
  width: 100%;
  border: 1px solid #e2e8f3;
  border-radius: 10px;
  background: #fff;
  text-align: left;
  padding: 8px;
  cursor: pointer;
}

.notification-item.unread {
  border-color: #f3c29d;
  background: #fff8f1;
}

.notification-item-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.notification-item p {
  margin: 4px 0 0;
  font-size: 0.85rem;
  color: #475569;
}

.profile-btn {
  width: 42px;
  height: 42px;
  border: none;
  border-radius: 999px;
  background: #e79c53;
  color: #fff;
  font: inherit;
  font-size: 1.15rem;
  font-weight: 800;
  cursor: pointer;
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
