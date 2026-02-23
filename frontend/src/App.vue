<script setup lang="ts">
import { ref } from 'vue'
import Register from './components/RegisterForm.vue'
import Login from './components/LoginForm.vue'
import HomePage from './components/HomePage.vue'

const currentView = ref<'register' | 'login'>('login')
const loggedInUser = ref<{
  id: number
  name: string
  email: string
  role: string
} | null>(null)

const toggleView = () => {
  currentView.value = currentView.value === 'register' ? 'login' : 'register'
}

const onLoginSuccess = (user: { id: number; name: string; email: string; role: string }) => {
  loggedInUser.value = user
}

const logout = () => {
  loggedInUser.value = null
  currentView.value = 'login'
}
</script>

<template>
  <div class="auth-root">
    <HomePage v-if="loggedInUser" :user="loggedInUser" @logout="logout" />
    <Register v-else-if="currentView === 'register'" @switch="toggleView" />
    <Login v-else @switch="toggleView" @success="onLoginSuccess" />
  </div>
</template>
