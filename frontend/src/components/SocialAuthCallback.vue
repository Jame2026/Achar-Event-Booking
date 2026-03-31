<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
const AUTH_TOKEN_STORAGE_KEY = 'token'
const SOCIAL_ERROR_STORAGE_KEY = 'achar_social_error'
const POST_AUTH_REDIRECT_KEY = 'achar_post_auth_redirect'
const POST_AUTH_REDIRECT_AT_KEY = 'achar_post_auth_redirect_at'
const POST_AUTH_REDIRECT_TTL_MS = 5 * 60 * 1000

const route = useRoute()
const router = useRouter()

function firstQueryValue(value) {
  if (Array.isArray(value)) return String(value[0] || '').trim()
  return typeof value === 'string' ? value.trim() : ''
}

function defaultRedirectForRole(role) {
  const normalizedRole = String(role || '').trim().toLowerCase()
  return normalizedRole === 'vendor' || normalizedRole === 'admin'
    ? '/legacy-app?page=dashboard'
    : '/legacy-app?page=bookings'
}

function consumePostAuthRedirect() {
  const redirectPath = sessionStorage.getItem(POST_AUTH_REDIRECT_KEY)
  const redirectAtRaw = sessionStorage.getItem(POST_AUTH_REDIRECT_AT_KEY)

  sessionStorage.removeItem(POST_AUTH_REDIRECT_KEY)
  sessionStorage.removeItem(POST_AUTH_REDIRECT_AT_KEY)

  if (!redirectPath || !redirectPath.startsWith('/')) {
    return ''
  }

  const redirectAt = Number(redirectAtRaw || 0)
  const isFresh = Number.isFinite(redirectAt) && Date.now() - redirectAt <= POST_AUTH_REDIRECT_TTL_MS

  return isFresh ? redirectPath : ''
}

function storeAuthUser(user) {
  localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(user))
  window.dispatchEvent(new Event('achar:auth-updated'))
}

function redirectTo(path) {
  router.replace(path).catch(() => {})
}

onMounted(() => {
  const socialStatus = firstQueryValue(route.query.social).toLowerCase()
  const message = firstQueryValue(route.query.message)
  const token = firstQueryValue(route.query.token)

  if (token) {
    localStorage.setItem(AUTH_TOKEN_STORAGE_KEY, token)
  }

  if (socialStatus === 'error') {
    localStorage.setItem(SOCIAL_ERROR_STORAGE_KEY, message || 'Social login failed.')
    redirectTo('/login')
    return
  }

  const name = firstQueryValue(route.query.name)
  const email = firstQueryValue(route.query.email)
  const phone = firstQueryValue(route.query.phone)
  const role = firstQueryValue(route.query.role) || 'user'
  const idValue = Number(firstQueryValue(route.query.id) || 0)
  const hasUserIdentity = Boolean(name && (email || phone))

  if (hasUserIdentity) {
    storeAuthUser({
      id: Number.isFinite(idValue) && idValue > 0 ? idValue : Date.now(),
      name,
      email: email || '',
      phone: phone || '',
      role,
    })

    redirectTo(consumePostAuthRedirect() || defaultRedirectForRole(role))
    return
  }

  if (token) {
    redirectTo(consumePostAuthRedirect() || '/')
    return
  }

  localStorage.setItem(
    SOCIAL_ERROR_STORAGE_KEY,
    message || 'Social login did not return valid user info.',
  )
  redirectTo('/login')
})
</script>

<template>
  <p>Logging in...</p>
</template>
