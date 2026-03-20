<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import PublicNavbar from "./PublicNavbar.vue";
import { apiGet } from "../features/apiClient";
import { useLanguage } from "../features/language";
import { fallbackPublicEvents } from "../features/publicEventFallbacks";

const router = useRouter();
const { language } = useLanguage();
const showAllEvents = ref(false);
const showAllVendors = ref(false);
const currentVendorIndex = ref(0);
const activeStepIndex = ref(0);

const stepIcons = {
  search: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>`,
  sliders: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>`,
  shield: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>`,
};
const heroSlides = ref([
  {
    image: "/W1.png",
    label: "Elegant ballroom",
  },
  {
    image: "/RE12.png",
    label: "Garden ceremony",
  },
  {
    image: "/RE.png",
    label: "Sunset vows",
  },
  {
    image: "/W2.png",
    label: "Classic hall",
  },
]);
const activeHeroIndex = ref(0);

const eventRows = ref([]);
const dataLoadFailed = ref(false);
const isLoadingHomeData = ref(false);
const HOME_EVENT_PAGE_SIZE = 100;

const copyByLanguage = {
  en: {
    heroTitlePrefix: "Your Perfect Event,",
    heroTitleHighlight: "Orchestrated",
    heroTitleSuffix: "to Perfection",
    heroLede:
      "Discover and book trusted vendors, venues, and specialists for both traditional and modern celebrations.",
    exploreServices: "Explore Services & Packages",
    browseByEvent: "Browse Package Services",
    suitedTitle: "Package services available right now",
    eventsSubtitle: "Services posted by vendors are shown here.",
    showLess: "Show less",
    showAllEvents: "Show all services",
    eventLoadError: "Live data is temporarily unavailable.",
    eventLoading: "Loading live vendor services...",
    noLiveServices: "No live vendor services available yet.",
    exploreStyle: "Explore style >",
    recommendedVendors: "Recommended Vendors",
    handpickedTitle: "Handpicked overall services, ready to book",
    showAllServices: "See all overall services",
    showLessServices: "Show fewer overall services",
    postedServicesSubtitle:
      "Every overall service posted by vendors is available here.",
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
    // listBusiness: "Click",
    fallbackVendor: "Vendor",
    fallbackLocation: "Location TBD",
    fallbackEvent: "Special Event",
    bookingRequestFromHome: "Booking request from homepage",
    startsFrom: "Starts from",
    from: "From",
    book: "Book",
    serviceBooking: "Service Booking",
    professionalServiceReady: "Professional vendor service ready for booking.",
    premium: "Premium",
    topRated: "Top Rated",
    reviews: "reviews",
    previous: "Previous",
    next: "Next",
    viewService: "View Service",
    noServiceUpdates: "No live service updates available yet.",
    howToPlanPrefix: "How to plan a great",
    howToPlanSuffix: "experience",
    fromVendor: "From",
    planner: "Planner",
  },
  km: {
    heroTitlePrefix: "ព្រឹត្តិការណ៍ដ៏ល្អឥតខ្ចោះរបស់អ្នក,",
    heroTitleHighlight: "រៀបចំ",
    heroTitleSuffix: "យ៉ាងល្អឥតខ្ចោះ",
    heroLede:
      "ស្វែងរក និងកក់អ្នកផ្គត់ផ្គង់ទីតាំង និងអ្នកជំនាញដែលទុកចិត្តបាន សម្រាប់ពិធីប្រពៃណី និងទំនើប។",
    exploreServices: "ស្វែងរកសេវាកម្ម និងកញ្ចប់",
    browseByEvent: "រកមើលសេវាកម្មកញ្ចប់",
    suitedTitle: "សេវាកម្មកញ្ចប់ដែលអាចកក់បានឥឡូវនេះ",
    eventsSubtitle: "សេវាកម្មដែលអ្នកផ្គត់ផ្គង់បានបង្ហោះត្រូវបានបង្ហាញនៅទីនេះ។",
    showLess: "បង្ហាញតិច",
    showAllEvents: "បង្ហាញសេវាកម្មទាំងអស់",
    eventLoadError: "ទិន្នន័យផ្ទាល់មិនអាចប្រើបានបណ្តោះអាសន្ន។",
    eventLoading: "កំពុងផ្ទុកសេវាកម្មផ្ទាល់ពីអ្នកផ្គត់ផ្គង់...",
    noLiveServices: "មិនទាន់មានសេវាកម្មផ្ទាល់ពីអ្នកផ្គត់ផ្គង់ទេ។",
    exploreStyle: "មើលរចនាប័ទ្ម >",
    recommendedVendors: "អ្នកផ្គត់ផ្គង់ណែនាំ",
    handpickedTitle: "សេវាកម្មទូទៅជ្រើសរើសរួច រៀបចំសម្រាប់ការកក់",
    showAllServices: "មើលសេវាកម្មទូទៅទាំងអស់",
    showLessServices: "បង្ហាញសេវាកម្មទូទៅតិច",
    postedServicesSubtitle:
      "សេវាកម្មទូទៅទាំងអស់ដែលអ្នកផ្គត់ផ្គង់បានបង្ហោះមាននៅទីនេះ។",
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
    serviceBooking: "ការកក់សេវាកម្ម",
    professionalServiceReady: "សេវាកម្មអ្នកផ្គត់ផ្គង់ដែលត្រៀមសម្រាប់ការកក់។",
    premium: "ពិសេស",
    topRated: "ពេញនិយម",
    reviews: "ការវាយតម្លៃ",
    previous: "មុន",
    next: "បន្ទាប់",
    viewService: "មើលសេវាកម្ម",
    noServiceUpdates: "មិនទាន់មានព័ត៌មានសេវាកម្មថ្មីទេ។",
    howToPlanPrefix: "របៀបរៀបចំ",
    howToPlanSuffix: "ឱ្យល្អ",
    fromVendor: "ពី",
    planner: "អ្នករៀបចំ",
  },
  zh: {
    heroTitlePrefix: "您的完美活动，",
    heroTitleHighlight: "精心策划",
    heroTitleSuffix: "到位",
    heroLede: "发现并预订值得信赖的供应商、场地和专家，适用于传统与现代庆典。",
    exploreServices: "探索服务与套餐",
    browseByEvent: "浏览套餐服务",
    suitedTitle: "当前可预订的套餐服务",
    eventsSubtitle: "这里显示商家发布的真实服务。",
    showLess: "收起",
    showAllEvents: "显示全部服务",
    eventLoadError: "实时数据暂不可用。",
    eventLoading: "正在加载实时商家服务...",
    noLiveServices: "暂无实时商家服务。",
    exploreStyle: "查看风格 >",
    recommendedVendors: "推荐商家",
    handpickedTitle: "精选整体服务，随时预订",
    showAllServices: "查看全部整体服务",
    showLessServices: "收起整体服务",
    postedServicesSubtitle: "这里会显示商家发布的全部整体服务。",
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
    serviceBooking: "服务预订",
    professionalServiceReady: "可立即预订的专业商家服务。",
    premium: "精选",
    topRated: "高评分",
    reviews: "条评价",
    previous: "上一项",
    next: "下一项",
    viewService: "查看服务",
    noServiceUpdates: "暂无最新服务动态。",
    howToPlanPrefix: "如何策划出色的",
    howToPlanSuffix: "体验",
    fromVendor: "来自",
    planner: "策划人",
  },
};

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);
const heroStyle = computed(() => {
  const current = heroSlides.value[activeHeroIndex.value % heroSlides.value.length];
  return {
    backgroundImage: `linear-gradient(120deg, rgba(8, 12, 26, 0.36), rgba(8, 12, 26, 0.18)), url(${current?.image})`,
    backgroundSize: "cover",
    backgroundPosition: "center",
    backgroundRepeat: "no-repeat",
    backgroundColor: "#f6f7fb",
  };
});

const setHero = (index) => {
  const total = heroSlides.value.length;
  activeHeroIndex.value = ((index % total) + total) % total;
};

const nextHero = () => setHero(activeHeroIndex.value + 1);
const prevHero = () => setHero(activeHeroIndex.value - 1);

let heroTimer;
let vendorTimer;
let stepTimer;

const autoRotateVendors = () => {
  if (showAllVendors.value) return;
  if (currentVendorIndex.value + VENDOR_PAGE_SIZE >= vendors.value.length) {
    currentVendorIndex.value = 0;
  } else {
    currentVendorIndex.value += VENDOR_PAGE_SIZE;
  }
};

const autoHighlightStep = () => {
  activeStepIndex.value = (activeStepIndex.value + 1) % steps.value.length;
};

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
    iconSvg: stepIcons.search,
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
    iconSvg: stepIcons.sliders,
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
    iconSvg: stepIcons.shield,
  },
]);

function normalizeServiceMode(item) {
  const mode = String(item?.service_mode || item?.serviceMode || "overall").toLowerCase();
  return mode === "package" ? "package" : "overall";
}

const packageRows = computed(() =>
  eventRows.value.filter((item) => normalizeServiceMode(item) === "package"),
);

const overallRows = computed(() =>
  eventRows.value.filter((item) => normalizeServiceMode(item) === "overall"),
);

const eventTypes = computed(() => {
  if (!packageRows.value.length) return [];
  return packageRows.value.map((item, index) => {
    const eventTypeKey = String(item.event_type || "other").toLowerCase();
    const price = Number(item.price || 0);
    return {
      id: Number(item.id || index + 1),
      key: `home-event-${item.id || index + 1}`,
      eventId: Number(item.id || 0) || null,
      vendorName: item.vendor?.name || uiText.value.fallbackVendor,
      requestedEventType: eventTypeKey,
      serviceMode: "package",
      name: item.title || uiText.value.serviceBooking,
      note: toEventLabel(eventTypeKey),
      description: String(item.description || "").trim() || uiText.value.professionalServiceReady,
      price,
      priceLabel: price > 0 ? `${uiText.value.from} $${Math.round(price).toLocaleString()}` : "$0",
      image: item.image_url || eventImageByType[eventTypeKey] || eventImageByType.other,
      fallback: eventFallbackByType[eventTypeKey] || eventFallbackByType.other,
    };
  });
});

const displayedEvents = computed(() => {
  const rows = eventTypes.value;
  return showAllEvents.value ? rows : rows.slice(0, 4);
});

const vendors = computed(() => {
  if (!overallRows.value.length) return [];
  return overallRows.value.map((item, index) => {
    const eventTypeKey = String(item.event_type || "other").toLowerCase();
    const price = Number(item.price || 0);
    return {
      id: Number(item.id || index + 1),
      eventId: Number(item.id || 0) || null,
      vendorId: Number(item.vendor_id || 0) || null,
      vendorName: item.vendor?.name || uiText.value.fallbackVendor,
      requestedEventType: eventTypeKey,
      serviceMode: "overall",
      tag: price >= 2500 ? uiText.value.premium : uiText.value.topRated,
      title: item.title || uiText.value.serviceBooking,
      categories: [toEventLabel(eventTypeKey), item.location || uiText.value.fallbackLocation],
      location: item.location || uiText.value.fallbackLocation,
      rating: Number((4.6 + ((index % 5) * 0.08)).toFixed(1)),
      reviews: Number(item.bookings_count || 0),
      price: price > 0 ? `$${Math.round(price).toLocaleString()}` : "$0",
      priceCaption: uiText.value.startsFrom,
      cta: uiText.value.book,
      image: item.image_url || eventImageByType[eventTypeKey] || eventImageByType.other,
    };
  });
});

const VENDOR_PAGE_SIZE = 4;
const vendorPageCount = computed(() => Math.max(1, Math.ceil(vendors.value.length / VENDOR_PAGE_SIZE)));
const hasMoreVendors = computed(() => vendors.value.length > VENDOR_PAGE_SIZE);
const displayedVendors = computed(() => {
  const rows = vendors.value;
  if (showAllVendors.value) return rows;
  const maxStart = Math.max(0, rows.length - VENDOR_PAGE_SIZE);
  const start = Math.min(currentVendorIndex.value, maxStart);
  return rows.slice(start, start + VENDOR_PAGE_SIZE);
});

const tips = computed(() => {
  if (!eventRows.value.length) return [];

  return eventRows.value.slice(0, 3).map((item) => {
    const key = String(item.event_type || "other").toLowerCase();
    return {
      category: toEventLabel(key),
      title: `${uiText.value.howToPlanPrefix} ${toEventLabel(key).toLowerCase()} ${uiText.value.howToPlanSuffix}`,
      meta: `${uiText.value.fromVendor} ${item.title || uiText.value.fallbackVendor}`,
      image: eventImageByType[key] || eventImageByType.other,
    };
  });
});

function nextVendors() {
  if (showAllVendors.value) return;
  if (currentVendorIndex.value + VENDOR_PAGE_SIZE < vendors.value.length) {
    currentVendorIndex.value += VENDOR_PAGE_SIZE;
  }
}

function prevVendors() {
  if (showAllVendors.value) return;
  if (currentVendorIndex.value - VENDOR_PAGE_SIZE >= 0) {
    currentVendorIndex.value -= VENDOR_PAGE_SIZE;
  }
}

function toggleAllVendors() {
  showAllVendors.value = !showAllVendors.value;
  if (!showAllVendors.value) {
    currentVendorIndex.value = 0;
  }
}

function onEventCardImageError(event, fallback) {
  event.target.src = fallback;
}

function goToEvent(event) {
  const query = {};
  const eventType = String(event.requestedEventType || "").trim();
  const title = String(event.name || event.title || "").trim();
  const targetPath = event.serviceMode === "overall" ? "/services/overall" : "/services/packages";

  if (eventType && eventType !== "other") query.event = eventType;
  if (title) query.q = title;

  router.push({ path: targetPath, query });
}

async function loadHomeData() {
  isLoadingHomeData.value = true;
  dataLoadFailed.value = false;
  try {
    const result = await apiGet("events", { per_page: HOME_EVENT_PAGE_SIZE });
    const rows = Array.isArray(result?.data) ? result.data : Array.isArray(result) ? result : [];
    if (rows.length) {
      eventRows.value = rows;
      return;
    }

    const fallbackResponse = await fetch(`/api/events?per_page=${HOME_EVENT_PAGE_SIZE}`, {
      headers: {
        Accept: "application/json",
      },
    });
    if (!fallbackResponse.ok) {
      throw new Error(`Home events request failed (${fallbackResponse.status})`);
    }

    const fallbackJson = await fallbackResponse.json().catch(() => ({}));
    const fallbackRows = Array.isArray(fallbackJson?.data)
      ? fallbackJson.data
      : Array.isArray(fallbackJson)
        ? fallbackJson
        : [];
    eventRows.value = fallbackRows.length ? fallbackRows : fallbackPublicEvents;
  } catch (error) {
    eventRows.value = fallbackPublicEvents;
  } finally {
    isLoadingHomeData.value = false;
  }
}

onMounted(() => {
  loadHomeData();
  heroTimer = window.setInterval(nextHero, 7000);
  vendorTimer = window.setInterval(autoRotateVendors, 8000);
  stepTimer = window.setInterval(autoHighlightStep, 5000);
});

onBeforeUnmount(() => {
  if (heroTimer) window.clearInterval(heroTimer);
  if (vendorTimer) window.clearInterval(vendorTimer);
  if (stepTimer) window.clearInterval(stepTimer);
});
</script>

<template>
  <div class="home-page">
    <PublicNavbar />

    <section class="hero" :style="heroStyle">
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

        <div class="hero__controls">
          <div class="hero__nav">
            <button type="button" class="hero__nav-btn" @click="prevHero" aria-label="Previous hero image">
              <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 18l-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            <button type="button" class="hero__nav-btn" @click="nextHero" aria-label="Next hero image">
              <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="hero__thumbs" role="listbox" aria-label="Hero gallery">
            <button
              v-for="(slide, idx) in heroSlides"
              :key="slide.image"
              type="button"
              class="hero__thumb"
              :class="{ active: idx === activeHeroIndex }"
              @click="setHero(idx)"
              :aria-label="slide.label"
              :aria-pressed="idx === activeHeroIndex"
            >
              <img :src="slide.image" :alt="slide.label" loading="lazy" />
            </button>
          </div>
          <div class="hero__support-icons" aria-hidden="true">
            <span class="support-pill">
              <svg width="16" height="16" viewBox="0 0 24 24"><path d="M12 2l3 7 7 .5-5.5 4.8 1.8 7.2L12 17.8 5.7 21.5 7.5 14 2 9.5 9 9z" fill="currentColor"/></svg>
              Trusted venues
            </span>
            <span class="support-pill">
              <svg width="16" height="16" viewBox="0 0 24 24"><path d="M3 11l9-9 9 9-9 9-9-9z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="12" cy="11" r="2.5" fill="currentColor"/></svg>
              Vendors verified
            </span>
            <span class="support-pill">
              <svg width="16" height="16" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
              Easy booking
            </span>
          </div>
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

      <div v-if="isLoadingHomeData" class="event-load-note">
        {{ uiText.eventLoading }}
      </div>

      <div v-else-if="displayedEvents.length === 0" class="event-load-note">
        {{ uiText.noLiveServices }}
      </div>

      <div v-else class="event-grid">
        <article
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
            <div class="event-copy">
              <p class="event-type">{{ event.note }}</p>
              <p class="event-title">{{ event.name }}</p>
              <p class="event-note">{{ event.vendorName }}</p>
              <p class="event-desc">{{ event.description }}</p>
            </div>
            <div class="event-card-footer">
              <strong>{{ event.priceLabel }}</strong>
              <button
                type="button"
                class="outline-btn"
                @click.stop="goToEvent(event)"
              >
                {{ uiText.viewService }}
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="section section--vendors">
      <div class="section__header">
        <div>
          <p class="eyebrow">{{ uiText.recommendedVendors }}</p>
          <h2>{{ uiText.handpickedTitle }}</h2>
          <p class="events-subtitle">{{ uiText.postedServicesSubtitle }}</p>
        </div>
        <div class="carousel-nav">
          <button
            v-if="hasMoreVendors"
            type="button"
            class="link-button event-toggle-btn"
            @click="toggleAllVendors"
          >
            {{ showAllVendors ? uiText.showLessServices : uiText.showAllServices }}
            <span aria-hidden="true">&gt;</span>
          </button>
        </div>
      </div>

      <div v-if="isLoadingHomeData" class="event-load-note">
        {{ uiText.eventLoading }}
      </div>

      <div v-else-if="displayedVendors.length === 0" class="event-load-note">
        {{ uiText.noLiveServices }}
      </div>

      <div v-else class="event-grid">
        <article
          class="event-card"
          v-for="vendor in displayedVendors"
          :key="vendor.id || vendor.title"
          role="button"
          tabindex="0"
          @click="goToEvent(vendor)"
          @keyup.enter="goToEvent(vendor)"
        >
          <div class="event-img-container">
            <img class="event-img" :src="vendor.image" :alt="vendor.title" />
          </div>
          <div class="event-info">
            <div class="event-copy">
              <p class="event-type">{{ vendor.categories[0] }}</p>
              <p class="event-title">{{ vendor.title }}</p>
              <p v-if="vendor.vendorName" class="event-note">{{ vendor.vendorName }}</p>
              <p class="event-desc">{{ vendor.location }}</p>
            </div>
            <div class="event-card-footer">
              <strong>{{ vendor.priceCaption }} {{ vendor.price }}</strong>
              <button
                type="button"
                class="outline-btn"
                @click.stop="goToEvent(vendor)"
              >
                {{ vendor.cta }}
              </button>
            </div>
          </div>
        </article>
      </div>
      <p v-if="!showAllVendors && hasMoreVendors" class="vendor-page-indicator">
        {{ Math.floor(currentVendorIndex / VENDOR_PAGE_SIZE) + 1 }} / {{ vendorPageCount }}
      </p>
    </section>

    <section class="section steps">
      <div class="section__header center">
        <p class="eyebrow">{{ uiText.planningSimple }}</p>
        <h2>{{ uiText.stepsTitle }}</h2>
      </div>
      <div class="steps-grid">
        <div
          class="step-card"
          v-for="(step, index) in steps"
          :key="step.title"
          :class="{ active: index === activeStepIndex }"
        >
          <div class="step-icon" v-html="step.iconSvg"></div>
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
      <div v-if="tips.length === 0" class="event-load-note">
        {{ uiText.noServiceUpdates }}
      </div>

      <div v-else class="tips-grid">
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
          <router-link class="primary-btn large cta-btn" to="/booking">
            <span class="btn-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-3.8L6 21l1.5-7.5L2 9h7z"/>
              </svg>
            </span>
            <span>{{ uiText.getStartedFree }}</span>
          </router-link>
          <router-link class="ghost-btn light large cta-btn" to="/customization">
            <span class="btn-icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4z"/>
                <path d="M9 22V12h6v10"/>
              </svg>
            </span>
            <span>{{ uiText.listBusiness }}</span>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
