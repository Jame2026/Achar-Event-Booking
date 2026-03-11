<script setup>
const supportChannels = [
  {
    title: 'Email',
    value: 'contact@achareventbooking.com',
    helper: 'Average response in under 1 hour',
  },
  {
    title: 'Phone',
    value: '+1 (555) 123-4567',
    helper: 'Daily, 8:00 AM - 10:00 PM ET',
  },
  {
    title: 'Live concierge',
    value: 'In-app chat for active bookings',
    helper: 'Instant replies during events',
  },
];

const officeDetails = [
  { label: 'Head office', value: '127 Madison Ave, Suite 402, New York, NY' },
  { label: 'Virtual studio', value: 'Video consultations worldwide' },
  { label: 'Vendors', value: 'Dedicated vendor success line available 24/7' },
];

const qrSrc =
  "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 120 120'%3E%3Crect width='120' height='120' fill='%23fff'/%3E%3Crect x='8' y='8' width='32' height='32' fill='none' stroke='%23000' stroke-width='8'/%3E%3Crect x='80' y='8' width='32' height='32' fill='none' stroke='%23000' stroke-width='8'/%3E%3Crect x='8' y='80' width='32' height='32' fill='none' stroke='%23000' stroke-width='8'/%3E%3Crect x='44' y='44' width='16' height='16' fill='%23000'/%3E%3Crect x='70' y='44' width='12' height='12' fill='%23000'/%3E%3Crect x='44' y='70' width='12' height='12' fill='%23000'/%3E%3Crect x='64' y='82' width='18' height='18' fill='%23000'/%3E%3Crect x='90' y='58' width='10' height='10' fill='%23000'/%3E%3C/svg%3E";

const stats = [
  { label: 'Avg first reply', value: '18 min' },
  { label: 'Coverage', value: '7 days / week' },
  { label: 'Bookings guided', value: '12k+/yr' },
];

const assurances = [
  { title: 'Human specialists', text: 'Former planners who know venues, cuisine, and rituals.' },
  { title: 'Secure payments', text: 'We walk you through protected checkout and vendor milestones.' },
  { title: 'Calendar-ready timelines', text: 'Get a clean schedule you can share with vendors and guests.' },
];

const handleSubmit = (event) => {
  event.preventDefault();
  const form = event.target;
  form.reset();
};
</script>

<template>
  <section id="contact" class="contact-page">
    <div class="contact-shell">
      <div class="contact-hero">
        <div class="badge">💬 Contact</div>
        <h2>Talk with our event concierge team</h2>
        <p class="lede">
          Whether you are planning your first ceremony or scaling corporate gatherings, our specialists help you book the
          right venues, vendors, and timelines.
        </p>
        <div class="hero-pills">
          <span class="pill">White-glove support for complex timelines</span>
          <span class="pill">Secure booking guidance end to end</span>
        </div>
      </div>

      <div class="contact-stats">
        <div class="stat" v-for="stat in stats" :key="stat.label">
          <p class="stat-value">{{ stat.value }}</p>
          <p class="stat-label">{{ stat.label }}</p>
        </div>
      </div>

      <div class="contact-layout">
        <div class="contact-card form-card">
          <p class="eyebrow">✉️ Send a message</p>
          <h3>Tell us about your event</h3>
          <form class="contact-form" @submit.prevent="handleSubmit">
            <div class="form-grid">
              <label class="field">
                <span>👤 Name</span>
                <input name="name" type="text" placeholder="Your full name" required />
              </label>
              <label class="field">
                <span>📧 Email</span>
                <input name="email" type="email" placeholder="you@example.com" required />
              </label>
              <label class="field">
                <span>📱 Phone</span>
                <input name="phone" type="tel" placeholder="+1 (555) 123-4567" />
              </label>
              <label class="field">
                <span>📅 Event date</span>
                <input name="date" type="date" />
              </label>
              <label class="field full">
                <span>📝 Event details</span>
                <textarea
                  name="details"
                  rows="4"
                  placeholder="Share the event type, location, guest count, and what you need help with"
                ></textarea>
              </label>
            </div>
            <div class="form-footer">
              <label class="checkbox">
                <input type="checkbox" name="newsletter" />
                <span>Keep me posted on planning guides and vendor offers</span>
              </label>
              <button class="primary-btn" type="submit">🚀 Send request</button>
            </div>
          </form>
        </div>

        <div class="contact-card info-card">
          <p class="eyebrow">📞 Direct lines</p>
          <h3>We are ready when you are</h3>
          <div class="channel-list">
            <div class="channel" v-for="channel in supportChannels" :key="channel.title">
              <div class="channel-title">{{ channel.title }}</div>
              <div class="channel-value">{{ channel.value }}</div>
              <div class="channel-helper">{{ channel.helper }}</div>
            </div>
          </div>

          <div class="office-card">
            <div class="office-head">
              <p class="office-title">Visit or schedule a consult</p>
              <p class="office-sub">We tailor recommendations to your traditions, cuisine, and guest experience.</p>
            </div>
            <ul class="office-list">
              <li v-for="office in officeDetails" :key="office.label">
                <span class="office-label">{{ office.label }}</span>
                <span class="office-value">{{ office.value }}</span>
              </li>
            </ul>
            <div class="map-shell" aria-hidden="true">
              <div class="map-figure">
                <p class="map-title">Achar HQ - Pearsonview</p>
                <p class="map-sub">Scan to pull directions and parking notes.</p>
              </div>
              <div class="map-qr">
                <img :src="qrSrc" alt="QR code to Achar HQ directions" />
                <span>Scan for directions</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="assurance-grid">
        <div class="assurance-card" v-for="assurance in assurances" :key="assurance.title">
          <div class="assurance-mark"></div>
          <p class="assurance-title">{{ assurance.title }}</p>
          <p class="assurance-text">{{ assurance.text }}</p>
        </div>
      </div>

      <div class="contact-cta">
        <div class="cta-copy">
          <p class="eyebrow light">Need to move fast?</p>
          <h3>Share your timeline and we will spin up a curated vendor shortlist today.</h3>
        </div>
        <div class="cta-actions">
          <a class="ghost-btn light" href="tel:+15551234567">Call concierge</a>
          <a class="primary-btn" href="mailto:contact@achareventbooking.com">Email the team</a>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped src="../assets/Contact.css"></style>
