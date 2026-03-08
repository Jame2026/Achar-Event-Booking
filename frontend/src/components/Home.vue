<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import PublicNavbar from "./PublicNavbar.vue";
import { apiGet } from "../features/apiClient";
import { useLanguage } from "../features/language";

const router = useRouter();
const { language } = useLanguage();
const showAllEvents = ref(false);
const currentVendorIndex = ref(0);
const showBookingModal = ref(false);
const selectedVendor = ref(null);
const bookingSuccess = ref(false);

const bookingForm = ref({
  fullName: "",
  email: "",
  eventDate: "",
  guests: 50,
  notes: "",
});

const eventRows = ref([]);
const dataLoadFailed = ref(false);

const copyByLanguage = {
  en: {
    heroTitlePrefix: "Your Perfect Event,",
    heroTitleHighlight: "Orchestrated",
    heroTitleSuffix: "to Perfection",
    heroLede:
      "Discover and book trusted vendors, venues, and specialists for both traditional and modern celebrations.",
    exploreServices: "Explore Services & Packages",
    browseByEvent: "Browse by Event Type",
    suitedTitle: "Perfectly suited for those extra-special occasions",
    eventsSubtitle: "Live categories from available events and vendors.",
    showLess: "Show less",
    showAllEvents: "Show all events",
    eventLoadError: "Live data is temporarily unavailable. Showing fallback results.",
    exploreStyle: "Explore style >",
    recommendedVendors: "Recommended Vendors",
    handpickedTitle: "Handpicked services, ready to book",
    bookingYouAre: "You are booking",
    bookingRequestSent: "Booking Request Sent",
    bookingRequestBodyA: "Your request to book",
    bookingRequestBodyB: "has been sent. The vendor will contact you by email.",
    done: "Done",
    confirmDetails: "Confirm Your Details",
    fillForm: "Fill out the form below to send a booking request.",
    fullName: "Full name",
    emailAddress: "Email address",
    eventDate: "Event date",
    numberOfGuests: "Number of guests",
    additionalNotes: "Additional notes",
    sendBookingRequest: "Send Booking Request",
    cancel: "Cancel",
    planningSimple: "Planning Made Simple",
    stepsTitle: "The easy steps to bring your dream event to life",
    latestTips: "Latest Planning Tips",
    tipsTitle: "Ideas and advice from our planning pros",
    readAllArticles: "Read all articles ->",
    startPlanning: "Start Planning",
    ctaTitle: "Ready to Plan Your Masterpiece?",
    ctaText: "Join planners and vendors on Achar. Start your journey today.",
    getStartedFree: "Get Started for Free",
    listBusiness: "List Your Business",
    fallbackVendor: "Vendor",
    fallbackLocation: "Location TBD",
    fallbackEvent: "Special Event",
    bookingRequestFromHome: "Booking request from homepage",
    startsFrom: "Starts from",
    from: "From",
    book: "Book",
  },
  km: {
    heroTitlePrefix: "ព្រឹត្តិការណ៍ដ៏ល្អឥតខ្ចោះរបស់អ្នក,",
    heroTitleHighlight: "រៀបចំ",
    heroTitleSuffix: "យ៉ាងល្អឥតខ្ចោះ",
    heroLede:
      "ស្វែងរក និងកក់អ្នកផ្គត់ផ្គង់ទីតាំង និងអ្នកជំនាញដែលទុកចិត្តបាន សម្រាប់ពិធីប្រពៃណី និងទំនើប។",
    exploreServices: "ស្វែងរកសេវាកម្ម និងកញ្ចប់",
    browseByEvent: "រកមើលតាមប្រភេទព្រឹត្តិការណ៍",
    suitedTitle: "សមស្របសម្រាប់ឱកាសពិសេសរបស់អ្នក",
    eventsSubtitle: "ប្រភេទព្រឹត្តិការណ៍ផ្ទាល់ពីអ្នកផ្គត់ផ្គង់ និងកម្មវិធីដែលមាន។",
    showLess: "បង្ហាញតិច",
    showAllEvents: "បង្ហាញព្រឹត្តិការណ៍ទាំងអស់",
    eventLoadError: "ទិន្នន័យផ្ទាល់មិនអាចប្រើបានបណ្តោះអាសន្ន។ កំពុងបង្ហាញទិន្នន័យជំនួស។",
    exploreStyle: "មើលរចនាប័ទ្ម >",
    recommendedVendors: "អ្នកផ្គត់ផ្គង់ណែនាំ",
    handpickedTitle: "សេវាកម្មជ្រើសរើសរួច រៀបចំសម្រាប់ការកក់",
    bookingYouAre: "អ្នកកំពុងកក់",
    bookingRequestSent: "សំណើកក់ត្រូវបានផ្ញើ",
    bookingRequestBodyA: "សំណើរបស់អ្នកសម្រាប់កក់",
    bookingRequestBodyB: "ត្រូវបានផ្ញើរួចហើយ។ អ្នកផ្គត់ផ្គង់នឹងទាក់ទងអ្នកតាមអ៊ីមែល។",
    done: "រួចរាល់",
    confirmDetails: "បញ្ជាក់ព័ត៌មានរបស់អ្នក",
    fillForm: "បំពេញទម្រង់ខាងក្រោមដើម្បីផ្ញើសំណើកក់។",
    fullName: "ឈ្មោះពេញ",
    emailAddress: "អាសយដ្ឋានអ៊ីមែល",
    eventDate: "កាលបរិច្ឆេទព្រឹត្តិការណ៍",
    numberOfGuests: "ចំនួនភ្ញៀវ",
    additionalNotes: "កំណត់ចំណាំបន្ថែម",
    sendBookingRequest: "ផ្ញើសំណើកក់",
    cancel: "បោះបង់",
    planningSimple: "រៀបចំផែនការងាយស្រួល",
    stepsTitle: "ជំហានងាយៗ ដើម្បីធ្វើឱ្យព្រឹត្តិការណ៍សុបិន្តក្លាយជាការពិត",
    latestTips: "គន្លឹះរៀបចំថ្មីៗ",
    tipsTitle: "គំនិត និងដំបូន្មានពីអ្នកជំនាញរៀបចំរបស់យើង",
    readAllArticles: "អានអត្ថបទទាំងអស់ ->",
    startPlanning: "ចាប់ផ្តើមរៀបចំ",
    ctaTitle: "រួចរាល់សម្រាប់រៀបចំព្រឹត្តិការណ៍របស់អ្នកហើយឬនៅ?",
    ctaText: "ចូលរួមជាមួយអ្នករៀបចំ និងអ្នកផ្គត់ផ្គង់លើ Achar។ ចាប់ផ្តើមថ្ងៃនេះ។",
    getStartedFree: "ចាប់ផ្តើមដោយឥតគិតថ្លៃ",
    listBusiness: "ចុះឈ្មោះអាជីវកម្ម",
    fallbackVendor: "អ្នកផ្គត់ផ្គង់",
    fallbackLocation: "ទីតាំងមិនទាន់មាន",
    fallbackEvent: "ព្រឹត្តិការណ៍ពិសេស",
    bookingRequestFromHome: "សំណើកក់ពីទំព័រដើម",
    startsFrom: "ចាប់ពី",
    from: "ពី",
    book: "កក់",
  },
  zh: {
    heroTitlePrefix: "您的完美活动，",
    heroTitleHighlight: "精心策划",
    heroTitleSuffix: "到位",
    heroLede: "发现并预订值得信赖的供应商、场地和专家，适用于传统与现代庆典。",
    exploreServices: "探索服务与套餐",
    browseByEvent: "按活动类型浏览",
    suitedTitle: "为您的特别时刻量身打造",
    eventsSubtitle: "来自可用活动和供应商的实时分类。",
    showLess: "收起",
    showAllEvents: "显示全部活动",
    eventLoadError: "实时数据暂不可用，正在显示备用内容。",
    exploreStyle: "查看风格 >",
    recommendedVendors: "推荐商家",
    handpickedTitle: "精选服务，随时预订",
    bookingYouAre: "您正在预订",
    bookingRequestSent: "预订请求已发送",
    bookingRequestBodyA: "您对",
    bookingRequestBodyB: "的预订请求已发送，商家将通过邮箱联系您。",
    done: "完成",
    confirmDetails: "确认您的信息",
    fillForm: "填写下方表单以发送预订请求。",
    fullName: "姓名",
    emailAddress: "邮箱地址",
    eventDate: "活动日期",
    numberOfGuests: "宾客人数",
    additionalNotes: "附加说明",
    sendBookingRequest: "发送预订请求",
    cancel: "取消",
    planningSimple: "规划更简单",
    stepsTitle: "轻松几步，实现您的理想活动",
    latestTips: "最新策划建议",
    tipsTitle: "来自策划专家的想法与建议",
    readAllArticles: "阅读全部文章 ->",
    startPlanning: "开始规划",
    ctaTitle: "准备好策划您的精彩活动了吗？",
    ctaText: "加入 Achar 的策划者与商家，今天就开始。",
    getStartedFree: "免费开始",
    listBusiness: "商家入驻",
    fallbackVendor: "商家",
    fallbackLocation: "地点待定",
    fallbackEvent: "特别活动",
    bookingRequestFromHome: "来自首页的预订请求",
    startsFrom: "起价",
    from: "起",
    book: "预订",
  },
};

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);

const eventTypeLabelsByLanguage = {
  en: {
    wedding: "Wedding",
    baby_shower: "Baby Shower",
    house_blessing: "House Blessing",
    monk_ceremony: "Monk Ceremony",
    company_party: "Company Party",
    engagement: "Engagement",
    birthday: "Birthday",
    anniversary: "Anniversary",
    graduation: "Graduation",
    school_event: "School Event",
    festival: "Festival",
    other: "Special Event",
  },
  km: {
    wedding: "ពិធីអាពាហ៍ពិពាហ៍",
    baby_shower: "ពិធីស្វាគមន៍ទារក",
    house_blessing: "ពិធីសូត្រមន្តផ្ទះ",
    monk_ceremony: "ពិធីព្រះសង្ឃ",
    company_party: "កម្មវិធីក្រុមហ៊ុន",
    engagement: "ពិធីភ្ជាប់ពាក្យ",
    birthday: "ខួបកំណើត",
    anniversary: "ខួបអនុស្សាវរីយ៍",
    graduation: "ពិធីបញ្ចប់ការសិក្សា",
    school_event: "កម្មវិធីសាលា",
    festival: "ពិធីបុណ្យ",
    other: "ព្រឹត្តិការណ៍ពិសេស",
  },
  zh: {
    wedding: "婚礼",
    baby_shower: "宝宝派对",
    house_blessing: "乔迁祈福",
    monk_ceremony: "僧侣仪式",
    company_party: "公司活动",
    engagement: "订婚",
    birthday: "生日",
    anniversary: "纪念日",
    graduation: "毕业典礼",
    school_event: "校园活动",
    festival: "节庆活动",
    other: "特别活动",
  },
};

const eventTypeNotesByLanguage = {
  en: {
    wedding: "Event Planning",
    baby_shower: "Event Planning",
    house_blessing: "Traditional Ritual",
    monk_ceremony: "Traditional Ritual",
    company_party: "Corporate Event",
    engagement: "Event Planning",
    birthday: "Celebration",
    anniversary: "Celebration",
    graduation: "Celebration",
    school_event: "Community Event",
    festival: "Public Event",
    other: "Custom Event",
  },
  km: {
    wedding: "រៀបចំព្រឹត្តិការណ៍",
    baby_shower: "រៀបចំព្រឹត្តិការណ៍",
    house_blessing: "ពិធីប្រពៃណី",
    monk_ceremony: "ពិធីប្រពៃណី",
    company_party: "កម្មវិធីអាជីវកម្ម",
    engagement: "រៀបចំព្រឹត្តិការណ៍",
    birthday: "ពិធីអបអរ",
    anniversary: "ពិធីអបអរ",
    graduation: "ពិធីអបអរ",
    school_event: "កម្មវិធីសហគមន៍",
    festival: "កម្មវិធីសាធារណៈ",
    other: "ប្ដូរតាមតម្រូវការ",
  },
  zh: {
    wedding: "活动策划",
    baby_shower: "活动策划",
    house_blessing: "传统仪式",
    monk_ceremony: "传统仪式",
    company_party: "企业活动",
    engagement: "活动策划",
    birthday: "庆典活动",
    anniversary: "庆典活动",
    graduation: "庆典活动",
    school_event: "社区活动",
    festival: "公共活动",
    other: "定制活动",
  },
};

const eventImageByType = {
  wedding: "/event-cards/wedding-stage.jpg",
  baby_shower: "/event-cards/orange-flowers.jpg",
  house_blessing: "/house.png",
  monk_ceremony: "/event-cards/house-blessing-offering.jpg",
  company_party: "/event-cards/ceremony-hall.jpg",
  engagement: "/event-cards/engagement-attire.jpg",
  birthday: "/W5.png",
  anniversary: "/event-cards/anniversary-arch.jpg",
  graduation: "/event-cards/anniversary-arch.jpg",
  school_event: "/event-cards/ceremony-hall.jpg",
  festival: "/event-cards/house-blessing-offering.jpg",
  other: "/W3.png",
};

const eventFallbackByType = {
  wedding: "/W1.png",
  baby_shower: "/W2.png",
  house_blessing: "/W3.png",
  monk_ceremony: "/W2.png",
  company_party: "/p1.png",
  engagement: "/p2.png",
  birthday: "/W5.png",
  anniversary: "/w4.png",
  graduation: "/p1.png",
  school_event: "/W1.png",
  festival: "/W5.png",
  other: "/W3.png",
};

const eventCatalogKeys = [
  "wedding",
  "baby_shower",
  "house_blessing",
  "monk_ceremony",
  "company_party",
  "engagement",
  "birthday",
  "anniversary",
  "graduation",
  "school_event",
  "festival",
  "other",
];

function toEventLabel(key) {
  const normalized = String(key || "other").toLowerCase();
  const map = eventTypeLabelsByLanguage[language.value] || eventTypeLabelsByLanguage.en;
  return map[normalized] || uiText.value.fallbackEvent;
}

function toEventNote(key) {
  const normalized = String(key || "other").toLowerCase();
  const map = eventTypeNotesByLanguage[language.value] || eventTypeNotesByLanguage.en;
  return map[normalized] || eventTypeNotesByLanguage.en.other;
}

function withFallbackRows(primaryRows, fallbackRows, minRows, keyBuilder) {
  const rows = [...primaryRows];
  const existingKeys = new Set(rows.map((row) => keyBuilder(row)));
  for (const row of fallbackRows) {
    if (rows.length >= minRows) break;
    const key = keyBuilder(row);
    if (existingKeys.has(key)) continue;
    existingKeys.add(key);
    rows.push(row);
  }
  return rows;
}

const fallbackEventTypes = computed(() =>
  ["wedding", "baby_shower", "house_blessing", "monk_ceremony"].map((key) => ({
    key,
    name: toEventLabel(key),
    note: toEventNote(key),
    image: eventImageByType[key] || eventImageByType.other,
    fallback: eventFallbackByType[key] || eventFallbackByType.other,
  })),
);

const fullEventCatalog = computed(() =>
  eventCatalogKeys.map((key) => ({
    key,
    name: toEventLabel(key),
    note: toEventNote(key),
    image: eventImageByType[key] || eventImageByType.other,
    fallback: eventFallbackByType[key] || eventFallbackByType.other,
  })),
);

const fallbackVendors = computed(() => [
  {
    tag: "Top Rated",
    title: "Skyline Grand Atrium",
    categories: [toEventLabel("wedding"), "Venue", "Design"],
    location: "Downtown Manhattan",
    rating: 4.9,
    reviews: 2458,
    price: "$3,500",
    priceCaption: uiText.value.startsFrom,
    cta: uiText.value.book,
    image: "/W2.png",
  },
  {
    tag: "Top Rated",
    title: "Artisan Palate",
    categories: ["Catering", "Dining", "Menu"],
    location: "Upper West Side",
    rating: 4.8,
    reviews: 1945,
    price: "$150/pp",
    priceCaption: uiText.value.from,
    cta: uiText.value.book,
    image: "/W5.png",
  },
  {
    tag: "Top Rated",
    title: "Lumina Studios",
    categories: ["Photography", "Video", "Styling"],
    location: "Brooklyn Heights",
    rating: 4.9,
    reviews: 3021,
    price: "$2,200",
    priceCaption: uiText.value.startsFrom,
    cta: uiText.value.book,
    image: "/w4.png",
  },
  {
    tag: "Best Value",
    title: "Elegant Events Co",
    categories: ["Decoration", "Floral", "Coordination"],
    location: "Queens Center",
    rating: 4.7,
    reviews: 1523,
    price: "$1,200",
    priceCaption: uiText.value.startsFrom,
    cta: uiText.value.book,
    image: "/W1.png",
  },
]);

const fallbackTips = computed(() => [
  {
    category: toEventNote("wedding"),
    title: "5 Secrets to a Stress-Free Wedding Ceremony",
    meta: "By Team Achar | 6 min read",
    image: "/W1.png",
  },
  {
    category: "Corporate",
    title: "Choosing the Right Venue for Corporate Galas",
    meta: "By Team Achar | 4 min read",
    image: "/p1.png",
  },
  {
    category: "Decor & Styling",
    title: "Minimalist Tablescapes That Still Feel Luxe",
    meta: "By Team Achar | 5 min read",
    image: "/p2.png",
  },
]);

const steps = computed(() => [
  {
    title:
      language.value === "km"
        ? "ស្វែងរកអ្នកផ្គត់ផ្គង់"
        : language.value === "zh"
          ? "发现商家"
          : "Discover Vendors",
    text:
      language.value === "km"
        ? "រកមើលសេវាកម្មដែលទុកចិត្តបាន និងប្រៀបធៀបជម្រើសបានលឿន។"
        : language.value === "zh"
          ? "快速浏览可信服务并比较方案。"
          : "Browse trusted services and compare options fast.",
    icon: "01",
  },
  {
    title:
      language.value === "km"
        ? "កំណត់តាមតម្រូវការ"
        : language.value === "zh"
          ? "定制细节"
          : "Customize Details",
    text:
      language.value === "km"
        ? "ជ្រើសរើសកញ្ចប់ឱ្យសមនឹងព្រឹត្តិការណ៍ និងថវិការបស់អ្នក។"
        : language.value === "zh"
          ? "选择适合您活动与预算的组合。"
          : "Select package pieces that fit your event and budget.",
    icon: "02",
  },
  {
    title:
      language.value === "km"
        ? "កក់ដោយសុវត្ថិភាព"
        : language.value === "zh"
          ? "安全预订"
          : "Book Securely",
    text:
      language.value === "km"
        ? "បញ្ជាក់ការកក់ដោយសុវត្ថិភាព និងទទួលបានស្ថានភាពច្បាស់លាស់។"
        : language.value === "zh"
          ? "通过安全结账确认，并获得清晰状态更新。"
          : "Confirm with secure checkout and receive clear status updates.",
    icon: "03",
  },
]);

const eventTypes = computed(() => {
  if (!eventRows.value.length) return fullEventCatalog.value;

  const grouped = new Map();
  eventRows.value.forEach((item) => {
    const key = String(item.event_type || "other").toLowerCase();
    if (!grouped.has(key)) grouped.set(key, item);
  });

  const liveRows = Array.from(grouped.entries()).map(([key]) => ({
    key,
    name: toEventLabel(key),
    note: toEventNote(key),
    image: eventImageByType[key] || eventImageByType.other,
    fallback: eventFallbackByType[key] || eventFallbackByType.other,
  }));

  return withFallbackRows(
    liveRows,
    fullEventCatalog.value,
    fullEventCatalog.value.length,
    (row) => row.key,
  );
});

const displayedEvents = computed(() => {
  const rows = eventTypes.value;
  return showAllEvents.value ? rows : rows.slice(0, 4);
});

const vendors = computed(() => {
  if (!eventRows.value.length) return fallbackVendors.value;

  const grouped = new Map();

  eventRows.value.forEach((item) => {
    const groupKey = item.vendor_id || `event-${item.id}`;
    if (!grouped.has(groupKey)) {
      grouped.set(groupKey, {
        title: item.vendor?.name || item.title || uiText.value.fallbackVendor,
        location: item.location || uiText.value.fallbackLocation,
        eventTypes: new Set([String(item.event_type || "other").toLowerCase()]),
        minPrice: Number(item.price || 0),
        coverType: String(item.event_type || "other").toLowerCase(),
        count: 1,
      });
      return;
    }

    const row = grouped.get(groupKey);
    row.eventTypes.add(String(item.event_type || "other").toLowerCase());
    row.minPrice = Math.min(row.minPrice, Number(item.price || 0));
    row.count += 1;
  });

  const liveRows = Array.from(grouped.values()).map((row, index) => {
    const categories = Array.from(row.eventTypes).slice(0, 3).map((key) => toEventLabel(key));
    const rating = Number((4.6 + ((index % 5) * 0.08)).toFixed(1));
    const reviews = 420 + index * 137;
    const isPremium = row.minPrice >= 2500;

    return {
      tag: isPremium ? "Premium" : "Top Rated",
      title: row.title,
      categories,
      location: row.location,
      rating,
      reviews,
      price: row.minPrice > 0 ? `$${Math.round(row.minPrice).toLocaleString()}` : "$0",
      priceCaption: "Starts from",
      cta: "Book",
      image: eventImageByType[row.coverType] || eventImageByType.other,
    };
  });

  return withFallbackRows(liveRows, fallbackVendors.value, 4, (row) => row.title);
});

const VENDOR_PAGE_SIZE = 4;
const displayedVendors = computed(() => {
  const rows = vendors.value;
  const maxStart = Math.max(0, rows.length - VENDOR_PAGE_SIZE);
  const start = Math.min(currentVendorIndex.value, maxStart);
  return rows.slice(start, start + VENDOR_PAGE_SIZE);
});

const tips = computed(() => {
  if (!eventRows.value.length) return fallbackTips.value;

  const liveRows = eventRows.value.slice(0, 3).map((item) => {
    const key = String(item.event_type || "other").toLowerCase();
    return {
      category: toEventLabel(key),
      title: `How to plan a great ${toEventLabel(key).toLowerCase()} experience`,
      meta: `From ${item.title || "Achar vendor"}`,
      image: eventImageByType[key] || eventImageByType.other,
    };
  });

  return withFallbackRows(liveRows, fallbackTips.value, 3, (row) => row.title);
});

function nextVendors() {
  if (currentVendorIndex.value + VENDOR_PAGE_SIZE < vendors.value.length) {
    currentVendorIndex.value += VENDOR_PAGE_SIZE;
  }
}

function prevVendors() {
  if (currentVendorIndex.value - VENDOR_PAGE_SIZE >= 0) {
    currentVendorIndex.value -= VENDOR_PAGE_SIZE;
  }
}

function onEventCardImageError(event, fallback) {
  event.target.src = fallback;
}

function goToEvent(event) {
  const key = event.key || event.name.toLowerCase().replace(/\s+/g, "_");
  router.push({ path: "/services/packages", query: { event: key } });
}

function openBookingModal(vendor) {
  selectedVendor.value = vendor;
  bookingSuccess.value = false;
  showBookingModal.value = true;
  bookingForm.value = {
    fullName: "",
    email: "",
    eventDate: "",
    guests: 50,
    notes: "",
  };
}

function closeBookingModal() {
  showBookingModal.value = false;
}

function toNumberPrice(value) {
  const raw = String(value || "").replace(/[^0-9.]/g, "");
  const parsed = Number(raw);
  return Number.isFinite(parsed) ? parsed : 0;
}

function submitBooking() {
  if (!selectedVendor.value) return;

  const unitPrice = toNumberPrice(selectedVendor.value.price);
  const quantity = Math.max(1, Number(bookingForm.value.guests || 1));
  const itemTotal = Number((unitPrice * quantity).toFixed(2));

  const payload = {
    eventId: null,
    vendorTitle: selectedVendor.value.title || "Selected Vendor",
    fullName: bookingForm.value.fullName || "",
    email: bookingForm.value.email || "",
    phone: "",
    location: selectedVendor.value.location || "Location TBD",
    eventDate: bookingForm.value.eventDate || "",
    guests: quantity,
    notes: bookingForm.value.notes || "",
    requestedEventType: "other",
    items: [
      {
        type: "service",
        name: selectedVendor.value.title || "Selected Vendor",
        description: bookingForm.value.notes || uiText.value.bookingRequestFromHome,
        qty: quantity,
        unitPrice,
        totalPrice: itemTotal,
      },
    ],
  };

  sessionStorage.setItem("achar_prebook_checkout", JSON.stringify(payload));
  showBookingModal.value = false;
  router.push("/checkout");
}

async function loadHomeData() {
  dataLoadFailed.value = false;
  try {
    const result = await apiGet("events", { per_page: 80 });
    const rows = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    eventRows.value = rows;
  } catch (error) {
    dataLoadFailed.value = true;
    eventRows.value = [];
  }
}

onMounted(loadHomeData);
</script>

<template>
  <div class="home-page">
    <PublicNavbar />

    <section class="hero">
      <div class="hero__bg-shape"></div>
      <div class="hero__grid">
        <div class="hero__text">
          <h1>
            {{ uiText.heroTitlePrefix }} <span class="highlight">{{ uiText.heroTitleHighlight }}</span>
            {{ uiText.heroTitleSuffix }}
          </h1>
          <p class="lede">
            {{ uiText.heroLede }}
          </p>
          <router-link class="search-btn hero-explore-btn" to="/services/packages">
            <span>{{ uiText.exploreServices }}</span>
            <span class="hero-explore-icon" aria-hidden="true">-&gt;</span>
          </router-link>
        </div>
      </div>
    </section>

    <section id="services" class="section section--events">
      <div class="section__header">
        <div class="events-header-copy">
          <p class="eyebrow">{{ uiText.browseByEvent }}</p>
          <h2>{{ uiText.suitedTitle }}</h2>
          <p class="events-subtitle">
            {{ uiText.eventsSubtitle }}
          </p>
        </div>
        <button class="link-button event-toggle-btn" @click="showAllEvents = !showAllEvents">
          {{ showAllEvents ? uiText.showLess : uiText.showAllEvents }}
          <span aria-hidden="true">&gt;</span>
        </button>
      </div>

      <div v-if="dataLoadFailed" class="event-load-note">
        {{ uiText.eventLoadError }}
      </div>

      <div class="event-grid">
        <div
          v-for="event in displayedEvents"
          :key="event.key || event.name"
          class="event-card"
          role="button"
          tabindex="0"
          @click="goToEvent(event)"
          @keyup.enter="goToEvent(event)"
        >
          <div class="event-img-container">
            <span class="event-chip">{{ event.note }}</span>
            <img
              class="event-img"
              :src="event.image"
              :alt="event.name"
              @error="onEventCardImageError($event, event.fallback)"
            />
          </div>
          <div class="event-info">
            <p class="event-title">{{ event.name }}</p>
            <p class="event-note">{{ event.note }}</p>
            <p class="event-cta">{{ uiText.exploreStyle }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section section--vendors">
      <div class="section__header">
        <div>
          <p class="eyebrow">{{ uiText.recommendedVendors }}</p>
          <h2>{{ uiText.handpickedTitle }}</h2>
        </div>
        <div class="carousel-nav">
          <button aria-label="Previous" class="pill-btn" @click="prevVendors">&lt;</button>
          <button aria-label="Next" class="pill-btn" @click="nextVendors">&gt;</button>
        </div>
      </div>

      <div class="vendor-grid">
        <article class="vendor-card" v-for="vendor in displayedVendors" :key="vendor.title">
          <span class="vendor-tag">{{ vendor.tag }}</span>
          <div class="vendor-media">
            <img :src="vendor.image" :alt="vendor.title" />
          </div>
          <div class="vendor-body">
            <div>
              <p class="vendor-title">{{ vendor.title }}</p>
              <p class="vendor-meta">
                <span class="dot" v-for="category in vendor.categories" :key="category">{{ category }}</span>
                <span class="location">{{ vendor.location }}</span>
              </p>
            </div>
            <div class="vendor-rating">
              <span class="star">*</span>
              <strong>{{ vendor.rating }}</strong>
              <span class="reviews">{{ vendor.reviews?.toLocaleString() || "0" }} reviews</span>
            </div>
            <div class="vendor-footer">
              <div class="pricing">
                <p class="price-caption">{{ vendor.priceCaption }}</p>
                <p class="price">{{ vendor.price }}</p>
              </div>
              <button type="button" class="outline-btn" @click="openBookingModal(vendor)">
                {{ vendor.cta }}
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <div v-if="showBookingModal" class="booking-modal-overlay" @click="closeBookingModal">
      <div v-if="selectedVendor" class="booking-modal" @click.stop>
        <button type="button" class="booking-close" @click="closeBookingModal">&times;</button>
        <div class="booking-modal-layout">
          <div class="booking-vendor-preview">
            <img :src="selectedVendor.image" :alt="selectedVendor.title" class="vendor-preview-image" />
            <div class="vendor-preview-info">
              <p class="eyebrow">{{ uiText.bookingYouAre }}</p>
              <h4>{{ selectedVendor.title }}</h4>
              <p class="vendor-location">{{ selectedVendor.location }}</p>
              <div class="vendor-rating">
                <span class="star">*</span>
                <strong>{{ selectedVendor.rating }}</strong>
                <span class="reviews">({{ selectedVendor.reviews?.toLocaleString() }} reviews)</span>
              </div>
            </div>
          </div>

          <div class="booking-form-section">
            <div v-if="bookingSuccess" class="booking-success-state">
              <h3>{{ uiText.bookingRequestSent }}</h3>
              <p>
                {{ uiText.bookingRequestBodyA }} <strong>{{ selectedVendor.title }}</strong>
                {{ uiText.bookingRequestBodyB }}
              </p>
              <button type="button" class="primary-btn" @click="closeBookingModal">{{ uiText.done }}</button>
            </div>

            <form
              v-else
              id="bookingRequestForm"
              class="booking-modal-form"
              @submit.prevent="submitBooking"
            >
              <div class="booking-modal-header">
                <h3>{{ uiText.confirmDetails }}</h3>
                <p>{{ uiText.fillForm }}</p>
              </div>

              <div class="form-group">
                <label for="fullName">{{ uiText.fullName }}</label>
                <input id="fullName" v-model.trim="bookingForm.fullName" type="text" required />
              </div>

              <div class="form-group">
                <label for="email">{{ uiText.emailAddress }}</label>
                <input id="email" v-model.trim="bookingForm.email" type="email" required />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="eventDate">{{ uiText.eventDate }}</label>
                  <input id="eventDate" v-model="bookingForm.eventDate" type="date" required />
                </div>
                <div class="form-group">
                  <label for="guests">{{ uiText.numberOfGuests }}</label>
                  <input id="guests" v-model.number="bookingForm.guests" type="number" min="1" required />
                </div>
              </div>

              <div class="form-group">
                <label for="notes">{{ uiText.additionalNotes }}</label>
                <textarea id="notes" v-model.trim="bookingForm.notes" rows="3"></textarea>
              </div>

            </form>
            <div class="booking-modal-actions">
              <button
                type="submit"
                form="bookingRequestForm"
                class="primary-btn booking-submit-btn"
              >
                {{ uiText.sendBookingRequest }}
              </button>
              <button
                type="button"
                class="ghost-btn booking-cancel-btn"
                @click="closeBookingModal"
              >
                {{ uiText.cancel }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="section steps">
      <div class="section__header center">
        <p class="eyebrow">{{ uiText.planningSimple }}</p>
        <h2>{{ uiText.stepsTitle }}</h2>
      </div>
      <div class="steps-grid">
        <div class="step-card" v-for="(step, index) in steps" :key="step.title">
          <div class="step-icon">{{ step.icon }}</div>
          <p class="step-index">0{{ index + 1 }}.</p>
          <p class="step-title">{{ step.title }}</p>
          <p class="step-text">{{ step.text }}</p>
        </div>
      </div>
    </section>

    <section id="favorite" class="section tips">
      <div class="section__header">
        <div>
          <p class="eyebrow">{{ uiText.latestTips }}</p>
          <h2>{{ uiText.tipsTitle }}</h2>
        </div>
        <router-link class="link" to="/customization">{{ uiText.readAllArticles }}</router-link>
      </div>
      <div class="tips-grid">
        <article class="tip-card" v-for="tip in tips" :key="tip.title">
          <img class="tip-img" :src="tip.image" :alt="tip.title" />
          <div class="tip-body">
            <p class="tip-category">{{ tip.category }}</p>
            <p class="tip-title">{{ tip.title }}</p>
            <p class="tip-meta">{{ tip.meta }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="cta">
      <div class="cta__content">
        <p class="eyebrow light">{{ uiText.startPlanning }}</p>
        <h2>{{ uiText.ctaTitle }}</h2>
        <p class="cta-text">
          {{ uiText.ctaText }}
        </p>
        <div class="cta-actions">
          <router-link class="primary-btn large" to="/booking">{{ uiText.getStartedFree }}</router-link>
          <router-link class="ghost-btn light large" to="/customization">{{ uiText.listBusiness }}</router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
