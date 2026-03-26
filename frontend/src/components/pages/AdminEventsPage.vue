<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { eventTypeMap } from "../../features/appData";
import { formatDateTime } from "../../features/bookingMappers";
import { apiGet } from "../../features/apiClient";

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
const statusFilter = ref("all");
const isLoading = ref(false);
const loadError = ref("");
const vendorEvents = ref([]);
const selectedEventId = ref(null);
const failedVendorImages = ref(new Set());
const navItems = [
  { key: "dashboard", label: "Dashboard", icon: "dashboard" },
  { key: "events", label: "Events", icon: "events" },
  { key: "admin-bookings", label: "Bookings", icon: "bookings" },
  { key: "vendors", label: "Vendors", icon: "vendors" },
  { key: "customers", label: "Customers", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];
const activeKey = ref("events");
const initials = computed(() => {
  const pieces = String(props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});
const normalizedEvents = computed(() =>
  vendorEvents.value.map((event) => {
    const vendorName = String(event.vendor?.name || event.vendor_name || "Vendor").trim() || "Vendor";
    const vendorId = Number(event.vendor_id || 0) || null;
    const eventType = String(event.event_type || "other").trim().toLowerCase();
    const isActive = Boolean(event.is_active);

    return {
      id: Number(event.id),
      key: `event:${event.id}`,
      badge: vendorName
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase() || "EV",
      title: String(event.title || "Untitled Service").trim() || "Untitled Service",
      vendorName,
      vendorId,
      vendorImageKey: vendorId ? `vendor:${vendorId}` : `vendor:${vendorName.toLowerCase()}`,
      vendorProfileImageUrl: String(event.vendor?.profile_image_url || event.vendor_profile_image_url || "").trim(),
      category: eventTypeMap[eventType] || "Other",
      location: String(event.location || "Location not added yet").trim() || "Location not added yet",
      startsAt: event.starts_at || "",
      updatedAt: event.updated_at || event.created_at || event.starts_at || "",
      submitted: formatDateTime(event.updated_at || event.created_at || event.starts_at),
      status: isActive ? "Active" : "Inactive",
      statusClass: isActive ? "active" : "inactive",
      bookingsCount: Number(event.bookings_count || 0),
      price: Number(event.price || 0),
      priceLabel: `$${Number(event.price || 0).toLocaleString()}`,
      capacity: Number(event.capacity || 0),
      serviceMode: String(event.service_mode || "overall").toLowerCase(),
      imageUrl: String(event.image_url || "").trim(),
      description: String(event.description || "").trim(),
      isActive,
    };
  }),
);

const filteredEvents = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return normalizedEvents.value.filter((event) => {
    if (statusFilter.value === "active" && !event.isActive) return false;
    if (statusFilter.value === "inactive" && event.isActive) return false;
    if (!query) return true;

    return [
      event.title,
      event.vendorName,
      event.category,
      event.location,
      event.status,
      String(event.id),
    ]
      .join(" ")
      .toLowerCase()
      .includes(query);
  });
});

const selectedEvent = computed(
  () => filteredEvents.value.find((event) => event.id === selectedEventId.value) || filteredEvents.value[0] || null,
);

const totalVendorCount = computed(() => {
  const keys = new Set(
    normalizedEvents.value.map((event) => event.vendorId || `vendor:${event.vendorName.toLowerCase()}`),
  );
  return keys.size;
});

const activeListingCount = computed(() => normalizedEvents.value.filter((event) => event.isActive).length);
const inactiveListingCount = computed(() => normalizedEvents.value.length - activeListingCount.value);
const totalBookingsCount = computed(() =>
  normalizedEvents.value.reduce((sum, event) => sum + Number(event.bookingsCount || 0), 0),
);

const highlights = computed(() => [
  {
    label: "Total Listings",
    value: String(normalizedEvents.value.length),
    delta: `${totalVendorCount.value} vendors`,
  },
  {
    label: "Active Listings",
    value: String(activeListingCount.value),
    delta: inactiveListingCount.value ? `${inactiveListingCount.value} inactive` : "All listings are live",
  },
  {
    label: "Vendor Coverage",
    value: String(totalVendorCount.value),
    delta: "Accounts with services",
  },
  {
    label: "Linked Bookings",
    value: String(totalBookingsCount.value),
    delta: "Across all vendor events",
  },
]);

const hasVendorProfileImage = (event) =>
  Boolean(String(event?.vendorProfileImageUrl || "").trim())
  && !failedVendorImages.value.has(event?.vendorImageKey || event?.key);

function handleVendorImageError(imageKey) {
  if (!imageKey || failedVendorImages.value.has(imageKey)) {
    return;
  }

  const next = new Set(failedVendorImages.value);
  next.add(imageKey);
  failedVendorImages.value = next;
}

async function loadAdminEvents() {
  isLoading.value = true;
  loadError.value = "";

  try {
    const result = await apiGet("events", { per_page: 100, include_inactive: 1, ts: Date.now() });
    vendorEvents.value = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    failedVendorImages.value = new Set();
  } catch (error) {
    vendorEvents.value = [];
    loadError.value = error?.message || "Could not load vendor events.";
  } finally {
    isLoading.value = false;
  }
}

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

function selectEvent(eventId) {
  selectedEventId.value = Number(eventId || 0) || null;
}

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
watch(
  filteredEvents,
  (rows) => {
    if (!rows.length) {
      selectedEventId.value = null;
      return;
    }

    if (!rows.some((event) => event.id === selectedEventId.value)) {
      selectedEventId.value = rows[0].id;
    }
  },
  { immediate: true },
);
onMounted(() => void loadAdminEvents());
</script>

<template>
  <section class="admin-shell events-shell">
    <aside class="admin-sidebar">
      <div class="brand-card">
        <div class="brand">
          <div class="brand-logo">
            <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
            <div v-else class="brand-mark">A</div>
          </div>
          <div>
            <p class="brand-kicker">Operations Console</p>
            <p class="brand-title">Achar Admin</p>
            <p class="brand-subtitle">Vendor listing workspace</p>
          </div>
        </div>
      </div>

      <section class="sidebar-block">
        <div class="sidebar-block-head">
          <span class="sidebar-section-label">Navigation</span>
          <span class="sidebar-section-caption">Admin modules</span>
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
            <span class="nav-copy">
              <strong>{{ item.label }}</strong>
              <small>{{ item.key === "events" ? "All vendor listings" : "Open workspace" }}</small>
            </span>
          </button>
        </nav>
      </section>

    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <label class="search">
          <span class="search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z" />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" placeholder="Search vendor events or services..." />
        </label>
        <div class="topbar-actions">
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
          <p class="eyebrow">Vendor Event Directory</p>
          <h1 class="hero-title">All Vendor Events & Services</h1>
          <p class="hero-subtitle">
            Browse every event and service your vendors have created, including inactive listings.
          </p>
        </div>
        <button class="primary-btn" type="button" @click="loadAdminEvents">Refresh List</button>
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
            <div>
              <h3>Vendor Event List</h3>
              <p class="queue-summary">
                {{ filteredEvents.length }} of {{ normalizedEvents.length }} listing(s) shown
              </p>
            </div>
            <div class="filters">
              <button class="pill" :class="{ active: statusFilter === 'all' }" type="button" @click="statusFilter = 'all'">All</button>
              <button class="pill" :class="{ active: statusFilter === 'active' }" type="button" @click="statusFilter = 'active'">Active</button>
              <button class="pill" :class="{ active: statusFilter === 'inactive' }" type="button" @click="statusFilter = 'inactive'">Inactive</button>
            </div>
          </header>
          <p v-if="loadError" class="queue-feedback error">{{ loadError }}</p>
          <p v-else-if="isLoading" class="queue-feedback">Loading vendor event list...</p>
          <p v-else-if="!filteredEvents.length" class="queue-feedback">No vendor events matched this filter.</p>
          <div v-else class="queue-list">
            <button
              v-for="item in filteredEvents"
              :key="item.key"
              type="button"
              class="queue-row"
              :class="{ selected: selectedEvent?.id === item.id }"
              @click="selectEvent(item.id)"
            >
              <div class="badge vendor-badge">
                <img
                  v-if="hasVendorProfileImage(item)"
                  :src="item.vendorProfileImageUrl"
                  :alt="`${item.vendorName} profile`"
                  @error="handleVendorImageError(item.vendorImageKey)"
                />
                <span v-else>{{ item.badge }}</span>
              </div>
              <div>
                <p class="queue-title">{{ item.title }}</p>
                <p class="queue-meta">{{ item.vendorName }} | {{ item.category }}</p>
              </div>
              <span class="queue-date">{{ item.submitted }}</span>
              <span class="status" :class="item.statusClass">{{ item.status }}</span>
              <div class="row-actions">
                <span class="queue-stat">{{ item.priceLabel }}</span>
                <span class="queue-stat muted">{{ item.bookingsCount }} booking(s)</span>
              </div>
            </button>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedEvent" class="card detail-card">
            <header>
              <h3>Selected Event</h3>
              <button class="link-btn" type="button" @click="navigateTo('vendors')">View Vendors</button>
            </header>
            <div class="vendor-block">
              <div class="vendor-avatar">
                <img
                  v-if="hasVendorProfileImage(selectedEvent)"
                  :src="selectedEvent.vendorProfileImageUrl"
                  :alt="`${selectedEvent.vendorName} profile`"
                  @error="handleVendorImageError(selectedEvent.vendorImageKey)"
                />
                <span v-else>{{ selectedEvent.badge }}</span>
              </div>
              <div>
                <p class="vendor-name">{{ selectedEvent.vendorName }}</p>
                <p class="vendor-meta">{{ selectedEvent.location }}</p>
              </div>
            </div>
            <div class="vendor-stats">
              <div>
                <span>Status</span>
                <strong>{{ selectedEvent.status }}</strong>
              </div>
              <div>
                <span>Bookings</span>
                <strong>{{ selectedEvent.bookingsCount }}</strong>
              </div>
            </div>
            <div class="event-details-grid">
              <div>
                <span>Category</span>
                <strong>{{ selectedEvent.category }}</strong>
              </div>
              <div>
                <span>Price</span>
                <strong>{{ selectedEvent.priceLabel }}</strong>
              </div>
              <div>
                <span>Capacity</span>
                <strong>{{ selectedEvent.capacity || "Open" }}</strong>
              </div>
              <div>
                <span>Mode</span>
                <strong>{{ selectedEvent.serviceMode === "package" ? "Package" : "Overall" }}</strong>
              </div>
              <div>
                <span>Event Date</span>
                <strong>{{ selectedEvent.startsAt ? formatDateTime(selectedEvent.startsAt) : "Date TBD" }}</strong>
              </div>
              <div>
                <span>Last Update</span>
                <strong>{{ selectedEvent.submitted }}</strong>
              </div>
            </div>
            <p class="event-detail-copy">
              {{ selectedEvent.description || "This listing does not have a description yet." }}
            </p>
            <button class="primary-btn full" type="button" @click="navigateTo('vendors')">Open Vendor Workspace</button>
          </article>
          <article v-else class="card detail-card empty-detail-card">
            <header>
              <h3>No Event Selected</h3>
            </header>
            <p>Select a vendor event from the list to inspect its details here.</p>
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
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(244, 247, 252, 0.98) 100%);
  border-right: 1px solid var(--stroke);
  padding: 28px 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  backdrop-filter: blur(16px);
  position: relative;
}

.admin-sidebar::after {
  content: "";
  position: absolute;
  inset: 20px 18px;
  border-radius: 30px;
  background:
    linear-gradient(165deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.24)),
    radial-gradient(circle at top left, rgba(255, 122, 26, 0.12), transparent 58%);
  pointer-events: none;
  border: 1px solid rgba(255, 255, 255, 0.48);
}

.admin-sidebar > * {
  position: relative;
  z-index: 1;
}

.brand-card,
.sidebar-block {
  border: 1px solid rgba(15, 23, 42, 0.07);
  background: rgba(255, 255, 255, 0.78);
  box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
  backdrop-filter: blur(14px);
}

.brand-card {
  display: grid;
  gap: 16px;
  padding: 18px;
  border-radius: 28px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  width: 52px;
  height: 52px;
  border-radius: 18px;
  background: linear-gradient(135deg, #fff5eb 0%, #ffd7b5 100%);
  display: grid;
  place-items: center;
  box-shadow: 0 16px 30px rgba(255, 122, 26, 0.2);
  border: 1px solid rgba(255, 122, 26, 0.16);
}

.brand-logo img {
  width: 30px;
  height: 30px;
  object-fit: contain;
}

.brand-mark {
  font-weight: 700;
  color: var(--accent);
}

.brand-kicker {
  margin: 0 0 4px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #c86423;
}

.brand-title {
  font-weight: 700;
  margin: 0;
  font-family: "Fraunces", serif;
  font-size: 22px;
  color: #132238;
}

.brand-subtitle {
  margin: 3px 0 0;
  font-size: 13px;
  line-height: 1.5;
  color: #66758d;
}

.sidebar-section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #9a6a4b;
}

.sidebar-block {
  display: grid;
  gap: 14px;
  padding: 16px;
  border-radius: 26px;
}

.sidebar-block-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 2px 4px 0;
}

.sidebar-section-caption {
  font-size: 12px;
  color: #7b8ba2;
}

.admin-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid transparent;
  background: rgba(255, 255, 255, 0.44);
  padding: 12px 14px;
  border-radius: 20px;
  font-size: 15px;
  cursor: pointer;
  color: #314258;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease,
    background-color 0.2s ease;
}

.nav-icon {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, #ffffff, #eef3f9);
  color: #7c8ba3;
  transition: all 0.2s ease;
  border: 1px solid rgba(148, 163, 184, 0.14);
  flex-shrink: 0;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.nav-copy {
  display: grid;
  gap: 2px;
  text-align: left;
}

.nav-copy strong {
  font-size: 15px;
  font-weight: 600;
}

.nav-copy small {
  font-size: 12px;
  color: #7f8ca3;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.8);
  border-color: rgba(255, 122, 26, 0.12);
  transform: translateX(3px);
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
}

.nav-item.active {
  background:
    linear-gradient(135deg, rgba(255, 244, 234, 0.98), rgba(255, 228, 207, 0.96));
  color: #d05f17;
  border-color: rgba(255, 122, 26, 0.22);
  box-shadow:
    inset 3px 0 0 var(--accent),
    0 14px 28px rgba(255, 122, 26, 0.12);
}

.nav-item.active .nav-icon {
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.24), rgba(255, 122, 26, 0.08));
  color: #d7641d;
  border-color: rgba(255, 122, 26, 0.24);
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

.queue-summary {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--muted);
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

.queue-feedback {
  margin: 0;
  padding: 16px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.86);
  border: 1px solid rgba(15, 23, 42, 0.06);
  color: var(--muted);
}

.queue-feedback.error {
  color: #b45309;
  background: #fff7ed;
  border-color: rgba(245, 158, 11, 0.18);
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
  width: 100%;
  text-align: left;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  overflow: visible;
}

.queue-row:hover {
  transform: translateX(4px);
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
}

.queue-row.selected {
  border-color: rgba(255, 122, 26, 0.2);
  background: linear-gradient(135deg, rgba(255, 247, 240, 0.98), rgba(255, 255, 255, 1));
  box-shadow: 0 18px 36px rgba(255, 122, 26, 0.12);
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
  overflow: hidden;
}

.vendor-badge img,
.vendor-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.badge span,
.vendor-avatar span {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
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

.status.active {
  background: #ecfdf3;
  color: #047857;
}

.status.inactive {
  background: #fef2f2;
  color: #b91c1c;
}

.row-actions {
  display: grid;
  gap: 4px;
  justify-self: end;
  align-items: center;
  min-width: 0;
  text-align: right;
}

.queue-stat {
  font-size: 13px;
  font-weight: 700;
  color: #18263d;
}

.queue-stat.muted {
  font-size: 12px;
  font-weight: 500;
  color: var(--muted);
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

.detail-card,
.empty-detail-card {
  background: var(--surface-strong);
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
  overflow: hidden;
  flex-shrink: 0;
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

.event-details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.event-details-grid div {
  padding: 12px;
  border-radius: 14px;
  background: rgba(248, 250, 252, 0.9);
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.event-details-grid span {
  display: block;
  font-size: 12px;
  color: var(--muted);
}

.event-details-grid strong {
  display: block;
  margin-top: 6px;
  font-size: 14px;
  color: #16253b;
}

.event-detail-copy {
  margin: 0 0 16px;
  color: #5b6c84;
  line-height: 1.6;
}

.empty-detail-card p {
  margin: 0;
  color: var(--muted);
  line-height: 1.6;
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

  .queue-date,
  .status,
  .row-actions {
    grid-column: 1 / -1;
  }

  .row-actions {
    justify-self: start;
    text-align: left;
  }
}

@media (max-width: 1024px) {
  .events-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .nav-item {
    min-width: 220px;
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

  .admin-sidebar {
    padding: 20px 16px;
  }

  .sidebar-block-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .event-details-grid {
    grid-template-columns: 1fr;
  }

  .admin-topbar {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>

