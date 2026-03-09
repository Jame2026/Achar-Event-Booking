<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useRoute } from "vue-router";
import DashboardPage from "./pages/DashboardPage.vue";
import CustomizationPage from "./pages/CustomizationPage.vue";
import ServiceCard from "./customization/ServiceCard.vue";
import BookingsPage from "./pages/BookingsPage.vue";
import PublicNavbar from "./PublicNavbar.vue";
import { apiGet } from "../features/apiClient";
import { useLanguage } from "../features/language";
import {
  buildPackageServiceDescriptions,
  eventTypeMap,
  eventTypeOptions,
  packageImageByEventType,
  serviceFeeRate,
  vendorProfile,
} from "../features/appData";
import { isDateLikelyBooked, isSlotLikelyBooked } from "../features/availabilityUtils";
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
    serviceFee: "Service Fee (10%)",
    totalPrice: "Total Price",
    prebookFavoriteItems: "Pre-book Favorite Items",
    favoriteBundle: "Favorite Services Bundle",
    noPackagesForEvent: "No packages available for this event.",
    signInLoadDashboard: "Sign in to load your dashboard data.",
    packagePageTitle: "Service Packages",
    packagePageText: "Browse available packages by event type. Search quickly and choose the best fit for your event.",
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
    serviceFee: "ថ្លៃសេវា (10%)",
    totalPrice: "តម្លៃសរុប",
    prebookFavoriteItems: "កក់ជាមុនរបស់ដែលចូលចិត្ត",
    favoriteBundle: "កញ្ចប់សេវាកម្មដែលចូលចិត្ត",
    noPackagesForEvent: "មិនមានកញ្ចប់សម្រាប់ព្រឹត្តិការណ៍នេះទេ។",
    signInLoadDashboard: "សូមចូលគណនីដើម្បីផ្ទុកទិន្នន័យផ្ទាំងគ្រប់គ្រងរបស់អ្នក។",
    packagePageTitle: "កញ្ចប់សេវាកម្ម",
    packagePageText: "រកមើលកញ្ចប់ដែលមានតាមប្រភេទព្រឹត្តិការណ៍។ ស្វែងរកយ៉ាងរហ័ស ហើយជ្រើសរើសអ្វីដែលសមបំផុតសម្រាប់ព្រឹត្តិការណ៍របស់អ្នក។",
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
    serviceFee: "服务费 (10%)",
    totalPrice: "总价",
    prebookFavoriteItems: "预订收藏项目",
    favoriteBundle: "收藏服务组合",
    noPackagesForEvent: "当前活动没有可用套餐。",
    signInLoadDashboard: "请登录以加载您的仪表盘数据。",
    packagePageTitle: "服务套餐",
    packagePageText: "按活动类型浏览可用套餐。快速搜索并选择最适合您活动的方案。",
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
const customizationEventType = ref("all");
const customizationSearch = ref("");
const selectedCustomizationPackageId = ref(null);
const customizationQuantity = ref(1);

// state for packages page selection
const selectedPackageId = ref(null);
const packageQuantity = ref(1);
const selectedServiceIds = ref([]);
const expandedServiceId = ref(null);
const packageEventType = ref("all");
const packageSearch = ref("");
const overallQuantity = ref(1);
const overallAvailabilityDate = ref(
  `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, "0")}-${String(new Date().getDate()).padStart(2, "0")}`,
);
const overallAvailabilitySlot = ref("");
const overallSlotOptions = ["08:00 AM", "09:30 AM", "11:00 AM", "01:00 PM", "02:30 PM", "04:00 PM", "05:30 PM", "07:00 PM", "08:30 PM"];

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

const emptyDashboardStats = {
  totalBookings: 0,
  upcomingBookings: 0,
  completedBookings: 0,
  unreadMessages: 0,
};
const savedFavorites = (() => {
  try {
    const raw = localStorage.getItem(FAVORITES_STORAGE_KEY);
    if (!raw) return { packageIds: [], serviceIds: [] };
    const parsed = JSON.parse(raw);
    return {
      packageIds: Array.isArray(parsed.packageIds) ? parsed.packageIds : [],
      serviceIds: Array.isArray(parsed.serviceIds) ? parsed.serviceIds : [],
    };
  } catch {
    return { packageIds: [], serviceIds: [] };
  }
})();
const favoritePackageIds = ref(savedFavorites.packageIds);
const favoriteServiceIds = ref(savedFavorites.serviceIds);
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
  };
}

function mapEventToGuestService(item) {
  const eventType = String(item.event_type || "other");
  return {
    id: `live-service-${item.id}`,
    name: String(item.title || "Service Booking"),
    description: String(item.description || "").trim() || "Professional vendor service ready for booking.",
    price: Number(item.price || 0),
    eventTypes: [eventType],
    backingEventId: Number(item.id || 0) || null,
    vendorName: String(item.vendor?.name || "Verified Vendor"),
    location: item.location || "Location TBD",
  };
}

async function loadLiveVendorEvents() {
  try {
    const result = await apiGet("events", { per_page: 100, include_inactive: 1 });
    liveVendorEvents.value = Array.isArray(result.data) ? result.data : [];
  } catch {
    liveVendorEvents.value = [];
  }
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
  const targetPackage = activePackage.value || selectedPackage.value;
  prebookTargetPackageId.value = targetPackage?.id || null;
  prebookTargetTitle.value = targetPackage?.title || "Selected Vendor";
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
    vendorTitle: prebookTargetTitle.value || "Selected Vendor",
    vendorName:
      selectedPackage.value?.vendorName ||
      activePackage.value?.vendorName ||
      "Selected Vendor",
    eventId:
      selectedPackage.value?.backingEventId ||
      activePackage.value?.backingEventId ||
      null,
    image:
      selectedPackage.value?.image ||
      activePackage.value?.image ||
      packageImageByEventType[
        selectedPackage.value?.eventType || activePackage.value?.eventType || "other"
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
    cells.push({
      id: iso,
      day,
      date: iso,
      inMonth: true,
      booked: prebookCalendarAvailability.value[iso] === "booked",
      available: prebookCalendarAvailability.value[iso] === "available",
      disabled: date < today,
    });
  }

  return cells;
});

const activePrebookEventId = computed(
  () => selectedPackage.value?.backingEventId || activePackage.value?.backingEventId || null,
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
      days.map((item) => [String(item.date), String(item.status || "available")]),
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

function overallDateChanged(e) {
  overallAvailabilityDate.value = e.target.value;
  overallAvailabilitySlot.value = "";
}

function selectOverallSlot(slot) {
  if (isSlotLikelyBooked(overallAvailabilityDate.value, slot)) return;
  overallAvailabilitySlot.value = slot;
}

function persistFavorites() {
  localStorage.setItem(
    FAVORITES_STORAGE_KEY,
    JSON.stringify({
      packageIds: favoritePackageIds.value,
      serviceIds: favoriteServiceIds.value,
    }),
  );
  window.dispatchEvent(new Event("achar:favorites-updated"));
}

function syncFavoritesWithLiveCatalog() {
  const validPackageIds = new Set(guestPreviewPackages.value.map((item) => item.id));
  const validServiceIds = new Set(servicesCatalog.value.map((service) => service.id));
  const nextPackageIds = favoritePackageIds.value.filter((id) => validPackageIds.has(id));
  const nextServiceIds = favoriteServiceIds.value.filter((id) => validServiceIds.has(id));
  const packagesChanged = nextPackageIds.length !== favoritePackageIds.value.length;
  const servicesChanged = nextServiceIds.length !== favoriteServiceIds.value.length;

  if (!packagesChanged && !servicesChanged) {
    return;
  }

  favoritePackageIds.value = nextPackageIds;
  favoriteServiceIds.value = nextServiceIds;
  persistFavorites();
}

function toggleFavoritePackage(id) {
  if (favoritePackageIds.value.includes(id)) {
    favoritePackageIds.value = favoritePackageIds.value.filter(
      (item) => item !== id,
    );
  } else {
    favoritePackageIds.value = [...favoritePackageIds.value, id];
  }
  persistFavorites();
}

function toggleFavoriteService(id) {
  if (favoriteServiceIds.value.includes(id)) {
    favoriteServiceIds.value = favoriteServiceIds.value.filter(
      (item) => item !== id,
    );
  } else {
    favoriteServiceIds.value = [...favoriteServiceIds.value, id];
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
  guestPreviewPackages.value.filter((item) =>
    favoritePackageIds.value.includes(item.id),
  ),
);

const favoriteServices = computed(() =>
  servicesCatalog.value.filter((service) =>
    favoriteServiceIds.value.includes(service.id),
  ),
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

onMounted(() => {
  void loadLiveVendorEvents();
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
const overallDateBooked = computed(() => isDateLikelyBooked(overallAvailabilityDate.value));
const overallSlotItems = computed(() =>
  overallSlotOptions.map((value) => ({
    value,
    booked: overallDateBooked.value || isSlotLikelyBooked(overallAvailabilityDate.value, value),
  })),
);
const selectedAvailabilityDateLabel = computed(() => {
  const value = String(overallAvailabilityDate.value || "").trim();
  if (!value) return "No date selected";
  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString("en-US", {
    weekday: "long",
    month: "long",
    day: "numeric",
    year: "numeric",
  });
});
const availabilitySlotGroups = computed(() => {
  const groups = [
    { key: "morning", label: uiText.value.morning, slots: ["08:00 AM", "09:30 AM", "11:00 AM"] },
    { key: "afternoon", label: uiText.value.afternoon, slots: ["01:00 PM", "02:30 PM", "04:00 PM", "05:30 PM"] },
    { key: "evening", label: uiText.value.evening, slots: ["07:00 PM", "08:30 PM"] },
  ];
  return groups.map((group) => ({
    ...group,
    items: group.slots
      .map((value) => overallSlotItems.value.find((slot) => slot.value === value))
      .filter(Boolean),
  }));
});
const overallAvailabilityState = computed(() => {
  if (!overallAvailabilityDate.value) return { label: uiText.value.pickDate, tone: "neutral" };
  if (overallDateBooked.value) return { label: uiText.value.bookedOnSelectedDate, tone: "booked" };
  if (!overallAvailabilitySlot.value) return { label: uiText.value.pickTimeSlot, tone: "neutral" };
  const booked = isSlotLikelyBooked(overallAvailabilityDate.value, overallAvailabilitySlot.value);
  return booked
    ? { label: uiText.value.selectedSlotBooked, tone: "booked" }
    : { label: uiText.value.selectedSlotAvailable, tone: "available" };
});

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
        class="package-layout"
      >
        <section class="package-head card">
          <div class="package-head-main">
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
            <div class="package-toolbar">
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
              <div class="package-count">
                {{ guestPreviewPackagesFiltered.length }}
                {{ guestPreviewPackagesFiltered.length === 1 ? uiText.packageCountSingle : uiText.packageCount }}
              </div>
            </div>
          </div>
        </section>

        <div class="package-layout-main">
          <div class="package-catalog">
            <div class="package-grid">
              <p
                v-if="guestPreviewPackagesFiltered.length === 0"
                class="guest-text package-empty"
              >
                {{ uiText.noPackagesForEvent }}
              </p>
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
                <img :src="item.image" :alt="item.title" />
                <div class="package-product-body">
                  <p class="package-product-type">{{ item.eventTypeLabel }}</p>
                  <h3>{{ item.title }}</h3>
                  <p class="package-product-desc">{{ item.description }}</p>
                  <div class="package-product-footer">
                    <strong>{{ item.priceLabel }}</strong>
                    <div class="package-product-actions">
                      <button
                        type="button"
                        class="favorite-btn"
                        :class="{ active: isPackageFavorite(item.id) }"
                        @click.stop="toggleFavoritePackage(item.id)"
                      >
                        {{ isPackageFavorite(item.id) ? "\u2665" : "\u2661" }}
                      </button>
                      <button
                        type="button"
                        class="choice-indicator"
                        @click.stop="selectPackage(item.id)"
                      >
                        {{
                          selectedPackageId === item.id
                            ? uiText.selected
                            : uiText.selectPackage
                        }}
                      </button>
                      <span>{{ uiText.viewDetails }}</span>
                    </div>
                  </div>
                </div>
              </article>
            </div>
            <!-- services below packages on the same page -->
          </div>
        </div>

        <aside class="card customization-summary package-summary">
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

          <div class="overall-availability-check">
            <h3>{{ uiText.checkAvailability }}</h3>
            <div class="availability-intro">
              <h4>{{ uiText.selectEventDate }}</h4>
              <p>{{ uiText.pickDateText }}</p>
            </div>
            <label class="availability-date-field">
              <span>{{ uiText.eventDate }}</span>
              <input
                type="date"
                :value="overallAvailabilityDate"
                :min="`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`"
                @input="overallDateChanged"
              />
            </label>

            <div class="availability-legend">
              <span><i class="availability-dot availability-dot-available"></i>{{ uiText.available }}</span>
              <span><i class="availability-dot availability-dot-booked"></i>{{ uiText.booked }}</span>
              <span><i class="availability-dot availability-dot-selected"></i>{{ uiText.selected }}</span>
            </div>

            <div class="availability-time-head">
              <h4>{{ uiText.availableTimeSlots }}</h4>
              <span>{{ selectedAvailabilityDateLabel }}</span>
            </div>
            <div
              v-for="group in availabilitySlotGroups"
              :key="group.key"
              class="availability-period"
            >
              <p class="availability-period-title">
                {{ group.label }}
              </p>
              <div class="availability-slot-grid">
                <button
                  v-for="slot in group.items"
                  :key="slot.value"
                  type="button"
                  class="availability-slot-btn"
                  :class="{
                    selected: overallAvailabilitySlot === slot.value && !slot.booked,
                    booked: slot.booked,
                  }"
                  :disabled="slot.booked"
                  @click="selectOverallSlot(slot.value)"
                >
                  {{ slot.booked ? uiText.booked : slot.value }}
                </button>
              </div>
            </div>

            <div class="availability-selection">
              <span>{{ uiText.selectedDate }}</span>
              <strong>{{ selectedAvailabilityDateLabel }}</strong>
            </div>
            <div
              class="availability-state"
              :class="{
                available: overallAvailabilityState.tone === 'available',
                booked: overallAvailabilityState.tone === 'booked',
              }"
            >
              {{ overallAvailabilityState.label }}
            </div>
          </div>

          <button type="button" class="confirm-selection" @click="openPrebookForm">
            {{ uiText.prebookNow }}
          </button>
        </aside>
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

          <div class="overall-availability-check">
            <h3>{{ uiText.checkAvailability }}</h3>
            <div class="availability-intro">
              <h4>{{ uiText.selectEventDate }}</h4>
              <p>{{ uiText.pickDateText }}</p>
            </div>
            <label class="availability-date-field">
              <span>{{ uiText.eventDate }}</span>
              <input
                type="date"
                :value="overallAvailabilityDate"
                :min="`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`"
                @input="overallDateChanged"
              />
            </label>

            <div class="availability-legend">
              <span><i class="availability-dot availability-dot-available"></i>{{ uiText.available }}</span>
              <span><i class="availability-dot availability-dot-booked"></i>{{ uiText.booked }}</span>
              <span><i class="availability-dot availability-dot-selected"></i>{{ uiText.selected }}</span>
            </div>

            <div class="availability-time-head">
              <h4>{{ uiText.availableTimeSlots }}</h4>
              <span>{{ selectedAvailabilityDateLabel }}</span>
            </div>
            <div
              v-for="group in availabilitySlotGroups"
              :key="group.key"
              class="availability-period"
            >
              <p class="availability-period-title">
                {{ group.label }}
              </p>
              <div class="availability-slot-grid">
                <button
                  v-for="slot in group.items"
                  :key="slot.value"
                  type="button"
                  class="availability-slot-btn"
                  :class="{
                    selected: overallAvailabilitySlot === slot.value && !slot.booked,
                    booked: slot.booked,
                  }"
                  :disabled="slot.booked"
                  @click="selectOverallSlot(slot.value)"
                >
                  {{ slot.booked ? uiText.booked : slot.value }}
                </button>
              </div>
            </div>

            <div class="availability-selection">
              <span>{{ uiText.selectedDate }}</span>
              <strong>{{ selectedAvailabilityDateLabel }}</strong>
            </div>
            <div
              class="availability-state"
              :class="{
                available: overallAvailabilityState.tone === 'available',
                booked: overallAvailabilityState.tone === 'booked',
              }"
            >
              {{ overallAvailabilityState.label }}
            </div>
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
                <div>
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
                <div>
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
        :notice="''"
        :is-loading-bookings="false"
        :filtered-bookings="[]"
        :go-to-dashboard="() => goToSection('dashboard')"
        :go-to-vendor="() => goToSection('services-packages')"
        :go-to-messages="goToSignIn"
        :go-to-profile="goToSignIn"
        :booking-secondary-action="noop"
        :booking-primary-action="noop"
      />
    </main>

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
            @click="goToVendor"
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
      v-if="showPrebookModal"
      class="prebook-overlay"
      @click="closePrebookModal"
    >
      <div class="prebook-modal" @click.stop>
        <div class="prebook-head">
          <h3>Book {{ prebookTargetTitle }}</h3>
          <button type="button" class="prebook-close" @click="closePrebookModal">
            x
          </button>
        </div>

        <form class="prebook-form" @submit.prevent="submitPrebookForm">
          <label>
            <span>Full name</span>
            <input v-model.trim="prebookForm.fullName" type="text" required />
          </label>

          <label>
            <span>Email</span>
            <input v-model.trim="prebookForm.email" type="email" required />
          </label>

          <label>
            <span>Phone number</span>
            <input
              v-model.trim="prebookForm.phone"
              type="tel"
              required
              placeholder="+1 555 234 5678"
            />
          </label>

          <label>
            <span>Location</span>
            <input
              v-model.trim="prebookForm.location"
              type="text"
              required
              placeholder="City / Venue address"
              @input="scheduleTypedPrebookLocationResolve"
            />
          </label>
          <div class="prebook-location-tools">
            <button
              type="button"
              class="modal-action-btn modal-action-neutral"
              :disabled="isDetectingPrebookLocation || isResolvingTypedPrebookLocation"
              @click="detectPrebookLocation"
            >
              {{ isDetectingPrebookLocation ? "Detecting location..." : "Use Current Location" }}
            </button>
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
            <a
              v-if="prebookLocationMapLinkUrl"
              class="prebook-map-link"
              :href="prebookLocationMapLinkUrl"
              target="_blank"
              rel="noopener noreferrer"
            >
              Open current location in map
            </a>
          </div>

          <label>
            <span>Event date</span>
            <div class="prebook-date-picker">
              <button type="button" class="prebook-date-trigger" @click="openPrebookCalendar">
                <span>{{ prebookForm.eventDate || "Select event date" }}</span>
                <span class="prebook-date-icon">[ ]</span>
              </button>

              <div v-if="showPrebookCalendar" class="prebook-calendar">
                <div class="prebook-calendar-head">
                  <div>
                    <strong>{{ prebookCalendarMonthLabel }}</strong>
                    <small>{{ prebookCalendarSelectedLabel }}</small>
                  </div>
                  <div class="prebook-calendar-nav">
                    <button type="button" @click="previousPrebookCalendarMonth">&lt;</button>
                    <button type="button" @click="nextPrebookCalendarMonth">&gt;</button>
                    <button type="button" class="prebook-calendar-close" @click="closePrebookCalendar">x</button>
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
                      available: cell.available && cell.inMonth,
                      selected: isPrebookCalendarCellSelected(cell),
                    }"
                    :disabled="!cell.inMonth || cell.disabled || cell.booked"
                    @click="selectPrebookCalendarDate(cell)"
                  >
                    {{ cell.day || '' }}
                  </button>
                </div>

                <div class="prebook-calendar-legend">
                  <span><i class="dot available"></i> Available</span>
                  <span><i class="dot booked"></i> Booked</span>
                  <span><i class="dot selected"></i> Selected</span>
                </div>
                <p v-if="isLoadingPrebookCalendar" class="prebook-calendar-loading">Loading date availability...</p>
              </div>
            </div>
          </label>

          <div
            class="prebook-availability-state"
            :class="{
              available: prebookAvailabilityTone === 'available',
              booked: prebookAvailabilityTone === 'booked',
            }"
          >
            {{ prebookAvailabilityLabel }}
          </div>

          <label>
            <span>Number of guests</span>
            <input v-model.number="prebookForm.guests" type="number" min="1" required />
          </label>

          <label>
            <span>Notes</span>
            <textarea
              v-model.trim="prebookForm.notes"
              rows="3"
              placeholder="Add preferences or requests"
            ></textarea>
          </label>

          <p v-if="prebookSuccess" class="prebook-success">{{ prebookSuccess }}</p>

          <div class="prebook-actions">
            <button
              type="submit"
              class="modal-action-btn modal-action-primary"
              :disabled="activePrebookEventId && !canSubmitPrebook"
            >
              {{ isCheckingPrebookAvailability ? "Checking..." : "Confirm Booking" }}
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
  grid-template-columns: 280px minmax(0, 1fr) 170px;
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
  grid-template-columns: 1fr 400px;
  gap: 16px;
  align-items: start;
}

.overall-list {
  max-height: calc(100vh - 120px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.overall-summary {
  position: sticky;
  top: 12px;
  max-height: calc(100vh - 24px);
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

.overall-availability-check {
  margin-top: 14px;
  border-top: 1px solid #e3e9f2;
  padding-top: 12px;
  display: grid;
  gap: 10px;
}

.overall-availability-check h3 {
  margin: 0;
  font-size: 17px;
}

.availability-intro h4 {
  margin: 0;
  font-size: 20px;
  color: #0f172a;
}

.availability-intro p {
  margin: 4px 0 0;
  color: #9a4b2f;
  font-size: 14px;
}

.availability-date-field {
  display: grid;
  gap: 6px;
}

.availability-date-field span {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
}

.availability-date-field input {
  width: 100%;
  border: 1px solid #d7e4f3;
  border-radius: 10px;
  background: #fff;
  padding: 10px 12px;
  font: inherit;
}

.availability-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  align-items: center;
  padding-top: 2px;
}

.availability-legend span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #7c3f2b;
  font-size: 13px;
  font-weight: 700;
}

.availability-dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  display: inline-block;
}

.availability-dot-available {
  background: #22c55e;
}

.availability-dot-booked {
  background: #cbd5e1;
}

.availability-dot-selected {
  background: #f97316;
}

.availability-time-head {
  margin-top: 4px;
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
}

.availability-time-head h4 {
  margin: 0;
  font-size: 18px;
  color: #0f172a;
}

.availability-time-head span {
  color: #ea580c;
  font-size: 14px;
  font-weight: 700;
}

.availability-period {
  display: grid;
  gap: 8px;
}

.availability-period-title {
  margin: 0;
  color: #9a4b2f;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.availability-slot-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 8px;
}

.availability-slot-btn {
  border: 1px solid #ffd4bc;
  border-radius: 10px;
  background: #fff;
  color: #374151;
  font-size: 13px;
  font-weight: 700;
  padding: 10px 8px;
  cursor: pointer;
}

.availability-slot-btn.selected {
  border-color: #f97316;
  background: #f97316;
  color: #fff;
}

.availability-slot-btn.booked {
  border-color: #e2e8f0;
  background: #f8fafc;
  color: #94a3b8;
  cursor: not-allowed;
}

.availability-state {
  border-radius: 10px;
  border: 1px solid #dbe4f1;
  background: #f8fbff;
  color: #475569;
  font-size: 13px;
  font-weight: 700;
  padding: 8px 10px;
}

.availability-selection {
  border: 1px solid #fee2d3;
  background: #fff7f2;
  border-radius: 10px;
  padding: 8px 10px;
  display: grid;
  gap: 2px;
}

.availability-selection span {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  color: #9a4b2f;
  font-weight: 700;
}

.availability-selection strong {
  color: #0f172a;
  font-size: 14px;
}

.availability-state.available {
  border-color: #bbf7d0;
  background: #f0fdf4;
  color: #166534;
}

.availability-state.booked {
  border-color: #fecaca;
  background: #fef2f2;
  color: #991b1b;
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
  padding: 18px 18px 16px;
  margin-bottom: 10px;
}

.package-head-main h1 {
  margin: 0;
  font-size: clamp(2rem, 3vw, 3rem);
  line-height: 1.08;
}

.package-head-main p {
  margin: 10px 0 0;
  color: #64748b;
  max-width: 820px;
}

.package-toolbar {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 260px minmax(0, 1fr) 170px;
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
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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
  background: linear-gradient(180deg, #fff, #fcfdff);
  overflow: hidden;
  scroll-margin-top: 120px;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
}

.package-product-card:hover {
  transform: translateY(-2px);
  border-color: #c6d5ea;
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.1);
}

.package-product-card img {
  width: 100%;
  height: 172px;
  object-fit: cover;
}

.package-product-body {
  padding: 13px;
  display: grid;
  gap: 7px;
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
  line-height: 1.25;
}

.package-product-desc {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.45;
}

.package-product-footer {
  margin-top: 4px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 10px;
}

.package-product-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
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
  width: auto;
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
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 14px;
  overflow-y: auto;
}

.prebook-modal {
  width: min(680px, 100%);
  max-height: calc(100dvh - 28px);
  margin: auto 0;
  display: flex;
  flex-direction: column;
  background: #fff;
  border-radius: 16px;
  border: 1px solid #dbe4f2;
  box-shadow: 0 26px 56px rgba(15, 23, 42, 0.24);
  overflow: hidden;
}

.prebook-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 18px;
  border-bottom: 1px solid #e2e8f3;
}

.prebook-head h3 {
  margin: 0;
  font-size: 22px;
}

.prebook-close {
  width: 38px;
  height: 38px;
  border: 1px solid #d7e1ee;
  border-radius: 10px;
  background: #fff;
  font-size: 18px;
  color: #334155;
  cursor: pointer;
}

.prebook-form {
  padding: 16px 18px 18px;
  display: grid;
  gap: 10px;
  overflow-y: auto;
}

.prebook-form label {
  display: grid;
  gap: 6px;
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
  border: 1px solid #d7e1ee;
  border-radius: 12px;
  background: #fff;
  color: #0f172a;
  font: inherit;
  padding: 12px 14px;
  cursor: pointer;
}

.prebook-date-trigger span:first-child {
  text-align: left;
}

.prebook-date-icon {
  color: #64748b;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.prebook-calendar {
  margin-top: 10px;
  border: 1px solid #dbe4f2;
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
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
  color: #64748b;
  font-size: 14px;
}

.prebook-calendar-nav {
  display: flex;
  gap: 8px;
}

.prebook-calendar-nav button {
  width: 40px;
  height: 40px;
  border: 1px solid #d7e1ee;
  border-radius: 12px;
  background: #fff;
  color: #334155;
  font: inherit;
  font-size: 18px;
  cursor: pointer;
}

.prebook-calendar-close {
  font-size: 14px !important;
  font-weight: 800;
  text-transform: uppercase;
}

.prebook-calendar-weekdays {
  display: grid;
  grid-template-columns: repeat(7, minmax(0, 1fr));
  gap: 8px;
}

.prebook-calendar-weekdays span {
  text-align: center;
  color: #b07a56;
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
  border: 1px solid #d7e1ee;
  border-radius: 14px;
  background: #fff;
  color: #1f2937;
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
  border-color: #ff8a3d;
  background: #ff7a12;
  box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.85);
  color: #fff;
}

.prebook-calendar-day.available {
  border-color: #d7e1ee;
  box-shadow: inset 0 -3px 0 #22c55e;
}

.prebook-calendar-day.booked {
  background: #f8fafc;
  color: #94a3b8;
  box-shadow: inset 0 -3px 0 #cbd5e1;
}

.prebook-calendar-legend {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  color: #64748b;
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
  background: #cbd5e1;
}

.prebook-calendar-legend .dot.selected {
  background: #f97316;
}

.prebook-calendar-loading {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.prebook-availability-state {
  border-radius: 12px;
  border: 1px solid #dbe4f1;
  background: #f8fbff;
  color: #475569;
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
  gap: 8px;
}

.prebook-location-notice,
.prebook-location-coords {
  margin: 0;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
}

.prebook-map-frame {
  width: 100%;
  height: 220px;
  border: 1px solid #d6dfec;
  border-radius: 12px;
}

.prebook-map-link {
  display: inline-block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #c25c13;
  text-decoration: none;
}

.prebook-map-link:hover {
  text-decoration: underline;
}

.prebook-form span {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
}

.prebook-form input,
.prebook-form textarea {
  width: 100%;
  border: 1px solid #d7e1ef;
  border-radius: 12px;
  padding: 11px 12px;
  font: inherit;
  background: #fff;
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
  display: flex;
  justify-content: flex-end;
  margin-top: 4px;
  padding-top: 8px;
  background: #fff;
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
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.favorite-card {
  border: 1px solid #e2e8f3;
  border-radius: 14px;
  background: #fbfdff;
  padding: 12px;
}

.favorite-card h3 {
  margin: 0;
}

.favorite-list {
  list-style: none;
  margin: 10px 0 0;
  padding: 0;
  display: grid;
  gap: 8px;
}

.favorite-list li {
  border: 1px solid #e2e8f3;
  border-radius: 10px;
  padding: 9px 10px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.favorite-list strong {
  display: block;
  font-size: 14px;
}

.favorite-list small {
  color: #64748b;
}

.favorite-remove {
  border: 1px solid #d6e0ef;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 8px;
  cursor: pointer;
}

.favorite-remove:hover {
  background: #f8fafc;
}

.favorite-booking-card {
  margin-top: 12px;
}

.favorite-booking-grid {
  margin-top: 10px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 160px;
  gap: 10px;
}

.favorite-booking-grid input {
  width: 100%;
  border: 1px solid #d7e4f3;
  border-radius: 10px;
  background: #fff;
  padding: 9px 10px;
  font: inherit;
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
    gap: 8px;
  }

  .prebook-form input,
  .prebook-form textarea {
    padding: 10px 11px;
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

  .availability-slot-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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
}

/* Packages page layout with right-hand booking summary */
.package-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
  align-items: start;
}

.package-layout > .package-head {
  grid-column: 1 / -1;
}

.package-layout-main {
  min-width: 0;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.package-summary {
  position: sticky;
  top: 12px;
  max-height: calc(100vh - 24px);
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



