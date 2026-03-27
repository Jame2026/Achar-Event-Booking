<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiGet, apiPatch, apiPost } from "../../features/apiClient";

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
  { key: "users", label: "Users", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];
const router = useRouter();
const route = useRoute();
const activeKey = ref("dashboard");
const isLoading = ref(false);
const loadError = ref("");
const settingsNotice = ref("");
const adminSummary = ref({ users_total: 0, events_total: 0, bookings_total: 0 });
const bookingRows = ref([]);
const eventRows = ref([]);
const userRows = ref([]);
const healthStatus = ref(null);
const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const DEFAULT_ADMIN_EMAIL = "ream.khorn@student.passerellesnumeriques.org";
const currentAdmin = computed(() => {
  try {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
});
const adminEmail = computed(() => {
  const fromUser = currentAdmin.value?.email;
  return (fromUser && String(fromUser).trim()) || DEFAULT_ADMIN_EMAIL;
});
const settingsSections = [
  { key: "profile", label: "Profile Settings" },
  { key: "security", label: "Security" },
  { key: "notifications", label: "Notifications" },
  { key: "system", label: "System Preferences" },
];
const activeSettingsSection = ref("profile");

const profileForm = ref({
  name: "",
  email: "",
  role: "Super Admin",
});
const avatarUploadRef = ref(null);
const avatarPreview = ref("");

const securityForm = ref({
  newPassword: "",
  twoFactor: true,
});

const notificationForm = ref({
  email: true,
  sms: false,
  push: true,
});

const systemForm = ref({
  darkMode: false,
  weeklyDigest: true,
  language: "English (US)",
  currency: "USD - US Dollar",
});

const notifications = ref([]);
const notificationsUnreadCount = ref(0);
const notificationsError = ref("");
const isLoadingNotifications = ref(false);
const notificationDropdownOpen = ref(false);
const notificationMenuRef = ref(null);
const isSendingEmail = ref(false);
const notificationPollTimer = ref(null);
const initials = computed(() => {
  const pieces = String(currentAdmin.value?.name || props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});
const avatarUrl = computed(() => currentAdmin.value?.profile_image_url || "");

const displayAvatar = computed(() => avatarPreview.value || avatarUrl.value || "");

const profileCompletion = computed(() => {
  let score = 0;
  if (profileForm.value.name?.trim()) score += 40;
  if (profileForm.value.email?.trim()) score += 40;
  if (displayAvatar.value) score += 20;
  return Math.min(100, score);
});

const hydrateSettings = () => {
  profileForm.value = {
    name: currentAdmin.value?.name || "Admin User",
    email: currentAdmin.value?.email || "admin@eventhorizon.com",
    role: "Super Admin",
  };
  avatarPreview.value = "";

  securityForm.value = {
    newPassword: "",
    twoFactor: true,
  };

  notificationForm.value = {
    email: true,
    sms: false,
    push: true,
  };

  systemForm.value = {
    darkMode: false,
    weeklyDigest: true,
    language: "English (US)",
    currency: "USD - US Dollar",
  };

  settingsNotice.value = "";
};

const resetSettings = () => {
  hydrateSettings();
  settingsNotice.value = "Settings reset to defaults.";
};

const saveSettings = async () => {
  settingsNotice.value = "Saving...";
  try {
    // In a real app, send to API here.
    await new Promise((resolve) => setTimeout(resolve, 400));
    settingsNotice.value = "Settings saved.";
  } catch (error) {
    settingsNotice.value = error?.message || "Could not save settings.";
  }
};

const triggerAvatarUpload = () => {
  avatarUploadRef.value?.click();
};

const handleAvatarFile = (event) => {
  const file = event?.target?.files?.[0];
  if (!file) return;
  if (!file.type.startsWith("image/")) {
    settingsNotice.value = "Please upload an image file.";
    return;
  }
  const reader = new FileReader();
  reader.onload = () => {
    avatarPreview.value = String(reader.result || "");
    settingsNotice.value = "Preview updated. Remember to Save.";
  };
  reader.readAsDataURL(file);
};

const clearAvatar = () => {
  avatarPreview.value = "";
  if (avatarUploadRef.value) avatarUploadRef.value.value = "";
  settingsNotice.value = "Profile picture removed. Remember to Save.";
};

const scrollToSection = (key) => {
  activeSettingsSection.value = key;
  const el = document.getElementById(`settings-${key}`);
  if (el) {
    el.scrollIntoView({ behavior: "smooth", block: "start" });
  }
};

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

const formatNumber = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat("en-US").format(Number.isFinite(amount) ? amount : 0);
};

const formatCurrency = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
  }).format(Number.isFinite(amount) ? amount : 0);
};

const formatPercentDelta = (current, previous) => {
  if (!previous) return "New";
  const delta = ((current - previous) / Math.abs(previous)) * 100;
  const sign = delta >= 0 ? "+" : "";
  return `${sign}${delta.toFixed(1)}%`;
};

const timeAgo = (value) => {
  const date = value ? new Date(value) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return "Just now";
  const diffMs = Date.now() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  if (diffMins < 2) return "Just now";
  if (diffMins < 60) return `${diffMins} mins ago`;
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return `${diffHours} hours ago`;
  const diffDays = Math.floor(diffHours / 24);
  return `${diffDays} days ago`;
};

const notificationItems = computed(() =>
  notifications.value.map((item) => ({
    ...item,
    createdLabel: timeAgo(item.created_at),
  })),
);

function notificationRole(role) {
  if (role === "vendor") return "vendor";
  if (role === "admin") return "admin";
  return "user";
}

function buildNotificationQuery() {
  const user = (() => {
    try {
      const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY);
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  })();

  const query = {
    role: notificationRole(user?.role || "admin"),
    limit: 20,
  };

  const userId = Number(user?.id);
  if (Number.isFinite(userId) && userId > 0) query.user_id = userId;

  const email = String(user?.email || "").trim().toLowerCase();
  if (email) query.email = email;

  if (!query.user_id && !query.email) return null;
  return query;
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
  } catch {
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

async function emailNotification(notification) {
  if (!notification) return;
  if (isSendingEmail.value) return;
  const to = adminEmail.value;
  isSendingEmail.value = true;
  notificationsError.value = "";
  try {
    await apiPost("notifications/bookings/email", {
      notification_id: notification.id,
      to,
      subject: notification.title || "Booking notification",
      body: notification.message || "",
    });
    notificationsError.value = `Email sent to ${to}`;
  } catch (error) {
    notificationsError.value = error?.message || "Could not send email; opening mail client.";
    const subject = encodeURIComponent(notification.title || "Booking notification");
    const body = encodeURIComponent(notification.message || "");
    window.open(`mailto:${to}?subject=${subject}&body=${body}`, "_blank");
  } finally {
    isSendingEmail.value = false;
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

function stopNotificationPolling() {
  if (notificationPollTimer.value) {
    window.clearInterval(notificationPollTimer.value);
    notificationPollTimer.value = null;
  }
}

function startNotificationPolling() {
  stopNotificationPolling();
  const query = buildNotificationQuery();
  if (!query) return;

  loadNotifications({ silent: true });
  notificationPollTimer.value = window.setInterval(() => {
    loadNotifications({ silent: true });
  }, 60000);
}

function handleDocumentClick(event) {
  if (!notificationDropdownOpen.value) return;
  if (!notificationMenuRef.value) return;
  if (!notificationMenuRef.value.contains(event.target)) {
    closeNotificationDropdown();
  }
}

const normalizeBookingStatus = (row) => {
  const status = String(row?.status || "").toLowerCase();
  const payment = String(row?.payment_status || "").toLowerCase();
  if (status === "cancelled") return "cancelled";
  if (status === "confirmed" || payment === "confirmed") return "confirmed";
  return "pending";
};

const getRowDate = (row, fallback) => {
  const raw = row?.created_at || row?.requested_event_date || row?.updated_at || fallback || null;
  const date = raw ? new Date(raw) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return null;
  return date;
};

const getMonthRange = (offset = 0) => {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth() + offset, 1);
  const end = new Date(now.getFullYear(), now.getMonth() + offset + 1, 0, 23, 59, 59, 999);
  return { start, end };
};

const sumRevenue = (rows, range) =>
  rows.reduce((sum, row) => {
    const status = normalizeBookingStatus(row);
    if (status !== "confirmed") return sum;
    const date = getRowDate(row, row?.event?.starts_at);
    if (!date || (range && (date < range.start || date > range.end))) return sum;
    return sum + Number(row?.total_amount || 0);
  }, 0);

const countByDate = (rows, range, dateField = "created_at") =>
  rows.reduce((sum, row) => {
    const date = getRowDate(row, row?.[dateField]);
    if (!date || (range && (date < range.start || date > range.end))) return sum;
    return sum + 1;
  }, 0);

const dashboardStats = computed(() => {
  const totalEvents = adminSummary.value?.events_total || eventRows.value.length;
  const totalBookings = adminSummary.value?.bookings_total || bookingRows.value.length;
  const totalUsers = adminSummary.value?.users_total || userRows.value.length;
  const totalRevenue = sumRevenue(bookingRows.value);

  const currentMonth = getMonthRange(0);
  const previousMonth = getMonthRange(-1);
  const eventsDelta = formatPercentDelta(
    countByDate(eventRows.value, currentMonth, "created_at"),
    countByDate(eventRows.value, previousMonth, "created_at"),
  );
  const bookingsDelta = formatPercentDelta(
    countByDate(bookingRows.value, currentMonth, "created_at"),
    countByDate(bookingRows.value, previousMonth, "created_at"),
  );
  const usersDelta = formatPercentDelta(
    countByDate(userRows.value, currentMonth, "created_at"),
    countByDate(userRows.value, previousMonth, "created_at"),
  );
  const revenueDelta = formatPercentDelta(
    sumRevenue(bookingRows.value, currentMonth),
    sumRevenue(bookingRows.value, previousMonth),
  );

  return [
    { label: "Total Events", value: formatNumber(totalEvents), delta: eventsDelta, tone: "up", icon: "events" },
    { label: "Total Bookings", value: formatNumber(totalBookings), delta: bookingsDelta, tone: "up", icon: "bookings" },
    {
      label: "Total Users",
      value: totalUsers ? formatNumber(totalUsers) : "N/A",
      delta: usersDelta,
      tone: totalUsers ? "down" : "neutral",
      icon: "users",
    },
    { label: "Revenue Summary", value: formatCurrency(totalRevenue), delta: revenueDelta, tone: "solid", icon: "revenue" },
  ];
});

const recentActivity = computed(() => {
  const bookingActivities = bookingRows.value.map((row) => {
    const status = normalizeBookingStatus(row);
    const eventTitle = row?.event?.title || row?.service_name || "a booking";
    const customer = row?.customer_name || row?.user?.name || "A customer";
    const verb = status === "confirmed" ? "completed" : status === "cancelled" ? "cancelled" : "requested";
    return {
      name: customer,
      detail: `${verb} ${eventTitle}`,
      time: timeAgo(getRowDate(row, row?.event?.starts_at)),
      at: getRowDate(row, row?.event?.starts_at)?.getTime() || 0,
    };
  });

  const userActivities = userRows.value.map((row) => ({
    name: row?.name || "New user",
    detail: "registered as a new user",
    time: timeAgo(getRowDate(row)),
    at: getRowDate(row)?.getTime() || 0,
  }));

  const eventActivities = eventRows.value.map((row) => {
    const host = row?.vendor?.name || row?.vendor_name || "A vendor";
    const title = row?.title || "a new event";
    return {
      name: host,
      detail: `created the event "${title}"`,
      time: timeAgo(getRowDate(row, row?.starts_at)),
      at: getRowDate(row, row?.starts_at)?.getTime() || 0,
    };
  });

  const merged = [...bookingActivities, ...userActivities, ...eventActivities]
    .filter((item) => item.at > 0)
    .sort((a, b) => b.at - a.at);

  const q = searchQuery.value.trim().toLowerCase();
  const filtered = q
    ? merged.filter((item) => item.name.toLowerCase().includes(q) || item.detail.toLowerCase().includes(q))
    : merged;

  return filtered.slice(0, 6);
});

const monthlyReport = computed(() => {
  const currentMonth = getMonthRange(0);
  const previousMonth = getMonthRange(-1);
  const currentRevenue = sumRevenue(bookingRows.value, currentMonth);
  const previousRevenue = sumRevenue(bookingRows.value, previousMonth);
  const growth = previousRevenue ? ((currentRevenue - previousRevenue) / previousRevenue) * 100 : 0;
  return {
    growthLabel: `${growth >= 0 ? "+" : ""}${growth.toFixed(1)}%`,
    message:
      previousRevenue > 0
        ? `Revenue is ${growth >= 0 ? "up" : "down"} ${Math.abs(growth).toFixed(1)}% compared to last month.`
        : "Revenue insights will appear once bookings are confirmed.",
  };
});

const systemStatus = computed(() => {
  const status = healthStatus.value?.status || "unknown";
  const cacheRoundTrip = healthStatus.value?.cache_round_trip;
  const isHealthy = status === "ok";
  return {
    apiLabel: isHealthy ? "Healthy" : status === "degraded" ? "Degraded" : "Unknown",
    apiPercent: isHealthy ? 99.9 : status === "degraded" ? 92.5 : 85,
    cacheLabel: cacheRoundTrip === true ? "Cache OK" : cacheRoundTrip === false ? "Cache Issue" : "Cache Unknown",
    cachePercent: cacheRoundTrip === true ? 88 : cacheRoundTrip === false ? 54 : 70,
  };
});

async function fetchPagedRows(endpoint, query = {}) {
  const rows = [];
  let page = 1;
  let lastPage = 1;
  const maxPages = 40;
  do {
    const result = await apiGet(endpoint, { ...query, page });
    const data = Array.isArray(result?.data) ? result.data : [];
    rows.push(...data);
    lastPage = Number(result?.last_page || result?.lastPage || 1);
    page += 1;
  } while (page <= lastPage && page <= maxPages);
  return rows;
}

async function loadDashboardData() {
  isLoading.value = true;
  loadError.value = "";
  try {
    try {
      adminSummary.value = await apiGet("admin/dashboard");
    } catch {
      adminSummary.value = { users_total: 0, events_total: 0, bookings_total: 0 };
    }

    try {
      bookingRows.value = await fetchPagedRows("admin/bookings");
    } catch {
      bookingRows.value = await fetchPagedRows("bookings");
    }

    try {
      const eventsResult = await apiGet("events", { per_page: 200, include_inactive: 1, ts: Date.now() });
      eventRows.value = Array.isArray(eventsResult?.data) ? eventsResult.data : [];
    } catch {
      eventRows.value = [];
    }

    try {
      userRows.value = await fetchPagedRows("admin/users");
    } catch {
      userRows.value = [];
    }

    try {
      healthStatus.value = await apiGet("health/redis");
    } catch {
      try {
        healthStatus.value = await apiGet("health");
      } catch {
        healthStatus.value = null;
      }
    }
  } catch (error) {
    loadError.value = error?.message || "Unable to load dashboard data.";
  } finally {
    isLoading.value = false;
  }
}

syncActiveKey();
watch(() => route.query.page, syncActiveKey);

onMounted(() => {
  document.addEventListener("click", handleDocumentClick);
  startNotificationPolling();
  hydrateSettings();
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleDocumentClick);
  stopNotificationPolling();
});
onMounted(loadDashboardData);
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
              <path
                d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z"
              />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" placeholder="Search insights..." />
        </label>
        <div class="topbar-actions">
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
                <path
                  d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z"
                />
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
                      <button
                        class="notification-inline-btn"
                        type="button"
                        :disabled="isSendingEmail"
                        @click="emailNotification(item)"
                      >
                        Email me
                      </button>
                    </div>
                  </article>
                </li>
              </ul>
            </section>
          </div>
          <button class="icon-btn" type="button" title="Help" aria-label="Help">
            <svg viewBox="0 0 24 24">
              <path
                d="M12 19a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 19Zm0-15a6 6 0 0 1 6 6c0 2.455-1.812 3.498-2.83 4.085-.87.505-1.17.75-1.17 1.415v.5h-2v-.5c0-1.86 1.126-2.512 2.17-3.115C14.98 11.83 16 11.235 16 10a4 4 0 0 0-8 0H6a6 6 0 0 1 6-6Z"
              />
            </svg>
          </button>
          <div class="topbar-avatar" :class="{ 'has-image': avatarUrl }">
            <img v-if="avatarUrl" :src="avatarUrl" alt="Profile" />
            <span v-else>{{ initials }}</span>
          </div>
        </div>
      </header>

      <section v-if="activeKey === 'dashboard'" class="admin-hero">
        <p class="eyebrow">Achar Event Admin</p>
        <h1 class="hero-title">Dashboard Overview</h1>
        <p class="hero-subtitle">Track bookings, vendors, and revenue at a glance.</p>
        <div class="hero-actions">
          <button class="ghost-btn" type="button">Create Event</button>
          <button class="primary-btn" type="button">Export Report</button>
        </div>
      </section>

      <section v-if="activeKey === 'dashboard'" class="admin-stats">
        <article
          v-for="card in dashboardStats"
          :key="card.label"
          class="stat-card"
          :class="card.tone"
        >
          <div class="stat-icon" :class="card.icon">
            <svg v-if="card.icon === 'events'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M7 3v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3H7Zm12 6H5v10h14V9Z" />
            </svg>
            <svg v-else-if="card.icon === 'bookings'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 4h12a2 2 0 0 1 2 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2Zm0 2v10.764l2-.999 4 2 4-2 2 .999V6H6Z" />
            </svg>
            <svg v-else-if="card.icon === 'users'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.79-6 4v1h12v-1c0-2.21-2.67-4-6-4Z" />
            </svg>
            <svg v-else-if="card.icon === 'revenue'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M5 3h2v18H5V3Zm12 6h2v12h-2V9Zm-6-4h2v16h-2V5Z" />
            </svg>
          </div>
          <p class="stat-label">{{ card.label }}</p>
          <p class="stat-value">{{ card.value }}</p>
          <span class="stat-delta" :class="card.tone">{{ card.delta }}</span>
          <div v-if="card.icon === 'events'" class="stat-action">
            <button class="link-btn" type="button" @click="router.push('/legacy-app?page=events').catch(() => {})">
              Manage Events →
            </button>
          </div>
        </article>
      </section>

      <section v-if="activeKey === 'dashboard'" class="admin-grid">
        <article class="activity-card">
          <header>
            <h3>Recent Activity</h3>
            <button class="link-btn" type="button">View All →</button>
          </header>
          <div class="activity-list">
            <div v-if="isLoading" class="activity-empty">Loading latest activity...</div>
            <div v-else-if="loadError" class="activity-empty">{{ loadError }}</div>
            <div v-else-if="!recentActivity.length" class="activity-empty">
              No activity yet. New bookings and users will appear here.
            </div>
            <template v-else>
              <div v-for="item in recentActivity" :key="item.name + item.time" class="activity-item">
                <div class="activity-icon"></div>
                <div>
                  <p class="activity-text">
                    <strong>{{ item.name }}</strong>
                    {{ item.detail }}
                  </p>
                  <p class="activity-time">{{ item.time }}</p>
                </div>
              </div>
            </template>
          </div>
        </article>

        <div class="side-stack">
          <article class="report-card">
            <div class="report-head">
              <h3>Monthly Report</h3>
              <span class="report-pill">{{ monthlyReport.growthLabel }}</span>
            </div>
            <p>{{ monthlyReport.message }}</p>
            <button class="primary-btn" type="button">Download PDF</button>
          </article>

          <article class="status-card">
            <h3>System Status</h3>
            <div class="status-row">
              <div>
                <p>API Health</p>
                <span>{{ systemStatus.apiLabel }} · {{ systemStatus.apiPercent }}%</span>
              </div>
              <div class="bar">
                <span class="bar-fill" :style="{ width: `${systemStatus.apiPercent}%` }"></span>
              </div>
            </div>
            <div class="status-row">
              <div>
                <p>Cache Sync</p>
                <span>{{ systemStatus.cacheLabel }} · {{ systemStatus.cachePercent }}%</span>
              </div>
              <div class="bar">
                <span class="bar-fill warning" :style="{ width: `${systemStatus.cachePercent}%` }"></span>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section v-else-if="activeKey === 'settings'" class="settings-page">
        <div class="settings-header">
          <div class="settings-title">
            <div class="settings-icon">
              <svg viewBox="0 0 24 24">
                <path
                  d="M12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4zm8.5 4a6.5 6.5 0 0 0-.08-1l2.08-1.6-2-3.46-2.45 1a6.86 6.86 0 0 0-1.73-1L14 2h-4l-.32 2.94a6.86 6.86 0 0 0-1.73 1l-2.45-1-2 3.46L5.58 11a6.5 6.5 0 0 0 0 2l-2.08 1.6 2 3.46 2.45-1a6.86 6.86 0 0 0 1.73 1L10 22h4l.32-2.94a6.86 6.86 0 0 0 1.73-1l2.45 1 2-3.46L20.42 13a6.5 6.5 0 0 0 .08-1z"
                />
              </svg>
            </div>
            <div>
              <h2>Settings</h2>
              <p>Manage your account preferences and notifications.</p>
            </div>
          </div>
        </div>

        <p v-if="settingsNotice" class="settings-notice">{{ settingsNotice }}</p>

        <div class="settings-layout">
          <aside class="settings-menu">
            <button
              v-for="item in settingsSections"
              :key="item.key"
              type="button"
              class="settings-link"
              :class="{ active: activeSettingsSection === item.key }"
              @click="scrollToSection(item.key)"
            >
              <span class="dot"></span>
              {{ item.label }}
            </button>
          </aside>

          <div class="settings-content">
            <article id="settings-profile" class="settings-card">
              <div class="card-header">
                <div class="card-title">
                  <div class="card-icon">
                    <svg viewBox="0 0 24 24">
                      <path
                        d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.33 0-6 1.34-6 3v1h12v-1c0-1.66-2.67-3-6-3Z"
                        fill="currentColor"
                      />
                    </svg>
                  </div>
                  <div>
                    <h3>Profile Settings</h3>
                    <p>Update your photo and personal details.</p>
                    <div class="profile-meta">
                      <span class="chip success">
                        <svg viewBox="0 0 24 24">
                          <path d="M20 6 9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Completion: {{ profileCompletion }}%
                      </span>
                      <span class="chip muted">Last updated: Today</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="profile-row">
                <div class="profile-avatar">
                  <div class="avatar-circle" :class="{ 'has-image': displayAvatar }">
                    <img v-if="displayAvatar" :src="displayAvatar" alt="Profile preview" />
                    <span v-else>{{ initials }}</span>
                  </div>
                  <div class="profile-details">
                    <p class="label">Profile Picture</p>
                    <p class="hint">JPG, GIF or PNG. Max size of 800K</p>
                    <div class="upload-actions">
                      <input
                        ref="avatarUploadRef"
                        type="file"
                        accept="image/png,image/jpeg,image/gif"
                        class="sr-only"
                        @change="handleAvatarFile"
                      />
                      <button class="primary-btn" type="button" @click="triggerAvatarUpload">Upload</button>
                      <button class="link-btn danger" type="button" @click="clearAvatar">Remove</button>
                    </div>
                    <p class="hint subtle">Tip: square images look best for avatars.</p>
                  </div>
                </div>
                <div class="form-grid">
                  <label>
                    <span>Full Name</span>
                    <input v-model="profileForm.name" type="text" />
                  </label>
                  <label>
                    <span>Role</span>
                    <input v-model="profileForm.role" type="text" disabled />
                  </label>
                  <label class="full">
                    <span>Email Address</span>
                    <input v-model="profileForm.email" type="email" />
                  </label>
                </div>
              </div>
            </article>

            <article id="settings-security" class="settings-card">
              <div class="card-header">
                <div class="card-title">
                  <div class="card-icon">
                    <svg viewBox="0 0 24 24">
                      <path
                        d="M12 2 5 5v6c0 5 3.5 9.74 7 11 3.5-1.26 7-6 7-11V5Z"
                        fill="currentColor"
                      />
                    </svg>
                  </div>
                  <div>
                    <h3>Security & Privacy</h3>
                    <p>Manage your password and authentication.</p>
                  </div>
                </div>
              </div>
              <div class="form-grid">
                <label>
                  <span>Current Password</span>
                  <input type="password" value="password" />
                </label>
                <label>
                  <span>New Password</span>
                  <input v-model="securityForm.newPassword" type="password" placeholder="Enter new password" />
                </label>
              </div>
              <div class="toggle-row">
                <div>
                  <p>Two-Factor Authentication</p>
                  <span>Add an extra layer of security to your account.</span>
                </div>
                <label class="switch">
                  <input v-model="securityForm.twoFactor" type="checkbox" />
                  <span></span>
                </label>
              </div>
            </article>

            <article id="settings-notifications" class="settings-card">
              <div class="card-header">
                <div class="card-title">
                  <div class="card-icon">
                    <svg viewBox="0 0 24 24">
                      <path
                        d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1Z"
                        fill="currentColor"
                      />
                    </svg>
                  </div>
                  <div>
                    <h3>Notification Preferences</h3>
                    <p>Choose how you want to be notified.</p>
                  </div>
                </div>
              </div>
              <div class="toggle-list">
                <div class="toggle-row">
                  <div>
                    <p>Email Notifications</p>
                    <span>Receive summaries of bookings and revenue.</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationForm.email" type="checkbox" />
                    <span></span>
                  </label>
                </div>
                <div class="toggle-row">
                  <div>
                    <p>SMS Alerts</p>
                    <span>Urgent event cancellations or security alerts.</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationForm.sms" type="checkbox" />
                    <span></span>
                  </label>
                </div>
                <div class="toggle-row">
                  <div>
                    <p>Push Notifications</p>
                    <span>Browser and mobile app push alerts.</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationForm.push" type="checkbox" />
                    <span></span>
                  </label>
                </div>
              </div>
            </article>

            <article id="settings-system" class="settings-card">
              <div class="card-header">
                <div class="card-title">
                  <div class="card-icon">
                    <svg viewBox="0 0 24 24">
                      <path
                        d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 2a8 8 0 0 1 7.75 6h-4.08a12.94 12.94 0 0 0-1.4-4.24A7.97 7.97 0 0 1 12 4Zm-2.27 0.76A12.94 12.94 0 0 0 8.33 8H4.25A8 8 0 0 1 9.73 4.76ZM4.25 10h3.5a14.76 14.76 0 0 0 0 4h-3.5a8.06 8.06 0 0 1 0-4Zm0 6h4.08a12.94 12.94 0 0 0 1.4 4.24A8 8 0 0 1 4.25 16Zm9.25 4a8 8 0 0 1-1.67-.18A10.94 10.94 0 0 1 10.83 16h6.92A8 8 0 0 1 13.5 20Zm1.67-8a12.94 12.94 0 0 0-1.4-4.24A8 8 0 0 1 19.75 8h-4.08A12.94 12.94 0 0 0 15.17 12Zm-2.34 2a10.94 10.94 0 0 1 .39 2h-3.44a10.94 10.94 0 0 1 .39-2Z"
                        fill="currentColor"
                      />
                    </svg>
                  </div>
                  <div>
                    <h3>System Preferences</h3>
                    <p>Set language, currency, and theme.</p>
                  </div>
                </div>
              </div>
              <div class="form-grid">
                <label>
                  <span>Interface Language</span>
                  <select v-model="systemForm.language">
                    <option>English (US)</option>
                    <option>English (UK)</option>
                    <option>Khmer</option>
                  </select>
                </label>
                <label>
                  <span>Default Currency</span>
                  <select v-model="systemForm.currency">
                    <option>USD - US Dollar</option>
                    <option>KHR - Khmer Riel</option>
                    <option>EUR - Euro</option>
                  </select>
                </label>
              </div>
              <div class="toggle-row theme-row">
                <div>
                  <p>Dark Mode Interface</p>
                  <span>Switch between light and dark display modes.</span>
                </div>
                <div class="theme-toggle">
                  <button
                    type="button"
                    :class="{ active: !systemForm.darkMode }"
                    @click="systemForm.darkMode = false"
                  >
                    Light
                  </button>
                  <button
                    type="button"
                    :class="{ active: systemForm.darkMode }"
                    @click="systemForm.darkMode = true"
                  >
                    Dark
                  </button>
                </div>
              </div>
              <div class="toggle-row">
                <div>
                  <p>Weekly PDF Digest</p>
                  <span>Receive a summary of bookings and revenue every Monday.</span>
                </div>
                <label class="switch">
                  <input v-model="systemForm.weeklyDigest" type="checkbox" />
                  <span></span>
                </label>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section v-else-if="activeKey === 'users'" class="users-page">
        <div class="users-toolbar">
          <label class="search users-search">
            <span class="search-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path
                  d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z"
                />
              </svg>
            </span>
            <input type="search" placeholder="Search users by name, email..." />
          </label>
        </div>

        <section class="users-stats">
          <article class="users-stat">
            <p>Total Users</p>
            <h3>24,892</h3>
            <span class="delta up">+12.5% from last month</span>
          </article>
          <article class="users-stat">
            <p>New Users (Month)</p>
            <h3>1,402</h3>
            <span class="delta up">+8% growth rate</span>
          </article>
          <article class="users-stat">
            <p>Active Users</p>
            <h3>18,245</h3>
            <span class="delta neutral">73% retention</span>
          </article>
          <article class="users-stat">
            <p>Churn Rate</p>
            <h3>2.4%</h3>
            <span class="delta down">0.5% decrease</span>
          </article>
        </section>

        <div class="users-layout">
          <article class="users-directory">
            <header class="directory-header">
              <div>
                <h2>User Directory</h2>
                <p>Managing all registered accounts across the platform.</p>
              </div>
              <div class="directory-actions">
                <button class="ghost-btn" type="button">Filter</button>
                <button class="ghost-btn" type="button">Export CSV</button>
              </div>
            </header>

            <div class="directory-table">
              <div class="table-head">
                <span>User</span>
                <span>Contact Info</span>
                <span>Joined Date</span>
                <span>Bookings</span>
                <span>Status</span>
                <span>Action</span>
              </div>
              <div class="table-row">
                <div class="user-cell">
                  <div class="user-avatar">SC</div>
                  <div>
                    <p>Sarah Connor</p>
                    <span>ID: #88219</span>
                  </div>
                </div>
                <div class="contact-cell">
                  <p>sarah.c@gmail.com</p>
                  <span>+855 12 345 678</span>
                </div>
                <div>Oct 12, 2023</div>
                <div><span class="tag">42 Total</span></div>
                <div><span class="status active">Active</span></div>
                <div class="dots">• • •</div>
              </div>
              <div class="table-row">
                <div class="user-cell">
                  <div class="user-avatar dark">MW</div>
                  <div>
                    <p>Marcus Wright</p>
                    <span>ID: #88154</span>
                  </div>
                </div>
                <div class="contact-cell">
                  <p>m.wright@tech.io</p>
                  <span>+855 88 990 112</span>
                </div>
                <div>Nov 03, 2023</div>
                <div><span class="tag">12 Total</span></div>
                <div><span class="status active">Active</span></div>
                <div class="dots">• • •</div>
              </div>
              <div class="table-row">
                <div class="user-cell">
                  <div class="user-avatar">JH</div>
                  <div>
                    <p>John Henry</p>
                    <span>ID: #88001</span>
                  </div>
                </div>
                <div class="contact-cell">
                  <p>j.henry@global.com</p>
                  <span>+855 96 111 223</span>
                </div>
                <div>Aug 21, 2023</div>
                <div><span class="tag">8 Total</span></div>
                <div><span class="status inactive">Inactive</span></div>
                <div class="dots">• • •</div>
              </div>
              <div class="table-row">
                <div class="user-cell">
                  <div class="user-avatar">GB</div>
                  <div>
                    <p>Grace Brewster</p>
                    <span>ID: #87992</span>
                  </div>
                </div>
                <div class="contact-cell">
                  <p>g.brew@outlook.com</p>
                  <span>+855 77 445 566</span>
                </div>
                <div>Jan 15, 2024</div>
                <div><span class="tag">105 Total</span></div>
                <div><span class="status active">Active</span></div>
                <div class="dots">• • •</div>
              </div>
            </div>

            <footer class="directory-footer">
              <span>Showing 1-10 of 24,892 users</span>
              <div class="pager">
                <button class="pager-btn" type="button">1</button>
                <button class="pager-btn active" type="button">2</button>
                <button class="pager-btn" type="button">3</button>
                <span class="dots">…</span>
              </div>
            </footer>
          </article>

          <aside class="users-profile">
            <div class="profile-card">
              <div class="profile-hero"></div>
              <div class="profile-body">
                <div class="profile-photo">SC</div>
                <h3>Sarah Connor</h3>
                <p class="profile-email">sarah.c@gmail.com</p>
                <div class="profile-stats">
                  <div>
                    <span>Spent</span>
                    <strong>$12.4k</strong>
                  </div>
                  <div>
                    <span>Bookings</span>
                    <strong>42</strong>
                  </div>
                  <div>
                    <span>Last Login</span>
                    <strong>2h ago</strong>
                  </div>
                </div>
                <div class="profile-activity">
                  <p>Recent Activity</p>
                  <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                      <strong>Booked: Angkor Night Run</strong>
                      <span>Today, 10:45 AM • $45.00</span>
                    </div>
                  </div>
                  <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                      <strong>Updated Profile Picture</strong>
                      <span>Yesterday, 04:22 PM</span>
                    </div>
                  </div>
                  <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                      <strong>Canceled: Tech Meetup '24</strong>
                      <span>Feb 18, 2024 • Refunded</span>
                    </div>
                  </div>
                </div>
                <div class="profile-actions">
                  <button class="ghost-btn" type="button">Edit</button>
                  <button class="ghost-btn" type="button">Reset</button>
                </div>
                <button class="danger-btn" type="button">Suspend User Account</button>
              </div>
            </div>
            <div class="elite-card">
              <h4>Elite Member</h4>
              <p>Sarah is in the top 2% of contributors in the Siem Reap region.</p>
              <button class="link-btn" type="button">View full engagement report</button>
            </div>
          </aside>
        </div>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.admin-shell {
  --ink: #0f172a;
  --muted: #5f6b7a;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #ffe7d2;
  --accent-cool: #3b82f6;
  --surface: rgba(255, 255, 255, 0.9);
  --surface-strong: rgba(255, 255, 255, 0.96);
  --stroke: rgba(17, 24, 39, 0.08);
  --shadow: 0 30px 72px rgba(15, 23, 42, 0.14);
  --shadow-soft: 0 16px 34px rgba(15, 23, 42, 0.1);
  display: grid;
  grid-template-columns: minmax(300px, 360px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
  background:
    radial-gradient(circle at 12% 12%, rgba(255, 122, 26, 0.18), transparent 45%),
    radial-gradient(circle at 78% 18%, rgba(59, 130, 246, 0.18), transparent 46%),
    linear-gradient(135deg, #fff2e6 0%, #f5f6ff 52%, #eef6f9 100%);
  color: var(--ink);
  position: relative;
  overflow: hidden;
}

.admin-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 14% 70%, rgba(255, 122, 26, 0.14), transparent 45%),
    radial-gradient(circle at 78% 78%, rgba(99, 102, 241, 0.12), transparent 48%);
  pointer-events: none;
}

.admin-shell > * {
  position: relative;
  z-index: 1;
}

.admin-sidebar {
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.95) 0%, rgba(245, 247, 252, 0.92) 100%);
  border-right: 1px solid var(--stroke);
  padding: 36px 28px;
  display: flex;
  flex-direction: column;
  gap: 28px;
  backdrop-filter: blur(16px);
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
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.9));
  border: 1px solid rgba(17, 24, 39, 0.06);
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
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(249, 252, 255, 0.94));
  border-radius: 18px;
  padding: 16px;
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
  display: grid;
  gap: 10px;
  border: 1px solid rgba(15, 23, 42, 0.05);
  position: relative;
  overflow: hidden;
}




.admin-user-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 90% 10%, rgba(255, 122, 26, 0.12), transparent 38%);
  pointer-events: none;
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
  box-shadow: 0 10px 18px rgba(241, 91, 42, 0.2);
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
  margin-top: 4px;
  border: 1px solid rgba(255, 122, 26, 0.22);
  background: linear-gradient(135deg, #ffede0, #ffe7d2);
  padding: 10px 12px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  color: #c65300;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  width: 100%;
}

.logout-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 22px rgba(241, 91, 42, 0.16);
}

.admin-main {
  padding: 28px 36px 40px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  position: relative;
  overflow: visible;
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
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.98));
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
  border-radius: 18px;
  padding: 14px;
  z-index: 1600;
}

.notification-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 10px;
}

.notification-actions {
  display: flex;
  gap: 8px;
}

.notification-action-btn {
  border: none;
  background: #f6f8fb;
  color: #0f172a;
  padding: 7px 11px;
  border-radius: 12px;
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
  gap: 12px;
  max-height: 320px;
  overflow-y: auto;
}

.notification-item {
  border: 1px solid rgba(15, 23, 42, 0.06);
  border-radius: 14px;
  padding: 12px;
  background: #fff;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
  transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.notification-item.unread {
  background: linear-gradient(180deg, #fff7f2, #fff);
  border-color: rgba(241, 91, 42, 0.35);
  box-shadow: 0 16px 32px rgba(241, 91, 42, 0.16);
}

.notification-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
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
  margin: 6px 0 10px;
  color: #0f172a;
  font-size: 13px;
}

.notification-item-actions {
  display: flex;
  gap: 10px;
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

.topbar-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.topbar-avatar.has-image {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 8px 14px rgba(15, 23, 42, 0.08);
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

.topbar-avatar.has-image {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.admin-hero {
  display: grid;
  gap: 12px;
  padding: 22px 24px;
  border-radius: 26px;
  background: var(--surface);
  border: 1px solid rgba(255, 255, 255, 0.65);
  box-shadow: var(--shadow);
}

.admin-hero .eyebrow {
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 1.6px;
  text-transform: uppercase;
  margin: 0;
  color: #b45309;
}

.hero-title {
  margin: 0;
  font-size: 38px;
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
  flex-wrap: wrap;
}

.hero-actions .ghost-btn,
.hero-actions .primary-btn {
  min-width: 160px;
}

.admin-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}

.stat-card {
  background: var(--surface);
  padding: 18px;
  border-radius: 20px;
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.65);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.stat-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.14), transparent 55%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.stat-card:hover::after {
  opacity: 1;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 32px 70px rgba(15, 23, 42, 0.16);
}

.stat-card.solid {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 100%);
  color: #fff;
  border-color: transparent;
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.2), rgba(255, 122, 26, 0.05));
  margin-bottom: 12px;
  border: 1px solid rgba(255, 122, 26, 0.2);
  display: grid;
  place-items: center;
  color: #f15b2a;
}

.stat-card.solid .stat-icon {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.stat-icon svg {
  width: 20px;
  height: 20px;
  fill: currentColor;
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

.stat-delta.neutral {
  background: #eef2f6;
  color: #64748b;
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

.report-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.report-pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.35);
  font-size: 12px;
  font-weight: 600;
  color: #fff;
}

.activity-list {
  display: grid;
  gap: 14px;
}

.activity-empty {
  padding: 16px;
  border-radius: 14px;
  border: 1px dashed rgba(15, 23, 42, 0.12);
  background: rgba(255, 255, 255, 0.9);
  color: var(--muted);
  font-size: 13px;
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

.settings-page {
  display: grid;
  gap: 24px;
  position: relative;
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.settings-title {
  display: flex;
  align-items: center;
  gap: 16px;
}

.settings-title h2 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.settings-title p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 14px;
}

.settings-icon {
  width: 52px;
  height: 52px;
  border-radius: 18px;
  background: linear-gradient(135deg, #fff1e4 0%, #ffe2cb 100%);
  border: 1px solid rgba(255, 122, 26, 0.2);
  display: grid;
  place-items: center;
  color: var(--accent);
  box-shadow: 0 12px 24px rgba(255, 122, 26, 0.2);
}

.settings-icon svg {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

.settings-layout {
  display: grid;
  grid-template-columns: minmax(220px, 260px) 1fr;
  gap: 24px;
}

.settings-notice {
  margin: 0 0 12px;
  padding: 10px 14px;
  border-radius: 12px;
  background: #fff7f2;
  color: #c65300;
  border: 1px solid rgba(255, 122, 26, 0.25);
  font-weight: 600;
}

.settings-menu {
  background: var(--surface-strong);
  border-radius: 20px;
  padding: 18px;
  display: grid;
  gap: 12px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
  height: fit-content;
}

.settings-link {
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 14px;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.settings-link .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #e2e8f0;
}

.settings-link.active,
.settings-link:hover {
  background: rgba(255, 122, 26, 0.12);
  color: var(--accent);
}

.settings-link.active .dot {
  background: var(--accent);
}

.settings-content {
  display: grid;
  gap: 20px;
}

.settings-card {
  background: var(--surface);
  border-radius: 20px;
  padding: 22px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.card-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #fff2e6;
  color: #c65300;
  display: grid;
  place-items: center;
  border: 1px solid rgba(255, 122, 26, 0.2);
  box-shadow: 0 8px 14px rgba(255, 122, 26, 0.15);
}

.card-icon svg {
  width: 18px;
  height: 18px;
}

.card-header h3 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.card-header p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: #fff2e6;
  color: #c65300;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.pill.alt {
  background: #eef6ff;
  color: #2f5aa8;
}

.profile-row {
  display: grid;
  gap: 20px;
}

.profile-avatar {
  display: flex;
  align-items: center;
  gap: 16px;
  background: rgba(255, 255, 255, 0.9);
  padding: 16px;
  border-radius: 16px;
  border: 1px solid var(--stroke);
  flex-wrap: wrap;
}

.profile-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.chip svg {
  width: 14px;
  height: 14px;
}

.chip.success {
  background: #e7f6ec;
  color: #0f9b4c;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.chip.muted {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.profile-details {
  flex: 1;
  min-width: 220px;
}

.avatar-circle {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: linear-gradient(135deg, #ffb98b 0%, #ff8b3d 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 700;
}

.avatar-circle.has-image {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  overflow: hidden;
  box-shadow: 0 8px 14px rgba(15, 23, 42, 0.12);
}

.avatar-circle img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.label {
  margin: 0;
  font-weight: 600;
}

.hint {
  margin: 4px 0 10px;
  color: var(--muted);
  font-size: 12px;
}

.hint.subtle {
  color: #94a3b8;
  margin: 6px 0 0;
}

.upload-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.upload-actions .ghost-btn,
.upload-actions .link-btn {
  white-space: nowrap;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
}

.form-grid label {
  display: grid;
  gap: 6px;
  font-size: 12px;
  color: var(--muted);
}

.form-grid label span {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 600;
  color: #6b7280;
}

.form-grid input,
.form-grid select {
  border: 1px solid var(--stroke);
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 14px;
  background: #fff;
  color: var(--ink);
}

.form-grid input:disabled {
  background: #f1f5f9;
  color: #94a3b8;
}

.form-grid .full {
  grid-column: 1 / -1;
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--stroke);
  background: rgba(255, 255, 255, 0.9);
  margin-top: 12px;
  flex-wrap: wrap;
}

.toggle-row p {
  margin: 0;
  font-weight: 600;
}

.toggle-row span {
  display: block;
  color: var(--muted);
  font-size: 12px;
  margin-top: 4px;
}

.toggle-list {
  display: grid;
  gap: 12px;
}

.switch {
  position: relative;
  width: 46px;
  height: 26px;
  flex-shrink: 0;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch span {
  position: absolute;
  inset: 0;
  background: #e2e8f0;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.switch span::after {
  content: "";
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s ease;
  box-shadow: 0 6px 12px rgba(15, 23, 42, 0.2);
}

.switch input:checked + span {
  background: #ff7a1a;
}

.switch input:checked + span::after {
  transform: translateX(20px);
}

.theme-row {
  margin-top: 16px;
}

.theme-toggle {
  display: inline-flex;
  background: #f1f5f9;
  border-radius: 999px;
  padding: 4px;
  gap: 4px;
}

.theme-toggle button {
  border: none;
  background: transparent;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  color: #6b7280;
}

.theme-toggle button.active {
  background: #fff;
  color: #c65300;
  box-shadow: var(--shadow-soft);
}

.link-btn.danger {
  color: #e2553f;
}

.users-page {
  display: grid;
  gap: 22px;
}

.users-toolbar {
  display: flex;
  justify-content: flex-end;
}

.users-search {
  max-width: 420px;
}

.users-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.users-stat {
  background: var(--surface-strong);
  border-radius: 18px;
  padding: 16px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
}

.users-stat h3 {
  margin: 8px 0;
  font-size: 22px;
}

.users-stat p {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.delta {
  font-size: 12px;
}

.delta.up {
  color: #f15b2a;
}

.delta.down {
  color: #e2553f;
}

.delta.neutral {
  color: #64748b;
}

.users-layout {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
  gap: 20px;
}

.users-directory {
  background: var(--surface);
  border-radius: 20px;
  padding: 20px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  min-width: 0;
  overflow: hidden;
}

.directory-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

.directory-header h2 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.directory-header p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.directory-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.directory-actions .ghost-btn {
  white-space: nowrap;
}

.directory-table {
  display: grid;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1.2fr 0.8fr 0.7fr 0.7fr 0.4fr;
  align-items: center;
  gap: 10px;
  padding: 10px 8px;
  min-width: 680px;
}

.table-head {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #6b7280;
}

.table-row {
  background: #fff;
  border-radius: 14px;
  border: 1px solid var(--stroke);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-cell p {
  margin: 0;
  font-weight: 600;
}

.user-cell span,
.contact-cell span {
  font-size: 12px;
  color: var(--muted);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #ffe7d2;
  display: grid;
  place-items: center;
  color: #c65300;
  font-weight: 700;
}

.user-avatar.dark {
  background: #0f172a;
  color: #fff;
}

.contact-cell p {
  margin: 0;
  font-size: 13px;
}

.tag {
  background: #fff2e6;
  color: #c65300;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status.active {
  background: #eaf8ef;
  color: #2f9e5f;
}

.status.inactive {
  background: #eef2f6;
  color: #64748b;
}

.dots {
  color: #94a3b8;
  font-weight: 700;
}

.directory-footer {
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 13px;
  color: var(--muted);
}

.pager {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pager-btn {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: 1px solid var(--stroke);
  background: #fff;
  cursor: pointer;
}

.pager-btn.active {
  background: var(--accent);
  color: #fff;
  border-color: transparent;
}

.users-profile {
  display: grid;
  gap: 16px;
  min-width: 0;
}

.profile-card {
  background: var(--surface-strong);
  border-radius: 22px;
  border: 1px solid var(--stroke);
  overflow: hidden;
  box-shadow: var(--shadow);
}

.profile-hero {
  height: 90px;
  background: linear-gradient(135deg, #ff7a1a 0%, #ff9a4d 100%);
}

.profile-body {
  padding: 18px;
  display: grid;
  gap: 14px;
}

.profile-photo {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  background: #111827;
  color: #fff;
  display: grid;
  place-items: center;
  font-weight: 700;
  margin-top: -40px;
  border: 4px solid #fff;
}

.profile-body h3 {
  margin: 0;
}

.profile-email {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
}

.profile-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  text-align: center;
  font-size: 12px;
}

.profile-stats strong {
  display: block;
  margin-top: 4px;
}

.profile-activity {
  display: grid;
  gap: 10px;
}

.profile-activity p {
  margin: 0;
  font-weight: 600;
}

.profile-activity .activity-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.profile-activity .activity-item span {
  display: block;
  font-size: 12px;
  color: var(--muted);
  margin-top: 2px;
}

.activity-dot {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  background: #fff2e6;
}

.profile-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.profile-actions .ghost-btn {
  flex: 1 1 120px;
  white-space: nowrap;
}

.danger-btn {
  border: 1px solid #ffb4a9;
  background: #fff5f5;
  color: #e2553f;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
}

.elite-card {
  background: linear-gradient(135deg, #ff7a1a 0%, #ff9a4d 100%);
  border-radius: 20px;
  padding: 18px;
  color: #fff;
  box-shadow: var(--shadow-soft);
}

.elite-card h4 {
  margin: 0 0 8px;
}

.elite-card p {
  margin: 0 0 12px;
  font-size: 13px;
}

@media (max-width: 1100px) {
  .users-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .directory-actions {
    width: 100%;
  }

  .directory-actions .ghost-btn {
    flex: 1 1 140px;
  }

  .profile-actions .ghost-btn,
  .danger-btn {
    width: 100%;
  }

  .profile-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    text-align: left;
  }
}

@media (max-width: 520px) {
  .profile-stats {
    grid-template-columns: 1fr;
  }
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

  .admin-user-card {
    margin-top: 0;
  }

  .admin-grid {
    grid-template-columns: 1fr;
  }

  .settings-layout {
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
    gap: 10px;
  }

  .hero-actions .ghost-btn,
  .hero-actions .primary-btn {
    width: 100%;
    text-align: center;
  }

  .nav-item {
    flex: 0 0 auto;
    white-space: nowrap;
  }
}
</style>
