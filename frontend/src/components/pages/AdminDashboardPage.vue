<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  appLogoSrc: {
    type: String,
    default: "",
  },
  adminDisplayName: {
    type: String,
    default: "Admin",
  },
  logoutUser: {
    type: Function,
    default: null,
  },
});

const searchQuery = ref("");
const navItems = [
  { key: "dashboard", label: "Dashboard" },
  { key: "events", label: "Events" },
  { key: "bookings", label: "Bookings" },
  { key: "vendors", label: "Vendors" },
  { key: "users", label: "Users" },
  { key: "revenue", label: "Revenue" },
  { key: "settings", label: "Settings" },
];
const activeKey = ref("dashboard");
const stats = [
  { label: "Total Events", value: "1,284", delta: "+12%", tone: "up" },
  { label: "Total Bookings", value: "856", delta: "+5.4%", tone: "up" },
  { label: "Total Users", value: "4,320", delta: "-2.1%", tone: "down" },
  { label: "Revenue Summary", value: "$42,950.00", delta: "+18%", tone: "solid" },
];
const activity = [
  { name: "Sophea Van", detail: "created a new event “Modern Loft Showcase”", time: "2 hours ago" },
  { name: "Meng Kong", detail: "completed a booking for “Design Consultation”", time: "5 hours ago" },
  { name: "Dara Rath", detail: "submitted a revenue withdrawal request", time: "1 day ago" },
  { name: "Srey Pich", detail: "registered as a new user", time: "2 days ago" },
];
const initials = computed(() => {
  const pieces = String(props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});
</script>

<template>
  <section class="admin-shell">
    <aside class="admin-sidebar">
      <div class="brand">
        <div class="brand-logo">
          <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
          <div v-else class="brand-mark">A</div>
        </div>
        <div>
          <p class="brand-title">Architect Admin</p>
          <p class="brand-subtitle">Management Suite</p>
        </div>
      </div>

      <nav class="admin-nav">
        <button
          v-for="item in navItems"
          :key="item.key"
          type="button"
          class="nav-item"
          :class="{ active: activeKey === item.key }"
          @click="activeKey = item.key"
        >
          <span class="nav-dot"></span>
          <span>{{ item.label }}</span>
        </button>
      </nav>

      <div class="admin-user-card">
        <div class="avatar">{{ initials }}</div>
        <div>
          <p class="user-name">{{ adminDisplayName }}</p>
          <p class="user-role">Super Admin</p>
        </div>
        <button v-if="logoutUser" class="logout-btn" type="button" @click="logoutUser">
          Log out
        </button>
      </div>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <label class="search">
          <span class="search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path
                d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z"
              />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" placeholder="Search insights..." />
        </label>
        <div class="topbar-actions">
          <button class="icon-btn" type="button" title="Notifications" aria-label="Notifications">
            <svg viewBox="0 0 24 24">
              <path
                d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z"
              />
            </svg>
          </button>
          <button class="icon-btn" type="button" title="Help" aria-label="Help">
            <svg viewBox="0 0 24 24">
              <path
                d="M12 19a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 19Zm0-15a6 6 0 0 1 6 6c0 2.455-1.812 3.498-2.83 4.085-.87.505-1.17.75-1.17 1.415v.5h-2v-.5c0-1.86 1.126-2.512 2.17-3.115C14.98 11.83 16 11.235 16 10a4 4 0 0 0-8 0H6a6 6 0 0 1 6-6Z"
              />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="admin-hero">
        <p class="eyebrow">Design Management Dashboard</p>
        <p class="hero-subtitle">Overview of your design management workspace</p>
      </section>

      <section class="admin-stats">
        <article
          v-for="card in stats"
          :key="card.label"
          class="stat-card"
          :class="card.tone"
        >
          <div class="stat-icon"></div>
          <p class="stat-label">{{ card.label }}</p>
          <p class="stat-value">{{ card.value }}</p>
          <span class="stat-delta" :class="card.tone">{{ card.delta }}</span>
        </article>
      </section>

      <section class="admin-grid">
        <article class="activity-card">
          <header>
            <h3>Recent Activity</h3>
            <button class="link-btn" type="button">View All →</button>
          </header>
          <div class="activity-list">
            <div v-for="item in activity" :key="item.name + item.time" class="activity-item">
              <div class="activity-icon"></div>
              <div>
                <p class="activity-text">
                  <strong>{{ item.name }}</strong>
                  {{ item.detail }}
                </p>
                <p class="activity-time">{{ item.time }}</p>
              </div>
            </div>
          </div>
        </article>

        <div class="side-stack">
          <article class="report-card">
            <h3>Monthly Report</h3>
            <p>
              Design project growth has increased by 24% compared to last month.
            </p>
            <button class="primary-btn" type="button">Download PDF</button>
          </article>

          <article class="status-card">
            <h3>System Status</h3>
            <div class="status-row">
              <div>
                <p>Server Uptime</p>
                <span>99.9%</span>
              </div>
              <div class="bar">
                <span class="bar-fill" style="width: 99.9%"></span>
              </div>
            </div>
            <div class="status-row">
              <div>
                <p>Database Load</p>
                <span>32%</span>
              </div>
              <div class="bar">
                <span class="bar-fill warning" style="width: 32%"></span>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap");

.admin-shell {
  display: grid;
  grid-template-columns: minmax(240px, 280px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Sora", "Segoe UI", sans-serif;
  background: radial-gradient(circle at top right, #fff6ed 0%, #f7fafb 50%, #f3f7f9 100%);
  color: #1f2a37;
}

.admin-sidebar {
  background: #f7f8fb;
  border-right: 1px solid #e8edf3;
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-logo {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #fff;
  display: grid;
  place-items: center;
  box-shadow: 0 10px 20px rgba(255, 120, 26, 0.18);
}

.brand-logo img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.brand-mark {
  font-weight: 700;
  color: #ff7a1a;
}

.brand-title {
  font-weight: 600;
  margin: 0;
}

.brand-subtitle {
  margin: 2px 0 0;
  font-size: 12px;
  color: #7c8794;
}

.admin-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid transparent;
  background: transparent;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  cursor: pointer;
  color: #5b6573;
  transition: all 0.2s ease;
}

.nav-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cdd6df;
}

.nav-item.active {
  background: #fff3ea;
  color: #ff7a1a;
  border-color: #ffd9bf;
  box-shadow: inset 3px 0 0 #ff7a1a;
}

.nav-item.active .nav-dot {
  background: #ff7a1a;
}

.admin-user-card {
  margin-top: auto;
  background: #fff;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
  display: grid;
  gap: 8px;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #ffe6d1;
  color: #c65300;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.user-name {
  font-weight: 600;
  margin: 0;
}

.user-role {
  margin: 0;
  font-size: 12px;
  color: #7c8794;
}

.logout-btn {
  margin-top: 6px;
  border: none;
  background: #f1f5f9;
  padding: 8px 12px;
  border-radius: 10px;
  font-size: 12px;
  cursor: pointer;
}

.admin-main {
  padding: 28px 36px 40px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.admin-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.search {
  flex: 1;
  max-width: 360px;
  display: flex;
  align-items: center;
  background: #f0f2f4;
  border-radius: 12px;
  padding: 8px 12px;
  gap: 8px;
}

.search-icon svg {
  width: 16px;
  height: 16px;
  fill: #9aa5b1;
}

.search input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  outline: none;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-btn {
  border: none;
  background: #fff;
  width: 36px;
  height: 36px;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: #6b7280;
}

.topbar-avatar {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  background: #ffb98b;
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 600;
}

.admin-hero .eyebrow {
  font-size: 22px;
  font-weight: 700;
  margin: 0;
  color: #ff7a1a;
}

.hero-subtitle {
  margin: 6px 0 0;
  color: #7c8794;
}

.admin-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}

.stat-card {
  background: #fff;
  padding: 18px;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
  position: relative;
  overflow: hidden;
}

.stat-card.solid {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 100%);
  color: #fff;
}

.stat-icon {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  background: #ffe6d1;
  margin-bottom: 12px;
}

.stat-card.solid .stat-icon {
  background: rgba(255, 255, 255, 0.2);
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: inherit;
  opacity: 0.7;
}

.stat-value {
  margin: 6px 0 0;
  font-size: 22px;
  font-weight: 700;
}

.stat-delta {
  position: absolute;
  top: 16px;
  right: 16px;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 999px;
  background: #e9f7ef;
  color: #2f9e5f;
}

.stat-delta.down {
  background: #ffe8e5;
  color: #e2553f;
}

.stat-card.solid .stat-delta {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.admin-grid {
  display: grid;
  grid-template-columns: minmax(280px, 2fr) minmax(220px, 1fr);
  gap: 20px;
  align-items: start;
}

.activity-card,
.report-card,
.status-card {
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}

.activity-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.activity-list {
  display: grid;
  gap: 14px;
}

.activity-item {
  display: flex;
  gap: 12px;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f4f8;
}

.activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  background: #ffe6d1;
}

.activity-text {
  margin: 0;
  font-size: 14px;
}

.activity-time {
  margin: 4px 0 0;
  font-size: 12px;
  color: #7c8794;
}

.link-btn {
  border: none;
  background: transparent;
  color: #ff7a1a;
  cursor: pointer;
}

.side-stack {
  display: grid;
  gap: 18px;
}

.report-card {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 100%);
  color: #fff;
}

.report-card p {
  margin: 12px 0 16px;
  opacity: 0.9;
}

.primary-btn {
  border: none;
  background: #fff;
  color: #f15b2a;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

.status-row {
  display: grid;
  gap: 10px;
  margin-top: 12px;
}

.status-row p {
  margin: 0;
  font-size: 13px;
}

.status-row span {
  font-weight: 600;
}

.bar {
  height: 6px;
  border-radius: 999px;
  background: #eef2f6;
  overflow: hidden;
}

.bar-fill {
  display: block;
  height: 100%;
  background: #2f9e5f;
}

.bar-fill.warning {
  background: #f59f00;
}

@media (max-width: 1024px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .admin-sidebar {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
  }

  .admin-nav {
    display: none;
  }

  .admin-user-card {
    margin-top: 0;
  }

  .admin-grid {
    grid-template-columns: 1fr;
  }
}
</style>
