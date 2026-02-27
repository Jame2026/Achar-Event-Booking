<script setup>
import { ref } from 'vue'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

const platformPillars = [
  {
    title: 'Curated Vendors',
    text: 'Achar connects clients with trusted venues, planners, decorators, caterers, photographers, and more in one place.',
  },
  {
    title: 'Smart Planning Flow',
    text: 'From discovery to customization and final booking, the platform keeps every step organized and easy to manage.',
  },
  {
    title: 'Built for Hosts and Vendors',
    text: 'Clients plan faster while vendors manage inquiries, packages, and bookings with better visibility.',
  },
]

const highlights = [
  { value: '10k+', label: 'Bookings supported' },
  { value: '500+', label: 'Vendor profiles' },
  { value: '25+', label: 'Event categories' },
  { value: '24/7', label: 'Platform access' },
]
</script>

<template>
  <div class="about-page-root">
    <header class="topbar">
      <div class="shell topbar-inner">
        <router-link class="brand" to="/">
          <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
          <span class="brand-text">Achar</span>
        </router-link>

        <nav class="top-links">
          <router-link to="/">Home</router-link>
          <router-link to="/about" class="active">About</router-link>
          <div class="service-menu">
            <button type="button" class="service-menu-trigger">Service</button>
            <div class="service-menu-dropdown">
              <router-link to="/services/packages">Packages</router-link>
              <router-link to="/services/overall">Overall Service</router-link>
            </div>
          </div>
          <router-link to="/booking">My Booking</router-link>
          <router-link to="/favorite">Favorite</router-link>
          <router-link to="/contact">Contact</router-link>
        </nav>

        <div class="top-actions">
          <router-link class="top-logout top-signin" to="/legacy-app">Sign in</router-link>
        </div>
      </div>
    </header>

    <main class="shell about-content">
      <section class="about-hero card">
        <p class="about-chip">About Achar Event Booking</p>
        <h1>A platform built to simplify event planning from idea to booking.</h1>
        <p>
          Achar is an event-booking platform that helps people plan celebrations and professional events by
          matching them with the right services. Instead of handling vendors across scattered channels, users
          can discover options, compare packages, customize services, and confirm bookings in one workflow.
        </p>
      </section>

      <section class="about-grid">
        <article class="card about-block" v-for="pillar in platformPillars" :key="pillar.title">
          <h2>{{ pillar.title }}</h2>
          <p>{{ pillar.text }}</p>
        </article>
      </section>

      <section class="card about-mission">
        <h2>What this platform delivers</h2>
        <p>
          The goal is to make event planning reliable, transparent, and less stressful. Clients get better
          control over budget, schedule, and service quality. Vendors get a clearer channel to showcase
          offerings, engage customers, and convert interest into confirmed bookings.
        </p>
      </section>

      <section class="about-stats">
        <article class="card stat-item" v-for="item in highlights" :key="item.label">
          <strong>{{ item.value }}</strong>
          <p>{{ item.label }}</p>
        </article>
      </section>
    </main>

    <footer id="contact" class="footer">
      <div class="shell footer-grid">
        <div class="footer-brand-col">
          <router-link class="brand" to="/">
            <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
            <span class="brand-text">Achar</span>
          </router-link>
          <p>
            Achar helps people plan events with confidence through curated vendors, flexible service
            options, and a clean booking experience.
          </p>
          <span class="footer-chip">One place for planning, booking, and coordination</span>
        </div>
        <div>
          <h4>Explore</h4>
          <router-link to="/about">About</router-link>
          <router-link to="/services/packages">Service</router-link>
        </div>
        <div>
          <h4>Account</h4>
          <router-link to="/booking">My Booking</router-link>
          <router-link to="/favorite">Favorite</router-link>
          <router-link to="/legacy-app">Sign in</router-link>
        </div>
        <div>
          <h4>Contact</h4>
          <p>support@acharbooking.com</p>
          <p>+1 (555) 123-4588</p>
        </div>
      </div>
      <div class="shell footer-bottom">
        <span>© 2026 Achar Event Booking. All rights reserved.</span>
        <div>
          <router-link to="/about">Privacy</router-link>
          <router-link to="/about">Terms</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.about-page-root {
  min-height: 100vh;
}

.top-signin {
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.about-content {
  padding: 1.2rem 0 2.2rem;
}

.service-menu {
  position: relative;
}

.service-menu-trigger {
  border: 0;
  background: transparent;
  color: #334155;
  border-radius: 999px;
  padding: 0.52rem 0.9rem;
  font: inherit;
  cursor: pointer;
}

.service-menu:hover .service-menu-trigger,
.service-menu:focus-within .service-menu-trigger {
  background: #fff1e7;
  color: #e45800;
  border: 1px solid #ffd8bc;
}

.service-menu-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  min-width: 190px;
  border: 1px solid #dde3ee;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 14px 34px rgba(10, 28, 34, 0.1);
  padding: 6px;
  display: none;
  z-index: 50;
}

.service-menu:hover .service-menu-dropdown,
.service-menu:focus-within .service-menu-dropdown {
  display: grid;
}

.service-menu-dropdown a {
  padding: 0.52rem 0.7rem;
  border-radius: 9px;
}

.service-menu-dropdown a:hover {
  background: #f8fafc;
}

.about-hero {
  padding: 1.4rem;
  background:
    radial-gradient(circle at 95% 0%, rgba(255, 106, 0, 0.13), transparent 42%),
    linear-gradient(180deg, #ffffff, #fbfdff);
}

.about-chip {
  width: fit-content;
  margin: 0;
  border: 1px solid #ffd3b1;
  border-radius: 999px;
  padding: 0.2rem 0.6rem;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.76rem;
  font-weight: 800;
  text-transform: uppercase;
}

.about-hero h1 {
  margin: 0.7rem 0 0;
  font-size: clamp(2rem, 3vw, 3rem);
  line-height: 1.05;
}

.about-hero p {
  margin: 0.75rem 0 0;
  max-width: 850px;
  color: #475569;
  line-height: 1.64;
}

.about-grid {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}

.about-block {
  padding: 1rem;
}

.about-block h2 {
  margin: 0;
  font-size: 1.35rem;
}

.about-block p {
  margin: 0.5rem 0 0;
  color: #64748b;
  line-height: 1.55;
}

.about-mission {
  margin-top: 0.9rem;
  padding: 1.1rem;
}

.about-mission h2 {
  margin: 0;
  font-size: 1.6rem;
}

.about-mission p {
  margin: 0.6rem 0 0;
  color: #64748b;
  line-height: 1.6;
}

.about-stats {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.8rem;
}

.stat-item {
  padding: 0.95rem;
  text-align: center;
}

.stat-item strong {
  display: block;
  font-size: 1.8rem;
}

.stat-item p {
  margin: 0.25rem 0 0;
  color: #64748b;
}

@media (max-width: 980px) {
  .about-grid {
    grid-template-columns: 1fr;
  }

  .about-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 680px) {
  .about-stats {
    grid-template-columns: 1fr;
  }
}
</style>
