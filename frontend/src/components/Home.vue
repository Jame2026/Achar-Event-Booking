<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import PublicNavbar from "./PublicNavbar.vue";
<<<<<<< HEAD
import { apiGet } from "../features/apiClient";

const router = useRouter();
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

const eventTypeLabelMap = {
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
};

const eventTypeNoteMap = {
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

const fallbackEventTypes = [
  { key: "wedding", name: "Wedding", note: "Event Planning" },
  { key: "baby_shower", name: "Baby Shower", note: "Event Planning" },
  { key: "house_blessing", name: "House Blessing", note: "Traditional Ritual" },
  { key: "monk_ceremony", name: "Monk Ceremony", note: "Traditional Ritual" },
].map((row) => ({
  ...row,
  image: eventImageByType[row.key] || eventImageByType.other,
  fallback: eventFallbackByType[row.key] || eventFallbackByType.other,
}));

const fullEventCatalog = [
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
].map((key) => ({
  key,
  name: toEventLabel(key),
  note: toEventNote(key),
  image: eventImageByType[key] || eventImageByType.other,
  fallback: eventFallbackByType[key] || eventFallbackByType.other,
}));

const fallbackVendors = [
  {
    tag: "Top Rated",
    title: "Skyline Grand Atrium",
    categories: ["Wedding", "Venue", "Design"],
=======
import { apiGet, apiPost } from "../features/apiClient";

const appLogoSrc = ref(localStorage.getItem("achar_brand_logo") || "/achar-logo.png");
const showAllEvents = ref(false);
const router = useRouter();
const dbServices = ref([]);

function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}

const eventTypes = [
  { name: "Wedding", note: "Event Planning", image: "/event-cards/wedding-stage.jpg", fallback: "/W1.png" },
  { name: "Baby Shower", note: "Event Planning", image: "/event-cards/orange-flowers.jpg", fallback: "/W2.png" },
  { name: "House Blessing", note: "Event Planning", image: "/house.png", fallback: "/W3.png" },
  { name: "Monk Ceremony", note: "Traditional Ritual", image: "/event-cards/house-blessing-offering.jpg", fallback: "/W2.png" },
  { name: "Company Party", note: "Event Planning", image: "/event-cards/ceremony-hall.jpg", fallback: "/p1.png" },
  { name: "Engagement", note: "Event Planning", image: "/event-cards/engagement-attire.jpg", fallback: "/p2.png" },
  { name: "Birthday", note: "Event Planning", image: "/W5.png", fallback: "/W5.png" },
  { name: "Anniversary", note: "Event Planning", image: "/event-cards/anniversary-arch.jpg", fallback: "/w4.png" },
];

const displayedEvents = computed(() => (showAllEvents.value ? eventTypes : eventTypes.slice(0, 4)));

function onEventCardImageError(event, fallback) {
  event.target.src = fallback;
}

function goToEvent(event) {
  const key = event.name.toLowerCase().replace(/\s+/g, "_");
  router.push({ path: "/services/packages", query: { event: key } });
}

const fallbackVendors = [
  {
    tag: "Top rated",
    title: "Skyline Grand Atrium",
    categories: ["Wedding Planning", "Event Design", "Venue"],
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
    location: "Downtown Manhattan",
    rating: 4.9,
    reviews: 2458,
    price: "$3,500",
    priceCaption: "Starts from",
    cta: "Book",
    image: "/W2.png",
  },
  {
<<<<<<< HEAD
    tag: "Top Rated",
    title: "Artisan Palate",
    categories: ["Catering", "Dining", "Menu"],
=======
    tag: "Top rated",
    title: "Artisan Palate",
    categories: ["Catering", "Private Dining", "Menu Design"],
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
    location: "Upper West Side",
    rating: 4.8,
    reviews: 1945,
    price: "$150/pp",
    priceCaption: "From",
    cta: "Book",
    image: "/W5.png",
  },
  {
<<<<<<< HEAD
    tag: "Top Rated",
    title: "Lumina Studios",
    categories: ["Photography", "Video", "Styling"],
    location: "Brooklyn Heights",
    rating: 4.9,
    reviews: 3021,
    price: "$2,200",
    priceCaption: "Starts from",
    cta: "Book",
    image: "/w4.png",
=======
    tag: "Premium",
    title: "Prime Venues",
    categories: ["Venue Rental", "Layout Design", "Catering"],
    location: "The Hamptons",
    rating: 4.8,
    reviews: 876,
    price: "$4,000",
    priceCaption: "From",
    cta: "Book",
    image: "/p1.png",
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
  },
  {
    tag: "Best Value",
    title: "Elegant Events Co",
<<<<<<< HEAD
    categories: ["Decoration", "Floral", "Coordination"],
=======
    categories: ["Decoration", "Floral Design", "Styling"],
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
    location: "Queens Center",
    rating: 4.7,
    reviews: 1523,
    price: "$1,200",
    priceCaption: "Starts from",
    cta: "Book",
    image: "/W1.png",
  },
];

<<<<<<< HEAD
const fallbackTips = [
  {
    category: "Wedding Planning",
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
];

const steps = [
  {
    title: "Discover Vendors",
    text: "Browse trusted services and compare options fast.",
    icon: "01",
  },
  {
    title: "Customize Details",
    text: "Select package pieces that fit your event and budget.",
    icon: "02",
  },
  {
    title: "Book Securely",
    text: "Confirm with secure checkout and receive clear status updates.",
    icon: "03",
  },
];

function toEventLabel(key) {
  const normalized = String(key || "other").toLowerCase();
  return eventTypeLabelMap[normalized] || "Special Event";
}

function toEventNote(key) {
  const normalized = String(key || "other").toLowerCase();
  return eventTypeNoteMap[normalized] || "Custom Event";
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

const eventTypes = computed(() => {
  if (!eventRows.value.length) return fullEventCatalog;

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
    fullEventCatalog,
    fullEventCatalog.length,
    (row) => row.key,
  );
});

const displayedEvents = computed(() => {
  const rows = eventTypes.value;
  return showAllEvents.value ? rows : rows.slice(0, 4);
});

const vendors = computed(() => {
  if (!eventRows.value.length) return fallbackVendors;

  const grouped = new Map();

  eventRows.value.forEach((item) => {
    const groupKey = item.vendor_id || `event-${item.id}`;
    if (!grouped.has(groupKey)) {
      grouped.set(groupKey, {
        title: item.vendor?.name || item.title || "Vendor",
        location: item.location || "Location TBD",
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

  return withFallbackRows(liveRows, fallbackVendors, 4, (row) => row.title);
});

const VENDOR_PAGE_SIZE = 4;
const displayedVendors = computed(() => {
  const rows = vendors.value;
  const maxStart = Math.max(0, rows.length - VENDOR_PAGE_SIZE);
  const start = Math.min(currentVendorIndex.value, maxStart);
  return rows.slice(start, start + VENDOR_PAGE_SIZE);
});

const tips = computed(() => {
  if (!eventRows.value.length) return fallbackTips;

  const liveRows = eventRows.value.slice(0, 3).map((item) => {
    const key = String(item.event_type || "other").toLowerCase();
    return {
      category: toEventLabel(key),
      title: `How to plan a great ${toEventLabel(key).toLowerCase()} experience`,
      meta: `From ${item.title || "Achar vendor"}`,
      image: eventImageByType[key] || eventImageByType.other,
    };
  });

  return withFallbackRows(liveRows, fallbackTips, 3, (row) => row.title);
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
=======
function mapDbServiceToCard(item) {
  const vendorName = String(item.vendor?.name || "Verified Vendor");
  const eventType = String(item.event_type || "service").replace(/_/g, " ");
  return {
    eventId: Number(item.id || 0) || null,
    vendorId: Number(item.vendor_id || 0) || null,
    vendorName,
    requestedEventType: String(item.event_type || "other"),
    tag: "New service",
    title: String(item.title || "Service Booking"),
    categories: [vendorName, eventType, String(item.description || "").trim() || "Available now"],
    location: item.location || "Location TBD",
    rating: 4.8,
    reviews: Number(item.bookings_count || 0),
    price: `$${Number(item.price || 0).toLocaleString()}`,
    priceCaption: "Starts from",
    cta: "Book",
    image: item.image_url || "/W2.png",
  };
}

async function loadVendorServices() {
  try {
    const result = await apiGet("events");
    const rows = Array.isArray(result.data) ? result.data : [];
    dbServices.value = rows.map(mapDbServiceToCard);
  } catch {
    dbServices.value = [];
  }
}

const displayedVendors = computed(() => {
  const source = dbServices.value.length ? dbServices.value : fallbackVendors;
  return source.slice(0, 4);
});

const steps = [
  { title: "Discover Vendors", text: "Browse verified services and compare offers from vendors.", icon: "Find" },
  { title: "Chat & Customize", text: "Ask questions, confirm details, and align the package with your event.", icon: "Chat" },
  { title: "Book Securely", text: "Send a booking request and continue through the platform flow.", icon: "Book" },
];

const tips = [
  { category: "Wedding Planning", title: "5 Secrets to a Stress-Free Wedding Ceremony", meta: "By Claire Muller - 6 min read", image: "/W1.png" },
  { category: "Corporate", title: "Choosing the Right Venue for Corporate Galas", meta: "By Team Achar - 4 min read", image: "/p1.png" },
  { category: "Decor & Styling", title: "Minimalist Tablescapes That Still Feel Luxe", meta: "By In-house Stylists - 5 min read", image: "/p2.png" },
];

const showBookingModal = ref(false);
const selectedVendor = ref(null);
const bookingSuccess = ref(false);
const bookingSubmitting = ref(false);
const bookingError = ref("");
const bookingForm = ref({
  fullName: "",
  email: "",
  eventDate: "",
  guests: 50,
  notes: "",
});

function openBookingModal(vendor) {
  selectedVendor.value = vendor;
  bookingSuccess.value = false;
  bookingError.value = "";
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
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
<<<<<<< HEAD
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
        description: bookingForm.value.notes || "Booking request from homepage",
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
=======
  bookingSubmitting.value = false;
  bookingError.value = "";
}

async function submitBooking() {
  if (!selectedVendor.value?.eventId) {
    bookingError.value = "This service is not connected to a live vendor listing yet.";
    return;
  }

  bookingSubmitting.value = true;
  bookingError.value = "";

  try {
    await apiPost("bookings", {
      event_id: selectedVendor.value.eventId,
      quantity: Number(bookingForm.value.guests || 1),
      customer_name: String(bookingForm.value.fullName || "").trim(),
      customer_email: String(bookingForm.value.email || "").trim(),
      service_name: selectedVendor.value.title,
      requested_event_type: selectedVendor.value.requestedEventType || "other",
    });

    bookingSuccess.value = true;
  } catch (error) {
    bookingError.value = error?.message || "Could not create booking right now.";
  } finally {
    bookingSubmitting.value = false;
  }
}

onMounted(() => {
  void loadVendorServices();
});
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
</script>

<template>
  <div class="home-page">
    <PublicNavbar />

    <section class="hero">
      <div class="hero__bg-shape"></div>
      <div class="hero__grid">
        <div class="hero__text">
          <h1>
            Your Perfect Event, <span class="highlight">Orchestrated</span> to
            Perfection
          </h1>
          <p class="lede">
            Discover and book trusted vendors, venues, and specialists for both
            traditional and modern celebrations.
          </p>
          <router-link class="search-btn hero-explore-btn" to="/services/packages">
            <span>Explore Services & Packages</span>
<<<<<<< HEAD
            <span class="hero-explore-icon" aria-hidden="true">-&gt;</span>
=======
            <span class="hero-explore-icon" aria-hidden="true">&rarr;</span>
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
          </router-link>
        </div>
      </div>
    </section>

    <section id="services" class="section section--events">
      <div class="section__header">
        <div class="events-header-copy">
          <p class="eyebrow">Browse by Event Type</p>
          <h2>Perfectly suited for those extra-special occasions</h2>
          <p class="events-subtitle">
            Live categories from available events and vendors.
          </p>
        </div>
<<<<<<< HEAD
        <button class="link-button event-toggle-btn" @click="showAllEvents = !showAllEvents">
          {{ showAllEvents ? "Show less" : "Show all events" }}
          <span aria-hidden="true">&gt;</span>
=======
        <button class="link-button" @click="showAllEvents = !showAllEvents">
          {{ showAllEvents ? "Show less" : "See all events" }} &rarr;
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
        </button>
      </div>

      <div v-if="dataLoadFailed" class="event-load-note">
        Live data is temporarily unavailable. Showing fallback results.
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
            <p class="event-cta">Explore style &gt;</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section section--vendors">
      <div class="section__header">
        <div>
          <p class="eyebrow">Recommended Vendors</p>
          <h2>Handpicked services, ready to book</h2>
        </div>
<<<<<<< HEAD
        <div class="carousel-nav">
          <button aria-label="Previous" class="pill-btn" @click="prevVendors">&lt;</button>
          <button aria-label="Next" class="pill-btn" @click="nextVendors">&gt;</button>
        </div>
=======
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
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
<<<<<<< HEAD
              <span class="star">*</span>
              <strong>{{ vendor.rating }}</strong>
              <span class="reviews">{{ vendor.reviews?.toLocaleString() || "0" }} reviews</span>
=======
              <span class="star">*</span> <strong>{{ vendor.rating }}</strong>
              <span class="reviews"
                >{{ vendor.reviews?.toLocaleString() || "0" }} reviews</span
              >
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
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
<<<<<<< HEAD

    <div v-if="showBookingModal" class="booking-modal-overlay" @click="closeBookingModal">
=======
    <div
      v-if="showBookingModal"
      class="booking-modal-overlay"
      @click="closeBookingModal"
    >
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
      <div v-if="selectedVendor" class="booking-modal" @click.stop>
        <button type="button" class="booking-close" @click="closeBookingModal">&times;</button>
        <div class="booking-modal-layout">
          <div class="booking-vendor-preview">
            <img :src="selectedVendor.image" :alt="selectedVendor.title" class="vendor-preview-image" />
            <div class="vendor-preview-info">
              <p class="eyebrow">You are booking</p>
              <h4>{{ selectedVendor.title }}</h4>
              <p class="vendor-location">{{ selectedVendor.location }}</p>
              <div class="vendor-rating">
                <span class="star">*</span>
                <strong>{{ selectedVendor.rating }}</strong>
<<<<<<< HEAD
                <span class="reviews">({{ selectedVendor.reviews?.toLocaleString() }} reviews)</span>
              </div>
            </div>
          </div>

          <div class="booking-form-section">
            <div v-if="bookingSuccess" class="booking-success-state">
              <h3>Booking Request Sent</h3>
=======
                <span class="reviews"
                  >({{ selectedVendor.reviews?.toLocaleString() }} reviews)</span
                >
              </div>
            </div>
          </div>
          <div class="booking-form-section">
            <div v-if="bookingSuccess" class="booking-success-state">
              <div class="success-icon">Sent</div>
              <h3>Booking Request Sent!</h3>
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
              <p>
                Your request to book <strong>{{ selectedVendor.title }}</strong> has been sent.
                The vendor will contact you by email.
              </p>
              <button type="button" class="primary-btn" @click="closeBookingModal">Done</button>
            </div>

            <form v-else class="booking-modal-form" @submit.prevent="submitBooking">
              <div class="booking-modal-header">
                <h3>Confirm Your Details</h3>
                <p>Fill out the form below to send a booking request.</p>
              </div>

              <div class="form-group">
                <label for="fullName">Full name</label>
                <input id="fullName" v-model.trim="bookingForm.fullName" type="text" required />
              </div>

              <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" v-model.trim="bookingForm.email" type="email" required />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="eventDate">Event date</label>
                  <input id="eventDate" v-model="bookingForm.eventDate" type="date" required />
                </div>
                <div class="form-group">
                  <label for="guests">Number of guests</label>
                  <input id="guests" v-model.number="bookingForm.guests" type="number" min="1" required />
                </div>
              </div>

              <div class="form-group">
                <label for="notes">Additional notes</label>
                <textarea id="notes" v-model.trim="bookingForm.notes" rows="3"></textarea>
              </div>
<<<<<<< HEAD

=======
              <p
                v-if="bookingError"
                style="margin: 0; color: #b91c1c; font-weight: 600"
              >
                {{ bookingError }}
              </p>
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
              <div class="booking-modal-actions">
                <button
                  type="button"
                  class="ghost-btn booking-cancel-btn"
                  @click="closeBookingModal"
                >
                  Cancel
                </button>
<<<<<<< HEAD
                <button
                  type="submit"
                  class="primary-btn booking-submit-btn"
                >
                  Send Booking Request
=======
                <button type="submit" class="primary-btn" :disabled="bookingSubmitting">
                  {{ bookingSubmitting ? "Sending..." : "Send Booking Request" }}
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <section class="section steps">
      <div class="section__header center">
        <p class="eyebrow">Planning Made Simple</p>
        <h2>The easy steps to bring your dream event to life</h2>
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
          <p class="eyebrow">Latest Planning Tips</p>
          <h2>Ideas and advice from our planning pros</h2>
        </div>
        <router-link class="link" to="/customization">Read all articles -&gt;</router-link>
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
        <p class="eyebrow light">Start Planning</p>
        <h2>Ready to Plan Your Masterpiece?</h2>
        <p class="cta-text">
          Join planners and vendors on Achar. Start your journey today.
        </p>
        <div class="cta-actions">
          <router-link class="primary-btn large" to="/booking">Get Started for Free</router-link>
          <router-link class="ghost-btn light large" to="/customization">List Your Business</router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
