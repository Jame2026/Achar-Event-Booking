<script setup>
import { ref } from 'vue'
import PublicNavbar from './PublicNavbar.vue'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

const eventTypes = [
  { name: 'Wedding', note: 'Event Planning', image: '/event-cards/wedding-stage.jpg', fallback: '/W1.png' },
  { name: 'Baby Shower', note: 'Event Planning', image: '/event-cards/orange-flowers.jpg', fallback: '/W2.png' },
  { name: 'House Blessing', note: 'Event Planning', image: '/event-cards/house-blessing-offering.jpg', fallback: '/W3.png' },
  { name: 'Company Party', note: 'Event Planning', image: '/event-cards/ceremony-hall.jpg', fallback: '/p1.png' },
  { name: 'Engagement', note: 'Event Planning', image: '/event-cards/engagement-attire.jpg', fallback: '/p2.png' },
  { name: 'Birthday', note: 'Event Planning', image: '/W5.png', fallback: '/W5.png' },
  { name: 'Anniversary', note: 'Event Planning', image: '/event-cards/anniversary-arch.jpg', fallback: '/w4.png' },
];

function onEventCardImageError(event, fallback) {
  event.target.src = fallback
}

const vendors = [
  {
    tag: 'Top rated',
    title: 'Skyline Grand Atrium',
    categories: ['Wedding Planning', 'Photographer'],
    location: 'Diamond Hills - Pearsonview',
    rating: 4.9,
    price: '$2,500',
    priceCaption: 'Begins at',
    cta: 'Book',
    image:
      'public/W2.png',
  },
  {
    tag: 'Top rated',
    title: 'Artisan Palate',
    categories: ['Catering', 'Private Dining'],
    location: 'Lecoteur Street - Pearsonview',
    rating: 4.7,
    price: '$120/pp',
    priceCaption: 'Begins at',
    cta: 'Book',
    image:
      'public/W5.png',
  },
  {
    tag: 'Top rated',
    title: 'Lumina Studios',
    categories: ['Photography', 'Cinematography'],
    location: 'Pearsonview - City Center',
    rating: 4.8,
    price: '$1,800',
    priceCaption: 'Begins at',
    cta: 'Book',
    image:
      'public/w4.png',
  },
];

const steps = [
  {
    title: 'Discover Vendors',
    text: 'Browse thousands of curated local services to match your vibe.',
    icon: '🔍',
  },
  {
    title: 'Chat & Customize',
    text: 'Message vendors, compare options, and personalize every detail.',
    icon: '💬',
  },
  {
    title: 'Book Securely',
    text: 'Lock in dates with confidence—secure payments and reminders.',
    icon: '🛡️',
  },
];

const tips = [
  {
    category: 'Wedding Planning',
    title: '5 Secrets to a Stress-Free Wedding Ceremony',
    meta: 'By Claire Muller • 6 min read',
    image:
      'public/W1.png',
  },
  {
    category: 'Corporate',
    title: 'Choosing the Right Venue for Corporate Galas',
    meta: 'By Team Achar • 4 min read',
    image:
      'public/p1.png',
  },
  {
    category: 'Decor & Styling',
    title: 'Minimalist Tablescapes That Still Feel Luxe',
    meta: 'By In-house Stylists • 5 min read',
    image:
      'public/p2.png',
  },
];

const showBookingModal = ref(false)
const selectedVendor = ref(null)
const bookingSuccess = ref('')
const bookingForm = ref({
  fullName: '',
  email: '',
  eventDate: '',
  guests: 50,
  notes: ''
})

function openBookingModal(vendor) {
  selectedVendor.value = vendor
  bookingSuccess.value = ''
  showBookingModal.value = true
}

function closeBookingModal() {
  showBookingModal.value = false
}

function submitBooking() {
  bookingSuccess.value = `Booking request sent for ${selectedVendor.value?.title || 'service'}.`
  bookingForm.value = {
    fullName: '',
    email: '',
    eventDate: '',
    guests: 50,
    notes: ''
  }
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
            Discover and book the finest venues, caterers, and specialists to master your traditional and modern ceremonies.
          </p>

          <router-link class="search-btn hero-explore-btn" to="/services/packages">
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
        <router-link class="link" to="/services">See all events -></router-link>
      </div>
      <div class="event-grid">
        <div class="event-card" v-for="event in eventTypes" :key="event.name">
          <div class="icon-circle">
            <img :src="event.image" :alt="event.name" @error="onEventCardImageError($event, event.fallback)" />
          </div>
          <div>
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
          <button aria-label="Previous" class="pill-btn">‹</button>
          <button aria-label="Next" class="pill-btn">›</button>
        </div>
      </div>
      <div class="vendor-grid">
        <article class="vendor-card" v-for="vendor in vendors" :key="vendor.title">
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
              <span class="star">★</span>
              <strong>{{ vendor.rating }}</strong>
              <span class="reviews">4,758 reviews</span>
            </div>
            <div class="vendor-footer">
              <div class="pricing">
                <p class="price-caption">{{ vendor.priceCaption }}</p>
                <p class="price">{{ vendor.price }}</p>
              </div>
              <button type="button" class="outline-btn" @click="openBookingModal(vendor)">{{ vendor.cta }}</button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <div v-if="showBookingModal" class="booking-modal-overlay" @click="closeBookingModal">
      <div class="booking-modal" @click.stop>
        <div class="booking-modal-header">
          <h3>Book {{ selectedVendor?.title }}</h3>
          <button type="button" class="booking-close" @click="closeBookingModal">×</button>
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
            <input v-model.number="bookingForm.guests" type="number" min="1" required />
          </label>
          <label>
            Notes
            <textarea v-model.trim="bookingForm.notes" rows="3" placeholder="Add preferences or requests"></textarea>
          </label>
          <div class="booking-modal-actions">
            <button type="button" class="ghost-btn" @click="closeBookingModal">Cancel</button>
            <button type="submit" class="primary-btn">Confirm Booking</button>
          </div>
          <p v-if="bookingSuccess" class="booking-success">{{ bookingSuccess }}</p>
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
        <router-link class="link" to="/customization">Read all articles -></router-link>
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
        <p class="cta-text">Join thousands of planners and vendors on Achar. Start your journey today.</p>
        <div class="cta-actions">
          <router-link class="primary-btn large" to="/booking">Get Started for Free</router-link>
          <router-link class="ghost-btn light large" to="/customization">List Your Business</router-link>
        </div>
      </div>
    </section>

    <footer id="contact" class="footer">
      <div class="footer__grid">
        <div>
          <div class="brand">
            <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
            <span class="brand-name">Achar.</span>
          </div>
          <p class="footer-copy">
            Achar makes event planning effortless with curated vendors, smart tools, and secure bookings.
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
          <router-link to="/dashboard" class="footer-link">Enterprise Solutions</router-link>
          <router-link to="/services" class="footer-link">Affiliate Program</router-link>
          <router-link to="/legacy-app" class="footer-link">Corporate Packages</router-link>
        </div>
        <div>
          <p class="footer-title">For Vendors</p>
          <router-link to="/dashboard" class="footer-link">Join as Vendor</router-link>
          <router-link to="/services" class="footer-link">Pricing</router-link>
          <router-link to="/booking" class="footer-link">Help Center</router-link>
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




