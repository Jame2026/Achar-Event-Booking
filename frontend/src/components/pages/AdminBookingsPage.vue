<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiGet, apiPatch } from "../../features/apiClient";

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
const activeKey = ref("admin-bookings");

const stats = [
  { label: "Total Bookings", value: "1,284", delta: "+12%" },
  { label: "Pending Confirmation", value: "42", delta: "Needs action" },
  { label: "Avg. Response Time", value: "1.4h", delta: "Top 5% Vendor" },
];

const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const currentAdmin = computed(() => {
  try {
    const stored = localStorage.getItem(AUTH_USER_STORAGE_KEY);
    return stored ? JSON.parse(stored) : null;
  } catch {
    return null;
  }
});
const avatarUrl = computed(() => currentAdmin.value?.profile_image_url || "");

const bookings = [
  {
    id: "#BK-1201",
    name: "Sarah Mitchell",
    email: "s.mitchell@email.com",
    event: "International Tech Summit 2024",
    date: "Oct 24, 2024",
    time: "9:30 PM",
    status: "Pending",
  },
  {
    id: "#BK-1194",
    name: "David Ling",
    email: "d.ling@email.com",
    event: "Product Launch: Ali Nexus",
    date: "Nov 02, 2024",
    time: "10:30 AM",
    status: "Confirmed",
  },
  {
    id: "#BK-1188",
    name: "Elena Kovac",
    email: "e.kovac@email.com",
    event: "Modern UI Workshop",
    date: "Oct 29, 2024",
    time: "2:00 PM",
    status: "Pending",
  },
  {
    id: "#BK-1179",
    name: "James Roland",
    email: "j.roland@email.com",
    event: "Networking Night",
    date: "Oct 15, 2024",
    time: "7:00 PM",
    status: "Cancelled",
  },
];

const initials = computed(() => {
  const pieces = String(currentAdmin.value?.name || props.adminDisplayName || "Admin")
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
  activeKey.value = page || "admin-bookings";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);

const notifications = ref([]);
const notificationsUnreadCount = ref(0);
const notificationsError = ref("");
const isLoadingNotifications = ref(false);
const notificationDropdownOpen = ref(false);
const notificationMenuRef = ref(null);

const notificationItems = computed(() =>
  notifications.value.map((item) => ({
    ...item,
    createdLabel: formatNotificationTime(item.created_at),
  })),
);

function notificationRole(role) {
  if (role === "vendor") return "vendor";
  if (role === "admin") return "admin";
  return "user";
}

function buildNotificationQuery() {
  const user = currentAdmin.value || {};
  const query = {
    role: notificationRole(user.role || "admin"),
    limit: 20,
  };

  const userId = Number(user.id);
  if (Number.isFinite(userId) && userId > 0) query.user_id = userId;

  const email = String(user.email || "").trim().toLowerCase();
  if (email) query.email = email;

  if (!query.user_id && !query.email) return null;
  return query;
}

function formatNotificationTime(value) {
  if (!value) return "Just now";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return "Just now";

  const diffMinutes = Math.floor((Date.now() - parsed.getTime()) / 60000);
  if (diffMinutes < 1) return "Just now";
  if (diffMinutes < 60) return `${diffMinutes}m ago`;
  if (diffMinutes < 24 * 60) return `${Math.floor(diffMinutes / 60)}h ago`;

  return parsed.toLocaleDateString("en-US", {
    month: "short",
    day: "2-digit",
  });
}

async function loadNotifications(options = {}) {
  const { silent = false } = options;
  const query = buildNotificationQuery();

  if (!query) {
    notifications.value = [];
    notificationsUnreadCount.value = 0;
    notificationsError.value = "Please sign in as admin.";
    return;
  }

  if (!silent) isLoadingNotifications.value = true;
  notificationsError.value = "";

  try {
    const result = await apiGet("notifications/bookings", query);
    const rows = Array.isArray(result.data) ? result.data : [];
    notifications.value = rows;
    notificationsUnreadCount.value = Number(result.unread_count || 0);
  } catch (error) {
    notificationsError.value = "Could not load notifications right now.";
  } finally {
    if (!silent) isLoadingNotifications.value = false;
  }
}

async function markNotificationAsRead(notification) {
  if (!notification || notification.is_read) return;
  const query = buildNotificationQuery();
  if (!query) return;

  notification.is_read = true;
  notificationsUnreadCount.value = Math.max(0, notificationsUnreadCount.value - 1);

  try {
    await apiPatch(`notifications/bookings/${notification.id}/read`, query);
  } catch {
    // revert on failure
    notification.is_read = false;
    await loadNotifications({ silent: true });
  }
}

async function markAllNotificationsAsRead() {
  if (notificationsUnreadCount.value < 1) return;
  const query = buildNotificationQuery();
  if (!query) return;

  try {
    await apiPatch("notifications/bookings/read-all", query);
    notifications.value = notifications.value.map((item) => ({ ...item, is_read: true }));
    notificationsUnreadCount.value = 0;
  } catch {
    notificationsError.value = "Could not mark all notifications as read.";
  }
}

async function toggleNotificationDropdown() {
  notificationDropdownOpen.value = !notificationDropdownOpen.value;
  if (notificationDropdownOpen.value) {
    await loadNotifications();
  }
}

function closeNotificationDropdown() {
  notificationDropdownOpen.value = false;
}

function handleDocumentClick(event) {
  if (!notificationDropdownOpen.value) return;
  if (!notificationMenuRef.value) return;
  if (!notificationMenuRef.value.contains(event.target)) {
    closeNotificationDropdown();
  }
}

onMounted(() => {
  document.addEventListener("click", handleDocumentClick);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleDocumentClick);
});
</script>

<template>
  <section class="admin-shell bookings-shell">
    <aside class="admin-sidebar">
      <div class="brand">
        <div class="brand-logo">
          <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
          <div v-else class="brand-mark">A</div>
        </div>
        <div>
          <p class="brand-title">EventMaster</p>
          <p class="brand-subtitle">Vendor Admin</p>
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
        <div class="avatar" :class="{ 'has-image': avatarUrl }">
          <img v-if="avatarUrl" :src="avatarUrl" alt="Profile" />
          <span v-else>{{ initials }}</span>
        </div>
        <div>
          <p class="user-name">{{ currentAdmin?.name || adminDisplayName }}</p>
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
          <input v-model="searchQuery" type="search" placeholder="Search bookings, attendees or events..." />
        </label>
        <div class="topbar-actions">
          <button class="primary-btn" type="button">Create Event</button>
          <div class="notification-wrap" ref="notificationMenuRef">
            <button
              class="icon-btn"
              type="button"
              title="Notifications"
              aria-label="Notifications"
              :aria-expanded="notificationDropdownOpen ? 'true' : 'false'"
              @click.stop="toggleNotificationDropdown"
            >
              <svg viewBox="0 0 24 24">
                <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
              </svg>
              <span v-if="notificationsUnreadCount > 0" class="notification-badge">
                {{ notificationsUnreadCount > 99 ? "99+" : notificationsUnreadCount }}
              </span>
            </button>
            <section v-if="notificationDropdownOpen" class="notification-panel" @click.stop>
              <div class="notification-head">
                <strong>Notifications</strong>
                <div class="notification-actions">
                  <button type="button" class="notification-action-btn" @click="loadNotifications">Refresh</button>
                  <button
                    v-if="notificationsUnreadCount > 0"
                    type="button"
                    class="notification-action-btn"
                    @click="markAllNotificationsAsRead"
                  >
                    Mark all read
                  </button>
                  <button type="button" class="notification-action-btn is-muted" @click="closeNotificationDropdown">
                    Close
                  </button>
                </div>
              </div>

              <p v-if="isLoadingNotifications" class="notification-empty">Loading notifications...</p>
              <p v-else-if="notificationsError" class="notification-empty">{{ notificationsError }}</p>
              <p v-else-if="notificationItems.length === 0" class="notification-empty">No notifications yet.</p>

              <ul v-else class="notification-list">
                <li v-for="item in notificationItems" :key="item.id">
                  <article class="notification-item" :class="{ unread: !item.is_read }">
                    <div class="notification-item-top">
                      <strong>{{ item.title }}</strong>
                      <span class="muted">{{ item.createdLabel }}</span>
                    </div>
                    <p class="notification-body">{{ item.message }}</p>
                    <div class="notification-item-actions">
                      <button
                        class="notification-inline-btn is-muted"
                        type="button"
                        @click="markNotificationAsRead(item)"
                      >
                        Mark read
                      </button>
                    </div>
                  </article>
                </li>
              </ul>
            </section>
          </div>
          <div class="topbar-avatar" :class="{ 'has-image': avatarUrl }">
            <img v-if="avatarUrl" :src="avatarUrl" alt="Profile" />
            <span v-else>{{ initials }}</span>
          </div>
        </div>
      </header>

      <section class="bookings-hero">
        <div>
          <p class="eyebrow">Booking Management</p>
          <h1 class="hero-title">Review and Manage Bookings</h1>
          <p class="hero-subtitle">Monitor active registrations, confirmations, and cancellations.</p>
        </div>
        <div class="pill-group">
          <button class="pill active" type="button">All</button>
          <button class="pill" type="button">Pending</button>
          <button class="pill" type="button">Confirmed</button>
          <button class="pill" type="button">Cancelled</button>
        </div>
      </section>

      <section class="bookings-stats">
        <article v-for="card in stats" :key="card.label" class="stat-card">
          <p class="stat-label">{{ card.label }}</p>
          <h3>{{ card.value }}</h3>
          <span class="stat-delta">{{ card.delta }}</span>
        </article>
      </section>

      <section class="bookings-grid">
        <article class="card table-card">
          <header>
            <h3>Recent Bookings</h3>
          </header>
          <div class="table">
            <div class="table-head">
              <span>Attendee</span>
              <span>Event Details</span>
              <span>Date & Time</span>
              <span>Status</span>
              <span>Actions</span>
            </div>
            <div v-for="item in bookings" :key="item.id" class="table-row">
              <div>
                <p class="attendee">{{ item.name }}</p>
                <span class="attendee-email">{{ item.email }}</span>
              </div>
              <span>{{ item.event }}</span>
              <span>{{ item.date }} ? {{ item.time }}</span>
              <span class="status" :class="item.status.toLowerCase()">{{ item.status }}</span>
              <button class="primary-btn small" type="button">View</button>
            </div>
          </div>
        </article>

        <aside class="side-column">
          <article class="card insight-card">
            <h3>Vendor Performance Insights</h3>
            <p>Your most ?International Tech Summit? has reached 85% capacity. Consider opening additional seating.</p>
         
          </article>

          <article class="card support-card">
            <h3>Need Assistance?</h3>
            <p>Dedicated vendor support is available 24/7 to help you manage attendee inquiries.</p>
            <button class="primary-btn" type="button">Chat with Support</button>
          </article>
        </aside>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.bookings-shell {
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

.bookings-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.bookings-shell > * {
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
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.9));
  border: 1px solid rgba(15, 23, 42, 0.06);
  padding: 14px;
  border-radius: 24px;
  box-shadow: var(--shadow-soft);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(15, 23, 42, 0.05);
  background: #fff;
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
  background: linear-gradient(180deg, #f8fafc, #eef2f7);
  color: #94a3b8;
  transition: all 0.2s ease;
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.nav-item:hover {
  background: linear-gradient(180deg, #fff9f3, #fef6ef);
  color: var(--accent);
  transform: translateX(2px);
  border-color: rgba(255, 122, 26, 0.18);
}

.nav-item.active {
  background: linear-gradient(135deg, #fff4ea 0%, #ffe2ce 100%);
  color: var(--accent);
  border-color: rgba(255, 122, 26, 0.2);
  box-shadow: inset 3px 0 0 var(--accent), 0 10px 22px rgba(255, 122, 26, 0.2);
}

.nav-item.active .nav-icon {
  background: linear-gradient(135deg, #ff7a1a, #f15b2a);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 10px 22px rgba(241, 91, 42, 0.25);
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
  overflow: visible;
}

.admin-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  position: relative;
  z-index: 1350;
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
  position: relative;
  z-index: 1400;
}

.notification-wrap {
  position: relative;
  z-index: 1500;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 999px;
  background: #f15b2a;
  color: #fff;
  font-size: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.notification-panel {
  position: absolute;
  right: 0;
  margin-top: 10px;
  width: min(360px, 80vw);
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
  border-radius: 16px;
  padding: 12px;
  z-index: 1600;
}

.notification-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
}

.notification-actions {
  display: flex;
  gap: 6px;
}

.notification-action-btn {
  border: none;
  background: #f6f8fb;
  color: #0f172a;
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 12px;
  cursor: pointer;
}

.notification-action-btn.is-muted {
  color: #64748b;
}

.notification-empty {
  padding: 12px;
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.notification-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 10px;
  max-height: 320px;
  overflow-y: auto;
}

.notification-item {
  border: 1px solid rgba(15, 23, 42, 0.06);
  border-radius: 12px;
  padding: 10px;
  background: #fff;
}

.notification-item.unread {
  background: #fff7f2;
  border-color: rgba(241, 91, 42, 0.25);
}

.notification-item-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  font-size: 13px;
}

.notification-item-top .muted {
  color: #94a3b8;
  font-size: 12px;
}

.notification-body {
  margin: 6px 0 8px;
  color: #0f172a;
  font-size: 13px;
}

.notification-item-actions {
  display: flex;
  gap: 8px;
}

.notification-inline-btn {
  border: none;
  background: transparent;
  color: #f15b2a;
  font-size: 12px;
  cursor: pointer;
}

.notification-inline-btn.is-muted {
  color: #64748b;
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
  overflow: hidden;
}

.topbar-avatar.has-image {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 8px 14px rgba(15, 23, 42, 0.08);
}

.topbar-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.avatar {
  overflow: hidden;
}

.avatar.has-image {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 8px 14px rgba(15, 23, 42, 0.08);
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.bookings-hero {
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

.bookings-stats {
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
}

.stat-card h3 {
  margin: 8px 0;
  font-size: 24px;
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
}

.stat-delta {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 999px;
  background: #fff3e6;
  color: #f15b2a;
}

.bookings-grid {
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
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.table-actions {
  display: flex;
  gap: 8px;
}

.table {
  display: grid;
  gap: 8px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1.4fr 1.2fr 0.8fr 0.8fr;
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

.attendee {
  margin: 0;
  font-weight: 600;
}

.attendee-email {
  font-size: 12px;
  color: var(--muted);
}

.status {
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 11px;
  display: inline-flex;
  justify-content: center;
}

.status.pending {
  background: #ffe9d5;
  color: #ff7a1a;
}

.status.confirmed {
  background: #fff3e6;
  color: #f15b2a;
}

.status.cancelled {
  background: #ffe0d8;
  color: #e2553f;
}

.insight-card {
  background: linear-gradient(135deg, #ff7a1a, #f15b2a);
  color: #fff;
}

.insight-card p {
  margin: 10px 0 16px;
  opacity: 0.9;
}

.support-card {
  background: #fff;
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

.primary-btn.small {
  padding: 6px 12px;
  font-size: 12px;
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
  .bookings-grid {
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
  .bookings-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
  }

  .bookings-hero {
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
