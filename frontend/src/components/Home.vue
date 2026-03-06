<script setup>
import { ref, computed } from "vue";
import PublicNavbar from "./PublicNavbar.vue";

const appLogoSrc = ref(
  localStorage.getItem("achar_brand_logo") || "/achar-logo.png",
);

function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}

const showAllEvents = ref(false);

const eventTypes = [
  {
    name: "Wedding",
    note: "Event Planning",
    image: "https://i.pinimg.com/736x/74/c4/10/74c4108355760ed6c7736a8bd0df35b0.jpg",
    fallback: "/W1.png",
  },
  {
    name: "Baby Shower",
    note: "Event Planning",
    image: "https://www.evite.com/cms_proxy/67afcdfc6e901cdfb811bece/6871230471d83ea5737bb74e_Baby%20shower%20etiquette.png",
    fallback: "/W2.png",
  },
  {
    name: "House Blessing",
    note: "Event Planning",
    image: "https://i.pinimg.com/736x/38/d4/43/38d44367a5e603f64dd6d2f75dcef1c9.jpg",
    fallback: "/W3.png",
  },
  {
    name: "Monk Ceremony",
    note: "Traditional Ritual",
    image: "https://image.freshnewsasia.com/2021/id-022/fn-2024-08-07-09-05-06-0.jpg",
    fallback: "/W2.png",
  },
  {
    name: "Company Party",
    note: "Event Planning",
    image: "https://houseofevents.uk/wp-content/uploads/2024/02/corporate-party1.jpg",
    fallback: "/p1.png",
  },
  {
    name: "Engagement",
    note: "Event Planning",
    image: "https://www.gunitevents.com/assets/images/Corporate-Event-Decorators-in-Bangalore/Images/Conferences%20and%20Seminars.webp",
    fallback: "/p2.png",
  },
  {
    name: "Birthday",
    note: "Event Planning",
    image: "https://m.media-amazon.com/images/I/718Ri9zvBrL._AC_UF894,1000_QL80_.jpg",
    fallback: "/W5.png",
  },
  {
    name: "Anniversary",
    note: "Event Planning",
    image: "https://m.media-amazon.com/images/I/71ka4kNhuQL._AC_UF894,1000_QL80_.jpg",
    fallback: "/w4.png",
  },
  {
    name: "Bridal Shower",
    note: "Event Planning",
    image: "https://symphonyevents.com.au/wp-content/uploads/2024/01/Arched-Bridal-Shower-Decoration-1024x1024.jpg",
    fallback: "/W2.png",
  },
  {
    name: "Graduation",
    note: "Celebration",
    image: "https://cdn.myportfolio.com/71a9ece9-52a1-484d-b494-c52179b9fc5b/57c4e865-5f51-4a2b-b1d9-dba8c527483d_rw_1920.jpg?h=f4f217d51b9106059c2b71992425462e",
    fallback: "/p1.png",
  },
  {
    name: "Retirement Party",
    note: "Celebration",
    image: "https://i5.walmartimages.com/asr/d8754314-ef12-4af8-9b6e-306ef8c62f50.b0eb9b913a2fe5f2a9c6fbdee4e4e8f8.jpeg?odnHeight=768&odnWidth=768&odnBg=FFFFFF",
    fallback: "/W1.png",
  },
  {
    name: "Product Launch",
    note: "Corporate Event",
    image: "https://omart.org/wp-content/uploads/2024/11/private_and_corp-4-768x503.jpg",
    fallback: "/p2.png",
  },
  {
    name: "Corporate Gala",
    note: "Business Event",
    image: "https://sipipa.com/blog/wp-content/uploads/2018/10/Catering-for-Businesses-Meetings-and-Events.jpg",
    fallback: "/W5.png",
  },
  {
    name: "Rehearsal Dinner",
    note: "Wedding Related",
    image: "https://static.wixstatic.com/media/df41f3_33c851a12947473ebc56fa16cbb59bab~mv2.png/v1/fill/w_736,h_964,al_c,q_90,enc_avif,quality_auto/df41f3_33c851a12947473ebc56fa16cbb59bab~mv2.png",
    fallback: "/w4.png",
  },
  {
    name: "Bachelorette Party",
    note: "Celebration",
    image: "https://i.pinimg.com/originals/6b/0f/9c/6b0f9cf1f6a34b38b0d8d5c3d8715ebf.jpg",
    fallback: "/W3.png",
  },
  {
    name: "Grand Opening",
    note: "Business Event",
    image: "https://i.pinimg.com/1200x/44/38/85/443885e7268f12b6e3d1653b8f7b326d.jpg",
    fallback: "/p1.png",
  },
  {
    name: "Team Building",
    note: "Corporate Event",
    image: "https://i.pinimg.com/1200x/d8/ef/cb/d8efcb5da60187c00c93bd5d30fd90e4.jpg",
    fallback: "/p2.png",
  },
  {
    name: "Seminar",
    note: "Professional Event",
    image: "https://i.pinimg.com/736x/06/f2/65/06f265afbe9d78bdee9c81c246dd55a9.jpg",
    fallback: "/W1.png",
  },
  {
    name: "Award Ceremony",
    note: "Corporate Event",
    image: "https://www.hsslnyc.org/apps/api/images/647f4a7ba77e4a0008eb8376/src",
    fallback: "/W5.png",
  },
];

const displayedEvents = computed(() => {
  return showAllEvents.value ? eventTypes : eventTypes.slice(0, 4);
});

// vendor carousel controls
const currentVendorIndex = ref(0);
const VENDOR_PAGE_SIZE = 4;
const displayedVendors = computed(() => {
  return vendors.slice(
    currentVendorIndex.value,
    currentVendorIndex.value + VENDOR_PAGE_SIZE,
  );
});

function nextVendors() {
  if (currentVendorIndex.value + VENDOR_PAGE_SIZE < vendors.length) {
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

const vendors = [
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
    image: "public/W2.png",
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
    image: "public/W5.png",
  },
  {
    tag: "Top rated",
    title: "Lumina Studios",
    categories: ["Photography", "Cinematography", "Videography"],
    location: "Brooklyn Heights",
    rating: 4.9,
    reviews: 3021,
    price: "$2,200",
    priceCaption: "Starts from",
    cta: "Book",
    image: "public/w4.png",
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
    image: "public/W1.png",
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
    image: "public/p1.png",
  },
  {
    tag: "Top rated",
    title: "Blooming Bliss Florals",
    categories: ["Floral Design", "Bouquets", "Installation"],
    location: "Midtown East",
    rating: 4.9,
    reviews: 2156,
    price: "$800",
    priceCaption: "Starts from",
    cta: "Book",
    image: "public/W3.png",
  },
  {
    tag: "Best Value",
    title: "Harmony Music Collective",
    categories: ["Live Music", "DJ Services", "Entertainment"],
    location: "Brooklyn",
    rating: 4.7,
    reviews: 1834,
    price: "$600",
    priceCaption: "From",
    cta: "Book",
    image: "public/p2.png",
  },
  {
    tag: "Top rated",
    title: "Beauty & Glamour Studio",
    categories: ["Makeup", "Hair Styling", "Bridal Services"],
    location: "Manhattan",
    rating: 4.8,
    reviews: 2342,
    price: "$350",
    priceCaption: "Per Person from",
    cta: "Book",
    image: "public/W1.png",
  },
  {
    tag: "Premium",
    title: "Luxe Rentals & Linens",
    categories: ["Table Rentals", "Linens", "Décor Elements"],
    location: "Long Island City",
    rating: 4.9,
    reviews: 1567,
    price: "$500",
    priceCaption: "Starts from",
    cta: "Book",
    image: "public/W5.png",
  },
  {
    tag: "Top rated",
    title: "Sweet Confections Bakery",
    categories: ["Custom Cakes", "Desserts", "Pastries"],
    location: "Upper East Side",
    rating: 4.8,
    reviews: 2089,
    price: "$250",
    priceCaption: "Per Tier from",
    cta: "Book",
    image: "public/w4.png",
  },
  {
    tag: "Top rated",
    title: "Prestige Event Planning",
    categories: [
      "Event Coordination",
      "Budget Management",
      "Timeline Planning",
    ],
    location: "Midtown Manhattan",
    rating: 4.9,
    reviews: 2567,
    price: "$1,500",
    priceCaption: "Planning Fee from",
    cta: "Book",
    image: "public/W2.png",
  },
  {
    tag: "Top rated",
    title: "ProAudio & Lighting Co",
    categories: ["Sound System", "Stage Lighting", "AV Equipment"],
    location: "Queens",
    rating: 4.8,
    reviews: 1923,
    price: "$900",
    priceCaption: "Setup from",
    cta: "Book",
    image: "public/p1.png",
  },
  {
    tag: "Best Value",
    title: "Elegant Invitations Studio",
    categories: ["Invitation Design", "Printing", "Stationery"],
    location: "Brooklyn",
    rating: 4.7,
    reviews: 1456,
    price: "$200",
    priceCaption: "Per Set from",
    cta: "Book",
    image: "public/W3.png",
  },
  {
    tag: "Premium",
    title: "Luxe Transportation Services",
    categories: ["Airport Pickup", "Valet Service", "Guest Transportation"],
    location: "Manhattan",
    rating: 4.9,
    reviews: 1765,
    price: "$75",
    priceCaption: "Per Hour from",
    cta: "Book",
    image: "public/p2.png",
  },
  {
    tag: "Top rated",
    title: "Grand Tent & Canopy Rentals",
    categories: ["Tent Rentals", "Canopy Setup", "Weather Protection"],
    location: "New Jersey",
    rating: 4.8,
    reviews: 1342,
    price: "$400",
    priceCaption: "Starts from",
    cta: "Book",
    image: "public/W1.png",
  },
  {
    tag: "Best Value",
    title: "Premium Bar & Beverage Service",
    categories: ["Bar Service", "Beverage Selection", "Bartending"],
    location: "Manhattan",
    rating: 4.7,
    reviews: 2134,
    price: "$35",
    priceCaption: "Per Person from",
    cta: "Book",
    image: "public/W5.png",
  },
  {
    tag: "Top rated",
    title: "Corporate Event Specialists",
    categories: ["Corporate Planning", "Conference Setup", "Team Events"],
    location: "Midtown",
    rating: 4.9,
    reviews: 2301,
    price: "$2,000",
    priceCaption: "Event Planning from",
    cta: "Book",
    image: "public/w4.png",
  },
  {
    tag: "Top rated",
    title: "Hospitality & Guest Services",
    categories: ["Guest Reception", "Coat Check", "Concierge Services"],
    location: "Upper East Side",
    rating: 4.8,
    reviews: 1678,
    price: "$25",
    priceCaption: "Per Person from",
    cta: "Book",
    image: "public/W2.png",
  },
  {
    tag: "Premium",
    title: "Sacred Rituals & Blessings",
    categories: [
      "House Blessing",
      "Ritual Services",
      "Traditional Ceremonies",
      "Monk Ceremony",
    ],
    location: "Queens",
    rating: 4.9,
    reviews: 987,
    price: "$400",
    priceCaption: "Service from",
    cta: "Book",
    image: "public/p1.png",
  },
  {
    tag: "Top rated",
    title: "Monk Ceremony Package",
    categories: ["Monk Ceremony", "Traditional Services", "Blessing"],
    location: "Phnom Penh",
    rating: 4.8,
    reviews: 643,
    price: "$300",
    priceCaption: "Service from",
    cta: "Book",
    image: "public/W3.png",
  },
  {
    tag: "Best Value",
    title: "Kids Paradise Entertainment",
    categories: [
      "Kids Entertainment",
      "Games & Activities",
      "Character Meet & Greet",
    ],
    location: "Brooklyn",
    rating: 4.8,
    reviews: 1523,
    price: "$300",
    priceCaption: "Per Hour from",
    cta: "Book",
    image: "public/W3.png",
  },
  {
    tag: "Top rated",
    title: "Gourmet Catering Elite",
    categories: ["Fine Dining Catering", "Menu Customization", "Chef Services"],
    location: "Manhattan",
    rating: 4.9,
    reviews: 2678,
    price: "$85",
    priceCaption: "Per Person from",
    cta: "Book",
    image: "public/p2.png",
  },
];

const steps = [
  {
    title: "Discover Vendors",
    text: "Browse thousands of curated local services to match your vibe.",
    icon: "🔍",
  },
  {
    title: "Chat & Customize",
    text: "Message vendors, compare options, and personalize every detail.",
    icon: "💬",
  },
  {
    title: "Book Securely",
    text: "Lock in dates with confidence—secure payments and reminders.",
    icon: "🛡️",
  },
];

const tips = [
  {
    category: "Wedding Planning",
    title: "5 Secrets to a Stress-Free Wedding Ceremony",
    meta: "By Claire Muller • 6 min read",
    image: "https://i.pinimg.com/736x/22/44/4e/22444ef729ed5c26d37bdf9754c7621f.jpg",
  },
  {
    category: "Corporate",
    title: "Choosing the Right Venue for Corporate Galas",
    meta: "By Team Achar • 4 min read",
    image: "https://i.pinimg.com/1200x/b6/59/a0/b659a0337ceb904f7370e52e6bd0ae50.jpg",
  },
  {
    category: "Decor & Styling",
    title: "Minimalist Tablescapes That Still Feel Luxe",
    meta: "By In-house Stylists • 5 min read",
    image: "public/p2.png",
  },
];

const showBookingModal = ref(false);
const selectedVendor = ref(null);
const bookingSuccess = ref("");
const bookingForm = ref({
  fullName: "",
  email: "",
  eventDate: "",
  guests: 50,
  notes: "",
});

function openBookingModal(vendor) {
  selectedVendor.value = vendor;
  bookingSuccess.value = "";
  showBookingModal.value = true;
}

function closeBookingModal() {
  showBookingModal.value = false;
}

function submitBooking() {
  bookingSuccess.value = `Booking request sent for ${selectedVendor.value?.title || "service"}.`;
  bookingForm.value = {
    fullName: "",
    email: "",
    eventDate: "",
    guests: 50,
    notes: "",
  };
}
</script>

<template>
  <div class="home-page">
    <PublicNavbar />

    <section class="hero">
      <div class="hero__bg-shape"></div>
      <div class="hero__grid">
        <div class="hero__text">
          <h1>
            Your Perfect Event,
            <span class="highlight">Orchestrated</span>
            to Perfection
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
            <span class="hero-explore-icon" aria-hidden="true">→</span>
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
          {{ showAllEvents ? "Show less" : "See all events" }} →
        </button>
      </div>
      <div class="event-grid">
        <div
          class="event-card"
          v-for="event in displayedEvents"
          :key="event.name"
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
        <div class="carousel-nav">
          <button aria-label="Previous" class="pill-btn" @click="prevVendors">
            ‹
          </button>
          <button aria-label="Next" class="pill-btn" @click="nextVendors">
            ›
          </button>
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
              <span class="star">★</span>
              <strong>{{ vendor.rating }}</strong>
              <span class="reviews"
                >{{ vendor.reviews?.toLocaleString() || "4,758" }} reviews</span
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
                @click="openBookingModal(vendor)"
              >
                {{ vendor.cta }}
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <div
      v-if="showBookingModal"
      class="booking-modal-overlay"
      @click="closeBookingModal"
    >
      <div class="booking-modal" @click.stop>
        <div class="booking-modal-header">
          <h3>Book {{ selectedVendor?.title }}</h3>
          <button
            type="button"
            class="booking-close"
            @click="closeBookingModal"
          >
            ×
          </button>
        </div>
        <form class="booking-modal-form" @submit.prevent="submitBooking">
          <label>
            Full name
            <input v-model.trim="bookingForm.fullName" type="text" required />
          </label>
          <label>
            Email
            <input v-model.trim="bookingForm.email" type="email" required />
          </label>
          <label>
            Event date
            <input v-model="bookingForm.eventDate" type="date" required />
          </label>
          <label>
            Number of guests
            <input
              v-model.number="bookingForm.guests"
              type="number"
              min="1"
              required
            />
          </label>
          <label>
            Notes
            <textarea
              v-model.trim="bookingForm.notes"
              rows="3"
              placeholder="Add preferences or requests"
            ></textarea>
          </label>
          <div class="booking-modal-actions">
            <button type="button" class="ghost-btn" @click="closeBookingModal">
              Cancel
            </button>
            <button type="submit" class="primary-btn">Confirm Booking</button>
          </div>
          <p v-if="bookingSuccess" class="booking-success">
            {{ bookingSuccess }}
          </p>
        </form>
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

    <footer id="contact" class="footer">
      <div class="footer__grid">
        <div>
          <div class="brand">
            <img
              class="brand-logo"
              :src="appLogoSrc"
              alt="Achar logo"
              @error="onLogoError"
            />
            <span class="brand-name">Achar.</span>
          </div>
          <p class="footer-copy">
            Achar makes event planning effortless with curated vendors, smart
            tools, and secure bookings.
          </p>
          <div class="social">
            <a href="#" aria-label="LinkedIn">in</a>
            <a href="#" aria-label="Facebook">f</a>
            <a href="#" aria-label="Instagram">ig</a>
            <a href="#" aria-label="Twitter">x</a>
          </div>
        </div>
        <div>
          <p class="footer-title">For Partners</p>
          <router-link to="/dashboard" class="footer-link"
            >Enterprise Solutions</router-link
          >
          <router-link to="/services" class="footer-link"
            >Affiliate Program</router-link
          >
          <router-link to="/legacy-app" class="footer-link"
            >Corporate Packages</router-link
          >
        </div>
        <div>
          <p class="footer-title">For Vendors</p>
          <router-link to="/dashboard" class="footer-link"
            >Join as Vendor</router-link
          >
          <router-link to="/services" class="footer-link">Pricing</router-link>
          <router-link to="/booking" class="footer-link"
            >Help Center</router-link
          >
        </div>
        <div>
          <p class="footer-title">Subscribe</p>
          <div class="subscribe">
            <input type="email" placeholder="Email address" />
            <button aria-label="Subscribe">➜</button>
          </div>
        </div>
      </div>
      <div class="footer__meta">
        <span>© 2026 Achar Event Booking</span>
        <div class="meta-links">
          <router-link to="/home">Terms of Policy</router-link>
          <router-link to="/home">Terms of Service</router-link>
          <router-link to="/home">Cookies</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
