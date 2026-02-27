<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()
const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

const isHomeActive = computed(() => route.path === '/' || route.path === '/home')
const isAboutActive = computed(() => route.path === '/about')
const isServiceActive = computed(() => route.path.startsWith('/services'))
const isBookingActive = computed(() => route.path === '/booking')
const isFavoriteActive = computed(() => route.path === '/favorite')
const isContactActive = computed(() => route.path === '/contact')
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
          <button class="nav-drop-trigger" type="button" :class="{ active: isServiceActive }">
            Service
          </button>
          <div class="nav-drop-menu">
            <RouterLink to="/services/packages">Packages</RouterLink>
            <RouterLink to="/services/overall">Overall Service</RouterLink>
          </div>
        </div>

        <RouterLink to="/booking" :class="{ active: isBookingActive }">My Booking</RouterLink>
        <RouterLink to="/favorite" :class="{ active: isFavoriteActive }">Favorite</RouterLink>
        <RouterLink to="/contact" :class="{ active: isContactActive }">Contact</RouterLink>
      </nav>

      <div class="nav-actions">
        <RouterLink class="signin-btn" to="/legacy-app">Sign in</RouterLink>
      </div>
    </div>
  </header>
</template>

<style scoped>
.public-nav-wrap {
  background: #fff;
  border-top: 3px solid #2f333c;
  border-bottom: 1px solid #e3e7ef;
  padding: 0.82rem 0;
}

.nav-shell {
  width: min(1240px, calc(100% - 2rem));
  margin: 0 auto;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 1.1rem;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 0.72rem;
  text-decoration: none;
}

.brand-logo {
  width: 66px;
  height: 66px;
  border-radius: 14px;
  border: 1px solid #f2d2bb;
  background: #fff;
  object-fit: contain;
  padding: 0.3rem;
}

.brand-name {
  font-size: clamp(1.9rem, 2.4vw, 2.9rem);
  font-weight: 800;
  line-height: 1;
  color: #d46613;
}

.nav-links {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  gap: 0.45rem;
  flex-wrap: wrap;
}

.nav-links a,
.nav-drop-trigger {
  border: 1px solid transparent;
  border-radius: 999px;
  background: transparent;
  color: #334155;
  padding: 0.58rem 0.98rem;
  font: inherit;
  font-size: 1.02rem;
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
}

.signin-btn {
  border: 1px solid #d6dde9;
  border-radius: 14px;
  background: #f9fafc;
  color: #111827;
  font-size: 1.08rem;
  font-weight: 700;
  text-decoration: none;
  padding: 0.62rem 1.08rem;
}

@media (max-width: 980px) {
  .nav-shell {
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
