<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { serviceFeeRate } from "../../features/appData";
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
  { key: "customers", label: "Customers", icon: "users" },
  { key: "revenue", label: "Revenue", icon: "revenue" },
  { key: "settings", label: "Settings", icon: "settings" },
];
const activeKey = ref("revenue");
const adminStore = useAdminDataStore();
const isLoading = computed(() => adminStore.loading.all || adminStore.loading.bookings);
const loadError = computed(() => adminStore.errors.bookings);
const rawBookings = computed(() => adminStore.state.bookings);
const rangeKey = ref("30d");
const chartMetric = ref("gross");
const isExportingReport = ref(false);
const rangeOptions = [
  { key: "30d", label: "Last 30 Days", days: 30 },
  { key: "quarter", label: "Quarter", days: 90 },
  { key: "year", label: "Year", days: 365 },
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

const formatCurrency = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
  }).format(Number.isFinite(amount) ? amount : 0);
};

const formatCompactCurrency = (value) => {
  const amount = Number(value || 0);
  if (!Number.isFinite(amount)) return "$0";
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    notation: "compact",
    maximumFractionDigits: 1,
  }).format(amount);
};

const normalizeStatus = (row) => {
  const status = String(row?.status || "").toLowerCase();
  const paymentStatus = String(row?.payment_status || "").toLowerCase();
  if (status === "cancelled") return "failed";
  if (status === "confirmed" || paymentStatus === "confirmed") return "completed";
  if (status === "pending" || paymentStatus === "pending") return "pending";
  return "pending";
};

const formatDateLabel = (value) => {
  const date = value instanceof Date ? value : new Date(value);
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return "Date TBD";
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "2-digit",
    year: "numeric",
  });
};

const getBookingDate = (row) => {
  const raw =
    row?.paid_at ||
    row?.requested_event_date ||
    row?.created_at ||
    row?.updated_at ||
    row?.event?.starts_at ||
    null;
  const date = raw ? new Date(raw) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return null;
  return date;
};

const normalizeBooking = (row) => {
  const date = getBookingDate(row);
  const vendorName =
    row?.event?.vendor?.name ||
    row?.event?.vendor_name ||
    row?.vendor_name ||
    row?.service_name ||
    row?.customer_name ||
    "Vendor";
  const amount = Number(row?.total_amount || 0);
  const status = normalizeStatus(row);
  return {
    id: row?.id ? `#TXN-${row.id}` : "#TXN",
    vendorName,
    amount,
    status,
    statusLabel: status === "completed" ? "Completed" : status === "failed" ? "Failed" : "Pending",
    date,
    dateLabel: date ? formatDateLabel(date) : "Date TBD",
    paymentMethod: String(row?.payment_method || ""),
  };
};

const selectedRange = computed(() => rangeOptions.find((item) => item.key === rangeKey.value) || rangeOptions[0]);

const normalizeRangeDates = (days) => {
  const end = new Date();
  end.setHours(23, 59, 59, 999);
  const start = new Date(end);
  start.setDate(end.getDate() - Math.max(days - 1, 0));
  start.setHours(0, 0, 0, 0);
  return { start, end };
};

const normalizedBookings = computed(() => rawBookings.value.map(normalizeBooking));

const rangeBookings = computed(() => {
  const { start, end } = normalizeRangeDates(selectedRange.value.days);
  return normalizedBookings.value.filter((row) => row.date && row.date >= start && row.date <= end);
});

const previousRangeBookings = computed(() => {
  const { start } = normalizeRangeDates(selectedRange.value.days);
  const prevEnd = new Date(start);
  prevEnd.setMilliseconds(-1);
  const prevStart = new Date(prevEnd);
  prevStart.setDate(prevEnd.getDate() - Math.max(selectedRange.value.days - 1, 0));
  prevStart.setHours(0, 0, 0, 0);
  return normalizedBookings.value.filter((row) => row.date && row.date >= prevStart && row.date <= prevEnd);
});

const sumByStatus = (rows, status) =>
  rows.reduce((sum, row) => (row.status === status ? sum + row.amount : sum), 0);

const countByStatus = (rows, status) => rows.filter((row) => row.status === status).length;

const revenueTotals = computed(() => {
  const confirmed = sumByStatus(rangeBookings.value, "completed");
  const pending = sumByStatus(rangeBookings.value, "pending");
  const failed = sumByStatus(rangeBookings.value, "failed");
  const fees = confirmed * serviceFeeRate;
  const net = confirmed - fees;
  return {
    confirmed,
    pending,
    failed,
    fees,
    net,
    pendingCount: countByStatus(rangeBookings.value, "pending"),
  };
});

const previousRevenueTotals = computed(() => ({
  confirmed: sumByStatus(previousRangeBookings.value, "completed"),
  net: sumByStatus(previousRangeBookings.value, "completed") * (1 - serviceFeeRate),
}));

const formatDelta = (current, previous) => {
  if (!previous) return "New";
  const delta = ((current - previous) / Math.abs(previous)) * 100;
  const sign = delta >= 0 ? "+" : "";
  return `${sign}${delta.toFixed(1)}%`;
};

const revenueStats = computed(() => [
  {
    label: "Total Revenue",
    value: formatCurrency(revenueTotals.value.confirmed),
    delta: formatDelta(revenueTotals.value.confirmed, previousRevenueTotals.value.confirmed),
    icon: "revenue",
  },
  {
    label: "Pending Payouts",
    value: formatCurrency(revenueTotals.value.pending),
    delta: `${revenueTotals.value.pendingCount} active`,
    icon: "payouts",
  },
  {
    label: "Platform Fees",
    value: formatCurrency(revenueTotals.value.fees),
    delta: `${(serviceFeeRate * 100).toFixed(1)}% fee`,
    icon: "fees",
  },
  {
    label: "Net Profit",
    value: formatCurrency(revenueTotals.value.net),
    delta: formatDelta(revenueTotals.value.net, previousRevenueTotals.value.net),
    icon: "profit",
  },
]);

const chartSeries = computed(() => {
  const { start, end } = normalizeRangeDates(selectedRange.value.days);
  const totalDays = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1);
  const bucketCount = 12;
  const bucketSize = Math.ceil(totalDays / bucketCount);
  const buckets = Array.from({ length: bucketCount }, (_, index) => {
    const bucketStart = new Date(start);
    bucketStart.setDate(start.getDate() + index * bucketSize);
    const bucketEnd = new Date(bucketStart);
    bucketEnd.setDate(bucketStart.getDate() + bucketSize - 1);
    bucketEnd.setHours(23, 59, 59, 999);
    return {
      label: bucketStart.toLocaleDateString("en-US", {
        month: "short",
        day: "2-digit",
      }),
      fullLabel: bucketStart.toLocaleDateString("en-US", {
        month: "short",
        day: "2-digit",
        year: "numeric",
      }),
      start: bucketStart,
      end: bucketEnd,
      value: 0,
    };
  });

  rangeBookings.value.forEach((row) => {
    if (!row.date) return;
    const target = buckets.find((bucket) => row.date >= bucket.start && row.date <= bucket.end);
    if (!target) return;
    const value = chartMetric.value === "net" ? row.amount * (1 - serviceFeeRate) : row.amount;
    target.value += value;
  });

  const maxValue = Math.max(...buckets.map((bucket) => bucket.value), 0);
  const peakValue = maxValue || 1;
  const peakIndex = buckets.findIndex((bucket) => bucket.value === maxValue);
  return buckets.map((bucket, index) => ({
    ...bucket,
    isPeak: index === peakIndex && maxValue > 0,
    height: Math.max(12, Math.round((bucket.value / peakValue) * 90)),
  }));
});

const chartMax = computed(() => Math.max(...chartSeries.value.map((item) => item.value), 0));

const chartLabels = computed(() => {
  const maxValue = chartMax.value || 0;
  if (!maxValue) {
    return ["$0", "$0", "$0", "$0"];
  }
  return [0.8, 0.6, 0.4, 0.2].map((ratio) => formatCompactCurrency(maxValue * ratio));
});

const chartFooter = computed(() => {
  const series = chartSeries.value;
  const peak = series.reduce((carry, item) => (item.value > carry.value ? item : carry), {
    value: 0,
    fullLabel: "N/A",
  });
  const growthRates = [];
  for (let i = 1; i < series.length; i += 1) {
    const prev = series[i - 1].value;
    const current = series[i].value;
    if (prev > 0) {
      growthRates.push(((current - prev) / prev) * 100);
    }
  }
  const avgGrowth =
    growthRates.length > 0
      ? growthRates.reduce((sum, value) => sum + value, 0) / growthRates.length
      : 0;
  const lastValue = series[series.length - 1]?.value || 0;
  const forecast = lastValue * (1 + avgGrowth / 100);
  return {
    peakLabel: peak.fullLabel || "N/A",
    avgGrowthLabel: `${avgGrowth >= 0 ? "+" : ""}${avgGrowth.toFixed(1)}%`,
    forecastLabel: formatCurrency(forecast),
    forecastValue: forecast,
  };
});

const projection = computed(() => {
  const totalCount = rangeBookings.value.length;
  const failedCount = countByStatus(rangeBookings.value, "failed");
  const failureRate = totalCount > 0 ? failedCount / totalCount : 0;
  const confidence = totalCount > 0 ? Math.round((1 - failureRate) * 100) : 0;
  let risk = "Low";
  if (failureRate > 0.2) risk = "High";
  else if (failureRate > 0.1) risk = "Medium";
  const status = confidence >= 80 ? "On track" : confidence >= 60 ? "Caution" : "At risk";
  return {
    valueLabel: formatCurrency(chartFooter.value.forecastValue || 0),
    confidence: confidence || 0,
    risk,
    status,
  };
});

const payoutSummary = computed(() => {
  const confirmed = revenueTotals.value.confirmed;
  const pending = revenueTotals.value.pending;
  const total = confirmed + pending;
  const progress = total > 0 ? Math.round((confirmed / total) * 100) : 0;
  const bankTransfers = rangeBookings.value
    .filter((row) => row.status === "completed" && /bank|transfer/i.test(row.paymentMethod))
    .reduce((sum, row) => sum + row.amount, 0);
  const cardRefunds = rangeBookings.value
    .filter((row) => row.status === "failed")
    .reduce((sum, row) => sum + row.amount, 0);
  const platformFees = revenueTotals.value.fees;
  return {
    available: formatCurrency(confirmed - platformFees),
    progress,
    bankTransfers: formatCurrency(bankTransfers),
    cardRefunds: formatCurrency(cardRefunds),
    platformFees: formatCurrency(platformFees),
  };
});

const transactions = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  const rows = rangeBookings.value
    .filter((row) => {
      if (!q) return true;
      return (
        row.id.toLowerCase().includes(q) ||
        row.vendorName.toLowerCase().includes(q) ||
        row.statusLabel.toLowerCase().includes(q)
      );
    })
    .sort((a, b) => (b.date?.getTime() || 0) - (a.date?.getTime() || 0));
  return rows.slice(0, 8);
});

const setRange = (key) => {
  rangeKey.value = key;
};

const setChartMetric = (metric) => {
  chartMetric.value = metric;
};

const openPrintWindow = (title, html) => {
  const popup = window.open("", "_blank", "width=980,height=720");
  if (!popup) {
    throw new Error("Popup blocked. Please allow popups to export the PDF.");
  }

  popup.document.open();
  popup.document.write(`<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>${title}</title>
    <style>
      * { box-sizing: border-box; }
      body { font-family: "Segoe UI", Arial, sans-serif; color: #0f172a; margin: 28px; }
      h1 { font-size: 24px; margin: 0 0 6px; }
      h2 { font-size: 16px; margin: 24px 0 10px; }
      p { margin: 6px 0; color: #475569; }
      .meta { font-size: 12px; color: #64748b; }
      .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
      .card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
      .card strong { display: block; font-size: 16px; margin-top: 6px; }
      table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 12px; }
      th, td { border-bottom: 1px solid #e2e8f0; text-align: left; padding: 8px 6px; }
      th { text-transform: uppercase; letter-spacing: 0.08em; font-size: 10px; color: #64748b; }
      @media print {
        @page { size: A4; margin: 16mm; }
        body { margin: 0; }
      }
    </style>
  </head>
  <body>
    ${html}
    <script>
      window.onload = () => {
        window.focus();
        window.print();
      };
      window.onafterprint = () => window.close();
    <\/script>
  </body>
</html>`);
  popup.document.close();
};

const handleExportReport = async () => {
  if (isExportingReport.value) return;
  isExportingReport.value = true;
  try {
    const now = new Date();
    const meta = `${now.toLocaleDateString()} ${now.toLocaleTimeString()}`;
    const summaryCards = revenueStats.value
      .map(
        (card) => `
        <div class="card">
          <div>${card.label}</div>
          <strong>${card.value}</strong>
          <p class="meta">${card.delta}</p>
        </div>`,
      )
      .join("");
    const rows = transactions.value
      .map(
        (row) => `
        <tr>
          <td>${row.id}</td>
          <td>${row.vendorName}</td>
          <td>${row.statusLabel}</td>
          <td>${row.dateLabel}</td>
          <td>${formatCurrency(row.amount)}</td>
        </tr>`,
      )
      .join("");
    const html = `
      <h1>Revenue Report</h1>
      <p class="meta">Generated at ${meta}</p>
      <p>Range: ${selectedRange.value.label}</p>
      <div class="grid">${summaryCards}</div>
      <h2>Recent Transactions</h2>
      <table>
        <thead>
          <tr>
            <th>Transaction</th>
            <th>Vendor</th>
            <th>Status</th>
            <th>Date</th>
            <th>Amount</th>
          </tr>
        </thead>
        <tbody>
          ${rows || `<tr><td colspan="5">No transactions in this range.</td></tr>`}
        </tbody>
      </table>
    `;
    openPrintWindow("Revenue Report", html);
  } catch (error) {
    console.error(error);
    alert(error?.message || "Unable to export report.");
  } finally {
    isExportingReport.value = false;
  }
};

syncActiveKey();
watch(() => route.query.page, syncActiveKey);
onMounted(() => void adminStore.loadAll());
</script>

<template>
  <section class="admin-shell revenue-shell">
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
            <p class="brand-subtitle">Revenue performance workspace</p>
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
        <div class="hero-copy">
          <p class="eyebrow">Revenue Intelligence</p>
          <h1 class="hero-title">Financial Health Overview</h1>
          <p class="hero-subtitle">Monitor revenue health, platform fees, and profit trends in one place.</p>
          <div class="hero-tags">
            <span class="hero-tag">Automated Payouts</span>
            <span class="hero-tag soft">Fraud Watch</span>
            <span class="hero-tag soft">Daily Updates</span>
          </div>
        </div>
        <div class="hero-actions">
          <div class="range-picker">
            <button
              v-for="option in rangeOptions"
              :key="option.key"
              class="range-pill"
              :class="{ active: rangeKey === option.key }"
              type="button"
              @click="setRange(option.key)"
            >
              {{ option.label }}
            </button>
          </div>
          <button class="primary-btn" type="button" :disabled="isExportingReport" @click="handleExportReport">
            {{ isExportingReport ? "Exporting..." : "Export Report" }}
          </button>
        </div>
      </section>

      <section class="revenue-stats">
        <article v-for="card in revenueStats" :key="card.label" class="stat-card">
          <div class="stat-icon">
            <svg v-if="card.icon === 'revenue'" viewBox="0 0 24 24" aria-hidden="true">
              <path
                d="M4 16.5c3.2-3.6 5.8-5.4 8.6-5.4 2.8 0 4.3 1.5 7.4 1.5v2c-3.5 0-5.2-1.8-7.6-1.8-2.4 0-4.4 1.4-7 4.2H4Z"
              />
              <path d="M5 18.5h14v2H5z" />
              <path d="M9 6.5h6v2H9z" />
            </svg>
            <svg v-else-if="card.icon === 'payouts'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 8h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z" />
              <path d="M8 6h8a2 2 0 0 1 2 2v1H6V8a2 2 0 0 1 2-2z" />
              <path d="M12 12.5v4m0-4-1.8 1.8m1.8-1.8 1.8 1.8" />
            </svg>
            <svg v-else-if="card.icon === 'fees'" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M6 6h12v12H6z" />
              <path d="M9 9h6v2H9zm0 4h4v2H9z" />
              <path d="M4 4h4v2H6v12h2v2H4z" />
            </svg>
            <svg v-else viewBox="0 0 24 24" aria-hidden="true">
              <path d="M5 14c2.4-2.8 4.6-4.2 6.8-4.2 2.2 0 3.6 1.1 6.2 1.1v2.1c-2.8 0-4.5-1.4-6.4-1.4-1.9 0-3.6 1.2-5.8 3.6H5Z" />
              <path d="M5 18h14v2H5z" />
              <path d="M9.5 6.5h5v2h-5z" />
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-meta">
              <p class="stat-label">{{ card.label }}</p>
              <span class="stat-delta">{{ card.delta }}</span>
            </div>
            <p class="stat-value">{{ card.value }}</p>
            <div class="sparkline">
              <span></span><span></span><span></span><span></span><span></span><span></span>
            </div>
          </div>
        </article>
      </section>

      <section class="revenue-grid">
        <article class="card wide chart-card">
          <header>
            <div>
              <h3>Revenue Trends</h3>
              <p class="card-subtitle">Gross vs net performance by month</p>
            </div>
            <div class="pill-group">
              <button
                class="pill"
                :class="{ active: chartMetric === 'gross' }"
                type="button"
                @click="setChartMetric('gross')"
              >
                Gross
              </button>
              <button
                class="pill"
                :class="{ active: chartMetric === 'net' }"
                type="button"
                @click="setChartMetric('net')"
              >
                Net
              </button>
            </div>
          </header>
          <div class="chart-grid">
            <div class="chart-labels">
              <span v-for="label in chartLabels" :key="label">{{ label }}</span>
            </div>
            <div class="bar-chart">
              <span
                v-for="(item, index) in chartSeries"
                :key="`${item.label}-${index}`"
                class="bar"
                :class="{ peak: item.isPeak }"
                :style="{ height: `${item.height}%` }"
                :title="`${item.fullLabel}: ${formatCurrency(item.value)}`"
              ></span>
            </div>
          </div>
          <div class="chart-footer">
            <div>
              <p>Peak period</p>
              <strong>{{ chartFooter.peakLabel }}</strong>
            </div>
            <div>
              <p>Avg. growth</p>
              <strong>{{ chartFooter.avgGrowthLabel }}</strong>
            </div>
            <div>
              <p>Forecast</p>
              <strong>{{ chartFooter.forecastLabel }}</strong>
            </div>
          </div>
        </article>

        <article class="card side payout-card">
          <header class="side-header">
            <div>
              <h3>Revenue Breakdown</h3>
              <p class="card-subtitle">Based on confirmed bookings in range</p>
            </div>
            <span class="status-pill">Ready</span>
          </header>
          <div class="payout-summary">
            <div>
              <p>Available Revenue</p>
              <h4>{{ payoutSummary.available }}</h4>
            </div>
            <div class="payout-progress">
              <span>{{ payoutSummary.progress }}% recognized</span>
              <div class="progress">
                <span class="progress-fill" :style="{ width: `${payoutSummary.progress}%` }"></span>
              </div>
            </div>
          </div>
          <div class="payout-list">
            <div>
              <span>Bank Transfers</span>
              <strong>{{ payoutSummary.bankTransfers }}</strong>
            </div>
            <div>
              <span>Card Refunds</span>
              <strong>{{ payoutSummary.cardRefunds }}</strong>
            </div>
            <div>
              <span>Platform Fees</span>
              <strong class="danger">{{ payoutSummary.platformFees }}</strong>
            </div>
          </div>
          <button class="primary-btn full" type="button">View Revenue Details</button>
        </article>

        <article class="card wide table-card">
          <header>
            <div>
              <h3>Transaction History</h3>
              <p class="card-subtitle">Latest payouts and adjustments</p>
            </div>
            <button class="link-btn" type="button">View All</button>
          </header>
          <div class="table">
            <div class="table-head">
              <span>Transaction</span>
              <span>Vendor</span>
              <span>Date</span>
              <span>Amount</span>
              <span>Status</span>
            </div>
            <div v-if="isLoading" class="table-empty">Loading revenue data...</div>
            <div v-else-if="loadError" class="table-empty">{{ loadError }}</div>
            <div v-else-if="!transactions.length" class="table-empty">No transactions found for this range.</div>
            <template v-else>
              <div v-for="item in transactions" :key="item.id" class="table-row">
                <span>{{ item.id }}</span>
                <span>{{ item.vendorName }}</span>
                <span>{{ item.dateLabel }}</span>
                <span>{{ formatCurrency(item.amount) }}</span>
                <span class="status" :class="item.status">
                  <span class="status-dot"></span>
                  {{ item.statusLabel }}
                </span>
              </div>
            </template>
          </div>
        </article>

        <article class="card side projection">
          <header class="projection-head">
            <div>
              <p class="projection-eyebrow">Forecast</p>
              <h3>Quarterly Projection</h3>
              <p class="projection-sub">Based on current range trends.</p>
            </div>
            <span class="status-pill">{{ projection.status }}</span>
          </header>
          <div class="projection-value">
            <span class="value-label">Expected revenue</span>
            <h4>{{ projection.valueLabel }}</h4>
            <div class="projection-bar" role="presentation">
              <span class="projection-bar-fill" :style="{ width: `${projection.confidence}%` }"></span>
            </div>
            <p class="projection-note">{{ projection.confidence }}% confidence</p>
          </div>
          <div class="projection-grid">
            <div class="projection-metric">
              <span>Confidence</span>
              <strong>{{ projection.confidence }}%</strong>
            </div>
            <div class="projection-metric">
              <span>Risk level</span>
              <strong>{{ projection.risk }}</strong>
            </div>
          </div>
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
  --accent-soft: #fff4ea;
  --accent-cool: #ffa262;
  --surface: rgba(255, 255, 255, 0.86);
  --surface-strong: #ffffff;
  --stroke: rgba(15, 23, 42, 0.08);
  --shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
  --shadow-soft: 0 14px 30px rgba(15, 23, 42, 0.08);
  display: grid;
  grid-template-columns: minmax(300px, 360px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
  background: radial-gradient(circle at 10% 0%, #fff0e5 0%, #f5f2ef 32%, #eef6fb 100%);
  color: var(--ink);
  position: relative;
  overflow: hidden;
}

.revenue-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.2), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.2), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.16), transparent 45%);
  pointer-events: none;
}

.revenue-shell::after {
  content: "";
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
  background-size: 120px 120px;
  opacity: 0.4;
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

.revenue-hero {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.85), rgba(255, 245, 238, 0.6));
  border: 1px solid rgba(15, 23, 42, 0.08);
  padding: 24px 28px;
  border-radius: 26px;
  box-shadow: 0 24px 50px rgba(15, 23, 42, 0.12);
  backdrop-filter: blur(12px);
  position: relative;
  overflow: hidden;
}

.revenue-hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(255, 122, 26, 0.18), transparent 55%);
  pointer-events: none;
}

.hero-copy {
  display: grid;
  gap: 10px;
  position: relative;
  z-index: 1;
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
  font-size: 36px;
  font-family: "Fraunces", serif;
}

.hero-subtitle {
  margin: 8px 0 0;
  color: var(--muted);
}

.hero-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 6px;
}

.hero-tag {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  background: rgba(255, 122, 26, 0.12);
  color: #b44a10;
}

.hero-tag.soft {
  background: rgba(15, 23, 42, 0.06);
  color: #475569;
}

.hero-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  position: relative;
  z-index: 1;
  flex-wrap: wrap;
}

.range-picker {
  display: inline-flex;
  gap: 6px;
  padding: 6px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.range-pill {
  border: none;
  background: transparent;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  color: var(--muted);
  cursor: pointer;
}

.range-pill.active {
  background: #fff;
  color: var(--accent-strong);
  box-shadow: var(--shadow-soft);
}

.revenue-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}

.stat-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 16px 18px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  position: relative;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: grid;
  grid-template-columns: 42px 1fr;
  gap: 14px;
}

.stat-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.14), transparent 55%);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.2), rgba(255, 122, 26, 0.05));
  display: grid;
  place-items: center;
  border: 1px solid rgba(255, 122, 26, 0.2);
  color: #f97316;
}

.stat-icon svg {
  width: 18px;
  height: 18px;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.7;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.stat-card:nth-child(1) .stat-icon {
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.22), rgba(255, 122, 26, 0.05));
  color: #f97316;
}

.stat-card:nth-child(2) .stat-icon {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.22), rgba(59, 130, 246, 0.06));
  color: #3b82f6;
  border-color: rgba(59, 130, 246, 0.22);
}

.stat-card:nth-child(3) .stat-icon {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.22), rgba(16, 185, 129, 0.06));
  color: #10b981;
  border-color: rgba(16, 185, 129, 0.22);
}

.stat-card:nth-child(4) .stat-icon {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.22), rgba(249, 115, 22, 0.06));
  color: #f97316;
  border-color: rgba(249, 115, 22, 0.22);
}

.stat-body {
  display: grid;
  gap: 6px;
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

.card-subtitle {
  margin: 4px 0 0;
  font-size: 13px;
  color: var(--muted);
}

.chart-card {
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.95), rgba(255, 246, 238, 0.7));
}

.chart-grid {
  display: grid;
  grid-template-columns: 60px 1fr;
  gap: 12px;
  align-items: stretch;
}

.chart-labels {
  display: grid;
  gap: 24px;
  font-size: 12px;
  color: var(--muted);
  padding: 8px 0;
}

.chart-footer {
  margin-top: 16px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  padding-top: 12px;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
  font-size: 12px;
  color: var(--muted);
}

.chart-footer strong {
  display: block;
  margin-top: 4px;
  font-size: 14px;
  color: var(--ink);
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
  position: relative;
  padding: 6px 0 12px;
}

.bar {
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.1), rgba(15, 23, 42, 0.02));
  border-radius: 999px;
  height: 40%;
}

.bar.peak {
  background: linear-gradient(180deg, #ff7a1a, #f15b2a);
  box-shadow: 0 10px 24px rgba(241, 91, 42, 0.35);
}


.payout-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.9);
  padding: 14px;
  border-radius: 16px;
  margin: 12px 0;
  border: 1px solid rgba(15, 23, 42, 0.08);
  gap: 12px;
  flex-wrap: wrap;
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

.side-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.payout-card {
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.92), rgba(255, 246, 238, 0.75));
}

.payout-progress {
  min-width: 140px;
  font-size: 12px;
  color: var(--muted);
  display: grid;
  gap: 6px;
}

.progress {
  height: 8px;
  background: rgba(15, 23, 42, 0.08);
  border-radius: 999px;
  overflow: hidden;
}

.progress-fill {
  display: block;
  height: 100%;
  width: 76%;
  background: linear-gradient(90deg, #ff7a1a, #f15b2a);
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

.table-empty {
  padding: 16px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.9);
  border: 1px dashed rgba(15, 23, 42, 0.12);
  color: var(--muted);
  font-size: 13px;
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
  align-items: center;
  gap: 6px;
  justify-content: center;
  font-weight: 600;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
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
  position: relative;
  overflow: hidden;
  background: linear-gradient(160deg, #ff7a1a 0%, #f15b2a 45%, #ff9a4d 100%);
  color: #fff;
  border: none;
  box-shadow: 0 26px 60px rgba(241, 91, 42, 0.32);
}

.projection::before {
  content: "";
  position: absolute;
  inset: -30% -10% 35% 20%;
  background: radial-gradient(circle at top, rgba(255, 255, 255, 0.4), transparent 60%);
  opacity: 0.7;
  pointer-events: none;
}

.projection::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent 60%);
  pointer-events: none;
}

.projection > * {
  position: relative;
  z-index: 1;
}

.projection-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.projection-eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 10px;
  font-weight: 600;
  opacity: 0.7;
}

.projection-sub {
  margin: 4px 0 0;
  font-size: 12px;
  opacity: 0.85;
}

.projection .status-pill {
  background: rgba(255, 255, 255, 0.22);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.35);
}

.projection-value {
  margin: 14px 0;
  padding: 12px 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.28);
}

.projection-value h4 {
  font-size: 28px;
  margin: 6px 0 10px;
  font-weight: 700;
}

.value-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  opacity: 0.7;
}

.projection-bar {
  height: 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.28);
  overflow: hidden;
}

.projection-bar-fill {
  display: block;
  height: 100%;
  background: linear-gradient(90deg, #fff1df, #ffffff);
  border-radius: inherit;
}

.projection-note {
  margin: 8px 0 0;
  font-size: 12px;
  opacity: 0.75;
}

.projection-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.projection-metric {
  padding: 10px 12px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.22);
  font-size: 12px;
}

.projection-metric strong {
  display: block;
  margin-top: 6px;
  font-size: 14px;
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
    padding-bottom: 4px;
  }

  .nav-item {
    min-width: 220px;
  }

  .revenue-hero {
    flex-direction: column;
    align-items: flex-start;
  }

  .chart-grid {
    grid-template-columns: 1fr;
  }

  .chart-labels {
    grid-template-columns: repeat(4, minmax(0, 1fr));
    grid-auto-flow: column;
    gap: 8px;
    order: 2;
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

  .chart-footer {
    grid-template-columns: 1fr;
  }

  .hero-actions {
    width: 100%;
  }

  .range-picker {
    width: 100%;
    justify-content: space-between;
  }
}
</style>

