<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import DashboardPage from "./pages/DashboardPage.vue";
import CustomizationPage from "./pages/CustomizationPage.vue";
import ServiceCard from "./customization/ServiceCard.vue";
import BookingsPage from "./pages/BookingsPage.vue";
import PublicNavbar from "./PublicNavbar.vue";
import { apiGet } from "../features/apiClient";
import { mapBooking as mapApiBooking } from "../features/bookingMappers";
import { useLanguage } from "../features/language";
import {
  buildPackageServiceDescriptions,
  eventTypeMap,
  eventTypeOptions,
  packageImageByEventType,
  fallbackVendorLocation,
  serviceFeeRate,
  vendorProfile,
} from "../features/appData";
import { useRouter } from "vue-router";

const props = defineProps({
  section: {
    type: String,
    default: "dashboard",
  },
});
const section = computed(() => props.section);
const FAVORITES_STORAGE_KEY = "achar_guest_favorites";
const CHECKOUT_FLOW_FLAG_KEY = "achar_checkout_flow_active";
const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const EVENTS_CACHE_KEY = "achar_guest_events_cache_v1";
const { language } = useLanguage();
const copyByLanguage = {
  en: {
    favorite: "Favorite",
    favoriteSub: "Your saved packages and services.",
    favoriteText: "Favorites are saved on this device. Sign in to sync across your account.",
    myBooking: "My Booking",
    bookingSub: "No booking data yet.",
    bookingText: "Sign in to view your booking history, upcoming events, and confirmations.",
    servicePackages: "Service Packages",
    packageSub: "Browse available packages by event type.",
    packageText: "Click any package card to see full details and included services.",
    overallService: "Overall Service",
    overallSub: "No overall service data yet.",
    overallText: "Sign in to select multiple services and packages for pre-booking.",
    customization: "Customization",
    customSub: "No customization data yet.",
    customText: "Sign in to customize your package and save your selected services.",
    dashboard: "Dashboard",
    dashboardSub: "No dashboard data yet.",
    dashboardText: "Sign in to view your dashboard, activity, and quick actions.",
    favoritePackages: "Favorite Packages",
    favoritePackagesEmpty: "No packages added yet.",
    favoriteServices: "Favorite Services",
    favoriteServicesEmpty: "No services added yet.",
    remove: "Remove",
    bookFavorites: "Book Favorites",
    bookFavoritesText: "Select package and quantity, include your favorite services, then pre-book with total calculation.",
    favoritePackageLabel: "Favorite Package",
    noPackage: "No package",
    quantity: "Quantity",
    packageSubtotal: "Package Subtotal",
    servicesSubtotal: "Services Subtotal",
    serviceFee: "Service Fee (3.5%)",
    totalPrice: "Total Price",
    prebookFavoriteItems: "Pre-book Favorite Items",
    favoriteBundle: "Favorite Services Bundle",
    noPackagesForEvent: "No packages available for this event.",
    signInLoadDashboard: "Sign in to load your dashboard data.",
	    packagePageTitle: "Service Packages",
	    packagePageText: "Browse available packages by event type. Search quickly and choose the best fit for your event.",
	    packageCatalog: "Package Catalog",
	    eventType: "Event type",
	    search: "Search",
	    searchPackages: "Search packages...",
    selected: "Selected",
    selectPackage: "Select Package",
    viewDetails: "View Details",
    bookingSummary: "Booking Summary",
    selectedPackage: "Selected Package",
    choosePackage: "Choose one package from the list.",
    packagePrice: "Package Price",
    selectedServices: "Selected Services",
    noAdditionalServices: "No additional services selected.",
    checkAvailability: "Check Availability",
    selectEventDate: "Select an Event Date",
    pickDateText: "Please pick your preferred date to see available time slots.",
    eventDate: "Event date",
    pickDate: "Pick a date",
    bookedOnSelectedDate: "Booked on selected date",
    available: "Available",
    booked: "Booked",
    availableTimeSlots: "Available Time Slots",
    selectedDate: "Selected Date",
    prebookNow: "Pre-book Now",
    noMatchingServicesFilter: "No matching services for this filter.",
    morning: "Morning",
    afternoon: "Afternoon",
    evening: "Evening",
    pickTimeSlot: "Pick a time slot",
    selectedSlotBooked: "Selected slot is booked",
    selectedSlotAvailable: "Selected slot is available",
    packageCount: "packages",
    packageCountSingle: "package",
  },
  km: {
    favorite: "ចូលចិត្ត",
    favoriteSub: "កញ្ចប់ និងសេវាកម្មដែលបានរក្សាទុក។",
    favoriteText: "ចំណូលចិត្តត្រូវបានរក្សាទុកលើឧបករណ៍នេះ។ សូមចូលប្រើ ដើម្បីសមកាលកម្មគណនី។",
    myBooking: "ការកក់របស់ខ្ញុំ",
    bookingSub: "មិនទាន់មានទិន្នន័យកក់ទេ។",
    bookingText: "សូមចូលប្រើ ដើម្បីមើលប្រវត្តិ និងការកក់ខាងមុខ។",
    servicePackages: "កញ្ចប់សេវាកម្ម",
    packageSub: "រកមើលកញ្ចប់តាមប្រភេទព្រឹត្តិការណ៍។",
    packageText: "ចុចកាតណាមួយ ដើម្បីមើលលម្អិត និងសេវាកម្មរួម។",
    overallService: "សេវាកម្មទូទៅ",
    overallSub: "មិនទាន់មានទិន្នន័យសេវាទូទៅ។",
    overallText: "សូមចូលប្រើ ដើម្បីជ្រើសសេវាកម្មជាច្រើនសម្រាប់ការកក់ជាមុន។",
    customization: "កែតម្រូវ",
    customSub: "មិនទាន់មានទិន្នន័យកែតម្រូវ។",
    customText: "សូមចូលប្រើ ដើម្បីកែតម្រូវកញ្ចប់ និងរក្សាទុកជម្រើស។",
    dashboard: "ផ្ទាំងគ្រប់គ្រង",
    dashboardSub: "មិនទាន់មានទិន្នន័យផ្ទាំងគ្រប់គ្រង។",
    dashboardText: "សូមចូលប្រើ ដើម្បីមើលសកម្មភាព និងមុខងាររហ័ស។",
    favoritePackages: "កញ្ចប់ដែលចូលចិត្ត",
    favoritePackagesEmpty: "មិនទាន់បានបន្ថែមកញ្ចប់ទេ។",
    favoriteServices: "សេវាកម្មដែលចូលចិត្ត",
    favoriteServicesEmpty: "មិនទាន់បានបន្ថែមសេវាកម្មទេ។",
    remove: "ដកចេញ",
    bookFavorites: "កក់របស់ដែលចូលចិត្ត",
    bookFavoritesText: "ជ្រើសរើសកញ្ចប់ និងចំនួន បញ្ចូលសេវាកម្មដែលចូលចិត្ត រួចកក់ជាមុនជាមួយការគណនាតម្លៃសរុប។",
    favoritePackageLabel: "កញ្ចប់ដែលចូលចិត្ត",
    noPackage: "គ្មានកញ្ចប់",
    quantity: "ចំនួន",
    packageSubtotal: "សរុបកញ្ចប់",
    servicesSubtotal: "សរុបសេវាកម្ម",
    serviceFee: "ថ្លៃសេវា (3.5%)",
    totalPrice: "តម្លៃសរុប",
    prebookFavoriteItems: "កក់ជាមុនរបស់ដែលចូលចិត្ត",
    favoriteBundle: "កញ្ចប់សេវាកម្មដែលចូលចិត្ត",
    noPackagesForEvent: "មិនមានកញ្ចប់សម្រាប់ព្រឹត្តិការណ៍នេះទេ។",
    signInLoadDashboard: "សូមចូលគណនីដើម្បីផ្ទុកទិន្នន័យផ្ទាំងគ្រប់គ្រងរបស់អ្នក។",
	    packagePageTitle: "កញ្ចប់សេវាកម្ម",
	    packagePageText: "រកមើលកញ្ចប់ដែលមានតាមប្រភេទព្រឹត្តិការណ៍។ ស្វែងរកយ៉ាងរហ័ស ហើយជ្រើសរើសអ្វីដែលសមបំផុតសម្រាប់ព្រឹត្តិការណ៍របស់អ្នក។",
	    packageCatalog: "បញ្ជីកញ្ចប់",
	    eventType: "ប្រភេទព្រឹត្តិការណ៍",
	    search: "ស្វែងរក",
	    searchPackages: "ស្វែងរកកញ្ចប់...",
    selected: "បានជ្រើស",
    selectPackage: "ជ្រើសរើសកញ្ចប់",
    viewDetails: "មើលព័ត៌មានលម្អិត",
    bookingSummary: "សេចក្តីសង្ខេបការកក់",
    selectedPackage: "កញ្ចប់ដែលបានជ្រើស",
    choosePackage: "ជ្រើសរើសកញ្ចប់មួយពីបញ្ជី។",
    packagePrice: "តម្លៃកញ្ចប់",
    selectedServices: "សេវាកម្មដែលបានជ្រើស",
    noAdditionalServices: "មិនមានសេវាកម្មបន្ថែមដែលបានជ្រើសទេ។",
    checkAvailability: "ពិនិត្យមើលពេលទំនេរ",
    selectEventDate: "ជ្រើសរើសកាលបរិច្ឆេទព្រឹត្តិការណ៍",
    pickDateText: "សូមជ្រើសរើសកាលបរិច្ឆេទដែលអ្នកពេញចិត្ត ដើម្បីមើលម៉ោងទំនេរ។",
    eventDate: "កាលបរិច្ឆេទព្រឹត្តិការណ៍",
    pickDate: "ជ្រើសរើសកាលបរិច្ឆេទ",
    bookedOnSelectedDate: "បានកក់នៅកាលបរិច្ឆេទដែលបានជ្រើស",
    available: "ទំនេរ",
    booked: "បានកក់",
    availableTimeSlots: "ម៉ោងទំនេរ",
    selectedDate: "កាលបរិច្ឆេទដែលបានជ្រើស",
    prebookNow: "កក់ជាមុនឥឡូវ",
    noMatchingServicesFilter: "មិនមានសេវាកម្មដែលត្រូវនឹងតម្រងនេះទេ។",
    morning: "ព្រឹក",
    afternoon: "រសៀល",
    evening: "ល្ងាច",
    pickTimeSlot: "ជ្រើសរើសម៉ោង",
    selectedSlotBooked: "ម៉ោងដែលបានជ្រើសត្រូវបានកក់",
    selectedSlotAvailable: "ម៉ោងដែលបានជ្រើសអាចកក់បាន",
    packageCount: "កញ្ចប់",
    packageCountSingle: "កញ្ចប់",
  },
  zh: {
    favorite: "收藏",
    favoriteSub: "您保存的套餐与服务。",
    favoriteText: "收藏保存在当前设备。登录后可与账户同步。",
    myBooking: "我的预订",
    bookingSub: "暂无预订数据。",
    bookingText: "登录后可查看历史、即将到来的活动与确认信息。",
    servicePackages: "服务套餐",
    packageSub: "按活动类型浏览可用套餐。",
    packageText: "点击任意套餐卡片可查看详细内容与包含服务。",
    overallService: "综合服务",
    overallSub: "暂无综合服务数据。",
    overallText: "登录后可选择多个服务与套餐进行预订。",
    customization: "定制",
    customSub: "暂无定制数据。",
    customText: "登录后可定制套餐并保存所选服务。",
    dashboard: "控制台",
    dashboardSub: "暂无控制台数据。",
    dashboardText: "登录后可查看您的控制台、活动与快捷操作。",
    favoritePackages: "收藏套餐",
    favoritePackagesEmpty: "还没有添加套餐。",
    favoriteServices: "收藏服务",
    favoriteServicesEmpty: "还没有添加服务。",
    remove: "移除",
    bookFavorites: "预订收藏",
    bookFavoritesText: "选择套餐和数量，包含您收藏的服务，然后按总价进行预订。",
    favoritePackageLabel: "收藏套餐",
    noPackage: "无套餐",
    quantity: "数量",
    packageSubtotal: "套餐小计",
    servicesSubtotal: "服务小计",
    serviceFee: "服务费 (3.5%)",
    totalPrice: "总价",
    prebookFavoriteItems: "预订收藏项目",
    favoriteBundle: "收藏服务组合",
    noPackagesForEvent: "当前活动没有可用套餐。",
    signInLoadDashboard: "请登录以加载您的仪表盘数据。",
	    packagePageTitle: "服务套餐",
	    packagePageText: "按活动类型浏览可用套餐。快速搜索并选择最适合您活动的方案。",
	    packageCatalog: "套餐目录",
	    eventType: "活动类型",
	    search: "搜索",
	    searchPackages: "搜索套餐...",
    selected: "已选择",
    selectPackage: "选择套餐",
    viewDetails: "查看详情",
    bookingSummary: "预订摘要",
    selectedPackage: "已选套餐",
    choosePackage: "请从列表中选择一个套餐。",
    packagePrice: "套餐价格",
    selectedServices: "已选服务",
    noAdditionalServices: "未选择额外服务。",
    checkAvailability: "查看档期",
    selectEventDate: "选择活动日期",
    pickDateText: "请选择您偏好的日期以查看可用时间段。",
    eventDate: "活动日期",
    pickDate: "选择日期",
    bookedOnSelectedDate: "所选日期已被预订",
    available: "可用",
    booked: "已预订",
    availableTimeSlots: "可用时间段",
    selectedDate: "已选日期",
    prebookNow: "立即预订",
    noMatchingServicesFilter: "没有符合此筛选条件的服务。",
    morning: "早晨",
    afternoon: "下午",
    evening: "晚上",
    pickTimeSlot: "选择时间段",
    selectedSlotBooked: "所选时间段已被预订",
    selectedSlotAvailable: "所选时间段可用",
    packageCount: "个套餐",
    packageCountSingle: "个套餐",
  },
};
const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);

// Demo seed for when API returns no events (keeps services list visible in preview)
const fallbackDemoEvents = [
  {
    id: 9001,
    title: "Signature Wedding Floral & Styling",
    description: "Full-venue floral design, aisle & backdrop styling tailored for intimate to mid-size weddings.",
    price: 1800,
    event_type: "wedding",
    location: "Phnom Penh, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.wedding,
    vendor: { name: vendorProfile.name },
  },
  {
    id: 9002,
    title: "Monk Ceremony Setup",
    description: "Respectful altar layout, seating, sound, and offering coordination with flexible timing.",
    price: 650,
    event_type: "monk_ceremony",
    location: "Kandal Province",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.monk_ceremony,
    vendor: { name: vendorProfile.name },
  },
  {
    id: 9003,
    title: "Corporate Gala Décor",
    description: "Stage styling, centerpieces, brand color palette execution, and guest flow support.",
    price: 2400,
    event_type: "company_party",
    location: "Phnom Penh, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.company_party,
    vendor: { name: vendorProfile.name },
  },
  {
    id: 9004,
    title: "Birthday Backdrop & Balloon Bar",
    description: "Photo-ready backdrop, themed props, and balloon bar for kids or adult celebrations.",
    price: 520,
    event_type: "birthday",
    location: "Siem Reap, Cambodia",
    starts_at: new Date().toISOString(),
    image_url: packageImageByEventType.birthday,
    vendor: { name: vendorProfile.name },
  },
];

const pageContent = computed(() => {
  if (props.section === "favorite") {
    return {
      title: uiText.value.favorite,
      subtitle: uiText.value.favoriteSub,
      text: uiText.value.favoriteText,
    };
  }

  if (props.section === "bookings") {
    return {
      title: uiText.value.myBooking,
      subtitle: uiText.value.bookingSub,
      text: uiText.value.bookingText,
    };
  }

  if (props.section === "services-packages") {
    return {
      title: uiText.value.servicePackages,
      subtitle: uiText.value.packageSub,
      text: uiText.value.packageText,
    };
  }

  if (props.section === "services-overall") {
    return {
      title: uiText.value.overallService,
      subtitle: uiText.value.overallSub,
      text: uiText.value.overallText,
    };
  }

  if (props.section === "customization") {
    return {
      title: uiText.value.customization,
      subtitle: uiText.value.customSub,
      text: uiText.value.customText,
    };
  }

  return {
    title: uiText.value.dashboard,
    subtitle: uiText.value.dashboardSub,
    text: uiText.value.dashboardText,
  };
});

const bookingFilter = ref("Upcoming");
const bookingEventTypeFilter = ref("all");
const guestBookings = ref([]);
const isLoadingGuestBookings = ref(false);
const bookingPageNotice = ref("");
const selectedBookingDetails = ref(null);
const customizationEventType = ref("all");
const customizationSearch = ref("");
const selectedCustomizationPackageId = ref(null);
const customizationQuantity = ref(1);

// state for packages page selection
const selectedPackageId = ref(null);
const packageQuantity = ref(1);
const selectedServiceIds = ref([]);
const expandedServiceId = ref(null);
const showVendorProfile = ref(false);
const activeVendorProfile = ref(null);
const packageEventType = ref("all");
const packageSearch = ref("");
const overallQuantity = ref(1);

const lastQty = ref(1);

const bookingBindings = { bookingFilter, bookingEventTypeFilter };
const customizationBindings = {
  customizationEventType,
  customizationSearch,
  selectedCustomizationPackageId,
  customizationQuantity,
};
const router = useRouter();
const route = useRoute();

// compute event filter key from query string
const selectedEventFilter = computed(() => {
  const val = route.query.event;
  return typeof val === "string" ? val : "";
});
const selectedSearchQuery = computed(() => {
  const val = route.query.q;
  return typeof val === "string" ? val : "";
});
const selectedPackageQuery = computed(() => {
  const val = route.query.package;
  return typeof val === "string" ? val.trim() : "";
});
const selectedServiceQuery = computed(() => {
  const val = route.query.service;
  return typeof val === "string" ? val.trim() : "";
});
const isFromCheckout = ref(
  route.query.from === "checkout" ||
    sessionStorage.getItem(CHECKOUT_FLOW_FLAG_KEY) === "1",
);
const focusedCardKey = ref("");
let focusedCardTimer = null;

watch(
  () => route.query.from,
  (value) => {
    if (value === "checkout") {
      isFromCheckout.value = true;
      sessionStorage.setItem(CHECKOUT_FLOW_FLAG_KEY, "1");
      return;
    }
    if (sessionStorage.getItem(CHECKOUT_FLOW_FLAG_KEY) === "1") {
      isFromCheckout.value = true;
    }
  },
  { immediate: true },
);
const selectedPrebookEventId = computed(() => {
  const raw = Array.isArray(route.query.prebookEventId)
    ? route.query.prebookEventId[0]
    : route.query.prebookEventId;
  const parsed = Number(raw);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
});

watch(
  selectedEventFilter,
  (value) => {
    if (value && packageEventType.value === "all") {
      packageEventType.value = value;
    }
  },
  { immediate: true },
);

const guestPreviewPackagesFiltered = computed(() => {
  const filter = packageEventType.value;
  const query = packageSearch.value.trim().toLowerCase();
  let rows = guestPreviewPackages.value;
  if (filter && filter !== "all") {
    rows = rows.filter((pkg) => pkg.eventType === filter);
  }
  if (query) {
    rows = rows.filter(
      (pkg) =>
        pkg.title.toLowerCase().includes(query) ||
        pkg.description.toLowerCase().includes(query),
    );
  }
  return rows;
});

const filteredGuestBookings = computed(() => {
  const activeType = bookingFilter.value;
  const activeEventType = bookingEventTypeFilter.value;

  return guestBookings.value.filter((item) => {
    const matchesType = !activeType || item.type === activeType;
    const matchesEventType = activeEventType === "all" || item.eventType === activeEventType;
    return matchesType && matchesEventType;
  });
});

const emptyDashboardStats = {
  totalBookings: 0,
  upcomingBookings: 0,
  completedBookings: 0,
  unreadMessages: 0,
};
const savedFavorites = (() => {
  try {
    const raw = localStorage.getItem(FAVORITES_STORAGE_KEY);
    if (!raw) return { packageIds: [], serviceIds: [], packageCache: {}, serviceCache: {} };
    const parsed = JSON.parse(raw);
    return {
      packageIds: Array.isArray(parsed.packageIds) ? parsed.packageIds : [],
      serviceIds: Array.isArray(parsed.serviceIds) ? parsed.serviceIds : [],
      packageCache: parsed.packageCache && typeof parsed.packageCache === "object" ? parsed.packageCache : {},
      serviceCache: parsed.serviceCache && typeof parsed.serviceCache === "object" ? parsed.serviceCache : {},
    };
  } catch {
    return { packageIds: [], serviceIds: [], packageCache: {}, serviceCache: {} };
  }
})();
const favoritePackageIds = ref(savedFavorites.packageIds);
const favoriteServiceIds = ref(savedFavorites.serviceIds);
const favoritePackageCache = ref(savedFavorites.packageCache);
const favoriteServiceCache = ref(savedFavorites.serviceCache);
const liveVendorEvents = ref([]);

function formatEventDateLabel(value) {
  if (!value) return "Date TBD";
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return "Date TBD";
  return parsed.toLocaleDateString("en-US", {
    month: "short",
    day: "2-digit",
    year: "numeric",
  });
}

function mapEventToGuestPackage(item) {
  const eventType = String(item.event_type || "other");
  const price = Number(item.price || 0);
  const vendorName = String(item.vendor?.name || "Verified Vendor");
  const vendorImage = item.vendor?.image_url || item.vendor?.profile_image_url || null;
  return {
    id: `live-package-${item.id}`,
    title: String(item.title || "Service Booking"),
    eventType,
    eventTypeLabel: eventTypeMap[eventType] || "Other",
    description: String(item.description || "").trim() || "Professional vendor service ready for booking.",
    location: item.location || "Location TBD",
    date: formatEventDateLabel(item.starts_at),
    price,
    priceLabel: `From $${price.toLocaleString()}`,
    image: item.image_url || packageImageByEventType[eventType] || packageImageByEventType.other,
    services: buildPackageServiceDescriptions(eventType, String(item.title || "Service Booking")),
    isPreview: false,
    backingEventId: Number(item.id || 0) || null,
    vendorName,
    vendorImage,
    vendor: item.vendor || null,
  };
}

function mapEventToGuestService(item) {
  const eventType = String(item.event_type || "other");
  const vendorImage = item.vendor?.image_url || item.vendor?.profile_image_url || null;
  return {
    id: `live-service-${item.id}`,
    name: String(item.title || "Service Booking"),
    description: String(item.description || "").trim() || "Professional vendor service ready for booking.",
    price: Number(item.price || 0),
    eventType,
    eventTypes: [eventType],
    backingEventId: Number(item.id || 0) || null,
    vendorName: String(item.vendor?.name || "Verified Vendor"),
    location: item.location || "Location TBD",
    image: item.image_url || packageImageByEventType[eventType] || packageImageByEventType.other,
    vendorImage,
    vendor: item.vendor || null,
  };
}

async function loadLiveVendorEvents() {
  // Try session cache first to avoid repeated network calls
  try {
    const cached = sessionStorage.getItem(EVENTS_CACHE_KEY);
    if (cached) {
      const parsed = JSON.parse(cached);
      if (Array.isArray(parsed) && parsed.length) {
        liveVendorEvents.value = parsed;
        return;
      }
    }
  } catch {
    // ignore cache read errors
  }

  try {
    const result = await apiGet("events", { per_page: 100, include_inactive: 1 });
    const rows = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    if (rows.length) {
      liveVendorEvents.value = rows;
      sessionStorage.setItem(EVENTS_CACHE_KEY, JSON.stringify(rows));
      return;
    }

    const fallbackResponse = await fetch("/api/events?per_page=100&include_inactive=1", {
      headers: {
        Accept: "application/json",
      },
    });
    if (!fallbackResponse.ok) {
      throw new Error(`Guest preview events request failed (${fallbackResponse.status})`);
    }

    const fallbackJson = await fallbackResponse.json().catch(() => ({}));
    liveVendorEvents.value = Array.isArray(fallbackJson?.data)
      ? fallbackJson.data
      : Array.isArray(fallbackJson)
        ? fallbackJson
        : [];
    if (liveVendorEvents.value.length) {
      sessionStorage.setItem(EVENTS_CACHE_KEY, JSON.stringify(liveVendorEvents.value));
    }
  } catch (error) {
    console.error(error);
    liveVendorEvents.value = [];
  }

  // If nothing came back (common in guest preview), seed demo events so services show up
  if (!liveVendorEvents.value.length) {
    liveVendorEvents.value = fallbackDemoEvents;
  }
}

async function loadGuestBookings() {
  const authRaw = localStorage.getItem(AUTH_USER_STORAGE_KEY);
  let authUser = null;

  if (authRaw) {
    try {
      authUser = JSON.parse(authRaw);
    } catch {
      authUser = null;
    }
  }

  if (!authUser) {
    guestBookings.value = [];
    bookingPageNotice.value = uiText.value.bookingText;
    return;
  }

  const customerEmail = String(authUser?.email || "").trim().toLowerCase();
  if (!customerEmail) {
    guestBookings.value = [];
    bookingPageNotice.value = uiText.value.bookingText;
    return;
  }

  isLoadingGuestBookings.value = true;
  try {
    const result = await apiGet("bookings", { customer_email: customerEmail });
    const rows = Array.isArray(result?.data) ? result.data : [];
    guestBookings.value = rows.map((row) => {
      const mapped = mapApiBooking(row, { vendorName: vendorProfile.name, eventTypeMap });
      return {
        ...mapped,
        primaryBtn: "View Details",
        secondaryBtn: mapped.statusClass === "pending" ? "Message Vendor" : "Reschedule",
        rawBooking: row,
      };
    });
    bookingPageNotice.value = "";
  } catch (error) {
    guestBookings.value = [];
    bookingPageNotice.value = error?.message || "Could not load your bookings.";
  } finally {
    isLoadingGuestBookings.value = false;
  }
}

function openBookingDetails(item) {
  selectedBookingDetails.value = item;
}

function closeBookingDetails() {
  selectedBookingDetails.value = null;
}

function bookingPrimaryAction(item) {
  openBookingDetails(item);
}

function bookingSecondaryAction(item) {
  if (item?.secondaryBtn === "Message Vendor") {
    goToSignIn();
    return;
  }

  bookingPageNotice.value = "Reschedule will be available soon.";
}

const guestPreviewPackages = computed(() => {
  return liveVendorEvents.value.map(mapEventToGuestPackage);
});

const servicesCatalog = computed(() => {
  return liveVendorEvents.value.map(mapEventToGuestService);
});

const selectedPackage = computed(
  () =>
    guestPreviewPackages.value.find((item) => item.id === selectedPackageId.value) ||
    null,
);

const selectedServices = computed(() =>
  servicesCatalog.value.filter((service) => selectedServiceIds.value.includes(service.id)),
);
const primarySelectedService = computed(() => selectedServices.value[0] || null);

const packagePrice = computed(() => {
  if (!selectedPackage.value) return 0;
  return Number(selectedPackage.value.price || 0) * Number(packageQuantity.value || 1);
});

const servicesSubtotal = computed(() => {
  const perUnit = selectedServices.value.reduce((sum, service) => sum + Number(service.price || 0), 0);
  return perUnit * Number(packageQuantity.value || 1);
});

const serviceFeeAmount = computed(() =>
  Number(((packagePrice.value + servicesSubtotal.value) * serviceFeeRate).toFixed(2)),
);

const totalPrice = computed(() => packagePrice.value + servicesSubtotal.value + serviceFeeAmount.value);

function formatPrebookMoney(value) {
  const num = Number(value || 0);
  if (!Number.isFinite(num)) return "$0";
  const abs = Math.abs(num);
  const hasCents = Math.round(abs * 100) % 100 !== 0;
  return `$${num.toLocaleString(undefined, {
    minimumFractionDigits: hasCents ? 2 : 0,
    maximumFractionDigits: 2,
  })}`;
}

const prebookSummaryQuantity = computed(() => {
  const usingOverallFlow = section.value === "services-overall";
  const raw = usingOverallFlow ? Number(overallQuantity.value || 1) : Number(packageQuantity.value || 1);
  return Number.isFinite(raw) && raw >= 1 ? raw : 1;
});

const prebookSummaryPackage = computed(() => {
  const byTarget = guestPreviewPackages.value.find((item) => item.id === prebookTargetPackageId.value) || null;
  return byTarget || selectedPackage.value || null;
});

const prebookSummaryPackageSubtotal = computed(() => {
  const pkg = prebookSummaryPackage.value;
  if (!pkg) return 0;
  return Number(pkg.price || 0) * prebookSummaryQuantity.value;
});

const prebookSummaryServicesSubtotal = computed(() => {
  const perUnit = selectedServices.value.reduce((sum, service) => sum + Number(service.price || 0), 0);
  return perUnit * prebookSummaryQuantity.value;
});

const prebookSummaryServicesLabel = computed(() => {
  const count = selectedServices.value.length;
  if (count === 1) return String(selectedServices.value[0]?.name || "Service");
  if (count > 1) return `Services (${count})`;
  return "Selected services";
});

const prebookSummaryServiceFee = computed(() =>
  Number(((prebookSummaryPackageSubtotal.value + prebookSummaryServicesSubtotal.value) * serviceFeeRate).toFixed(2)),
);

const prebookSummaryTotal = computed(
  () => prebookSummaryPackageSubtotal.value + prebookSummaryServicesSubtotal.value + prebookSummaryServiceFee.value,
);

const activePackageId = ref(null);
const showPrebookModal = ref(false);
const prebookTargetTitle = ref("");
const prebookTargetPackageId = ref(null);
function createEmptyPrebookForm() {
  return {
    fullName: "",
    email: "",
    phone: "",
    location: "",
    latitude: null,
    longitude: null,
    eventDate: "",
    guests: 50,
    notes: "",
  };
}
const prebookForm = ref(createEmptyPrebookForm());
const prebookSuccess = ref("");
const isDetectingPrebookLocation = ref(false);
const isResolvingTypedPrebookLocation = ref(false);
const lastResolvedPrebookLocationQuery = ref("");
const prebookLocationNotice = ref("");
let typedLocationResolveTimer = null;
const prebookAvailability = ref(null);
const isCheckingPrebookAvailability = ref(false);
const showPrebookCalendar = ref(false);
const prebookCalendarCursor = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1));
const prebookCalendarAvailability = ref({});
const isLoadingPrebookCalendar = ref(false);
const lastHandledPrebookEventId = ref(null);
const activePackage = computed(
  () =>
    guestPreviewPackages.value.find(
      (item) => item.id === activePackageId.value,
    ) || null,
);

const guestPreviewPackagesForCustomization = computed(() => {
  const q = customizationSearch.value.trim().toLowerCase();
  const filter = customizationEventType.value;
  let pkgs = guestPreviewPackages.value;
  if (filter && filter !== "all") {
    pkgs = pkgs.filter((p) => p.eventType === filter);
  }
  if (q) {
    pkgs = pkgs.filter(
      (p) =>
        p.title.toLowerCase().includes(q) ||
        p.description.toLowerCase().includes(q),
    );
  }
  return pkgs;
});

const matchingServicesFiltered = computed(() => {
  const q =
    section.value === "services-packages"
      ? packageSearch.value.trim().toLowerCase()
      : customizationSearch.value.trim().toLowerCase();
  const filter = customizationEventType.value;
  return servicesCatalog.value.filter((s) => {
    const matchesType =
      !filter ||
      filter === "all" ||
      (Array.isArray(s.eventTypes) ? s.eventTypes.includes(filter) : false);
    const matchesQ =
      !q ||
      s.name.toLowerCase().includes(q) ||
      (s.description && s.description.toLowerCase().includes(q));
    return matchesType && matchesQ;
  });
});

function openPackageDetails(id) {
  activePackageId.value = id;
}

function closePackageDetails() {
  activePackageId.value = null;
}

function openPrebookForm() {
  const usingOverallFlow = section.value === "services-overall";
  const targetPackage = activePackage.value || selectedPackage.value;
  const targetService = usingOverallFlow ? primarySelectedService.value : null;

  if (!targetPackage && !targetService) return;

  prebookTargetPackageId.value = targetPackage?.id || null;
  prebookTargetTitle.value = targetPackage?.title || targetService?.name || "Selected Vendor";
  prebookForm.value = createEmptyPrebookForm();
  prebookSuccess.value = "";
  prebookLocationNotice.value = "";
  isResolvingTypedPrebookLocation.value = false;
  lastResolvedPrebookLocationQuery.value = "";
  prebookAvailability.value = null;
  showPrebookCalendar.value = false;
  closePackageDetails();
  showPrebookModal.value = true;
}

function closePrebookModal() {
  if (typedLocationResolveTimer) {
    clearTimeout(typedLocationResolveTimer);
    typedLocationResolveTimer = null;
  }
  showPrebookModal.value = false;
  prebookAvailability.value = null;
  showPrebookCalendar.value = false;
}

function hasValidPrebookCoordinates() {
  return Number.isFinite(Number(prebookForm.value.latitude)) && Number.isFinite(Number(prebookForm.value.longitude));
}

async function resolveTypedPrebookLocation() {
  const query = String(prebookForm.value.location || "").trim();
  if (!query) {
    prebookForm.value.latitude = null;
    prebookForm.value.longitude = null;
    lastResolvedPrebookLocationQuery.value = "";
    prebookLocationNotice.value = "Enter a location to pin it on the map.";
    return false;
  }

  const normalizedQuery = query.toLowerCase().replace(/\s+/g, " ");
  if (normalizedQuery === lastResolvedPrebookLocationQuery.value && hasValidPrebookCoordinates()) {
    return true;
  }

  isResolvingTypedPrebookLocation.value = true;
  prebookLocationNotice.value = "Finding your typed location...";
  prebookForm.value.latitude = null;
  prebookForm.value.longitude = null;

  try {
    const url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q=${encodeURIComponent(query)}`;
    const response = await fetch(url, {
      headers: {
        Accept: "application/json",
        "Accept-Language": "en",
      },
    });
    if (!response.ok) {
      throw new Error("geocode failed");
    }
    const rows = await response.json();
    const best = Array.isArray(rows) ? rows[0] : null;
    const lat = Number(best?.lat);
    const lng = Number(best?.lon);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
      prebookLocationNotice.value = "Location not found. Add city or full address.";
      return false;
    }

    prebookForm.value.latitude = Number(lat.toFixed(6));
    prebookForm.value.longitude = Number(lng.toFixed(6));
    if (String(best?.display_name || "").trim()) {
      prebookForm.value.location = String(best.display_name).trim();
    }
    lastResolvedPrebookLocationQuery.value = normalizedQuery;
    prebookLocationNotice.value = "Location captured from your address.";
    return true;
  } catch {
    prebookLocationNotice.value = "Could not match this location right now.";
    return false;
  } finally {
    isResolvingTypedPrebookLocation.value = false;
  }
}

function scheduleTypedPrebookLocationResolve() {
  if (typedLocationResolveTimer) {
    clearTimeout(typedLocationResolveTimer);
    typedLocationResolveTimer = null;
  }
  const query = String(prebookForm.value.location || "").trim();
  if (!query) {
    prebookForm.value.latitude = null;
    prebookForm.value.longitude = null;
    lastResolvedPrebookLocationQuery.value = "";
    prebookLocationNotice.value = "";
    return;
  }
  if (query.length < 4) {
    prebookForm.value.latitude = null;
    prebookForm.value.longitude = null;
    prebookLocationNotice.value = "";
    return;
  }
  typedLocationResolveTimer = setTimeout(() => {
    typedLocationResolveTimer = null;
    void resolveTypedPrebookLocation();
  }, 700);
}

async function submitPrebookForm() {
  if (activePrebookEventId.value && !canSubmitPrebook.value) {
    prebookSuccess.value = prebookAvailabilityLabel.value;
    return;
  }

  const resolved = await resolveTypedPrebookLocation();
  if (!resolved) return;

  const usingOverallFlow = section.value === "services-overall";
  const quantity = usingOverallFlow ? Number(overallQuantity.value || 1) : Number(packageQuantity.value || 1);
  const checkoutItems = [];
  const primaryService = usingOverallFlow ? primarySelectedService.value : null;
  const targetPackage =
    guestPreviewPackages.value.find((item) => item.id === prebookTargetPackageId.value) ||
    selectedPackage.value ||
    activePackage.value;

  if (!usingOverallFlow) {
    const pkg = targetPackage;
    if (pkg) {
      checkoutItems.push({
        type: "package",
        name: pkg.title,
        description: pkg.description || "",
        qty: quantity,
        unitPrice: Number(pkg.price || 0),
        totalPrice: Number(pkg.price || 0) * quantity,
      });
    }
  }

  selectedServices.value.forEach((svc) => {
    checkoutItems.push({
      type: "service",
      name: svc.name,
      description: svc.description || "",
      qty: quantity,
      unitPrice: Number(svc.price || 0),
      totalPrice: Number(svc.price || 0) * quantity,
    });
  });

  const payload = {
    vendorTitle: prebookTargetTitle.value || targetPackage?.title || primaryService?.name || "Selected Vendor",
    vendorName:
      selectedPackage.value?.vendorName ||
      activePackage.value?.vendorName ||
      primaryService?.vendorName ||
      "Selected Vendor",
    eventId:
      selectedPackage.value?.backingEventId ||
      activePackage.value?.backingEventId ||
      primaryService?.backingEventId ||
      null,
    image:
      selectedPackage.value?.image ||
      activePackage.value?.image ||
      primaryService?.image ||
      packageImageByEventType[
        selectedPackage.value?.eventType || activePackage.value?.eventType || primaryService?.eventType || "other"
      ] ||
      packageImageByEventType.other,
    fullName: prebookForm.value.fullName,
    email: prebookForm.value.email,
    phone: prebookForm.value.phone,
    location: prebookForm.value.location,
    latitude: prebookForm.value.latitude,
    longitude: prebookForm.value.longitude,
    eventDate: prebookForm.value.eventDate,
    guests: Number(prebookForm.value.guests || 1),
    notes: prebookForm.value.notes,
    requestedEventType: usingOverallFlow
      ? customizationEventType.value
      : targetPackage?.eventType || "other",
    items: checkoutItems,
  };
  sessionStorage.setItem("achar_prebook_checkout", JSON.stringify(payload));
  showPrebookModal.value = false;
  router.push("/checkout");
}

const prebookLocationMapEmbedUrl = computed(() => {
  const lat = Number(prebookForm.value.latitude);
  const lng = Number(prebookForm.value.longitude);
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

const prebookLocationMapLinkUrl = computed(() => {
  const lat = Number(prebookForm.value.latitude);
  const lng = Number(prebookForm.value.longitude);
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return "";
  const safeLat = Number(lat.toFixed(6));
  const safeLng = Number(lng.toFixed(6));
  return `https://www.openstreetmap.org/?mlat=${safeLat}&mlon=${safeLng}#map=14/${safeLat}/${safeLng}`;
});

const prebookCalendarMonthLabel = computed(() =>
  prebookCalendarCursor.value.toLocaleDateString("en-US", { month: "long", year: "numeric" }),
);

const prebookCalendarSelectedLabel = computed(() => {
  if (!prebookForm.value.eventDate) return "No date selected";
  const date = new Date(prebookForm.value.eventDate);
  if (Number.isNaN(date.getTime())) return "No date selected";
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
});

const prebookCalendarCells = computed(() => {
  const year = prebookCalendarCursor.value.getFullYear();
  const month = prebookCalendarCursor.value.getMonth();
  const firstDay = new Date(year, month, 1);
  const firstWeekday = firstDay.getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  const cells = [];

  for (let index = 0; index < firstWeekday; index += 1) {
    cells.push({ id: `blank-${index}`, day: null, date: "", inMonth: false, disabled: true });
  }

  for (let day = 1; day <= daysInMonth; day += 1) {
    const date = new Date(year, month, day);
    const iso = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
    const dayAvailability = prebookCalendarAvailability.value[iso] || null;
    const isBooked = dayAvailability?.status === "booked";
    const isAvailable = dayAvailability?.status === "available";
    const isVendorBusy = Boolean(dayAvailability?.has_another_booked || dayAvailability?.has_existing_booking);
    cells.push({
      id: iso,
      day,
      date: iso,
      inMonth: true,
      booked: isBooked,
      available: isAvailable,
      vendorBusy: isVendorBusy,
      disabled: date < today,
    });
  }

  return cells;
});

const activePrebookEventId = computed(
  () =>
    selectedPackage.value?.backingEventId ||
    activePackage.value?.backingEventId ||
    primarySelectedService.value?.backingEventId ||
    null,
);

const prebookAvailabilityTone = computed(() => {
  if (!activePrebookEventId.value || !prebookForm.value.eventDate) return "neutral";
  if (isCheckingPrebookAvailability.value) return "neutral";
  if (!prebookAvailability.value) return "neutral";
  if (!prebookAvailability.value.is_active || !prebookAvailability.value.service_available) return "booked";
  if (!prebookAvailability.value.vendor_available) return "booked";
  return "available";
});

const prebookAvailabilityLabel = computed(() => {
  if (!activePrebookEventId.value) return "Select a live vendor service to check date availability.";
  if (!prebookForm.value.eventDate) return "Choose your event date to check if this vendor is free.";
  if (isCheckingPrebookAvailability.value) return "Checking selected date...";
  return prebookAvailability.value?.message || "Choose your event date to check if this vendor is free.";
});

const canSubmitPrebook = computed(() => {
  if (!activePrebookEventId.value) return true;
  if (isCheckingPrebookAvailability.value || !prebookForm.value.eventDate || !prebookAvailability.value) {
    return false;
  }

  return Boolean(prebookAvailability.value.service_available && prebookAvailability.value.vendor_available);
});

async function checkPrebookAvailability() {
  if (!activePrebookEventId.value || !prebookForm.value.eventDate) {
    prebookAvailability.value = null;
    return;
  }

  isCheckingPrebookAvailability.value = true;
  try {
    prebookAvailability.value = await apiGet(`events/${activePrebookEventId.value}/availability`, {
      requested_date: prebookForm.value.eventDate,
      quantity: Number(prebookForm.value.guests || 1),
    });
  } catch (error) {
    prebookAvailability.value = {
      service_available: false,
      vendor_available: false,
      is_active: true,
      message: error?.message || "Could not check availability for the selected date.",
    };
  } finally {
    isCheckingPrebookAvailability.value = false;
  }
}

async function loadPrebookCalendarAvailability() {
  if (!activePrebookEventId.value) {
    prebookCalendarAvailability.value = {};
    return;
  }

  isLoadingPrebookCalendar.value = true;
  try {
    const month = `${prebookCalendarCursor.value.getFullYear()}-${String(prebookCalendarCursor.value.getMonth() + 1).padStart(2, "0")}`;
    const result = await apiGet(`events/${activePrebookEventId.value}/availability-calendar`, { month });
    const days = Array.isArray(result.days) ? result.days : [];
    prebookCalendarAvailability.value = Object.fromEntries(
      days.map((item) => [
        String(item.date),
        {
          status: String(item.status || "available"),
          has_another_booked: Boolean(item.has_another_booked),
          has_existing_booking: Boolean(item.has_existing_booking),
        },
      ]),
    );
  } catch {
    prebookCalendarAvailability.value = {};
  } finally {
    isLoadingPrebookCalendar.value = false;
  }
}

function openPrebookCalendar() {
  const sourceDate = prebookForm.value.eventDate ? new Date(prebookForm.value.eventDate) : new Date();
  const safeDate = Number.isNaN(sourceDate.getTime()) ? new Date() : sourceDate;
  prebookCalendarCursor.value = new Date(safeDate.getFullYear(), safeDate.getMonth(), 1);
  showPrebookCalendar.value = true;
  void loadPrebookCalendarAvailability();
}

function closePrebookCalendar() {
  showPrebookCalendar.value = false;
}

function previousPrebookCalendarMonth() {
  prebookCalendarCursor.value = new Date(
    prebookCalendarCursor.value.getFullYear(),
    prebookCalendarCursor.value.getMonth() - 1,
    1,
  );
  void loadPrebookCalendarAvailability();
}

function nextPrebookCalendarMonth() {
  prebookCalendarCursor.value = new Date(
    prebookCalendarCursor.value.getFullYear(),
    prebookCalendarCursor.value.getMonth() + 1,
    1,
  );
  void loadPrebookCalendarAvailability();
}

function isPrebookCalendarCellSelected(cell) {
  return cell.inMonth && cell.date === prebookForm.value.eventDate;
}

function selectPrebookCalendarDate(cell) {
  if (!cell?.inMonth || cell.disabled || cell.booked) return;
  prebookForm.value.eventDate = cell.date;
  showPrebookCalendar.value = false;
}

function detectPrebookLocation() {
  if (!navigator.geolocation) {
    prebookLocationNotice.value = "Geolocation is not supported by this browser.";
    return;
  }

  isDetectingPrebookLocation.value = true;
  prebookLocationNotice.value = "";

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
          const streetNumber = address.house_number || address.house_name || address.building || "";
          const streetName = address.road || address.pedestrian || address.footway || "";
          const street = [streetNumber, streetName].filter(Boolean).join(" ").trim();
          const village = address.village || address.hamlet || address.neighbourhood || address.suburb || "";
          const district = address.city_district || address.district || address.borough || address.county || "";
          const city = address.city || address.town || address.municipality || address.state_district || "";
          const province = address.state || address.region || address.province || "";
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
          placeName = parts.length ? parts.join(", ") : String(primaryName || "").trim();
        }
      } catch {
        placeName = "";
      }
      prebookForm.value.latitude = lat;
      prebookForm.value.longitude = lng;
      prebookForm.value.location = placeName || `${lat}, ${lng}`;
      lastResolvedPrebookLocationQuery.value = String(prebookForm.value.location || "")
        .toLowerCase()
        .replace(/\s+/g, " ")
        .trim();
      prebookLocationNotice.value = "Current location captured.";
      isDetectingPrebookLocation.value = false;
    },
    () => {
      prebookLocationNotice.value = "Could not access your current location.";
      isDetectingPrebookLocation.value = false;
    },
    {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 300000,
    },
  );
}

function selectPackage(id) {
  selectedPackageId.value = id;
  packageQuantity.value = 1;
}

function toggleService(id) {
  const idx = selectedServiceIds.value.indexOf(id);
  if (idx === -1) {
    selectedServiceIds.value.push(id);
  } else {
    selectedServiceIds.value.splice(idx, 1);
  }
}

function toggleServiceDetails(id) {
  expandedServiceId.value = expandedServiceId.value === id ? null : id;
}

function openServiceVendor(service) {
  if (!service) return;
  const eventTypeLabel =
    eventTypeMap[service.eventType] || eventTypeMap[service.eventTypes?.[0]] || "Event";
  const vendor = service.vendor || {};

  activeVendorProfile.value = {
    name: vendor.name || service.vendorName || vendorProfile.name,
    subtitle: vendor.subtitle || vendor.tagline || vendorProfile.subtitle,
    rating: vendor.rating || vendorProfile.rating,
    serviceName: service.name,
    servicePriceLabel: `$${Number(service.price || 0).toLocaleString()}`,
    location: vendor.location || service.location || fallbackVendorLocation,
    eventTypeLabel,
    description: vendor.about || service.description || vendorProfile.about || vendorProfile.subtitle,
    image:
      vendor.cover_image_url ||
      vendor.hero_image_url ||
      vendor.profile_image_url ||
      vendor.image_url ||
      vendorProfile.heroImage ||
      vendorProfile.image ||
      service.vendorImage ||
      service.vendor?.image_url ||
      service.vendor?.profile_image_url ||
      packageImageByEventType[service.eventType] ||
      packageImageByEventType.other,
    phone: vendor.phone || vendorProfile.phone,
    email: vendor.email || vendorProfile.email,
    website: vendor.website || vendorProfile.website,
  };
  showVendorProfile.value = true;
}

function openPackageVendor() {
  if (!activePackage.value) return;
  const vendor = activePackage.value.vendor || {};
  activeVendorProfile.value = {
    name: vendor.name || activePackage.value.vendorName || vendorProfile.name,
    subtitle: vendor.subtitle || vendor.tagline || vendorProfile.subtitle,
    rating: vendor.rating || vendorProfile.rating,
    serviceName: activePackage.value.title,
    servicePriceLabel: activePackage.value.priceLabel,
    location: vendor.location || activePackage.value.location || fallbackVendorLocation,
    eventTypeLabel: activePackage.value.eventTypeLabel,
    description: vendor.about || activePackage.value.description || vendorProfile.about || vendorProfile.subtitle,
    image:
      vendor.cover_image_url ||
      vendor.hero_image_url ||
      vendor.profile_image_url ||
      vendor.image_url ||
      vendorProfile.heroImage ||
      vendorProfile.image ||
      activePackage.value.vendorImage ||
      activePackage.value.vendor?.image_url ||
      activePackage.value.vendor?.profile_image_url ||
      activePackage.value.image,
    phone: vendor.phone || vendorProfile.phone,
    email: vendor.email || vendorProfile.email,
    website: vendor.website || vendorProfile.website,
  };
  showVendorProfile.value = true;
}

function closeVendorProfile() {
  showVendorProfile.value = false;
  activeVendorProfile.value = null;
}

function clearFocusedCard() {
  if (focusedCardTimer) {
    clearTimeout(focusedCardTimer);
    focusedCardTimer = null;
  }
  focusedCardKey.value = "";
}

function markFocusedCard(key) {
  clearFocusedCard();
  focusedCardKey.value = key;
  focusedCardTimer = setTimeout(() => {
    focusedCardKey.value = "";
    focusedCardTimer = null;
  }, 1800);
}

function normalizeText(value) {
  return String(value || "")
    .toLowerCase()
    .replace(/\s+/g, " ")
    .trim();
}

function findPackageByTitle(title) {
  const target = normalizeText(title);
  if (!target) return null;
  return (
    guestPreviewPackages.value.find((pkg) => normalizeText(pkg.title) === target) ||
    guestPreviewPackages.value.find((pkg) => normalizeText(pkg.title).includes(target)) ||
    null
  );
}

function findServiceByName(name) {
  const target = normalizeText(name);
  if (!target) return null;
  return (
    servicesCatalog.value.find((service) => normalizeText(service.name) === target) ||
    servicesCatalog.value.find((service) => normalizeText(service.name).includes(target)) ||
    null
  );
}

async function focusPackageFromCheckout(targetName) {
  const pkg = findPackageByTitle(targetName);
  if (!pkg) return;

  packageEventType.value = pkg.eventType || "all";
  packageSearch.value = pkg.title;
  selectedPackageId.value = pkg.id;

  await nextTick();
  const cardId = `package-card-${pkg.id}`;
  const el = document.getElementById(cardId);
  if (!el) return;
  el.scrollIntoView({ behavior: "smooth", block: "center" });
  markFocusedCard(cardId);
}

async function focusServiceFromCheckout(targetName) {
  const svc = findServiceByName(targetName);
  if (!svc) return;

  const firstEventType = Array.isArray(svc.eventTypes) && svc.eventTypes.length
    ? svc.eventTypes[0]
    : "all";
  customizationEventType.value = firstEventType || "all";
  customizationSearch.value = svc.name;
  if (!selectedServiceIds.value.includes(svc.id)) {
    selectedServiceIds.value.push(svc.id);
  }
  expandedServiceId.value = svc.id;

  await nextTick();
  const cardId = `service-card-${svc.id}`;
  const el = document.getElementById(cardId);
  if (!el) return;
  el.scrollIntoView({ behavior: "smooth", block: "center" });
  markFocusedCard(cardId);
}

function packageQtyChanged(e) {
  const val = Number(e.target.value);
  if (Number.isFinite(val) && val >= 1) packageQuantity.value = val;
}

function overallQtyChanged(e) {
  const val = Number(e.target.value);
  if (Number.isFinite(val) && val >= 1) overallQuantity.value = val;
}

function persistFavorites() {
  localStorage.setItem(
    FAVORITES_STORAGE_KEY,
    JSON.stringify({
      packageIds: favoritePackageIds.value,
      serviceIds: favoriteServiceIds.value,
      packageCache: favoritePackageCache.value,
      serviceCache: favoriteServiceCache.value,
    }),
  );
  window.dispatchEvent(new Event("achar:favorites-updated"));
}

function syncFavoritesWithLiveCatalog() {
  const packages = guestPreviewPackages.value;
  const services = servicesCatalog.value;
  // Avoid clearing favorites before catalogs finish loading
  if (!packages.length && !services.length) return;

  const packageMap = new Map(packages.map((item) => [item.id, item]));
  const serviceMap = new Map(services.map((service) => [service.id, service]));

  // refresh caches with latest catalog data
  favoritePackageIds.value.forEach((id) => {
    if (packageMap.has(id)) {
      favoritePackageCache.value[id] = packageMap.get(id);
    }
  });
  favoriteServiceIds.value.forEach((id) => {
    if (serviceMap.has(id)) {
      favoriteServiceCache.value[id] = serviceMap.get(id);
    }
  });

  // prune cache entries no longer referenced
  Object.keys(favoritePackageCache.value).forEach((id) => {
    if (!favoritePackageIds.value.includes(id)) {
      delete favoritePackageCache.value[id];
    }
  });
  Object.keys(favoriteServiceCache.value).forEach((id) => {
    if (!favoriteServiceIds.value.includes(id)) {
      delete favoriteServiceCache.value[id];
    }
  });

  persistFavorites();
}

function toggleFavoritePackage(id) {
  if (favoritePackageIds.value.includes(id)) {
    favoritePackageIds.value = favoritePackageIds.value.filter(
      (item) => item !== id,
    );
    delete favoritePackageCache.value[id];
  } else {
    favoritePackageIds.value = [...favoritePackageIds.value, id];
    const match = guestPreviewPackages.value.find((item) => item.id === id);
    if (match) favoritePackageCache.value[id] = match;
  }
  persistFavorites();
}

function toggleFavoriteService(id) {
  if (favoriteServiceIds.value.includes(id)) {
    favoriteServiceIds.value = favoriteServiceIds.value.filter(
      (item) => item !== id,
    );
    delete favoriteServiceCache.value[id];
  } else {
    favoriteServiceIds.value = [...favoriteServiceIds.value, id];
    const match = servicesCatalog.value.find((service) => service.id === id);
    if (match) favoriteServiceCache.value[id] = match;
  }
  persistFavorites();
}

function isPackageFavorite(id) {
  return favoritePackageIds.value.includes(id);
}

function isServiceFavorite(id) {
  return favoriteServiceIds.value.includes(id);
}

const favoritePackages = computed(() =>
  favoritePackageIds.value
    .map((id) => guestPreviewPackages.value.find((item) => item.id === id) || favoritePackageCache.value[id])
    .filter(Boolean),
);

const favoriteServices = computed(() =>
  favoriteServiceIds.value
    .map((id) => servicesCatalog.value.find((service) => service.id === id) || favoriteServiceCache.value[id])
    .filter(Boolean),
);
const favoriteBookingPackageId = ref(null);
const favoriteBookingQuantity = ref(1);
const favoriteSelectedPackage = computed(() =>
  favoritePackages.value.find((item) => item.id === favoriteBookingPackageId.value) || null,
);
const favoriteSelectedServiceIds = computed(() =>
  favoriteServices.value.map((service) => service.id),
);
const favoritePackageSubtotal = computed(() =>
  Number(favoriteSelectedPackage.value?.price || 0) * Number(favoriteBookingQuantity.value || 1),
);
const favoriteServicesSubtotal = computed(() => {
  const perUnit = favoriteServices.value.reduce((sum, service) => sum + Number(service.price || 0), 0);
  return perUnit * Number(favoriteBookingQuantity.value || 1);
});
const favoriteServiceFee = computed(() =>
  Number(((favoritePackageSubtotal.value + favoriteServicesSubtotal.value) * serviceFeeRate).toFixed(2)),
);
const favoriteTotal = computed(() =>
  Number((favoritePackageSubtotal.value + favoriteServicesSubtotal.value + favoriteServiceFee.value).toFixed(2)),
);

watch([guestPreviewPackages, servicesCatalog], () => {
  syncFavoritesWithLiveCatalog();
}, { immediate: true });

watch(
  () => [prebookForm.value.eventDate, prebookForm.value.guests, activePrebookEventId.value],
  ([eventDate, guests, eventId]) => {
    prebookSuccess.value = "";
    if (!eventId || !eventDate || !Number.isFinite(Number(guests)) || Number(guests) < 1) {
      prebookAvailability.value = null;
      return;
    }

    void checkPrebookAvailability();
  },
);

watch(
  [activePrebookEventId, showPrebookCalendar],
  ([eventId, isCalendarOpen]) => {
    if (!isCalendarOpen) return;
    if (!eventId) {
      prebookCalendarAvailability.value = {};
      return;
    }
    void loadPrebookCalendarAvailability();
  },
  { immediate: true },
);

watch(
  favoritePackages,
  (rows) => {
    if (!rows.length) {
      favoriteBookingPackageId.value = null;
      return;
    }
    if (!favoriteBookingPackageId.value || !rows.some((item) => item.id === favoriteBookingPackageId.value)) {
      favoriteBookingPackageId.value = rows[0].id;
    }
  },
  { immediate: true },
);
watch(
  selectedSearchQuery,
  (value) => {
    if (!value) return;
    packageEventType.value = "all";
    customizationEventType.value = "all";
    packageSearch.value = value;
    customizationSearch.value = value;
  },
  { immediate: true },
);
watch(
  [() => section.value, () => route.query.from, selectedPackageQuery, guestPreviewPackages],
  async ([currentSection, from, targetPackage]) => {
    if (currentSection !== "services-packages" || from !== "checkout" || !targetPackage) return;
    await focusPackageFromCheckout(targetPackage);
  },
  { immediate: true },
);
watch(
  [() => section.value, () => route.query.from, selectedServiceQuery, matchingServicesFiltered],
  async ([currentSection, from, targetService]) => {
    if (currentSection !== "services-overall" || from !== "checkout" || !targetService) return;
    await focusServiceFromCheckout(targetService);
  },
  { immediate: true },
);
watch(
  [selectedPrebookEventId, guestPreviewPackages, section],
  ([eventId, packages, currentSection]) => {
    if (currentSection !== "services-packages" || !eventId) return;
    if (lastHandledPrebookEventId.value === eventId) return;

    const targetPackage = packages.find((item) => Number(item.backingEventId || 0) === eventId);
    if (!targetPackage) return;

    selectedPackageId.value = targetPackage.id;
    packageQuantity.value = 1;
    prebookTargetTitle.value = targetPackage.title || "Selected Vendor";
    prebookForm.value = createEmptyPrebookForm();
    prebookSuccess.value = "";
    prebookLocationNotice.value = "";
    showPrebookModal.value = true;
    lastHandledPrebookEventId.value = eventId;

    const nextQuery = { ...route.query };
    delete nextQuery.prebookEventId;
    router.replace({ path: route.path, query: nextQuery }).catch(() => {});
  },
  { immediate: true },
);

watch(
  () => section.value,
  (currentSection) => {
    if (currentSection === "bookings") {
      void loadGuestBookings();
    }
  },
  { immediate: true },
);

onMounted(() => {
  void loadLiveVendorEvents();
  void loadGuestBookings();
});

onUnmounted(() => {
  if (typedLocationResolveTimer) {
    clearTimeout(typedLocationResolveTimer);
    typedLocationResolveTimer = null;
  }
});

function favoriteQtyChanged(e) {
  const val = Number(e.target.value);
  if (Number.isFinite(val) && val >= 1) favoriteBookingQuantity.value = val;
}

function openFavoritePrebookForm() {
  if (!favoriteSelectedPackage.value && favoriteServices.value.length === 0) return;
  selectedPackageId.value = favoriteSelectedPackage.value?.id || null;
  selectedServiceIds.value = [...favoriteSelectedServiceIds.value];
  packageQuantity.value = Number(favoriteBookingQuantity.value || 1);
  prebookTargetPackageId.value = favoriteSelectedPackage.value?.id || null;
  prebookTargetTitle.value = favoriteSelectedPackage.value?.title || uiText.value.favoriteBundle;
  prebookForm.value = createEmptyPrebookForm();
  prebookSuccess.value = "";
  prebookLocationNotice.value = "";
  isResolvingTypedPrebookLocation.value = false;
  lastResolvedPrebookLocationQuery.value = "";
  prebookAvailability.value = null;
  showPrebookModal.value = true;
}

const overallServicesSubtotal = computed(() => {
  const perUnit = selectedServices.value.reduce((sum, service) => sum + Number(service.price || 0), 0);
  return perUnit * Number(overallQuantity.value || 1);
});

const overallServiceFeeAmount = computed(() =>
  Number((overallServicesSubtotal.value * serviceFeeRate).toFixed(2)),
);

const overallTotalPrice = computed(() => overallServicesSubtotal.value + overallServiceFeeAmount.value);

function goToSignIn() {
  router.push("/legacy-app");
}

function goToVendor() {
  router.push("/vendor");
}

function goToSection(nextSection) {
  if (nextSection === "bookings") router.push("/booking");
  if (nextSection === "services-packages") router.push("/services/packages");
  if (nextSection === "services-overall") router.push("/services/overall");
  if (nextSection === "dashboard") router.push("/dashboard");
  if (nextSection === "customization") router.push("/customization");
  if (nextSection === "favorite") router.push("/favorite");
}

function noop() {}
</script>

<template>
  <div class="guest-page">
    <PublicNavbar />

    <main class="shell guest-content">
      <section
        v-if="section !== 'services-overall' && section !== 'services-packages'"
        class="guest-panel"
      >
        <h1>{{ pageContent.title }}</h1>
        <p class="guest-subtitle">{{ pageContent.subtitle }}</p>
        <p class="guest-text">{{ pageContent.text }}</p>
      </section>

      <DashboardPage
        v-if="section === 'dashboard'"
        :notice="uiText.signInLoadDashboard"
        :customer-name="''"
        :dashboard-stats="emptyDashboardStats"
        :recent-bookings="[]"
        :recent-conversations="[]"
        :go-to-vendor="() => goToSection('services-packages')"
        :go-to-bookings="() => goToSection('bookings')"
        :go-to-messages="goToSignIn"
        :go-to-package-customization="() => goToSection('customization')"
        :open-upcoming-bookings="() => goToSection('bookings')"
      />

	      <section
	        v-else-if="section === 'services-packages'"
	        class="overall-service-page packages-service-page"
	      >
	        <section class="overall-head card">
	          <div class="overall-head-main">
	            <div class="flow-head-row">
	              <h1>{{ uiText.packagePageTitle }}</h1>
	              <div v-if="isFromCheckout" class="checkout-flow-steps">
	                <RouterLink
	                  :to="section === 'services-overall' ? '/services/overall' : '/services/packages'"
                  class="step-link active"
                >
                  {{ uiText.servicesStep || '1 Services' }}
                </RouterLink>
                <RouterLink to="/checkout" class="step-link">
                  {{ uiText.reviewStep || '2 Review & Payment' }}
                </RouterLink>
              </div>
            </div>
	            <p>
	              {{ uiText.packagePageText }}
	            </p>
	            <div class="overall-toolbar">
	              <label class="filter-field">
	                <span>{{ uiText.eventType }}</span>
	                <select
	                  class="event-type-select"
                  :value="packageEventType"
                  @change="packageEventType = $event.target.value"
                >
                  <option
                    v-for="option in eventTypeOptions"
                    :key="`pkg-${option.value}`"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </select>
              </label>
              <label class="filter-field">
                <span>{{ uiText.search }}</span>
                <input
                  class="customization-search"
                  type="search"
                  :placeholder="uiText.searchPackages"
                  :value="packageSearch"
                  @input="packageSearch = $event.target.value"
                />
	              </label>
	              <div class="overall-count">
	                {{ guestPreviewPackagesFiltered.length }}
	                {{ guestPreviewPackagesFiltered.length === 1 ? uiText.packageCountSingle : uiText.packageCount }}
	              </div>
	            </div>
	          </div>
	        </section>

	        <section class="overall-layout">
	          <div class="overall-list">
	            <article class="customization-section">
	              <div class="customization-section-head">
	                <span>P</span>
	                <h2>{{ uiText.packageCatalog }}</h2>
	              </div>

	              <div
	                v-if="guestPreviewPackagesFiltered.length === 0"
	                class="card empty-state"
	              >
	                {{ uiText.noPackagesForEvent }}
	              </div>

	              <div v-else class="package-grid">
	                <article
	                  v-for="item in guestPreviewPackagesFiltered"
	                  :key="item.id"
	                  class="package-product-card"
	                  :id="`package-card-${item.id}`"
	                  :class="{ 'focused-target-card': focusedCardKey === `package-card-${item.id}` }"
	                  role="button"
	                  tabindex="0"
	                  @click="openPackageDetails(item.id)"
	                  @keyup.enter="openPackageDetails(item.id)"
	                >
	                  <div class="package-card-image-wrap">
	                    <img class="package-card-image" :src="item.image" :alt="item.title" />
	                    <span class="package-card-pill">{{ item.eventTypeLabel || 'Event' }}</span>
	                    <button
	                      type="button"
	                      class="package-fav-badge"
	                      :class="{ active: isPackageFavorite(item.id) }"
	                      @click.stop="toggleFavoritePackage(item.id)"
	                      :aria-label="isPackageFavorite(item.id) ? 'Remove from favorites' : 'Add to favorites'"
	                    >
	                      {{ isPackageFavorite(item.id) ? '♥' : '♡' }}
	                    </button>
	                  </div>

	                  <div class="package-product-body">
	                    <h3>{{ item.title }}</h3>
	                    <p class="package-vendor">{{ item.vendorName || 'Verified Vendor' }}</p>
	                    <div class="package-rating">
	                      <span class="star">★</span>
	                      <strong>{{ (item.rating || 4.7).toFixed ? (item.rating || 4.7).toFixed(1) : '4.7' }}</strong>
	                      <small>{{ uiText.reviewsLabel || '0 reviews' }}</small>
	                    </div>

	                    <div class="package-bottom">
	                      <div class="package-price-stack">
	                        <small>{{ uiText.startsFrom || 'Starts from' }}</small>
	                        <strong class="package-price">{{ item.priceLabel }}</strong>
	                      </div>
	                      <button
	                        type="button"
	                        class="choice-indicator package-book-btn"
	                        @click.stop="selectPackage(item.id)"
	                      >
	                        {{
	                          selectedPackageId === item.id
	                            ? uiText.selected
	                            : uiText.selectPackage
	                        }}
	                      </button>
	                    </div>
	                  </div>
	                </article>
	              </div>
	            </article>
	          </div>

	          <aside class="card customization-summary overall-summary package-summary">
	            <h2>{{ uiText.bookingSummary }}</h2>
	          <div class="summary-items">
	            <h3>{{ uiText.selectedPackage }}</h3>
	            <p v-if="!selectedPackage">{{ uiText.choosePackage }}</p>
	            <div v-else class="summary-package">
              <strong>{{ selectedPackage.title }}</strong>
              <p>
                {{ selectedPackage.eventTypeLabel }} |
                {{ selectedPackage.location }}
              </p>
            </div>
          </div>

          <div class="summary-row">
            <span>{{ uiText.quantity }}</span>
            <input
              type="number"
              min="1"
              max="20"
              :value="packageQuantity"
              @input="packageQtyChanged"
            />
          </div>
          <div class="summary-row">
            <span>{{ uiText.packagePrice }}</span>
            <strong>${{ packagePrice.toLocaleString() }}</strong>
          </div>
          <div class="summary-items">
            <h3>{{ uiText.selectedServices }}</h3>
            <p v-if="selectedServices.length === 0">
              {{ uiText.noAdditionalServices }}
            </p>
            <div v-else>
              <div
                v-for="svc in selectedServices"
                :key="svc.id"
                class="summary-row"
              >
                <span>{{ svc.name }}</span>
                <strong>+${{ Number(svc.price || 0).toLocaleString() }}</strong>
              </div>
            </div>
          </div>
          <div class="summary-row">
            <span>{{ uiText.servicesSubtotal }}</span>
            <strong>${{ servicesSubtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-row muted">
            <span>{{ uiText.serviceFee }}</span>
            <strong>${{ serviceFeeAmount.toLocaleString() }}</strong>
          </div>

          <div class="summary-total">
            <span>{{ uiText.totalPrice }}</span>
            <strong>${{ totalPrice.toLocaleString() }}</strong>
          </div>

          <button
            type="button"
            class="confirm-selection"
            :disabled="!selectedPackage && selectedServices.length === 0"
            @click="openPrebookForm"
          >
	            {{ uiText.prebookNow }}
	          </button>
	        </aside>
	        </section>
	      </section>

      <CustomizationPage
        v-else-if="section === 'customization'"
        :event-type-options="eventTypeOptions"
        :event-type-map="eventTypeMap"
        :service-fee-rate="serviceFeeRate"
        :vendor-profile="vendorProfile"
        :bindings="customizationBindings"
        :filtered-customization-packages="guestPreviewPackagesForCustomization"
        :selected-services-count="0"
        :customization-total="0"
        :selected-customization-package="null"
        :selected-matching-services="[]"
        :selected-services-subtotal="0"
        :customization-package-subtotal="0"
        :service-fee-amount="0"
        :booking-submitting-event-id="null"
        :effective-customization-event-type="'all'"
        :filtered-matching-services="matchingServicesFiltered"
        :is-package-expanded="() => false"
        :toggle-package-details="noop"
        :go-to-availability="goToSignIn"
        :go-to-messages="goToSignIn"
        :select-customization-package="noop"
        :is-service-selected="() => false"
        :is-service-expanded="() => false"
        :toggle-matching-service="noop"
        :toggle-service-details="noop"
        :confirm-customization="goToSignIn"
        :is-package-favorite="isPackageFavorite"
        :is-service-favorite="isServiceFavorite"
        :toggle-favorite-package="toggleFavoritePackage"
        :toggle-favorite-service="toggleFavoriteService"
      />

      <section
        v-else-if="section === 'services-overall'"
        class="overall-service-page"
      >
        <section class="overall-head card">
          <div class="overall-head-main">
            <div class="flow-head-row">
              <h1>General Services</h1>
              <div v-if="isFromCheckout" class="checkout-flow-steps">
                <RouterLink
                  :to="section === 'services-overall' ? '/services/overall' : '/services/packages'"
                  class="step-link active"
                >
                  1 Services
                </RouterLink>
                <RouterLink to="/checkout" class="step-link">
                  2 Review &amp; Payment
                </RouterLink>
              </div>
            </div>
            <p>
              Browse all available add-on services. Filter by event type, then
              save favorites before signing in.
            </p>
            <div class="overall-toolbar">
              <label class="filter-field">
                <span>Event type</span>
                <select
                  class="event-type-select"
                  :value="customizationEventType.value"
                  @change="customizationEventType.value = $event.target.value"
                >
                  <option
                    v-for="option in eventTypeOptions"
                    :key="option.value"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </select>
              </label>
              <label class="filter-field">
                <span>Search</span>
                <input
                  class="customization-search"
                  type="search"
                  placeholder="Search services..."
                  :value="customizationSearch.value"
                  @input="customizationSearch.value = $event.target.value"
                />
              </label>
                <div class="overall-count">
                {{ matchingServicesFiltered.length }} services
                
                </div>
              </div>
          </div>
        </section>

        <section class="overall-layout">
          <div class="overall-list">
            <article class="customization-section">
              <div class="customization-section-head">
                <span>S</span>
                <h2>Service Catalog</h2>
              </div>

              <div
                v-if="matchingServicesFiltered.length === 0"
                class="card empty-state"
              >
                {{ uiText.noMatchingServicesFilter }}
              </div>

              <div class="addon-grid">
                <div
                  v-for="service in matchingServicesFiltered"
                  :key="service.id"
                  :id="`service-card-${service.id}`"
                  class="service-card-anchor"
                  :class="{ 'focused-target-card': focusedCardKey === `service-card-${service.id}` }"
                >
                  <ServiceCard
                    :service="service"
                    :is-selected="selectedServiceIds.includes(service.id)"
                    :is-expanded="expandedServiceId === service.id"
                    :is-favorite="isServiceFavorite(service.id)"
                    :event-type-map="eventTypeMap"
                    :service-fee-rate="serviceFeeRate"
                    @toggle-service="toggleService(service.id)"
                    @toggle-details="toggleServiceDetails"
                    @view-vendor="openServiceVendor(service)"
                    @message="goToSignIn"
                    @toggle-favorite="toggleFavoriteService"
                  />
                </div>
              </div>
            </article>
          </div>

          <aside class="card customization-summary overall-summary">
            <h2>{{ uiText.bookingSummary }}</h2>
            <div class="summary-items">
              <h3>{{ uiText.selectedPackage }}</h3>
              <p>{{ uiText.choosePackage }}</p>
            </div>
            <div class="summary-row">
              <span>{{ uiText.quantity }}</span>
              <input
                type="number"
                min="1"
                max="20"
                :value="overallQuantity"
                @input="overallQtyChanged"
              />
            </div>
            <div class="summary-row">
              <span>{{ uiText.packagePrice }}</span>
              <strong>$0</strong>
            </div>
            <div class="summary-items">
              <h3>{{ uiText.selectedServices }}</h3>
              <p v-if="selectedServices.length === 0">
                {{ uiText.noAdditionalServices }}
              </p>
              <div v-else>
                <div
                  v-for="svc in selectedServices"
                  :key="svc.id"
                  class="summary-row"
                >
                  <span>{{ svc.name }}</span>
                  <strong>+${{ Number(svc.price || 0).toLocaleString() }}</strong>
                </div>
              </div>
            </div>
            <div class="summary-row">
              <span>{{ uiText.servicesSubtotal }}</span>
              <strong>${{ overallServicesSubtotal.toLocaleString() }}</strong>
            </div>
            <div class="summary-row muted">
              <span>{{ uiText.serviceFee }}</span>
              <strong>${{ overallServiceFeeAmount.toLocaleString() }}</strong>
            </div>

          <div class="summary-total">
            <span>{{ uiText.totalPrice }}</span>
            <strong>${{ overallTotalPrice.toLocaleString() }}</strong>
          </div>

          <button type="button" class="confirm-selection" @click="openPrebookForm">
            {{ uiText.prebookNow }}
          </button>
        </aside>
        </section>
      </section>

      <section
        v-else-if="section === 'favorite'"
        class="guest-panel guest-favorite-block"
      >
        <div class="favorite-layout">
          <article class="favorite-card">
            <h3>{{ uiText.favoritePackages }}</h3>
            <p v-if="favoritePackages.length === 0" class="guest-text">
              {{ uiText.favoritePackagesEmpty }}
            </p>
            <ul v-else class="favorite-list">
              <li v-for="item in favoritePackages" :key="item.id">
                <img class="favorite-thumb" :src="item.image" :alt="item.title" loading="lazy" />
                <div class="favorite-text">
                  <strong>{{ item.title }}</strong>
                  <small
                    >{{ item.eventTypeLabel }} | {{ item.priceLabel }}</small
                  >
                </div>
                <button
                  type="button"
                  class="favorite-remove"
                  @click="toggleFavoritePackage(item.id)"
                >
                  {{ uiText.remove }}
                </button>
              </li>
            </ul>
          </article>

          <article class="favorite-card">
            <h3>{{ uiText.favoriteServices }}</h3>
            <p v-if="favoriteServices.length === 0" class="guest-text">
              {{ uiText.favoriteServicesEmpty }}
            </p>
            <ul v-else class="favorite-list">
              <li v-for="service in favoriteServices" :key="service.id">
                <img class="favorite-thumb" :src="service.image" :alt="service.name" loading="lazy" />
                <div class="favorite-text">
                  <strong>{{ service.name }}</strong>
                  <small
                    >${{ Number(service.price || 0).toLocaleString() }}</small
                  >
                </div>
                <button
                  type="button"
                  class="favorite-remove"
                  @click="toggleFavoriteService(service.id)"
                >
                  {{ uiText.remove }}
                </button>
              </li>
            </ul>
          </article>
        </div>

        <article class="favorite-card favorite-booking-card">
          <h3>{{ uiText.bookFavorites }}</h3>
          <p class="guest-text">
            {{ uiText.bookFavoritesText }}
          </p>

          <div class="favorite-booking-grid">
            <label class="filter-field">
              <span>{{ uiText.favoritePackageLabel }}</span>
              <select
                class="event-type-select"
                :value="favoriteBookingPackageId || ''"
                @change="favoriteBookingPackageId = $event.target.value || null"
              >
                <option value="">{{ uiText.noPackage }}</option>
                <option
                  v-for="item in favoritePackages"
                  :key="`fav-pkg-${item.id}`"
                  :value="item.id"
                >
                  {{ item.title }} ({{ item.priceLabel }})
                </option>
              </select>
            </label>

            <label class="filter-field">
              <span>{{ uiText.quantity }}</span>
              <input
                type="number"
                min="1"
                max="20"
                :value="favoriteBookingQuantity"
                @input="favoriteQtyChanged"
              />
            </label>
          </div>

          <div class="summary-row">
            <span>{{ uiText.packageSubtotal }}</span>
            <strong>${{ favoritePackageSubtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-row">
            <span>{{ uiText.servicesSubtotal }}</span>
            <strong>${{ favoriteServicesSubtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-row muted">
            <span>{{ uiText.serviceFee }}</span>
            <strong>${{ favoriteServiceFee.toLocaleString() }}</strong>
          </div>

          <div class="summary-total">
            <span>{{ uiText.totalPrice }}</span>
            <strong>${{ favoriteTotal.toLocaleString() }}</strong>
          </div>

          <button
            type="button"
            class="confirm-selection"
            :disabled="!favoriteSelectedPackage && favoriteServices.length === 0"
            @click="openFavoritePrebookForm"
          >
            {{ uiText.prebookFavoriteItems }}
          </button>
        </article>
      </section>

      <BookingsPage
        v-else
        :bindings="bookingBindings"
        :event-type-options="eventTypeOptions"
        :notice="bookingPageNotice"
        :is-loading-bookings="isLoadingGuestBookings"
        :filtered-bookings="filteredGuestBookings"
        :go-to-dashboard="() => goToSection('dashboard')"
        :go-to-vendor="() => goToSection('services-packages')"
        :go-to-messages="goToSignIn"
        :go-to-profile="goToSignIn"
        :booking-secondary-action="bookingSecondaryAction"
        :booking-primary-action="bookingPrimaryAction"
      />
    </main>

    <div
      v-if="selectedBookingDetails"
      class="package-modal-overlay"
      @click="closeBookingDetails"
    >
      <div class="package-modal booking-details-modal" @click.stop>
        <div class="package-modal-head">
          <div>
            <p class="package-product-type">{{ selectedBookingDetails.status }}</p>
            <h3>{{ selectedBookingDetails.service }}</h3>
          </div>
          <button
            type="button"
            class="package-modal-close"
            @click="closeBookingDetails"
          >
            &times;
          </button>
        </div>
        <img
          class="package-modal-image"
          :src="selectedBookingDetails.image"
          :alt="selectedBookingDetails.service"
        />
        <div class="booking-detail-grid">
          <p><strong>Vendor</strong><span>{{ selectedBookingDetails.vendor }}</span></p>
          <p><strong>Date</strong><span>{{ selectedBookingDetails.date }}</span></p>
          <p><strong>Event Type</strong><span>{{ selectedBookingDetails.metaValue }}</span></p>
          <p><strong>Total</strong><span>{{ selectedBookingDetails.placeValue }}</span></p>
          <p><strong>Quantity</strong><span>{{ selectedBookingDetails.quantity }}</span></p>
          <p><strong>Service Price</strong><span>${{ Number(selectedBookingDetails.servicePrice || 0).toLocaleString() }}</span></p>
          <p><strong>Status</strong><span>{{ selectedBookingDetails.status }}</span></p>
          <p><strong>Booking ID</strong><span>#{{ selectedBookingDetails.id }}</span></p>
        </div>
        <div class="booking-items-panel">
          <h4>Booked Services</h4>
          <ul v-if="selectedBookingDetails.bookedItems?.length" class="booking-items-list">
            <li v-for="(item, index) in selectedBookingDetails.bookedItems" :key="`${selectedBookingDetails.id}-${index}`">
              <div>
                <strong>{{ item.name || selectedBookingDetails.service }}</strong>
                <small>{{ item.type || "service" }} | Qty {{ Number(item.qty || 1) }}</small>
                <small v-if="item.description" class="booking-item-description">{{ item.description }}</small>
              </div>
              <div class="booking-item-prices">
                <span>${{ Number(item.totalPrice || item.unitPrice || 0).toLocaleString() }}</span>
                <small v-if="item.unitPrice">Unit ${{ Number(item.unitPrice || 0).toLocaleString() }}</small>
              </div>
            </li>
          </ul>
          <p v-else class="booking-detail-empty">{{ selectedBookingDetails.service }}</p>
        </div>
        <p class="booking-detail-note">{{ selectedBookingDetails.note }}</p>
      </div>
    </div>

    <div
      v-if="activePackage"
      class="package-modal-overlay"
      @click="closePackageDetails"
    >
      <div class="package-modal" @click.stop>
        <div class="package-modal-head">
          <div>
            <p class="package-product-type">
              {{ activePackage.eventTypeLabel }}
            </p>
            <h3>{{ activePackage.title }}</h3>
          </div>
          <button
            type="button"
            class="package-modal-close"
            @click="closePackageDetails"
          >
            &times;
          </button>
        </div>
        <img
          class="package-modal-image"
          :src="activePackage.image"
          :alt="activePackage.title"
        />
        <p class="package-modal-desc">{{ activePackage.description }}</p>
        <div class="package-modal-meta">
          <p><strong>Price:</strong> {{ activePackage.priceLabel }}</p>
          <p><strong>Location:</strong> {{ activePackage.location }}</p>
          <p><strong>Date:</strong> {{ activePackage.date }}</p>
        </div>
        <div class="package-modal-services">
          <h4>Included Services</h4>
          <ul>
            <li
              v-for="service in activePackage.services"
              :key="`${activePackage.id}-${service.name}`"
            >
              <strong>{{ service.name }}:</strong> {{ service.detail }}
            </li>
          </ul>
        </div>
        <div class="package-modal-actions">
          <button
            type="button"
            class="modal-action-btn modal-action-secondary"
            @click="toggleFavoritePackage(activePackage.id)"
          >
            {{
              isPackageFavorite(activePackage.id)
                ? "Remove Favorite"
                : "Add to Favorite"
            }}
          </button>
          <button
            type="button"
            class="modal-action-btn modal-action-neutral"
            @click="openPackageVendor"
          >
            Vendor
          </button>
          <button
            type="button"
            class="modal-action-btn modal-action-primary"
            @click="openPrebookForm"
          >
            Pre-book Now
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showVendorProfile && activeVendorProfile"
      class="package-modal-overlay"
      @click="closeVendorProfile"
    >
      <div class="package-modal vendor-modal" @click.stop>
        <div class="package-modal-head">
          <div>
            <p class="package-product-type">
              {{ activeVendorProfile.eventTypeLabel }}
            </p>
            <h3>{{ activeVendorProfile.name }}</h3>
            <small class="vendor-subtitle">{{ activeVendorProfile.subtitle }}</small>
          </div>
          <button
            type="button"
            class="package-modal-close"
            @click="closeVendorProfile"
          >
            &times;
          </button>
        </div>
        <img
          class="package-modal-image vendor-modal-image"
          :src="activeVendorProfile.image"
          :alt="activeVendorProfile.name"
        />
        <div class="vendor-meta">
          <p><strong>Service</strong><span>{{ activeVendorProfile.serviceName }}</span></p>
          <p><strong>Price</strong><span>{{ activeVendorProfile.servicePriceLabel }}</span></p>
          <p><strong>Event</strong><span>{{ activeVendorProfile.eventTypeLabel }}</span></p>
          <p><strong>Rating</strong><span>{{ activeVendorProfile.rating }}</span></p>
          <p><strong>Location</strong><span>{{ activeVendorProfile.location }}</span></p>
          <p v-if="activeVendorProfile.phone"><strong>Phone</strong><span>{{ activeVendorProfile.phone }}</span></p>
          <p v-if="activeVendorProfile.email"><strong>Email</strong><span>{{ activeVendorProfile.email }}</span></p>
          <p v-if="activeVendorProfile.website">
            <strong>Website</strong>
            <span>{{ activeVendorProfile.website }}</span>
          </p>
        </div>
        <p class="package-modal-desc">{{ activeVendorProfile.description }}</p>
        <div class="package-modal-actions">
          <button type="button" class="modal-action-btn modal-action-neutral" @click="closeVendorProfile">
            Close
          </button>
          <button type="button" class="modal-action-btn modal-action-primary" @click="goToSignIn">
            {{ uiText.messageVendor || "Message Vendor" }}
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showPrebookModal"
      class="prebook-overlay"
      @click="closePrebookModal"
    >
      <div class="prebook-modal" @click.stop>
        <div class="prebook-head">
          <div class="prebook-head-main">
            <span class="prebook-head-badge" aria-hidden="true">
              <svg class="prebook-head-icon" viewBox="0 0 24 24" fill="none">
                <path
                  d="M7 8.5h10M7 12h10M7 15.5h6M6.5 4h11A2.5 2.5 0 0 1 20 6.5v11A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-11A2.5 2.5 0 0 1 6.5 4Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
              </svg>
            </span>
            <div class="prebook-head-copy">
              <h3>Book {{ prebookTargetTitle }}</h3>
              <p>Fill in the details below—then review everything at checkout.</p>
              <div class="prebook-steps" aria-hidden="true">
                <span class="prebook-step prebook-step-active">1 Details</span>
                <span class="prebook-step">2 Location</span>
                <span class="prebook-step">3 Checkout</span>
              </div>
            </div>
          </div>
          <button
            type="button"
            class="prebook-close"
            @click="closePrebookModal"
            aria-label="Close booking form"
          >
            <svg class="prebook-close-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M6.5 6.5l11 11M17.5 6.5l-11 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
          </button>
        </div>

        <form class="prebook-form" @submit.prevent="submitPrebookForm">
          <div class="prebook-layout">
            <div class="prebook-col prebook-col-main">
              <section class="prebook-section">
                <div class="prebook-section-head">
                  <h4>Contact</h4>
                  <p>So the vendor can reach you quickly.</p>
                </div>
                <div class="prebook-grid">
                  <label class="prebook-field">
                    <span>Full name</span>
                    <input v-model.trim="prebookForm.fullName" type="text" placeholder="Your name" required />
                  </label>

                  <label class="prebook-field">
                    <span>Email</span>
                    <input v-model.trim="prebookForm.email" type="email" placeholder="you@example.com" required />
                  </label>

                  <label class="prebook-field prebook-span-2">
                    <span>Phone number</span>
                    <input
                      v-model.trim="prebookForm.phone"
                      type="tel"
                      required
                      placeholder="+1 555 234 5678"
                    />
                  </label>
                </div>
              </section>

              <section class="prebook-section">
                <div class="prebook-section-head">
                  <h4>Event details</h4>
                  <p>Choose the date and add any important notes.</p>
                </div>
                <div class="prebook-grid">
                  <label class="prebook-field prebook-span-2">
                    <span>Event date</span>
                    <div class="prebook-date-picker">
                      <button type="button" class="prebook-date-trigger" @click="openPrebookCalendar">
                        <span>{{ prebookForm.eventDate || "Select event date" }}</span>
                        <span class="prebook-date-icon" aria-hidden="true">
                          <svg viewBox="0 0 24 24" fill="none">
                            <path
                              d="M7 3v3M17 3v3M4.5 9h15M6.5 6h11A2.5 2.5 0 0 1 20 8.5v10A2.5 2.5 0 0 1 17.5 21h-11A2.5 2.5 0 0 1 4 18.5v-10A2.5 2.5 0 0 1 6.5 6Z"
                              stroke="currentColor"
                              stroke-width="1.8"
                              stroke-linecap="round"
                            />
                          </svg>
                        </span>
                      </button>

                      <div v-if="showPrebookCalendar" class="prebook-calendar">
                        <div class="prebook-calendar-head">
                          <div>
                            <strong>{{ prebookCalendarMonthLabel }}</strong>
                            <small>{{ prebookCalendarSelectedLabel }}</small>
                          </div>
                          <div class="prebook-calendar-nav">
                            <button type="button" @click="previousPrebookCalendarMonth" aria-label="Previous month">
                              &lt;
                            </button>
                            <button type="button" @click="nextPrebookCalendarMonth" aria-label="Next month">
                              &gt;
                            </button>
                            <button
                              type="button"
                              class="prebook-calendar-close"
                              @click="closePrebookCalendar"
                              aria-label="Close calendar"
                            >
                              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path
                                  d="M6.5 6.5l11 11M17.5 6.5l-11 11"
                                  stroke="currentColor"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                />
                              </svg>
                            </button>
                          </div>
                        </div>

                        <div class="prebook-calendar-weekdays">
                          <span v-for="label in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="label">
                            {{ label }}
                          </span>
                        </div>

                        <div class="prebook-calendar-grid">
                          <button
                            v-for="cell in prebookCalendarCells"
                            :key="cell.id"
                            type="button"
                            class="prebook-calendar-day"
                            :class="{
                              muted: !cell.inMonth,
                              booked: cell.booked && cell.inMonth,
                              busy: cell.vendorBusy && cell.inMonth,
                              available: cell.available && cell.inMonth,
                              selected: isPrebookCalendarCellSelected(cell),
                            }"
                            :disabled="!cell.inMonth || cell.disabled || cell.booked"
                            @click="selectPrebookCalendarDate(cell)"
                          >
                            {{ cell.day || "" }}
                          </button>
                        </div>

                        <div class="prebook-calendar-legend">
                          <span><i class="dot available"></i> Available</span>
                          <span><i class="dot booked"></i> Already Booked</span>
                          <span><i class="dot selected"></i> Selected</span>
                        </div>
                        <p v-if="isLoadingPrebookCalendar" class="prebook-calendar-loading">Loading date availability...</p>
                      </div>
                    </div>
                  </label>

                  <div
                    class="prebook-availability-state prebook-span-2"
                    :class="{
                      available: prebookAvailabilityTone === 'available',
                      booked: prebookAvailabilityTone === 'booked',
                    }"
                  >
                    {{ prebookAvailabilityLabel }}
                  </div>

                  <label class="prebook-field">
                    <span>Guests</span>
                    <input v-model.number="prebookForm.guests" type="number" min="1" required />
                  </label>

                  <label class="prebook-field prebook-span-2">
                    <span>Notes</span>
                    <textarea
                      v-model.trim="prebookForm.notes"
                      rows="3"
                      placeholder="Add preferences or requests"
                    ></textarea>
                  </label>
                </div>
              </section>

              <p v-if="prebookSuccess" class="prebook-success">{{ prebookSuccess }}</p>
            </div>

            <div class="prebook-col prebook-col-aside">
              <div class="prebook-summary" aria-label="Booking summary">
                <div class="prebook-summary-top">
                  <div class="prebook-summary-title">{{ prebookTargetTitle }}</div>
                  <div class="prebook-summary-pill">Qty {{ prebookSummaryQuantity }}</div>
                </div>
                <div class="prebook-summary-lines">
                  <div v-if="prebookSummaryPackage" class="prebook-summary-line">
                    <span>Package</span>
                    <strong>{{ prebookSummaryPackage.title }}</strong>
                  </div>
                  <div v-if="prebookSummaryPackageSubtotal > 0" class="prebook-summary-line">
                    <span>Package subtotal</span>
                    <strong>{{ formatPrebookMoney(prebookSummaryPackageSubtotal) }}</strong>
                  </div>
                  <div v-if="prebookSummaryServicesSubtotal > 0" class="prebook-summary-line">
                    <span>{{ prebookSummaryServicesLabel }}</span>
                    <strong>{{ formatPrebookMoney(prebookSummaryServicesSubtotal) }}</strong>
                  </div>
                  <div v-if="prebookSummaryServiceFee > 0" class="prebook-summary-line prebook-summary-muted">
                    <span>Service fee ({{ Math.round(serviceFeeRate * 100) }}%)</span>
                    <strong>{{ formatPrebookMoney(prebookSummaryServiceFee) }}</strong>
                  </div>
                  <div class="prebook-summary-total">
                    <span>Estimated total</span>
                    <strong>{{ formatPrebookMoney(prebookSummaryTotal) }}</strong>
                  </div>
                </div>
              </div>

              <section class="prebook-section">
                <div class="prebook-section-head">
                  <h4>Location</h4>
                  <p>Pin your venue so the vendor can plan ahead.</p>
                </div>
                <div class="prebook-grid">
                  <label class="prebook-field prebook-span-2">
                    <span>Venue</span>
                    <input
                      v-model.trim="prebookForm.location"
                      type="text"
                      required
                      placeholder="City / Venue address"
                      @input="scheduleTypedPrebookLocationResolve"
                    />
                  </label>

                  <div class="prebook-location-tools prebook-span-2">
                    <div class="prebook-location-row">
                      <button
                        type="button"
                        class="modal-action-btn modal-action-neutral"
                        :disabled="isDetectingPrebookLocation || isResolvingTypedPrebookLocation"
                        @click="detectPrebookLocation"
                      >
                        {{ isDetectingPrebookLocation ? "Detecting location..." : "Use Current Location" }}
                      </button>

                      <a
                        v-if="prebookLocationMapLinkUrl"
                        class="prebook-map-link"
                        :href="prebookLocationMapLinkUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Open in map
                      </a>
                    </div>

                    <p v-if="prebookLocationNotice" class="prebook-location-notice">{{ prebookLocationNotice }}</p>
                    <p
                      v-if="prebookForm.latitude !== null && prebookForm.longitude !== null"
                      class="prebook-location-coords"
                    >
                      Lat: {{ Number(prebookForm.latitude).toFixed(6) }}, Lng: {{ Number(prebookForm.longitude).toFixed(6) }}
                    </p>
                    <iframe
                      v-if="prebookLocationMapEmbedUrl"
                      class="prebook-map-frame"
                      :src="prebookLocationMapEmbedUrl"
                      loading="lazy"
                      referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                    <div v-else class="prebook-map-placeholder">
                      Map preview appears after a location is found.
                    </div>
                  </div>
                </div>
              </section>
            </div>
          </div>

          <div class="prebook-actions">
            <p class="prebook-actions-hint">You’ll review details before placing your order.</p>
            <button
              type="submit"
              class="modal-action-btn modal-action-primary"
              :disabled="activePrebookEventId && !canSubmitPrebook"
            >
              {{ isCheckingPrebookAvailability ? "Checking..." : "Continue to Checkout" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.guest-page {
  min-height: 100vh;
}

.guest-content {
  padding-top: 14px;
  padding-bottom: 14px;
}

.overall-service-page {
  display: grid;
  gap: 16px;
  padding-bottom: 10px;
}

.overall-head {
  border: 1px solid #dbe4f2;
  border-radius: 24px;
  background:
    radial-gradient(circle at 95% 12%, rgba(255, 106, 0, 0.12), transparent 35%),
    linear-gradient(180deg, #ffffff, #fbfdff);
  padding: 22px 24px;
}

.overall-head-main h1 {
  margin: 0;
  font-size: clamp(2.2rem, 3vw, 3.2rem);
  line-height: 1.02;
}

.flow-head-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.overall-head-main p {
  margin: 12px 0 0;
  color: #64748b;
  max-width: 920px;
  font-size: 18px;
}

.overall-toolbar {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 12px;
  align-items: end;
}

.filter-field {
  display: grid;
  gap: 6px;
}

.filter-field span {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: #64748b;
}

.customization-summary {
  padding: 14px 16px;
  border-radius: 18px;
}

.customization-summary h2 {
  margin: 0 0 8px;
  font-size: 22px;
}

.summary-items {
  margin: 6px 0 8px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e8eef6;
}

.summary-items h3 {
  margin: 0;
  font-size: 12px;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: #7a869c;
}

.summary-items p {
  margin: 4px 0 0;
  color: #8d9bad;
  font-size: 13px;
}

.summary-package {
  margin-top: 4px;
}

.summary-package strong {
  font-size: 14px;
}

.summary-package p {
  margin: 2px 0 0;
  color: #6b7a8f;
  font-size: 13px;
}

.summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 0;
  border-top: 1px solid #f0f3f8;
}

.summary-row:first-of-type {
  border-top: none;
}

.summary-row span {
  font-weight: 700;
  color: #1f2937;
  font-size: 14px;
}

.summary-row strong {
  font-size: 15px;
  color: #0f172a;
}

.summary-row.muted span,
.summary-row.muted strong {
  color: #8a96a9;
}

.summary-row input {
  height: 36px;
  border-radius: 10px;
  padding: 6px 10px;
}

.summary-total {
  margin-top: 10px;
  padding: 10px 0 12px;
  border-top: 1px solid #e8edf5;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.summary-total span {
  font-size: 15px;
}

.summary-total strong {
  font-size: 20px;
  color: #e15c04;
}

.confirm-selection {
  margin-top: 10px;
  min-height: 44px;
  border-radius: 12px;
  font-size: 15px;
  padding: 11px;
}

.overall-count {
  border: 1px solid #d7e4f3;
  border-radius: 14px;
  background: #f8fbff;
  color: #1e293b;
  font-size: 14px;
  font-weight: 800;
  padding: 13px 12px;
  text-align: center;
}

.overall-list .customization-section {
  margin-top: 0;
}

.overall-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  align-items: start;
}

.overall-list {
  max-height: calc(100dvh - 80px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.overall-summary {
  position: sticky;
  top: 12px;
  max-height: calc(100dvh - 40px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  height: fit-content;
  border-radius: 24px;
}

.overall-list .customization-section {
  border-radius: 24px;
  border-color: #d8e3f2;
  padding: 16px;
}

.overall-list .customization-section-head {
  margin-bottom: 8px;
}

.overall-list .customization-section-head span {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: #fff2e8;
  color: #0f172a;
  font-size: 22px;
  font-weight: 800;
}

.overall-list .customization-section-head h2 {
  font-size: 26px;
}

.guest-panel {
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: #fff;
  padding: 18px 22px;
  margin-bottom: 14px;
}

.guest-panel h1 {
  margin: 0;
  font-size: 30px;
}

.guest-subtitle {
  margin: 10px 0 0;
  color: #6b7280;
  font-weight: 700;
}

.guest-text {
  margin: 8px 0 0;
  color: #6b7280;
}

.guest-favorite-block {
  margin-top: 0;
}

.package-catalog {
  margin-top: 0;
}

.package-head {
  border: 1px solid #dbe4f2;
  border-radius: 22px;
  background:
    radial-gradient(circle at 94% 14%, rgba(255, 106, 0, 0.1), transparent 35%),
    linear-gradient(180deg, #ffffff, #fbfdff);
  padding: 12px 14px 12px;
  margin-bottom: 8px;
}

.package-head-main h1 {
  margin: 0;
  font-size: clamp(1.6rem, 2.5vw, 2.4rem);
  line-height: 1.08;
}

.package-head-main p {
  margin: 6px 0 0;
  color: #64748b;
  max-width: 820px;
}

.package-toolbar {
  margin-top: 10px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
  align-items: end;
}

.checkout-flow-steps {
  margin-top: 2px;
  color: #7c8ca2;
  font-weight: 700;
  display: inline-flex;
  gap: 8px;
  font-size: 13px;
  background: #f8fbff;
  border: 1px solid #dbe4f1;
  border-radius: 999px;
  padding: 6px;
  width: fit-content;
}

.checkout-flow-steps .step-link {
  color: #7c8ca2;
  border-radius: 999px;
  padding: 5px 10px;
  font-weight: 800;
  line-height: 1;
}

.checkout-flow-steps .step-link.active {
  color: #fff;
  background: linear-gradient(120deg, #ff6a00, #fb923c);
  border-radius: 999px;
  box-shadow: 0 8px 16px rgba(249, 115, 22, 0.24);
}

.checkout-flow-steps .step-link:not(.active):hover {
  color: #475569;
  background: #eef4fb;
}

.checkout-flow-steps .step-link.active:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(249, 115, 22, 0.28);
}

.package-catalog-head {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  margin: 0 0 10px;
}

.package-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
}

.event-filter-note {
  margin: 0;
  font-size: 14px;
  color: #475569;
  font-weight: 700;
}

.package-count {
  border: 1px solid #d3deee;
  border-radius: 12px;
  background: #f8fbff;
  color: #1e293b;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.01em;
  text-transform: none;
  padding: 10px 12px;
  text-align: center;
}

.package-empty {
  border: 1px dashed #d2deef;
  border-radius: 12px;
  background: #f8fbff;
  padding: 16px;
  margin: 0;
}

.package-product-card {
  border: 1px solid #dbe4f1;
  border-radius: 16px;
  background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
  overflow: hidden;
  scroll-margin-top: 120px;
  box-shadow: 0 10px 14px rgba(15, 23, 42, 0.06);
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
  display: grid;
  grid-template-rows: auto 1fr;
  gap: 12px;
  padding: 0;
}

.package-product-card:hover {
  transform: translateY(-2px);
  border-color: #c6d5ea;
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
}

.package-product-card img {
  width: 100%;
  height: 268px;
  object-fit: cover;
}

.package-card-image-wrap {
  position: relative;
  width: 100%;
  height: 268px;
  overflow: hidden;
}

.package-card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.package-card-pill {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(17, 24, 39, 0.85);
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.01em;
  text-transform: capitalize;
  border: 1px solid rgba(255, 255, 255, 0.24);
}

.package-fav-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 34px;
  height: 34px;
  border-radius: 999px;
  border: 1px solid #f0d7c2;
  background: #fffaf5;
  color: #d4580a;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.package-fav-badge.active {
  background: #ffe9d7;
  border-color: #f2b88f;
}

.package-fav-badge:hover {
  transform: translateY(-1px);
}

.package-product-body {
  padding: 8px 14px 12px;
  display: grid;
  gap: 6px;
}

.package-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.package-head h3 {
  margin: 2px 0 0;
}

.package-product-type {
  margin: 0;
  color: #e45800;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.package-product-body h3 {
  margin: 0;
  font-size: 18px;
  line-height: 1.2;
}

.package-vendor {
  margin: 0;
  color: #556173;
  font-weight: 700;
  line-height: 1.25;
}

.package-location {
  margin: 0;
  color: #7687a0;
  line-height: 1.3;
}

.package-price {
  font-size: 17px;
  color: #d4580a;
  white-space: nowrap;
}

.package-product-desc {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.35;
}

.package-price {
  display: block;
}

.package-rating {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #475569;
  font-size: 13px;
  line-height: 1.2;
}

.package-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: 4px;
}

.package-price-stack {
  display: grid;
  gap: 1px;
}

.package-price-stack small {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #8a94a8;
  font-size: 11px;
}

.package-product-footer {
  margin-top: 4px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.package-product-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: nowrap;
  min-width: 0;
}

.package-product-actions .view-details-btn {
  border: 1px solid #d7e4f3;
  background: #f5f8ff;
  color: #1e293b;
}

.package-product-actions button {
  flex: 1 1 0;
  min-width: 0;
  white-space: nowrap;
}

.package-product-actions .favorite-btn {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border: 1px solid #f0d7c2;
  color: #d4580a;
  background: #fffaf5;
}

.package-product-footer strong {
  color: #1e293b;
  font-size: 16px;
  line-height: 1.2;
}

.package-product-footer span {
  color: #e45800;
  font-weight: 700;
  font-size: 14px;
}

.package-product-actions .choice-indicator {
  width: 100%;
  min-height: 34px;
  padding: 7px 11px;
  border-radius: 10px;
  border-color: #f5c09c;
  background: #fff7f1;
  color: #c2410c;
}

.package-product-actions .choice-indicator:hover {
  background: #fff1e8;
  border-color: #efb183;
}

.package-product-actions .favorite-btn {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
}

.service-card-anchor {
  scroll-margin-top: 120px;
}

.focused-target-card {
  animation: targetFocusPulse 1.2s ease;
}

@keyframes targetFocusPulse {
  0% {
    box-shadow: 0 0 0 0 rgba(242, 92, 5, 0.35);
    border-color: #f8b182;
  }
  70% {
    box-shadow: 0 0 0 12px rgba(242, 92, 5, 0);
    border-color: #f29957;
  }
  100% {
    box-shadow: none;
    border-color: #dbe4f1;
  }
}

.package-product-actions .favorite-btn {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
}

.package-product-actions button {
  flex: 1 1 0;
  min-width: 0;
  white-space: nowrap;
}

.package-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 90;
}

.prebook-overlay {
  position: fixed;
  inset: 0;
  z-index: 95;
  background: rgba(17, 24, 39, 0.55);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 14px;
  overflow-y: auto;
}

.prebook-modal {
  width: min(760px, 100%);
  max-height: calc(100dvh - 28px);
  margin: auto 0;
  display: flex;
  flex-direction: column;
  background:
    radial-gradient(circle at 92% 0%, rgba(255, 107, 0, 0.14), transparent 46%),
    radial-gradient(circle at 0% 100%, rgba(59, 130, 246, 0.1), transparent 42%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
  border-radius: 24px;
  border: 1px solid rgba(229, 231, 235, 0.9);
  box-shadow:
    0 40px 110px rgba(15, 23, 42, 0.36),
    0 18px 44px rgba(255, 107, 0, 0.14);
  overflow: hidden;
}

.prebook-head {
  position: sticky;
  top: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(229, 231, 235, 0.9);
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.prebook-head-main {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.prebook-head-badge {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-weight: 800;
  color: var(--text);
  background:
    linear-gradient(
        180deg,
        rgba(255, 255, 255, 0.9) 0%,
        rgba(255, 255, 255, 0.7) 100%
      )
      padding-box,
    linear-gradient(135deg, rgba(255, 107, 0, 0.6), rgba(59, 130, 246, 0.22))
      border-box;
  border: 1px solid transparent;
  box-shadow:
    0 18px 40px rgba(15, 23, 42, 0.1),
    0 1px 0 rgba(255, 255, 255, 0.85) inset;
}

.prebook-head-icon {
  width: 22px;
  height: 22px;
  display: block;
}

.prebook-head-copy {
  min-width: 0;
}

.prebook-head h3 {
  margin: 0;
  font-size: 20px;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.prebook-head p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 13.5px;
}

.prebook-steps {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.prebook-step {
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  background: rgba(255, 255, 255, 0.72);
  font-size: 12px;
  font-weight: 700;
  color: rgba(31, 41, 55, 0.78);
}

.prebook-step-active {
  border-color: rgba(255, 107, 0, 0.32);
  background: rgba(255, 247, 237, 0.92);
  color: #c2410c;
}

.prebook-close {
  width: 38px;
  height: 38px;
  border: 1px solid rgba(229, 231, 235, 0.95);
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.75);
  color: rgba(31, 41, 55, 0.88);
  cursor: pointer;
  display: grid;
  place-items: center;
  box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
  padding: 0;
}

.prebook-close-icon {
  width: 18px;
  height: 18px;
  display: block;
}

.prebook-close:hover {
  background: rgba(255, 255, 255, 0.92);
  transform: translateY(-1px);
}

.prebook-form {
  padding: 16px 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  overflow-y: auto;
}

.prebook-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.85fr);
  gap: 14px;
  align-items: start;
}

.prebook-col {
  min-width: 0;
  display: grid;
  gap: 14px;
}

.prebook-col-aside .prebook-grid {
  grid-template-columns: 1fr;
}

.prebook-section {
  border-radius: 20px;
  border: 1px solid rgba(229, 231, 235, 0.9);
  background:
    radial-gradient(circle at 0% 0%, rgba(255, 106, 0, 0.08), transparent 40%),
    radial-gradient(circle at 100% 100%, rgba(37, 99, 235, 0.06), transparent 46%),
    rgba(255, 255, 255, 0.72);
  box-shadow:
    0 18px 48px rgba(15, 23, 42, 0.06),
    0 1px 0 rgba(255, 255, 255, 0.85) inset;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  overflow: hidden;
}

.prebook-section-head {
  padding: 14px 14px 10px;
  border-bottom: 1px solid rgba(229, 231, 235, 0.9);
  background: rgba(255, 255, 255, 0.6);
}

.prebook-section-head h4 {
  margin: 0;
  font-size: 14px;
  letter-spacing: 0.02em;
  font-weight: 700;
}

.prebook-section-head p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.prebook-section .prebook-grid {
  padding: 12px 14px 14px;
}

.prebook-summary {
  border-radius: 20px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  padding: 14px;
  background:
    radial-gradient(circle at 10% 0%, rgba(255, 107, 0, 0.22), transparent 56%),
    radial-gradient(circle at 100% 90%, rgba(59, 130, 246, 0.16), transparent 52%),
    rgba(255, 255, 255, 0.74);
  box-shadow:
    0 22px 56px rgba(15, 23, 42, 0.08),
    0 1px 0 rgba(255, 255, 255, 0.85) inset;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  display: grid;
  gap: 12px;
}

.prebook-summary-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.prebook-summary-title {
  font-family: "Sora", "Manrope", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-weight: 700;
  letter-spacing: -0.02em;
  color: var(--text);
  line-height: 1.2;
  min-width: 0;
}

.prebook-summary-pill {
  flex-shrink: 0;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  background: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  font-weight: 800;
  color: rgba(31, 41, 55, 0.82);
}

.prebook-summary-lines {
  display: grid;
  gap: 10px;
}

.prebook-summary-line,
.prebook-summary-total {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
}

.prebook-summary-line span {
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
  max-width: 64%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.prebook-summary-line strong {
  color: var(--text);
  font-size: 13px;
  font-weight: 800;
  text-align: right;
  max-width: 62%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.prebook-summary-muted strong {
  color: #475569;
}

.prebook-summary-total {
  padding-top: 12px;
  border-top: 1px solid rgba(229, 231, 235, 0.92);
}

.prebook-summary-total span {
  color: rgba(31, 41, 55, 0.86);
  font-size: 13px;
  font-weight: 800;
}

.prebook-summary-total strong {
  font-family: "Sora", "Manrope", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  color: var(--accent-dark);
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.prebook-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.prebook-span-2 {
  grid-column: 1 / -1;
}

.prebook-field {
  display: grid;
  gap: 8px;
}

.prebook-field span {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(107, 114, 128, 0.95);
}

.prebook-date-picker {
  position: relative;
}

.prebook-date-trigger {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.86);
  color: var(--text);
  font: inherit;
  padding: 12px 14px;
  cursor: pointer;
  box-shadow:
    0 12px 24px rgba(15, 23, 42, 0.06),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
}

.prebook-date-trigger span:first-child {
  text-align: left;
}

.prebook-date-icon {
  width: 34px;
  height: 34px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(248, 250, 252, 0.92);
  border: 1px solid rgba(229, 231, 235, 0.92);
  color: rgba(31, 41, 55, 0.84);
}

.prebook-date-icon svg {
  width: 18px;
  height: 18px;
  display: block;
}

.prebook-calendar {
  margin-top: 10px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.1);
  padding: 16px;
  display: grid;
  gap: 14px;
}

.prebook-calendar-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.prebook-calendar-head strong {
  display: block;
  font-size: 28px;
  line-height: 1.05;
}

.prebook-calendar-head small {
  display: block;
  margin-top: 6px;
  color: var(--muted);
  font-size: 14px;
}

.prebook-calendar-nav {
  display: flex;
  gap: 8px;
}

.prebook-calendar-nav button {
  width: 40px;
  height: 40px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 12px;
  background: #fff;
  color: rgba(31, 41, 55, 0.9);
  font: inherit;
  font-size: 18px;
  cursor: pointer;
}

.prebook-calendar-close {
  padding: 0;
  display: grid;
  place-items: center;
}

.prebook-calendar-close svg {
  width: 18px;
  height: 18px;
  display: block;
}

.prebook-calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 8px;
}

.prebook-calendar-weekdays span {
  text-align: center;
  color: rgba(107, 114, 128, 0.9);
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.prebook-calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 8px;
}

.prebook-calendar-day {
  min-height: 56px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 14px;
  background: #fff;
  color: var(--text);
  font: inherit;
  font-size: 22px;
  cursor: pointer;
}

.prebook-calendar-day.muted {
  background: #f8fafc;
  color: #cbd5e1;
  cursor: not-allowed;
}

.prebook-calendar-day:disabled {
  background: #f8fafc;
  color: #cbd5e1;
  cursor: not-allowed;
}

.prebook-calendar-day.selected {
  border-color: #f49e1d;
  background: #f49e1d;
  box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.72);
  color: #ffffff;
}

.prebook-calendar-day.available {
  background: #22c55e;
  color: #ffffff;
  border-color: #22c55e;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.34);
}

.prebook-calendar-day.booked {
  background: #ef4444;
  color: #fff;
  border-color: #ef4444;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
}

.prebook-calendar-day.busy {
  background:
    linear-gradient(135deg, rgba(239, 68, 68, 0.14), rgba(248, 113, 113, 0.04)),
    #fff1f2;
  color: #991b1b;
  border-color: #fda4af;
}

.prebook-calendar-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  color: var(--muted);
  font-size: 13px;
}

.prebook-calendar-legend span {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.prebook-calendar-legend .dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  display: inline-block;
}

.prebook-calendar-legend .dot.available {
  background: #22c55e;
}

.prebook-calendar-legend .dot.booked {
  background: #ef4444;
}

.prebook-calendar-legend .dot.selected {
  background: #f4c21d;
}

.prebook-calendar-loading {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
}

.prebook-availability-state {
  border-radius: 12px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  background: rgba(248, 250, 252, 0.8);
  color: rgba(75, 85, 99, 0.96);
  font-size: 13px;
  font-weight: 700;
  line-height: 1.5;
  padding: 10px 12px;
}

.prebook-availability-state.available {
  border-color: #bbf7d0;
  background: #f0fdf4;
  color: #166534;
}

.prebook-availability-state.booked {
  border-color: #fecaca;
  background: #fef2f2;
  color: #991b1b;
}

.prebook-location-tools {
  display: grid;
  gap: 10px;
  padding: 12px;
  border-radius: 18px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  background: rgba(248, 250, 252, 0.66);
  box-shadow:
    0 18px 46px rgba(15, 23, 42, 0.08),
    0 1px 0 rgba(255, 255, 255, 0.85) inset;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.prebook-location-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.prebook-location-notice,
.prebook-location-coords {
  margin: 0;
  color: rgba(75, 85, 99, 0.98);
  font-size: 13px;
  font-weight: 600;
}

.prebook-map-placeholder {
  height: 220px;
  border: 1px dashed rgba(209, 213, 219, 0.95);
  border-radius: 18px;
  display: grid;
  place-items: center;
  background:
    radial-gradient(circle at 20% 10%, rgba(234, 88, 12, 0.08), transparent 55%),
    radial-gradient(circle at 90% 90%, rgba(37, 99, 235, 0.06), transparent 55%),
    rgba(226, 232, 240, 0.45);
  color: rgba(75, 85, 99, 0.86);
  font-size: 13px;
  font-weight: 700;
  text-align: center;
  padding: 10px;
}

.prebook-map-frame {
  width: 100%;
  height: 220px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 18px;
  background: #e2e8f0;
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
}

.prebook-map-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 0.86rem;
  font-weight: 800;
  color: var(--accent-dark);
  text-decoration: none;
}

.prebook-map-link:hover {
  text-decoration: underline;
  text-underline-offset: 3px;
}

.prebook-form input,
.prebook-form textarea {
  width: 100%;
  min-height: 46px;
  border: 1px solid rgba(229, 231, 235, 0.92);
  border-radius: 16px;
  padding: 12px 14px;
  font: inherit;
  background: rgba(255, 255, 255, 0.86);
  color: var(--text);
  outline: none;
  box-shadow:
    0 12px 24px rgba(15, 23, 42, 0.06),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
  transition:
    border-color 0.18s ease,
    box-shadow 0.18s ease,
    background-color 0.18s ease,
    transform 0.18s ease;
}

.prebook-form input:focus,
.prebook-form textarea:focus {
  border-color: rgba(255, 107, 0, 0.6);
  background: rgba(255, 255, 255, 0.98);
  box-shadow:
    0 0 0 4px rgba(255, 107, 0, 0.18),
    0 18px 34px rgba(15, 23, 42, 0.08);
}

.prebook-form textarea {
  min-height: 96px;
  resize: vertical;
}

.prebook-success {
  margin: 0;
  color: #166534;
  font-size: 13px;
  font-weight: 700;
}

.prebook-actions {
  position: sticky;
  bottom: 0;
  margin: 4px -16px -18px;
  padding: 12px 16px 14px;
  display: grid;
  gap: 10px;
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-top: 1px solid rgba(229, 231, 235, 0.95);
}

.prebook-actions-hint {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
  font-weight: 600;
}

.prebook-actions .modal-action-btn {
  width: 100%;
  border-radius: 16px;
  min-height: 54px;
  font-size: 16px;
  letter-spacing: 0.01em;
  box-shadow: 0 18px 44px rgba(255, 107, 0, 0.28);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0)) padding-box,
    linear-gradient(90deg, #ff6b00 0%, #ff7a1f 45%, #ff6b00 100%) border-box;
  border: 1px solid transparent;
}

.package-modal {
  width: min(760px, 100%);
  max-height: 88vh;
  overflow-y: auto;
  border: 1px solid #dde5f2;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.3);
  padding: 16px;
}

.package-modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.package-modal-head h3 {
  margin: 2px 0 0;
  font-size: 24px;
}

.package-modal-close {
  border: 1px solid #dbe4f1;
  border-radius: 10px;
  background: #fff;
  width: 34px;
  height: 34px;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.package-modal-image {
  width: 100%;
  height: 280px;
  object-fit: cover;
  border-radius: 12px;
  border: 1px solid #e2e8f3;
  margin-top: 10px;
}

.package-modal-desc {
  margin: 12px 0 0;
  color: #475569;
  line-height: 1.5;
}

.package-modal-meta {
  margin-top: 10px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

.package-modal-meta p {
  margin: 0;
  border: 1px solid #e2e8f3;
  background: #f8fafc;
  border-radius: 10px;
  padding: 10px;
  font-size: 13px;
}

.package-modal-services {
  margin-top: 14px;
}

.package-modal-services h4 {
  margin: 0;
}

.package-modal-services ul {
  margin: 8px 0 0;
  padding-left: 18px;
  display: grid;
  gap: 6px;
}

.package-modal-services li {
  color: #5a6e88;
  line-height: 1.4;
}

.package-modal-actions {
  margin-top: 14px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.vendor-modal {
  max-width: 640px;
}

.vendor-modal-image {
  height: 260px;
  object-fit: cover;
}

.vendor-subtitle {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
}

.vendor-meta {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
}

.vendor-meta p {
  margin: 0;
  border: 1px solid #e2e8f3;
  border-radius: 10px;
  padding: 10px;
  background: #f8fafc;
  display: grid;
  gap: 6px;
}

.vendor-meta strong {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #64748b;
}

.vendor-meta span {
  color: #0f172a;
  font-weight: 800;
}

.vendor-modal .package-modal-actions {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.view-vendor-btn {
  border-color: #f5d0b5;
  background: #fff7f1;
  color: #c2410c;
}

.view-vendor-btn:hover {
  background: #ffefe2;
  border-color: #f2b98d;
}

.modal-action-btn {
  width: auto;
  min-height: 44px;
  border-radius: 12px;
  border: 1px solid #d8e2ef;
  font: inherit;
  font-size: 15px;
  font-weight: 800;
  line-height: 1;
  padding: 10px 14px;
  cursor: pointer;
  text-align: center;
}

.modal-action-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.modal-action-secondary {
  background: #fff;
  border-color: #ffc7a3;
  color: #c2410c;
}

.modal-action-secondary:hover {
  background: #fff6f0;
  border-color: #ffb081;
}

.modal-action-neutral {
  background: #f8fafc;
  border-color: #d7e1ee;
  color: #334155;
}

.modal-action-neutral:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.modal-action-primary {
  background: #f25c05;
  border-color: #f25c05;
  color: #fff;
}

.modal-action-primary:hover {
  background: #e45800;
  border-color: #e45800;
}

.favorite-layout {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 20px;
  align-items: start;
}

.favorite-card {
  position: relative;
  border: 1px solid #e4ecf8;
  border-radius: 16px;
  background: linear-gradient(145deg, #ffffff, #f7faff);
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  padding: 16px 18px;
}

.favorite-card h3 {
  margin: 0 0 6px;
  font-size: 18px;
  letter-spacing: -0.01em;
}

.favorite-list {
  list-style: none;
  margin: 16px 0 0;
  padding: 0;
  display: grid;
  gap: 14px;
}

.favorite-list li {
  border: 1px solid #e6edf8;
  border-radius: 18px;
  padding: 16px;
  background:
    radial-gradient(circle at 8% 12%, rgba(255, 195, 113, 0.14), transparent 42%),
    radial-gradient(circle at 90% 0%, rgba(99, 179, 237, 0.14), transparent 42%),
    #ffffff;
  box-shadow:
    0 12px 30px rgba(15, 23, 42, 0.1),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 14px;
  transition: transform 180ms ease, box-shadow 180ms ease;
}

.favorite-list li:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.14);
}

.favorite-thumb {
  width: 78px;
  height: 78px;
  aspect-ratio: 1;
  border-radius: 12px;
  object-fit: cover;
  background: #f8fafc;
  border: 1px solid #e2e8f3;
  flex-shrink: 0;
}

.favorite-text {
  flex: 1;
  min-width: 0;
}

.favorite-list strong {
  display: block;
  font-size: 16px;
  color: #0f172a;
  letter-spacing: -0.01em;
}

.favorite-list small {
  color: #556177;
  font-size: 13px;
}

.favorite-remove {
  border: 1px solid #f4c7a0;
  border-radius: 999px;
  background: linear-gradient(180deg, #fff5ec 0%, #ffe8d6 100%);
  color: #d9480f;
  font-size: 12px;
  font-weight: 800;
  padding: 10px 16px;
  cursor: pointer;
  transition: background 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
  flex-shrink: 0;
  box-shadow: 0 10px 22px rgba(234, 88, 12, 0.12);
}

.favorite-remove:hover {
  background: #ffe0c7;
  border-color: #eca26b;
  box-shadow: 0 12px 24px rgba(217, 72, 15, 0.16);
}

.booking-details-modal {
  max-width: 640px;
}

.booking-detail-grid {
  margin-top: 14px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.booking-detail-grid p {
  margin: 0;
  border: 1px solid #e2e8f3;
  background: #f8fafc;
  border-radius: 10px;
  padding: 10px 12px;
  display: grid;
  gap: 6px;
}

.booking-detail-grid strong {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #64748b;
}

.booking-detail-grid span {
  color: #1e293b;
  font-weight: 700;
}

.booking-detail-note {
  margin: 14px 0 0;
  color: #475569;
  line-height: 1.5;
}

.booking-items-panel {
  margin-top: 14px;
  border: 1px solid #e2e8f3;
  border-radius: 12px;
  background: #fbfdff;
  padding: 12px;
}

.booking-items-panel h4 {
  margin: 0 0 10px;
  font-size: 16px;
}

.booking-items-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 8px;
}

.booking-items-list li {
  border: 1px solid #e2e8f3;
  border-radius: 10px;
  background: #fff;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.booking-items-list strong {
  display: block;
  color: #1e293b;
}

.booking-items-list small {
  color: #64748b;
}

.booking-item-description {
  display: block;
  margin-top: 4px;
  line-height: 1.4;
}

.booking-item-prices {
  text-align: right;
  display: grid;
  gap: 4px;
}

.booking-items-list span {
  color: #0f172a;
  font-weight: 800;
}

.booking-item-prices small {
  color: #64748b;
}

.booking-detail-empty {
  margin: 0;
  color: #475569;
}

.favorite-booking-card {
  margin-top: 12px;
  border: 1px solid #f5d5bd;
  background: radial-gradient(circle at 20% 20%, #fff6ec, #ffffff);
  padding: 18px;
  box-shadow: 0 12px 32px rgba(242, 92, 5, 0.08);
}

.favorite-booking-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 14px;
}

.favorite-booking-grid input {
  width: 100%;
  height: 44px;
  border: 1px solid #d7e4f3;
  border-radius: 12px;
  background: #fff;
  padding: 10px 12px;
  font: inherit;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
}

.favorite-booking-grid select {
  width: 100%;
  height: 44px;
  border-radius: 12px;
  border: 1px solid #d7e4f3;
  background: #fff;
  padding: 10px 12px;
  font: inherit;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
}

@media (min-width: 1024px) {
  .overall-layout {
    grid-template-columns: minmax(0, 1fr) 380px;
  }

  .package-layout {
    grid-template-columns: minmax(0, 1fr) 360px;
  }
}

@media (max-width: 720px) {
  .flow-head-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .checkout-flow-steps {
    margin-top: 6px;
  }

  .package-toolbar {
    grid-template-columns: 1fr;
    align-items: stretch;
  }

  .package-count {
    justify-self: start;
  }

  .package-catalog-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .prebook-overlay {
    padding: 8px;
  }

  .prebook-modal {
    max-height: calc(100dvh - 16px);
    border-radius: 12px;
  }

  .prebook-head {
    padding: 12px;
  }

  .prebook-head h3 {
    font-size: 19px;
  }

  .prebook-form {
    padding: 12px;
    gap: 10px;
  }

  .prebook-layout {
    grid-template-columns: 1fr;
  }

  .prebook-actions {
    margin: 4px -12px -12px;
    padding: 10px 12px 12px;
  }

  .prebook-form input,
  .prebook-form textarea {
    padding: 10px 11px;
  }

  .prebook-grid {
    grid-template-columns: 1fr;
  }

  .prebook-calendar-head {
    flex-direction: column;
  }

  .prebook-calendar-weekdays,
  .prebook-calendar-grid {
    gap: 6px;
  }

  .prebook-calendar-day {
    min-height: 48px;
    font-size: 18px;
  }

  .overall-toolbar {
    grid-template-columns: 1fr;
    align-items: stretch;
  }

  .overall-count {
    justify-self: start;
    font-size: 16px;
    padding: 10px 12px;
  }

  .overall-layout {
    grid-template-columns: 1fr;
  }

  .overall-summary {
    position: static;
    margin-top: 14px;
  }

  .favorite-layout {
    grid-template-columns: 1fr;
  }

  .favorite-booking-grid {
    grid-template-columns: 1fr;
  }

  .package-modal-meta {
    grid-template-columns: 1fr;
  }

  .package-modal-image {
    height: 200px;
  }

  .package-modal-actions {
    grid-template-columns: 1fr;
  }

  .booking-detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .favorite-card {
    padding: 14px 14px;
  }

  .favorite-thumb {
    width: 48px;
    height: 48px;
  }

  .favorite-list li {
    gap: 10px;
    grid-template-columns: auto 1fr;
  }
}

/* Packages page layout with right-hand booking summary */
.package-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  align-items: start;
}

.package-layout > .package-head {
  grid-column: 1 / -1;
}

.package-layout-main {
  min-width: 0;
  max-height: calc(100dvh - 80px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.package-summary {
  position: sticky;
  top: 12px;
  max-height: calc(100dvh - 40px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  margin-top: 0;
  height: fit-content;
}

@media (max-width: 980px) {
  .package-layout {
    grid-template-columns: 1fr;
  }
  .package-summary {
    position: static;
    max-height: none;
    overflow: visible;
    margin-top: 14px;
  }

  .package-layout-main,
  .overall-list,
  .overall-summary {
    max-height: none;
    overflow: visible;
  }
}
</style>



