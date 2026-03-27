<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { eventTypeMap } from "../../features/appData";
import { formatDateTime } from "../../features/bookingMappers";
import { apiGet, apiPatch } from "../../features/apiClient";
import { useAdminDataStore } from "../../features/useAdminDataStore";

const props = defineProps({
  appLogoSrc: { type: String, default: "" },
  adminDisplayName: { type: String, default: "Admin" },
  logoutUser: { type: Function, default: null },
});

const router = useRouter();
const route = useRoute();

const navItems = [
  { key: "dashboard", label: "Dashboard", icon: "dashboard" },
  { key: "events", label: "Events", icon: "events" },
  { key: "admin-bookings", label: "Bookings", icon: "bookings" },
  { key: "vendors", label: "Vendors", icon: "vendors" },
  { key: "customers", label: "Customers", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];

const activeKey = ref("vendors");
const searchQuery = ref("");
const visibilityFilter = ref("all");
const categoryFilter = ref("all");
const adminStore = useAdminDataStore();
const isLoading = ref(false);
const isSaving = ref(false);
const notice = ref("");
const noticeTone = ref("info");
const vendorUsers = ref([]);
const vendorEvents = ref([]);
const selectedVendorKey = ref("");
const failedVendorImages = ref(new Set());

const initials = computed(() => {
  const parts = String(props.adminDisplayName || "Admin").split(" ").filter(Boolean);
  return `${parts[0]?.[0] || "A"}${parts[1]?.[0] || ""}`.toUpperCase();
});
function vendorKey(id, name) {
  return id ? `vendor:${id}` : `vendor-name:${String(name || "vendor").trim().toLowerCase()}`;
}

function shortName(value) {
  return String(value || "Vendor")
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();
}

function stamp(value) {
  const parsed = new Date(value || "");
  return Number.isNaN(parsed.getTime()) ? 0 : parsed.getTime();
}

function money(value) {
  return `$${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
}

function count(value) {
  return Number(value || 0).toLocaleString();
}

function listingKind(value) {
  const packages = Array.isArray(value?.packages) ? value.packages.filter(Boolean) : [];
  const mode = String(value?.service_mode || "").trim().toLowerCase();
  return mode === "package" || packages.length ? "Package" : "Service";
}

function visibilityLabel(value) {
  if (value === "paused") return "Paused";
  if (value === "mixed") return "Mixed";
  if (value === "empty") return "No Listings";
  return "Live";
}

function setNotice(message, tone = "info") {
  notice.value = String(message || "").trim();
  noticeTone.value = tone;
}

const vendorRows = computed(() => {
  const groups = new Map();

  vendorUsers.value.forEach((vendor) => {
    const vendorId = Number(vendor?.id || 0) || null;
    const vendorName = String(vendor?.name || "Vendor").trim() || "Vendor";
    const key = vendorKey(vendorId, vendorName);

    groups.set(key, {
      key,
      id: vendorId,
      name: vendorName,
      initials: shortName(vendorName),
      email: String(vendor?.email || "").trim(),
      phone: String(vendor?.phone || "").trim(),
      location: String(vendor?.location || "").trim(),
      profileImageUrl: String(vendor?.profile_image_url || "").trim(),
      vendorImageKey: vendorId ? `vendor:${vendorId}` : `vendor:${vendorName.toLowerCase()}`,
      categories: new Set(),
      serviceCount: 0,
      activeCount: 0,
      bookingsCount: 0,
      packageCount: 0,
      lastActivity: "",
      joinedAt: vendor?.created_at || "",
      listings: [],
    });
  });

  vendorEvents.value.forEach((event) => {
    const vendorId = Number(event?.vendor_id || 0) || null;
    const vendorName = String(event?.vendor?.name || event?.vendor_name || "Vendor").trim() || "Vendor";
    const key = vendorKey(vendorId, vendorName);
    const current = groups.get(key) || {
      key,
      id: vendorId,
      name: vendorName,
      initials: shortName(vendorName),
      email: "",
      phone: "",
      location: "",
      profileImageUrl: String(event?.vendor?.profile_image_url || "").trim(),
      vendorImageKey: vendorId ? `vendor:${vendorId}` : `vendor:${vendorName.toLowerCase()}`,
      categories: new Set(),
      serviceCount: 0,
      activeCount: 0,
      bookingsCount: 0,
      packageCount: 0,
      lastActivity: "",
      joinedAt: "",
      listings: [],
    };

    const typeLabel = eventTypeMap[event?.event_type] || "Other";
    const kindLabel = listingKind(event);
    const packagesCount = Array.isArray(event?.packages) ? event.packages.filter(Boolean).length : 0;

    current.serviceCount += 1;
    current.activeCount += event?.is_active ? 1 : 0;
    current.bookingsCount += Number(event?.bookings_count || 0);
    current.packageCount += kindLabel === "Package" ? 1 : 0;
    current.categories.add(typeLabel);
    if (!current.location && event?.location) current.location = String(event.location).trim();
    if (!current.profileImageUrl && event?.vendor?.profile_image_url) current.profileImageUrl = String(event.vendor.profile_image_url).trim();

    const candidate = String(event?.updated_at || event?.starts_at || event?.created_at || "");
    if (stamp(candidate) > stamp(current.lastActivity)) current.lastActivity = candidate;

    current.listings.push({
      ...event,
      typeLabel,
      kindLabel,
      packagesCount,
      dateLabel: event?.starts_at ? formatDateTime(event.starts_at) : "Date TBD",
      priceLabel: money(event?.price || 0),
      lastUpdateLabel: formatDateTime(event?.updated_at || event?.created_at || event?.starts_at),
    });

    groups.set(key, current);
  });

  return Array.from(groups.values())
    .map((vendor) => {
      const inactiveCount = Math.max(0, vendor.serviceCount - vendor.activeCount);

      return {
        ...vendor,
        categories: Array.from(vendor.categories).sort((left, right) => left.localeCompare(right)),
        listings: [...vendor.listings].sort((left, right) => stamp(right.updated_at || right.starts_at) - stamp(left.updated_at || left.starts_at)),
        inactiveCount,
        serviceOnlyCount: Math.max(0, vendor.serviceCount - vendor.packageCount),
        visibility: vendor.serviceCount === 0 ? "empty" : vendor.activeCount === 0 ? "paused" : inactiveCount > 0 ? "mixed" : "live",
        location: vendor.location || "Location not added yet",
        joinedLabel: vendor.joinedAt ? formatDateTime(vendor.joinedAt) : "Join date unavailable",
        lastActivityLabel: vendor.lastActivity ? formatDateTime(vendor.lastActivity) : "No listing activity yet",
      };
    })
    .sort((left, right) => {
      if (right.serviceCount !== left.serviceCount) return right.serviceCount - left.serviceCount;
      return left.name.localeCompare(right.name);
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

    return [
      vendor.name,
      vendor.email,
      vendor.phone,
      vendor.location,
      vendor.categories.join(" "),
      vendor.listings.map((item) => item.title).join(" "),
      String(vendor.id || ""),
    ]
      .join(" ")
      .toLowerCase()
      .includes(query);
  });
});

const selectedVendor = computed(
  () => filteredVendors.value.find((item) => item.key === selectedVendorKey.value) || filteredVendors.value[0] || null,
);

const selectedServices = computed(() => selectedVendor.value?.listings || []);

const highlightCards = computed(() => [
  { label: "Total Vendors", value: count(vendorRows.value.length), note: `${count(filteredVendors.value.length)} shown here` },
  { label: "Live Vendors", value: count(vendorRows.value.filter((item) => ["live", "mixed"].includes(item.visibility)).length), note: "With visible listings" },
  { label: "Listings In System", value: count(vendorRows.value.reduce((sum, item) => sum + item.serviceCount, 0)), note: `${count(vendorRows.value.reduce((sum, item) => sum + item.packageCount, 0))} package listings` },
  { label: "Bookings", value: count(vendorRows.value.reduce((sum, item) => sum + item.bookingsCount, 0)), note: "Across vendor listings" },
]);
const hasVendorProfileImage = (vendor) =>
  Boolean(String(vendor?.profileImageUrl || "").trim())
  && !failedVendorImages.value.has(vendor?.vendorImageKey || vendor?.key);

function handleVendorImageError(imageKey) {
  if (!imageKey || failedVendorImages.value.has(imageKey)) return;
  const next = new Set(failedVendorImages.value);
  next.add(imageKey);
  failedVendorImages.value = next;
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
  const index = vendorEvents.value.findIndex((item) => Number(item?.id) === Number(updated?.id));
  if (index >= 0) {
    const existing = vendorEvents.value[index] || {};
    vendorEvents.value.splice(index, 1, {
      ...existing,
      ...updated,
      vendor: updated?.vendor || existing?.vendor,
      vendor_name: updated?.vendor_name || existing?.vendor_name,
      bookings_count: updated?.bookings_count ?? existing?.bookings_count ?? 0,
    });
  }
  adminStore.updateEvent(updated);
}

async function loadVendorDirectory() {
  isLoading.value = true;
  notice.value = "";

  try {
    const [vendorResult, eventResult] = await Promise.all([
      apiGet("vendors", { per_page: 100, ts: Date.now() }),
      apiGet("events", { per_page: 100, include_inactive: 1, ts: Date.now() }),
    ]);

    vendorUsers.value = Array.isArray(vendorResult?.data) ? vendorResult.data : Array.isArray(vendorResult) ? vendorResult : [];
    vendorEvents.value = Array.isArray(eventResult?.data) ? eventResult.data : Array.isArray(eventResult) ? eventResult : [];
    failedVendorImages.value = new Set();
    if (!vendorUsers.value.length) notice.value = "No vendor accounts found yet.";
  } catch (error) {
    vendorUsers.value = [];
    vendorEvents.value = [];
    setNotice(error?.message || "Could not load vendor directory.", "error");
  } finally {
    isLoading.value = false;
  }
}

async function setVendorVisibility(nextActive) {
  if (!selectedVendor.value?.id) return setNotice("This vendor does not have a connected vendor account ID.", "error");
  const services = selectedServices.value.filter((item) => Boolean(item.is_active) !== nextActive);
  if (!services.length) return setNotice(nextActive ? "All listings are already live." : "All listings are already paused.");

  isSaving.value = true;
  try {
    for (const service of services) {
      const updated = await apiPatch(`vendor/services/${service.id}`, {
        vendor_user_id: selectedVendor.value.id,
        is_active: nextActive,
      });
      patchLocalEvent(updated);
    }
    setNotice(nextActive ? "Vendor listings are live again." : "Vendor listings were paused.", "success");
  } catch (error) {
    setNotice(error?.message || "Could not update vendor visibility.", "error");
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
      <div class="brand-card">
        <div class="brand">
          <div class="brand-logo">
            <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
            <div v-else class="brand-mark">A</div>
          </div>
          <div>
            <p class="brand-kicker">Operations Console</p>
            <p class="brand-title">Achar Admin</p>
            <p class="brand-subtitle">Vendor directory workspace</p>
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
            <span>{{ item.label }}</span>
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
          <input v-model="searchQuery" class="search-input" type="search" placeholder="Search vendor names, listings, location, or contact..." />
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

      <section class="vendors-hero">
        <div class="hero-copy">
          <p class="eyebrow">Vendor Directory</p>
          <h1>All Vendors and Their Listings</h1>
          <p>Select a vendor to inspect profile information and every service or package they currently have in the system.</p>
          <div class="hero-meta">
            <span class="hero-pill">{{ count(vendorRows.length) }} total vendors</span>
            <span class="hero-pill soft">{{ count(vendorRows.reduce((sum, item) => sum + item.serviceCount, 0)) }} total listings</span>
          </div>
        </div>
        <div class="hero-aside">
          <div v-if="selectedVendor" class="hero-selected">
            <span class="hero-selected-label">Selected Vendor</span>
            <strong>{{ selectedVendor.name }}</strong>
            <small>{{ count(selectedVendor.serviceCount) }} listing(s) - {{ selectedVendor.joinedLabel }}</small>
          </div>
          <button class="primary-btn" type="button" :disabled="!selectedVendor || !selectedServices.length || isSaving" @click="setVendorVisibility(selectedVendor?.visibility === 'paused')">
            {{ selectedVendor?.visibility === "paused" ? "Go Live Again" : "Pause Vendor" }}
          </button>
        </div>
      </section>

      <p v-if="notice" class="notice" :class="noticeTone">{{ notice }}</p>

      <section class="highlights">
        <article v-for="card in highlightCards" :key="card.label" class="highlight-card">
          <span class="highlight-label">{{ card.label }}</span>
          <strong>{{ card.value }}</strong>
          <small class="highlight-note">{{ card.note }}</small>
        </article>
      </section>

      <section class="content-grid">
        <article class="card directory-card">
          <header class="card-head">
            <div>
              <p class="card-eyebrow">Vendor Directory</p>
              <h3>All Vendors</h3>
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
                <div class="vendor-photo">
                  <img
                    v-if="hasVendorProfileImage(vendor)"
                    :src="vendor.profileImageUrl"
                    :alt="`${vendor.name} profile`"
                    @error="handleVendorImageError(vendor.vendorImageKey)"
                  />
                  <span v-else>{{ vendor.initials }}</span>
                </div>
                <div class="vendor-copy">
                  <div class="vendor-title-row">
                    <strong>{{ vendor.name }}</strong>
                  </div>
                  <p>{{ vendor.location }}</p>
                  <div class="chips">
                    <span class="chip">{{ visibilityLabel(vendor.visibility) }}</span>
                    <span class="chip muted">{{ count(vendor.serviceCount) }} listing(s)</span>
                    <span class="chip muted">{{ count(vendor.packageCount) }} package</span>
                  </div>
                </div>
              </div>
              <div class="vendor-side">
                <span>{{ count(vendor.bookingsCount) }} bookings</span>
                <small>{{ vendor.lastActivityLabel }}</small>
              </div>
            </button>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedVendor" class="card spotlight-card">
            <div class="sidebar-head">
              <div>
                <p class="card-eyebrow">Vendor Profile</p>
                <h3>{{ selectedVendor.name }}</h3>
                <p>{{ selectedVendor.location }}</p>
              </div>
              <span class="chip">{{ visibilityLabel(selectedVendor.visibility) }}</span>
            </div>
            <div class="vendor-identity">
              <div class="vendor-photo large">
                <img
                  v-if="hasVendorProfileImage(selectedVendor)"
                  :src="selectedVendor.profileImageUrl"
                  :alt="`${selectedVendor.name} profile`"
                  @error="handleVendorImageError(selectedVendor.vendorImageKey)"
                />
                <span v-else>{{ selectedVendor.initials }}</span>
              </div>
              <div class="identity-copy">
                <strong>{{ selectedVendor.name }}</strong>
                <small>{{ selectedVendor.joinedLabel }}</small>
                <div class="chips">
                  <span class="chip muted">{{ count(selectedVendor.serviceOnlyCount) }} services</span>
                  <span class="chip muted">{{ count(selectedVendor.packageCount) }} packages</span>
                </div>
              </div>
            </div>
            <div class="stats-grid">
              <div><span>Live</span><strong>{{ selectedVendor.activeCount }}</strong></div>
              <div><span>Total</span><strong>{{ selectedVendor.serviceCount }}</strong></div>
              <div><span>Bookings</span><strong>{{ count(selectedVendor.bookingsCount) }}</strong></div>
              <div><span>Hidden</span><strong>{{ count(selectedVendor.inactiveCount) }}</strong></div>
            </div>
            <div class="detail-grid">
              <div class="detail-block">
                <span>Email</span>
                <strong>{{ selectedVendor.email || "Not provided" }}</strong>
              </div>
              <div class="detail-block">
                <span>Phone</span>
                <strong>{{ selectedVendor.phone || "Not provided" }}</strong>
              </div>
              <div class="detail-block detail-wide">
                <span>Categories</span>
                <strong>{{ selectedVendor.categories.length ? selectedVendor.categories.join(", ") : "No categories yet" }}</strong>
              </div>
            </div>
          </article>

          <article v-if="selectedVendor" class="card services-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">Listings In System</p>
                <h3>Services and Packages</h3>
              </div>
              <span class="card-meta">{{ count(selectedServices.length) }}</span>
            </header>
            <div v-if="!selectedServices.length" class="empty small">No services or packages for this vendor yet.</div>
            <div v-else class="service-list">
              <div v-for="service in selectedServices" :key="service.id" class="service-row">
                <div class="service-copy">
                  <div class="service-title-row">
                    <strong>{{ service.title }}</strong>
                    <span class="chip" :class="{ muted: !service.is_active }">{{ service.is_active ? "Live" : "Hidden" }}</span>
                  </div>
                  <p>{{ service.typeLabel }} - {{ service.kindLabel }} - {{ service.dateLabel }}</p>
                  <small>{{ service.location || "Location not added yet" }}</small>
                  <div class="service-chip-row">
                    <span class="chip muted">{{ service.priceLabel }}</span>
                    <span class="chip muted">{{ count(service.bookings_count || 0) }} bookings</span>
                    <span v-if="service.packagesCount" class="chip muted">{{ count(service.packagesCount) }} package item(s)</span>
                  </div>
                  <p v-if="service.description" class="service-description">{{ service.description }}</p>
                </div>
              </div>
            </div>
          </article>

          <article v-else class="card empty-selection">
            <p class="card-eyebrow">Vendor Profile</p>
            <h3>Select a Vendor</h3>
            <p>Choose a vendor from the directory to inspect their account information and system listings here.</p>
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
  margin: 0;
  font-family: "Fraunces", serif;
  font-size: 22px;
  font-weight: 700;
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

.hero-copy p,
.hero-selected small,
.highlight-label,
.highlight-note,
.vendor-copy p,
.vendor-side small,
.sidebar-head p,
.identity-copy small,
.detail-block span,
.service-copy p,
.service-copy small,
.empty,
.empty-selection,
.card-meta {
  color: var(--muted);
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
  border: 1px solid rgba(148, 163, 184, 0.14);
  flex-shrink: 0;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
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

.hero-copy p,
.hero-selected small,
.highlight-label,
.highlight-note,
.vendor-copy p,
.vendor-side small,
.sidebar-head p,
.identity-copy small,
.detail-block span,
.service-copy p,
.service-copy small,
.empty,
.empty-selection,
.card-meta {
  color: var(--muted);
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
  border: 1px solid rgba(148, 163, 184, 0.14);
  flex-shrink: 0;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
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

.ghost-btn,
.primary-btn,
.search-input,
select {
  font: inherit;
}

.ghost-btn,
.primary-btn {
  padding: 10px 14px;
  border-radius: 14px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
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
  display: flex;
  align-items: center;
  gap: 10px;
  min-height: 56px;
  padding: 0 18px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: var(--shadow-soft);
}

.search-icon svg {
  width: 18px;
  height: 18px;
  fill: #94a3b8;
}

.search-input {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  color: var(--ink);
  font-size: 14px;
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
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

.vendors-hero,
.hero-aside,
.hero-meta,
.filters,
.chips,
.service-chip-row {
  display: flex;
  flex-wrap: wrap;
}

.vendors-hero {
  align-items: end;
  justify-content: space-between;
  gap: 22px;
  padding: 28px;
  background: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(15, 23, 42, 0.06);
  border-radius: 26px;
  box-shadow: var(--shadow-soft);
  position: relative;
  overflow: hidden;
}

.vendors-hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 14% 12%, rgba(255, 122, 26, 0.12), transparent 28%),
    radial-gradient(circle at 88% 18%, rgba(90, 146, 240, 0.14), transparent 30%);
  pointer-events: none;
}

.vendors-hero > * {
  position: relative;
  z-index: 1;
}

.hero-copy {
  max-width: 700px;
}

.eyebrow,
.card-eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  color: #c45a12;
}

.hero-copy h1 {
  margin: 0;
  font-family: "Fraunces", serif;
  font-size: 52px;
  line-height: 0.98;
  color: #132238;
  max-width: 14ch;
}

.hero-copy p {
  margin: 12px 0 0;
  max-width: 720px;
  font-size: 16px;
  line-height: 1.65;
}

.hero-meta {
  gap: 10px;
  margin-top: 18px;
}

.hero-pill {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(255, 122, 26, 0.14);
  color: #b45309;
}

.hero-pill.soft {
  background: rgba(15, 23, 42, 0.06);
  color: #475569;
}

.hero-aside {
  gap: 14px;
  align-items: center;
  justify-content: flex-end;
  max-width: 420px;
}

.hero-selected {
  display: grid;
  gap: 4px;
  min-width: 250px;
  padding: 16px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.84);
  border: 1px solid rgba(15, 23, 42, 0.07);
  box-shadow: var(--shadow-soft);
}

.hero-selected-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #7c8ca3;
}

.hero-selected strong {
  color: #17263d;
}

.notice {
  margin: 0;
  padding: 12px 14px;
  border-radius: 16px;
  border: 1px solid transparent;
}

.notice.success {
  background: rgba(31, 157, 108, 0.12);
  color: #0f7a51;
  border-color: rgba(31, 157, 108, 0.18);
}

.notice.error {
  background: rgba(220, 38, 38, 0.1);
  color: #b42318;
  border-color: rgba(220, 38, 38, 0.16);
}

.notice.info {
  background: rgba(15, 23, 42, 0.05);
  color: #334155;
  border-color: rgba(15, 23, 42, 0.08);
}

.highlights,
.content-grid,
.stats-grid,
.detail-grid,
.vendor-list,
.side-column,
.service-list {
  display: grid;
  gap: 14px;
}

.highlights {
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
}

.highlight-card,
.card {
  background: var(--surface);
  border: 1px solid var(--stroke);
  border-radius: 22px;
  box-shadow: var(--shadow);
}

.highlight-card {
  display: grid;
  gap: 10px;
  padding: 20px 22px 22px;
  border-radius: 26px;
  background:
    radial-gradient(120% 140% at 10% 0%, rgba(255, 255, 255, 0.95) 0%, rgba(255, 245, 238, 0.9) 38%, rgba(243, 248, 255, 0.92) 70%, rgba(255, 236, 224, 0.9) 100%);
  border: 1px solid rgba(255, 255, 255, 0.75);
  box-shadow:
    0 22px 55px rgba(15, 23, 42, 0.16),
    inset 0 0 0 1px rgba(255, 255, 255, 0.6);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.highlight-card::before {
  content: "";
  position: absolute;
  inset: 10px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.45);
  opacity: 0.9;
  pointer-events: none;
}

.highlight-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 85% 15%, rgba(255, 122, 26, 0.22), transparent 56%),
    radial-gradient(circle at 8% 85%, rgba(59, 130, 246, 0.16), transparent 62%),
    radial-gradient(circle at 70% 60%, rgba(16, 185, 129, 0.14), transparent 55%);
  opacity: 0.75;
  pointer-events: none;
}

.highlight-card:nth-child(2)::before {
  border-color: rgba(59, 130, 246, 0.22);
}

.highlight-card:nth-child(3)::before {
  border-color: rgba(16, 185, 129, 0.22);
}

.highlight-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 30px 70px rgba(15, 23, 42, 0.2),
    inset 0 0 0 1px rgba(255, 255, 255, 0.7);
  border-color: rgba(255, 122, 26, 0.26);
}

.highlight-card strong {
  font-size: 30px;
  font-weight: 720;
  color: #17263d;
  position: relative;
  z-index: 1;
}

.highlight-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.22em;
  color: #7b8798;
  position: relative;
  z-index: 1;
}

.highlight-note {
  font-size: 12px;
  color: #5f6c7e;
  position: relative;
  z-index: 1;
}

.content-grid {
  grid-template-columns: minmax(0, 1.35fr) minmax(340px, 1fr);
  align-items: start;
}

.card {
  padding: 20px;
}

.card-head,
.sidebar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}

.card-head h3,
.sidebar-head h3 {
  margin: 0;
  font-family: "Fraunces", serif;
  font-size: 26px;
  color: #132238;
}

.card-meta {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.05);
  font-size: 12px;
  font-weight: 600;
}

.filters {
  gap: 12px;
  margin-bottom: 16px;
}

.filter-field {
  display: grid;
  gap: 8px;
  min-width: 180px;
}

.filter-field span {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 700;
  color: #73839a;
}

select {
  width: 100%;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
  border-radius: 14px;
  padding: 11px 12px;
  outline: none;
}

.vendor-row {
  width: 100%;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 16px;
  padding: 16px;
  border: 1px solid rgba(15, 23, 42, 0.06);
  background: rgba(255, 255, 255, 0.92);
  border-radius: 18px;
  text-align: left;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.vendor-row:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 122, 26, 0.18);
  box-shadow: 0 16px 26px rgba(15, 23, 42, 0.08);
}

.vendor-row.selected {
  border-color: rgba(255, 122, 26, 0.24);
  background: linear-gradient(135deg, rgba(255, 247, 240, 0.96), rgba(255, 255, 255, 0.98));
  box-shadow: 0 18px 30px rgba(255, 122, 26, 0.12);
}

.vendor-main,
.vendor-title-row,
.service-title-row,
.vendor-identity {
  display: flex;
  align-items: flex-start;
}

.vendor-main {
  gap: 12px;
}

.vendor-title-row,
.service-title-row,
.chips,
.service-chip-row {
  gap: 8px;
}

.vendor-title-row,
.service-title-row,
.chips {
  flex-wrap: wrap;
}

.vendor-photo {
  width: 50px;
  height: 50px;
  border-radius: 16px;
  background: linear-gradient(135deg, #ffe9d6, #ffd2aa);
  color: #bf5c06;
  display: grid;
  place-items: center;
  font-weight: 700;
  overflow: hidden;
  box-shadow: 0 12px 28px rgba(255, 122, 26, 0.16);
  flex-shrink: 0;
}

.vendor-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.vendor-photo span {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
}

.vendor-photo.large {
  width: 62px;
  height: 62px;
  border-radius: 18px;
}

.vendor-copy,
.service-copy,
.identity-copy {
  display: grid;
  gap: 8px;
}

.vendor-copy strong,
.service-copy strong,
.identity-copy strong {
  display: block;
  color: #17263d;
}

.vendor-copy p,
.service-copy p,
.service-copy small {
  margin: 0;
  font-size: 13px;
}

.vendor-side {
  display: grid;
  justify-items: end;
  gap: 4px;
  min-width: 120px;
  font-weight: 600;
}

.chip {
  display: inline-flex;
  align-items: center;
  padding: 5px 9px;
  border-radius: 999px;
  background: #fff3e6;
  color: #f15b2a;
  font-size: 11px;
  font-weight: 700;
}

.chip.muted {
  background: #f8fafc;
  color: #475569;
}

.spotlight-card {
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 122, 26, 0.12), transparent 28%),
    rgba(255, 255, 255, 0.92);
}

.sidebar-head p {
  margin: 4px 0 0;
}

.vendor-identity {
  gap: 12px;
  margin-bottom: 16px;
}

.identity-copy strong {
  font-size: 18px;
}

.stats-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.stats-grid div,
.detail-block,
.service-row {
  padding: 14px;
  border-radius: 16px;
  background: linear-gradient(180deg, #fff, #f8fafc);
  border: 1px solid rgba(15, 23, 42, 0.05);
}

.stats-grid span {
  display: block;
  font-size: 12px;
  color: var(--muted);
}

.stats-grid strong {
  display: block;
  margin-top: 6px;
  font-size: 20px;
  color: #17263d;
}

.detail-grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  margin-top: 16px;
}

.detail-block span {
  display: block;
  font-size: 12px;
}

.detail-block strong {
  display: block;
  margin-top: 6px;
  font-size: 14px;
  color: #17263d;
  line-height: 1.5;
  word-break: break-word;
}

.detail-wide {
  grid-column: 1 / -1;
}

.primary-btn {
  color: #fff;
  background: linear-gradient(135deg, #ff7a1a, #f15b2a);
  box-shadow: 0 14px 26px rgba(241, 91, 42, 0.24);
}

.ghost-btn {
  color: #c65300;
  background: rgba(255, 255, 255, 0.92);
  border-color: rgba(255, 122, 26, 0.24);
}

.service-row {
  display: grid;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(180deg, #fff, #fcfdff);
}

.service-copy {
  gap: 10px;
}

.service-description {
  margin: 0;
  color: #596981;
  line-height: 1.6;
}

.empty,
.empty-selection {
  min-height: 180px;
  display: grid;
  place-items: center;
  text-align: center;
}

.empty-selection {
  gap: 10px;
}

.small {
  min-height: 90px;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

@media (max-width: 1180px) {
  .vendors-shell,
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .vendors-shell {
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

  .vendors-hero,
  .hero-aside {
    flex-direction: column;
    align-items: flex-start;
  }

  .hero-copy h1 {
    max-width: none;
    font-size: 42px;
  }
}

@media (max-width: 840px) {
  .admin-topbar,
  .vendor-row,
  .sidebar-head {
    flex-direction: column;
    align-items: stretch;
  }

  .vendor-row {
    grid-template-columns: 1fr;
  }

  .vendor-side {
    justify-items: start;
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

  .stats-grid,
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .hero-copy h1 {
    font-size: 34px;
  }

  .topbar-actions {
    width: 100%;
  }

  .topbar-actions {
    justify-content: space-between;
  }
}
</style>



