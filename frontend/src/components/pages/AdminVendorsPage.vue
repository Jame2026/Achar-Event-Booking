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
  { key: "users", label: "Users", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];
const activeKey = ref("vendors");

const highlights = [
  { label: "Total Vendors", value: "1,284", delta: "+12%" },
  { label: "Pending Review", value: "42", delta: "High priority" },
  { label: "Avg. Performance", value: "4.82", delta: "Top ratings" },
  { label: "Total Revenue", value: "$4.2M", delta: "FY 2024" },
];

const vendors = [
  {
    id: "VD-8821",
    name: "Culinary Excellence Ltd.",
    category: "Company Party",
    location: "London, UK",
    events: "142",
  },
  {
    id: "VD-1205",
    name: "Events & Design",
    category: "Wedding",
    location: "Paris, France",
    events: "89",
  },
  {
    id: "VD-5542",
    name: "Skyline AV Solutions",
    category: "Corporate",
    location: "New York, US",
    events: "210",
  },
];

const verificationDocs = [
  { name: "Business License", status: "Verified" },
  { name: "Tax Certification", status: "Verified" },
  { name: "Insurance Policy", status: "Expiring Soon" },
];

const recentActivity = [
  { title: "Completed Event: TechX Gala", meta: "2 hours ago · Revenue: $4,120" },
  { title: "New Review: 5.0 Stars", meta: "Yesterday · “Exceptional catering service.”" },
  { title: "Updated Profile Details", meta: "3 days ago · Changed contact number" },
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
  activeKey.value = page || "vendors";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
</script>

<template>
  <section class="admin-shell vendors-shell">
    <aside class="admin-sidebar">
      <div class="brand">
        <div class="brand-logo">
          <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
          <div v-else class="brand-mark">A</div>
        </div>
        <div>
          <p class="brand-title">Architect Admin</p>
          <p class="brand-subtitle">Vendor Management</p>
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
              <path d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z" />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" placeholder="Search vendors, categories, or IDs..." />
        </label>
        <div class="topbar-actions">
          <span class="topbar-label">Vendor Central</span>
          <button class="icon-btn" type="button" title="Notifications" aria-label="Notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="vendors-hero">
        <div>
          <p class="eyebrow">Vendor Management</p>
          <h1 class="hero-title">Vendor Management</h1>
          <p class="hero-subtitle">Oversee your global network of event specialists and maintain service quality standards.</p>
        </div>
        <button class="primary-btn" type="button">+ Add New Vendor</button>
      </section>

      <section class="vendors-highlights">
        <article v-for="card in highlights" :key="card.label" class="highlight-card">
          <p class="highlight-label">{{ card.label }}</p>
          <h3>{{ card.value }}</h3>
          <span class="highlight-delta">{{ card.delta }}</span>
        </article>
      </section>

      <section class="vendors-grid">
        <article class="card table-card">
          <header>
            <div class="filters">
              <button class="pill active" type="button">Category: All Categories</button>
              <button class="pill" type="button">Status: Active</button>
              <button class="pill" type="button">Rating: Any</button>
              <button class="pill" type="button">More Filters</button>
            </div>
          </header>
          <div class="table">
            <div class="table-head">
              <span>Vendor Profile</span>
              <span>Category</span>
              <span>Location</span>
              <span>Events</span>
              <span></span>
            </div>
            <div v-for="vendor in vendors" :key="vendor.id" class="table-row">
              <div class="vendor-profile">
                <div class="vendor-photo"></div>
                <div>
                  <p class="vendor-name">{{ vendor.name }}</p>
                  <span class="vendor-id">ID: {{ vendor.id }}</span>
                </div>
              </div>
              <span class="chip">{{ vendor.category }}</span>
              <span>{{ vendor.location }}</span>
              <span>{{ vendor.events }}</span>
              <button class="star-btn" type="button" aria-label="Favorite vendor">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                  <path
                    d="M12 2.5 14.8 8l6.2.9-4.5 4.4 1.1 6.2L12 16.9 6.4 19.5l1.1-6.2-4.5-4.4 6.2-.9L12 2.5z"
                  />
                </svg>
              </button>
            </div>
          </div>
          <div class="table-footer">
            <span>Showing 1-10 of 1,284 vendors</span>
            <div class="pagination">
              <button class="page-btn">1</button>
              <button class="page-btn active">2</button>
              <button class="page-btn">3</button>
            </div>
          </div>
        </article>

        <aside class="side-column">
          <article class="card profile-card">
            <header>
              <h3>Vendor Central</h3>
              <div class="mini-actions">
                <button class="icon-btn" type="button" title="Edit">?</button>
                <button class="icon-btn" type="button" title="Delete">?</button>
              </div>
            </header>
            <div class="profile">
              <div class="profile-avatar">CE</div>
              <div>
                <p class="profile-name">Culinary Excellence Ltd.</p>
                <p class="profile-meta">London, UK ? Since 2019</p>
              </div>
            </div>
            <div class="profile-stats">
              <div>
                <span>Contact</span>
                <strong>contact@culinaryx.com</strong>
              </div>
              <div>
                <span>Phone</span>
                <strong>+44 20 7946 0958</strong>
              </div>
            </div>
          </article>

          <article class="card revenue-card">
            <h3>Revenue Generated</h3>
            <h4>$284,500.00</h4>
            <p>Payouts pending: $12,400</p>
            <button class="primary-btn" type="button">View Financials</button>
          </article>

          <article class="card docs-card">
            <h3>Verification Documents</h3>
            <ul>
              <li v-for="doc in verificationDocs" :key="doc.name">
                <span>{{ doc.name }}</span>
                <span class="doc-status">{{ doc.status }}</span>
              </li>
            </ul>
          </article>

          <article class="card activity-card">
            <h3>Recent Activity</h3>
            <ul>
              <li v-for="item in recentActivity" :key="item.title">
                <strong>{{ item.title }}</strong>
                <span>{{ item.meta }}</span>
              </li>
            </ul>
            <button class="ghost-btn" type="button">View Full Analytics Report ?</button>
          </article>
        </aside>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.vendors-shell {
  --ink: #0f172a;
  --muted: #64748b;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #fff1e7;
  --surface: rgba(255, 255, 255, 0.92);
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

.vendors-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.vendors-shell > * {
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

.admin-user-card {
  margin-top: auto;
  background: var(--surface-strong);
  border-radius: 18px;
  padding: 16px;
  box-shadow: var(--shadow-soft);
  display: grid;
  gap: 8px;
  border: 1px solid var(--stroke);
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #ffe7d2;
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
  color: var(--muted);
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

.topbar-label {
  font-size: 12px;
  color: var(--muted);
}

.icon-btn {
  border: 1px solid var(--stroke);
  background: #fff;
  width: 34px;
  height: 34px;
  border-radius: 10px;
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
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: linear-gradient(135deg, #ffb98b 0%, #ff8b3d 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 600;
}

.vendors-hero {
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
  font-size: 32px;
  font-family: "Fraunces", serif;
}

.hero-subtitle {
  margin: 8px 0 0;
  color: var(--muted);
}

.vendors-highlights {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 14px;
}

.highlight-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 14px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
}

.highlight-label {
  margin: 0;
  font-size: 11px;
  color: var(--muted);
}

.highlight-card h3 {
  margin: 8px 0 6px;
  font-size: 20px;
}

.highlight-delta {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 999px;
  background: #fff3e6;
  color: #f15b2a;
}

.vendors-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
  gap: 18px;
  align-items: start;
}

.card {
  background: var(--surface);
  border-radius: 18px;
  padding: 18px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
}

.table-card header {
  display: flex;
  justify-content: flex-start;
  align-items: center;
  margin-bottom: 12px;
}

.filters {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pill {
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  color: var(--muted);
  cursor: pointer;
}

.pill.active {
  color: var(--accent-strong);
  border-color: rgba(255, 122, 26, 0.3);
  box-shadow: var(--shadow-soft);
}

.table {
  display: grid;
  gap: 8px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.6fr 0.9fr 1fr 0.7fr 0.2fr;
  gap: 12px;
  font-size: 12px;
}

.table-head {
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.6px;
}

.table-row {
  padding: 12px;
  border-radius: 12px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.05);
  align-items: center;
}

.vendor-profile {
  display: flex;
  gap: 10px;
  align-items: center;
}

.vendor-photo {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #fef3e6, #ffe0c7);
}

.vendor-name {
  margin: 0;
  font-weight: 600;
}

.vendor-id {
  font-size: 12px;
  color: var(--muted);
}

.chip {
  padding: 4px 8px;
  border-radius: 999px;
  background: #fff3e6;
  color: #f15b2a;
  font-size: 11px;
}

.star-btn {
  border: none;
  background: #fff7f1;
  color: #f15b2a;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  cursor: pointer;
  display: grid;
  place-items: center;
}

.star-btn svg {
  width: 16px;
  height: 16px;
  fill: currentColor;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12px;
  font-size: 12px;
  color: var(--muted);
}

.pagination {
  display: flex;
  gap: 6px;
}

.page-btn {
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #fff;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  font-size: 12px;
  cursor: pointer;
}

.page-btn.active {
  background: var(--accent);
  color: #fff;
  border-color: transparent;
}

.profile-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.mini-actions {
  display: flex;
  gap: 6px;
}

.profile {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 12px;
}

.profile-avatar {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: #fff3e6;
  color: #f15b2a;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.profile-name {
  margin: 0;
  font-weight: 600;
}

.profile-meta {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.profile-stats {
  display: grid;
  gap: 10px;
}

.profile-stats span {
  font-size: 12px;
  color: var(--muted);
}

.profile-stats strong {
  display: block;
  margin-top: 4px;
}

.revenue-card {
  background: linear-gradient(135deg, #0f172a, #0b3b4d);
  color: #fff;
}

.revenue-card h4 {
  font-size: 24px;
  margin: 10px 0;
}

.docs-card ul,
.activity-card ul {
  list-style: none;
  padding: 0;
  margin: 12px 0 0;
  display: grid;
  gap: 10px;
}

.docs-card li,
.activity-card li {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  font-size: 12px;
  color: var(--muted);
}

.doc-status {
  color: #ff7a1a;
  font-weight: 600;
}

.activity-card li strong {
  color: var(--ink);
  font-weight: 600;
}

.activity-card li span {
  font-size: 11px;
}

.side-column {
  display: grid;
  gap: 16px;
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
  box-shadow: 0 12px 24px rgba(241, 91, 42, 0.22);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.primary-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(241, 91, 42, 0.3);
}

.ghost-btn {
  border: 1px solid rgba(255, 122, 26, 0.3);
  background: rgba(255, 255, 255, 0.85);
  color: #c65300;
  padding: 8px 12px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
}

@media (max-width: 1100px) {
  .vendors-grid {
    grid-template-columns: 1fr;
  }

  .table-head {
    display: none;
  }

  .table-row {
    grid-template-columns: 1fr 1fr;
    row-gap: 8px;
  }
}

@media (max-width: 1024px) {
  .vendors-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
  }

  .vendors-hero {
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
}
</style>
