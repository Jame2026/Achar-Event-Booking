<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { eventTypeMap } from "../../features/appData";
import { formatDateTime, summarizeBookedServices } from "../../features/bookingMappers";
import { apiGet, apiPatch, apiPost } from "../../features/apiClient";
import { useLanguageCopy } from "../../features/language";

const props = defineProps({
  appLogoSrc: { type: String, default: "" },
  adminDisplayName: { type: String, default: "Admin" },
  adminUserId: { type: [Number, String], default: null },
  logoutUser: { type: Function, default: null },
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
    brandSubtitle: "Customer relationship workspace",
    navigation: "Navigation",
    adminModules: "Admin modules",
    searchPlaceholder: "Search customer names, email, phone, or booked services...",
    notifications: "Notifications",
    heroEyebrow: "Customer Directory",
    heroTitle: "All Customers and Their Bookings",
    heroSubtitle: "Review registered customer accounts and inspect the services or packages they have booked across your system.",
    totalCustomersText: "{count} total customers",
    totalBookingsText: "{count} total bookings",
    selectedCustomer: "Selected Customer",
    customerSelectedSummary: "{count} booking(s) - {date}",
    totalCustomers: "Total Customers",
    shownHere: "{count} shown here",
    activeBookers: "Active Bookers",
    customersWithBookings: "Customers with bookings",
    bookingsInSystem: "Bookings In System",
    acrossServicesPackages: "Across services and packages",
    confirmedRevenue: "Confirmed Revenue",
    fromConfirmedBookings: "From confirmed bookings",
    directoryEyebrow: "Customer Directory",
    allCustomers: "All Customers",
    results: "{count} results",
    activity: "Activity",
    allCustomersFilter: "All Customers",
    withBookings: "With Bookings",
    repeatCustomers: "Repeat Customers",
    noBookingsYetFilter: "No Bookings Yet",
    bookingStatus: "Booking Status",
    anyStatus: "Any Status",
    confirmed: "Confirmed",
    pending: "Pending",
    cancelled: "Cancelled",
    loadingCustomerDirectory: "Loading customer directory...",
    noCustomersMatch: "No customers match your filters.",
    emailNotProvided: "Email not provided",
    noCategoryYet: "No category yet",
    bookingCount: "{count} booking(s)",
    customerProfile: "Customer Profile",
    confirmedCount: "{count} confirmed",
    pendingCount: "{count} pending",
    bookings: "Bookings",
    totalSpend: "Total Spend",
    email: "Email",
    phone: "Phone",
    location: "Location",
    joined: "Joined",
    preferredCategories: "Preferred Categories",
    noBookingsYet: "No bookings yet",
    bookingHistory: "Booking History",
    customerBookings: "Customer Bookings",
    noServicePackageYet: "This customer has not booked any service or package yet.",
    qty: "Qty {count}",
    selectCustomer: "Select a Customer",
    selectCustomerSubtitle: "Choose a customer from the directory to inspect their profile and booking history here.",
    notProvided: "Not provided",
    timeTbd: "Time TBD",
    allDay: "All day",
    unknown: "Unknown",
    customerFallback: "Customer",
    vendorFallback: "Vendor",
    serviceBooking: "Service Booking",
    package: "Package",
    service: "Service",
    other: "Other",
    locationMissing: "Location not added yet",
    joinDateUnavailable: "Join date unavailable",
    newMember: "New",
    repeatMember: "Repeat",
    activeMember: "Active",
    unpaid: "Unpaid",
    paid: "Paid",
    refunded: "Refunded",
    failed: "Failed",
    adminMissing: "Admin account is missing. Please sign in again.",
    noCustomerAccounts: "No customer accounts found yet.",
    couldNotLoadCustomerDirectory: "Could not load customer directory.",
    eventTypes: {
      wedding: "Wedding",
      monk_ceremony: "Monk Ceremony",
      house_blessing: "House Blessing",
      company_party: "Company Party",
      birthday: "Birthday",
      school_event: "School Event",
      engagement: "Engagement",
      anniversary: "Anniversary",
      baby_shower: "Baby Shower",
      graduation: "Graduation",
      festival: "Festival",
      other: "Other",
    },
  },
  zh: {
    nav: {
      dashboard: "ä»ªè¡¨ç›˜",
      events: "æ´»åŠ¨",
      bookings: "é¢„è®¢",
      vendors: "å•†å®¶",
      customers: "å®¢æˆ·",
      revenue: "æ”¶å…¥",
      settings: "è®¾ç½®",
    },
    brandKicker: "è¿è¥æŽ§åˆ¶å°",
    brandTitle: "Achar Admin",
    brandSubtitle: "å®¢æˆ·å…³ç³»å·¥ä½œåŒº",
    navigation: "å¯¼èˆª",
    adminModules: "ç®¡ç†å‘˜æ¨¡å—",
    searchPlaceholder: "æœç´¢å®¢æˆ·å§“åã€é‚®ç®±ã€ç”µè¯æˆ–å·²é¢„è®¢æœåŠ¡...",
    notifications: "é€šçŸ¥",
    heroEyebrow: "å®¢æˆ·ç›®å½•",
    heroTitle: "æ‰€æœ‰å®¢æˆ·åŠå…¶é¢„è®¢",
    heroSubtitle: "æŸ¥çœ‹å·²æ³¨å†Œå®¢æˆ·è´¦æˆ·ï¼Œå¹¶æ£€æŸ¥ä»–ä»¬åœ¨ç³»ç»Ÿä¸­é¢„è®¢çš„æœåŠ¡æˆ–å¥—é¤ã€‚",
    totalCustomersText: "{count} ä½å®¢æˆ·",
    totalBookingsText: "{count} æ¡é¢„è®¢",
    selectedCustomer: "å·²é€‰å®¢æˆ·",
    customerSelectedSummary: "{count} æ¡é¢„è®¢ - {date}",
    totalCustomers: "å®¢æˆ·æ€»æ•°",
    shownHere: "å½“å‰æ˜¾ç¤º {count} ä½",
    activeBookers: "æ´»è·ƒé¢„è®¢å®¢æˆ·",
    customersWithBookings: "æœ‰é¢„è®¢çš„å®¢æˆ·",
    bookingsInSystem: "ç³»ç»Ÿä¸­çš„é¢„è®¢",
    acrossServicesPackages: "æ¶µç›–æœåŠ¡ä¸Žå¥—é¤",
    confirmedRevenue: "å·²ç¡®è®¤æ”¶å…¥",
    fromConfirmedBookings: "æ¥è‡ªå·²ç¡®è®¤é¢„è®¢",
    directoryEyebrow: "å®¢æˆ·ç›®å½•",
    allCustomers: "æ‰€æœ‰å®¢æˆ·",
    results: "{count} æ¡ç»“æžœ",
    activity: "æ´»è·ƒåº¦",
    allCustomersFilter: "å…¨éƒ¨å®¢æˆ·",
    withBookings: "æœ‰é¢„è®¢",
    repeatCustomers: "å›žå¤´å®¢",
    noBookingsYetFilter: "å°šæ— é¢„è®¢",
    bookingStatus: "é¢„è®¢çŠ¶æ€",
    anyStatus: "ä»»æ„çŠ¶æ€",
    confirmed: "å·²ç¡®è®¤",
    pending: "å¾…å¤„ç†",
    cancelled: "å·²å–æ¶ˆ",
    loadingCustomerDirectory: "æ­£åœ¨åŠ è½½å®¢æˆ·ç›®å½•...",
    noCustomersMatch: "æ²¡æœ‰ç¬¦åˆç­›é€‰æ¡ä»¶çš„å®¢æˆ·ã€‚",
    emailNotProvided: "æœªæä¾›é‚®ç®±",
    noCategoryYet: "æš‚æ— åˆ†ç±»",
    bookingCount: "{count} æ¡é¢„è®¢",
    customerProfile: "å®¢æˆ·èµ„æ–™",
    confirmedCount: "{count} å·²ç¡®è®¤",
    pendingCount: "{count} å¾…å¤„ç†",
    bookings: "é¢„è®¢",
    totalSpend: "æ€»æ¶ˆè´¹",
    email: "é‚®ç®±",
    phone: "ç”µè¯",
    location: "ä½ç½®",
    joined: "åŠ å…¥æ—¶é—´",
    preferredCategories: "åå¥½åˆ†ç±»",
    noBookingsYet: "æš‚æ— é¢„è®¢",
    bookingHistory: "é¢„è®¢è®°å½•",
    customerBookings: "å®¢æˆ·é¢„è®¢",
    noServicePackageYet: "è¯¥å®¢æˆ·å°šæœªé¢„è®¢ä»»ä½•æœåŠ¡æˆ–å¥—é¤ã€‚",
    qty: "æ•°é‡ {count}",
    selectCustomer: "é€‰æ‹©å®¢æˆ·",
    selectCustomerSubtitle: "ä»Žç›®å½•ä¸­é€‰æ‹©ä¸€ä½å®¢æˆ·ï¼Œä»¥åœ¨æ­¤æŸ¥çœ‹å…¶èµ„æ–™å’Œé¢„è®¢è®°å½•ã€‚",
    notProvided: "æœªæä¾›",
    timeTbd: "æ—¶é—´å¾…å®š",
    allDay: "å…¨å¤©",
    unknown: "æœªçŸ¥",
    customerFallback: "å®¢æˆ·",
    vendorFallback: "å•†å®¶",
    serviceBooking: "æœåŠ¡é¢„è®¢",
    package: "å¥—é¤",
    service: "æœåŠ¡",
    other: "å…¶ä»–",
    locationMissing: "ä½ç½®å°šæœªæ·»åŠ ",
    joinDateUnavailable: "åŠ å…¥æ—¥æœŸä¸å¯ç”¨",
    newMember: "æ–°å®¢æˆ·",
    repeatMember: "å›žå¤´å®¢",
    activeMember: "æ´»è·ƒ",
    unpaid: "æœªæ”¯ä»˜",
    paid: "å·²æ”¯ä»˜",
    refunded: "å·²é€€æ¬¾",
    failed: "å¤±è´¥",
    adminMissing: "ç®¡ç†å‘˜è´¦æˆ·ç¼ºå¤±ï¼Œè¯·é‡æ–°ç™»å½•ã€‚",
    noCustomerAccounts: "æš‚æ— å®¢æˆ·è´¦æˆ·ã€‚",
    couldNotLoadCustomerDirectory: "æ— æ³•åŠ è½½å®¢æˆ·ç›®å½•ã€‚",
    eventTypes: {
      wedding: "å©šç¤¼",
      monk_ceremony: "åƒ§ä¾£ä»ªå¼",
      house_blessing: "ä½å®…ç¥ˆç¦",
      company_party: "å…¬å¸æ´¾å¯¹",
      birthday: "ç”Ÿæ—¥",
      school_event: "å­¦æ ¡æ´»åŠ¨",
      engagement: "è®¢å©š",
      anniversary: "å‘¨å¹´çºªå¿µ",
      baby_shower: "è¿Žå©´æ´¾å¯¹",
      graduation: "æ¯•ä¸šå…¸ç¤¼",
      festival: "èŠ‚åº†",
      other: "å…¶ä»–",
    },
  },
};
copyByLanguage.km = {
  ...copyByLanguage.en,
  nav: {
    dashboard: "áž•áŸ’áž‘áž¶áŸ†áž„áž‚áŸ’ážšáž”áŸ‹áž‚áŸ’ážšáž„",
    events: "áž–áŸ’ážšáž¹ážáŸ’ážáž·áž€áž¶ážšážŽáŸ",
    bookings: "áž€áž¶ážšáž€áž€áŸ‹",
    vendors: "áž¢áŸ’áž“áž€áž•áŸ’áž‚ážáŸ‹áž•áŸ’áž‚áž„áŸ‹",
    customers: "áž¢ážáž·ážáž·áž‡áž“",
    revenue: "áž…áŸ†ážŽáž¼áž›",
    settings: "áž€áž¶ážšáž€áŸ†ážŽážáŸ‹",
  },
  brandKicker: "áž•áŸ’áž‘áž¶áŸ†áž„áž”áŸ’ážšážáž·áž”ážáŸ’ážáž·áž€áž¶ážš",
  brandTitle: "Achar Admin",
  brandSubtitle: "áž€áž“áŸ’áž›áŸ‚áž„áž’áŸ’ážœáž¾áž€áž¶ážšáž‘áŸ†áž“áž¶áž€áŸ‹áž‘áŸ†áž“áž„áž¢ážáž·ážáž·áž‡áž“",
  navigation: "áž€áž¶ážšážšáž»áž€ážšáž€",
  adminModules: "áž˜áž»ážáž„áž¶ážšáž¢áŸ’áž“áž€áž‚áŸ’ážšáž”áŸ‹áž‚áŸ’ážšáž„",
  searchPlaceholder: "ážŸáŸ’ážœáŸ‚áž„ážšáž€ážˆáŸ’áž˜áŸ„áŸ‡áž¢ážáž·ážáž·áž‡áž“ áž¢áŸŠáž¸áž˜áŸ‚áž› áž‘áž¼ážšážŸáŸáž–áŸ’áž‘ áž¬ážŸáŸážœáž¶ážŠáŸ‚áž›áž”áž¶áž“áž€áž€áŸ‹...",
  notifications: "áž€áž¶ážšáž‡áž¼áž“ážŠáŸ†ážŽáž¹áž„",
  heroEyebrow: "áž”áž‰áŸ’áž‡áž¸áž¢ážáž·ážáž·áž‡áž“",
  heroTitle: "áž¢ážáž·ážáž·áž‡áž“áž‘áž¶áŸ†áž„áž¢ážŸáŸ‹ áž“áž·áž„áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹áž–áž½áž€áž‚áŸ",
  heroSubtitle: "áž–áž·áž“áž·ážáŸ’áž™áž‚ážŽáž“áž¸áž¢ážáž·ážáž·áž‡áž“ážŠáŸ‚áž›áž”áž¶áž“áž…áž»áŸ‡ážˆáŸ’áž˜áŸ„áŸ‡ áž“áž·áž„ážŸáŸážœáž¶ áž¬áž€áž‰áŸ’áž…áž”áŸ‹ážŠáŸ‚áž›áž–áž½áž€áž‚áŸáž”áž¶áž“áž€áž€áŸ‹áž“áŸ…áž€áŸ’áž“áž»áž„áž”áŸ’ážšáž–áŸáž“áŸ’áž’áŸ”",
  totalCustomersText: "áž¢ážáž·ážáž·áž‡áž“ážŸážšáž»áž” {count}",
  totalBookingsText: "áž€áž¶ážšáž€áž€áŸ‹ážŸážšáž»áž” {count}",
  selectedCustomer: "áž¢ážáž·ážáž·áž‡áž“ážŠáŸ‚áž›áž”áž¶áž“áž‡áŸ’ážšáž¾ážŸ",
  customerSelectedSummary: "áž€áž¶ážšáž€áž€áŸ‹ {count} - {date}",
  totalCustomers: "áž¢ážáž·ážáž·áž‡áž“ážŸážšáž»áž”",
  shownHere: "áž”áž„áŸ’áž áž¶áž‰áž“áŸ…áž‘áž¸áž“áŸáŸ‡ {count}",
  activeBookers: "áž¢ážáž·ážáž·áž‡áž“ážŠáŸ‚áž›áž€áŸ†áž–áž»áž„áž€áž€áŸ‹",
  customersWithBookings: "áž¢ážáž·ážáž·áž‡áž“ážŠáŸ‚áž›áž˜áž¶áž“áž€áž¶ážšáž€áž€áŸ‹",
  bookingsInSystem: "áž€áž¶ážšáž€áž€áŸ‹áž“áŸ…áž€áŸ’áž“áž»áž„áž”áŸ’ážšáž–áŸáž“áŸ’áž’",
  acrossServicesPackages: "áž“áŸ…áž‘áž¼áž‘áž¶áŸ†áž„ážŸáŸážœáž¶ áž“áž·áž„áž€áž‰áŸ’áž…áž”áŸ‹",
  confirmedRevenue: "áž…áŸ†ážŽáž¼áž›ážŠáŸ‚áž›áž”áž¶áž“áž”áž‰áŸ’áž‡áž¶áž€áŸ‹",
  fromConfirmedBookings: "áž–áž¸áž€áž¶ážšáž€áž€áŸ‹ážŠáŸ‚áž›áž”áž¶áž“áž”áž‰áŸ’áž‡áž¶áž€áŸ‹",
  directoryEyebrow: "áž”áž‰áŸ’áž‡áž¸áž¢ážáž·ážáž·áž‡áž“",
  allCustomers: "áž¢ážáž·ážáž·áž‡áž“áž‘áž¶áŸ†áž„áž¢ážŸáŸ‹",
  results: "áž›áž‘áŸ’áž’áž•áž› {count}",
  activity: "ážŸáž€áž˜áŸ’áž˜áž—áž¶áž–",
  allCustomersFilter: "áž¢ážáž·ážáž·áž‡áž“áž‘áž¶áŸ†áž„áž¢ážŸáŸ‹",
  withBookings: "áž˜áž¶áž“áž€áž¶ážšáž€áž€áŸ‹",
  repeatCustomers: "áž¢ážáž·ážáž·áž‡áž“ážáŸ’ážšáž¡áž”áŸ‹áž˜áž€ážœáž·áž‰",
  noBookingsYetFilter: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž˜áž¶áž“áž€áž¶ážšáž€áž€áŸ‹",
  bookingStatus: "ážŸáŸ’ážáž¶áž“áž—áž¶áž–áž€áž¶ážšáž€áž€áŸ‹",
  anyStatus: "ážŸáŸ’ážáž¶áž“áž—áž¶áž–ážŽáž¶áž˜áž½áž™",
  confirmed: "áž”áž¶áž“áž”áž‰áŸ’áž‡áž¶áž€áŸ‹",
  pending: "ážšáž„áŸ‹áž…áž¶áŸ†",
  cancelled: "áž”áž¶áž“áž”áŸ„áŸ‡áž”áž„áŸ‹",
  loadingCustomerDirectory: "áž€áŸ†áž–áž»áž„áž•áŸ’áž‘áž»áž€áž”áž‰áŸ’áž‡áž¸áž¢ážáž·ážáž·áž‡áž“...",
  noCustomersMatch: "áž˜áž·áž“áž˜áž¶áž“áž¢ážáž·ážáž·áž‡áž“ážáŸ’ážšáž¼ážœáž“áž¹áž„ážáž˜áŸ’ážšáž„ážšáž”ážŸáŸ‹áž¢áŸ’áž“áž€áž‘áŸáŸ”",
  emailNotProvided: "áž˜áž·áž“áž”áž¶áž“áž•áŸ’ážáž›áŸ‹áž¢áŸŠáž¸áž˜áŸ‚áž›",
  noCategoryYet: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž˜áž¶áž“áž”áŸ’ážšáž—áŸáž‘",
  bookingCount: "áž€áž¶ážšáž€áž€áŸ‹ {count}",
  customerProfile: "áž”áŸ’ážšážœážáŸ’ážáž·ážšáž¼áž”áž¢ážáž·ážáž·áž‡áž“",
  confirmedCount: "{count} áž”áž¶áž“áž”áž‰áŸ’áž‡áž¶áž€áŸ‹",
  pendingCount: "{count} ážšáž„áŸ‹áž…áž¶áŸ†",
  bookings: "áž€áž¶ážšáž€áž€áŸ‹",
  totalSpend: "áž…áŸ†ážŽáž¶áž™ážŸážšáž»áž”",
  email: "áž¢áŸŠáž¸áž˜áŸ‚áž›",
  phone: "áž‘áž¼ážšážŸáŸáž–áŸ’áž‘",
  location: "áž‘áž¸ážáž¶áŸ†áž„",
  joined: "áž”áž¶áž“áž…áž¼áž›",
  preferredCategories: "áž”áŸ’ážšáž—áŸáž‘ážŠáŸ‚áž›áž–áŸáž‰áž…áž·ážáŸ’áž",
  noBookingsYet: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž˜áž¶áž“áž€áž¶ážšáž€áž€áŸ‹",
  bookingHistory: "áž”áŸ’ážšážœážáŸ’ážáž·áž€áž¶ážšáž€áž€áŸ‹",
  customerBookings: "áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹áž¢ážáž·ážáž·áž‡áž“",
  noServicePackageYet: "áž¢ážáž·ážáž·áž‡áž“áž“áŸáŸ‡áž˜áž·áž“áž‘áž¶áž“áŸ‹áž”áž¶áž“áž€áž€áŸ‹ážŸáŸážœáž¶ áž¬áž€áž‰áŸ’áž…áž”áŸ‹ážŽáž¶áž˜áž½áž™áž‘áŸáŸ”",
  qty: "áž…áŸ†áž“áž½áž“ {count}",
  selectCustomer: "áž‡áŸ’ážšáž¾ážŸáž¢ážáž·ážáž·áž‡áž“",
  selectCustomerSubtitle: "áž‡áŸ’ážšáž¾ážŸáž¢ážáž·ážáž·áž‡áž“áž˜áŸ’áž“áž¶áž€áŸ‹áž–áž¸áž”áž‰áŸ’áž‡áž¸ ážŠáž¾áž˜áŸ’áž”áž¸áž–áž·áž“áž·ážáŸ’áž™áž”áŸ’ážšážœážáŸ’ážáž·ážšáž¼áž” áž“áž·áž„áž”áŸ’ážšážœážáŸ’ážáž·áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹áž–áž½áž€áž‚áŸáž“áŸ…áž‘áž¸áž“áŸáŸ‡áŸ”",
  notProvided: "áž˜áž·áž“áž”áž¶áž“áž•áŸ’ážáž›áŸ‹",
  timeTbd: "áž˜áŸ‰áŸ„áž„áž˜áž·áž“áž‘áž¶áž“áŸ‹áž€áŸ†ážŽážáŸ‹",
  allDay: "áž–áŸáž‰áž˜áž½áž™ážáŸ’áž„áŸƒ",
  unknown: "áž˜áž·áž“ážŸáŸ’áž‚áž¶áž›áŸ‹",
  customerFallback: "áž¢ážáž·ážáž·áž‡áž“",
  vendorFallback: "áž¢áŸ’áž“áž€áž•áŸ’áž‚ážáŸ‹áž•áŸ’áž‚áž„áŸ‹",
  serviceBooking: "áž€áž¶ážšáž€áž€áŸ‹ážŸáŸážœáž¶",
  package: "áž€áž‰áŸ’áž…áž”áŸ‹",
  service: "ážŸáŸážœáž¶",
  other: "áž•áŸ’ážŸáŸáž„áŸ—",
  locationMissing: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž”áž“áŸ’ážáŸ‚áž˜áž‘áž¸ážáž¶áŸ†áž„",
  joinDateUnavailable: "áž˜áž·áž“áž˜áž¶áž“áž€áž¶áž›áž”ážšáž·áž…áŸ’áž†áŸáž‘áž…áž¼áž›",
  newMember: "ážáŸ’áž˜áž¸",
  repeatMember: "ážáŸ’ážšáž¡áž”áŸ‹áž˜áž€ážœáž·áž‰",
  activeMember: "ážŸáž€áž˜áŸ’áž˜",
  unpaid: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž”áž„áŸ‹",
  paid: "áž”áž¶áž“áž”áž„áŸ‹",
  refunded: "áž”áž¶áž“ážŸáž„áž”áŸ’ážšáž¶áž€áŸ‹ážœáž·áž‰",
  failed: "áž”ážšáž¶áž‡áŸáž™",
  adminMissing: "áž˜áž·áž“áž˜áž¶áž“áž‚ážŽáž“áž¸áž¢áŸ’áž“áž€áž‚áŸ’ážšáž”áŸ‹áž‚áŸ’ážšáž„ ážŸáž¼áž˜áž…áž¼áž›áž˜áŸ’ážáž„áž‘áŸ€ážáŸ”",
  noCustomerAccounts: "áž˜áž·áž“áž‘áž¶áž“áŸ‹áž˜áž¶áž“áž‚ážŽáž“áž¸áž¢ážáž·ážáž·áž‡áž“áŸ”",
  couldNotLoadCustomerDirectory: "áž˜áž·áž“áž¢áž¶áž…áž•áŸ’áž‘áž»áž€áž”áž‰áŸ’áž‡áž¸áž¢ážáž·ážáž·áž‡áž“áž”áž¶áž“áž‘áŸáŸ”",
  eventTypes: {
    wedding: "áž¢áž¶áž–áž¶áž áŸáž–áž·áž–áž¶áž áŸ",
    monk_ceremony: "áž–áž·áž’áž¸áž–áŸ’ážšáŸ‡ážŸáž„áŸ’ážƒ",
    house_blessing: "áž–áž·áž’áž¸áž¡áž¾áž„áž•áŸ’áž‘áŸ‡",
    company_party: "áž–áž·áž’áž¸áž‡áž”áŸ‹áž›áŸ€áž„áž€áŸ’ážšáž»áž˜áž áŸŠáž»áž“",
    birthday: "ážáž½áž”áž€áŸ†ážŽáž¾áž",
    school_event: "áž–áŸ’ážšáž¹ážáŸ’ážáž·áž€áž¶ážšážŽáŸážŸáž¶áž›áž¶",
    engagement: "áž–áž·áž’áž¸áž—áŸ’áž‡áž¶áž”áŸ‹áž–áž¶áž€áŸ’áž™",
    anniversary: "ážáž½áž”áž¢áž“áž»ážŸáŸ’ážŸáž¶ážœážšáž¸áž™áŸ",
    baby_shower: "áž–áž·áž’áž¸ážŸáŸ’ážœáž¶áž‚áž˜áž“áŸáž‘áž¶ážšáž€",
    graduation: "áž–áž·áž’áž¸áž”áž‰áŸ’áž…áž”áŸ‹áž€áž¶ážšážŸáž·áž€áŸ’ážŸáž¶",
    festival: "áž–áž·áž’áž¸áž”áž»ážŽáŸ’áž™",
    other: "áž•áŸ’ážŸáŸáž„áŸ—",
  },
};

const { language, uiText } = useLanguageCopy(copyByLanguage);
const activeLocale = computed(() => (language.value === "zh" ? "zh-CN" : language.value === "km" ? "km-KH" : "en-US"));
const interpolate = (template, values = {}) =>
  String(template || "").replace(/\{(\w+)\}/g, (_, key) => String(values[key] ?? ""));

const router = useRouter();
const route = useRoute();

const navItems = computed(() => [
  { key: "dashboard", label: uiText.value.nav.dashboard, icon: "dashboard" },
  { key: "events", label: uiText.value.nav.events, icon: "events" },
  { key: "admin-bookings", label: uiText.value.nav.bookings, icon: "bookings" },
  { key: "vendors", label: uiText.value.nav.vendors, icon: "vendors" },
  { key: "customers", label: uiText.value.nav.customers, icon: "users" },
  { key: "revenue", label: uiText.value.nav.revenue, icon: "revenue" },
  { key: "settings", label: uiText.value.nav.settings, icon: "settings" },
]);

const activeKey = ref("customers");
const searchQuery = ref("");
const activityFilter = ref("all");
const bookingStateFilter = ref("all");
const isLoading = ref(false);
const notice = ref("");
const noticeTone = ref("info");
const customers = ref([]);
const selectedCustomerKey = ref("");
const blacklistedCustomers = ref([]);
const deletingCustomerId = ref(null);
const approvingBlacklistId = ref(null);
const failedCustomerImages = ref(new Set());

function count(value) {
  return Number(value || 0).toLocaleString(activeLocale.value);
}

function money(value) {
  return new Intl.NumberFormat(activeLocale.value, {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(value || 0));
}

function shortName(value) {
  return String(value || uiText.value.customerFallback)
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
  if (!raw) return uiText.value.timeTbd;
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return uiText.value.allDay;

  const date = new Date(raw);
  if (Number.isNaN(date.getTime())) return uiText.value.timeTbd;

  return date.toLocaleTimeString(activeLocale.value, {
    hour: "numeric",
    minute: "2-digit",
  });
}

function formatBadgeLabel(value, fallback = uiText.value.unknown) {
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

function eventTypeLabel(value) {
  const normalized = String(value || "").trim().toLowerCase();
  return uiText.value.eventTypes?.[normalized] || eventTypeMap[normalized] || uiText.value.other;
}

const initials = computed(() => {
  const parts = String(props.adminDisplayName || "Admin").split(" ").filter(Boolean);
  return `${parts[0]?.[0] || "A"}${parts[1]?.[0] || ""}`.toUpperCase();
});

const customerRows = computed(() =>
  customers.value
    .map((customer) => {
      const customerName = String(customer?.name || uiText.value.customerFallback).trim() || uiText.value.customerFallback;
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
            vendorName: String(vendor?.name || uiText.value.vendorFallback).trim() || uiText.value.vendorFallback,
            vendorEmail: String(vendor?.email || "").trim(),
            serviceLabel: summarizeBookedServices(
              bookedItems,
              String(booking?.service_name || event?.title || uiText.value.serviceBooking).trim() || uiText.value.serviceBooking,
            ),
            bookingKind:
              String(event?.service_mode || "").trim().toLowerCase() === "package" || bookedItems.length > 1
                ? uiText.value.package
                : uiText.value.service,
            eventTypeLabel: eventTypeLabel(event?.event_type),
            quantity: Number(booking?.quantity || 1),
            location: String(event?.location || "").trim() || uiText.value.locationMissing,
            status,
            statusLabel: bookingStatusLabel(status),
            paymentStatusLabel: paymentStatusLabel(booking?.payment_status),
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
        location: String(customer?.location || "").trim() || uiText.value.locationMissing,
        profileImageUrl: String(customer?.profile_image_url || "").trim(),
        customerImageKey: customer?.id ? `customer:${customer.id}` : `customer:${customerName.toLowerCase()}`,
        joinedLabel: customer?.created_at ? formatDateTime(customer.created_at) : uiText.value.joinDateUnavailable,
        bookingsCount,
        confirmedCount,
        pendingCount,
        cancelledCount,
        confirmedSpend,
        confirmedSpendLabel: money(confirmedSpend),
        preferredTypes,
        bookingHistory,
        memberState: bookingsCount === 0 ? uiText.value.newMember : bookingsCount > 1 ? uiText.value.repeatMember : uiText.value.activeMember,
        lastBookingLabel: bookingHistory[0]?.dateLabel || uiText.value.noBookingsYet,
        lastBookingService: bookingHistory[0]?.serviceLabel || uiText.value.noBookingsYet,
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
const customerBlacklistRows = computed(() =>
  blacklistedCustomers.value
    .map((entry) => {
      const approvedAt = String(entry?.approved_at || "").trim();

      return {
        id: Number(entry?.id || 0),
        name: String(entry?.subject_name || uiText.value.customerFallback || "Customer").trim() || uiText.value.customerFallback || "Customer",
        email: String(entry?.blocked_email || "").trim(),
        phone: String(entry?.blocked_phone || "").trim(),
        reason: String(entry?.reason || "").trim() || "No blacklist note was added.",
        blacklistedAt: String(entry?.blacklisted_at || entry?.created_at || "").trim(),
        blacklistedAtLabel: entry?.blacklisted_at || entry?.created_at ? formatDateTime(entry?.blacklisted_at || entry?.created_at) : uiText.value.timeTbd,
        approvedAt,
        approvedAtLabel: approvedAt ? formatDateTime(approvedAt) : "",
        canApprove: !approvedAt,
        statusLabel: approvedAt ? "Approved" : "Blocked",
      };
    })
    .sort((left, right) => stamp(right.blacklistedAt) - stamp(left.blacklistedAt)),
);

const highlightCards = computed(() => [
  {
    label: uiText.value.totalCustomers,
    value: count(customerRows.value.length),
    note: interpolate(uiText.value.shownHere, { count: count(filteredCustomers.value.length) }),
  },
  {
    label: uiText.value.activeBookers,
    value: count(customerRows.value.filter((item) => item.bookingsCount > 0).length),
    note: uiText.value.customersWithBookings,
  },
  {
    label: uiText.value.bookingsInSystem,
    value: count(customerRows.value.reduce((sum, item) => sum + item.bookingsCount, 0)),
    note: uiText.value.acrossServicesPackages,
  },
  {
    label: uiText.value.confirmedRevenue,
    value: money(customerRows.value.reduce((sum, item) => sum + item.confirmedSpend, 0)),
    note: uiText.value.fromConfirmedBookings,
  },
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
    return setNotice(uiText.value.adminMissing, "error");
  }

  isLoading.value = true;
  notice.value = "";

  try {
    const [result, blacklistResult] = await Promise.all([
      apiGet("admin/customer-directory", {
        admin_user_id: props.adminUserId,
        per_page: 100,
        ts: Date.now(),
      }),
      apiGet("admin/blacklist", {
        admin_user_id: props.adminUserId,
        role: "user",
        per_page: 100,
        ts: Date.now(),
      }),
    ]);

    customers.value = Array.isArray(result?.data) ? result.data : [];
    blacklistedCustomers.value = Array.isArray(blacklistResult?.data) ? blacklistResult.data : [];
    failedCustomerImages.value = new Set();
    if (!customers.value.length) notice.value = uiText.value.noCustomerAccounts;
  } catch (error) {
    customers.value = [];
    blacklistedCustomers.value = [];
    setNotice(error?.message || uiText.value.couldNotLoadCustomerDirectory, "error");
  } finally {
    isLoading.value = false;
  }
}

async function deleteCustomerAndBlacklist(customer = selectedCustomer.value) {
  const targetCustomer = customer || selectedCustomer.value;
  const customerId = Number(targetCustomer?.id || 0);
  if (!customerId) return setNotice("This customer account is missing an ID.", "error");
  if (!props.adminUserId) return setNotice(uiText.value.adminMissing, "error");

  const reason = window.prompt(
    `Add a blacklist note for ${targetCustomer?.name || "this customer"}.`,
    `${targetCustomer?.name || "Customer"} was removed for fraudulent or abusive activity.`,
  );

  if (reason === null) return;
  if (!String(reason).trim()) {
    return setNotice("A blacklist note is required before deleting this customer.", "error");
  }

  const confirmed = window.confirm(
    `Delete ${targetCustomer?.name || "this customer"} and blacklist their email or phone number?`,
  );
  if (!confirmed) return;

  deletingCustomerId.value = customerId;
  try {
    await apiPost(`admin/users/${customerId}/delete-with-blacklist`, {
      admin_user_id: props.adminUserId,
      reason: String(reason).trim(),
    });
    await loadCustomerDirectory();
    setNotice("Customer deleted and blacklisted successfully.", "success");
  } catch (error) {
    setNotice(error?.message || "Could not delete and blacklist this customer.", "error");
  } finally {
    deletingCustomerId.value = null;
  }
}

async function approveBlacklistEntry(entry) {
  if (!entry?.id) return;
  if (!props.adminUserId) return setNotice(uiText.value.adminMissing, "error");

  const confirmed = window.confirm(`Approve ${entry.name || "this customer"} to reuse the platform again?`);
  if (!confirmed) return;

  approvingBlacklistId.value = entry.id;
  try {
    await apiPatch(`admin/blacklist/${entry.id}/approve`, {
      admin_user_id: props.adminUserId,
    });
    await loadCustomerDirectory();
    setNotice("Blacklist approval saved. This customer can register again.", "success");
  } catch (error) {
    setNotice(error?.message || "Could not approve this blacklist entry.", "error");
  } finally {
    approvingBlacklistId.value = null;
  }
}

watch(() => route.query.page, syncActiveKey, { immediate: true });
watch(
  filteredCustomers,
  (rows) => {
    if (!rows.length) return (selectedCustomerKey.value = "");
    if (!rows.some((item) => item.key === selectedCustomerKey.value)) selectedCustomerKey.value = rows[0].key;
  },
  { immediate: true },
);

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
          <input v-model="searchQuery" class="search-input" type="search" :placeholder="uiText.searchPlaceholder" />
        </label>
        <div class="topbar-actions">
          <button class="icon-btn" type="button" :title="uiText.notifications" :aria-label="uiText.notifications">
            <svg viewBox="0 0 24 24">
              <path d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z" />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section class="customers-hero">
        <div class="hero-copy">
          <p class="eyebrow">{{ uiText.heroEyebrow }}</p>
          <h1>{{ uiText.heroTitle }}</h1>
          <p>{{ uiText.heroSubtitle }}</p>
          <div class="hero-meta">
            <span class="hero-pill">{{ interpolate(uiText.totalCustomersText, { count: count(customerRows.length) }) }}</span>
            <span class="hero-pill soft">{{ interpolate(uiText.totalBookingsText, { count: count(totalBookingsCount) }) }}</span>
          </div>
        </div>
        <div class="hero-aside">
          <div v-if="selectedCustomer" class="hero-selected">
            <span class="hero-selected-label">{{ uiText.selectedCustomer }}</span>
            <strong>{{ selectedCustomer.name }}</strong>
            <small>{{ interpolate(uiText.customerSelectedSummary, { count: count(selectedCustomer.bookingsCount), date: selectedCustomer.joinedLabel }) }}</small>
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
              <p class="card-eyebrow">{{ uiText.directoryEyebrow }}</p>
              <h3>{{ uiText.allCustomers }}</h3>
            </div>
            <span class="card-meta">{{ interpolate(uiText.results, { count: count(filteredCustomers.length) }) }}</span>
          </header>
          <div class="filters">
            <label class="filter-field">
              <span>{{ uiText.activity }}</span>
              <select v-model="activityFilter">
                <option value="all">{{ uiText.allCustomersFilter }}</option>
                <option value="booked">{{ uiText.withBookings }}</option>
                <option value="repeat">{{ uiText.repeatCustomers }}</option>
                <option value="new">{{ uiText.noBookingsYetFilter }}</option>
              </select>
            </label>
            <label class="filter-field">
              <span>{{ uiText.bookingStatus }}</span>
              <select v-model="bookingStateFilter">
                <option value="all">{{ uiText.anyStatus }}</option>
                <option value="confirmed">{{ uiText.confirmed }}</option>
                <option value="pending">{{ uiText.pending }}</option>
                <option value="cancelled">{{ uiText.cancelled }}</option>
              </select>
            </label>
          </div>

          <div v-if="isLoading" class="empty">{{ uiText.loadingCustomerDirectory }}</div>
          <div v-else-if="!filteredCustomers.length" class="empty">{{ uiText.noCustomersMatch }}</div>
          <div v-else class="customer-list">
            <article
              v-for="customer in filteredCustomers"
              :key="customer.key"
              class="customer-row"
              :class="{ selected: selectedCustomer?.key === customer.key }"
              role="button"
              tabindex="0"
              :aria-pressed="selectedCustomer?.key === customer.key ? 'true' : 'false'"
              @click="selectedCustomerKey = customer.key"
              @keydown.enter.prevent="selectedCustomerKey = customer.key"
              @keydown.space.prevent="selectedCustomerKey = customer.key"
            >
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
                  <p>{{ customer.email || uiText.emailNotProvided }}</p>
                  <div class="chips">
                    <span class="chip">{{ interpolate(uiText.bookingCount, { count: count(customer.bookingsCount) }) }}</span>
                    <span class="chip muted">{{ customer.confirmedSpendLabel }}</span>
                    <span class="chip muted">{{ customer.preferredTypes[0] || uiText.noCategoryYet }}</span>
                  </div>
                </div>
              </div>
              <div class="directory-summary">
                <strong class="directory-metric">
                  {{
                    customer.bookingsCount
                      ? interpolate(uiText.bookingCount, { count: count(customer.bookingsCount) })
                      : uiText.noBookingsYet
                  }}
                </strong>
                <small>{{ customer.lastBookingLabel }}</small>
              </div>
              <div class="directory-actions customer-actions">
                <button
                  class="ghost-btn listing-delete-btn"
                  type="button"
                  :disabled="deletingCustomerId === customer.id"
                  @click.stop="deleteCustomerAndBlacklist(customer)"
                >
                  {{ deletingCustomerId === customer.id ? "Deleting..." : "Delete + Blacklist" }}
                </button>
              </div>
            </article>
          </div>
        </article>

        <aside class="side-column">
          <article v-if="selectedCustomer" class="card spotlight-card">
            <div class="sidebar-head">
              <div>
                <p class="card-eyebrow">{{ uiText.customerProfile }}</p>
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
                  <span class="chip muted">{{ interpolate(uiText.confirmedCount, { count: count(selectedCustomer.confirmedCount) }) }}</span>
                  <span class="chip muted">{{ interpolate(uiText.pendingCount, { count: count(selectedCustomer.pendingCount) }) }}</span>
                </div>
              </div>
            </div>
            <div class="stats-grid">
              <div><span>{{ uiText.bookings }}</span><strong>{{ count(selectedCustomer.bookingsCount) }}</strong></div>
              <div><span>{{ uiText.confirmed }}</span><strong>{{ count(selectedCustomer.confirmedCount) }}</strong></div>
              <div><span>{{ uiText.pending }}</span><strong>{{ count(selectedCustomer.pendingCount) }}</strong></div>
              <div><span>{{ uiText.totalSpend }}</span><strong>{{ selectedCustomer.confirmedSpendLabel }}</strong></div>
            </div>
            <div class="detail-grid">
              <div class="detail-block">
                <span>{{ uiText.email }}</span>
                <strong>{{ selectedCustomer.email || uiText.notProvided }}</strong>
              </div>
              <div class="detail-block">
                <span>{{ uiText.phone }}</span>
                <strong>{{ selectedCustomer.phone || uiText.notProvided }}</strong>
              </div>
              <div class="detail-block">
                <span>{{ uiText.location }}</span>
                <strong>{{ selectedCustomer.location }}</strong>
              </div>
              <div class="detail-block">
                <span>{{ uiText.joined }}</span>
                <strong>{{ selectedCustomer.joinedLabel }}</strong>
              </div>
              <div class="detail-block detail-wide">
                <span>{{ uiText.preferredCategories }}</span>
                <strong>{{ selectedCustomer.preferredTypes.length ? selectedCustomer.preferredTypes.join(", ") : uiText.noBookingsYet }}</strong>
              </div>
            </div>
          </article>

          <article v-if="selectedCustomer" class="card bookings-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">{{ uiText.bookingHistory }}</p>
                <h3>{{ uiText.customerBookings }}</h3>
              </div>
              <span class="card-meta">{{ count(selectedBookings.length) }}</span>
            </header>
            <div v-if="!selectedBookings.length" class="section-empty section-empty-compact">
              <span class="section-empty-label">{{ uiText.bookingHistory }}</span>
              <strong>{{ uiText.noBookingsYet }}</strong>
              <p>{{ uiText.noServicePackageYet }}</p>
            </div>
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
                    <span class="chip muted">{{ interpolate(uiText.qty, { count: booking.quantity }) }}</span>
                    <span class="chip muted">{{ booking.paymentStatusLabel }}</span>
                    <span class="chip muted">{{ booking.eventTypeLabel }}</span>
                  </div>
                </div>
              </div>
            </div>
          </article>

          <article class="card bookings-card">
            <header class="card-head">
              <div>
                <p class="card-eyebrow">Safety Watch</p>
                <h3>Blacklisted Customers</h3>
              </div>
              <span class="card-meta">{{ count(customerBlacklistRows.length) }}</span>
            </header>
            <div v-if="!customerBlacklistRows.length" class="section-empty section-empty-compact">
              <span class="section-empty-label">Safety Watch</span>
              <strong>No customers are blacklisted right now.</strong>
              <p>The moderation list will appear here one by one when an account is removed for rule violations.</p>
            </div>
            <div v-else class="booking-list">
              <div v-for="entry in customerBlacklistRows" :key="entry.id" class="booking-row blacklist-card">
                <div class="booking-copy">
                  <div class="booking-title-row">
                    <strong>{{ entry.name }}</strong>
                    <span class="chip" :class="{ muted: !entry.canApprove }">{{ entry.statusLabel }}</span>
                  </div>
                  <p>{{ entry.email || uiText.notProvided }}<template v-if="entry.phone"> | {{ entry.phone }}</template></p>
                </div>
                <div class="blacklist-meta">
                  <span class="chip muted">Blacklisted {{ entry.blacklistedAtLabel }}</span>
                  <span v-if="entry.approvedAtLabel" class="chip muted">Approved {{ entry.approvedAtLabel }}</span>
                </div>
                <p class="service-description blacklist-reason">{{ entry.reason }}</p>
                <div class="blacklist-actions">
                  <button
                    class="ghost-btn approve-btn"
                    type="button"
                    :disabled="!entry.canApprove || approvingBlacklistId === entry.id"
                    @click="approveBlacklistEntry(entry)"
                  >
                    {{ approvingBlacklistId === entry.id ? "Approving..." : entry.canApprove ? "Approve Reuse" : "Reuse Approved" }}
                  </button>
                </div>
              </div>
            </div>
          </article>

          <article v-if="!selectedCustomer" class="card empty-selection">
            <p class="card-eyebrow">{{ uiText.customerProfile }}</p>
            <h3>{{ uiText.selectCustomer }}</h3>
            <p>{{ uiText.selectCustomerSubtitle }}</p>
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
  display: grid;
  grid-template-columns: minmax(260px, 340px);
  gap: 14px;
  align-items: stretch;
  justify-content: flex-end;
  width: min(100%, 340px);
}

.hero-selected {
  display: grid;
  gap: 6px;
  min-width: 0;
  padding: 18px 20px;
  border-radius: 22px;
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.92), rgba(255, 250, 246, 0.84));
  border: 1px solid rgba(15, 23, 42, 0.07);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.9),
    var(--shadow-soft);
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
  grid-template-columns: minmax(0, 1fr) minmax(150px, 180px) minmax(170px, auto);
  gap: 24px;
  align-items: center;
  padding: 22px 24px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(255, 250, 246, 0.96));
  border-radius: 24px;
  text-align: left;
  cursor: pointer;
  transition:
    transform 0.18s ease,
    box-shadow 0.18s ease,
    border-color 0.18s ease,
    background-color 0.18s ease;
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
}

.customer-row:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 122, 26, 0.2);
  box-shadow: 0 20px 36px rgba(15, 23, 42, 0.09);
}

.customer-row.selected {
  border-color: rgba(255, 122, 26, 0.28);
  background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(255, 245, 236, 0.98));
  box-shadow: 0 20px 40px rgba(255, 122, 26, 0.1);
}

.customer-row:focus-visible {
  outline: 3px solid rgba(255, 122, 26, 0.2);
  outline-offset: 2px;
}

.customer-main,
.customer-title-row,
.booking-title-row,
.customer-identity {
  display: flex;
  align-items: flex-start;
}

.customer-main {
  gap: 16px;
  min-width: 0;
  align-items: center;
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
  width: 58px;
  height: 58px;
  border-radius: 18px;
  background: linear-gradient(135deg, #ffe9d6, #ffd2aa);
  color: #bf5c06;
  display: grid;
  place-items: center;
  font-weight: 700;
  overflow: hidden;
  box-shadow: 0 10px 20px rgba(255, 122, 26, 0.12);
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
  gap: 10px;
  min-width: 0;
}

.customer-copy strong,
.booking-copy strong,
.identity-copy strong {
  display: block;
  color: #17263d;
}

.customer-copy strong {
  font-size: 18px;
  line-height: 1.2;
}

.customer-copy p,
.booking-copy p,
.booking-copy small {
  margin: 0;
  font-size: 14px;
}

.customer-copy p {
  color: #68778d;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.directory-summary {
  display: grid;
  justify-items: end;
  gap: 5px;
  min-width: 0;
  text-align: right;
}

.directory-actions {
  display: grid;
  gap: 10px;
  justify-items: end;
  min-width: 0;
}

.customer-actions {
  align-content: center;
}

.directory-metric {
  font-size: 18px;
  font-weight: 700;
  line-height: 1.2;
  color: #17263d;
}

.directory-summary small {
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

.directory-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 13px;
  font-weight: 700;
  line-height: 1;
  white-space: nowrap;
  text-align: center;
}

.fixed-action-btn {
  min-width: 156px;
}

.listing-delete-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  justify-self: end;
  min-height: 0;
  padding: 8px 10px;
  border-radius: 10px;
  border: 1px solid rgba(220, 38, 38, 0.24);
  background: rgba(255, 244, 244, 0.96);
  color: #b42318;
  font-size: 11.5px;
  font-weight: 600;
  line-height: 1.1;
  white-space: nowrap;
  cursor: pointer;
  box-shadow: none;
  transition: none;
}

.listing-delete-btn:hover:not(:disabled) {
  transform: none;
  box-shadow: none;
  background: rgba(255, 244, 244, 0.96);
}

.directory-action-copy {
  display: grid;
  gap: 1px;
  justify-items: center;
  text-align: center;
}

.directory-action-copy span {
  display: block;
}

.chip {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(255, 122, 26, 0.1);
  color: #c45a12;
  font-size: 12px;
  font-weight: 700;
}

.chip.muted {
  background: #f8fafc;
  color: #526377;
  border: 1px solid rgba(148, 163, 184, 0.14);
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

.danger-btn {
  color: #b42318;
  border-color: rgba(220, 38, 38, 0.2);
  background:
    linear-gradient(135deg, rgba(255, 244, 244, 0.98), rgba(255, 235, 236, 0.96));
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.92),
    0 14px 24px rgba(220, 38, 38, 0.1);
}

.danger-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.92),
    0 18px 28px rgba(220, 38, 38, 0.14);
}

.booking-row {
  display: grid;
  gap: 12px;
  padding: 16px;
  background: linear-gradient(180deg, #fff, #fcfdff);
}

.blacklist-card {
  gap: 14px;
  padding: 18px;
  border-color: rgba(220, 38, 38, 0.08);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 246, 244, 0.94));
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.92),
    0 14px 28px rgba(15, 23, 42, 0.05);
}

.blacklist-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.blacklist-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-start;
  margin-top: 4px;
}

.approve-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: auto;
  min-height: 34px;
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
  color: #b45309;
  border-color: rgba(241, 91, 42, 0.18);
  background: linear-gradient(135deg, rgba(255, 250, 245, 0.98), rgba(255, 240, 232, 0.94));
  box-shadow: none;
}

.approve-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 10px 18px rgba(241, 91, 42, 0.1);
}

.directory-action-btn:hover:not(:disabled) {
  transform: none;
  box-shadow: none;
}

.approve-btn:disabled {
  color: #94a3b8;
  border-color: rgba(148, 163, 184, 0.24);
  background: linear-gradient(135deg, rgba(248, 250, 252, 0.98), rgba(241, 245, 249, 0.95));
}

.booking-copy {
  gap: 10px;
}

.service-description {
  margin: 0;
  color: #596981;
  line-height: 1.6;
}

.blacklist-reason {
  margin-top: 2px;
}

.section-empty {
  display: grid;
  gap: 10px;
  align-content: center;
  padding: 20px 18px;
  border-radius: 18px;
  border: 1px dashed rgba(255, 122, 26, 0.24);
  background: linear-gradient(135deg, rgba(255, 248, 241, 0.96), rgba(255, 255, 255, 0.98));
  text-align: left;
}

.section-empty strong,
.section-empty p {
  margin: 0;
}

.section-empty strong {
  color: #17263d;
  font-size: 16px;
}

.section-empty p {
  color: #64748b;
  line-height: 1.6;
}

.section-empty-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #f15b2a;
}

.section-empty-compact {
  min-height: auto;
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
    align-items: stretch;
  }

  .hero-copy h1 {
    max-width: none;
    font-size: 42px;
  }

  .hero-aside {
    grid-template-columns: 1fr;
    width: 100%;
    max-width: none;
  }
}

@media (max-width: 840px) {
  .admin-topbar,
  .customer-row,
  .sidebar-head,
  .blacklist-card {
    flex-direction: column;
    align-items: stretch;
  }

  .customer-row {
    grid-template-columns: 1fr;
    gap: 18px;
    padding: 22px 20px;
  }

  .directory-summary,
  .directory-actions {
    justify-items: start;
    text-align: left;
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

  .customer-row {
    padding: 20px 18px;
    border-radius: 20px;
  }

  .topbar-actions {
    width: 100%;
  }

  .topbar-actions {
    justify-content: space-between;
  }
}
</style>
