<script setup>
import { computed, ref, watch } from "vue";
import { RouterLink } from "vue-router";
import { useLanguageCopy } from "../../features/language";

const props = defineProps([
  "appLogoSrc",
  "vendorDisplayName",
  "activeTab",
  "eventTypeOptions",
  "vendorEvents",
  "vendorBookings",
  "isLoadingEvents",
  "isLoadingVendorBookings",
  "vendorServiceForm",
  "isSubmittingVendorService",
  "vendorServiceNotice",
  "vendorIncome",
  "messagesSummary",
  "submitVendorService",
  "toggleVendorServiceActive",
  "deleteVendorService",
  "updateVendorBookingStatus",
  "goToMessages",
  "logoutUser",
]);

const emit = defineEmits(["update:activeTab"]);

const localActiveTab = ref(
  typeof props.activeTab === "string" ? props.activeTab : "overview",
);
const isCreateServiceModalOpen = ref(false);
const isDetectingVendorLocation = ref(false);
const vendorLocationNotice = ref("");
const incomePeriod = ref("month");
const copyByLanguage = {
  en: {
    overview: "Overview",
    myServices: "My Services",
    bookings: "Bookings",
    messages: "Messages",
    analytics: "Analytics",
    week: "Week",
    month: "Month",
    year: "Year",
    noData: "No data",
    geoUnsupported: "Geolocation is not supported by this browser.",
    locationCaptured: "Current location captured.",
    locationDenied: "Could not access your current location.",
    vendorPortal: "Vendor Portal",
    backHome: "Back to Home",
    settings: "Settings",
    logout: "Logout",
    verifiedWorkspace: "Verified vendor workspace",
    dashboardEyebrow: "Vendor dashboard",
    dashboardTitle: "Achar Vendor Dashboard",
    dashboardText:
      "Manage your services, booking requests, and customer messages from one workspace.",
    newService: "+ New Service",
    signedInAs: "Signed in as",
    vendor: "Vendor",
    totalIncome: "Total Income",
    confirmedRevenue: "Confirmed bookings revenue",
    totalBookedServices: "Total Booked Services",
    completedConfirmations: "Completed and active confirmations",
    newRequests: "New Requests",
    waitingResponse: "Waiting for your response",
    unreadMessages: "Unread Messages",
    conversationsNeedAttention: "Customer conversations needing attention",
    performance: "Performance",
    incomeTrend: "Income Trend Overview",
    confirmedRevenueRange: "Confirmed revenue in selected range",
    average: "Average",
    averagePerPoint: "Average per point on the chart",
    peak: "Peak",
    noPeakYet: "No peak yet",
    bestPeriod: "Best period",
    na: "N/A",
    performanceLabel: "Performance",
    confirmedBookings: "Confirmed bookings",
    coverage: "Coverage",
    activeServicesListed: "Active services listed",
    noConfirmedIncome: "No confirmed income yet for this period.",
    createService: "Create service",
    insertService: "Insert Service",
    currentListings: "Current listings",
    loadingServices: "Loading services...",
    bookingRequests: "Requests",
    newBookingRequests: "New Booking Requests",
    loadingBookings: "Loading bookings...",
    customerMessages: "Customer Messages",
    openMessages: "Open Messages",
    incomeInsights: "Vendor Income Insights",
    addNewService: "Add New Service",
  },
  km: {
    overview: "ទិដ្ឋភាពទូទៅ",
    myServices: "សេវាកម្មរបស់ខ្ញុំ",
    bookings: "ការកក់",
    messages: "សារ",
    analytics: "វិភាគទិន្នន័យ",
    week: "សប្ដាហ៍",
    month: "ខែ",
    year: "ឆ្នាំ",
    noData: "គ្មានទិន្នន័យ",
    geoUnsupported: "កម្មវិធីរុករកនេះមិនគាំទ្រការកំណត់ទីតាំងទេ។",
    locationCaptured: "បានចាប់យកទីតាំងបច្ចុប្បន្ន។",
    locationDenied: "មិនអាចចូលប្រើទីតាំងបច្ចុប្បន្នរបស់អ្នកបានទេ។",
    vendorPortal: "ផតថលអ្នកផ្គត់ផ្គង់",
    backHome: "ត្រឡប់ទៅទំព័រដើម",
    settings: "ការកំណត់",
    logout: "ចាកចេញ",
    verifiedWorkspace: "កន្លែងធ្វើការអ្នកផ្គត់ផ្គង់ដែលបានផ្ទៀងផ្ទាត់",
    dashboardEyebrow: "ផ្ទាំងគ្រប់គ្រងអ្នកផ្គត់ផ្គង់",
    dashboardTitle: "ផ្ទាំងគ្រប់គ្រងអ្នកផ្គត់ផ្គង់ Achar",
    dashboardText:
      "គ្រប់គ្រងសេវាកម្ម សំណើកក់ និងសារអតិថិជនរបស់អ្នកពីកន្លែងធ្វើការតែមួយ។",
    newService: "+ សេវាកម្មថ្មី",
    signedInAs: "បានចូលជា",
    vendor: "អ្នកផ្គត់ផ្គង់",
    totalIncome: "ចំណូលសរុប",
    confirmedRevenue: "ចំណូលពីការកក់ដែលបានបញ្ជាក់",
    totalBookedServices: "សេវាកម្មដែលបានកក់សរុប",
    completedConfirmations: "ការបញ្ជាក់ដែលបានបញ្ចប់ និងកំពុងសកម្ម",
    newRequests: "សំណើថ្មី",
    waitingResponse: "កំពុងរង់ចាំការឆ្លើយតបរបស់អ្នក",
    unreadMessages: "សារមិនទាន់អាន",
    conversationsNeedAttention: "ការសន្ទនារបស់អតិថិជនដែលត្រូវការយកចិត្តទុកដាក់",
    performance: "លទ្ធផល",
    incomeTrend: "ទិដ្ឋភាពនិន្នាការចំណូល",
    confirmedRevenueRange: "ចំណូលដែលបានបញ្ជាក់ក្នុងរយៈពេលដែលបានជ្រើស",
    average: "មធ្យម",
    averagePerPoint: "មធ្យមក្នុងមួយចំណុចលើក្រាហ្វ",
    peak: "ខ្ពស់បំផុត",
    noPeakYet: "មិនទាន់មានកំពូល",
    bestPeriod: "រយៈពេលល្អបំផុត",
    na: "មិនមាន",
    performanceLabel: "លទ្ធផល",
    confirmedBookings: "ការកក់ដែលបានបញ្ជាក់",
    coverage: "ការគ្របដណ្តប់",
    activeServicesListed: "សេវាកម្មសកម្មដែលបានបញ្ជី",
    noConfirmedIncome: "មិនទាន់មានចំណូលដែលបានបញ្ជាក់សម្រាប់រយៈពេលនេះទេ។",
    createService: "បង្កើតសេវាកម្ម",
    insertService: "បញ្ចូលសេវាកម្ម",
    currentListings: "បញ្ជីបច្ចុប្បន្ន",
    loadingServices: "កំពុងផ្ទុកសេវាកម្ម...",
    bookingRequests: "សំណើ",
    newBookingRequests: "សំណើកក់ថ្មី",
    loadingBookings: "កំពុងផ្ទុកការកក់...",
    customerMessages: "សារអតិថិជន",
    openMessages: "បើកសារ",
    incomeInsights: "ការយល់ដឹងអំពីចំណូលអ្នកផ្គត់ផ្គង់",
    addNewService: "បន្ថែមសេវាកម្មថ្មី",
  },
  zh: {
    overview: "概览",
    myServices: "我的服务",
    bookings: "预订",
    messages: "消息",
    analytics: "分析",
    week: "周",
    month: "月",
    year: "年",
    noData: "无数据",
    geoUnsupported: "当前浏览器不支持地理定位。",
    locationCaptured: "已获取当前位置。",
    locationDenied: "无法访问您的当前位置。",
    vendorPortal: "商家门户",
    backHome: "返回首页",
    settings: "设置",
    logout: "退出登录",
    verifiedWorkspace: "已认证商家工作台",
    dashboardEyebrow: "商家仪表盘",
    dashboardTitle: "Achar 商家仪表盘",
    dashboardText: "在一个工作区中管理您的服务、预订请求和客户消息。",
    newService: "+ 新建服务",
    signedInAs: "当前登录为",
    vendor: "商家",
    totalIncome: "总收入",
    confirmedRevenue: "已确认预订收入",
    totalBookedServices: "已预订服务总数",
    completedConfirmations: "已完成和进行中的确认",
    newRequests: "新请求",
    waitingResponse: "等待您的回复",
    unreadMessages: "未读消息",
    conversationsNeedAttention: "需要处理的客户对话",
    performance: "表现",
    incomeTrend: "收入趋势概览",
    confirmedRevenueRange: "所选范围内的已确认收入",
    average: "平均",
    averagePerPoint: "图表中每个点的平均值",
    peak: "峰值",
    noPeakYet: "尚无峰值",
    bestPeriod: "最佳时段",
    na: "无",
    performanceLabel: "表现",
    confirmedBookings: "已确认预订",
    coverage: "覆盖范围",
    activeServicesListed: "已上架的活跃服务",
    noConfirmedIncome: "该时段暂无已确认收入。",
    createService: "创建服务",
    insertService: "录入服务",
    currentListings: "当前列表",
    loadingServices: "正在加载服务...",
    bookingRequests: "请求",
    newBookingRequests: "新的预订请求",
    loadingBookings: "正在加载预订...",
    customerMessages: "客户消息",
    openMessages: "打开消息",
    incomeInsights: "商家收入洞察",
    addNewService: "添加新服务",
  },
};
const { uiText } = useLanguageCopy(copyByLanguage);

const navItems = computed(() => [
  { key: "overview", label: uiText.value.overview },
  { key: "services", label: uiText.value.myServices },
  { key: "bookings", label: uiText.value.bookings },
  { key: "messages", label: uiText.value.messages },
  { key: "income", label: uiText.value.analytics },
]);

const safeIncome = computed(() => ({
  total: Number(props.vendorIncome?.total || 0),
  confirmedCount: Number(props.vendorIncome?.confirmedCount || 0),
  newBookings: Number(props.vendorIncome?.newBookings || 0),
  thisMonth: Number(props.vendorIncome?.thisMonth || 0),
  thisYear: Number(props.vendorIncome?.thisYear || 0),
  activeServices: Number(props.vendorIncome?.activeServices || 0),
  periods: props.vendorIncome?.periods || {},
}));

const safeMessagesSummary = computed(() => Number(props.messagesSummary || 0));
const safeVendorEvents = computed(() =>
  Array.isArray(props.vendorEvents) ? props.vendorEvents : [],
);
const safeVendorBookings = computed(() =>
  Array.isArray(props.vendorBookings) ? props.vendorBookings : [],
);
const vendorServiceNoticeTone = computed(() => {
  const message = String(props.vendorServiceNotice || "")
    .trim()
    .toLowerCase();
  if (!message) return "";
  if (
    message.includes("success") ||
    message.includes("created") ||
    message.includes("saved") ||
    message.includes("visible")
  ) {
    return "success";
  }
  return "error";
});
const eventOptions = computed(() =>
  Array.isArray(props.eventTypeOptions)
    ? props.eventTypeOptions.filter(
        (item) => item?.value && item.value !== "all",
      )
    : [],
);
const incomePeriodOptions = computed(() => [
  { key: "week", label: uiText.value.week },
  { key: "month", label: uiText.value.month },
  { key: "year", label: uiText.value.year },
]);
const activeIncomePeriod = computed(
  () =>
    safeIncome.value.periods?.[incomePeriod.value] || {
      label: uiText.value.noData,
      points: [],
      total: 0,
    },
);
const activeIncomePoints = computed(() =>
  Array.isArray(activeIncomePeriod.value.points)
    ? activeIncomePeriod.value.points
    : [],
);
const incomePeakValue = computed(() =>
  Math.max(
    ...activeIncomePoints.value.map((item) => Number(item.value || 0)),
    0,
  ),
);
const incomeAverageValue = computed(() =>
  activeIncomePoints.value.length
    ? activeIncomePoints.value.reduce(
        (sum, item) => sum + Number(item.value || 0),
        0,
      ) / activeIncomePoints.value.length
    : 0,
);
const incomeMidValue = computed(() => incomePeakValue.value / 2);
const topIncomePoint = computed(() => {
  if (!activeIncomePoints.value.length) return null;
  return activeIncomePoints.value.reduce((best, point) =>
    Number(point.value || 0) > Number(best.value || 0) ? point : best,
  );
});
const normalizedIncomeChartPoints = computed(() => {
  const points = activeIncomePoints.value;
  if (!points.length) return [];

  const width = 100;
  const height = 100;
  const maxValue = incomePeakValue.value || 1;

  return points.map((point, index) => ({
    x: points.length === 1 ? width / 2 : (index / (points.length - 1)) * width,
    y: height - (Number(point.value || 0) / maxValue) * 82 - 9,
    value: Number(point.value || 0),
    label: point.label,
    fullLabel: point.fullLabel,
  }));
});

const showIncomeDots = computed(
  () => normalizedIncomeChartPoints.value.length <= 18,
);
const incomeLabelStep = computed(() => {
  const count = activeIncomePoints.value.length;
  if (count <= 8) return 1;
  if (count <= 16) return 2;
  if (count <= 24) return 3;
  if (count <= 32) return 4;
  return Math.max(5, Math.ceil(count / 8));
});
const chartHoverIndex = ref(-1);
const chartHoverPoint = computed(() => {
  const points = normalizedIncomeChartPoints.value;
  const index = chartHoverIndex.value;
  if (!points.length) return null;
  if (typeof index !== "number" || index < 0 || index >= points.length)
    return null;
  return points[index];
});
const chartTooltipLeft = computed(() => {
  if (!chartHoverPoint.value) return "50%";
  const x = Math.min(94, Math.max(6, Number(chartHoverPoint.value.x || 50)));
  return `${x}%`;
});
const chartTooltipTop = computed(() => {
  if (!chartHoverPoint.value) return "50%";
  let y = Number(chartHoverPoint.value.y || 50);
  if (y < 18) y = 18;
  if (y > 92) y = 92;
  return `${y}%`;
});

function updateChartHover(event) {
  const points = normalizedIncomeChartPoints.value;
  if (!points.length) return;

  const rect = event.currentTarget.getBoundingClientRect();
  const rawX = ((event.clientX - rect.left) / rect.width) * 100;
  const clampedX = Math.min(100, Math.max(0, rawX));
  const index =
    points.length === 1
      ? 0
      : Math.round((clampedX / 100) * (Math.max(1, points.length - 1) || 1));
  chartHoverIndex.value = Math.min(points.length - 1, Math.max(0, index));
}

function clearChartHover() {
  chartHoverIndex.value = -1;
}

watch(
  () => normalizedIncomeChartPoints.value.length,
  () => {
    chartHoverIndex.value = -1;
  },
);

function buildSmoothPath(points) {
  if (!points.length) return "";
  if (points.length === 1)
    return `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`;

  let path = `M ${points[0].x.toFixed(2)} ${points[0].y.toFixed(2)}`;

  for (let index = 0; index < points.length - 1; index += 1) {
    const current = points[index];
    const next = points[index + 1];
    const controlX = (current.x + next.x) / 2;
    path += ` C ${controlX.toFixed(2)} ${current.y.toFixed(2)}, ${controlX.toFixed(2)} ${next.y.toFixed(2)}, ${next.x.toFixed(2)} ${next.y.toFixed(2)}`;
  }

  return path;
}

const incomeChartPath = computed(() =>
  buildSmoothPath(normalizedIncomeChartPoints.value),
);
const incomeLineShadowPath = computed(() =>
  buildSmoothPath(
    normalizedIncomeChartPoints.value.map((point) => ({
      ...point,
      y: point.y + 1.8,
    })),
  ),
);
const latestIncomePoint = computed(() =>
  normalizedIncomeChartPoints.value.length
    ? normalizedIncomeChartPoints.value[
        normalizedIncomeChartPoints.value.length - 1
      ]
    : null,
);
const averageLineY = computed(() => {
  const maxValue = incomePeakValue.value || 1;
  return 100 - (Number(incomeAverageValue.value || 0) / maxValue) * 82 - 9;
});
const incomeAreaPath = computed(() => {
  const linePath = incomeChartPath.value;
  const points = normalizedIncomeChartPoints.value;
  if (!linePath || !points.length) return "";
  const width = 100;
  return `${linePath} L ${width} 100 L 0 100 Z`;
});

function formatCurrency(value) {
  return `$${Number(value || 0).toLocaleString()}`;
}

function setIncomePeriod(periodKey) {
  incomePeriod.value = periodKey;
}

function setActiveTab(tabKey) {
  localActiveTab.value = tabKey;
  emit("update:activeTab", tabKey);
}

function openCreateServiceModal() {
  isCreateServiceModalOpen.value = true;
  setActiveTab("services");
}

function closeCreateServiceModal() {
  isCreateServiceModalOpen.value = false;
}

function submitServiceForm() {
  if (typeof props.submitVendorService === "function") {
    props.submitVendorService();
  }
}

function handleVendorServiceImageChange(event) {
  const [file] = Array.from(event?.target?.files || []);
  if (!props.vendorServiceForm) return;

  if (!file) {
    props.vendorServiceForm.image_file = null;
    return;
  }

  props.vendorServiceForm.image_file = file;

  const reader = new FileReader();
  reader.onload = () => {
    props.vendorServiceForm.image_url =
      typeof reader.result === "string" ? reader.result : "";
  };
  reader.readAsDataURL(file);
}

function clearVendorServiceImage() {
  if (!props.vendorServiceForm) return;
  props.vendorServiceForm.image_file = null;
  props.vendorServiceForm.image_url = "";
}

function handleVendorServiceImageUrlInput(event) {
  if (!props.vendorServiceForm) return;
  props.vendorServiceForm.image_file = null;
  props.vendorServiceForm.image_url = event?.target?.value || "";
}

const vendorLocationMapEmbedUrl = computed(() => {
  const lat = Number(props.vendorServiceForm?.location_latitude);
  const lng = Number(props.vendorServiceForm?.location_longitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return "";

  const safeLat = Number(lat.toFixed(6));
  const safeLng = Number(lng.toFixed(6));
  const delta = 0.012;
  const left = (safeLng - delta).toFixed(6);
  const bottom = (safeLat - delta).toFixed(6);
  const right = (safeLng + delta).toFixed(6);
  const top = (safeLat + delta).toFixed(6);

  return `https://www.openstreetmap.org/export/embed.html?bbox=${left}%2C${bottom}%2C${right}%2C${top}&layer=mapnik&marker=${safeLat}%2C${safeLng}`;
});

const vendorLocationMapLinkUrl = computed(() => {
  const lat = Number(props.vendorServiceForm?.location_latitude);
  const lng = Number(props.vendorServiceForm?.location_longitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return "";

  const safeLat = Number(lat.toFixed(6));
  const safeLng = Number(lng.toFixed(6));
  return `https://www.openstreetmap.org/?mlat=${safeLat}&mlon=${safeLng}#map=14/${safeLat}/${safeLng}`;
});

async function detectVendorLocation() {
  if (!props.vendorServiceForm) return;

  if (!navigator.geolocation) {
    vendorLocationNotice.value = uiText.value.geoUnsupported;
    return;
  }

  isDetectingVendorLocation.value = true;
  vendorLocationNotice.value = "";

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const lat = Number(position.coords.latitude.toFixed(6));
      const lng = Number(position.coords.longitude.toFixed(6));
      let placeName = "";

      try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
        const response = await fetch(url, {
          headers: {
            Accept: "application/json",
            "Accept-Language": "en",
          },
        });

        if (response.ok) {
          const data = await response.json();
          const address = data?.address || {};
          const streetNumber =
            address.house_number ||
            address.house_name ||
            address.building ||
            "";
          const streetName =
            address.road || address.pedestrian || address.footway || "";
          const street = [streetNumber, streetName]
            .filter(Boolean)
            .join(" ")
            .trim();
          const village =
            address.village ||
            address.hamlet ||
            address.neighbourhood ||
            address.suburb ||
            "";
          const district =
            address.city_district ||
            address.district ||
            address.borough ||
            address.county ||
            "";
          const city =
            address.city ||
            address.town ||
            address.municipality ||
            address.state_district ||
            "";
          const province =
            address.state || address.region || address.province || "";
          const parts = [street, village, district, city, province].filter(
            (value) => String(value).trim().length > 0,
          );
          const primaryName =
            data?.name ||
            address.shop ||
            address.amenity ||
            address.tourism ||
            address.building ||
            "";

          placeName = parts.length
            ? parts.join(", ")
            : String(primaryName || "").trim();
        }
      } catch {
        placeName = "";
      }

      props.vendorServiceForm.location_latitude = lat;
      props.vendorServiceForm.location_longitude = lng;
      props.vendorServiceForm.location = placeName || `${lat}, ${lng}`;
      vendorLocationNotice.value = uiText.value.locationCaptured;
      isDetectingVendorLocation.value = false;
    },
    () => {
      vendorLocationNotice.value = uiText.value.locationDenied;
      isDetectingVendorLocation.value = false;
    },
    {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 0,
    },
  );
}

function openMessages() {
  setActiveTab("messages");
  if (typeof props.goToMessages === "function") {
    props.goToMessages();
  }
}

function bookingStatusClass(status) {
  const value = String(status || "").toLowerCase();
  if (value === "confirmed") return "ok";
  if (value === "cancelled") return "bad";
  return "wait";
}

watch(
  () => props.activeTab,
  (value) => {
    if (typeof value === "string" && value && value !== localActiveTab.value) {
      localActiveTab.value = value;
    }
  },
);
</script>

<template>
  <main class="vendor-dashboard">
    <aside class="sidebar">
      <div class="brand">
        <img :src="props.appLogoSrc || '/achar-logo.png'" alt="Achar logo" />
        <div>
          <strong>Achar</strong>
          <span>{{ uiText.vendorPortal }}</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <button
          v-for="item in navItems"
          :key="item.key"
          type="button"
          class="sidebar-link"
          :class="{ active: localActiveTab === item.key }"
          @click="setActiveTab(item.key)"
        >
          <span class="sidebar-icon" aria-hidden="true">
            <svg
              v-if="item.key === 'overview'"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <rect
                x="3"
                y="3"
                width="8"
                height="8"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <rect
                x="13"
                y="3"
                width="8"
                height="6"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <rect
                x="13"
                y="11"
                width="8"
                height="10"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <rect
                x="3"
                y="13"
                width="8"
                height="8"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
            </svg>
            <svg
              v-else-if="item.key === 'services'"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9 7V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
              <rect
                x="3"
                y="7"
                width="18"
                height="14"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <path
                d="M3 12h18"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
            <svg
              v-else-if="item.key === 'bookings'"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <rect
                x="3"
                y="4"
                width="18"
                height="18"
                rx="2"
                stroke="currentColor"
                stroke-width="2"
              />
              <path
                d="M8 2v4M16 2v4"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
              <path
                d="M3 10h18"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
              <path
                d="M8 15l2 2 4-4"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <svg
              v-else-if="item.key === 'messages'"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M21 14a4 4 0 0 1-4 4H9l-6 4V6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z"
                stroke="currentColor"
                stroke-width="2"
                stroke-linejoin="round"
              />
              <path
                d="M7.5 9.5h9M7.5 13h6"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
            <svg
              v-else
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M4 19V5"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
              <path
                d="M4 19h16"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
              <rect
                x="7"
                y="12"
                width="3"
                height="6"
                rx="1"
                stroke="currentColor"
                stroke-width="2"
              />
              <rect
                x="12"
                y="9"
                width="3"
                height="9"
                rx="1"
                stroke="currentColor"
                stroke-width="2"
              />
              <rect
                x="17"
                y="6"
                width="3"
                height="12"
                rx="1"
                stroke="currentColor"
                stroke-width="2"
              />
            </svg>
          </span>
          <span>{{ item.label }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <RouterLink class="side-utility home" to="/">{{
          uiText.backHome
        }}</RouterLink>
        <button type="button" class="side-utility">
          {{ uiText.settings }}
        </button>
        <button
          type="button"
          class="side-utility logout"
          @click="props.logoutUser"
        >
          {{ uiText.logout }}
        </button>
        <div class="vendor-card">
          <span class="vendor-avatar">{{
            (props.vendorDisplayName || "V").slice(0, 1).toUpperCase()
          }}</span>
          <div>
            <strong>{{ props.vendorDisplayName || uiText.vendor }}</strong>
            <small>{{ uiText.verifiedWorkspace }}</small>
          </div>
        </div>
      </div>
    </aside>

    <section class="main-panel">
      <header class="dashboard-head vendor-dashboard-head">
        <div class="dashboard-head-main">
          <span class="dash-chip">{{ uiText.dashboardEyebrow }}</span>
          <h1>{{ uiText.dashboardTitle }}</h1>
          <p>{{ uiText.dashboardText }}</p>
          <div class="dashboard-inline-stats">
            <span
              ><strong>{{ safeIncome.activeServices }}</strong>
              {{ uiText.activeServicesListed }}</span
            >
            <span
              ><strong>{{ safeIncome.newBookings }}</strong>
              {{ uiText.newRequests }}</span
            >
            <span
              ><strong>{{ safeMessagesSummary }}</strong>
              {{ uiText.unreadMessages }}</span
            >
          </div>
        </div>

        <div class="vendor-head-actions">
          <div class="dashboard-actions">
            <button
              type="button"
              class="btn-accent"
              @click="openCreateServiceModal"
            >
              {{ uiText.newService }}
            </button>
          </div>
          <div class="signed-user">
            <span>{{ uiText.signedInAs }}</span>
            <strong>{{ props.vendorDisplayName || uiText.vendor }}</strong>
          </div>
        </div>
      </header>

      <section class="stats-grid">
        <article class="stat-card stat-income">
          <div class="stat-header-row">
            <span class="stat-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <circle
                  cx="12"
                  cy="12"
                  r="9"
                  stroke="currentColor"
                  stroke-width="1.8"
                />
                <path
                  d="M12 7v10M9.5 9.5C9.5 8.4 10.6 7.5 12 7.5s2.5.9 2.5 2c0 1-.7 1.7-2.5 2-1.8.3-2.5 1-2.5 2 0 1.1 1.1 2 2.5 2s2.5-.9 2.5-2"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
              </svg>
            </span>
            <small>{{ uiText.totalIncome }}</small>
          </div>
          <strong>${{ safeIncome.total.toLocaleString() }}</strong>
          <span>{{ uiText.confirmedRevenue }}</span>
        </article>
        <article class="stat-card stat-bookings">
          <div class="stat-header-row">
            <span class="stat-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <rect
                  x="3"
                  y="4"
                  width="18"
                  height="18"
                  rx="2"
                  stroke="currentColor"
                  stroke-width="1.8"
                />
                <path
                  d="M8 2v4M16 2v4M3 10h18"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
                <path
                  d="M8 15l2 2 4-4"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <small>{{ uiText.totalBookedServices }}</small>
          </div>
          <strong>{{ safeIncome.confirmedCount }}</strong>
          <span>{{ uiText.completedConfirmations }}</span>
        </article>
        <article class="stat-card stat-requests">
          <div class="stat-header-row">
            <span class="stat-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M12 5v7l4 2"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <circle
                  cx="12"
                  cy="12"
                  r="9"
                  stroke="currentColor"
                  stroke-width="1.8"
                />
              </svg>
            </span>
            <small>{{ uiText.newRequests }}</small>
          </div>
          <strong>{{ safeIncome.newBookings }}</strong>
          <span>{{ uiText.waitingResponse }}</span>
        </article>
        <article class="stat-card accent stat-messages">
          <div class="stat-header-row">
            <span class="stat-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M21 14a4 4 0 0 1-4 4H9l-6 4V6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linejoin="round"
                />
                <path
                  d="M7.5 9.5h9M7.5 13h6"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
              </svg>
            </span>
            <small>{{ uiText.unreadMessages }}</small>
          </div>
          <strong>{{ safeMessagesSummary }}</strong>
          <span>{{ uiText.conversationsNeedAttention }}</span>
        </article>
      </section>

      <section
        v-show="localActiveTab === 'overview'"
        class="content-grid overview-grid"
      >
        <article class="panel panel-wide">
          <div class="panel-head">
            <div>
              <p class="eyebrow">{{ uiText.performance }}</p>
              <h2>{{ uiText.incomeTrend }}</h2>
            </div>
            <div class="period-switcher">
              <button
                v-for="option in incomePeriodOptions"
                :key="option.key"
                type="button"
                class="period-chip"
                :class="{ active: incomePeriod === option.key }"
                @click="setIncomePeriod(option.key)"
              >
                {{ option.label }}
              </button>
            </div>
          </div>
          <div class="income-chart-card">
            <div class="income-chart-summary">
              <article class="metric-tile">
                <small>{{ activeIncomePeriod.label }}</small>
                <strong>{{ formatCurrency(activeIncomePeriod.total) }}</strong>
                <span>{{ uiText.confirmedRevenueRange }}</span>
              </article>
              <article class="metric-tile">
                <small>{{ uiText.average }}</small>
                <strong>{{ formatCurrency(incomeAverageValue) }}</strong>
                <span>{{ uiText.averagePerPoint }}</span>
              </article>
              <article class="metric-tile">
                <small>{{ uiText.peak }}</small>
                <strong>{{ formatCurrency(incomePeakValue) }}</strong>
                <span>{{ topIncomePoint?.fullLabel || uiText.noPeakYet }}</span>
              </article>
            </div>

            <div v-if="activeIncomePoints.length" class="income-chart-layout">
              <div class="chart-scale">
                <span>{{ formatCurrency(incomePeakValue) }}</span>
                <span>{{ formatCurrency(incomeMidValue) }}</span>
                <span>$0</span>
              </div>
              <div class="chart-shell">
                <div class="chart-plot">
                  <div class="chart-grid" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>
                  <svg
                    viewBox="0 0 100 100"
                    class="income-chart"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                    @pointermove="updateChartHover"
                    @pointerleave="clearChartHover"
                    @pointerdown="updateChartHover"
                  >
                    <defs>
                      <linearGradient
                        id="income-area"
                        x1="0%"
                        y1="0%"
                        x2="0%"
                        y2="100%"
                      >
                        <stop
                          offset="0%"
                          stop-color="#ea580c"
                          stop-opacity="0.24"
                        />
                        <stop
                          offset="100%"
                          stop-color="#ea580c"
                          stop-opacity="0.02"
                        />
                      </linearGradient>
                      <linearGradient
                        id="income-line-gradient"
                        x1="0%"
                        y1="0%"
                        x2="100%"
                        y2="0%"
                      >
                        <stop offset="0%" stop-color="#f97316" />
                        <stop offset="100%" stop-color="#c2410c" />
                      </linearGradient>
                    </defs>
                    <line
                      class="income-average-line"
                      x1="0"
                      :y1="averageLineY"
                      x2="100"
                      :y2="averageLineY"
                    />
                    <path class="income-area" :d="incomeAreaPath" />
                    <path
                      class="income-line-shadow"
                      :d="incomeLineShadowPath"
                    />
                    <path class="income-line" :d="incomeChartPath" />
                    <template v-if="showIncomeDots">
                      <circle
                        v-for="(point, index) in normalizedIncomeChartPoints"
                        :key="`${incomePeriod}-${index}`"
                        class="income-dot"
                        :cx="point.x"
                        :cy="point.y"
                        r="1.1"
                      />
                    </template>
                    <line
                      v-if="chartHoverPoint"
                      class="income-hover-line"
                      :x1="chartHoverPoint.x"
                      y1="0"
                      :x2="chartHoverPoint.x"
                      y2="100"
                    />
                    <circle
                      v-if="chartHoverPoint"
                      class="income-hover-halo"
                      :cx="chartHoverPoint.x"
                      :cy="chartHoverPoint.y"
                      r="4.2"
                    />
                    <circle
                      v-if="chartHoverPoint"
                      class="income-hover-dot"
                      :cx="chartHoverPoint.x"
                      :cy="chartHoverPoint.y"
                      r="2.2"
                    />
                    <circle
                      v-if="latestIncomePoint"
                      class="income-dot-highlight"
                      :class="{ dim: chartHoverPoint }"
                      :cx="latestIncomePoint.x"
                      :cy="latestIncomePoint.y"
                      r="3.2"
                    />
                    <circle
                      v-if="latestIncomePoint"
                      class="income-dot-core"
                      :class="{ dim: chartHoverPoint }"
                      :cx="latestIncomePoint.x"
                      :cy="latestIncomePoint.y"
                      r="1.9"
                    />
                  </svg>
                  <div
                    v-if="chartHoverPoint"
                    class="chart-tooltip"
                    :style="{ left: chartTooltipLeft, top: chartTooltipTop }"
                  >
                    <strong>{{ formatCurrency(chartHoverPoint.value) }}</strong>
                    <span>{{
                      chartHoverPoint.fullLabel || chartHoverPoint.label
                    }}</span>
                  </div>
                </div>
                <div class="chart-labels">
                  <span
                    v-for="(point, index) in activeIncomePoints"
                    :key="`${point.label}-${index}`"
                  >
                    {{
                      index === 0 ||
                      index === activeIncomePoints.length - 1 ||
                      index % incomeLabelStep === 0
                        ? point.label
                        : ""
                    }}
                  </span>
                </div>
              </div>
              <aside class="chart-insights">
                <article>
                  <small>{{ uiText.bestPeriod }}</small>
                  <strong>{{ topIncomePoint?.label || uiText.na }}</strong>
                  <span>{{ formatCurrency(topIncomePoint?.value || 0) }}</span>
                </article>
                <article>
                  <small>{{ uiText.performanceLabel }}</small>
                  <strong>{{ safeIncome.confirmedCount }}</strong>
                  <span>{{ uiText.confirmedBookings }}</span>
                </article>
                <article>
                  <small>{{ uiText.coverage }}</small>
                  <strong>{{ safeIncome.activeServices }}</strong>
                  <span>{{ uiText.activeServicesListed }}</span>
                </article>
              </aside>
            </div>

            <p v-else class="notice">{{ uiText.noConfirmedIncome }}</p>
          </div>
        </article>
      </section>

      <section
        v-show="localActiveTab === 'services'"
        class="content-grid services-grid"
      >
        <article class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">{{ uiText.createService }}</p>
              <h2>{{ uiText.insertService }}</h2>
            </div>
            <span class="badge">Visible to users when active</span>
          </div>

          <form class="service-form" @submit.prevent="submitServiceForm">
            <label class="field">
              <span>Service name</span>
              <input
                :value="props.vendorServiceForm?.title || ''"
                type="text"
                placeholder="Community Workshop"
                @input="props.vendorServiceForm.title = $event.target.value"
              />
            </label>

            <label class="field">
              <span>Types</span>
              <select
                :value="props.vendorServiceForm?.event_type || ''"
                @change="
                  props.vendorServiceForm.event_type = $event.target.value
                "
              >
                <option
                  v-for="option in eventOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
            </label>

            <label class="field">
              <span>Number of count</span>
              <input
                :value="props.vendorServiceForm?.capacity ?? 1"
                type="number"
                min="1"
                placeholder="50"
                @input="
                  props.vendorServiceForm.capacity = Number($event.target.value)
                "
              />
            </label>

            <label class="field">
              <span>Price</span>
              <input
                :value="props.vendorServiceForm?.price ?? 0"
                type="number"
                min="0"
                step="0.01"
                placeholder="150"
                @input="
                  props.vendorServiceForm.price = Number($event.target.value)
                "
              />
            </label>

            <label class="field">
              <span>Location</span>
              <input
                :value="props.vendorServiceForm?.location || ''"
                type="text"
                placeholder="Phnom Penh"
                @input="props.vendorServiceForm.location = $event.target.value"
              />
            </label>

            <div class="field">
              <span>Map location</span>
              <button
                type="button"
                class="secondary-button location-button"
                :disabled="isDetectingVendorLocation"
                @click="detectVendorLocation"
              >
                {{
                  isDetectingVendorLocation
                    ? "Detecting location..."
                    : "Use Current Location"
                }}
              </button>
            </div>

            <div class="field field-full location-tools">
              <p v-if="vendorLocationNotice" class="location-notice">
                {{ vendorLocationNotice }}
              </p>
              <p
                v-if="
                  props.vendorServiceForm?.location_latitude !== null &&
                  props.vendorServiceForm?.location_longitude !== null
                "
                class="location-coords"
              >
                Lat:
                {{
                  Number(props.vendorServiceForm.location_latitude).toFixed(6)
                }}, Lng:
                {{
                  Number(props.vendorServiceForm.location_longitude).toFixed(6)
                }}
              </p>
              <iframe
                v-if="vendorLocationMapEmbedUrl"
                class="location-map-frame"
                :src="vendorLocationMapEmbedUrl"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
              <a
                v-if="vendorLocationMapLinkUrl"
                class="location-map-link"
                :href="vendorLocationMapLinkUrl"
                target="_blank"
                rel="noopener noreferrer"
              >
                Open current location in map
              </a>
            </div>

            <label class="field">
              <span>Start date and time</span>
              <input
                :value="props.vendorServiceForm?.starts_at || ''"
                type="datetime-local"
                @input="props.vendorServiceForm.starts_at = $event.target.value"
              />
            </label>

            <label class="field field-full">
              <span>Picture of services</span>
              <input
                type="file"
                accept="image/*"
                @change="handleVendorServiceImageChange"
              />
              <small class="field-hint"
                >Choose an image from your device.</small
              >
            </label>

            <label class="field field-full">
              <span>Or paste image link</span>
              <input
                :value="props.vendorServiceForm?.image_url || ''"
                type="url"
                placeholder="https://example.com/service-photo.jpg"
                @input="handleVendorServiceImageUrlInput"
              />
            </label>

            <div
              v-if="props.vendorServiceForm?.image_url"
              class="field field-full"
            >
              <span>Preview</span>
              <div class="image-preview-card">
                <img
                  class="image-preview"
                  :src="props.vendorServiceForm.image_url"
                  alt="Selected service preview"
                />
                <button
                  type="button"
                  class="secondary-button"
                  @click="clearVendorServiceImage"
                >
                  Remove image
                </button>
              </div>
            </div>

            <label class="field field-full">
              <span>Service information</span>
              <textarea
                :value="props.vendorServiceForm?.description || ''"
                placeholder="Describe the service, what is included, and what the customer should know."
                @input="
                  props.vendorServiceForm.description = $event.target.value
                "
              ></textarea>
            </label>

            <div class="form-actions">
              <button
                type="submit"
                class="primary-button"
                :disabled="props.isSubmittingVendorService"
              >
                {{
                  props.isSubmittingVendorService
                    ? "Saving..."
                    : "Create Service"
                }}
              </button>
            </div>
          </form>

          <p
            v-if="props.vendorServiceNotice"
            class="notice"
            :class="{
              'notice-success': vendorServiceNoticeTone === 'success',
              'notice-error': vendorServiceNoticeTone === 'error',
            }"
          >
            {{ props.vendorServiceNotice }}
          </p>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">{{ uiText.currentListings }}</p>
              <h2>{{ uiText.myServices }}</h2>
            </div>
            <span class="badge">{{ safeVendorEvents.length }} services</span>
          </div>

          <p v-if="props.isLoadingEvents" class="notice">
            {{ uiText.loadingServices }}
          </p>
          <p v-else-if="!safeVendorEvents.length" class="notice">
            No service yet. Create one from the form.
          </p>
          <ul v-else class="service-list">
            <li
              v-for="item in safeVendorEvents"
              :key="item.id"
              class="service-item"
            >
              <img class="service-image" :src="item.image" :alt="item.title" />
              <div class="service-copy">
                <div class="service-header">
                  <strong>{{ item.title }}</strong>
                  <span class="service-state" :class="{ live: item.isActive }">
                    {{ item.isActive ? "Active" : "Paused" }}
                  </span>
                </div>
                <small>{{ item.eventTypeLabel }} | {{ item.priceLabel }}</small>
                <p>{{ item.description }}</p>
              </div>
              <div class="row-actions">
                <button
                  type="button"
                  @click="props.toggleVendorServiceActive(item)"
                >
                  {{ item.isActive ? "Pause" : "Activate" }}
                </button>
                <button
                  type="button"
                  class="danger"
                  @click="props.deleteVendorService(item)"
                >
                  Delete
                </button>
              </div>
            </li>
          </ul>
        </article>
      </section>

      <section v-show="localActiveTab === 'bookings'" class="panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">{{ uiText.bookingRequests }}</p>
            <h2>{{ uiText.newBookingRequests }}</h2>
          </div>
        </div>

        <div class="mini-stats-row">
          <div class="mini-stat-card mini-stat-green">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <rect
                    x="3"
                    y="4"
                    width="18"
                    height="18"
                    rx="2"
                    stroke="currentColor"
                    stroke-width="1.8"
                  />
                  <path
                    d="M8 2v4M16 2v4M3 10h18"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                  />
                  <path
                    d="M8 15l2 2 4-4"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <small>Total</small>
            </div>
            <strong>{{ safeVendorBookings.length }}</strong>
            <span>All booking requests</span>
          </div>
          <div class="mini-stat-card mini-stat-orange">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M12 5v7l4 2"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke="currentColor"
                    stroke-width="1.8"
                  />
                </svg>
              </span>
              <small>Pending</small>
            </div>
            <strong>{{
              safeVendorBookings.filter((b) => b.status === "pending").length
            }}</strong>
            <span>Awaiting confirmation</span>
          </div>
          <div class="mini-stat-card mini-stat-blue">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke="currentColor"
                    stroke-width="1.8"
                  />
                  <path
                    d="M8 12l3 3 5-5"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <small>Confirmed</small>
            </div>
            <strong>{{
              safeVendorBookings.filter((b) => b.status === "confirmed").length
            }}</strong>
            <span>Active bookings</span>
          </div>
          <div class="mini-stat-card mini-stat-red">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle
                    cx="12"
                    cy="12"
                    r="9"
                    stroke="currentColor"
                    stroke-width="1.8"
                  />
                  <path
                    d="M9 9l6 6M15 9l-6 6"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                  />
                </svg>
              </span>
              <small>Cancelled</small>
            </div>
            <strong>{{
              safeVendorBookings.filter((b) => b.status === "cancelled").length
            }}</strong>
            <span>Cancelled requests</span>
          </div>
        </div>

        <p v-if="props.isLoadingVendorBookings" class="notice">
          {{ uiText.loadingBookings }}
        </p>
        <p v-else-if="!safeVendorBookings.length" class="notice">
          No booking requests yet.
        </p>
        <table v-else class="table">
          <thead>
            <tr>
              <th>Service Name</th>
              <th>Client</th>
              <th>Date & Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in safeVendorBookings" :key="item.id">
              <td>{{ item.service_name }}</td>
              <td>{{ item.customer_name }}</td>
              <td>{{ item.date_label }}</td>
              <td>
                <span
                  class="status-chip"
                  :class="bookingStatusClass(item.status)"
                >
                  {{ item.status }}
                </span>
              </td>
              <td class="row-actions">
                <button
                  type="button"
                  @click="props.updateVendorBookingStatus(item, 'confirmed')"
                >
                  Confirm
                </button>
                <button
                  type="button"
                  class="danger"
                  @click="props.updateVendorBookingStatus(item, 'cancelled')"
                >
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section v-show="localActiveTab === 'messages'" class="panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Inbox</p>
            <h2>{{ uiText.customerMessages }}</h2>
          </div>
          <span class="badge">{{ safeMessagesSummary }} unread</span>
        </div>

        <div class="mini-stats-row">
          <div class="mini-stat-card mini-stat-orange">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M21 14a4 4 0 0 1-4 4H9l-6 4V6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <small>Unread</small>
            </div>
            <strong>{{ safeMessagesSummary }}</strong>
            <span>Messages to review</span>
          </div>
          <div class="mini-stat-card mini-stat-blue">
            <div class="stat-header-row">
              <span class="stat-icon">
                <svg
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M8 12h.01M12 12h.01M16 12h.01"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                  />
                  <path
                    d="M21 14a4 4 0 0 1-4 4H9l-6 4V6a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <small>Inbox</small>
            </div>
            <strong>Open</strong>
            <span>Customer conversations</span>
          </div>
        </div>

        <p class="panel-copy">
          Respond quickly to customer questions and booking confirmations.
        </p>
        <button type="button" class="primary-button" @click="openMessages">
          {{ uiText.openMessages }}
        </button>
      </section>

      <section v-show="localActiveTab === 'income'" class="income-layout">
        <article class="panel panel-wide">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Analytics</p>
              <h2>{{ uiText.incomeInsights }}</h2>
            </div>
            <div class="period-switcher">
              <button
                v-for="option in incomePeriodOptions"
                :key="`analytics-${option.key}`"
                type="button"
                class="period-chip"
                :class="{ active: incomePeriod === option.key }"
                @click="setIncomePeriod(option.key)"
              >
                {{ option.label }}
              </button>
            </div>
          </div>

          <div class="income-chart-card">
            <div class="income-chart-summary income-chart-summary-wide">
              <article class="metric-tile">
                <small>Selected period</small>
                <strong>{{ activeIncomePeriod.label }}</strong>
                <span>Reporting window</span>
              </article>
              <article class="metric-tile">
                <small>Income</small>
                <strong>{{ formatCurrency(activeIncomePeriod.total) }}</strong>
                <span>Confirmed revenue only</span>
              </article>
              <article class="metric-tile">
                <small>Average</small>
                <strong>{{ formatCurrency(incomeAverageValue) }}</strong>
                <span>Average per displayed period</span>
              </article>
              <article class="metric-tile">
                <small>Peak</small>
                <strong>{{ formatCurrency(incomePeakValue) }}</strong>
                <span>{{ topIncomePoint?.fullLabel || "No peak yet" }}</span>
              </article>
            </div>

            <div
              v-if="activeIncomePoints.length"
              class="income-chart-layout analytics-chart-layout"
            >
              <div class="chart-scale">
                <span>{{ formatCurrency(incomePeakValue) }}</span>
                <span>{{ formatCurrency(incomeMidValue) }}</span>
                <span>$0</span>
              </div>
              <div class="chart-shell">
                <div class="chart-plot">
                  <div class="chart-grid" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>
                  <svg
                    viewBox="0 0 100 100"
                    class="income-chart"
                    preserveAspectRatio="none"
                    aria-hidden="true"
                    @pointermove="updateChartHover"
                    @pointerleave="clearChartHover"
                    @pointerdown="updateChartHover"
                  >
                    <defs>
                      <linearGradient
                        id="income-area-analytics"
                        x1="0%"
                        y1="0%"
                        x2="0%"
                        y2="100%"
                      >
                        <stop
                          offset="0%"
                          stop-color="#ea580c"
                          stop-opacity="0.24"
                        />
                        <stop
                          offset="100%"
                          stop-color="#ea580c"
                          stop-opacity="0.02"
                        />
                      </linearGradient>
                      <linearGradient
                        id="income-line-gradient-analytics"
                        x1="0%"
                        y1="0%"
                        x2="100%"
                        y2="0%"
                      >
                        <stop offset="0%" stop-color="#f97316" />
                        <stop offset="100%" stop-color="#c2410c" />
                      </linearGradient>
                    </defs>
                    <line
                      class="income-average-line"
                      x1="0"
                      :y1="averageLineY"
                      x2="100"
                      :y2="averageLineY"
                    />
                    <path class="income-area alt" :d="incomeAreaPath" />
                    <path
                      class="income-line-shadow"
                      :d="incomeLineShadowPath"
                    />
                    <path class="income-line" :d="incomeChartPath" />
                    <template v-if="showIncomeDots">
                      <circle
                        v-for="(point, index) in normalizedIncomeChartPoints"
                        :key="`analytics-${incomePeriod}-${index}`"
                        class="income-dot"
                        :cx="point.x"
                        :cy="point.y"
                        r="1.1"
                      />
                    </template>
                    <line
                      v-if="chartHoverPoint"
                      class="income-hover-line"
                      :x1="chartHoverPoint.x"
                      y1="0"
                      :x2="chartHoverPoint.x"
                      y2="100"
                    />
                    <circle
                      v-if="chartHoverPoint"
                      class="income-hover-halo"
                      :cx="chartHoverPoint.x"
                      :cy="chartHoverPoint.y"
                      r="4.2"
                    />
                    <circle
                      v-if="chartHoverPoint"
                      class="income-hover-dot"
                      :cx="chartHoverPoint.x"
                      :cy="chartHoverPoint.y"
                      r="2.2"
                    />
                    <circle
                      v-if="latestIncomePoint"
                      class="income-dot-highlight"
                      :class="{ dim: chartHoverPoint }"
                      :cx="latestIncomePoint.x"
                      :cy="latestIncomePoint.y"
                      r="3.2"
                    />
                    <circle
                      v-if="latestIncomePoint"
                      class="income-dot-core"
                      :class="{ dim: chartHoverPoint }"
                      :cx="latestIncomePoint.x"
                      :cy="latestIncomePoint.y"
                      r="1.9"
                    />
                  </svg>
                  <div
                    v-if="chartHoverPoint"
                    class="chart-tooltip"
                    :style="{ left: chartTooltipLeft, top: chartTooltipTop }"
                  >
                    <strong>{{ formatCurrency(chartHoverPoint.value) }}</strong>
                    <span>{{
                      chartHoverPoint.fullLabel || chartHoverPoint.label
                    }}</span>
                  </div>
                </div>
                <div class="chart-labels">
                  <span
                    v-for="(point, index) in activeIncomePoints"
                    :key="`analytics-${point.label}-${index}`"
                  >
                    {{
                      index === 0 ||
                      index === activeIncomePoints.length - 1 ||
                      index % incomeLabelStep === 0
                        ? point.label
                        : ""
                    }}
                  </span>
                </div>
              </div>
              <aside class="chart-insights">
                <article>
                  <small>Best period</small>
                  <strong>{{ topIncomePoint?.label || "N/A" }}</strong>
                  <span>{{ formatCurrency(topIncomePoint?.value || 0) }}</span>
                </article>
                <article>
                  <small>This month</small>
                  <strong>{{ formatCurrency(safeIncome.thisMonth) }}</strong>
                  <span>Current calendar month</span>
                </article>
                <article>
                  <small>This year</small>
                  <strong>{{ formatCurrency(safeIncome.thisYear) }}</strong>
                  <span>Current calendar year</span>
                </article>
              </aside>
            </div>
          </div>
        </article>

        <section class="stats-grid income-stats-grid">
          <article class="stat-card">
            <small>This Month</small>
            <strong>{{ formatCurrency(safeIncome.thisMonth) }}</strong>
            <span>Confirmed income this calendar month</span>
          </article>
          <article class="stat-card">
            <small>This Year</small>
            <strong>{{ formatCurrency(safeIncome.thisYear) }}</strong>
            <span>Confirmed income this calendar year</span>
          </article>
          <article class="stat-card">
            <small>Active Services</small>
            <strong>{{ safeIncome.activeServices }}</strong>
            <span>Public listings currently visible</span>
          </article>
          <article class="stat-card accent">
            <small>Confirmed Bookings</small>
            <strong>{{ safeIncome.confirmedCount }}</strong>
            <span>Orders already producing income</span>
          </article>
        </section>
      </section>
    </section>

    <div
      v-if="isCreateServiceModalOpen"
      class="modal-backdrop"
      @click="closeCreateServiceModal"
    >
      <section class="modal-card" @click.stop>
        <div class="panel-head">
          <div>
            <p class="eyebrow">{{ uiText.createService }}</p>
            <h2>{{ uiText.addNewService }}</h2>
          </div>
          <button
            type="button"
            class="secondary-button"
            @click="closeCreateServiceModal"
          >
            Close
          </button>
        </div>

        <form class="service-form" @submit.prevent="submitServiceForm">
          <label class="field">
            <span>Service name</span>
            <input
              :value="props.vendorServiceForm?.title || ''"
              type="text"
              placeholder="Community Workshop"
              @input="props.vendorServiceForm.title = $event.target.value"
            />
          </label>

          <label class="field">
            <span>Types</span>
            <select
              :value="props.vendorServiceForm?.event_type || ''"
              @change="props.vendorServiceForm.event_type = $event.target.value"
            >
              <option
                v-for="option in eventOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Number of count</span>
            <input
              :value="props.vendorServiceForm?.capacity ?? 1"
              type="number"
              min="1"
              placeholder="50"
              @input="
                props.vendorServiceForm.capacity = Number($event.target.value)
              "
            />
          </label>

          <label class="field">
            <span>Price</span>
            <input
              :value="props.vendorServiceForm?.price ?? 0"
              type="number"
              min="0"
              step="0.01"
              placeholder="150"
              @input="
                props.vendorServiceForm.price = Number($event.target.value)
              "
            />
          </label>

          <label class="field">
            <span>Location</span>
            <input
              :value="props.vendorServiceForm?.location || ''"
              type="text"
              placeholder="Phnom Penh"
              @input="props.vendorServiceForm.location = $event.target.value"
            />
          </label>

          <div class="field">
            <span>Map location</span>
            <button
              type="button"
              class="secondary-button location-button"
              :disabled="isDetectingVendorLocation"
              @click="detectVendorLocation"
            >
              {{
                isDetectingVendorLocation
                  ? "Detecting location..."
                  : "Use Current Location"
              }}
            </button>
          </div>

          <div class="field field-full location-tools">
            <p v-if="vendorLocationNotice" class="location-notice">
              {{ vendorLocationNotice }}
            </p>
            <p
              v-if="
                props.vendorServiceForm?.location_latitude !== null &&
                props.vendorServiceForm?.location_longitude !== null
              "
              class="location-coords"
            >
              Lat:
              {{
                Number(props.vendorServiceForm.location_latitude).toFixed(6)
              }}, Lng:
              {{
                Number(props.vendorServiceForm.location_longitude).toFixed(6)
              }}
            </p>
            <iframe
              v-if="vendorLocationMapEmbedUrl"
              class="location-map-frame"
              :src="vendorLocationMapEmbedUrl"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
            <a
              v-if="vendorLocationMapLinkUrl"
              class="location-map-link"
              :href="vendorLocationMapLinkUrl"
              target="_blank"
              rel="noopener noreferrer"
            >
              Open current location in map
            </a>
          </div>

          <label class="field">
            <span>Start date and time</span>
            <input
              :value="props.vendorServiceForm?.starts_at || ''"
              type="datetime-local"
              @input="props.vendorServiceForm.starts_at = $event.target.value"
            />
          </label>

          <label class="field field-full">
            <span>Picture of services</span>
            <input
              type="file"
              accept="image/*"
              @change="handleVendorServiceImageChange"
            />
            <small class="field-hint">Choose an image from your device.</small>
          </label>

          <label class="field field-full">
            <span>Or paste image link</span>
            <input
              :value="props.vendorServiceForm?.image_url || ''"
              type="url"
              placeholder="https://example.com/service-photo.jpg"
              @input="handleVendorServiceImageUrlInput"
            />
          </label>

          <div
            v-if="props.vendorServiceForm?.image_url"
            class="field field-full"
          >
            <span>Preview</span>
            <div class="image-preview-card">
              <img
                class="image-preview"
                :src="props.vendorServiceForm.image_url"
                alt="Selected service preview"
              />
              <button
                type="button"
                class="secondary-button"
                @click="clearVendorServiceImage"
              >
                Remove image
              </button>
            </div>
          </div>

          <label class="field field-full">
            <span>Service information</span>
            <textarea
              :value="props.vendorServiceForm?.description || ''"
              placeholder="Describe the service, what is included, and what the customer should know."
              @input="props.vendorServiceForm.description = $event.target.value"
            ></textarea>
          </label>

          <div class="form-actions">
            <button
              type="submit"
              class="primary-button"
              :disabled="props.isSubmittingVendorService"
            >
              {{
                props.isSubmittingVendorService ? "Saving..." : "Create Service"
              }}
            </button>
          </div>
        </form>

        <p
          v-if="props.vendorServiceNotice"
          class="notice"
          :class="{
            'notice-success': vendorServiceNoticeTone === 'success',
            'notice-error': vendorServiceNoticeTone === 'error',
          }"
        >
          {{ props.vendorServiceNotice }}
        </p>
      </section>
    </div>
  </main>
</template>

<style scoped>
.vendor-dashboard {
  --vd-accent: #ea580c;
  --vd-accent-strong: #c2410c;
  --vd-border: rgba(148, 163, 184, 0.22);
  --vd-surface: rgba(255, 255, 255, 0.72);
  --vd-text: #0f172a;
  --vd-muted: #475569;

  position: relative;
  min-height: 100vh;
  display: grid;
  grid-template-columns: 272px minmax(0, 1fr);
  background:
    radial-gradient(
      circle at 12% 12%,
      rgba(234, 88, 12, 0.12),
      transparent 36%
    ),
    radial-gradient(circle at 92% 6%, rgba(37, 99, 235, 0.1), transparent 40%),
    linear-gradient(180deg, #f8fafc 0%, #eef2f7 55%, #fff7ed 100%);
  color: var(--vd-text);
  isolation: isolate;
  font-family:
    "Inter",
    system-ui,
    -apple-system,
    sans-serif;
}

.vendor-dashboard::after {
  content: "";
  position: absolute;
  inset: -40% -30% auto auto;
  width: 520px;
  height: 520px;
  border-radius: 999px;
  background: radial-gradient(
    circle,
    rgba(234, 88, 12, 0.14) 0%,
    transparent 62%
  );
  filter: blur(42px);
  opacity: 0.7;
  pointer-events: none;
  z-index: 0;
}

/* ─── Sidebar ─────────────────────────────────────────────────── */
.sidebar {
  position: sticky;
  top: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 22px 16px;
  background: var(--vd-surface);
  border-right: 1px solid var(--vd-border);
  backdrop-filter: blur(22px);
  -webkit-backdrop-filter: blur(22px);
  box-shadow: 2px 0 30px rgba(15, 23, 42, 0.07);
  z-index: 10;
  overflow: hidden;
}

/* Subtle decorative blob in sidebar background */
.sidebar::after {
  content: "";
  position: absolute;
  bottom: -60px;
  left: -40px;
  width: 220px;
  height: 220px;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(234, 88, 12, 0.07) 0%,
    transparent 70%
  );
  pointer-events: none;
  z-index: 0;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 4px 4px 18px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.14);
  margin-bottom: 6px;
  position: relative;
  z-index: 1;
}

.brand img {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  object-fit: contain;
  background: #fff;
  border: 1px solid rgba(234, 88, 12, 0.18);
  box-shadow:
    0 4px 14px rgba(234, 88, 12, 0.14),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
}

.brand strong {
  display: block;
  font-size: 21px;
  line-height: 1;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0f172a;
}

.brand span {
  display: block;
  margin-top: 4px;
  color: #ea580c;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

.sidebar-nav {
  display: grid;
  gap: 3px;
  position: relative;
  z-index: 1;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 11px 13px;
  border: 1px solid transparent;
  border-radius: 14px;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  text-align: left;
  transition: all 170ms cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.sidebar-link:focus-visible,
.side-utility:focus-visible {
  outline: 2px solid rgba(234, 88, 12, 0.4);
  outline-offset: 2px;
}

.sidebar-link:hover {
  background: rgba(255, 255, 255, 0.72);
  border-color: rgba(234, 88, 12, 0.2);
  color: #7c2d12;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
  transform: translateX(3px);
}

.sidebar-link.active {
  background: linear-gradient(
    135deg,
    rgba(255, 247, 237, 0.98) 0%,
    rgba(255, 237, 213, 0.9) 100%
  );
  border-color: rgba(234, 88, 12, 0.3);
  color: #7c2d12;
  box-shadow:
    0 8px 24px rgba(234, 88, 12, 0.13),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
}

.sidebar-icon {
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
  border: 1px solid rgba(148, 163, 184, 0.18);
  flex-shrink: 0;
  transition: all 170ms ease;
}

.sidebar-icon svg {
  width: 17px;
  height: 17px;
  display: block;
}

.sidebar-link:hover .sidebar-icon {
  background: rgba(255, 247, 237, 0.9);
  border-color: rgba(234, 88, 12, 0.24);
  color: #ea580c;
}

.sidebar-link.active .sidebar-icon {
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: #fff;
  border-color: rgba(234, 88, 12, 0.4);
  box-shadow: 0 6px 16px rgba(234, 88, 12, 0.3);
}

.sidebar-footer {
  margin-top: auto;
  display: grid;
  gap: 6px;
  padding-top: 14px;
  border-top: 1px solid rgba(148, 163, 184, 0.14);
  position: relative;
  z-index: 1;
}

.side-utility {
  display: block;
  width: 100%;
  padding: 10px 13px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.7);
  color: #334155;
  font-size: 13.5px;
  font-weight: 600;
  text-align: left;
  text-decoration: none;
  transition: all 150ms ease;
  cursor: pointer;
}

.side-utility:hover {
  background: #fff;
  box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08);
  border-color: rgba(148, 163, 184, 0.3);
  color: #0f172a;
}

.side-utility.home {
  background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
  border-color: rgba(234, 88, 12, 0.22);
  color: #9a3412;
  font-weight: 700;
}

.side-utility.home:hover {
  background: #ffedd5;
  border-color: rgba(234, 88, 12, 0.35);
  box-shadow: 0 4px 16px rgba(234, 88, 12, 0.1);
}

.side-utility.logout {
  color: #dc2626;
  border-color: rgba(248, 113, 113, 0.22);
  background: rgba(255, 255, 255, 0.7);
}

.side-utility.logout:hover {
  background: #fef2f2;
  border-color: rgba(220, 38, 38, 0.3);
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.08);
}

.vendor-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: 16px;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.95) 0%,
    rgba(248, 250, 252, 0.9) 100%
  );
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow:
    0 4px 18px rgba(15, 23, 42, 0.06),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
}

.vendor-avatar {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  color: #1d4ed8;
  font-weight: 800;
  font-size: 16px;
  flex-shrink: 0;
  border: 2px solid rgba(59, 130, 246, 0.22);
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);
}

.vendor-card strong {
  display: block;
  font-size: 13.5px;
  color: #0f172a;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.vendor-card small {
  display: block;
  color: #94a3b8;
  font-size: 11px;
  margin-top: 2px;
  font-weight: 500;
}

/* ─── Main Panel ──────────────────────────────────────────────── */
.main-panel {
  display: grid;
  gap: 20px;
  padding: 24px;
  min-height: 0;
  position: relative;
  z-index: 1;
}

.panel,
.stat-card {
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.82);
  box-shadow:
    0 4px 6px rgba(15, 23, 42, 0.04),
    0 20px 60px rgba(15, 23, 42, 0.07),
    0 1px 0 rgba(255, 255, 255, 0.95) inset;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

/* ─── Dashboard Header ────────────────────────────────────────── */
.vendor-dashboard-head {
  margin: 0;
}

.vendor-head-actions {
  display: grid;
  gap: 0.55rem;
  justify-items: end;
}

.vendor-head-actions .btn-accent {
  white-space: nowrap;
}

.vendor-dashboard-head.dashboard-head {
  padding: 1rem 1.2rem;
  background: linear-gradient(
    135deg,
    rgba(255, 255, 255, 0.92) 0%,
    rgba(255, 247, 237, 0.85) 100%
  );
  border: 1px solid rgba(255, 255, 255, 0.8);
  box-shadow:
    0 4px 6px rgba(15, 23, 42, 0.04),
    0 20px 60px rgba(15, 23, 42, 0.07),
    0 1px 0 rgba(255, 255, 255, 0.95) inset;
  border-radius: 24px;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.vendor-dashboard-head h1 {
  font-size: clamp(1.6rem, 2.4vw, 2.4rem);
  line-height: 1.05;
  letter-spacing: -0.03em;
  background: linear-gradient(135deg, #0f172a 30%, #7c2d12 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.vendor-dashboard-head p {
  font-size: 0.92rem;
  color: #64748b;
}

.vendor-dashboard-head .dash-chip {
  margin-bottom: 0.5rem;
  padding: 0.2rem 0.6rem;
  font-size: 0.68rem;
  background: linear-gradient(
    135deg,
    rgba(249, 115, 22, 0.12) 0%,
    rgba(234, 88, 12, 0.08) 100%
  );
  border: 1px solid rgba(249, 115, 22, 0.25);
  border-radius: 999px;
  color: #c2410c;
}

.vendor-dashboard-head .dashboard-inline-stats {
  margin-top: 0.7rem;
  gap: 0.45rem;
}

.vendor-dashboard-head .dashboard-inline-stats span {
  font-size: 0.76rem;
  padding: 0.32rem 0.65rem;
  background: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 999px;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
}

.vendor-dashboard-head .dashboard-actions button {
  border-radius: 12px;
  padding: 0.55rem 0.9rem;
  font-size: 0.9rem;
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  box-shadow: 0 6px 20px rgba(234, 88, 12, 0.32);
  border: 1px solid rgba(194, 65, 12, 0.3);
  transition:
    box-shadow 180ms ease,
    transform 120ms ease;
}

.vendor-dashboard-head .dashboard-actions button:hover {
  box-shadow: 0 8px 28px rgba(234, 88, 12, 0.44);
  transform: translateY(-1px);
}

@supports not (
  (backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))
) {
  .sidebar {
    background: rgba(255, 255, 255, 0.96);
  }

  .panel,
  .stat-card {
    background: rgba(255, 255, 255, 0.96);
  }
}

@media (prefers-reduced-motion: reduce) {
  .sidebar-link {
    transition: none;
  }

  .sidebar-link:hover:not(.active) {
    transform: none;
  }
}

.eyebrow {
  margin: 0 0 6px;
  color: #ea580c;
  font-size: 10.5px;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}

.panel h2 {
  margin: 0;
  line-height: 1.05;
  letter-spacing: -0.04em;
  color: #0f172a;
}

.panel h2 {
  font-size: clamp(18px, 1.7vw, 26px);
  font-weight: 800;
}

.panel-copy,
.stat-card span,
.signed-user span {
  color: #64748b;
}

.panel-copy {
  max-width: 700px;
  font-size: 17px;
  line-height: 1.6;
}

.signed-user {
  text-align: right;
  padding: 10px 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.9);
  box-shadow:
    0 4px 6px rgba(15, 23, 42, 0.04),
    0 14px 36px rgba(15, 23, 42, 0.07);
}

.signed-user span {
  display: block;
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.13em;
  color: rgba(100, 116, 139, 0.9);
}

.signed-user strong {
  display: block;
  margin-top: 5px;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
}

.primary-button,
.secondary-button,
.row-actions button {
  border-radius: 12px;
  padding: 11px 16px;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: all 150ms ease;
}

.primary-button {
  border: 1px solid rgba(194, 65, 12, 0.4);
  background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
  color: #fff;
  box-shadow: 0 4px 14px rgba(234, 88, 12, 0.3);
}

.primary-button:hover:not(:disabled) {
  box-shadow: 0 6px 20px rgba(234, 88, 12, 0.42);
  transform: translateY(-1px);
}

.secondary-button,
.row-actions button {
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: #fff7ed;
  color: #9a3412;
}

.secondary-button:hover:not(:disabled),
.row-actions button:hover:not(:disabled) {
  background: #ffedd5;
  border-color: rgba(234, 88, 12, 0.3);
}

.row-actions .danger {
  background: #fff;
  color: #dc2626;
  border-color: rgba(248, 113, 113, 0.28);
}

.row-actions .danger:hover:not(:disabled) {
  background: #fef2f2;
  border-color: rgba(220, 38, 38, 0.35);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

/* Mini stat cards inside booking/messages panels */
.mini-stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
  margin-bottom: 16px;
}

.mini-stat-card {
  padding: 10px 12px 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 1px;
  position: relative;
  overflow: hidden;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  transition:
    box-shadow 160ms ease,
    transform 130ms ease;
}

.mini-stat-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
}

.mini-stat-card::before {
  content: "";
  position: absolute;
  top: 8px;
  bottom: 8px;
  left: 0;
  width: 3px;
  border-radius: 0 2px 2px 0;
}

.mini-stat-green::before {
  background: linear-gradient(180deg, #16a34a, #4ade80);
}
.mini-stat-orange::before {
  background: linear-gradient(180deg, #ea580c, #fb923c);
}
.mini-stat-blue::before {
  background: linear-gradient(180deg, #2563eb, #60a5fa);
}
.mini-stat-red::before {
  background: linear-gradient(180deg, #dc2626, #f87171);
}

.mini-stat-card .stat-header-row {
  margin-bottom: 3px;
}

.mini-stat-card small {
  color: #475569;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  line-height: 1;
}

.mini-stat-card strong {
  font-size: clamp(16px, 1.6vw, 22px);
  line-height: 1.1;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0f172a;
  margin: 2px 0 1px;
}

.mini-stat-card span {
  font-size: 10.5px;
  color: #94a3b8;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mini-stat-card .stat-icon {
  width: 20px;
  height: 20px;
  border-radius: 5px;
}

.mini-stat-card .stat-icon svg {
  width: 11px;
  height: 11px;
}

.mini-stat-green .stat-icon {
  background: rgba(22, 163, 74, 0.08);
  border-color: rgba(22, 163, 74, 0.16);
  color: #16a34a;
}
.mini-stat-orange .stat-icon {
  background: rgba(234, 88, 12, 0.08);
  border-color: rgba(234, 88, 12, 0.16);
  color: #ea580c;
}
.mini-stat-blue .stat-icon {
  background: rgba(37, 99, 235, 0.08);
  border-color: rgba(37, 99, 235, 0.16);
  color: #2563eb;
}
.mini-stat-red .stat-icon {
  background: rgba(220, 38, 38, 0.08);
  border-color: rgba(220, 38, 38, 0.16);
  color: #dc2626;
}

.stat-card {
  padding: 12px 14px 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 1px;
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  transition:
    box-shadow 180ms ease,
    transform 140ms ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow:
    0 6px 10px rgba(15, 23, 42, 0.06),
    0 20px 50px rgba(15, 23, 42, 0.09),
    0 1px 0 rgba(255, 255, 255, 0.95) inset;
}

/* Colored left accent bar */
.stat-card::before {
  content: "";
  position: absolute;
  top: 10px;
  bottom: 10px;
  left: 0;
  width: 3px;
  border-radius: 0 3px 3px 0;
  background: rgba(234, 88, 12, 0.3);
}

.stat-income::before {
  background: linear-gradient(180deg, #16a34a, #4ade80);
}
.stat-bookings::before {
  background: linear-gradient(180deg, #2563eb, #60a5fa);
}
.stat-requests::before {
  background: linear-gradient(180deg, #7c3aed, #a78bfa);
}
.stat-messages::before {
  background: linear-gradient(180deg, #ea580c, #fb923c);
}

/* Header row: icon + label inline */
.stat-header-row {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
}

.stat-icon {
  width: 22px;
  height: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  background: rgba(234, 88, 12, 0.08);
  border: 1px solid rgba(234, 88, 12, 0.14);
  color: #ea580c;
  flex-shrink: 0;
}

.stat-income .stat-icon {
  background: rgba(22, 163, 74, 0.08);
  border-color: rgba(22, 163, 74, 0.16);
  color: #16a34a;
}
.stat-bookings .stat-icon {
  background: rgba(37, 99, 235, 0.08);
  border-color: rgba(37, 99, 235, 0.16);
  color: #2563eb;
}
.stat-requests .stat-icon {
  background: rgba(124, 58, 237, 0.08);
  border-color: rgba(124, 58, 237, 0.16);
  color: #7c3aed;
}
.stat-messages .stat-icon {
  background: rgba(234, 88, 12, 0.1);
  border-color: rgba(234, 88, 12, 0.18);
  color: #ea580c;
}

.stat-icon svg {
  width: 12px;
  height: 12px;
}

.stat-card.accent {
  background: linear-gradient(145deg, #fff7ed 0%, #ffedd5 100%);
}

.stat-card small {
  color: #475569;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  line-height: 1;
}

.stat-card strong {
  font-size: clamp(18px, 1.8vw, 26px);
  line-height: 1.1;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #0f172a;
  margin: 2px 0 1px;
}

.stat-card span {
  font-size: 11px;
  color: #94a3b8;
  line-height: 1.3;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
  gap: 16px;
}

.services-grid {
  grid-template-columns: minmax(360px, 0.95fr) minmax(0, 1.05fr);
}

.overview-grid {
  grid-template-columns: 1fr;
}

.panel {
  padding: 22px;
  min-height: 360px;
}

.panel-wide {
  min-height: 360px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.14);
  margin-bottom: 14px;
}

.badge {
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid rgba(234, 88, 12, 0.22);
  background: rgba(255, 247, 237, 0.9);
  color: #7c2d12;
  font-size: 13px;
  font-weight: 700;
}

.period-switcher {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
  padding: 3px;
  border-radius: 999px;
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.14);
}

.period-chip {
  border: 0;
  border-radius: 999px;
  padding: 8px 12px;
  background: transparent;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.period-chip.active {
  background: #ffffff;
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
  color: #0f172a;
}

.metric-tile {
  display: grid;
  gap: 6px;
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.76);
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.metric-tile span {
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.income-chart-layout {
  display: grid;
  grid-template-columns: 84px minmax(0, 1fr) 220px;
  gap: 18px;
  align-items: stretch;
}

.chart-scale {
  display: grid;
  align-content: stretch;
  gap: 0;
}

.chart-scale span {
  display: flex;
  align-items: center;
  color: #94a3b8;
  font-size: 12px;
  font-weight: 700;
}

.chart-scale span:nth-child(1) {
  align-items: flex-start;
}

.chart-scale span:nth-child(2) {
  align-items: center;
}

.chart-scale span:nth-child(3) {
  align-items: flex-end;
}

.analytics-chart-layout {
  grid-template-columns: 84px minmax(0, 1fr) 240px;
}

.chart-insights {
  display: grid;
  gap: 12px;
}

.chart-insights article {
  display: grid;
  gap: 6px;
  padding: 16px 18px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.chart-insights small {
  color: #94a3b8;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.chart-insights strong {
  font-size: 24px;
  line-height: 1.1;
}

.chart-insights span {
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.chart-shell {
  position: relative;
  min-height: 248px;
  padding: 14px 16px 10px;
  border-radius: 22px;
  background: linear-gradient(
    180deg,
    rgba(255, 255, 255, 0.96) 0%,
    rgba(248, 250, 252, 0.92) 100%
  );
  border: 1px solid rgba(148, 163, 184, 0.12);
  overflow: visible;
}

.chart-plot {
  position: relative;
  height: 220px;
  border-radius: 18px;
  background: radial-gradient(
    circle at 30% 12%,
    rgba(234, 88, 12, 0.08),
    transparent 42%
  );
}

.chart-grid {
  position: absolute;
  inset: 10px 10px;
  display: grid;
  grid-template-rows: repeat(3, minmax(0, 1fr));
  pointer-events: none;
  z-index: 0;
  opacity: 0.75;
}

.chart-grid span {
  border-top: 1px dashed rgba(148, 163, 184, 0.22);
}

.chart-grid span:first-child {
  border-top: none;
}

.income-chart {
  width: 100%;
  height: 100%;
  overflow: hidden;
  position: absolute;
  inset: 0;
  z-index: 1;
  touch-action: none;
  cursor: pointer;
}

.income-area {
  fill: url(#income-area);
}

.income-area.alt {
  fill: url(#income-area-analytics);
}

.income-line {
  fill: none;
  stroke: url(#income-line-gradient);
  stroke-width: 2.2;
  stroke-linecap: round;
  stroke-linejoin: round;
  filter: drop-shadow(0 10px 14px rgba(234, 88, 12, 0.14));
}

.income-line-shadow {
  fill: none;
  stroke: rgba(194, 65, 12, 0.14);
  stroke-width: 3.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.income-average-line {
  stroke: rgba(148, 163, 184, 0.5);
  stroke-width: 0.65;
  stroke-dasharray: 2.5 2.5;
}

.income-dot {
  fill: rgba(255, 255, 255, 0.95);
  stroke: rgba(194, 65, 12, 0.8);
  stroke-width: 1;
  opacity: 0.7;
}

.income-dot-highlight {
  fill: rgba(249, 115, 22, 0.15);
  stroke: rgba(249, 115, 22, 0.2);
  stroke-width: 0.6;
}

.income-dot-core {
  fill: #f97316;
  stroke: #ffffff;
  stroke-width: 0.9;
}

.income-dot-highlight.dim,
.income-dot-core.dim {
  opacity: 0.35;
}

.income-hover-line {
  stroke: rgba(234, 88, 12, 0.22);
  stroke-width: 0.85;
  stroke-dasharray: 2 3;
}

.income-hover-halo {
  fill: rgba(249, 115, 22, 0.16);
  stroke: rgba(249, 115, 22, 0.24);
  stroke-width: 0.7;
}

.income-hover-dot {
  fill: #f97316;
  stroke: #ffffff;
  stroke-width: 1.1;
}

.chart-labels {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 1fr;
  gap: 8px;
  margin-top: 6px;
  position: relative;
  z-index: 2;
  user-select: none;
}

.chart-labels span {
  color: #64748b;
  font-size: 11px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chart-tooltip {
  position: absolute;
  transform: translate(-50%, -110%);
  padding: 10px 12px;
  border-radius: 14px;
  background: rgba(15, 23, 42, 0.88);
  color: #ffffff;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.24);
  border: 1px solid rgba(255, 255, 255, 0.12);
  pointer-events: none;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  min-width: 140px;
}

.chart-tooltip::after {
  content: "";
  position: absolute;
  left: 50%;
  bottom: -6px;
  transform: translateX(-50%);
  border: 6px solid transparent;
  border-top-color: rgba(15, 23, 42, 0.88);
}

.chart-tooltip strong {
  display: block;
  font-size: 13px;
  line-height: 1.1;
}

.chart-tooltip span {
  display: block;
  margin-top: 4px;
  font-size: 11px;
  opacity: 0.85;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
}

.income-chart-card {
  display: grid;
  gap: 18px;
  min-height: 240px;
  padding: 22px;
  border-radius: 24px;
  background: linear-gradient(
    180deg,
    rgba(255, 255, 255, 0.96) 0%,
    rgba(248, 250, 252, 0.96) 100%
  );
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.income-chart-summary {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.income-chart-summary-wide {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.income-chart-summary small {
  display: block;
  color: #94a3b8;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.income-chart-summary strong {
  font-size: clamp(22px, 2vw, 30px);
  line-height: 1.1;
}

.income-layout {
  display: grid;
  gap: 18px;
}

.income-stats-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.action-row,
.form-actions,
.row-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.service-form .form-actions {
  grid-column: 1 / -1;
  justify-content: flex-end;
  padding-top: 12px;
  margin-top: 2px;
  border-top: 1px solid rgba(148, 163, 184, 0.18);
}

.service-form {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  align-items: start;
}

.field {
  display: grid;
  gap: 6px;
}

.field span {
  color: #475569;
  font-size: 13px;
  font-weight: 700;
}

.field:focus-within span {
  color: var(--vd-accent-strong);
}

.field-hint {
  color: #64748b;
  font-size: 12px;
}

.field input,
.field select,
.field textarea {
  width: 100%;
  padding: 11px 12px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 14px;
  background: rgba(248, 250, 252, 0.9);
  color: #0f172a;
  font-size: 14px;
  transition:
    box-shadow 160ms ease,
    border-color 160ms ease,
    background 160ms ease;
}

.field select {
  cursor: pointer;
}

.field input:disabled,
.field select:disabled,
.field textarea:disabled {
  cursor: not-allowed;
  opacity: 0.75;
}

.primary-button:disabled,
.secondary-button:disabled {
  cursor: not-allowed;
  opacity: 0.75;
}

.field input::placeholder,
.field textarea::placeholder {
  color: rgba(71, 85, 105, 0.6);
}

.field input:focus-visible,
.field select:focus-visible,
.field textarea:focus-visible {
  outline: none;
  border-color: rgba(234, 88, 12, 0.35);
  background: #fff;
  box-shadow:
    0 0 0 4px rgba(234, 88, 12, 0.12),
    0 12px 34px rgba(15, 23, 42, 0.08);
}

.field input[type="file"] {
  padding: 10px 12px;
  background: #fff;
}

.field input[type="file"]::file-selector-button {
  margin-right: 12px;
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: #f8fafc;
  color: #0f172a;
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 700;
  cursor: pointer;
}

.field input[type="file"]::file-selector-button:hover {
  background: #fff7ed;
  border-color: rgba(234, 88, 12, 0.28);
}

.field textarea {
  min-height: 120px;
  resize: vertical;
}

.field-full {
  grid-column: 1 / -1;
}

.image-preview-card {
  display: grid;
  gap: 12px;
  justify-items: start;
  padding: 14px;
  border: 1px solid rgba(148, 163, 184, 0.16);
  border-radius: 18px;
  background: #f8fafc;
}

.image-preview {
  width: min(260px, 100%);
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: 16px;
  background: #e2e8f0;
}

.location-button {
  align-self: end;
  width: 100%;
  min-height: 44px;
}

.location-tools {
  gap: 10px;
  padding: 12px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.7);
  box-shadow: 0 18px 48px rgba(15, 23, 42, 0.06);
}

.location-notice,
.location-coords {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.location-notice {
  padding: 8px 10px;
  border-radius: 14px;
  border: 1px dashed rgba(148, 163, 184, 0.35);
  background: rgba(248, 250, 252, 0.9);
}

.location-coords {
  padding: 8px 10px;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: rgba(255, 247, 237, 0.9);
  color: #7c2d12;
  font-family:
    ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
    "Courier New", monospace;
  font-size: 13px;
}

.location-map-frame {
  width: 100%;
  min-height: 240px;
  aspect-ratio: 16 / 9;
  border: 0;
  border-radius: 16px;
  background: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
}

.location-map-link {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid rgba(234, 88, 12, 0.24);
  background: rgba(255, 247, 237, 0.9);
  color: #7c2d12;
  font-weight: 700;
  font-size: 13px;
  text-decoration: none;
}

.location-map-link:hover {
  background: #fff7ed;
  border-color: rgba(234, 88, 12, 0.35);
}

.service-list {
  display: grid;
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.service-item {
  display: grid;
  grid-template-columns: 104px minmax(0, 1fr) auto;
  gap: 14px;
  align-items: center;
  padding: 14px;
  border-radius: 18px;
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.service-image {
  width: 104px;
  height: 80px;
  border-radius: 16px;
  object-fit: cover;
  background: #e2e8f0;
}

.service-header {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.service-copy small,
.notice {
  color: #64748b;
}

.notice-success {
  color: #15803d;
}

.notice-error {
  color: #b91c1c;
}

.service-copy p {
  margin: 8px 0 0;
  color: #334155;
}

.service-state {
  padding: 6px 10px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #475569;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
}

.service-state.live {
  background: #dcfce7;
  color: #15803d;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 12px 10px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.16);
  text-align: left;
}

.table th {
  color: #64748b;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.status-chip {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
}

.status-chip.wait {
  background: #ffedd5;
  color: #c2410c;
}

.status-chip.ok {
  background: #dcfce7;
  color: #15803d;
}

.status-chip.bad {
  background: #fee2e2;
  color: #dc2626;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.38);
}

.modal-card {
  width: min(880px, 100%);
  max-height: 90vh;
  overflow: auto;
  padding: 22px;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
}

@media (max-width: 1180px) {
  .vendor-dashboard {
    grid-template-columns: 1fr;
    grid-template-rows: auto minmax(0, 1fr);
    height: 100vh;
    height: 100dvh;
    overflow: hidden;
  }

  .sidebar {
    min-height: auto;
    max-height: 58vh;
    overflow: auto;
    border-right: 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }

  .main-panel {
    overflow: auto;
    padding: 18px;
  }

  .stats-grid,
  .content-grid,
  .services-grid,
  .income-stats-grid,
  .income-chart-summary,
  .income-chart-summary-wide,
  .income-chart-layout,
  .analytics-chart-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .main-panel {
    padding: 18px;
  }

  .panel-head {
    flex-direction: column;
  }

  .vendor-head-actions {
    width: 100%;
    justify-items: start;
  }

  .signed-user {
    text-align: left;
  }

  .service-form,
  .service-item {
    grid-template-columns: 1fr;
  }

  .chart-labels {
    grid-auto-flow: row;
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .chart-labels span:empty {
    display: none;
  }

  .period-switcher {
    justify-content: flex-start;
  }
}
</style>
