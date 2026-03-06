<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import PublicNavbar from "./PublicNavbar.vue";
<<<<<<< HEAD

const FAVORITE_VENDORS_KEY = 'achar_favorite_vendors'

// Load saved favorites from localStorage
function loadFavoriteVendors() {
  const saved = localStorage.getItem(FAVORITE_VENDORS_KEY)
  if (saved) {
    try {
      return JSON.parse(saved)
    } catch {
      return []
    }
  }
  return []
}

// Save favorites to localStorage
function saveFavoriteVendors(favorites) {
  localStorage.setItem(FAVORITE_VENDORS_KEY, JSON.stringify(favorites))
}

const favoriteVendorTitles = ref(loadFavoriteVendors())

function isVendorFavorite(title) {
  return favoriteVendorTitles.value.includes(title)
}

function toggleVendorFavorite(title) {
  if (favoriteVendorTitles.value.includes(title)) {
    favoriteVendorTitles.value = favoriteVendorTitles.value.filter(t => t !== title)
  } else {
    favoriteVendorTitles.value = [...favoriteVendorTitles.value, title]
  }
  saveFavoriteVendors(favoriteVendorTitles.value)
}

=======
>>>>>>> 9d35ffa2d1ff350b05b246cd65a5b3509f643bb5
const appLogoSrc = ref(
  localStorage.getItem("achar_brand_logo") || "/achar-logo.png",
);
function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}
const showAllEvents = ref(false);
const router = useRouter();
const eventTypes = [
  {
    name: "Wedding",
    note: "Event Planning",
    image: "/event-cards/wedding-stage.jpg",
    fallback: "/W1.png",
  },
  {
    name: "Baby Shower",
    note: "Event Planning",
    image: "/event-cards/orange-flowers.jpg",
    fallback: "/W2.png",
  },
  {
    name: "House Blessing",
    note: "Event Planning",
    image: "/house.png",    fallback: "/W3.png",
  },
  {
    name: "Monk Ceremony",
    note: "Traditional Ritual",
    image: "/event-cards/house-blessing-offering.jpg",
    fallback: "/W2.png",
  },
  {
    name: "Company Party",
    note: "Event Planning",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/p1.png",
  },
  {
    name: "Engagement",
    note: "Event Planning",
    image: "/event-cards/engagement-attire.jpg",
    fallback: "/p2.png",
  },
  {
    name: "Birthday",
    note: "Event Planning",
    image: "/W5.png",
    fallback: "/W5.png",
  },
  {
    name: "Anniversary",
    note: "Event Planning",
    image: "/event-cards/anniversary-arch.jpg",
    fallback: "/w4.png",
  },
  {
    name: "Bridal Shower",
    note: "Event Planning",
    image: "/event-cards/engagement-attire.jpg",
    fallback: "/W2.png",
  },
  {
    name: "Graduation",
    note: "Celebration",
    image: "/event-cards/anniversary-arch.jpg",
    fallback: "/p1.png",
  },
  {
    name: "Retirement Party",
    note: "Celebration",
    image: "/event-cards/house-blessing-offering.jpg",
    fallback: "/W1.png",
  },
  {
    name: "Product Launch",
    note: "Corporate Event",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/p2.png",
  },
  {
    name: "Corporate Gala",
    note: "Business Event",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/W5.png",
  },
  {
    name: "Rehearsal Dinner",
    note: "Wedding Related",
    image: "/event-cards/wedding-stage.jpg",
    fallback: "/w4.png",
  },
  {
    name: "Bachelorette Party",
    note: "Celebration",
    image: "/event-cards/wedding-stage.jpg",
    fallback: "/W3.png",
  },
  {
    name: "Grand Opening",
    note: "Business Event",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/p1.png",
  },
  {
    name: "Team Building",
    note: "Corporate Event",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/p2.png",
  },
  {
    name: "Seminar",
    note: "Professional Event",
    image: "/event-cards/house-blessing-offering.jpg",
    fallback: "/W1.png",
  },
  {
    name: "Award Ceremony",
    note: "Corporate Event",
    image: "/event-cards/ceremony-hall.jpg",
    fallback: "/W5.png",
  },
];
const displayedEvents = computed(() => {
  return showAllEvents.value ? eventTypes : eventTypes.slice(0, 4);
}); // vendor carousel controlsconst currentVendorIndex = ref(0);const VENDOR_PAGE_SIZE = 4;const displayedVendors = computed(() => {  return vendors.slice(    currentVendorIndex.value,    currentVendorIndex.value + VENDOR_PAGE_SIZE,  );});function nextVendors() {  if (currentVendorIndex.value + VENDOR_PAGE_SIZE < vendors.length) {    currentVendorIndex.value += VENDOR_PAGE_SIZE;  }}function prevVendors() {  if (currentVendorIndex.value - VENDOR_PAGE_SIZE >= 0) {    currentVendorIndex.value -= VENDOR_PAGE_SIZE;  }}function onEventCardImageError(event, fallback) {  event.target.src = fallback;}function goToEvent(event) {  // convert name to key used in packageCatalogByEventType  const key = event.name.toLowerCase().replace(/\s+/g, "_");  router.push({ path: "/services/packages", query: { event: key } });}const vendors = [  {    tag: "Top rated",    title: "Skyline Grand Atrium",    categories: ["Wedding Planning", "Event Design", "Venue"],    location: "Downtown Manhattan",    rating: 4.9,    reviews: 2458,    price: "$3,500",    priceCaption: "Starts from",    cta: "Book",    image: "/W2.png",  },  {    tag: "Top rated",    title: "Artisan Palate",    categories: ["Catering", "Private Dining", "Menu Design"],    location: "Upper West Side",    rating: 4.8,    reviews: 1945,    price: "$150/pp",    priceCaption: "From",    cta: "Book",    image: "/W5.png",  },  {    tag: "Top rated",    title: "Lumina Studios",    categories: ["Photography", "Cinematography", "Videography"],    location: "Brooklyn Heights",    rating: 4.9,    reviews: 3021,    price: "$2,200",    priceCaption: "Starts from",    cta: "Book",    image: "/w4.png",  },  {    tag: "Best Value",    title: "Elegant Events Co",    categories: ["Decoration", "Floral Design", "Styling"],    location: "Queens Center",    rating: 4.7,    reviews: 1523,    price: "$1,200",    priceCaption: "Starts from",    cta: "Book",    image: "/W1.png",  },  {    tag: "Premium",    title: "Prime Venues",    categories: ["Venue Rental", "Layout Design", "Catering"],    location: "The Hamptons",    rating: 4.8,    reviews: 876,    price: "$4,000",    priceCaption: "From",    cta: "Book",    image: "/p1.png",  },  {    tag: "Top rated",    title: "Blooming Bliss Florals",    categories: ["Floral Design", "Bouquets", "Installation"],    location: "Midtown East",    rating: 4.9,    reviews: 2156,    price: "$800",    priceCaption: "Starts from",    cta: "Book",    image: "/W3.png",  },  {    tag: "Best Value",    title: "Harmony Music Collective",    categories: ["Live Music", "DJ Services", "Entertainment"],    location: "Brooklyn",    rating: 4.7,    reviews: 1834,    price: "$600",    priceCaption: "From",    cta: "Book",    image: "/p2.png",  },  {    tag: "Top rated",    title: "Beauty & Glamour Studio",    categories: ["Makeup", "Hair Styling", "Bridal Services"],    location: "Manhattan",    rating: 4.8,    reviews: 2342,    price: "$350",    priceCaption: "Per Person from",    cta: "Book",    image: "/W1.png",  },  {    tag: "Premium",    title: "Luxe Rentals & Linens",    categories: ["Table Rentals", "Linens", "DÃ©cor Elements"],    location: "Long Island City",    rating: 4.9,    reviews: 1567,    price: "$500",    priceCaption: "Starts from",    cta: "Book",    image: "/W5.png",  },  {    tag: "Top rated",    title: "Sweet Confections Bakery",    categories: ["Custom Cakes", "Desserts", "Pastries"],    location: "Upper East Side",    rating: 4.8,    reviews: 2089,    price: "$250",    priceCaption: "Per Tier from",    cta: "Book",    image: "/w4.png",  },  {    tag: "Top rated",    title: "Prestige Event Planning",    categories: [      "Event Coordination",      "Budget Management",      "Timeline Planning",    ],    location: "Midtown Manhattan",    rating: 4.9,    reviews: 2567,    price: "$1,500",    priceCaption: "Planning Fee from",    cta: "Book",    image: "/W2.png",  },  {    tag: "Top rated",    title: "ProAudio & Lighting Co",    categories: ["Sound System", "Stage Lighting", "AV Equipment"],    location: "Queens",    rating: 4.8,    reviews: 1923,    price: "$900",    priceCaption: "Setup from",    cta: "Book",    image: "/p1.png",  },  {    tag: "Best Value",    title: "Elegant Invitations Studio",    categories: ["Invitation Design", "Printing", "Stationery"],    location: "Brooklyn",    rating: 4.7,    reviews: 1456,    price: "$200",    priceCaption: "Per Set from",    cta: "Book",    image: "/W3.png",  },  {    tag: "Premium",    title: "Luxe Transportation Services",    categories: ["Airport Pickup", "Valet Service", "Guest Transportation"],    location: "Manhattan",    rating: 4.9,    reviews: 1765,    price: "$75",    priceCaption: "Per Hour from",    cta: "Book",    image: "/p2.png",  },  {    tag: "Top rated",    title: "Grand Tent & Canopy Rentals",    categories: ["Tent Rentals", "Canopy Setup", "Weather Protection"],    location: "New Jersey",    rating: 4.8,    reviews: 1342,    price: "$400",    priceCaption: "Starts from",    cta: "Book",    image: "/W1.png",  },  {    tag: "Best Value",    title: "Premium Bar & Beverage Service",    categories: ["Bar Service", "Beverage Selection", "Bartending"],    location: "Manhattan",    rating: 4.7,    reviews: 2134,    price: "$35",    priceCaption: "Per Person from",    cta: "Book",    image: "/W5.png",  },  {    tag: "Top rated",    title: "Corporate Event Specialists",    categories: ["Corporate Planning", "Conference Setup", "Team Events"],    location: "Midtown",    rating: 4.9,    reviews: 2301,    price: "$2,000",    priceCaption: "Event Planning from",    cta: "Book",    image: "/w4.png",  },  {    tag: "Top rated",    title: "Hospitality & Guest Services",    categories: ["Guest Reception", "Coat Check", "Concierge Services"],    location: "Upper East Side",    rating: 4.8,    reviews: 1678,    price: "$25",    priceCaption: "Per Person from",    cta: "Book",    image: "/W2.png",  },  {    tag: "Premium",    title: "Sacred Rituals & Blessings",    categories: [      "House Blessing",      "Ritual Services",      "Traditional Ceremonies",      "Monk Ceremony",    ],    location: "Queens",    rating: 4.9,    reviews: 987,    price: "$400",    priceCaption: "Service from",    cta: "Book",    image: "/p1.png",  },  {    tag: "Top rated",    title: "Monk Ceremony Package",    categories: ["Monk Ceremony", "Traditional Services", "Blessing"],    location: "Phnom Penh",    rating: 4.8,    reviews: 643,    price: "$300",    priceCaption: "Service from",    cta: "Book",    image: "/W3.png",  },  {    tag: "Best Value",    title: "Kids Paradise Entertainment",    categories: [      "Kids Entertainment",      "Games & Activities",      "Character Meet & Greet",    ],    location: "Brooklyn",    rating: 4.8,    reviews: 1523,    price: "$300",    priceCaption: "Per Hour from",    cta: "Book",    image: "/W3.png",  },  {    tag: "Top rated",    title: "Gourmet Catering Elite",    categories: ["Fine Dining Catering", "Menu Customization", "Chef Services"],    location: "Manhattan",    rating: 4.9,    reviews: 2678,    price: "$85",    priceCaption: "Per Person from",    cta: "Book",    image: "/p2.png",  },];const steps = [  {    title: "Discover Vendors",    text: "Browse thousands of curated local services to match your vibe.",    icon: "ðŸ”",  },  {    title: "Chat & Customize",    text: "Message vendors, compare options, and personalize every detail.",    icon: "ðŸ’¬",  },  {    title: "Book Securely",    text: "Lock in dates with confidenceâ€”secure payments and reminders.",    icon: "ðŸ›¡ï¸",  },];const tips = [  {    category: "Wedding Planning",    title: "5 Secrets to a Stress-Free Wedding Ceremony",    meta: "By Claire Muller â€¢ 6 min read",    image: "/W1.png",  },  {    category: "Corporate",    title: "Choosing the Right Venue for Corporate Galas",    meta: "By Team Achar â€¢ 4 min read",    image: "/p1.png",  },  {    category: "Decor & Styling",    title: "Minimalist Tablescapes That Still Feel Luxe",    meta: "By In-house Stylists â€¢ 5 min read",    image: "/p2.png",  },];const showBookingModal = ref(false);const selectedVendor = ref(null);const bookingSuccess = ref(false);const bookingForm = ref({  fullName: "",  email: "",  eventDate: "",  guests: 50,  notes: "",});function openBookingModal(vendor) {  selectedVendor.value = vendor;  bookingSuccess.value = false;  showBookingModal.value = true;  // Reset form for a clean slate each time the modal is opened  bookingForm.value = {    fullName: "",    email: "",    eventDate: "",    guests: 50,    notes: "",  };}function closeBookingModal() {  showBookingModal.value = false;}function submitBooking() {  // In a real app, you'd make an API call here.  // On success, we just need to flip the boolean to show the success view.  bookingSuccess.value = true;}
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
            <span class="hero-explore-icon" aria-hidden="true">â†’</span>
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
          {{ showAllEvents ? "Show less" : "See all events" }} â†’
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
        <div class="carousel-nav">
          <button aria-label="Previous" class="pill-btn" @click="prevVendors">
            â€¹
          </button>
          <button aria-label="Next" class="pill-btn" @click="nextVendors">
            â€º
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
              <span class="star">â˜…</span> <strong>{{ vendor.rating }}</strong>
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
                class="favorite-btn"
                :class="{ active: isVendorFavorite(vendor.title) }"
                @click.stop="toggleVendorFavorite(vendor.title)"
              >
                {{ isVendorFavorite(vendor.title) ? '♥' : '♡' }}
              </button>
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
<<<<<<< HEAD

    <!-- Booking Modal -->
=======
    <!-- Improved Booking Modal -->
>>>>>>> 9d35ffa2d1ff350b05b246cd65a5b3509f643bb5
    <div
      v-if="showBookingModal"
      class="booking-modal-overlay"
      @click.self="closeBookingModal"
    >
      <div v-if="selectedVendor" class="booking-modal" @click.stop>
<<<<<<< HEAD
        <!-- Banner Image -->
        <div class="booking-modal-banner">
          <img :src="selectedVendor.image" :alt="selectedVendor.title" />
        </div>

        <!-- Vendor Info -->
        <div class="booking-vendor-panel">
          <p class="booking-eyebrow">YOU ARE BOOKING</p>
          <strong class="booking-vendor-name">{{ selectedVendor.title }}</strong>
          <p class="booking-vendor-location">{{ selectedVendor.location }}</p>
          <div class="booking-vendor-rating">
            <span class="star">★</span>
            <strong>{{ selectedVendor.rating }}</strong>
            <span>({{ selectedVendor.reviews?.toLocaleString() }} reviews)</span>
=======
        <button type="button" class="booking-close" @click="closeBookingModal">
          &times;
        </button>
        <div class="booking-modal-layout">
          <!-- Left Side: Vendor Info -->
          <div class="booking-vendor-preview">
            <img
              :src="selectedVendor.image"
              :alt="selectedVendor.title"
              class="vendor-preview-image"
            />
            <div class="vendor-preview-info">
              <p class="eyebrow">You are booking</p>
              <h4>{{ selectedVendor.title }}</h4>
              <p class="vendor-location">{{ selectedVendor.location }}</p>
              <div class="vendor-rating">
                <span class="star">â˜…</span>
                <strong>{{ selectedVendor.rating }}</strong>
                <span class="reviews"
                  >({{
                    selectedVendor.reviews?.toLocaleString()
                  }}
                  reviews)</span
                >
              </div>
            </div>
          </div>
          <!-- Right Side: Form or Success State -->
          <div class="booking-form-section">
            <div v-if="bookingSuccess" class="booking-success-state">
              <div class="success-icon">ðŸŽ‰</div>
              <h3>Booking Request Sent!</h3>
              <p>
                Your request to book
                <strong>{{ selectedVendor.title }}</strong> has been sent. The
                vendor will contact you via email shortly.
              </p>
              <button
                type="button"
                class="primary-btn"
                @click="closeBookingModal"
              >
                Done
              </button>
            </div>
            <form
              v-else
              class="booking-modal-form"
              @submit.prevent="submitBooking"
            >
              <div class="booking-modal-header">
                <h3>Confirm Your Details</h3>
                <p>Fill out the form below to send a booking request.</p>
              </div>
              <div class="form-group">
                <label for="fullName">Full name</label>
                <input
                  id="fullName"
                  v-model.trim="bookingForm.fullName"
                  type="text"
                  required
                  placeholder="e.g., Jane Doe"
                />
              </div>
              <div class="form-group">
                <label for="email">Email address</label>
                <input
                  id="email"
                  v-model.trim="bookingForm.email"
                  type="email"
                  required
                  placeholder="e.g., jane.doe@email.com"
                />
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="eventDate">Event date</label>
                  <input
                    id="eventDate"
                    v-model="bookingForm.eventDate"
                    type="date"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="guests">Number of guests</label>
                  <input
                    id="guests"
                    v-model.number="bookingForm.guests"
                    type="number"
                    min="1"
                    required
                    placeholder="50"
                  />
                </div>
              </div>
              <div class="form-group">
                <label for="notes">Additional notes</label>
                <textarea
                  id="notes"
                  v-model.trim="bookingForm.notes"
                  rows="3"
                  placeholder="Any special requests or details for the vendor..."
                ></textarea>
              </div>
              <div class="booking-modal-actions">
                <button
                  type="button"
                  class="ghost-btn"
                  @click="closeBookingModal"
                >
                  Cancel
                </button>
                <button type="submit" class="primary-btn">
                  Send Booking Request
                </button>
              </div>
            </form>
>>>>>>> 9d35ffa2d1ff350b05b246cd65a5b3509f643bb5
          </div>
        </div>

        <!-- Success State -->
        <div v-if="bookingSuccess" class="booking-success-state">
          <div class="success-icon">🎉</div>
          <h3>Booking Request Sent!</h3>
          <p>
            Your request to book <strong>{{ selectedVendor.title }}</strong> has
            been sent. The vendor will contact you via email shortly.
          </p>
          <button type="button" class="primary-btn" @click="closeBookingModal">
            Done
          </button>
        </div>

        <!-- Booking Form -->
        <form v-else class="booking-modal-form" @submit.prevent="submitBooking">
          <!-- Two-column header -->
          <div class="booking-form-header-row">
            <h3>Confirm Your<br />Details</h3>
            <p>Fill out the form below to send a booking request.</p>
          </div>

          <!-- Fields -->
          <div class="booking-form-body">
            <div class="bm-form-group">
              <label for="fullName">Full name</label>
              <input
                id="fullName"
                v-model.trim="bookingForm.fullName"
                type="text"
                required
                placeholder="e.g., Jane Doe"
              />
            </div>

            <div class="bm-form-group">
              <label for="email">Email address</label>
              <input
                id="email"
                v-model.trim="bookingForm.email"
                type="email"
                required
                placeholder="e.g., jane.doe@email.com"
              />
            </div>

            <div class="bm-form-row">
              <div class="bm-form-group">
                <label for="eventDate">Event date</label>
                <input
                  id="eventDate"
                  v-model="bookingForm.eventDate"
                  type="date"
                  required
                />
              </div>
              <div class="bm-form-group">
                <label for="guests">Number of guests</label>
                <input
                  id="guests"
                  v-model.number="bookingForm.guests"
                  type="number"
                  min="1"
                  required
                  placeholder="50"
                />
              </div>
            </div>

            <div class="bm-form-group">
              <label for="notes">Additional notes</label>
              <textarea
                id="notes"
                v-model.trim="bookingForm.notes"
                rows="3"
                placeholder="Any special requests or details for the vendor..."
              ></textarea>
            </div>

            <button type="submit" class="primary-btn booking-submit-btn">
              Send Booking Request
            </button>
          </div>
        </form>
      </div>

      <!-- Cancel button outside the modal -->
      <button
        type="button"
        class="booking-cancel-outside"
        @click="closeBookingModal"
      >
        Cancel
      </button>
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
  </div>
</template>
<style scoped src="../assets/Home.css"></style>

