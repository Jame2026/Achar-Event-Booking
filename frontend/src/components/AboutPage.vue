<script setup>
import { ref } from 'vue'
import PublicNavbar from './PublicNavbar.vue'

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

const aboutGallery = [
  {
    title: 'Premium Wedding Setup',
    text: 'Elegant stage styling and vendor coordination for memorable celebrations.',
    image: '/event-cards/wedding-stage.jpg',
  },
  {
    title: 'Vendor Network',
    text: 'Trusted venues, decorators, caterers, and planners in one platform.',
    image: '/W1.png',
  },
  {
    title: 'Smart Booking Flow',
    text: 'From service discovery to checkout and confirmation without friction.',
    image: '/event-cards/ceremony-hall.jpg',
  },
]

const journeyCards = [
  {
    step: '01',
    title: 'Discover',
    text: 'Browse packages, services, and professional vendors.',
    image: '/event-cards/engagement-attire.jpg',
  },
  {
    step: '02',
    title: 'Customize',
    text: 'Adjust quantity, add-ons, and event preferences in one flow.',
    image: '/W5.png',
  },
  {
    step: '03',
    title: 'Book',
    text: 'Confirm your event plan with transparent summary and checkout.',
    image: '/event-cards/orange-flowers.jpg',
  },
]
</script>
<template>
  <div class="about-page-root">
    <PublicNavbar />
    <main class="shell about-content">
      <section class="about-banner card">
        <img class="about-banner-watermark" :src="appLogoSrc" alt="Achar emblem" @error="onLogoError" />
        <div class="about-banner-content">
          <p class="about-chip">About Achar Event Booking</p>
          <h1>
            Your Perfect Event,
            <span>Orchestrated</span> to Perfection
          </h1>
          <p>
            Discover and book trusted vendors, venues, and specialists for both
            traditional and modern celebrations.
          </p>
          <RouterLink to="/services/packages" class="about-banner-cta">
            Explore Services & Packages
          </RouterLink>
        </div>
      </section>

      <section class="about-visuals">
        <article
          v-for="(card, index) in aboutGallery"
          :key="card.title"
          class="card about-visual-card"
          :class="{ 'about-visual-featured': index === 0 }"
        >
          <img :src="card.image" :alt="card.title" loading="lazy" />
          <div>
            <p v-if="index === 0" class="about-chip">Featured Experience</p>
            <h3>{{ card.title }}</h3>
            <p>{{ card.text }}</p>
          </div>
        </article>
      </section>

      <section class="about-hero card">
        <p class="about-hero-kicker">Why Achar</p>
        <h2>A modern platform for seamless event planning</h2>
        <p>
          Achar helps people plan celebrations and professional events by
          matching them with the right services. Instead of handling vendors
          across scattered channels, users can discover options, compare
          packages, customize services, and confirm bookings in one workflow.
        </p>
      </section>

      <section class="about-grid">
        <article
          class="card about-block"
          v-for="(pillar, index) in platformPillars"
          :key="pillar.title"
        >
          <span class="about-block-index">0{{ index + 1 }}</span>
          <h2>{{ pillar.title }}</h2>
          <p>{{ pillar.text }}</p>
        </article>
      </section>
      <section class="card about-mission">
        <h2>What this platform delivers</h2>
        <p>
          The goal is to make event planning reliable, transparent, and less
          stressful. Clients get better control over budget, schedule, and
          service quality. Vendors get a clearer channel to showcase offerings,
          engage customers, and convert interest into confirmed bookings.
        </p>
        <div class="about-mission-tags">
          <span>Transparent Pricing</span>
          <span>Reliable Vendors</span>
          <span>Faster Booking</span>
          <span>Better Visibility</span>
        </div>
      </section>
      <section class="about-stats">
        <article
          class="card stat-item"
          v-for="item in highlights"
          :key="item.label"
        >
          <strong>{{ item.value }}</strong>
          <p>{{ item.label }}</p>
        </article>
      </section>

      <section class="card about-journey">
        <h2>How Achar Works</h2>
        <div class="about-journey-grid">
          <article v-for="item in journeyCards" :key="item.step" class="about-journey-card">
            <img :src="item.image" :alt="item.title" loading="lazy" />
            <div>
              <span>{{ item.step }}</span>
              <h3>{{ item.title }}</h3>
              <p>{{ item.text }}</p>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>
<style scoped>
.about-page-root {
  min-height: 100vh;
}

.about-content {
  padding: 1rem 0 2.2rem;
}

.about-banner {
  position: relative;
  overflow: hidden;
  padding: 2.4rem 2rem 2.2rem;
  border-radius: 30px;
  background:
    radial-gradient(circle at 12% 0%, rgba(255, 106, 0, 0.12), transparent 34%),
    radial-gradient(circle at 86% 0%, rgba(255, 173, 112, 0.18), transparent 36%),
    linear-gradient(180deg, #fffefc, #fff8f2);
  animation: aboutFadeUp 0.55s ease both;
}

.about-banner-watermark {
  position: absolute;
  left: 50%;
  top: 50%;
  width: min(460px, 70%);
  transform: translate(-50%, -50%);
  opacity: 0.16;
  filter: saturate(1.2);
  pointer-events: none;
}

.about-banner-content {
  position: relative;
  z-index: 1;
  max-width: 980px;
}

.about-hero {
  margin-top: 1rem;
  padding: 1.35rem 1.4rem;
  border-radius: 22px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 95% 0%, rgba(255, 106, 0, 0.14), transparent 42%),
    linear-gradient(180deg, #ffffff, #f8fbff);
}

.about-hero-kicker {
  width: fit-content;
  margin: 0;
  border-radius: 999px;
  padding: 0.2rem 0.58rem;
  border: 1px solid #ffd1af;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
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

.about-banner h1 {
  margin: 0.8rem 0 0;
  font-size: clamp(2.4rem, 5vw, 5.15rem);
  line-height: 0.98;
  letter-spacing: -0.02em;
  max-width: 980px;
  color: #0f172a;
}

.about-banner h1 span {
  color: #ff6a00;
  font-style: italic;
  text-shadow: 0 8px 24px rgba(255, 106, 0, 0.2);
}

.about-banner p {
  margin: 1rem 0 0;
  max-width: 720px;
  color: #475569;
  font-size: clamp(1rem, 2vw, 1.15rem);
  line-height: 1.6;
}

.about-banner-cta {
  margin-top: 1.2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 50px;
  padding: 0 1.5rem;
  border-radius: 999px;
  background: linear-gradient(145deg, #ff8a2b, #ff6a00);
  color: #fff;
  font-weight: 800;
  box-shadow: 0 14px 30px rgba(255, 106, 0, 0.28);
  position: relative;
  overflow: hidden;
}

.about-banner-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 18px 34px rgba(255, 106, 0, 0.34);
}

.about-banner-cta::after {
  content: '';
  position: absolute;
  top: 0;
  left: -120%;
  width: 70%;
  height: 100%;
  background: linear-gradient(100deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0));
  transform: skewX(-22deg);
  transition: left 0.55s ease;
}

.about-banner-cta:hover::after {
  left: 140%;
}

.about-hero h2 {
  margin: 0;
  font-size: clamp(1.6rem, 2.6vw, 2.3rem);
  line-height: 1.15;
}

.about-hero p {
  margin: 0.65rem 0 0;
  max-width: 940px;
  color: #475569;
  line-height: 1.64;
}

.about-visuals {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 0.8rem;
}

.about-visual-card {
  display: grid;
  grid-template-rows: 300px auto;
  align-content: start;
  overflow: hidden;
  padding: 0.9rem;
  border-radius: 28px;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-visual-featured {
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.12), transparent 30%),
    #fff;
}

.about-visual-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 20px;
  transition: transform 0.35s ease;
}

.about-visual-card h3 {
  margin: 0.75rem 0 0;
  font-size: 2rem;
  line-height: 1.12;
  letter-spacing: -0.01em;
}

.about-visual-card p {
  margin: 0.35rem 0 0;
  color: #64748b;
  font-size: 0.98rem;
  line-height: 1.5;
}

.about-visual-card:not(.about-visual-featured) h3 {
  font-size: 2rem;
}

.about-visual-card:hover {
  transform: translateY(-4px);
  border-color: #cdddf2;
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.1);
}

.about-visual-card:hover img {
  transform: scale(1.03);
}

.about-grid {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}
.about-block {
  padding: 1.1rem;
  border-radius: 20px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.08), transparent 35%),
    linear-gradient(180deg, #ffffff, #f9fcff);
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-block-index {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 24px;
  border-radius: 999px;
  border: 1px solid #ffd3b1;
  background: #fff6ee;
  color: #c2410c;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.03em;
}

.about-block h2 {
  margin: 0.65rem 0 0;
  font-size: 1.35rem;
}

.about-block p {
  margin: 0.5rem 0 0;
  color: #64748b;
  line-height: 1.55;
}
.about-mission {
  margin-top: 0.9rem;
  padding: 1.25rem;
  border-radius: 22px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.12), transparent 32%),
    linear-gradient(180deg, #ffffff, #f9fcff);
  animation: aboutFadeUp 0.55s ease both;
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

.about-mission-tags {
  margin-top: 0.8rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.about-mission-tags span {
  border: 1px solid #d8e3f4;
  border-radius: 999px;
  background: #fff;
  color: #334155;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.22rem 0.62rem;
}
.about-stats {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.8rem;
}
.stat-item {
  padding: 1.05rem 0.85rem;
  text-align: center;
  border-radius: 18px;
  border: 1px solid #dce7f5;
  background:
    radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.08), transparent 42%),
    #fff;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.stat-item strong {
  display: block;
  font-size: 1.8rem;
}

.stat-item p {
  margin: 0.25rem 0 0;
  color: #64748b;
}

.about-journey {
  margin-top: 0.9rem;
  padding: 1.1rem;
}

.about-journey h2 {
  margin: 0;
  font-size: 1.5rem;
}

.about-journey-grid {
  margin-top: 0.8rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}

.about-journey-card {
  border: 1px solid #e2e8f3;
  border-radius: 14px;
  overflow: hidden;
  background: #fbfdff;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-journey-card img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.about-journey-card div {
  padding: 0.7rem 0.8rem 0.9rem;
}

.about-journey-card span {
  display: inline-flex;
  border-radius: 999px;
  border: 1px solid #ffd0b0;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 0.12rem 0.5rem;
}

.about-journey-card h3 {
  margin: 0.45rem 0 0;
  font-size: 1.08rem;
}

.about-journey-card p {
  margin: 0.4rem 0 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
}

.about-block:hover,
.about-mission:hover,
.stat-item:hover,
.about-journey-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
}

.about-journey-card:hover img {
  transform: scale(1.03);
}

.about-visual-card:nth-child(1),
.about-block:nth-child(1),
.stat-item:nth-child(1),
.about-journey-card:nth-child(1) {
  animation-delay: 0.04s;
}

.about-visual-card:nth-child(2),
.about-block:nth-child(2),
.stat-item:nth-child(2),
.about-journey-card:nth-child(2) {
  animation-delay: 0.1s;
}

.about-visual-card:nth-child(3),
.about-block:nth-child(3),
.stat-item:nth-child(3),
.about-journey-card:nth-child(3) {
  animation-delay: 0.16s;
}

.stat-item:nth-child(4) {
  animation-delay: 0.22s;
}

@keyframes aboutFadeUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 980px) {
  .about-banner {
    padding: 1.8rem 1.2rem;
  }

  .about-visuals {
    grid-template-columns: 1fr;
  }

  .about-visual-card {
    grid-template-rows: 220px auto;
  }

  .about-visual-card h3,
  .about-visual-card:not(.about-visual-featured) h3 {
    font-size: 1.65rem;
  }

  .about-grid {
    grid-template-columns: 1fr;
  }

  .about-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .about-journey-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 680px) {
  .about-banner-watermark {
    width: min(360px, 84%);
    opacity: 0.12;
  }

  .about-banner h1 {
    line-height: 1.02;
  }

  .about-stats {
    grid-template-columns: 1fr;
  }
}
</style>
