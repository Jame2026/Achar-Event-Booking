<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import PublicNavbar from './PublicNavbar.vue'

const router = useRouter()
const showAllEvents = ref(false)
const currentVendorIndex = ref(0)
const VENDOR_PAGE_SIZE = 4

const eventTypes = [
  {
    name: 'Wedding',
    note: 'Event Planning',
    image: '/event-cards/wedding-stage.jpg',
    fallback: '/W1.png',
  },
  {
    name: 'Baby Shower',
    note: 'Event Planning',
    image: '/event-cards/orange-flowers.jpg',
    fallback: '/W2.png',
  },
  {
    name: 'House Blessing',
    note: 'Event Planning',
    image: '/event-cards/house-blessing-offering.jpg',
    fallback: '/W3.png',
  },
  {
    name: 'Monk Ceremony',
    note: 'Traditional Ritual',
    image: '/10442cc348903c28309a0b967a9f1c11.jpg',
    fallback: '/W2.png',
  },
  {
    name: 'Engagement',
    note: 'Event Planning',
    image: '/event-cards/engagement-attire.jpg',
    fallback: '/p2.png',
  },
  {
    name: 'Anniversary',
    note: 'Event Planning',
    image: '/event-cards/anniversary-arch.jpg',
    fallback: '/w4.png',
  },
]

const vendors = [
  {
    tag: 'Top rated',
    title: 'Skyline Grand Atrium',
    categories: ['Wedding Planning', 'Event Design'],
    location: 'Phnom Penh',
    rating: 4.9,
    reviews: 2458,
    price: '$3,500',
    priceCaption: 'Starts from',
    cta: 'Book',
    image: '/10442cc348903c28309a0b967a9f1c11.jpg',
  },
  {
    tag: 'Top rated',
    title: 'Artisan Palate',
    categories: ['Catering', 'Private Dining'],
    location: 'Siem Reap',
    rating: 4.8,
    reviews: 1945,
    price: '$150/pp',
    priceCaption: 'From',
    cta: 'Book',
    image: '/6bd05e85477e1de00d848b67e75710ec.jpg',
  },
  {
    tag: 'Top rated',
    title: 'Lumina Studios',
    categories: ['Photography', 'Videography'],
    location: 'Phnom Penh',
    rating: 4.9,
    reviews: 3021,
    price: '$2,200',
    priceCaption: 'Starts from',
    cta: 'Book',
    image: '/809395af0a420d3e22970e1fdda1d6e3.jpg',
  },
  {
    tag: 'Best Value',
    title: 'Elegant Events Co',
    categories: ['Decoration', 'Floral Design'],
    location: 'Battambang',
    rating: 4.7,
    reviews: 1523,
    price: '$1,200',
    priceCaption: 'Starts from',
    cta: 'Book',
    image: '/c3327dfbbaf34f11d3f6cd86cf97deca.jpg',
  },
  {
    tag: 'Premium',
    title: 'Prime Venues',
    categories: ['Venue Rental', 'Layout Design'],
    location: 'Sihanoukville',
    rating: 4.8,
    reviews: 876,
    price: '$4,000',
    priceCaption: 'From',
    cta: 'Book',
    image: '/f2bd9df78c9a0de5e994b1156edc002b.jpg',
  },
]

const steps = [
  {
    title: 'Discover Vendors',
    text: 'Browse trusted local services to match your event style.',
    icon: '01',
  },
  {
    title: 'Chat & Customize',
    text: 'Compare offers and customize details with each vendor.',
    icon: '02',
  },
  {
    title: 'Book Securely',
    text: 'Confirm your booking with secure checkout and reminders.',
    icon: '03',
  },
]

const tips = [
  {
    category: 'Wedding Planning',
    title: '5 Secrets to a Stress-Free Wedding Ceremony',
    meta: 'By Claire Muller - 6 min read',
    image: '/cac7a51f1da8e4e7ed23183239f17657.jpg',
  },
  {
    category: 'Corporate',
    title: 'Choosing the Right Venue for Corporate Galas',
    meta: 'By Team Achar - 4 min read',
    image: '/f2bd9df78c9a0de5e994b1156edc002b.jpg',
  },
  {
    category: 'Decor & Styling',
    title: 'Minimalist Tablescapes That Still Feel Luxe',
    meta: 'By In-house Stylists - 5 min read',
    image: '/809395af0a420d3e22970e1fdda1d6e3.jpg',
  },
]

const displayedEvents = computed(() =>
  showAllEvents.value ? eventTypes : eventTypes.slice(0, 4),
)

const displayedVendors = computed(() =>
  vendors.slice(currentVendorIndex.value, currentVendorIndex.value + VENDOR_PAGE_SIZE),
)

function nextVendors() {
  if (currentVendorIndex.value + VENDOR_PAGE_SIZE < vendors.length) {
    currentVendorIndex.value += VENDOR_PAGE_SIZE
  }
}

function prevVendors() {
  if (currentVendorIndex.value - VENDOR_PAGE_SIZE >= 0) {
    currentVendorIndex.value -= VENDOR_PAGE_SIZE
  }
}

function onEventCardImageError(event, fallback) {
  event.target.src = fallback
}

function goToEvent(eventItem) {
  const key = eventItem.name.toLowerCase().replace(/\s+/g, '_')
  router.push({ path: '/services/packages', query: { event: key } })
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
            Your Perfect Event, <span class="highlight">Orchestrated</span> to
            Perfection
          </h1>
          <p class="lede">
            Discover and book the finest venues, caterers, and specialists to master
            your traditional and modern ceremonies.
          </p>
          <router-link class="search-btn hero-explore-btn" to="/services/packages">
            <span>Explore Services & Packages</span>
            <span class="hero-explore-icon" aria-hidden="true">-&gt;</span>
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
          {{ showAllEvents ? 'Show less' : 'See all events' }} -&gt;
        </button>
      </div>

      <div class="event-grid">
        <div
          v-for="event in displayedEvents"
          :key="event.name"
          class="event-card"
          role="button"
          tabindex="0"
          @click="goToEvent(event)"
          @keyup.enter="goToEvent(event)"
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
          <button aria-label="Previous" class="pill-btn" @click="prevVendors">&lt;</button>
          <button aria-label="Next" class="pill-btn" @click="nextVendors">&gt;</button>
        </div>
      </div>

      <div class="vendor-grid">
        <article v-for="vendor in displayedVendors" :key="vendor.title" class="vendor-card">
          <span class="vendor-tag">{{ vendor.tag }}</span>
          <div class="vendor-media">
            <img :src="vendor.image" :alt="vendor.title" />
          </div>
          <div class="vendor-body">
            <div>
              <p class="vendor-title">{{ vendor.title }}</p>
              <p class="vendor-meta">
                <span v-for="category in vendor.categories" :key="category" class="dot">{{ category }}</span>
                <span class="location">{{ vendor.location }}</span>
              </p>
            </div>
            <div class="vendor-rating">
              <span class="star">*</span>
              <strong>{{ vendor.rating }}</strong>
              <span class="reviews">{{ vendor.reviews.toLocaleString() }} reviews</span>
            </div>
            <div class="vendor-footer">
              <div class="pricing">
                <p class="price-caption">{{ vendor.priceCaption }}</p>
                <p class="price">{{ vendor.price }}</p>
              </div>
              <button type="button" class="outline-btn">{{ vendor.cta }}</button>
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
        <div v-for="(step, index) in steps" :key="step.title" class="step-card">
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
        <article v-for="tip in tips" :key="tip.title" class="tip-card">
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
          Join thousands of planners and vendors on Achar. Start your journey today.
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
