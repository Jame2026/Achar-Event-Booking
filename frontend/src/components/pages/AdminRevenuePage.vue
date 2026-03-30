<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { serviceFeeRate } from "../../features/appData";
import { useAdminDataStore } from "../../features/useAdminDataStore";
import { useLanguageCopy } from "../../features/language";

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

const copyByLanguage = {
  en: {
    nav: {
      dashboard: "Dashboard",
      events: "Events",
      bookings: "Bookings",
      vendors: "Vendors",
      customers: "Customers",
      revenue: "Revenue",
      settings: "Settings",
    },
    brandKicker: "Operations Console",
    brandTitle: "Achar Admin",
    brandSubtitle: "Revenue performance workspace",
    navigation: "Navigation",
    adminModules: "Admin modules",
    searchPlaceholder: "Search revenue records...",
    notifications: "Notifications",
    help: "Help",
    heroEyebrow: "Revenue Intelligence",
    heroTitle: "Financial Health Overview",
    heroSubtitle: "Monitor revenue health, platform fees, and profit trends in one place.",
    automatedPayouts: "Automated Payouts",
    fraudWatch: "Fraud Watch",
    dailyUpdates: "Daily Updates",
    last30Days: "Last 30 Days",
    quarter: "Quarter",
    year: "Year",
    exportReport: "Export Report",
    totalRevenue: "Total Revenue",
    pendingPayouts: "Pending Payouts",
    platformFees: "Platform Fees",
    netProfit: "Net Profit",
    activeCount: "{count} active",
    feeLabel: "{percent}% fee",
    newBadge: "New",
    revenueTrends: "Revenue Trends",
    grossVsNet: "Gross vs net performance by month",
    gross: "Gross",
    net: "Net",
    peakPeriod: "Peak period",
    avgGrowth: "Avg. growth",
    forecast: "Forecast",
    revenueBreakdown: "Revenue Breakdown",
    confirmedBookingsRange: "Based on confirmed bookings in range",
    ready: "Ready",
    availableRevenue: "Available Revenue",
    recognized: "{percent}% recognized",
    bankTransfers: "Bank Transfers",
    cardRefunds: "Card Refunds",
    viewRevenueDetails: "View Revenue Details",
    transactionHistory: "Transaction History",
    latestPayouts: "Latest payouts and adjustments",
    viewAll: "View All",
    transaction: "Transaction",
    vendor: "Vendor",
    date: "Date",
    amount: "Amount",
    status: "Status",
    expectedRevenue: "Expected revenue",
    confidenceLabel: "Confidence",
    riskLevel: "Risk level",
    loadingRevenue: "Loading revenue data...",
    noTransactions: "No transactions found for this range.",
    forecastEyebrow: "Forecast",
    quarterlyProjection: "Quarterly Projection",
    currentRangeTrends: "Based on current range trends.",
    confidence: "{percent}% confidence",
    dateTbd: "Date TBD",
    completed: "Completed",
    failed: "Failed",
    pending: "Pending",
    vendorFallback: "Vendor",
    onTrack: "On track",
    caution: "Caution",
    atRisk: "At risk",
    low: "Low",
    medium: "Medium",
    high: "High",
  },
  zh: {
    nav: {
      dashboard: "仪表盘",
      events: "活动",
      bookings: "预订",
      vendors: "商家",
      customers: "客户",
      revenue: "收入",
      settings: "设置",
    },
    brandKicker: "运营控制台",
    brandTitle: "Achar Admin",
    brandSubtitle: "收入表现工作区",
    navigation: "导航",
    adminModules: "管理员模块",
    searchPlaceholder: "搜索收入记录...",
    notifications: "通知",
    help: "帮助",
    heroEyebrow: "收入洞察",
    heroTitle: "财务健康总览",
    heroSubtitle: "在一个页面中监控收入健康、平台费用和利润趋势。",
    automatedPayouts: "自动结算",
    fraudWatch: "欺诈监测",
    dailyUpdates: "每日更新",
    last30Days: "最近 30 天",
    quarter: "季度",
    year: "全年",
    exportReport: "导出报告",
    totalRevenue: "总收入",
    pendingPayouts: "待结算款项",
    platformFees: "平台费用",
    netProfit: "净利润",
    activeCount: "{count} 笔处理中",
    feeLabel: "{percent}% 费率",
    newBadge: "新增",
    revenueTrends: "收入趋势",
    grossVsNet: "按月查看总额与净额表现",
    gross: "总额",
    net: "净额",
    peakPeriod: "峰值周期",
    avgGrowth: "平均增长",
    forecast: "预测",
    revenueBreakdown: "收入拆分",
    confirmedBookingsRange: "基于所选范围内已确认的预订",
    ready: "已就绪",
    availableRevenue: "可用收入",
    recognized: "已确认 {percent}%",
    bankTransfers: "银行转账",
    cardRefunds: "银行卡退款",
    viewRevenueDetails: "查看收入详情",
    transactionHistory: "交易记录",
    latestPayouts: "最新结算与调整",
    viewAll: "查看全部",
    transaction: "交易",
    vendor: "商家",
    date: "日期",
    amount: "金额",
    status: "状态",
    expectedRevenue: "预期收入",
    confidenceLabel: "置信度",
    riskLevel: "风险等级",
    loadingRevenue: "正在加载收入数据...",
    noTransactions: "此范围内没有交易记录。",
    forecastEyebrow: "预测",
    quarterlyProjection: "季度预测",
    currentRangeTrends: "基于当前范围趋势。",
    confidence: "置信度 {percent}%",
    dateTbd: "日期待定",
    completed: "已完成",
    failed: "失败",
    pending: "待处理",
    vendorFallback: "商家",
    onTrack: "进展正常",
    caution: "需要留意",
    atRisk: "存在风险",
    low: "低",
    medium: "中",
    high: "高",
  },
};
copyByLanguage.km = {
  ...copyByLanguage.en,
  nav: {
    dashboard: "ផ្ទាំងគ្រប់គ្រង",
    events: "ព្រឹត្តិការណ៍",
    bookings: "ការកក់",
    vendors: "អ្នកផ្គត់ផ្គង់",
    customers: "អតិថិជន",
    revenue: "ចំណូល",
    settings: "ការកំណត់",
  },
  brandKicker: "ផ្ទាំងប្រតិបត្តិការ",
  brandTitle: "Achar Admin",
  brandSubtitle: "កន្លែងធ្វើការវិភាគចំណូល",
  navigation: "ការរុករក",
  adminModules: "មុខងារអ្នកគ្រប់គ្រង",
  searchPlaceholder: "ស្វែងរកកំណត់ត្រាចំណូល...",
  notifications: "ការជូនដំណឹង",
  help: "ជំនួយ",
  heroEyebrow: "ការវិភាគចំណូល",
  heroTitle: "ទិដ្ឋភាពទូទៅសុខភាពហិរញ្ញវត្ថុ",
  heroSubtitle: "តាមដានសុខភាពចំណូល ថ្លៃសេវាវេទិកា និងនិន្នាការប្រាក់ចំណេញនៅកន្លែងតែមួយ។",
  automatedPayouts: "ការទូទាត់ស្វ័យប្រវត្តិ",
  fraudWatch: "ការតាមដានការក្លែងបន្លំ",
  dailyUpdates: "បច្ចុប្បន្នភាពប្រចាំថ្ងៃ",
  last30Days: "30 ថ្ងៃចុងក្រោយ",
  quarter: "ត្រីមាស",
  year: "ឆ្នាំ",
  exportReport: "នាំចេញរបាយការណ៍",
  totalRevenue: "ចំណូលសរុប",
  pendingPayouts: "ការទូទាត់ដែលកំពុងរង់ចាំ",
  platformFees: "ថ្លៃសេវាវេទិកា",
  netProfit: "ប្រាក់ចំណេញសុទ្ធ",
  activeCount: "សកម្ម {count}",
  feeLabel: "ថ្លៃ {percent}%",
  newBadge: "ថ្មី",
  revenueTrends: "និន្នាការចំណូល",
  grossVsNet: "ការប្រៀបធៀបចំណូលសរុប និងសុទ្ធតាមខែ",
  gross: "សរុប",
  net: "សុទ្ធ",
  peakPeriod: "រយៈពេលខ្ពស់បំផុត",
  avgGrowth: "កំណើនមធ្យម",
  forecast: "ការព្យាករណ៍",
  revenueBreakdown: "ការបែងចែកចំណូល",
  confirmedBookingsRange: "ផ្អែកលើការកក់ដែលបានបញ្ជាក់ក្នុងចន្លោះនេះ",
  ready: "រួចរាល់",
  availableRevenue: "ចំណូលដែលអាចប្រើបាន",
  recognized: "បានទទួលស្គាល់ {percent}%",
  bankTransfers: "ការផ្ទេរតាមធនាគារ",
  cardRefunds: "ការសងប្រាក់វិញតាមកាត",
  viewRevenueDetails: "មើលព័ត៌មានចំណូលលម្អិត",
  transactionHistory: "ប្រវត្តិប្រតិបត្តិការ",
  latestPayouts: "ការទូទាត់ និងការកែតម្រូវចុងក្រោយ",
  viewAll: "មើលទាំងអស់",
  transaction: "ប្រតិបត្តិការ",
  vendor: "អ្នកផ្គត់ផ្គង់",
  date: "កាលបរិច្ឆេទ",
  amount: "ចំនួនទឹកប្រាក់",
  status: "ស្ថានភាព",
  expectedRevenue: "ចំណូលដែលរំពឹងទុក",
  confidenceLabel: "កម្រិតជឿជាក់",
  riskLevel: "កម្រិតហានិភ័យ",
  loadingRevenue: "កំពុងផ្ទុកទិន្នន័យចំណូល...",
  noTransactions: "មិនមានប្រតិបត្តិការសម្រាប់ចន្លោះនេះទេ។",
  forecastEyebrow: "ការព្យាករណ៍",
  quarterlyProjection: "ការព្យាករណ៍ត្រីមាស",
  currentRangeTrends: "ផ្អែកលើនិន្នាការនៃចន្លោះបច្ចុប្បន្ន។",
  confidence: "ភាពជឿជាក់ {percent}%",
  dateTbd: "កាលបរិច្ឆេទមិនទាន់កំណត់",
  completed: "បានបញ្ចប់",
  failed: "បរាជ័យ",
  pending: "រង់ចាំ",
  vendorFallback: "អ្នកផ្គត់ផ្គង់",
  onTrack: "ដំណើរការល្អ",
  caution: "ប្រយ័ត្ន",
  atRisk: "មានហានិភ័យ",
  low: "ទាប",
  medium: "មធ្យម",
  high: "ខ្ពស់",
};

const { language, uiText } = useLanguageCopy(copyByLanguage);
const activeLocale = computed(() => (language.value === "zh" ? "zh-CN" : language.value === "km" ? "km-KH" : "en-US"));
const interpolate = (template, values = {}) =>
  String(template || "").replace(/\{(\w+)\}/g, (_, key) => String(values[key] ?? ""));

const router = useRouter();
const route = useRoute();
const searchQuery = ref("");
const navItems = computed(() => [
  { key: "dashboard", label: uiText.value.nav.dashboard, icon: "dashboard" },
  { key: "events", label: uiText.value.nav.events, icon: "events" },
  { key: "admin-bookings", label: uiText.value.nav.bookings, icon: "bookings" },
  { key: "vendors", label: uiText.value.nav.vendors, icon: "vendors" },
  { key: "customers", label: uiText.value.nav.customers, icon: "users" },
  { key: "revenue", label: uiText.value.nav.revenue, icon: "revenue" },
  { key: "settings", label: uiText.value.nav.settings, icon: "settings" },
]);
const activeKey = ref("revenue");
const adminStore = useAdminDataStore();
const isLoading = computed(() => adminStore.loading.all || adminStore.loading.bookings);
const loadError = computed(() => adminStore.errors.bookings);
const rawBookings = computed(() => adminStore.state.bookings);
const rangeKey = ref("30d");
const chartMetric = ref("gross");
const rangeOptions = computed(() => [
  { key: "30d", label: uiText.value.last30Days, days: 30 },
  { key: "quarter", label: uiText.value.quarter, days: 90 },
  { key: "year", label: uiText.value.year, days: 365 },
]);

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
  return new Intl.NumberFormat(activeLocale.value, {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
  }).format(Number.isFinite(amount) ? amount : 0);
};

const formatCompactCurrency = (value) => {
  const amount = Number(value || 0);
  if (!Number.isFinite(amount)) return "$0";
  return new Intl.NumberFormat(activeLocale.value, {
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
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return uiText.value.dateTbd;
  return date.toLocaleDateString(activeLocale.value, {
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
    uiText.value.vendorFallback;
  const amount = Number(row?.total_amount || 0);
  const status = normalizeStatus(row);
  return {
    id: row?.id ? `#TXN-${row.id}` : "#TXN",
    vendorName,
    amount,
    status,
    statusLabel: status === "completed" ? uiText.value.completed : status === "failed" ? uiText.value.failed : uiText.value.pending,
    date,
    dateLabel: date ? formatDateLabel(date) : uiText.value.dateTbd,
    paymentMethod: String(row?.payment_method || ""),
  };
};

const selectedRange = computed(() => rangeOptions.value.find((item) => item.key === rangeKey.value) || rangeOptions.value[0]);

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
  if (!previous) return uiText.value.newBadge;
  const delta = ((current - previous) / Math.abs(previous)) * 100;
  const sign = delta >= 0 ? "+" : "";
  return `${sign}${delta.toFixed(1)}%`;
};

const revenueStats = computed(() => [
  {
    label: uiText.value.totalRevenue,
    value: formatCurrency(revenueTotals.value.confirmed),
    delta: formatDelta(revenueTotals.value.confirmed, previousRevenueTotals.value.confirmed),
  },
  {
    label: uiText.value.pendingPayouts,
    value: formatCurrency(revenueTotals.value.pending),
    delta: interpolate(uiText.value.activeCount, { count: revenueTotals.value.pendingCount }),
  },
  {
    label: uiText.value.platformFees,
    value: formatCurrency(revenueTotals.value.fees),
    delta: interpolate(uiText.value.feeLabel, { percent: (serviceFeeRate * 100).toFixed(1) }),
  },
  {
    label: uiText.value.netProfit,
    value: formatCurrency(revenueTotals.value.net),
    delta: formatDelta(revenueTotals.value.net, previousRevenueTotals.value.net),
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
      label: bucketStart.toLocaleDateString(activeLocale.value, {
        month: "short",
        day: "2-digit",
      }),
      fullLabel: bucketStart.toLocaleDateString(activeLocale.value, {
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
  let risk = uiText.value.low;
  if (failureRate > 0.2) risk = uiText.value.high;
  else if (failureRate > 0.1) risk = uiText.value.medium;
  const status = confidence >= 80 ? uiText.value.onTrack : confidence >= 60 ? uiText.value.caution : uiText.value.atRisk;
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
            <p class="brand-kicker">{{ uiText.brandKicker }}</p>
            <p class="brand-title">{{ uiText.brandTitle }}</p>
            <p class="brand-subtitle">{{ uiText.brandSubtitle }}</p>
          </div>
        </div>
      </div>

      <section class="sidebar-block">
        <div class="sidebar-block-head">
          <span class="sidebar-section-label">{{ uiText.navigation }}</span>
          <span class="sidebar-section-caption">{{ uiText.adminModules }}</span>
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
          <input v-model="searchQuery" type="search" :placeholder="uiText.searchPlaceholder" />
        </label>
        <div class="topbar-actions">
          <button class="icon-btn" type="button" :title="uiText.notifications" :aria-label="uiText.notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <button class="icon-btn" type="button" :title="uiText.help" :aria-label="uiText.help">
            <svg viewBox="0 0 24 24">
              <path d="M12 19a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 19Zm0-15a6 6 0 0 1 6 6c0 2.455-1.812 3.498-2.83 4.085-.87.505-1.17.75-1.17 1.415v.5h-2v-.5c0-1.86 1.126-2.512 2.17-3.115C14.98 11.83 16 11.235 16 10a4 4 0 0 0-8 0H6a6 6 0 0 1 6-6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="revenue-hero">
        <div class="hero-copy">
          <p class="eyebrow">{{ uiText.heroEyebrow }}</p>
          <h1 class="hero-title">{{ uiText.heroTitle }}</h1>
          <p class="hero-subtitle">{{ uiText.heroSubtitle }}</p>
          <div class="hero-tags">
            <span class="hero-tag">{{ uiText.automatedPayouts }}</span>
            <span class="hero-tag soft">{{ uiText.fraudWatch }}</span>
            <span class="hero-tag soft">{{ uiText.dailyUpdates }}</span>
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
          <button class="primary-btn" type="button">{{ uiText.exportReport }}</button>
        </div>
      </section>

      <section class="revenue-stats">
        <article v-for="card in revenueStats" :key="card.label" class="stat-card">
          <div class="stat-icon">
            <span></span>
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
              <h3>{{ uiText.revenueTrends }}</h3>
              <p class="card-subtitle">{{ uiText.grossVsNet }}</p>
            </div>
            <div class="pill-group">
              <button
                class="pill"
                :class="{ active: chartMetric === 'gross' }"
                type="button"
                @click="setChartMetric('gross')"
              >
                {{ uiText.gross }}
              </button>
              <button
                class="pill"
                :class="{ active: chartMetric === 'net' }"
                type="button"
                @click="setChartMetric('net')"
              >
                {{ uiText.net }}
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
              <p>{{ uiText.peakPeriod }}</p>
              <strong>{{ chartFooter.peakLabel }}</strong>
            </div>
            <div>
              <p>{{ uiText.avgGrowth }}</p>
              <strong>{{ chartFooter.avgGrowthLabel }}</strong>
            </div>
            <div>
              <p>{{ uiText.forecast }}</p>
              <strong>{{ chartFooter.forecastLabel }}</strong>
            </div>
          </div>
        </article>

        <article class="card side payout-card">
          <header class="side-header">
            <div>
              <h3>{{ uiText.revenueBreakdown }}</h3>
              <p class="card-subtitle">{{ uiText.confirmedBookingsRange }}</p>
            </div>
            <span class="status-pill">{{ uiText.ready }}</span>
          </header>
          <div class="payout-summary">
            <div>
              <p>{{ uiText.availableRevenue }}</p>
              <h4>{{ payoutSummary.available }}</h4>
            </div>
            <div class="payout-progress">
              <span>{{ interpolate(uiText.recognized, { percent: payoutSummary.progress }) }}</span>
              <div class="progress">
                <span class="progress-fill" :style="{ width: `${payoutSummary.progress}%` }"></span>
              </div>
            </div>
          </div>
          <div class="payout-list">
            <div>
              <span>{{ uiText.bankTransfers }}</span>
              <strong>{{ payoutSummary.bankTransfers }}</strong>
            </div>
            <div>
              <span>{{ uiText.cardRefunds }}</span>
              <strong>{{ payoutSummary.cardRefunds }}</strong>
            </div>
            <div>
              <span>{{ uiText.platformFees }}</span>
              <strong class="danger">{{ payoutSummary.platformFees }}</strong>
            </div>
          </div>
          <button class="primary-btn full" type="button">{{ uiText.viewRevenueDetails }}</button>
        </article>

        <article class="card wide table-card">
          <header>
            <div>
              <h3>{{ uiText.transactionHistory }}</h3>
              <p class="card-subtitle">{{ uiText.latestPayouts }}</p>
            </div>
            <button class="link-btn" type="button">{{ uiText.viewAll }}</button>
          </header>
          <div class="table">
            <div class="table-head">
              <span>{{ uiText.transaction }}</span>
              <span>{{ uiText.vendor }}</span>
              <span>{{ uiText.date }}</span>
              <span>{{ uiText.amount }}</span>
              <span>{{ uiText.status }}</span>
            </div>
            <div v-if="isLoading" class="table-empty">{{ uiText.loadingRevenue }}</div>
            <div v-else-if="loadError" class="table-empty">{{ loadError }}</div>
            <div v-else-if="!transactions.length" class="table-empty">{{ uiText.noTransactions }}</div>
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
              <p class="projection-eyebrow">{{ uiText.forecastEyebrow }}</p>
              <h3>{{ uiText.quarterlyProjection }}</h3>
              <p class="projection-sub">{{ uiText.currentRangeTrends }}</p>
            </div>
            <span class="status-pill">{{ projection.status }}</span>
          </header>
          <div class="projection-value">
            <span class="value-label">{{ uiText.expectedRevenue }}</span>
            <h4>{{ projection.valueLabel }}</h4>
            <div class="projection-bar" role="presentation">
              <span class="projection-bar-fill" :style="{ width: `${projection.confidence}%` }"></span>
            </div>
            <p class="projection-note">{{ interpolate(uiText.confidence, { percent: projection.confidence }) }}</p>
          </div>
          <div class="projection-grid">
            <div class="projection-metric">
              <span>{{ uiText.confidenceLabel }}</span>
              <strong>{{ projection.confidence }}%</strong>
            </div>
            <div class="projection-metric">
              <span>{{ uiText.riskLevel }}</span>
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
  background: radial-gradient(circle at 8% 0%, #fff1e6 0%, #f7f2ee 35%, #eef6f9 100%);
  color: var(--ink);
  position: relative;
  overflow: hidden;
}

.revenue-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.revenue-shell::after {
  content: none;
}

.revenue-shell > * {
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
}

.stat-icon span {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: var(--accent);
  box-shadow: 0 6px 14px rgba(241, 91, 42, 0.4);
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

