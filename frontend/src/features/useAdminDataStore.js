import { computed, reactive } from "vue";
import { apiGet } from "./apiClient";
import { serviceFeeRate } from "./appData";

const emptySummary = () => ({
  users_total: 0,
  users_by_role: {
    admin: 0,
    vendor: 0,
    user: 0,
  },
  admins_total: 0,
  vendors_total: 0,
  customers_total: 0,
  accounts_total: 0,
  events_total: 0,
  bookings_total: 0,
  confirmed_bookings_total: 0,
  gross_revenue_total: 0,
  service_fee_total: 0,
});

const state = reactive({
  summary: emptySummary(),
  bookings: [],
  events: [],
  users: [],
  health: null,
  lastLoadedAt: 0,
});

const loading = reactive({
  summary: false,
  bookings: false,
  events: false,
  users: false,
  health: false,
  all: false,
});

const loaded = reactive({
  summary: false,
  bookings: false,
  events: false,
  users: false,
  health: false,
});

const errors = reactive({
  summary: "",
  bookings: "",
  events: "",
  users: "",
  health: "",
});

let inFlight = null;

function setError(section, value) {
  errors[section] = value ? String(value) : "";
}

function replaceArray(target, next) {
  target.splice(0, target.length, ...next);
}

function normalizeRole(value) {
  const role = String(value || "").trim().toLowerCase();
  if (["admin", "vendor", "user"].includes(role)) return role;
  return "user";
}

function withRole(rows, role) {
  return rows.map((row) => ({
    ...row,
    role,
  }));
}

function mergeUsers(...groups) {
  const merged = new Map();

  groups.flat().forEach((row) => {
    const numericId = Number(row?.id || 0);
    const email = String(row?.email || "").trim().toLowerCase();
    const key = numericId > 0 ? `id:${numericId}` : `email:${email || Math.random()}`;
    const existing = merged.get(key) || {};
    merged.set(key, {
      ...existing,
      ...row,
      role: normalizeRole(row?.role || existing?.role),
    });
  });

  return Array.from(merged.values());
}

function normalizeBookingStatus(row) {
  const status = String(row?.status || "").toLowerCase();
  const paymentStatus = String(row?.payment_status || "").toLowerCase();
  if (status === "cancelled") return "cancelled";
  if (status === "confirmed" || paymentStatus === "confirmed") return "confirmed";
  return "pending";
}

function buildSummaryFromState() {
  const users = Array.isArray(state.users) ? state.users : [];
  const vendorsTotal = users.filter((row) => normalizeRole(row?.role) === "vendor").length;
  const customersTotal = users.filter((row) => normalizeRole(row?.role) === "user").length;
  const adminsTotal = users.filter((row) => normalizeRole(row?.role) === "admin").length;
  const confirmedBookings = state.bookings.filter((row) => normalizeBookingStatus(row) === "confirmed");
  const grossRevenueTotal = Number(
    confirmedBookings.reduce((sum, row) => sum + Number(row?.total_amount || 0), 0).toFixed(2),
  );

  return {
    ...emptySummary(),
    users_total: users.length,
    users_by_role: {
      admin: adminsTotal,
      vendor: vendorsTotal,
      user: customersTotal,
    },
    admins_total: adminsTotal,
    vendors_total: vendorsTotal,
    customers_total: customersTotal,
    accounts_total: vendorsTotal + customersTotal,
    events_total: state.events.length,
    bookings_total: state.bookings.length,
    confirmed_bookings_total: confirmedBookings.length,
    gross_revenue_total: grossRevenueTotal,
    service_fee_total: Number((grossRevenueTotal * serviceFeeRate).toFixed(2)),
  };
}

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

async function loadSummary({ force = false } = {}) {
  if (loading.summary) return;
  if (loaded.summary && !force) return;
  loading.summary = true;
  setError("summary", "");
  try {
    state.summary = {
      ...emptySummary(),
      ...((await apiGet("admin/dashboard")) || {}),
    };
    loaded.summary = true;
  } catch (error) {
    state.summary = emptySummary();
    setError("summary", error?.message || "Unable to load admin summary.");
  } finally {
    loading.summary = false;
  }
}

async function loadBookings({ force = false } = {}) {
  if (loading.bookings) return;
  if (loaded.bookings && !force) return;
  loading.bookings = true;
  setError("bookings", "");
  try {
    const rows = await fetchPagedRows("admin/bookings");
    replaceArray(state.bookings, rows);
    loaded.bookings = true;
  } catch (error) {
    try {
      const rows = await fetchPagedRows("bookings");
      replaceArray(state.bookings, rows);
      loaded.bookings = true;
    } catch (inner) {
      replaceArray(state.bookings, []);
      setError("bookings", inner?.message || error?.message || "Unable to load bookings.");
    }
  } finally {
    loading.bookings = false;
  }
}

async function loadEvents({ force = false } = {}) {
  if (loading.events) return;
  if (loaded.events && !force) return;
  loading.events = true;
  setError("events", "");
  try {
    const result = await apiGet("events", { per_page: 200, include_inactive: 1, ts: Date.now() });
    const rows = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    replaceArray(state.events, rows);
    loaded.events = true;
  } catch (error) {
    replaceArray(state.events, []);
    setError("events", error?.message || "Unable to load events.");
  } finally {
    loading.events = false;
  }
}

async function loadUsers({ force = false, adminUserId = null } = {}) {
  if (loading.users) return;
  if (loaded.users && !force) return;
  loading.users = true;
  setError("users", "");
  try {
    const rows = await fetchPagedRows("admin/users");
    replaceArray(state.users, rows);
    loaded.users = true;
  } catch (error) {
    try {
      if (!adminUserId) {
        throw error;
      }

      const [vendorRows, customerRows] = await Promise.all([
        fetchPagedRows("vendors", { per_page: 100 }),
        fetchPagedRows("admin/customer-directory", {
          admin_user_id: adminUserId,
          per_page: 100,
          ts: Date.now(),
        }),
      ]);

      replaceArray(
        state.users,
        mergeUsers(
          withRole(vendorRows, "vendor"),
          withRole(customerRows, "user"),
        ),
      );
      loaded.users = true;
      setError("users", "");
    } catch (inner) {
      replaceArray(state.users, []);
      setError("users", inner?.message || error?.message || "Unable to load users.");
    }
  } finally {
    loading.users = false;
  }
}

async function loadHealth({ force = false } = {}) {
  if (loading.health) return;
  if (loaded.health && !force) return;
  loading.health = true;
  setError("health", "");
  try {
    state.health = await apiGet("health/redis");
    loaded.health = true;
  } catch (error) {
    try {
      state.health = await apiGet("health");
      loaded.health = true;
    } catch (inner) {
      state.health = null;
      setError("health", inner?.message || error?.message || "Unable to load system health.");
    }
  } finally {
    loading.health = false;
  }
}

async function loadAll({ force = false, adminUserId = null } = {}) {
  if (inFlight) return inFlight;
  if (!force && state.lastLoadedAt && Date.now() - state.lastLoadedAt < 60 * 1000) {
    return state;
  }
  loading.all = true;
  inFlight = Promise.all([
    loadSummary({ force }),
    loadBookings({ force }),
    loadEvents({ force }),
    loadUsers({ force, adminUserId }),
    loadHealth({ force }),
  ])
    .then(() => {
      if (errors.summary) {
        state.summary = buildSummaryFromState();
        loaded.summary = true;
        setError("summary", "");
      }
      state.lastLoadedAt = Date.now();
      return state;
    })
    .finally(() => {
      loading.all = false;
      inFlight = null;
    });
  return inFlight;
}

function updateEvent(updated) {
  const index = state.events.findIndex((item) => Number(item?.id) === Number(updated?.id));
  if (index < 0) return;
  const existing = state.events[index] || {};
  state.events.splice(index, 1, {
    ...existing,
    ...updated,
    vendor: updated?.vendor || existing?.vendor,
    vendor_name: updated?.vendor_name || existing?.vendor_name,
    bookings_count: updated?.bookings_count ?? existing?.bookings_count ?? 0,
  });
}

function removeEvent(id) {
  const next = state.events.filter((item) => Number(item?.id) !== Number(id));
  replaceArray(state.events, next);
}

const error = computed(() => Object.values(errors).find(Boolean) || "");

export function useAdminDataStore() {
  return {
    state,
    loading,
    errors,
    error,
    loadAll,
    loadEvents,
    loadBookings,
    loadUsers,
    loadSummary,
    loadHealth,
    updateEvent,
    removeEvent,
  };
}
