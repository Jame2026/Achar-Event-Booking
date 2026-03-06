<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import SiteFooter from './components/SiteFooter.vue'

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const route = useRoute()
const hasAuthUser = ref(Boolean(localStorage.getItem(AUTH_USER_STORAGE_KEY)))

function refreshAuthUserState() {
  hasAuthUser.value = Boolean(localStorage.getItem(AUTH_USER_STORAGE_KEY))
}

const hideFooter = computed(() => {
  if (route.path === '/reset-password' || route.path === '/forgot-password') return true
  if (route.path === '/legacy-app' && !hasAuthUser.value) return true
  return false
})

onMounted(() => {
  window.addEventListener('storage', refreshAuthUserState)
  window.addEventListener('achar:auth-updated', refreshAuthUserState)
})

onBeforeUnmount(() => {
  window.removeEventListener('storage', refreshAuthUserState)
  window.removeEventListener('achar:auth-updated', refreshAuthUserState)
})
</script>

<template>
<<<<<<< HEAD
  <div class="app-container">
    <router-view />
  </div>
=======
  <router-view />
  <SiteFooter v-if="!hideFooter" />
>>>>>>> 9d35ffa2d1ff350b05b246cd65a5b3509f643bb5
</template>

<script setup>
</script>

<style>
.app-container {
  min-height: 100vh;
  position: relative;
  z-index: 1;
}
</style>
