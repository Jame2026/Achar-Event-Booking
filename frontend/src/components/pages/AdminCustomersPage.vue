<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { formatDateTime, summarizeBookedServices } from "../../features/bookingMappers";
import { apiGet } from "../../features/apiClient";

const props = defineProps({
  appLogoSrc: { type: String, default: "" },
  adminDisplayName: { type: String, default: "Admin" },
  adminUserId: { type: [Number, String], default: null },
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

const activeKey = ref("customers");
const searchQuery = ref("");
const activityFilter = ref("all");
const bookingStateFilter = ref("all");
const isLoading = ref(false);
const notice = ref("");
const noticeTone = ref("info");
const customers = ref([]);
const selectedCustomerKey = ref("");
const failedCustomerImages = ref(new Set());

function count(value) {
  return Number(value || 0).toLocaleString();
}

function money(value) {
  return `$${Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 0 })}`;
}

function shortName(value) {
  return String(value || "Customer")
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

function formatTimeLabel(dateString) {
  const raw = String(dateString || "").trim();
  if (!raw) return "Time TBD";
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return "All day";

  const date = new Date(raw);
  if (Number.isNaN(date.getTime())) return "Time TBD";

  return date.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit",
  });
}

function formatBadgeLabel(value, fallback = "Unknown") {
  const normalized = String(value || "")
    .replace(/[_-]+/g, " ")
    .trim();

  if (!normalized) return fallback;

  return normalized
    .split(/\s+/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(" ");
}

function setNotice(message, tone = "info") {
  notice.value = String(message || "").trim();
  noticeTone.value = tone;
}

const initials = computed(() => {
  const parts = String(props.adminDisplayName || "Admin").split(" ").filter(Boolean);
  return `${parts[0]?.[0] || "A"}${parts[1]?.[0] || ""}`.toUpperCase();
});

const customerRows = computed(() =>
  customers.value
    .map((customer) => {
      const customerName = String(customer?.name || "Customer").trim() || "Customer";
      const bookingHistory = (Array.isArray(customer?.bookings) ? customer.bookings : [])
        .map((booking) => {
          const event = booking?.event || {};
          const vendor = event?.vendor || {};
          const bookedItems = Array.isArray(booking?.booked_items) ? booking.booked_items : [];
          const bookingDate = booking?.requested_event_date || event?.starts_at || booking?.created_at || "";
          const totalAmount = Number(booking?.total_amount || 0);
          const status = ["confirmed", "cancelled"].includes(String(booking?.status || "").toLowerCase())
            ? String(booking.status).toLowerCase()
            : "pending";

          return {
            id: Number(booking?.id || 0),
            bookingCode: `#BK-${String(booking?.id || 0).padStart(4, "0")}`,
            vendorName: String(vendor?.name || "Vendor").trim() || "Vendor",
            vendorEmail: String(vendor?.email || "").trim(),
            serviceLabel: summarizeBookedServices(
              bookedItems,
              String(booking?.service_name || event?.title || "Service Booking").trim() || "Service Booking",
            ),
            bookingKind:
              String(event?.service_mode || "").trim().toLowerCase() === "package" || bookedItems.length > 1
                ? "Package"
                : "Service",
            eventTypeLabel: formatBadgeLabel(event?.event_type, "Other"),
            quantity: Number(booking?.quantity || 1),
            location: String(event?.location || "").trim() || "Location not added yet",
            status,
            statusLabel: status === "confirmed" ? "Confirmed" : status === "cancelled" ? "Cancelled" : "Pending",
            paymentStatusLabel: formatBadgeLabel(booking?.payment_status, "Unpaid"),
            totalAmount,
            totalLabel: money(totalAmount),
            dateLabel: formatDateTime(bookingDate),
            timeLabel: formatTimeLabel(event?.starts_at || bookingDate),
            sortAt: bookingDate || booking?.created_at || "",
          };
        })
        .sort((left, right) => stamp(right.sortAt) - stamp(left.sortAt));

      const confirmedSpend = bookingHistory
        .filter((booking) => booking.status === "confirmed")
        .reduce((sum, booking) => sum + Number(booking.totalAmount || 0), 0);
      const preferredTypes = Array.from(new Set(bookingHistory.map((booking) => booking.eventTypeLabel).filter(Boolean)));
      const bookingsCount = Number(customer?.bookings_count || bookingHistory.length || 0);
      const confirmedCount = Number(customer?.confirmed_bookings_count || 0);
      const pendingCount = Number(customer?.pending_bookings_count || 0);
      const cancelledCount = Number(customer?.cancelled_bookings_count || 0);

      return {
        id: Number(customer?.id || 0),
        key: customer?.id ? `customer:${customer.id}` : `customer:${customerName.toLowerCase()}`,
        name: customerName,
        initials: shortName(customerName),
        email: String(customer?.email || "").trim(),
        phone: String(customer?.phone || "").trim(),
        location: String(customer?.location || "").trim() || "Location not added yet",
        profileImageUrl: String(customer?.profile_image_url || "").trim(),
        customerImageKey: customer?.id ? `customer:${customer.id}` : `customer:${customerName.toLowerCase()}`,
        joinedLabel: customer?.created_at ? formatDateTime(customer.created_at) : "Join date unavailable",
        bookingsCount,
        confirmedCount,
        pendingCount,
        cancelledCount,
        confirmedSpend,
        confirmedSpendLabel: money(confirmedSpend),
        preferredTypes,
        bookingHistory,
        memberState: bookingsCount === 0 ? "New" : bookingsCount > 1 ? "Repeat" : "Active",
        lastBookingLabel: bookingHistory[0]?.dateLabel || "No bookings yet",
        lastBookingService: bookingHistory[0]?.serviceLabel || "No bookings yet",
      };
    })
    .sort((left, right) => {
      if (right.bookingsCount !== left.bookingsCount) return right.bookingsCount - left.bookingsCount;
      return left.name.localeCompare(right.name);
    }),
);

const filteredCustomers = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return customerRows.value.filter((customer) => {
    if (activityFilter.value === "booked" && customer.bookingsCount === 0) return false;
    if (activityFilter.value === "repeat" && customer.bookingsCount < 2) return false;
    if (activityFilter.value === "new" && customer.bookingsCount !== 0) return false;
    if (bookingStateFilter.value !== "all" && !customer.bookingHistory.some((booking) => booking.status === bookingStateFilter.value)) return false;
    if (!query) return true;

    return [
      customer.name,
      customer.email,
      customer.phone,
      customer.location,
      customer.preferredTypes.join(" "),
      customer.bookingHistory.map((booking) => `${booking.serviceLabel} ${booking.vendorName}`).join(" "),
      String(customer.id || ""),
    ]
      .join(" ")
      .toLowerCase()
      .includes(query);
  });
});

const selectedCustomer = computed(
  () => filteredCustomers.value.find((item) => item.key === selectedCustomerKey.value) || filteredCustomers.value[0] || null,
);

const selectedBookings = computed(() => selectedCustomer.value?.bookingHistory || []);

const highlightCards = computed(() => [
  { label: "Total Customers", value: count(customerRows.value.length), note: `${count(filteredCustomers.value.length)} shown here` },
  { label: "Active Bookers", value: count(customerRows.value.filter((item) => item.bookingsCount > 0).length), note: "Customers with bookings" },
  { label: "Bookings In System", value: count(customerRows.value.reduce((sum, item) => sum + item.bookingsCount, 0)), note: "Across services and packages" },
  { label: "Confirmed Revenue", value: money(customerRows.value.reduce((sum, item) => sum + item.confirmedSpend, 0)), note: "From confirmed bookings" },
]);

const totalBookingsCount = computed(() => customerRows.value.reduce((sum, item) => sum + item.bookingsCount, 0));
const hasCustomerProfileImage = (customer) =>
  Boolean(String(customer?.profileImageUrl || "").trim())
  && !failedCustomerImages.value.has(customer?.customerImageKey || customer?.key);

function handleCustomerImageError(imageKey) {
  if (!imageKey || failedCustomerImages.value.has(imageKey)) return;
  const next = new Set(failedCustomerImages.value);
  next.add(imageKey);
  failedCustomerImages.value = next;
}

function syncActiveKey() {
  const page = Array.isArray(route.query.page) ? route.query.page[0] : route.query.page;
  activeKey.value = page || "customers";
}

function navigateTo(key) {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
}

async function loadCustomerDirectory() {
  if (!props.adminUserId) {
    customers.value = [];
    return setNotice("Admin account is missing. Please sign in again.", "error");
  }

  isLoading.value = true;
  notice.value = "";

  try {
    const result = await apiGet("admin/customer-directory", {
      admin_user_id: props.adminUserId,
      per_page: 100,
      ts: Date.now(),
    });

    customers.value = Array.isArray(result?.data) ? result.data : [];
    failedCustomerImages.value = new Set();
    if (!customers.value.length) notice.value = "No customer accounts found yet.";
  } catch (error) {
    customers.value = [];
    setNotice(error?.message || "Could not load customer directory.", "error");
  } finally {
    isLoading.value = false;
  }
}

watch(() => route.query.page, syncActiveKey);
watch(
  filteredCustomers,
  (rows) => {
    if (!rows.length) return (selectedCustomerKey.value = "");
    if (!rows.some((item) => item.key === selectedCustomerKey.value)) selectedCustomerKey.value = rows[0].key;
  },
  { immediate: true },
);

syncActiveKey();
onMounted(() => void loadCustomerDirectory());
</script>

<template>
  <section class="customers-shell">
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
            <p class="brand-subtitle">Customer relationship workspace</p>
          </div>
        </div>
      </div>

      <section class="sidebar-block">
        <div class="sidebar-block-head">
          <span class="sidebar-section-label">Navigation</span>
          <span class="sidebar-section-caption">Admin modules</span>
        </div>

        <nav class="admin-nav">
          <button v-for="item in navItems" :key="item.key" type="button" class="nav-item" :class="{ active: activeKey === item.key }" @click="navigateTo(item.key)">
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
          <input v-model="searchQuery" class="search-input" type="search" placeholder="Search customer names, email, phone, or booked services..." />
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

      <section class="customers-hero">
        <div class="hero-copy">
          <p class="eyebrow">Customer Directory</p>
          <h1>All Customers and Their Bookings</h1>
          <p>Review registered customer accounts and inspect the services or packages they have booked across your system.</p>
          <div class="hero-meta">
            <span class="hero-pill">{{ count(customerRows.length) }} total customers</span>
            <span class="hero-pill soft">{{ count(totalBookingsCount) }} total bookings</span>
          </div>
        </div>
        <div class="hero-aside">
          <div v-if="selectedCustomer" class="hero-selected">
            <span class="hero-selected-label">Selected Customer</span>
            <strong>{{ selectedCustomer.name }}</strong>
            <small>{{ count(selectedCustomer.bookingsCount) }} booking(s) - {{ selectedCustomer.joinedLabel }}</small>
          </div>
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
              <p class="card-eyebrow">Customer Directory</p>
              <h3>All Customers</h3>
            </div>
            <span class="card-meta">{{ count(filteredCustomers.length) }} results</span>
          </header>
          <div class="filters">
            <label class="filter-field">
              <span>Activity</span>
              <select v-model="activityFilter">
                <option value="all">All Customers</option>
                <option value="booked">With Bookings</option>
                <option value="repeat">Repeat Customers</option>
                <option value="new">No Bookings Yet</option>
              </select>
            </label>
            <label class="filter-field">
              <span>Booking Status</span>
              <select v-model="bookingStateFilter">
                <option value="all">Any Status</option>
                <option value="confirmed">Confirmed</option>
                <option value="pending">Pending</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </label>
          </div>

          <div v-if="isLoading" class="empty">Loading customer directory...</div>
          <div v-else-if="!filteredCustomers.length" class="empty">No customers match your filters.</div>
          <div v-else class="customer-list">
            <button v-for="customer in filteredCustomers" :key="customer.key" type="button" class="customer-row" :class="{ selected: selectedCustomer?.key === customer.key }" @click="selectedCustomerKey = customer.key">
              <div class="customer-main">
                <div class="customer-photo">
                  <img
                    v-if="hasCustomerProfileImage(customer)"
                    :src="customer.profileImageUrl"
                    :alt="`${customer.name} profile`"
                    @error="handleCustomerImageError(customer.customerImageKey)"
                  />
                  <span v-else>{{ customer.initials }}</span>
                </div>
                <div class="customer-copy">
                  <div class="customer-title-row">
                    <strong>{{ customer.name }}</strong>
                    <span class="chip muted">{{ customer.memberState }}</span>
                  </div>
                  <p>{{ customer.email || "Email not provided" }}</p>
                  <div class="chips">
                    <span class="chip">{{ count(customer.bookingsCount) }} booking(s)</span>
                    <span class="chip muted">{{ customer.confirmedSpendLabel }}</span>
                    <span class="chip muted">{{ customer.preferredTypes[0] || "No category yet" }}</span>
                  </div>
                </div>
              </div>
              <div class="customer-side">
                <span>{{ customer.lastBookingService }}</span>
                <small>{{ customer.lastBookingLabel }}</small>
              </div>
            </button>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedCustomer" class="card spotlight-card">
            <div class="sidebar-head">
              <div>
                <p class="card-eyebrow">Customer Profile</p>
                <h3>{{ selectedCustomer.name }}</h3>
                <p>{{ selectedCustomer.location }}</p>
              </div>
              <span class="chip">{{ selectedCustomer.memberState }}</span>
            </div>
            <div class="customer-identity">
              <div class="customer-photo large">
                <img
                  v-if="hasCustomerProfileImage(selectedCustomer)"
                  :src="selectedCustomer.profileImageUrl"
                  :alt="`${selectedCustomer.name} profile`"
                  @error="handleCustomerImageError(selectedCustomer.customerImageKey)"
                />
                <span v-else>{{ selectedCustomer.initials }}</span>
              </div>
              <div class="identity-copy">
                <strong>{{ selectedCustomer.name }}</strong>
                <small>{{ selectedCustomer.joinedLabel }}</small>
                <div class="chips">
                  <span class="chip muted">{{ count(selectedCustomer.confirmedCount) }} confirmed</span>
                  <span class="chip muted">{{ count(selectedCustomer.pendingCount) }} pending</span>
                </div>
              </div>
            </div>
            <div class="stats-grid">
              <div><span>Bookings</span><strong>{{ count(selectedCustomer.bookingsCount) }}</strong></div>
              <div><span>Confirmed</span><strong>{{ count(selectedCustomer.confirmedCount) }}</strong></div>
              <div><span>Pending</span><strong>{{ count(selectedCustomer.pendingCount) }}</strong></div>
              <div><span>Total Spend</span><strong>{{ selectedCustomer.confirmedSpendLabel }}</strong></div>
            </div>
            <div class="detail-grid">
              <div class="detail-block">
                <span>Email</span>
                <strong>{{ selectedCustomer.email || "Not provided" }}</strong>
              </div>
              <div class="detail-block">
                <span>Phone</span>
                <strong>{{ selectedCustomer.phone || "Not provided" }}</strong>
              </div>
              <div class="detail-block">
                <span>Location</span>
                <strong>{{ selectedCustomer.location }}</strong>
              </div>
              <div class="detail-block">
                <span>Joined</span>
                <strong>{{ selectedCustomer.joinedLabel }}</strong>
              </div>
              <div class="detail-block detail-wide">
                <span>Preferred Categories</span>
                <strong>{{ selectedCustomer.preferredTypes.length ? selectedCustomer.preferredTypes.join(", ") : "No bookings yet" }}</strong>
              </div>
            </div>
          </article>

          <article v-if="selectedCustomer" class="card bookings-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">Booking History</p>
                <h3>Customer Bookings</h3>
              </div>
              <span class="card-meta">{{ count(selectedBookings.length) }}</span>
            </header>
            <div v-if="!selectedBookings.length" class="empty small">This customer has not booked any service or package yet.</div>
            <div v-else class="booking-list">
              <div v-for="booking in selectedBookings" :key="booking.id" class="booking-row">
                <div class="booking-copy">
                  <div class="booking-title-row">
                    <strong>{{ booking.serviceLabel }}</strong>
                    <span class="chip" :class="{ muted: booking.status !== 'confirmed' }">{{ booking.statusLabel }}</span>
                  </div>
                  <p>{{ booking.vendorName }} - {{ booking.bookingKind }} - {{ booking.bookingCode }}</p>
                  <small>{{ booking.dateLabel }} - {{ booking.timeLabel }}</small>
                  <div class="booking-chip-row">
                    <span class="chip muted">{{ booking.totalLabel }}</span>
                    <span class="chip muted">Qty {{ booking.quantity }}</span>
                    <span class="chip muted">{{ booking.paymentStatusLabel }}</span>
                    <span class="chip muted">{{ booking.eventTypeLabel }}</span>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <article v-else class="card empty-selection">
            <p class="card-eyebrow">Customer Profile</p>
            <h3>Select a Customer</h3>
            <p>Choose a customer from the directory to inspect their profile and booking history here.</p>
          </article>
        </aside>
      </section>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");
.customers-shell {
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

.customers-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.customers-shell > * {
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
.customer-copy p,
.customer-side small,
.sidebar-head p,
.identity-copy small,
.detail-block span,
.booking-copy p,
.booking-copy small,
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
.customer-copy p,
.customer-side small,
.sidebar-head p,
.identity-copy small,
.detail-block span,
.booking-copy p,
.booking-copy small,
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

.customers-hero,
.hero-aside,
.hero-meta,
.filters,
.chips,
.booking-chip-row {
  display: flex;
  flex-wrap: wrap;
}

.customers-hero {
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

.customers-hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 14% 12%, rgba(255, 122, 26, 0.12), transparent 28%),
    radial-gradient(circle at 88% 18%, rgba(90, 146, 240, 0.14), transparent 30%);
  pointer-events: none;
}

.customers-hero > * {
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
.customer-list,
.side-column,
.booking-list {
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
  gap: 8px;
  padding: 18px;
  position: relative;
  overflow: hidden;
}

.highlight-card::before {
  content: "";
  position: absolute;
  inset: auto 16px 16px auto;
  width: 40px;
  height: 40px;
  border-radius: 14px;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.14), rgba(255, 122, 26, 0.04));
}

.highlight-card strong {
  font-size: 28px;
  color: #17263d;
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

.customer-row {
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

.customer-row:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 122, 26, 0.18);
  box-shadow: 0 16px 26px rgba(15, 23, 42, 0.08);
}

.customer-row.selected {
  border-color: rgba(255, 122, 26, 0.24);
  background: linear-gradient(135deg, rgba(255, 247, 240, 0.96), rgba(255, 255, 255, 0.98));
  box-shadow: 0 18px 30px rgba(255, 122, 26, 0.12);
}

.customer-main,
.customer-title-row,
.booking-title-row,
.customer-identity {
  display: flex;
  align-items: flex-start;
}

.customer-main {
  gap: 12px;
}

.customer-title-row,
.booking-title-row,
.chips,
.booking-chip-row {
  gap: 8px;
}

.customer-title-row,
.booking-title-row,
.chips {
  flex-wrap: wrap;
}

.customer-photo {
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

.customer-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.customer-photo span {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
}

.customer-photo.large {
  width: 62px;
  height: 62px;
  border-radius: 18px;
}

.customer-copy,
.booking-copy,
.identity-copy {
  display: grid;
  gap: 8px;
}

.customer-copy strong,
.booking-copy strong,
.identity-copy strong {
  display: block;
  color: #17263d;
}

.customer-copy p,
.booking-copy p,
.booking-copy small {
  margin: 0;
  font-size: 13px;
}

.customer-side {
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

.customer-identity {
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
.booking-row {
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

.booking-row {
  display: grid;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(180deg, #fff, #fcfdff);
}

.booking-copy {
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
  .customers-shell,
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .customers-shell {
    grid-template-columns: 1fr;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
  }

  .customers-hero,
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
  .customer-row,
  .sidebar-head {
    flex-direction: column;
    align-items: stretch;
  }

  .customer-row {
    grid-template-columns: 1fr;
  }

  .customer-side {
    justify-items: start;
  }
}

@media (max-width: 720px) {
  .admin-main {
    padding: 24px;
  }

  .admin-sidebar {
    padding: 24px 18px;
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


