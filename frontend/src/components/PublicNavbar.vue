<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const isLoggedIn = ref(false)
const currentUser = ref(null)
const navSearch = ref('')

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

function logout() {
  localStorage.removeItem(AUTH_USER_STORAGE_KEY)
  refreshAuthState()
  router.push('/').catch(() => {})
}

function openProfile() {
  router.push('/legacy-app?page=profile').catch(() => {})
}

function runSearch() {
  const query = navSearch.value.trim()
  router
    .push({
      path: '/services/packages',
      query: query ? { q: query, event: 'all' } : { event: 'all' },
    })
    .catch(() => {})
}

onMounted(() => {
  refreshAuthState()
})

const isHomeActive = computed(() => route.path === '/' || route.path === '/home')
const isAboutActive = computed(() => route.path === '/about')
const isServiceActive = computed(() => route.path.startsWith('/services'))
const isServicePackagesActive = computed(() => route.path === '/services/packages')
const isServiceOverallActive = computed(() => route.path === '/services/overall')
const isBookingActive = computed(() => route.path === '/booking' || route.path === '/legacy-app')
const isFavoriteActive = computed(() => route.path === '/favorite')
const isContactActive = computed(() => route.path === '/contact')
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
        <RouterLink to="/favorite" :class="{ active: isFavoriteActive }">Favorite</RouterLink>
        <RouterLink to="/contact" :class="{ active: isContactActive }">Contact</RouterLink>
      </nav>

      <div class="nav-actions">
        <div v-if="isLoggedIn" class="auth-tools">
          <input
            v-model="navSearch"
            type="search"
            class="nav-search"
            placeholder="Search services & packages..."
            @keyup.enter="runSearch"
          />
          <button type="button" class="profile-btn" @click="openProfile">
            {{ String(currentUser?.name || 'U').trim().charAt(0).toUpperCase() || 'U' }}
          </button>
        </div>
        <button
          v-if="isLoggedIn"
          type="button"
          class="signin-btn"
          @click="logout"
        >
          Logout
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

.auth-tools {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.nav-search {
  width: min(300px, 30vw);
  border: 1px solid #d6dde9;
  border-radius: 14px;
  background: #f9fafc;
  color: #111827;
  font: inherit;
  font-size: 1rem;
  padding: 0.62rem 0.9rem;
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
}
</style>
