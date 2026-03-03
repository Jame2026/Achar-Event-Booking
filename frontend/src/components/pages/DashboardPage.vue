<script setup>
const props = defineProps([
  'notice',
  'customerName',
  'dashboardStats',
  'recentBookings',
  'recentConversations',
  'goToVendor',
  'goToBookings',
  'goToMessages',
  'goToPackageCustomization',
  'openUpcomingBookings',
])
</script>

<template>
  <main class="shell dashboard-page">
    <div class="breadcrumbs">Home > Dashboard</div>

    <section class="dashboard-head">
      <div class="dashboard-head-main">
        <span class="dash-chip">Planner Workspace</span>
        <h1>Welcome back, {{ props.customerName || 'Planner' }}</h1>
        <p>Track your planning progress, follow updates, and move your events forward in one place.</p>
        <div class="dashboard-inline-stats">
          <span><strong>{{ props.dashboardStats.upcomingBookings }}</strong> upcoming</span>
          <span><strong>{{ props.dashboardStats.completedBookings }}</strong> completed</span>
          <span><strong>{{ props.dashboardStats.unreadMessages }}</strong> active chats</span>
        </div>
      </div>
      <div class="dashboard-actions">
        <button type="button" class="btn-light" @click="props.goToVendor()">View Vendors</button>
        <button type="button" class="btn-accent" @click="props.goToBookings">View Bookings</button>
      </div>
    </section>

    <p v-if="props.notice" class="notice">{{ props.notice }}</p>

    <section class="kpi-grid">
      <article class="card kpi-card">
        <p>Total Bookings</p>
        <strong>{{ props.dashboardStats.totalBookings }}</strong>
        <small>All planned events</small>
      </article>
      <article class="card kpi-card">
        <p>Upcoming</p>
        <strong>{{ props.dashboardStats.upcomingBookings }}</strong>
        <small>Next milestone focus</small>
      </article>
      <article class="card kpi-card">
        <p>Completed</p>
        <strong>{{ props.dashboardStats.completedBookings }}</strong>
        <small>Successfully delivered</small>
      </article>
      <article class="card kpi-card">
        <p>New Messages</p>
        <strong>{{ props.dashboardStats.unreadMessages }}</strong>
        <small>Unread this session</small>
      </article>
    </section>

    <section class="dashboard-grid">
      <article class="card dashboard-card">
        <div class="dashboard-card-head">
          <h2>Recent Bookings</h2>
          <a href="#" @click.prevent="props.goToBookings">See all</a>
        </div>
        <div class="dashboard-list">
          <div v-for="item in props.recentBookings" :key="item.id" class="dashboard-item">
            <img :src="item.image" :alt="item.vendor" />
            <div>
              <strong>{{ item.service }}</strong>
              <p>{{ item.metaValue }}</p>
            </div>
            <span class="booking-status" :class="item.statusClass">{{ item.status }}</span>
          </div>
        </div>
      </article>

      <article class="card dashboard-card">
        <div class="dashboard-card-head">
          <h2>Recent Messages</h2>
          <a href="#" @click.prevent="props.goToMessages()">Open inbox</a>
        </div>
        <div class="dashboard-list">
          <div
            v-for="chat in props.recentConversations"
            :key="chat.id"
            class="dashboard-item message"
            @click="props.goToMessages(chat.name)"
          >
            <img :src="chat.image" :alt="chat.name" />
            <div>
              <strong>{{ chat.name }}</strong>
              <p>{{ chat.preview }}</p>
            </div>
            <span class="dash-time">{{ chat.time }}</span>
          </div>
        </div>
      </article>

      <article class="card dashboard-card wide">
        <div class="dashboard-card-head">
          <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions">
          <button type="button" @click="props.goToVendor()">
            <strong>Browse Vendors</strong>
            <span>Explore categories and compare providers</span>
          </button>
          <button type="button" @click="props.goToPackageCustomization()">
            <strong>Book Event Package</strong>
            <span>Reserve a service in a few steps</span>
          </button>
          <button type="button" @click="props.openUpcomingBookings">
            <strong>Manage Upcoming Events</strong>
            <span>Review confirmations and next actions</span>
          </button>
          <button type="button" @click="props.goToMessages()">
            <strong>Contact Vendors</strong>
            <span>Follow up and keep conversations moving</span>
          </button>
        </div>
      </article>
    </section>
  </main>
</template>
