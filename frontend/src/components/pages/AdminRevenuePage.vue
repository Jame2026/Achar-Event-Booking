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

const router = useRouter();
const route = useRoute();
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
const activeKey = ref("revenue");
const stats = [
  { label: "Total Revenue", value: "$42,950.00", delta: "+12.4%" },
  { label: "Pending Payouts", value: "$12,400.00", delta: "8 active" },
  { label: "Platform Fees", value: "$6,442.50", delta: "1.5% fixed" },
  { label: "Net Profit", value: "$24,107.50", delta: "+5.2%" },
];
const transactions = [
  { id: "#TXN-88421", name: "Skyline Media", date: "Oct 24, 2023", amount: "$1,240.00", status: "Completed" },
  { id: "#TXN-88390", name: "Lumina Projects", date: "Oct 23, 2023", amount: "$2,850.00", status: "Pending" },
  { id: "#TXN-88385", name: "Vertex Build", date: "Oct 22, 2023", amount: "$4,100.00", status: "Failed" },
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
  activeKey.value = page || "revenue";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
</script>

<template>
  <section class="admin-shell revenue-shell">
    <aside class="admin-sidebar">
      <div class="brand">
        <div class="brand-logo">
          <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
          <div v-else class="brand-mark">A</div>
        </div>
        <div>
          <p class="brand-title">Revenue Admin</p>
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
              <path d="M4 12.5 11.5 4 20 12.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z" />
            </svg>
            <svg v-else-if="item.icon === 'events'" viewBox="0 0 24 24">
              <path d="M7 3v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3zm12 6H5v10h14z" />
            </svg>
            <svg v-else-if="item.icon === 'bookings'" viewBox="0 0 24 24">
              <path d="M6 4h12a2 2 0 0 1 2 2v4H4V6a2 2 0 0 1 2-2zm-2 8h16v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z" />
            </svg>
            <svg v-else-if="item.icon === 'vendors'" viewBox="0 0 24 24">
              <path d="M4 10h16l-1.5 9a2 2 0 0 1-2 1H7.5a2 2 0 0 1-2-1L4 10zm4-6h8l1 4H7z" />
            </svg>
            <svg v-else-if="item.icon === 'users'" viewBox="0 0 24 24">
              <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z" />
            </svg>
            <svg v-else-if="item.icon === 'revenue'" viewBox="0 0 24 24">
              <path d="M4 18h16v2H4zm2-4h3v3H6zm5-6h3v9h-3zm5-3h3v12h-3z" />
            </svg>
            <svg v-else viewBox="0 0 24 24">
              <path d="M12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4zm8.5 4a6.5 6.5 0 0 0-.08-1l2.08-1.6-2-3.46-2.45 1a6.86 6.86 0 0 0-1.73-1L14 2h-4l-.32 2.94a6.86 6.86 0 0 0-1.73 1l-2.45-1-2 3.46L5.58 11a6.5 6.5 0 0 0 0 2l-2.08 1.6 2 3.46 2.45-1a6.86 6.86 0 0 0 1.73 1L10 22h4l.32-2.94a6.86 6.86 0 0 0 1.73-1l2.45 1 2-3.46L20.42 13a6.5 6.5 0 0 0 .08-1z" />
            </svg>
          </span>
          <span>{{ item.label }}</span>
        </button>
        <RouterLink class="nav-item home-link" to="/">
          <span class="nav-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M10.707 6.293 5 12l5.707 5.707 1.414-1.414L8.828 13H20v-2H8.828l3.293-3.293-1.414-1.414Z" />
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
              <path d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z" />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" placeholder="Search revenue records..." />
        </label>
        <div class="topbar-actions">
          <button class="icon-btn" type="button" title="Notifications" aria-label="Notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <button class="icon-btn" type="button" title="Help" aria-label="Help">
            <svg viewBox="0 0 24 24">
              <path d="M12 19a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 19Zm0-15a6 6 0 0 1 6 6c0 2.455-1.812 3.498-2.83 4.085-.87.505-1.17.75-1.17 1.415v.5h-2v-.5c0-1.86 1.126-2.512 2.17-3.115C14.98 11.83 16 11.235 16 10a4 4 0 0 0-8 0H6a6 6 0 0 1 6-6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="revenue-hero">
        <div>
          <p class="eyebrow">Revenue Management</p>
          <h1 class="hero-title">Financial Health Overview</h1>
          <p class="hero-subtitle">Monitor payouts, platform fees, and profit trends in one place.</p>
        </div>
        <div class="hero-actions">
          <button class="ghost-btn" type="button">Last 30 Days</button>
          <button class="primary-btn" type="button">Export Report</button>
        </div>
      </section>

      <section class="revenue-stats">
        <article v-for="card in stats" :key="card.label" class="stat-card">
          <div class="stat-meta">
            <p class="stat-label">{{ card.label }}</p>
            <span class="stat-delta">{{ card.delta }}</span>
          </div>
          <p class="stat-value">{{ card.value }}</p>
          <div class="sparkline">
            <span></span><span></span><span></span><span></span><span></span><span></span>
          </div>
        </article>
      </section>

      <section class="revenue-grid">
        <article class="card wide">
          <header>
            <h3>Revenue Trends</h3>
            <div class="pill-group">
              <button class="pill active" type="button">Gross</button>
              <button class="pill" type="button">Net</button>
            </div>
          </header>
          <div class="bar-chart">
            <span v-for="i in 12" :key="i" class="bar"></span>
          </div>
        </article>

        <article class="card side">
          <h3>Payout Management</h3>
          <div class="payout-summary">
            <div>
              <p>Available for Payout</p>
              <h4>$18,650.00</h4>
            </div>
            <span class="status-pill">Ready</span>
          </div>
          <div class="payout-list">
            <div>
              <span>Bank Transfers</span>
              <strong>$12,400</strong>
            </div>
            <div>
              <span>Card Refunds</span>
              <strong>$1,200</strong>
            </div>
            <div>
              <span>Platform Fees</span>
              <strong class="danger">$942</strong>
            </div>
          </div>
          <button class="primary-btn full" type="button">Process Payouts Now</button>
        </article>

        <article class="card wide">
          <header>
            <h3>Transaction History</h3>
            <button class="link-btn" type="button">View All →</button>
          </header>
          <div class="table">
            <div class="table-head">
              <span>Transaction</span>
              <span>Vendor</span>
              <span>Date</span>
              <span>Amount</span>
              <span>Status</span>
            </div>
            <div v-for="item in transactions" :key="item.id" class="table-row">
              <span>{{ item.id }}</span>
              <span>{{ item.name }}</span>
              <span>{{ item.date }}</span>
              <span>{{ item.amount }}</span>
              <span class="status" :class="item.status.toLowerCase()">{{ item.status }}</span>
            </div>
          </div>
        </article>

        <article class="card side projection">
          <h3>Quarterly Projection</h3>
          <p>Based on current growth of 12.4% MoM.</p>
          <h4>$154,200</h4>
          <span class="status-pill">On track</span>
        </article>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.revenue-shell {
  --ink: #0f172a;
  --muted: #64748b;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #fff1e7;
  --accent-cool: #ff9a4d;
  --surface: rgba(255, 255, 255, 0.9);
  --surface-strong: #ffffff;
  --stroke: rgba(15, 23, 42, 0.08);
  --shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
  --shadow-soft: 0 14px 30px rgba(15, 23, 42, 0.08);
  display: grid;
  grid-template-columns: minmax(300px, 360px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
  background: radial-gradient(circle at 8% 0%, #fff1e6 0%, #f7f2ee 35%, #eef6f9 100%);
  color: var(--ink);
  position: relative;
  overflow: hidden;
}

.revenue-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.revenue-shell > * {
  position: relative;
  z-index: 1;
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
  background: linear-gradient(160deg, rgba(255, 122, 26, 0.08), transparent 45%);
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
  border: 1px solid rgba(15, 23, 42, 0.06);
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
  color: #475569;
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
  fill: #94a3b8;
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

.revenue-hero {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(15, 23, 42, 0.06);
  padding: 20px 24px;
  border-radius: 24px;
  box-shadow: var(--shadow-soft);
}

.eyebrow {
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 1.4px;
  color: #c45a12;
  margin: 0;
}

.hero-title {
  margin: 6px 0 0;
  font-size: 34px;
  font-family: "Fraunces", serif;
}

.hero-subtitle {
  margin: 8px 0 0;
  color: var(--muted);
}

.hero-actions {
  display: flex;
  gap: 12px;
}

.revenue-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.stat-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 16px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.14), transparent 55%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
}

.stat-card:hover::after {
  opacity: 1;
}

.stat-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
}

.stat-value {
  margin: 12px 0 0;
  font-size: 22px;
  font-weight: 700;
}

.stat-delta {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 999px;
  background: #e9f7ef;
  color: #2f9e5f;
  position: relative;
  z-index: 1;
}

.sparkline {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 6px;
  margin-top: 12px;
}

.sparkline span {
  height: 28px;
  border-radius: 8px;
  background: linear-gradient(180deg, rgba(255, 122, 26, 0.45), rgba(255, 122, 26, 0.08));
}

.revenue-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
  gap: 18px;
}

.card {
  background: var(--surface);
  border-radius: 18px;
  padding: 18px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
}

.card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.pill-group {
  display: inline-flex;
  gap: 6px;
  background: #f8fafc;
  padding: 4px;
  border-radius: 999px;
}

.pill {
  border: none;
  background: transparent;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  color: var(--muted);
  cursor: pointer;
}

.pill.active {
  background: #fff;
  color: var(--accent-strong);
  box-shadow: var(--shadow-soft);
}

.bar-chart {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  gap: 8px;
  align-items: end;
  height: 180px;
}

.bar {
  background: linear-gradient(180deg, #e2e8f0, #f8fafc);
  border-radius: 999px;
  height: 40%;
}

.bar:nth-child(2) { height: 65%; }
.bar:nth-child(3) { height: 80%; }
.bar:nth-child(4) { height: 35%; }
.bar:nth-child(5) { height: 60%; }
.bar:nth-child(6) { height: 90%; background: linear-gradient(180deg, #ff7a1a, #f15b2a); }
.bar:nth-child(7) { height: 55%; }
.bar:nth-child(8) { height: 70%; }
.bar:nth-child(9) { height: 50%; }
.bar:nth-child(10) { height: 45%; }
.bar:nth-child(11) { height: 68%; }
.bar:nth-child(12) { height: 75%; }

.payout-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
  padding: 12px;
  border-radius: 14px;
  margin: 12px 0;
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.payout-summary h4 {
  margin: 6px 0 0;
}

.status-pill {
  padding: 4px 10px;
  border-radius: 999px;
  background: #e9f7ef;
  color: #2f9e5f;
  font-size: 12px;
}

.payout-list {
  display: grid;
  gap: 10px;
  margin-bottom: 12px;
}

.payout-list div {
  display: flex;
  justify-content: space-between;
}

.payout-list .danger {
  color: #e2553f;
}

.table {
  display: grid;
  gap: 8px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1.2fr 1fr 1fr 1fr;
  gap: 10px;
  font-size: 12px;
}

.table-head {
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.table-row {
  padding: 10px;
  border-radius: 12px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.table-row:hover {
  transform: translateX(4px);
  box-shadow: var(--shadow-soft);
}

.status {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 11px;
  display: inline-flex;
  justify-content: center;
}

.status.completed {
  background: #fff3e6;
  color: #f15b2a;
}

.status.pending {
  background: #ffe9d5;
  color: #ff7a1a;
}

.status.failed {
  background: #ffe0d8;
  color: #e2553f;
}

.projection {
  background: linear-gradient(135deg, #ff7a1a, #f15b2a);
  color: #fff;
}

.projection h4 {
  font-size: 26px;
  margin: 12px 0;
}

.link-btn {
  border: none;
  background: transparent;
  color: var(--accent-strong);
  cursor: pointer;
}

.primary-btn {
  border: none;
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 100%);
  color: #fff;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(241, 91, 42, 0.28);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.primary-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(241, 91, 42, 0.3);
}

.primary-btn.full {
  width: 100%;
}

.ghost-btn {
  border: 1px solid rgba(255, 122, 26, 0.3);
  background: rgba(255, 255, 255, 0.85);
  color: #c65300;
  padding: 10px 16px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ghost-btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-soft);
}

@media (max-width: 1100px) {
  .revenue-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .revenue-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
  }

  .revenue-hero {
    flex-direction: column;
    align-items: flex-start;
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

  .table-head {
    display: none;
  }

  .table-row {
    grid-template-columns: 1fr 1fr;
    row-gap: 6px;
  }
}
</style>

