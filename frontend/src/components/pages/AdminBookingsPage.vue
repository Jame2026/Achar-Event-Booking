<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { formatDateTime, summarizeBookedServices } from "../../features/bookingMappers";
import { apiGet } from "../../features/apiClient";
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
    brandSubtitle: "Customer booking workspace",
    navigation: "Navigation",
    adminModules: "Admin modules",
    searchPlaceholder: "Search bookings, attendees or events...",
    refreshList: "Refresh List",
    notifications: "Notifications",
    heroEyebrow: "Booking Management",
    heroTitle: "Customer Bookings for Vendor Services",
    heroSubtitle: "Review the real bookings customers placed for vendor services and packages.",
    all: "All",
    pending: "Pending",
    confirmed: "Confirmed",
    cancelled: "Cancelled",
    totalBookings: "Total Bookings",
    pendingConfirmation: "Pending Confirmation",
    confirmedRevenue: "Confirmed Revenue",
    confirmedCount: "{count} confirmed",
    needsAttention: "Needs attention",
    allCaughtUp: "All caught up",
    cancelledCount: "{count} cancelled",
    customerBookingList: "Customer Booking List",
    bookingSummary: "{shown} of {total} booking(s) shown",
    packageCount: "{count} package",
    serviceCount: "{count} service",
    loadingBookings: "Loading customer bookings...",
    noBookingsMatch: "No bookings matched this filter.",
    qty: "Qty {count}",
    bookingDetail: "Booking Detail",
    selectedBooking: "Selected Booking",
    email: "Email",
    phone: "Phone",
    location: "Location",
    notProvided: "Not provided",
    total: "Total",
    quantity: "Quantity",
    vendor: "Vendor",
    vendorEmail: "Vendor Email",
    eventDate: "Event Date",
    time: "Time",
    bookingType: "Booking Type",
    payment: "Payment",
    bookedItems: "Booked items",
    item: "Item",
    noBookingSelected: "No Booking Selected",
    noBookingSelectedSubtitle: "Select a booking from the list to inspect its customer and vendor details here.",
    summaryTitle: "Booking Summary",
    summarySubtitle: "Live totals across vendor services and package orders in the platform.",
    packageOrders: "Package Orders",
    serviceOrders: "Service Orders",
    revenue: "Revenue",
    noContactProvided: "No contact provided",
    vendorFallback: "Vendor",
    guestCustomer: "Guest Customer",
    serviceBooking: "Service Booking",
    package: "Package",
    service: "Service",
    unpaid: "Unpaid",
    paid: "Paid",
    refunded: "Refunded",
    failed: "Failed",
    unknown: "Unknown",
    timeTbd: "Time TBD",
    allDay: "All day",
    couldNotLoad: "Could not load customer bookings.",
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
    brandSubtitle: "客户预订工作区",
    navigation: "导航",
    adminModules: "管理员模块",
    searchPlaceholder: "搜索预订、客户或活动...",
    refreshList: "刷新列表",
    notifications: "通知",
    heroEyebrow: "预订管理",
    heroTitle: "商家服务客户预订",
    heroSubtitle: "查看客户为商家服务和套餐提交的真实预订。",
    all: "全部",
    pending: "待确认",
    confirmed: "已确认",
    cancelled: "已取消",
    totalBookings: "预订总数",
    pendingConfirmation: "待确认预订",
    confirmedRevenue: "已确认收入",
    confirmedCount: "{count} 已确认",
    needsAttention: "需要处理",
    allCaughtUp: "已全部处理",
    cancelledCount: "{count} 已取消",
    customerBookingList: "客户预订列表",
    bookingSummary: "显示 {shown} / 共 {total} 条预订",
    packageCount: "{count} 套餐",
    serviceCount: "{count} 服务",
    loadingBookings: "正在加载客户预订...",
    noBookingsMatch: "没有符合当前筛选条件的预订。",
    qty: "数量 {count}",
    bookingDetail: "预订详情",
    selectedBooking: "已选预订",
    email: "邮箱",
    phone: "电话",
    location: "位置",
    notProvided: "未提供",
    total: "总额",
    quantity: "数量",
    vendor: "商家",
    vendorEmail: "商家邮箱",
    eventDate: "活动日期",
    time: "时间",
    bookingType: "预订类型",
    payment: "支付",
    bookedItems: "已预订项目",
    item: "项目",
    noBookingSelected: "未选择预订",
    noBookingSelectedSubtitle: "从列表中选择一条预订，以在此查看客户和商家详情。",
    summaryTitle: "预订摘要",
    summarySubtitle: "平台内商家服务与套餐订单的实时统计。",
    packageOrders: "套餐订单",
    serviceOrders: "服务订单",
    revenue: "收入",
    noContactProvided: "未提供联系方式",
    vendorFallback: "商家",
    guestCustomer: "游客客户",
    serviceBooking: "服务预订",
    package: "套餐",
    service: "服务",
    unpaid: "未支付",
    paid: "已支付",
    refunded: "已退款",
    failed: "失败",
    unknown: "未知",
    timeTbd: "时间待定",
    allDay: "全天",
    couldNotLoad: "无法加载客户预订。",
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
  brandSubtitle: "កន្លែងធ្វើការគ្រប់គ្រងការកក់អតិថិជន",
  navigation: "ការរុករក",
  adminModules: "មុខងារអ្នកគ្រប់គ្រង",
  searchPlaceholder: "ស្វែងរកការកក់ អ្នកចូលរួម ឬព្រឹត្តិការណ៍...",
  refreshList: "ផ្ទុកបញ្ជីឡើងវិញ",
  notifications: "ការជូនដំណឹង",
  heroEyebrow: "ការគ្រប់គ្រងការកក់",
  heroTitle: "ការកក់របស់អតិថិជនសម្រាប់សេវារបស់អ្នកផ្គត់ផ្គង់",
  heroSubtitle: "ពិនិត្យមើលការកក់ពិតប្រាកដដែលអតិថិជនបានធ្វើសម្រាប់សេវា និងកញ្ចប់របស់អ្នកផ្គត់ផ្គង់។",
  all: "ទាំងអស់",
  pending: "រង់ចាំ",
  confirmed: "បានបញ្ជាក់",
  cancelled: "បានបោះបង់",
  totalBookings: "ការកក់សរុប",
  pendingConfirmation: "ការកក់រង់ចាំការបញ្ជាក់",
  confirmedRevenue: "ចំណូលដែលបានបញ្ជាក់",
  confirmedCount: "{count} បានបញ្ជាក់",
  needsAttention: "ត្រូវការការយកចិត្តទុកដាក់",
  allCaughtUp: "បានដោះស្រាយរួចរាល់",
  cancelledCount: "{count} បានបោះបង់",
  customerBookingList: "បញ្ជីការកក់អតិថិជន",
  bookingSummary: "បង្ហាញ {shown} នៃ {total} ការកក់",
  packageCount: "កញ្ចប់ {count}",
  serviceCount: "សេវា {count}",
  loadingBookings: "កំពុងផ្ទុកការកក់អតិថិជន...",
  noBookingsMatch: "មិនមានការកក់ត្រូវនឹងតម្រងនេះទេ។",
  qty: "ចំនួន {count}",
  bookingDetail: "ព័ត៌មានការកក់",
  selectedBooking: "ការកក់ដែលបានជ្រើស",
  email: "អ៊ីមែល",
  phone: "ទូរស័ព្ទ",
  location: "ទីតាំង",
  notProvided: "មិនបានផ្តល់",
  total: "សរុប",
  quantity: "បរិមាណ",
  vendor: "អ្នកផ្គត់ផ្គង់",
  vendorEmail: "អ៊ីមែលអ្នកផ្គត់ផ្គង់",
  eventDate: "កាលបរិច្ឆេទព្រឹត្តិការណ៍",
  time: "ម៉ោង",
  bookingType: "ប្រភេទការកក់",
  payment: "ការទូទាត់",
  bookedItems: "មុខទំនិញដែលបានកក់",
  item: "មុខទំនិញ",
  noBookingSelected: "មិនទាន់ជ្រើសការកក់",
  noBookingSelectedSubtitle: "ជ្រើសការកក់មួយពីបញ្ជី ដើម្បីមើលព័ត៌មានអតិថិជន និងអ្នកផ្គត់ផ្គង់នៅទីនេះ។",
  summaryTitle: "សេចក្តីសង្ខេបការកក់",
  summarySubtitle: "សរុបបច្ចុប្បន្ននៃសេវា និងការបញ្ជាទិញកញ្ចប់របស់អ្នកផ្គត់ផ្គង់ក្នុងប្រព័ន្ធ។",
  packageOrders: "ការបញ្ជាទិញកញ្ចប់",
  serviceOrders: "ការបញ្ជាទិញសេវា",
  revenue: "ចំណូល",
  noContactProvided: "មិនបានផ្តល់ព័ត៌មានទំនាក់ទំនង",
  vendorFallback: "អ្នកផ្គត់ផ្គង់",
  guestCustomer: "អតិថិជនភ្ញៀវ",
  serviceBooking: "ការកក់សេវា",
  package: "កញ្ចប់",
  service: "សេវា",
  unpaid: "មិនទាន់បង់",
  paid: "បានបង់",
  refunded: "បានសងប្រាក់វិញ",
  failed: "បរាជ័យ",
  unknown: "មិនស្គាល់",
  timeTbd: "ម៉ោងមិនទាន់កំណត់",
  allDay: "ពេញមួយថ្ងៃ",
  couldNotLoad: "មិនអាចផ្ទុកការកក់អតិថិជនបានទេ។",
};

const { language, uiText } = useLanguageCopy(copyByLanguage);
const activeLocale = computed(() => (language.value === "zh" ? "zh-CN" : language.value === "km" ? "km-KH" : "en-US"));
const interpolate = (template, values = {}) =>
  String(template || "").replace(/\{(\w+)\}/g, (_, key) => String(values[key] ?? ""));

const router = useRouter();
const route = useRoute();
const searchQuery = ref("");
const statusFilter = ref("all");
const isLoading = ref(false);
const loadError = ref("");
const adminBookings = ref([]);
const selectedBookingId = ref(null);
const failedCustomerImages = ref(new Set());
const navItems = computed(() => [
  { key: "dashboard", label: uiText.value.nav.dashboard, icon: "dashboard" },
  { key: "events", label: uiText.value.nav.events, icon: "events" },
  { key: "admin-bookings", label: uiText.value.nav.bookings, icon: "bookings" },
  { key: "vendors", label: uiText.value.nav.vendors, icon: "vendors" },
  { key: "customers", label: uiText.value.nav.customers, icon: "users" },
  { key: "revenue", label: uiText.value.nav.revenue, icon: "revenue" },
  { key: "settings", label: uiText.value.nav.settings, icon: "settings" },
]);
const activeKey = ref("admin-bookings");

function countLabel(value) {
  return Number(value || 0).toLocaleString(activeLocale.value);
}

function formatCurrency(value) {
  return new Intl.NumberFormat(activeLocale.value, {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(value || 0));
}

function formatTimeLabel(dateString) {
  const raw = String(dateString || "").trim();
  if (!raw) return uiText.value.timeTbd;
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return uiText.value.allDay;

  const date = new Date(raw);
  if (Number.isNaN(date.getTime())) return uiText.value.timeTbd;

  return date.toLocaleTimeString(activeLocale.value, {
    hour: "numeric",
    minute: "2-digit",
  });
}

function getInitials(name, fallback = "BK") {
  const pieces = String(name || "")
    .trim()
    .split(" ")
    .filter(Boolean);

  if (!pieces.length) {
    return fallback;
  }

  return pieces
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();
}

function formatBadgeLabel(value, fallback = uiText.value.unknown) {
  const normalized = String(value || "")
    .replace(/[_-]+/g, " ")
    .trim();

  if (!normalized) {
    return fallback;
  }

  return normalized
    .split(/\s+/)
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(" ");
}

function bookingStatusLabel(value) {
  const normalized = String(value || "").trim().toLowerCase();
  if (normalized === "confirmed") return uiText.value.confirmed;
  if (normalized === "cancelled" || normalized === "canceled") return uiText.value.cancelled;
  return uiText.value.pending;
}

function paymentStatusLabel(value) {
  const normalized = String(value || "").trim().toLowerCase();
  const known = {
    unpaid: uiText.value.unpaid,
    paid: uiText.value.paid,
    pending: uiText.value.pending,
    refunded: uiText.value.refunded,
    failed: uiText.value.failed,
    cancelled: uiText.value.cancelled,
    canceled: uiText.value.cancelled,
    confirmed: uiText.value.confirmed,
  };

  return known[normalized] || formatBadgeLabel(value, uiText.value.unknown);
}

const initials = computed(() => {
  const pieces = String(props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});
const normalizedBookings = computed(() =>
  adminBookings.value.map((booking) => {
    const event = booking?.event || {};
    const vendor = event?.vendor || {};
    const customer = booking?.user || {};
    const bookedItems = Array.isArray(booking?.booked_items) ? booking.booked_items : [];
    const status = ["confirmed", "cancelled"].includes(String(booking?.status || "").toLowerCase())
      ? String(booking.status).toLowerCase()
      : "pending";
    const bookingDate =
      booking?.requested_event_date ||
      event?.starts_at ||
      booking?.created_at ||
      booking?.updated_at ||
      "";
    const customerName =
      String(booking?.customer_name || customer?.name || uiText.value.guestCustomer).trim() || uiText.value.guestCustomer;
    const customerEmail = String(booking?.customer_email || customer?.email || "").trim();
    const customerPhone = String(booking?.customer_phone || customer?.phone || "").trim();
    const serviceLabel = summarizeBookedServices(
      bookedItems,
      String(booking?.service_name || event?.title || uiText.value.serviceBooking).trim() || uiText.value.serviceBooking,
    );
    const totalAmount = Number(booking?.total_amount || 0);
    const paymentStatus = String(booking?.payment_status || "unpaid").trim() || "unpaid";

    return {
      id: Number(booking?.id || 0),
      key: `booking:${booking?.id}`,
      bookingCode: `#BK-${String(booking?.id || 0).padStart(4, "0")}`,
      customerName,
      customerInitials: getInitials(customerName),
      customerImageKey: customer?.id ? `customer:${customer.id}` : `booking:${booking?.id}`,
      customerProfileImageUrl: String(
        customer?.profile_image_url || booking?.customer_profile_image_url || booking?.customer_avatar || "",
      ).trim(),
      customerEmail,
      customerPhone,
      customerLocation: String(booking?.customer_location || customer?.location || "").trim(),
      customerContact: customerEmail || customerPhone || uiText.value.noContactProvided,
      vendorName: String(vendor?.name || uiText.value.vendorFallback).trim() || uiText.value.vendorFallback,
      vendorEmail: String(vendor?.email || "").trim(),
      serviceLabel,
      bookingKind: bookedItems.length > 1 ? uiText.value.package : uiText.value.service,
      eventTitle: String(event?.title || serviceLabel).trim() || serviceLabel,
      dateLabel: formatDateTime(bookingDate),
      timeLabel: formatTimeLabel(event?.starts_at || bookingDate),
      status,
      statusLabel: bookingStatusLabel(status),
      statusClass: status,
      quantity: Number(booking?.quantity || 1),
      totalAmount,
      totalLabel: formatCurrency(totalAmount),
      paymentStatus,
      paymentStatusLabel: paymentStatusLabel(paymentStatus),
      bookedItems,
    };
  }),
);

const filteredBookings = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return normalizedBookings.value.filter((booking) => {
    if (statusFilter.value !== "all" && booking.status !== statusFilter.value) {
      return false;
    }

    if (!query) {
      return true;
    }

    return [
      booking.bookingCode,
      booking.customerName,
      booking.customerEmail,
      booking.customerPhone,
      booking.vendorName,
      booking.serviceLabel,
      booking.eventTitle,
      booking.statusLabel,
      booking.totalLabel,
      booking.dateLabel,
    ]
      .join(" ")
      .toLowerCase()
      .includes(query);
  });
});

const selectedBooking = computed(
  () => filteredBookings.value.find((booking) => booking.id === selectedBookingId.value) || filteredBookings.value[0] || null,
);

const totalBookingsCount = computed(() => normalizedBookings.value.length);
const pendingBookingsCount = computed(() => normalizedBookings.value.filter((booking) => booking.status === "pending").length);
const confirmedBookingsCount = computed(() => normalizedBookings.value.filter((booking) => booking.status === "confirmed").length);
const cancelledBookingsCount = computed(() => normalizedBookings.value.filter((booking) => booking.status === "cancelled").length);
const packageBookingsCount = computed(() => normalizedBookings.value.filter((booking) => booking.bookingKind === uiText.value.package).length);
const serviceBookingsCount = computed(() => normalizedBookings.value.filter((booking) => booking.bookingKind === uiText.value.service).length);
const confirmedRevenue = computed(() =>
  normalizedBookings.value
    .filter((booking) => booking.status === "confirmed")
    .reduce((sum, booking) => sum + Number(booking.totalAmount || 0), 0),
);
const confirmedRevenueLabel = computed(() => formatCurrency(confirmedRevenue.value));

const stats = computed(() => [
  {
    label: uiText.value.totalBookings,
    value: countLabel(totalBookingsCount.value),
    delta: interpolate(uiText.value.confirmedCount, { count: countLabel(confirmedBookingsCount.value) }),
  },
  {
    label: uiText.value.pendingConfirmation,
    value: countLabel(pendingBookingsCount.value),
    delta: pendingBookingsCount.value ? uiText.value.needsAttention : uiText.value.allCaughtUp,
  },
  {
    label: uiText.value.confirmedRevenue,
    value: confirmedRevenueLabel.value,
    delta: interpolate(uiText.value.cancelledCount, { count: countLabel(cancelledBookingsCount.value) }),
  },
]);

const hasCustomerProfileImage = (booking) =>
  Boolean(String(booking?.customerProfileImageUrl || "").trim())
  && !failedCustomerImages.value.has(booking?.customerImageKey || booking?.key);

function handleCustomerImageError(imageKey) {
  if (!imageKey || failedCustomerImages.value.has(imageKey)) {
    return;
  }

  const next = new Set(failedCustomerImages.value);
  next.add(imageKey);
  failedCustomerImages.value = next;
}

async function loadAdminBookings() {
  isLoading.value = true;
  loadError.value = "";

  try {
    const result = await apiGet("bookings", { per_page: 100 });
    adminBookings.value = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    failedCustomerImages.value = new Set();
  } catch (error) {
    adminBookings.value = [];
    loadError.value = error?.message || uiText.value.couldNotLoad;
  } finally {
    isLoading.value = false;
  }
}

function selectBooking(bookingId) {
  selectedBookingId.value = Number(bookingId || 0) || null;
}

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
watch(
  filteredBookings,
  (rows) => {
    if (!rows.length) {
      selectedBookingId.value = null;
      return;
    }

    if (!rows.some((booking) => booking.id === selectedBookingId.value)) {
      selectedBookingId.value = rows[0].id;
    }
  },
  { immediate: true },
);
onMounted(() => void loadAdminBookings());
</script>

<template>
  <section class="admin-shell bookings-shell">
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
          <button class="primary-btn" type="button" @click="loadAdminBookings">{{ uiText.refreshList }}</button>
          <button class="icon-btn" type="button" :title="uiText.notifications" :aria-label="uiText.notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="bookings-hero">
        <div>
          <p class="eyebrow">{{ uiText.heroEyebrow }}</p>
          <h1 class="hero-title">{{ uiText.heroTitle }}</h1>
          <p class="hero-subtitle">{{ uiText.heroSubtitle }}</p>
        </div>
        <div class="pill-group">
          <button class="pill" :class="{ active: statusFilter === 'all' }" type="button" @click="statusFilter = 'all'">{{ uiText.all }}</button>
          <button class="pill" :class="{ active: statusFilter === 'pending' }" type="button" @click="statusFilter = 'pending'">{{ uiText.pending }}</button>
          <button class="pill" :class="{ active: statusFilter === 'confirmed' }" type="button" @click="statusFilter = 'confirmed'">{{ uiText.confirmed }}</button>
          <button class="pill" :class="{ active: statusFilter === 'cancelled' }" type="button" @click="statusFilter = 'cancelled'">{{ uiText.cancelled }}</button>
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
        <article class="queue-card">
          <header>
            <div>
              <h3>{{ uiText.customerBookingList }}</h3>
              <p class="table-summary">
                {{ interpolate(uiText.bookingSummary, { shown: filteredBookings.length, total: normalizedBookings.length }) }}
              </p>
            </div>
            <div class="table-actions">
              <span class="table-chip">{{ interpolate(uiText.packageCount, { count: countLabel(packageBookingsCount) }) }}</span>
              <span class="table-chip">{{ interpolate(uiText.serviceCount, { count: countLabel(serviceBookingsCount) }) }}</span>
            </div>
          </header>
          <p v-if="loadError" class="table-feedback error">{{ loadError }}</p>
          <p v-else-if="isLoading" class="table-feedback">{{ uiText.loadingBookings }}</p>
          <p v-else-if="!filteredBookings.length" class="table-feedback">{{ uiText.noBookingsMatch }}</p>
          <div v-else class="booking-list">
            <button
              v-for="item in filteredBookings"
              :key="item.key"
              type="button"
              class="booking-row"
              :class="{ selected: selectedBooking?.id === item.id }"
              @click="selectBooking(item.id)"
            >
              <div class="booking-row-main">
                <div class="customer-cell">
                  <div class="customer-avatar customer-avatar-small">
                    <img
                      v-if="hasCustomerProfileImage(item)"
                      :src="item.customerProfileImageUrl"
                      :alt="`${item.customerName} profile`"
                      @error="handleCustomerImageError(item.customerImageKey)"
                    />
                    <span v-else>{{ item.customerInitials }}</span>
                  </div>
                  <div class="customer-copy">
                    <p class="attendee">{{ item.customerName }}</p>
                  </div>
                </div>
                <div class="booking-overview">
                  <p class="service-name">{{ item.serviceLabel }}</p>
                  <span class="service-meta">{{ item.vendorName }} | {{ item.bookingKind }} | {{ item.bookingCode }}</span>
                  <div class="booking-chip-row">
                    <span class="info-chip">{{ item.timeLabel }}</span>
                    <span class="info-chip">{{ item.paymentStatusLabel }}</span>
                    <span class="info-chip">{{ interpolate(uiText.qty, { count: item.quantity }) }}</span>
                  </div>
                </div>
              </div>
              <div class="booking-row-side">
                <span class="booking-date">{{ item.dateLabel }}</span>
                <span class="status" :class="item.statusClass">{{ item.statusLabel }}</span>
                <span class="booking-stat">{{ item.totalLabel }}</span>
              </div>
            </button>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedBooking" class="card detail-card">
            <header class="detail-head">
              <div>
                <p class="detail-eyebrow">{{ uiText.bookingDetail }}</p>
                <h3>{{ uiText.selectedBooking }}</h3>
              </div>
              <span class="detail-code">{{ selectedBooking.bookingCode }}</span>
            </header>
            <div class="customer-block">
              <div class="customer-avatar">
                <img
                  v-if="hasCustomerProfileImage(selectedBooking)"
                  :src="selectedBooking.customerProfileImageUrl"
                  :alt="`${selectedBooking.customerName} profile`"
                  @error="handleCustomerImageError(selectedBooking.customerImageKey)"
                />
                <span v-else>{{ selectedBooking.customerInitials }}</span>
              </div>
              <div class="customer-details">
                <p class="customer-name">{{ selectedBooking.customerName }}</p>
                <p class="customer-meta">{{ selectedBooking.serviceLabel }}</p>
                <div class="detail-tags">
                  <span class="status" :class="selectedBooking.statusClass">{{ selectedBooking.statusLabel }}</span>
                  <span>{{ selectedBooking.bookingKind }}</span>
                  <span>{{ selectedBooking.paymentStatusLabel }}</span>
                </div>
              </div>
            </div>
            <div class="contact-grid">
              <div>
                <span>{{ uiText.email }}</span>
                <strong>{{ selectedBooking.customerEmail || uiText.notProvided }}</strong>
              </div>
              <div>
                <span>{{ uiText.phone }}</span>
                <strong>{{ selectedBooking.customerPhone || uiText.notProvided }}</strong>
              </div>
              <div class="contact-grid-wide">
                <span>{{ uiText.location }}</span>
                <strong>{{ selectedBooking.customerLocation || uiText.notProvided }}</strong>
              </div>
            </div>
            <div class="booking-stats">
              <div>
                <span>{{ uiText.total }}</span>
                <strong>{{ selectedBooking.totalLabel }}</strong>
              </div>
              <div>
                <span>{{ uiText.quantity }}</span>
                <strong>{{ selectedBooking.quantity }}</strong>
              </div>
            </div>
            <div class="booking-details-grid">
              <div>
                <span>{{ uiText.vendor }}</span>
                <strong>{{ selectedBooking.vendorName }}</strong>
              </div>
              <div>
                <span>{{ uiText.vendorEmail }}</span>
                <strong class="detail-email">{{ selectedBooking.vendorEmail || uiText.notProvided }}</strong>
              </div>
              <div>
                <span>{{ uiText.eventDate }}</span>
                <strong>{{ selectedBooking.dateLabel }}</strong>
              </div>
              <div>
                <span>{{ uiText.time }}</span>
                <strong>{{ selectedBooking.timeLabel }}</strong>
              </div>
              <div>
                <span>{{ uiText.bookingType }}</span>
                <strong>{{ selectedBooking.bookingKind }}</strong>
              </div>
              <div>
                <span>{{ uiText.payment }}</span>
                <strong>{{ selectedBooking.paymentStatusLabel }}</strong>
              </div>
            </div>
            <div v-if="selectedBooking.bookedItems.length" class="booked-items">
              <span class="booked-items-label">{{ uiText.bookedItems }}</span>
              <div class="booked-items-list">
                <span v-for="(entry, index) in selectedBooking.bookedItems" :key="`${selectedBooking.key}:item:${index}`">
                  {{ entry?.name || uiText.item }}
                </span>
              </div>
            </div>
          </article>
          <article v-else class="card detail-card empty-detail-card">
            <header>
              <h3>{{ uiText.noBookingSelected }}</h3>
            </header>
            <p>{{ uiText.noBookingSelectedSubtitle }}</p>
          </article>

          <article class="card summary-card">
            <h3>{{ uiText.summaryTitle }}</h3>
            <p>{{ uiText.summarySubtitle }}</p>
            <div class="summary-feature">
              <div>
                <span>{{ uiText.packageOrders }}</span>
                <strong>{{ countLabel(packageBookingsCount) }}</strong>
              </div>
              <div>
                <span>{{ uiText.serviceOrders }}</span>
                <strong>{{ countLabel(serviceBookingsCount) }}</strong>
              </div>
            </div>
            <div class="summary-list">
              <div class="summary-item">
                <span>{{ uiText.confirmed }}</span>
                <strong>{{ countLabel(confirmedBookingsCount) }}</strong>
              </div>
              <div class="summary-item">
                <span>{{ uiText.pending }}</span>
                <strong>{{ countLabel(pendingBookingsCount) }}</strong>
              </div>
              <div class="summary-item">
                <span>{{ uiText.cancelled }}</span>
                <strong>{{ countLabel(cancelledBookingsCount) }}</strong>
              </div>
              <div class="summary-item">
                <span>{{ uiText.revenue }}</span>
                <strong>{{ confirmedRevenueLabel }}</strong>
              </div>
            </div>
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

.queue-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 18px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
}

.card {
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

.table-summary {
  margin: 6px 0 0;
  font-size: 13px;
  color: var(--muted);
}

.table-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.table-chip {
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(255, 245, 236, 0.95);
  border: 1px solid rgba(255, 122, 26, 0.12);
  color: #d05f17;
  font-size: 12px;
  font-weight: 700;
}

.table-feedback {
  margin: 0;
  padding: 16px 14px;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.86);
  border: 1px solid rgba(15, 23, 42, 0.06);
  color: var(--muted);
}

.table-feedback.error {
  color: #b45309;
  background: #fff7ed;
  border-color: rgba(245, 158, 11, 0.18);
}

.booking-list {
  display: grid;
  gap: 12px;
}

.booking-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 18px;
  align-items: center;
  padding: 16px 18px;
  border-radius: 22px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.05);
  width: 100%;
  text-align: left;
  font: inherit;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.booking-row:hover {
  transform: translateX(4px);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.1);
}

.booking-row.selected {
  border-color: rgba(255, 122, 26, 0.2);
  background: linear-gradient(135deg, rgba(255, 247, 240, 0.98), rgba(255, 255, 255, 1));
  box-shadow: 0 18px 36px rgba(255, 122, 26, 0.12);
}

.booking-row-main {
  display: grid;
  grid-template-columns: minmax(220px, 0.92fr) minmax(240px, 1.08fr);
  gap: 18px;
  align-items: center;
  min-width: 0;
}

.attendee {
  margin: 0;
  font-weight: 600;
}

.customer-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.customer-copy,
.booking-overview {
  display: grid;
  gap: 4px;
  min-width: 0;
}

.service-name {
  margin: 0;
  font-weight: 600;
  color: #17263d;
  font-size: 16px;
  line-height: 1.35;
}

.service-meta {
  font-size: 12px;
  color: var(--muted);
}

.booking-chip-row {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 4px;
}

.info-chip {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  background: #f7fafc;
  border: 1px solid rgba(15, 23, 42, 0.06);
  color: #5d6d84;
  font-size: 11px;
  font-weight: 700;
}

.booking-row-side {
  display: grid;
  gap: 8px;
  justify-items: end;
  align-content: center;
  text-align: right;
}

.booking-date {
  font-size: 13px;
  color: #425168;
  font-weight: 600;
}

.booking-stat {
  font-size: 16px;
  font-weight: 700;
  color: #17263d;
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

.side-column {
  display: grid;
  gap: 16px;
}

.detail-card,
.empty-detail-card,
.summary-card {
  background: var(--surface-strong);
}

.detail-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.detail-head {
  gap: 12px;
}

.detail-eyebrow {
  margin: 0 0 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #b66731;
}

.detail-code {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  background: rgba(255, 244, 234, 0.98);
  border: 1px solid rgba(255, 122, 26, 0.14);
  color: #c95e18;
  font-size: 12px;
  font-weight: 700;
}

.customer-block {
  display: flex;
  gap: 14px;
  align-items: center;
  margin-bottom: 16px;
}

.customer-avatar {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  background: linear-gradient(135deg, rgba(255, 245, 236, 0.98), rgba(255, 223, 193, 0.92));
  color: #f15b2a;
  display: grid;
  place-items: center;
  font-weight: 700;
  flex-shrink: 0;
  overflow: hidden;
  border: 1px solid rgba(255, 122, 26, 0.14);
  box-shadow: 0 14px 30px rgba(255, 122, 26, 0.14);
}

.customer-avatar-small {
  width: 52px;
  height: 52px;
  border-radius: 16px;
}

.customer-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.customer-avatar span {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
}

.customer-details {
  min-width: 0;
}

.customer-name {
  margin: 0;
  font-weight: 600;
}

.customer-meta {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.detail-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.detail-tags span:not(.status) {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  background: #f7fafc;
  border: 1px solid rgba(15, 23, 42, 0.06);
  color: #5d6d84;
  font-size: 11px;
  font-weight: 700;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.contact-grid div {
  padding: 14px;
  border-radius: 16px;
  background: rgba(248, 250, 252, 0.92);
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.contact-grid-wide {
  grid-column: 1 / -1;
}

.contact-grid span {
  font-size: 12px;
  color: var(--muted);
}

.contact-grid strong {
  display: block;
  margin-top: 6px;
  font-size: 14px;
  line-height: 1.5;
  color: #17263d;
  word-break: break-word;
}

.booking-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 16px;
}

.booking-stats div {
  padding: 14px 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 246, 239, 0.98), rgba(255, 255, 255, 0.98));
  border: 1px solid rgba(255, 122, 26, 0.12);
}

.booking-stats span,
.booking-details-grid span,
.contact-grid span,
.summary-item span,
.summary-feature span,
.booked-items-label {
  font-size: 12px;
  color: var(--muted);
}

.booking-stats strong {
  display: block;
  margin-top: 4px;
  font-size: 14px;
}

.booking-details-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.booking-details-grid div {
  padding: 12px;
  border-radius: 14px;
  background: rgba(248, 250, 252, 0.9);
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.booking-details-grid strong {
  display: block;
  margin-top: 6px;
  font-size: 14px;
  color: #17263d;
}

.detail-email {
  line-height: 1.5;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.booked-items {
  margin-top: 16px;
}

.booked-items-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.booked-items-list span {
  padding: 7px 10px;
  border-radius: 999px;
  background: #fff3e6;
  color: #d05f17;
  font-size: 12px;
  font-weight: 600;
}

.summary-card p,
.empty-detail-card p {
  margin: 10px 0 16px;
  color: var(--muted);
  line-height: 1.6;
}

.summary-feature {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 14px;
}

.summary-feature div {
  padding: 14px 16px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 246, 239, 0.98), rgba(255, 255, 255, 0.98));
  border: 1px solid rgba(255, 122, 26, 0.12);
}

.summary-feature strong {
  display: block;
  margin-top: 6px;
  font-size: 18px;
  color: #17263d;
}

.summary-list {
  display: grid;
  gap: 12px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(248, 250, 252, 0.9);
  border: 1px solid rgba(15, 23, 42, 0.06);
}

.summary-item strong {
  color: #17263d;
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

@media (max-width: 1100px) {
  .bookings-grid {
    grid-template-columns: 1fr;
  }

  .booking-row-main {
    grid-template-columns: 1fr;
  }

  .booking-row {
    grid-template-columns: 1fr;
    row-gap: 8px;
  }

  .booking-row-side {
    justify-self: start;
    text-align: left;
    justify-items: start;
  }
}

@media (max-width: 1024px) {
  .bookings-shell {
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

  .bookings-hero {
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

  .contact-grid,
  .summary-feature,
  .booking-details-grid {
    grid-template-columns: 1fr;
  }

  .admin-topbar {
    flex-direction: column;
    align-items: stretch;
  }

  .table-actions,
  .detail-head {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>

