<script setup lang="ts">
defineProps<{
  user: {
    id: number
    name: string
    email: string
    role: string
  }
}>()

defineEmits<{
  logout: []
}>()
</script>

<template>
  <section class="home-shell">
    <header class="home-topbar">
      <div class="brand-row">
        <span class="brand-dot"></span>
        <span class="brand-name">Achar Booking</span>
      </div>
      <button class="link-btn" type="button" @click="$emit('logout')">Logout</button>
    </header>

    <main class="home-content">
      <h1>Welcome, {{ user.name }}</h1>
      <p>This is an example homepage shown after correct login credentials.</p>

      <div class="home-grid">
        <article class="home-card">
          <h2>Account</h2>
          <p><strong>Email:</strong> {{ user.email }}</p>
          <p><strong>Role:</strong> {{ user.role }}</p>
        </article>

        <article class="home-card">
          <h2>Next Step</h2>
          <p>You can now replace this page with your actual dashboard modules.</p>
        </article>
      </div>
    </main>
  </section>
</template>
