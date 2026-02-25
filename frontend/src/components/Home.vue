<script setup>
const eventTypes = [
  { name: 'Wedding', note: 'Event Planning', icon: '💍' },
  { name: 'Baby Shower', note: 'Event Planning', icon: '🍼' },
  { name: 'House Blessing', note: 'Event Planning', icon: '🕯️' },
  { name: 'Company Party', note: 'Event Planning', icon: '🏢' },
  { name: 'Engagement', note: 'Event Planning', icon: '💞' },
  { name: 'Birthday', note: 'Event Planning', icon: '🎂' },
  { name: 'Anniversary', note: 'Event Planning', icon: '🎉' },
];

const vendors = [
  {
    tag: 'Top rated',
    title: 'Skyline Grand Atrium',
    categories: ['Wedding Planning', 'Photographer'],
    location: 'Diamond Hills - Pearsonview',
    rating: 4.9,
    price: '$2,500',
    priceCaption: 'Begins at',
    cta: 'View Details',
    image:
      'https://images.unsplash.com/photo-1525253013412-55c1a69a5738?auto=format&fit=crop&w=800&q=80',
  },
  {
    tag: 'Top rated',
    title: 'Artisan Palate',
    categories: ['Catering', 'Private Dining'],
    location: 'Lecoteur Street - Pearsonview',
    rating: 4.7,
    price: '$120/pp',
    priceCaption: 'Begins at',
    cta: 'View Details',
    image:
      'https://images.unsplash.com/photo-1467003909585-2f8a72700288?auto=format&fit=crop&w=800&q=80',
  },
  {
    tag: 'Top rated',
    title: 'Lumina Studios',
    categories: ['Photography', 'Cinematography'],
    location: 'Pearsonview - City Center',
    rating: 4.8,
    price: '$1,800',
    priceCaption: 'Begins at',
    cta: 'View Details',
    image:
      'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80',
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
      'https://images.unsplash.com/photo-1529634893741-b3b35a1c5207?auto=format&fit=crop&w=600&q=80',
  },
  {
    category: 'Corporate',
    title: 'Choosing the Right Venue for Corporate Galas',
    meta: 'By Team Achar • 4 min read',
    image:
      'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=600&q=80',
  },
  {
    category: 'Decor & Styling',
    title: 'Minimalist Tablescapes That Still Feel Luxe',
    meta: 'By In-house Stylists • 5 min read',
    image:
      'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=600&q=80',
  },
];
</script>

<template>
  <div class="home-page">
    <header class="navbar">
      <div class="brand">
        <div class="brand-mark">A</div>
        <span class="brand-name">Achar.</span>
      </div>
      <nav class="nav-links">
        <a class="active" href="#">Home</a>
        <a href="#">My Booking</a>
        <a href="#">Services</a>
        <a href="#">Favorites</a>
        <a href="#">Message</a>
      </nav>
      <div class="nav-actions">
        <button class="ghost-btn">Sign in</button>
        <button class="primary-btn">Sign up</button>
      </div>
    </header>

    <section class="hero">
      <div class="hero__bg-shape"></div>
      <div class="hero__grid">
        <div class="hero__text">
          <div class="badge">Popular</div>
          <h1>
            Your Perfect Event,
            <span class="highlight">Orchestrated</span>
            to Perfection
          </h1>
          <p class="lede">
            Discover and book the finest venues, caterers, and specialists to master your traditional and modern ceremonies.
          </p>

          <div class="search-bar">
            <div class="field">
              <label>Event type or venue</label>
              <input type="text" placeholder='Try "garden wedding", "banquet hall"' />
            </div>
            <div class="field">
              <label>Service type</label>
              <select>
                <option>Photography</option>
                <option>Catering</option>
                <option>Event Planning</option>
                <option>Decor & Styling</option>
              </select>
            </div>
            <button class="search-btn">Search</button>
          </div>
        </div>
        <div class="hero__visual">
          <div class="hero-card">
            <p class="stat-label">Trending vendor</p>
            <h3>Orchestral Events Co.</h3>
            <p class="stat-value">4.9 ★★★★★</p>
            <p class="stat-note">Wedding planning - Cinematography</p>
          </div>
          <div class="floating-tag">Curated picks daily</div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section__header">
        <div>
          <p class="eyebrow">Browse by Event Type</p>
          <h2>Perfectly suited for those extra-special occasions</h2>
        </div>
        <a class="link" href="#">See all events →</a>
      </div>
      <div class="event-grid">
        <div class="event-card" v-for="event in eventTypes" :key="event.name">
          <div class="icon-circle">{{ event.icon }}</div>
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
              <button class="outline-btn">{{ vendor.cta }}</button>
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

    <section class="section tips">
      <div class="section__header">
        <div>
          <p class="eyebrow">Latest Planning Tips</p>
          <h2>Ideas and advice from our planning pros</h2>
        </div>
        <a class="link" href="#">Read all articles →</a>
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
          <button class="primary-btn large">Get Started for Free</button>
          <button class="ghost-btn light large">List Your Business</button>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer__grid">
        <div>
          <div class="brand">
            <div class="brand-mark">A</div>
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
          <a href="#" class="footer-link">Enterprise Solutions</a>
          <a href="#" class="footer-link">Affiliate Program</a>
          <a href="#" class="footer-link">Corporate Packages</a>
        </div>
        <div>
          <p class="footer-title">For Vendors</p>
          <a href="#" class="footer-link">Join as Vendor</a>
          <a href="#" class="footer-link">Pricing</a>
          <a href="#" class="footer-link">Help Center</a>
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
          <a href="#">Terms of Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Cookies</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped src="../assets/Home.css"></style>
