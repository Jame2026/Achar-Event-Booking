<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { eventTypeMap } from "../../features/appData";
import { useAdminDataStore } from "../../features/useAdminDataStore";

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
const activeKey = ref("events");
const adminStore = useAdminDataStore();
const isLoading = computed(() => adminStore.loading.all || adminStore.loading.events);
const loadError = computed(() => adminStore.errors.events);

const eventRows = computed(() => adminStore.state.events);

const formatDate = (value) => {
  const date = value ? new Date(value) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return "Date TBD";
  return date.toLocaleDateString("en-US", { month: "short", day: "2-digit", year: "numeric" });
};

const normalizeStatus = (row) => {
  const status = String(row?.status || "").toLowerCase();
  if (status === "approved" || status === "active") return "Approved";
  if (status === "pending" || status === "review") return "Pending";
  if (row?.is_active === false) return "Pending";
  return "Approved";
};

const reviewQueue = computed(() => {
  const sorted = [...eventRows.value].sort((a, b) => {
    const left = new Date(a?.created_at || a?.updated_at || 0).getTime();
    const right = new Date(b?.created_at || b?.updated_at || 0).getTime();
    return right - left;
  });
  return sorted.slice(0, 8).map((event) => ({
    id: event?.id ? `EV-${event.id}` : "EV",
    name: event?.vendor?.name || event?.vendor_name || "Vendor",
    title: event?.title || "Event listing",
    category: eventTypeMap[event?.event_type] || event?.event_type || "Other",
    submitted: formatDate(event?.created_at || event?.updated_at),
    status: normalizeStatus(event),
  }));
});

const highlights = computed(() => {
  const total = eventRows.value.length;
  const pending = eventRows.value.filter((row) => normalizeStatus(row) === "Pending").length;
  const weekAgo = Date.now() - 7 * 24 * 60 * 60 * 1000;
  const approvedThisWeek = eventRows.value.filter((row) => {
    const created = row?.created_at ? new Date(row.created_at).getTime() : 0;
    return created >= weekAgo && normalizeStatus(row) === "Approved";
  }).length;
  return [
    { label: "Total Submissions", value: total.toLocaleString(), delta: `${pending} pending` },
    { label: "Pending Review", value: pending.toLocaleString(), delta: "Action required" },
    { label: "Approved This Week", value: approvedThisWeek.toLocaleString(), delta: "Trending up" },
  ];
});

const vendorSnapshot = computed(() => {
  if (!eventRows.value.length) {
    return {
      name: "No vendors yet",
      location: "Location TBD",
      listings: "0 Active",
      rating: "N/A",
    };
  }
  const counts = new Map();
  eventRows.value.forEach((event) => {
    const key = event?.vendor?.name || event?.vendor_name || "Vendor";
    counts.set(key, (counts.get(key) || 0) + 1);
  });
  const [name] = Array.from(counts.entries()).sort((a, b) => b[1] - a[1])[0];
  const vendorEvent = eventRows.value.find(
    (event) => (event?.vendor?.name || event?.vendor_name || "Vendor") === name,
  );
  return {
    name: name || "Vendor",
    location: vendorEvent?.location || vendorEvent?.vendor?.location || "Location TBD",
    listings: `${counts.get(name) || 0} Active`,
    rating: vendorEvent?.vendor?.rating ? String(vendorEvent.vendor.rating) : "N/A",
  };
});

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
  activeKey.value = page || "events";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
onMounted(() => void adminStore.loadAll());
</script>

<template>
  <section class="admin-shell events-shell">
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
          <input v-model="searchQuery" type="search" placeholder="Search service applications..." />
        </label>
        <div class="topbar-actions">
          <button class="ghost-btn" type="button">Export CSV</button>
          <button class="icon-btn" type="button" title="Notifications" aria-label="Notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="events-hero">
        <div>
          <p class="eyebrow">Service Approvals</p>
          <h1 class="hero-title">Vendor Service Reviews</h1>
          <p class="hero-subtitle">
            Review and manage new service applications from your vendor network.
          </p>
        </div>
        <button class="primary-btn" type="button">Approve Queue</button>
      </section>

      <section class="events-highlights">
        <article v-for="card in highlights" :key="card.label" class="highlight-card">
          <p class="highlight-label">{{ card.label }}</p>
          <h3>{{ card.value }}</h3>
          <span class="highlight-delta">{{ card.delta }}</span>
        </article>
      </section>

      <section class="events-grid">
        <article class="queue-card">
          <header>
            <h3>Pending Review Queue</h3>
            <div class="filters">
              <button class="pill active" type="button">All</button>
              <button class="pill" type="button">Pending</button>
              <button class="pill" type="button">Approved</button>
            </div>
          </header>
          <div class="queue-list">
            <div v-if="isLoading" class="queue-empty">Loading event submissions...</div>
            <div v-else-if="loadError" class="queue-empty">{{ loadError }}</div>
            <div v-else-if="!reviewQueue.length" class="queue-empty">No event submissions yet.</div>
            <div v-else v-for="item in reviewQueue" :key="item.title" class="queue-row">
              <div class="badge">{{ item.id }}</div>
              <div>
                <p class="queue-title">{{ item.title }}</p>
                <p class="queue-meta">{{ item.name }} ? {{ item.category }}</p>
              </div>
              <span class="queue-date">{{ item.submitted }}</span>
              <span class="status" :class="item.status.toLowerCase()">{{ item.status }}</span>
              <div class="row-actions">
                <button class="ghost-btn" type="button">Reject</button>
                <button class="primary-btn" type="button">Approve</button>
              </div>
            </div>
          </div>
        </article>

        <aside class="side-column">
          <article class="card detail-card">
            <header>
              <h3>Vendor Verification</h3>
              <button class="link-btn" type="button">View Profile</button>
            </header>
            <div class="vendor-block">
              <div class="vendor-avatar">EG</div>
              <div>
                <p class="vendor-name">{{ vendorSnapshot.name }}</p>
                <p class="vendor-meta">{{ vendorSnapshot.location }}</p>
              </div>
            </div>
            <div class="vendor-stats">
              <div>
                <span>Listings</span>
                <strong>{{ vendorSnapshot.listings }}</strong>
              </div>
              <div>
                <span>Rating</span>
                <strong>{{ vendorSnapshot.rating }}</strong>
              </div>
            </div>
            <button class="primary-btn full" type="button">Confirm Approval</button>
          </article>

          <article class="card action-card">
            <h3>Ready to Approve?</h3>
            <p>Approve the selected vendor and notify them instantly.</p>
            <button class="primary-btn full" type="button">Approve Vendor</button>
            <button class="ghost-btn full" type="button">Request Changes</button>
          </article>
        </aside>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.events-shell {
  --ink: #0f172a;
  --muted: #64748b;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #fff1e7;
  --accent-deep: #e4541f;
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

.events-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.events-shell > * {
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

.events-hero {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.78), rgba(255, 243, 230, 0.6));
  border: 1px solid rgba(255, 122, 26, 0.12);
  padding: 20px 24px;
  border-radius: 26px;
  box-shadow: 0 22px 48px rgba(15, 23, 42, 0.12);
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

.events-highlights {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.highlight-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 16px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.highlight-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.12), transparent 55%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.highlight-card:hover::after {
  opacity: 1;
}

.highlight-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.14);
}

.highlight-label {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
}

.highlight-card h3 {
  margin: 10px 0 8px;
  font-size: 24px;
}

.highlight-delta {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 999px;
  background: #fff3e6;
  color: #f15b2a;
}

.events-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
  gap: 18px;
  align-items: start;
}

.queue-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 18px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
}

.queue-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.filters {
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

.queue-list {
  display: grid;
  gap: 12px;
}

.queue-empty {
  padding: 16px;
  border-radius: 14px;
  border: 1px dashed rgba(15, 23, 42, 0.12);
  background: #fff;
  color: var(--muted);
  font-size: 13px;
}

.queue-row {
  display: grid;
  grid-template-columns: auto minmax(160px, 1.4fr) minmax(90px, 0.8fr) auto minmax(140px, 180px);
  gap: 10px;
  align-items: center;
  padding: 12px;
  border-radius: 14px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.05);
  box-shadow: var(--shadow-soft);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: visible;
}

.queue-row:hover {
  transform: translateX(4px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}

.badge {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: var(--accent);
  background: #fff3e6;
  border: 1px solid rgba(255, 122, 26, 0.15);
}

.queue-title {
  margin: 0;
  font-weight: 600;
}

.queue-meta {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.queue-date {
  font-size: 12px;
  color: var(--muted);
}

.status {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 11px;
  justify-self: start;
}

.status.pending {
  background: #ffe9d5;
  color: #ff7a1a;
}

.status.approved {
  background: #fff3e6;
  color: #f15b2a;
}

.row-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-self: end;
  align-items: center;
  min-width: 0;
}

.row-actions .primary-btn,
.row-actions .ghost-btn {
  padding: 6px 10px;
  border-radius: 10px;
  min-width: 76px;
  font-size: 12px;
  white-space: nowrap;
}

.row-actions .ghost-btn {
  background: #fff;
  border: 1px solid rgba(255, 122, 26, 0.35);
  color: #d4551a;
  box-shadow: none;
  height: 34px;
  line-height: 18px;
}

.row-actions .ghost-btn:hover {
  transform: translateY(-1px);
  box-shadow: none;
  background: #fff7f1;
}

.row-actions .primary-btn {
  box-shadow: 0 12px 20px rgba(241, 91, 42, 0.28);
  height: 34px;
  line-height: 18px;
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

.side-column {
  display: grid;
  gap: 16px;
}

.detail-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.vendor-block {
  display: flex;
  gap: 12px;
  align-items: center;
  margin-bottom: 12px;
}

.vendor-avatar {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: #fff3e6;
  color: #f15b2a;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.vendor-name {
  margin: 0;
  font-weight: 600;
}

.vendor-meta {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.vendor-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 16px;
}

.vendor-stats span {
  font-size: 12px;
  color: var(--muted);
}

.vendor-stats strong {
  display: block;
  font-size: 14px;
  margin-top: 4px;
}

.action-card {
  background: linear-gradient(135deg, #ff7a1a, #f15b2a);
  color: #fff;
}

.action-card p {
  margin: 10px 0 16px;
  opacity: 0.9;
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
  box-shadow: 0 12px 24px rgba(241, 91, 42, 0.26);
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

.ghost-btn.full {
  width: 100%;
}

@media (max-width: 1100px) {
  .events-grid {
    grid-template-columns: 1fr;
  }

  .queue-row {
    grid-template-columns: auto 1fr;
    row-gap: 8px;
  }

  .row-actions {
    grid-column: 1 / -1;
  }
}

@media (max-width: 1024px) {
  .events-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
  }

  .events-hero {
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
