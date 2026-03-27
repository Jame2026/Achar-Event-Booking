<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { eventTypeMap } from "../../features/appData";
import { formatDateTime } from "../../features/bookingMappers";
import { apiDelete, apiGet, apiPatch } from "../../features/apiClient";

const STORE_KEY = "achar_admin_vendor_sidebar_state_v1";

const props = defineProps({
  appLogoSrc: { type: String, default: "" },
  adminDisplayName: { type: String, default: "Admin" },
  logoutUser: { type: Function, default: null },
});

const router = useRouter();
const route = useRoute();

const navItems = [
  { key: "dashboard", label: "Dashboard" },
  { key: "events", label: "Events" },
  { key: "admin-bookings", label: "Bookings" },
  { key: "vendors", label: "Vendors" },
  { key: "users", label: "Users" },
  { key: "revenue", label: "Revenue" },
  { key: "settings", label: "Settings" },
];

const reviewOptions = [
  { value: "approved", label: "Approved" },
  { value: "review", label: "Review" },
  { value: "watch", label: "Watchlist" },
];

const priorityOptions = [
  { value: "low", label: "Low" },
  { value: "normal", label: "Normal" },
  { value: "high", label: "High" },
];

const activeKey = ref("vendors");
const searchQuery = ref("");
const visibilityFilter = ref("all");
const categoryFilter = ref("all");
const isLoading = ref(false);
const isSaving = ref(false);
const notice = ref("");
const noticeTone = ref("info");
const vendorEvents = ref([]);
const selectedVendorKey = ref("");
const adminState = ref(loadState());
const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const currentAdmin = computed(() => {
  try {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
});

const initials = computed(() => {
  const parts = String(currentAdmin.value?.name || props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  return `${parts[0]?.[0] || "A"}${parts[1]?.[0] || ""}`.toUpperCase();
});
const avatarUrl = computed(() => currentAdmin.value?.profile_image_url || "");

const vendorRows = computed(() => {
  const groups = new Map();

  vendorEvents.value.forEach((event) => {
    const vendorId = Number(event.vendor_id || 0) || null;
    const vendorName = String(event.vendor?.name || event.vendor_name || "Vendor").trim() || "Vendor";
    const key = vendorKey(vendorId, vendorName);
    const current = groups.get(key) || {
      key,
      id: vendorId,
      name: vendorName,
      initials: shortName(vendorName),
      location: "",
      categories: new Set(),
      serviceCount: 0,
      activeCount: 0,
      bookingsCount: 0,
      revenue: 0,
      lastActivity: "",
    };

    current.serviceCount += 1;
    current.activeCount += event.is_active ? 1 : 0;
    current.bookingsCount += Number(event.bookings_count || 0);
    current.revenue += Number(event.price || 0);
    current.categories.add(eventTypeMap[event.event_type] || "Other");
    if (!current.location && event.location) current.location = String(event.location).trim();

    const candidate = String(event.updated_at || event.starts_at || event.created_at || "");
    if (stamp(candidate) > stamp(current.lastActivity)) current.lastActivity = candidate;

    groups.set(key, current);
  });

  return Array.from(groups.values()).map((vendor) => {
    const state = getState(vendor.key);
    const inactiveCount = vendor.serviceCount - vendor.activeCount;
    return {
      ...vendor,
      categories: Array.from(vendor.categories),
      inactiveCount,
      visibility: vendor.activeCount === 0 ? "paused" : inactiveCount > 0 ? "mixed" : "live",
      reviewState: state.reviewState,
      priority: state.priority,
      featured: state.featured,
      note: state.note,
      location: vendor.location || "Location not added yet",
      revenueLabel: money(vendor.revenue),
      lastActivityLabel: vendor.lastActivity ? formatDateTime(vendor.lastActivity) : "No activity yet",
    };
  });
});

const categoryOptions = computed(() => {
  const values = new Set();
  vendorRows.value.forEach((vendor) => vendor.categories.forEach((item) => values.add(item)));
  return ["all", ...Array.from(values).sort((a, b) => a.localeCompare(b))];
});

const filteredVendors = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return vendorRows.value.filter((vendor) => {
    if (visibilityFilter.value !== "all" && vendor.visibility !== visibilityFilter.value) return false;
    if (categoryFilter.value !== "all" && !vendor.categories.includes(categoryFilter.value)) return false;
    if (!query) return true;
    return [vendor.name, vendor.location, vendor.categories.join(" "), vendor.note, String(vendor.id || "")]
      .join(" ")
      .toLowerCase()
      .includes(query);
  });
});

const selectedVendor = computed(
  () => filteredVendors.value.find((item) => item.key === selectedVendorKey.value) || filteredVendors.value[0] || null,
);

const selectedState = computed(() => (selectedVendor.value ? getState(selectedVendor.value.key) : defaultState()));

const selectedServices = computed(() => {
  if (!selectedVendor.value) return [];

  return vendorEvents.value
    .filter((event) => vendorKey(Number(event.vendor_id || 0) || null, String(event.vendor?.name || event.vendor_name || "Vendor")) === selectedVendor.value.key)
    .sort((a, b) => stamp(b.updated_at || b.starts_at) - stamp(a.updated_at || a.starts_at))
    .map((event) => ({
      ...event,
      typeLabel: eventTypeMap[event.event_type] || "Other",
      dateLabel: formatDateTime(event.starts_at),
    }));
});

const highlightCards = computed(() => [
  { label: "Total Vendors", value: count(vendorRows.value.length), note: `${count(filteredVendors.value.length)} visible now`, icon: "vendors" },
  { label: "Needs Review", value: count(vendorRows.value.filter((item) => item.reviewState !== "approved").length), note: "Admin follow-up", icon: "review" },
  { label: "Live Services", value: count(vendorRows.value.reduce((sum, item) => sum + item.activeCount, 0)), note: "Publicly visible", icon: "live" },
  { label: "Bookings", value: count(vendorRows.value.reduce((sum, item) => sum + item.bookingsCount, 0)), note: "Vendor total", icon: "bookings" },
]);

function defaultState() {
  return { reviewState: "approved", priority: "normal", featured: false, note: "" };
}

function normalizeState(value) {
  const reviewState = String(value?.reviewState || "approved").toLowerCase();
  const priority = String(value?.priority || "normal").toLowerCase();
  return {
    reviewState: ["approved", "review", "watch"].includes(reviewState) ? reviewState : "approved",
    priority: ["low", "normal", "high"].includes(priority) ? priority : "normal",
    featured: Boolean(value?.featured),
    note: typeof value?.note === "string" ? value.note : "",
  };
}

function loadState() {
  try {
    const raw = localStorage.getItem(STORE_KEY);
    const parsed = raw ? JSON.parse(raw) : {};
    return parsed && typeof parsed === "object" && !Array.isArray(parsed) ? parsed : {};
  } catch {
    return {};
  }
}

function saveState() {
  localStorage.setItem(STORE_KEY, JSON.stringify(adminState.value));
}

function getState(key) {
  return normalizeState(adminState.value[key]);
}

function updateState(patch) {
  if (!selectedVendor.value) return;
  adminState.value = {
    ...adminState.value,
    [selectedVendor.value.key]: { ...getState(selectedVendor.value.key), ...patch },
  };
  saveState();
}

function vendorKey(id, name) {
  return id ? `vendor:${id}` : `vendor-name:${String(name || "vendor").trim().toLowerCase()}`;
}

function shortName(value) {
  return String(value || "Vendor").split(" ").filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function stamp(value) {
  const parsed = new Date(value);
  return Number.isNaN(parsed.getTime()) ? 0 : parsed.getTime();
}

function money(value) {
  return `$${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
}

function count(value) {
  return Number(value || 0).toLocaleString();
}

function vendorLiveRate(vendor) {
  const total = Number(vendor?.serviceCount || 0);
  if (!total) return 0;
  return Math.round((Number(vendor?.activeCount || 0) / total) * 100);
}

function reviewLabel(value) {
  if (value === "review") return "Needs Review";
  if (value === "watch") return "Watchlist";
  return "Approved";
}

function visibilityLabel(value) {
  if (value === "paused") return "Paused";
  if (value === "mixed") return "Mixed";
  return "Live";
}

function setNotice(message, tone = "info") {
  notice.value = String(message || "").trim();
  noticeTone.value = tone;
}

function syncActiveKey() {
  const page = Array.isArray(route.query.page) ? route.query.page[0] : route.query.page;
  activeKey.value = page || "vendors";
}

function navigateTo(key) {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
}

function patchLocalEvent(updated) {
  const index = vendorEvents.value.findIndex((item) => Number(item.id) === Number(updated.id));
  if (index < 0) return;
  const existing = vendorEvents.value[index] || {};
  vendorEvents.value.splice(index, 1, {
    ...existing,
    ...updated,
    vendor: updated?.vendor || existing?.vendor,
    vendor_name: updated?.vendor_name || existing?.vendor_name,
    bookings_count: updated?.bookings_count ?? existing?.bookings_count ?? 0,
  });
}

async function loadVendorDirectory() {
  isLoading.value = true;
  try {
    const result = await apiGet("events", { per_page: 100, include_inactive: 1, ts: Date.now() });
    vendorEvents.value = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    notice.value = vendorEvents.value.length ? "" : "No vendor services found yet.";
  } catch (error) {
    vendorEvents.value = [];
    setNotice(error?.message || "Could not load vendor directory.", "error");
  } finally {
    isLoading.value = false;
  }
}

async function setVendorVisibility(nextActive) {
  if (!selectedVendor.value?.id) return setNotice("This vendor does not have a connected vendor account ID.", "error");
  const services = selectedServices.value.filter((item) => Boolean(item.is_active) !== nextActive);
  if (!services.length) return setNotice(nextActive ? "All services are already live." : "All services are already paused.");

  isSaving.value = true;
  try {
    for (const service of services) {
      const updated = await apiPatch(`vendor/services/${service.id}`, {
        vendor_user_id: selectedVendor.value.id,
        is_active: nextActive,
      });
      patchLocalEvent(updated);
    }
    setNotice(nextActive ? "Vendor is live again across all services." : "Vendor has been paused across all services.", "success");
  } catch (error) {
    setNotice(error?.message || "Could not update vendor visibility.", "error");
  } finally {
    isSaving.value = false;
  }
}

async function setServiceVisibility(service, nextActive) {
  if (!selectedVendor.value?.id) return setNotice("This vendor does not have a connected vendor account ID.", "error");
  isSaving.value = true;
  try {
    const updated = await apiPatch(`vendor/services/${service.id}`, {
      vendor_user_id: selectedVendor.value.id,
      is_active: nextActive,
    });
    patchLocalEvent(updated);
    setNotice(nextActive ? `${service.title} is live again.` : `${service.title} has been hidden.`, "success");
  } catch (error) {
    setNotice(error?.message || "Could not update this service.", "error");
  } finally {
    isSaving.value = false;
  }
}

async function removeService(service) {
  if (!selectedVendor.value?.id) return setNotice("This vendor does not have a connected vendor account ID.", "error");
  if (!window.confirm(`Remove "${service.title}" from this vendor?`)) return;
  isSaving.value = true;
  try {
    await apiDelete(`vendor/services/${service.id}`, { vendor_user_id: selectedVendor.value.id });
    vendorEvents.value = vendorEvents.value.filter((item) => Number(item.id) !== Number(service.id));
    setNotice(`${service.title} was removed.`, "success");
  } catch (error) {
    setNotice(error?.message || "Could not remove this service.", "error");
  } finally {
    isSaving.value = false;
  }
}

watch(() => route.query.page, syncActiveKey);
watch(
  filteredVendors,
  (rows) => {
    if (!rows.length) return (selectedVendorKey.value = "");
    if (!rows.some((item) => item.key === selectedVendorKey.value)) selectedVendorKey.value = rows[0].key;
  },
  { immediate: true },
);

syncActiveKey();
onMounted(() => void loadVendorDirectory());
</script>

<template>
  <section class="vendors-shell">
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
        <button v-for="item in navItems" :key="item.key" type="button" class="nav-item" :class="{ active: activeKey === item.key }" @click="navigateTo(item.key)">
          <span class="nav-icon">{{ item.label.slice(0, 1) }}</span>
          <span>{{ item.label }}</span>
        </button>
        <RouterLink class="nav-item home-link" to="/">
          <span class="nav-icon">H</span>
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
        <button v-if="logoutUser" class="logout-btn" type="button" @click="logoutUser">Log out</button>
      </div>
    </aside>

    <main class="admin-main">
      <header class="topbar">
        <label class="search">
          <span class="search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z" />
            </svg>
          </span>
          <input v-model="searchQuery" class="search-input" type="search" placeholder="Search vendors, notes, categories, or locations..." />
        </label>
        <div class="topbar-actions">
          <div class="topbar-badge">
            <span>Visible Vendors</span>
            <strong>{{ count(filteredVendors.length) }}</strong>
          </div>
          <!-- <button class="ghost-btn" type="button" :disabled="isLoading" @click="loadVendorDirectory">{{ isLoading ? "Refreshing..." : "Refresh" }}</button> -->
          <div class="topbar-avatar" :class="{ 'has-image': avatarUrl }">
            <img v-if="avatarUrl" :src="avatarUrl" alt="Profile" />
            <span v-else>{{ initials }}</span>
          </div>
        </div>
      </header>

      <section class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Vendor Sidebar Controls</p>
          <h1>Control Vendors From Here</h1>
          <p>Select a vendor to pause services, restore listings, and keep review notes in the sidebar.</p>
          <div class="hero-meta">
            <span class="hero-pill">{{ count(vendorRows.length) }} total vendors</span>
            <span class="hero-pill soft">{{ count(vendorRows.reduce((sum, item) => sum + item.activeCount, 0)) }} live services</span>
          </div>
        </div>
        <div class="hero-actions">
          <div v-if="selectedVendor" class="hero-selected">
            <span class="hero-selected-label">Selected Vendor</span>
            <strong>{{ selectedVendor.name }}</strong>
            <small>{{ reviewLabel(selectedVendor.reviewState) }} · {{ selectedVendor.lastActivityLabel }}</small>
          </div>
          <button class="primary-btn" type="button" :disabled="!selectedVendor || isSaving" @click="setVendorVisibility(selectedVendor?.visibility === 'paused')">
            {{ selectedVendor?.visibility === "paused" ? "Go Live Again" : "Pause Vendor" }}
          </button>
        </div>
      </section>

      <p v-if="notice" class="notice" :class="noticeTone">{{ notice }}</p>

      <section class="highlights">
        <article v-for="card in highlightCards" :key="card.label" class="highlight-card">
          <div class="highlight-icon" :class="card.icon">
            <svg v-if="card.icon === 'vendors'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 10h16l-1.5 9a2 2 0 0 1-2 1H7.5a2 2 0 0 1-2-1Z" />
              <path d="M8 4h8l1 4H7Z" />
            </svg>
            <svg v-else-if="card.icon === 'review'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M5 4h14v14H7l-2 2V4Zm2 2v10h10V6H7Zm2 2h6v2H9V8Zm0 4h6v2H9v-2Z" />
            </svg>
            <svg v-else-if="card.icon === 'live'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 7a5 5 0 1 1-3.536 1.464A5 5 0 0 1 12 7Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
              <path d="M4.222 4.222 5.636 5.636 4.5 6.772A9 9 0 0 0 12 21v-2a7 7 0 0 1-7-7c0-1.81.688-3.461 1.818-4.707L4.222 4.222ZM19.778 4.222l-1.414 1.414A7 7 0 0 1 19 12a7 7 0 0 1-7 7v2a9 9 0 0 0 9-9c0-2.308-.88-4.413-2.308-6.036l1.086-1.086Z" />
            </svg>
            <svg v-else-if="card.icon === 'bookings'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 4h12a2 2 0 0 1 2 2v12l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2Z" />
            </svg>
          </div>
          <span class="highlight-label">{{ card.label }}</span>
          <strong>{{ card.value }}</strong>
          <div class="highlight-footer">
            <span class="highlight-note">{{ card.note }}</span>
            <span class="highlight-chip">{{ card.delta || 'Updated' }}</span>
          </div>
        </article>
      </section>

      <section class="content-grid">
        <article class="card directory-card">
          <header class="card-head">
            <div>
              <p class="card-eyebrow">Vendor Directory</p>
              <h3>Review Queue</h3>
            </div>
            <span class="card-meta">{{ count(filteredVendors.length) }} results</span>
          </header>
          <div class="filters">
            <label class="filter-field">
              <span>Visibility</span>
              <select v-model="visibilityFilter">
                <option value="all">All Visibility</option>
                <option value="live">Live</option>
                <option value="mixed">Mixed</option>
                <option value="paused">Paused</option>
              </select>
            </label>
            <label class="filter-field">
              <span>Category</span>
              <select v-model="categoryFilter">
                <option value="all">All Categories</option>
                <option v-for="item in categoryOptions.filter((value) => value !== 'all')" :key="item" :value="item">{{ item }}</option>
              </select>
            </label>
          </div>

          <div v-if="isLoading" class="empty">Loading vendor directory...</div>
          <div v-else-if="!filteredVendors.length" class="empty">No vendors match your filters.</div>
          <div v-else class="vendor-list">
            <button v-for="vendor in filteredVendors" :key="vendor.key" type="button" class="vendor-row" :class="{ selected: selectedVendor?.key === vendor.key }" @click="selectedVendorKey = vendor.key">
              <div class="vendor-main">
                <div class="vendor-photo">{{ vendor.initials }}</div>
                <div class="vendor-copy">
                  <div class="vendor-title-row">
                    <strong>{{ vendor.name }}</strong>
                    <span v-if="vendor.featured" class="chip muted">Featured</span>
                  </div>
                  <p>{{ vendor.location }}</p>
                  <div class="chips">
                    <span class="chip">{{ visibilityLabel(vendor.visibility) }}</span>
                    <span class="chip muted">{{ reviewLabel(vendor.reviewState) }}</span>
                    <span class="chip muted">{{ vendor.priority }} priority</span>
                  </div>
                  <div class="vendor-progress">
                    <div class="vendor-progress-track">
                      <span class="vendor-progress-fill" :style="{ width: `${vendorLiveRate(vendor)}%` }"></span>
                    </div>
                    <small>Last activity: {{ vendor.lastActivityLabel }}</small>
                  </div>
                </div>
              </div>
              <div class="vendor-side">
                <span>{{ vendor.activeCount }}/{{ vendor.serviceCount }} live</span>
                <small>{{ count(vendor.bookingsCount) }} bookings</small>
              </div>
            </button>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedVendor" class="card spotlight-card">
            <div class="sidebar-head">
              <div>
                <p class="card-eyebrow">Vendor Spotlight</p>
                <h3>{{ selectedVendor.name }}</h3>
                <p>{{ selectedVendor.location }}</p>
              </div>
              <span class="chip">{{ visibilityLabel(selectedVendor.visibility) }}</span>
            </div>
            <div class="stats-grid">
              <div><span>Live</span><strong>{{ selectedVendor.activeCount }}</strong></div>
              <div><span>Total</span><strong>{{ selectedVendor.serviceCount }}</strong></div>
              <div><span>Bookings</span><strong>{{ count(selectedVendor.bookingsCount) }}</strong></div>
              <div><span>Revenue</span><strong>{{ selectedVendor.revenueLabel }}</strong></div>
            </div>
          </article>

          <article v-if="selectedVendor" class="card control-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">Admin Sidebar</p>
                <h3>Review Controls</h3>
              </div>
            </header>
            <div class="stack">
              <div class="control-group">
                <label class="group-label">Review State</label>
                <div class="button-row">
                  <button v-for="item in reviewOptions" :key="item.value" type="button" class="mini-btn" :class="{ active: selectedState.reviewState === item.value }" @click="updateState({ reviewState: item.value })">{{ item.label }}</button>
                </div>
              </div>
              <div class="control-group">
                <label class="group-label">Priority</label>
                <div class="button-row">
                  <button v-for="item in priorityOptions" :key="item.value" type="button" class="mini-btn" :class="{ active: selectedState.priority === item.value }" @click="updateState({ priority: item.value })">{{ item.label }}</button>
                </div>
              </div>
              <label class="toggle">
                <input type="checkbox" :checked="selectedState.featured" @change="updateState({ featured: $event.target.checked })" />
                <span>Feature this vendor in admin view</span>
              </label>
              <label class="notes-area">
                <span class="field-label">Notes</span>
                <textarea rows="4" :value="selectedState.note" placeholder="Write vendor notes here..." @input="updateState({ note: $event.target.value })"></textarea>
              </label>
            </div>
          </article>

          <article v-if="selectedVendor" class="card services-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">Service Access</p>
                <h3>Vendor Services</h3>
              </div>
            </header>
            <div v-if="!selectedServices.length" class="empty small">No services for this vendor yet.</div>
            <div v-else class="service-list">
              <div v-for="service in selectedServices" :key="service.id" class="service-row">
                <div class="service-copy">
                  <div class="service-title-row">
                    <strong>{{ service.title }}</strong>
                    <span class="chip" :class="{ muted: !service.is_active }">{{ service.is_active ? "Live" : "Hidden" }}</span>
                  </div>
                  <p>{{ service.typeLabel }} - {{ service.dateLabel }}</p>
                  <small>{{ service.location || "Location not added yet" }}</small>
                </div>
                <div class="service-actions">
                  <button class="danger-btn small-btn" type="button" :disabled="isSaving" @click="removeService(service)">Remove</button>
                </div>
              </div>
            </div>
          </article>

          <article v-else class="card empty-selection">
            <p class="card-eyebrow">Vendor Spotlight</p>
            <h3>Select a Vendor</h3>
            <p>Choose a vendor from the directory to open the styled sidebar controls and service actions.</p>
          </article>
        </aside>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");
.vendors-shell{--ink:#111827;--muted:#64748b;--accent:#ff7a1a;--accent-strong:#f15b2a;--stroke:rgba(15,23,42,.08);--surface:rgba(255,255,255,.9);--shadow:0 24px 60px rgba(15,23,42,.12);--shadow-soft:0 14px 30px rgba(15,23,42,.08);display:grid;grid-template-columns:minmax(290px,350px) 1fr;min-height:calc(100vh - 90px);font-family:"Space Grotesk","Segoe UI",sans-serif;color:var(--ink);background:radial-gradient(circle at 10% 0%,rgba(255,122,26,.16),transparent 30%),radial-gradient(circle at 92% 8%,rgba(110,165,255,.16),transparent 28%),linear-gradient(180deg,#fff7f1 0%,#f5f7fc 42%,#edf5fa 100%)}
.admin-sidebar{display:flex;flex-direction:column;gap:24px;padding:34px 26px;border-right:1px solid var(--stroke);background:linear-gradient(180deg,rgba(255,255,255,.9),rgba(245,247,252,.96));backdrop-filter:blur(18px)}
.brand,.topbar,.topbar-actions,.hero,.hero-actions,.vendor-main,.vendor-title-row,.service-title-row,.nav-item,.sidebar-head,.card-head,.actions,.button-row,.service-actions,.chips,.toggle{display:flex;align-items:center}
.brand{gap:12px}.brand-logo,.avatar,.topbar-avatar,.vendor-photo,.nav-icon{display:grid;place-items:center}.brand-logo{width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,#fff1e4,#ffe2cb);box-shadow:0 16px 32px rgba(255,122,26,.2);border:1px solid rgba(255,122,26,.18)}.brand-logo img{width:28px;height:28px;object-fit:contain}
.brand-mark,.avatar,.topbar-avatar,.vendor-photo,.nav-icon{font-weight:700}.brand-title,h1,h3{margin:0;font-family:"Fraunces",serif}
.brand-subtitle,.user-role,.hero-copy p,.vendor-copy p,.vendor-progress small,.service-copy p,.service-copy small,.sidebar-head p,.highlight-label,.highlight-note,.field-label,.group-label,.card-meta,.card-eyebrow,.topbar-badge span,.vendor-side small{color:var(--muted)}
.admin-nav{display:grid;gap:10px;padding:14px;background:rgba(255,255,255,.72);border:1px solid rgba(15,23,42,.06);border-radius:24px;box-shadow:var(--shadow-soft)}
.nav-item{gap:12px;padding:14px 16px;border:1px solid transparent;border-radius:18px;background:transparent;color:#475569;cursor:pointer;text-decoration:none;transition:transform .2s ease,box-shadow .2s ease,background .2s ease}
.nav-item:hover,.nav-item.active{color:var(--accent);background:linear-gradient(135deg,#fff4ea,#ffe2ce);border-color:rgba(255,122,26,.18);box-shadow:inset 3px 0 0 var(--accent),0 10px 18px rgba(255,122,26,.14);transform:translateX(2px)}
.nav-icon{width:38px;height:38px;border-radius:12px;background:radial-gradient(circle at 30% 20%,#f8fafc,#eef2f7 70%);color:#94a3b8;border:1px solid rgba(148,163,184,.16);display:grid;place-items:center}
.admin-user-card,.card,.highlight-card,.hero{background:var(--surface);border:1px solid var(--stroke);border-radius:22px;box-shadow:var(--shadow)}
.admin-user-card{margin-top:auto;padding:16px;display:grid;gap:8px}.avatar,.vendor-photo{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,#ffe9d6,#ffd2aa);color:#bf5c06}.user-name{margin:0;font-weight:600}
.logout-btn,.ghost-btn,.primary-btn,.danger-btn,.mini-btn,.search-input,select,textarea,.vendor-row{font:inherit}.logout-btn,.ghost-btn,.primary-btn,.danger-btn,.mini-btn{padding:10px 14px;border-radius:12px;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,opacity .2s ease;border:1px solid transparent}.logout-btn{background:#f1f5f9;border:none}
.admin-main{padding:28px 32px 40px;display:grid;gap:20px;overflow:visible}.topbar{justify-content:space-between;gap:14px;background:rgba(255,255,255,.9);border:1px solid rgba(15,23,42,.06);border-radius:18px;padding:12px 14px;box-shadow:var(--shadow-soft)}.topbar-actions{gap:10px;align-items:center;display:flex;padding:6px 8px;border-radius:14px;background:rgba(255,255,255,.85);border:1px solid rgba(15,23,42,.05);box-shadow:inset 0 1px 0 rgba(255,255,255,.6)}.topbar-badge{display:grid;gap:2px;padding:10px 14px;border-radius:16px;background:linear-gradient(180deg,#fff,#f6f7fb);border:1px solid rgba(15,23,42,.07);box-shadow:var(--shadow-soft)}.topbar-badge strong{font-size:16px}
.search{display:flex;align-items:center;gap:10px;flex:1;min-height:54px;padding:0 18px;border:1px solid rgba(15,23,42,.08);border-radius:18px;background:linear-gradient(180deg,#fff,#f8fafc);box-shadow:var(--shadow-soft)}.search-icon svg{width:18px;height:18px;fill:#94a3b8}.search-input{width:100%;border:none;background:transparent;outline:none;color:var(--ink)}
.topbar-avatar{width:40px;height:40px;border-radius:14px;color:#fff;background:linear-gradient(135deg,#ffb98b,#ff8b3d);box-shadow:0 10px 18px rgba(241,91,42,.22);overflow:hidden;display:grid;place-items:center}
.topbar-avatar.has-image{background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 8px 14px rgba(15,23,42,.08)}
.topbar-avatar img{width:100%;height:100%;object-fit:cover;display:block}
.avatar{overflow:hidden}
.avatar.has-image{background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 8px 14px rgba(15,23,42,.08)}
.avatar img{width:100%;height:100%;object-fit:cover;display:block}
.hero{justify-content:space-between;gap:22px;padding:26px 28px;position:relative;overflow:hidden}.hero::after{content:"";position:absolute;inset:0;background:radial-gradient(circle at 14% 12%,rgba(255,122,26,.12),transparent 28%),radial-gradient(circle at 88% 18%,rgba(90,146,240,.14),transparent 30%);pointer-events:none}.hero>*{position:relative;z-index:1}.hero-copy{max-width:620px}.eyebrow,.card-eyebrow{margin:0 0 8px;text-transform:uppercase;letter-spacing:.12em;font-size:12px;color:#c45a12}.hero h1{font-size:38px;line-height:1.05}.hero-meta{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}.hero-pill{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;font-size:12px;font-weight:600;background:rgba(255,122,26,.14);color:#b45309}.hero-pill.soft{background:rgba(15,23,42,.06);color:#475569}.hero-actions{gap:14px;justify-content:flex-end}.hero-selected{display:grid;gap:3px;min-width:220px;padding:14px 16px;border-radius:18px;background:rgba(255,255,255,.78);border:1px solid rgba(15,23,42,.07);box-shadow:var(--shadow-soft)}.hero-selected-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted)}.hero-selected small{color:var(--muted)}
.notice{margin:0;padding:12px 14px;border-radius:16px;border:1px solid transparent}.notice.success{background:rgba(31,157,108,.12);color:#0f7a51;border-color:rgba(31,157,108,.18)}.notice.error{background:rgba(220,38,38,.1);color:#b42318;border-color:rgba(220,38,38,.16)}.notice.info{background:rgba(15,23,42,.05);color:#334155;border-color:rgba(15,23,42,.08)}
.highlights,.content-grid,.stats-grid{display:grid;gap:14px}.highlights{grid-template-columns:repeat(auto-fit,minmax(180px,1fr))}.highlight-card{display:grid;gap:8px;padding:16px;position:relative;overflow:hidden}.highlight-icon{width:36px;height:36px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(180deg,#f8fafc,#eef2f7);border:1px solid rgba(148,163,184,.18);color:#f15b2a}.highlight-icon svg{width:18px;height:18px;fill:currentColor}.highlight-card strong{font-size:26px}.highlight-footer{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-top:2px}.highlight-chip{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;background:#fff3e6;color:#c65300;font-size:11px;font-weight:700;border:1px solid rgba(255,122,26,.2)}.content-grid{grid-template-columns:minmax(0,1.45fr) minmax(340px,1fr);align-items:start}.card{padding:20px}.card-head{justify-content:space-between;gap:12px;margin-bottom:16px}.card-meta{display:inline-flex;align-items:center;padding:8px 12px;border-radius:999px;background:rgba(15,23,42,.05)}
.filters,.button-row,.chips,.actions,.service-actions{display:flex;gap:10px;flex-wrap:wrap}.filters{margin-bottom:16px}.filter-field,.control-group,.notes-area,.vendor-copy,.vendor-progress,.service-copy{display:grid;gap:8px}.filter-field{min-width:170px}.filter-field span,.group-label,.field-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:600}
select,textarea{width:100%;border:1px solid rgba(15,23,42,.08);background:#fff;border-radius:14px;padding:11px 12px;outline:none;transition:border-color .2s ease,box-shadow .2s ease}select:focus,textarea:focus{border-color:rgba(255,122,26,.24);box-shadow:0 0 0 4px rgba(255,122,26,.08)}
.vendor-list,.side-column,.stack,.service-list{display:grid;gap:12px}.vendor-row{width:100%;display:flex;justify-content:space-between;gap:16px;padding:16px;border:1px solid rgba(15,23,42,.06);background:rgba(255,255,255,.92);border-radius:18px;text-align:left;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}.vendor-row:hover{transform:translateY(-2px);border-color:rgba(255,122,26,.18);box-shadow:0 16px 26px rgba(15,23,42,.08)}.vendor-row.selected{border-color:rgba(255,122,26,.24);background:linear-gradient(135deg,rgba(255,247,240,.96),rgba(255,255,255,.98));box-shadow:0 18px 30px rgba(255,122,26,.12)}.vendor-main{align-items:flex-start;gap:12px}.vendor-title-row,.service-title-row{gap:8px;flex-wrap:wrap}.vendor-copy strong,.service-copy strong{display:block}.vendor-copy p,.service-copy p{margin:0;font-size:13px}
.vendor-progress-track{width:100%;max-width:260px;height:8px;border-radius:999px;background:#eef2f7;overflow:hidden}.vendor-progress-fill{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#ff7a1a,#f15b2a)}.vendor-side{display:grid;justify-items:end;gap:4px;min-width:100px;font-weight:600}
.chip{display:inline-flex;align-items:center;padding:4px 9px;border-radius:999px;background:#fff3e6;color:#f15b2a;font-size:11px;font-weight:600}.chip.muted{background:#f8fafc;color:#475569}
.sidebar-head{justify-content:space-between;gap:12px;margin-bottom:16px}.sidebar-head p{margin:4px 0 0}.spotlight-card{background:radial-gradient(circle at 100% 0%,rgba(255,122,26,.12),transparent 28%),rgba(255,255,255,.92)}.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.stats-grid div{padding:14px;border-radius:16px;background:linear-gradient(180deg,#fff,#f8fafc);border:1px solid rgba(15,23,42,.05)}.stats-grid span{display:block;font-size:12px;color:var(--muted)}.stats-grid strong{display:block;margin-top:6px;font-size:20px}
.primary-btn{color:#fff;background:linear-gradient(135deg,#ff7a1a,#f15b2a);box-shadow:0 14px 26px rgba(241,91,42,.24)}.ghost-btn,.mini-btn{color:#c65300;background:rgba(255,255,255,.92);border-color:rgba(255,122,26,.24)}.danger-btn{color:#dc2626;background:rgba(220,38,38,.08);border-color:rgba(220,38,38,.18)}.mini-btn.active{color:#f15b2a;background:#fff4ea;box-shadow:var(--shadow-soft)}.toggle{gap:10px}.toggle input{accent-color:#ff7a1a}
.service-row{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:start;padding:16px;border:1px solid rgba(15,23,42,.06);border-radius:16px;background:linear-gradient(180deg,#fff,#fcfdff)}.service-actions{justify-content:flex-end;gap:8px;flex-wrap:wrap}.small-btn{padding:6px 10px;font-size:12px;border-radius:10px;min-width:0}.empty,.empty-selection{min-height:180px;display:grid;place-items:center;text-align:center;color:var(--muted)}.empty-selection{gap:10px}.small{min-height:90px}textarea{resize:vertical;min-height:120px}
button:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}
@media (max-width:1180px){.vendors-shell,.content-grid{grid-template-columns:1fr}}
@media (max-width:840px){.topbar,.hero,.vendor-row,.sidebar-head,.hero-actions{flex-direction:column;align-items:stretch}.vendor-side{justify-items:start}}
@media (max-width:720px){.admin-main{padding:20px}.admin-sidebar{padding:24px 18px}.stats-grid{grid-template-columns:1fr}.hero h1{font-size:30px}}

/* Enhancements */
.search:focus-within{border-color:rgba(255,122,26,.28);box-shadow:0 18px 32px rgba(15,23,42,.12)}
.primary-btn:hover{transform:translateY(-1px);box-shadow:0 18px 32px rgba(241,91,42,.28)}
.ghost-btn:hover,.mini-btn:hover{background:#fff7f1;box-shadow:0 10px 18px rgba(15,23,42,.08)}
.danger-btn:hover{background:rgba(220,38,38,.12);box-shadow:0 10px 18px rgba(220,38,38,.16)}
.vendor-row:hover{border-color:rgba(255,122,26,.22);box-shadow:0 18px 32px rgba(15,23,42,.12)}
.chip{border:1px solid rgba(255,122,26,.14)}
.chip.muted{border-color:rgba(148,163,184,.22)}
</style>
