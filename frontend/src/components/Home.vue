<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import PublicNavbar from "./PublicNavbar.vue";
import { apiGet } from "../features/apiClient";

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
    location: "Downtown Manhattan",
    rating: 4.9,
    reviews: 2458,
    price: "$3,500",
    priceCaption: "Starts from",
    cta: "Book",
    image: "/W2.png",
  },
  {
    tag: "Top rated",
    title: "Artisan Palate",
    categories: ["Catering", "Private Dining", "Menu Design"],
    location: "Upper West Side",
    rating: 4.8,
    reviews: 1945,
    price: "$150/pp",
    priceCaption: "From",
    cta: "Book",
    image: "/W5.png",
  },
  {
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
  },
  {
    tag: "Best Value",
    title: "Elegant Events Co",
    categories: ["Decoration", "Floral Design", "Styling"],
    location: "Queens Center",
    rating: 4.7,
    reviews: 1523,
    price: "$1,200",
    priceCaption: "Starts from",
    cta: "Book",
    image: "/W1.png",
  },
];

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

function openServiceBookingForm(vendor) {
  const query = {};
  const title = String(vendor?.title || "").trim();
  const eventType = String(vendor?.requestedEventType || "").trim();
  const eventId = Number(vendor?.eventId || 0);

  if (title) query.q = title;
  if (eventType && eventType !== "other") query.event = eventType;
  if (Number.isFinite(eventId) && eventId > 0) {
    query.prebookEventId = String(eventId);
  }

  router.push({ path: "/services/packages", query });
}

onMounted(() => {
  void loadVendorServices();
});
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
            Discover and book the finest venues, caterers, and specialists to
            master your traditional and modern ceremonies.
          </p>
          <router-link
            class="search-btn hero-explore-btn"
            to="/services/packages"
          >
            <span>Explore Services & Packages</span>
            <span class="hero-explore-icon" aria-hidden="true">&rarr;</span>
          </router-link>
        </div>
      </div>
    </section>
    <section id="services" class="section">
      <div class="section__header">
        <div>
          <p class="eyebrow">Browse by Event Type</p>
          <h2>Perfectly suited for those extra-special occasions</h2>
        </div>
        <button class="link-button" @click="showAllEvents = !showAllEvents">
          {{ showAllEvents ? "Show less" : "See all events" }} &rarr;
        </button>
      </div>
      <div class="event-grid">
        <div
          class="event-card"
          v-for="event in displayedEvents"
          :key="event.name"
          role="button"
          tabindex="0"
          @click="goToEvent(event)"
          @keyup.enter="goToEvent(event)"
          style="cursor: pointer"
        >
          <div class="event-img-container">
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
          </div>
        </div>
      </div>
    </section>
    <section class="section">
      <div class="section__header">
        <div>
          <p class="eyebrow">Recommended Vendors</p>
          <h2>Handpicked services, ready to book</h2>
        </div>
      </div>
      <div class="vendor-grid">
        <article
          class="vendor-card"
          v-for="vendor in displayedVendors"
          :key="vendor.title"
        >
          <span class="vendor-tag">{{ vendor.tag }}</span>
          <div class="vendor-media">
            <img :src="vendor.image" :alt="vendor.title" />
          </div>
          <div class="vendor-body">
            <div>
              <p class="vendor-title">{{ vendor.title }}</p>
              <p class="vendor-meta">
                <span
                  class="dot"
                  v-for="category in vendor.categories"
                  :key="category"
                  >{{ category }}</span
                >
                <span class="location">{{ vendor.location }}</span>
              </p>
            </div>
            <div class="vendor-rating">
              <span class="star">*</span> <strong>{{ vendor.rating }}</strong>
              <span class="reviews"
                >{{ vendor.reviews?.toLocaleString() || "0" }} reviews</span
              >
            </div>
            <div class="vendor-footer">
              <div class="pricing">
                <p class="price-caption">{{ vendor.priceCaption }}</p>
                <p class="price">{{ vendor.price }}</p>
              </div>
              <button
                type="button"
                class="outline-btn"
                @click="openServiceBookingForm(vendor)"
              >
                {{ vendor.cta }}
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>
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
        <router-link class="link" to="/customization"
          >Read all articles -></router-link
        >
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
          Join thousands of planners and vendors on Achar. Start your journey
          today.
        </p>
        <div class="cta-actions">
          <router-link class="primary-btn large" to="/booking"
            >Get Started for Free</router-link
          >
          <router-link class="ghost-btn light large" to="/customization"
            >List Your Business</router-link
          >
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
