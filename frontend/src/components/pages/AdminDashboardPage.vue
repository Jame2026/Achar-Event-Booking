<script setup>
import { computed, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";

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
  { key: "dashboard", label: "Dashboard", icon: "dashboard" },
  { key: "events", label: "Events", icon: "events" },
  { key: "admin-bookings", label: "Bookings", icon: "bookings" },
  { key: "vendors", label: "Vendors", icon: "vendors" },
  { key: "customers", label: "Customers", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];
const router = useRouter();
const route = useRoute();
const activeKey = ref("dashboard");
const stats = [
  { label: "Total Events", value: "1,284", delta: "+12%", tone: "up" },
  { label: "Total Bookings", value: "856", delta: "+5.4%", tone: "up" },
  { label: "Total Customers", value: "4,320", delta: "-2.1%", tone: "down" },
  { label: "Revenue Summary", value: "$42,950.00", delta: "+18%", tone: "solid" },
];
const activity = [
  { name: "Sophea Van", detail: "created a new event “Modern Loft Showcase”", time: "2 hours ago" },
  { name: "Meng Kong", detail: "completed a booking for “Design Consultation”", time: "5 hours ago" },
  { name: "Dara Rath", detail: "submitted a revenue withdrawal request", time: "1 day ago" },
  { name: "Srey Pich", detail: "registered as a new customer", time: "2 days ago" },
];
const initials = computed(() => {
  const pieces = String(props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});

const getRoutePage = () => {
  const raw = route.query.page;
  return Array.isArray(raw) ? raw[0] : raw;
};

const syncActiveKey = () => {
  const page = getRoutePage();
  activeKey.value = page || "dashboard";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
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
        @click="navigateTo(item.key)"
      >
          <span class="nav-icon" aria-hidden="true">
            <svg v-if="item.icon === 'dashboard'" viewBox="0 0 24 24">
              <path
                d="M4 12.5 11.5 4 20 12.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"
              />
            </svg>
            <svg v-else-if="item.icon === 'events'" viewBox="0 0 24 24">
              <path
                d="M7 3v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3zm12 6H5v10h14z"
              />
            </svg>
            <svg v-else-if="item.icon === 'bookings'" viewBox="0 0 24 24">
              <path
                d="M6 4h12a2 2 0 0 1 2 2v4H4V6a2 2 0 0 1 2-2zm-2 8h16v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"
              />
            </svg>
            <svg v-else-if="item.icon === 'vendors'" viewBox="0 0 24 24">
              <path
                d="M4 10h16l-1.5 9a2 2 0 0 1-2 1H7.5a2 2 0 0 1-2-1L4 10zm4-6h8l1 4H7z"
              />
            </svg>
            <svg v-else-if="item.icon === 'users'" viewBox="0 0 24 24">
              <path
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z"
              />
            </svg>
            <svg v-else-if="item.icon === 'revenue'" viewBox="0 0 24 24">
              <path
                d="M4 18h16v2H4zm2-4h3v3H6zm5-6h3v9h-3zm5-3h3v12h-3z"
              />
            </svg>
            <svg v-else viewBox="0 0 24 24">
              <path
                d="M12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4zm8.5 4a6.5 6.5 0 0 0-.08-1l2.08-1.6-2-3.46-2.45 1a6.86 6.86 0 0 0-1.73-1L14 2h-4l-.32 2.94a6.86 6.86 0 0 0-1.73 1l-2.45-1-2 3.46L5.58 11a6.5 6.5 0 0 0 0 2l-2.08 1.6 2 3.46 2.45-1a6.86 6.86 0 0 0 1.73 1L10 22h4l.32-2.94a6.86 6.86 0 0 0 1.73-1l2.45 1 2-3.46L20.42 13a6.5 6.5 0 0 0 .08-1z"
              />
            </svg>
          </span>
          <span>{{ item.label }}</span>
        </button>
        <RouterLink class="nav-item home-link" to="/">
          <span class="nav-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path
                d="M10.707 6.293 5 12l5.707 5.707 1.414-1.414L8.828 13H20v-2H8.828l3.293-3.293-1.414-1.414Z"
              />
            </svg>
          </span>
          <span>Back to Home</span>
        </RouterLink>
      </nav>

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
        <p class="eyebrow">Achar Event Admin</p>
        <h1 class="hero-title">Dashboard Overview</h1>
        <p class="hero-subtitle">Track bookings, vendors, and revenue at a glance.</p>
        <div class="hero-actions">
          <button class="ghost-btn" type="button">Create Event</button>
          <button class="primary-btn" type="button">Export Report</button>
        </div>
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
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.admin-shell {
  --ink: #111827;
  --muted: #5f6b7a;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #ffe7d2;
  --accent-cool: #4a90e2;
  --surface: rgba(255, 255, 255, 0.86);
  --surface-strong: #ffffff;
  --stroke: rgba(17, 24, 39, 0.08);
  --shadow: 0 26px 70px rgba(15, 23, 42, 0.12);
  --shadow-soft: 0 14px 30px rgba(15, 23, 42, 0.08);
  display: grid;
  grid-template-columns: minmax(300px, 360px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
  background: radial-gradient(circle at 12% 0%, #fff2e6 0%, #f5f6ff 45%, #eef6f9 100%);
  color: var(--ink);
}

.admin-sidebar {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.92) 0%, #f5f7fc 100%);
  border-right: 1px solid var(--stroke);
  padding: 36px 28px;
  display: flex;
  flex-direction: column;
  gap: 28px;
  backdrop-filter: blur(12px);
  position: relative;
}

.admin-sidebar::after {
  content: "";
  position: absolute;
  inset: 24px;
  border-radius: 24px;
  background: linear-gradient(160deg, rgba(255, 122, 26, 0.06), transparent 45%);
  pointer-events: none;
}

.admin-sidebar > * {
  position: relative;
  z-index: 1;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
}

.brand-logo {
  width: 46px;
  height: 46px;
  border-radius: 16px;
  background: linear-gradient(135deg, #fff1e4 0%, #ffe2cb 100%);
  display: grid;
  place-items: center;
  box-shadow: 0 14px 28px rgba(255, 122, 26, 0.22);
  border: 1px solid rgba(255, 122, 26, 0.2);
}

.brand-logo img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.brand-mark {
  font-weight: 700;
  color: var(--accent);
}

.brand-title {
  font-weight: 600;
  margin: 0;
  font-family: "Fraunces", serif;
  letter-spacing: 0.2px;
}

.brand-subtitle {
  margin: 2px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.admin-nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(17, 24, 39, 0.06);
  padding: 14px;
  border-radius: 24px;
  box-shadow: var(--shadow-soft);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid transparent;
  background: transparent;
  padding: 14px 16px;
  border-radius: 18px;
  font-size: 15px;
  cursor: pointer;
  color: #4b5563;
  transition: all 0.2s ease;
}

.nav-icon {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: radial-gradient(circle at 30% 20%, #f8fafc 0%, #eef2f7 70%);
  color: #94a3b8;
  transition: all 0.2s ease;
  border: 1px solid rgba(148, 163, 184, 0.15);
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.nav-item:hover {
  background: rgba(255, 122, 26, 0.08);
  color: var(--accent);
  transform: translateX(2px);
}

.nav-item.active {
  background: linear-gradient(135deg, #fff4ea 0%, #ffe2ce 100%);
  color: var(--accent);
  border-color: rgba(255, 122, 26, 0.2);
  box-shadow: inset 3px 0 0 var(--accent), 0 8px 18px rgba(255, 122, 26, 0.18);
}

.nav-item.active .nav-icon {
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.2), rgba(255, 122, 26, 0.05));
  color: var(--accent);
  border-color: rgba(255, 122, 26, 0.25);
}

.home-link {
  text-decoration: none;
}

.admin-main {
  padding: 28px 36px 40px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  position: relative;
  overflow: hidden;
}

.admin-main::before {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 16% 8%, rgba(255, 122, 26, 0.08), transparent 42%),
    radial-gradient(circle at 80% 18%, rgba(74, 144, 226, 0.12), transparent 50%),
    radial-gradient(circle at 60% 80%, rgba(255, 122, 26, 0.06), transparent 45%);
  pointer-events: none;
}

.admin-main > * {
  position: relative;
  z-index: 1;
}

.admin-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.search {
  flex: 1;
  max-width: 380px;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 16px;
  padding: 10px 14px;
  gap: 8px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search:focus-within {
  transform: translateY(-1px);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
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
  color: var(--ink);
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-btn {
  border: 1px solid var(--stroke);
  background: #fff;
  width: 38px;
  height: 38px;
  border-radius: 14px;
  box-shadow: var(--shadow-soft);
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.icon-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: #6b7280;
}

.topbar-avatar {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #ffb98b 0%, #ff8b3d 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 600;
}

.admin-hero {
  display: grid;
  gap: 10px;
}

.admin-hero .eyebrow {
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  margin: 0;
  color: #9a4d14;
}

.hero-title {
  margin: 0;
  font-size: 36px;
  font-weight: 700;
  font-family: "Fraunces", serif;
  color: var(--ink);
}

.hero-subtitle {
  margin: 0;
  color: var(--muted);
  font-size: 15px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.admin-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}

.stat-card {
  background: var(--surface);
  padding: 18px;
  border-radius: 18px;
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  border: 1px solid var(--stroke);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.08), transparent 55%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.stat-card:hover::after {
  opacity: 1;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
}

.stat-card.solid {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 100%);
  color: #fff;
  border-color: transparent;
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
  opacity: 0.75;
}

.stat-value {
  margin: 6px 0 0;
  font-size: 24px;
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
  grid-template-columns: minmax(280px, 2fr) minmax(240px, 1fr);
  gap: 20px;
  align-items: start;
}

.activity-card,
.report-card,
.status-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 20px;
  box-shadow: var(--shadow);
  border: 1px solid var(--stroke);
}

.activity-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.activity-card h3,
.report-card h3,
.status-card h3 {
  margin: 0;
  font-family: "Fraunces", serif;
  font-weight: 600;
  color: var(--ink);
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
  transition: transform 0.2s ease;
}

.activity-item:hover {
  transform: translateX(4px);
}

.activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.activity-icon {
  width: 42px;
  height: 42px;
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
  color: var(--muted);
}

.link-btn {
  border: none;
  background: transparent;
  color: var(--accent);
  cursor: pointer;
  font-weight: 600;
}

.side-stack {
  display: grid;
  gap: 18px;
}

.report-card {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 60%, #ff9a4d 100%);
  color: #fff;
  border: none;
  position: relative;
  overflow: hidden;
}

.report-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 45%);
  opacity: 0.7;
}

.report-card > * {
  position: relative;
  z-index: 1;
}

.report-card p {
  margin: 12px 0 16px;
  opacity: 0.9;
}

.primary-btn {
  border: none;
  background: linear-gradient(135deg, #ff8a3c 0%, #f15b2a 100%);
  color: #fff;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(241, 91, 42, 0.25);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.primary-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(241, 91, 42, 0.3);
}

.ghost-btn {
  border: 1px solid rgba(255, 122, 26, 0.3);
  background: rgba(255, 255, 255, 0.8);
  color: #c65300;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ghost-btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-soft);
}

.report-card .primary-btn {
  background: #fff;
  color: #f15b2a;
  box-shadow: none;
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
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    padding: 20px;
    gap: 16px;
  }

  .admin-nav {
    display: flex;
    flex-direction: row;
    gap: 10px;
    overflow-x: auto;
    padding: 10px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid var(--stroke);
  }

  .admin-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .admin-main {
    padding: 24px;
  }

  .admin-topbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search {
    max-width: none;
  }

  .hero-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .nav-item {
    flex: 0 0 auto;
    white-space: nowrap;
  }
}
</style>


