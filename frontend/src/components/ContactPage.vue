<script setup>
import { reactive, ref } from 'vue'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const successMessage = ref('')

const form = reactive({
  name: '',
  email: '',
  topic: 'General inquiry',
  message: '',
})

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

function submitContactForm() {
  successMessage.value = 'Thank you. Your message has been received and our team will contact you soon.'
  form.name = ''
  form.email = ''
  form.topic = 'General inquiry'
  form.message = ''
}
</script>

<template>
  <div class="contact-page-root">
    <header class="topbar">
      <div class="shell topbar-inner">
        <router-link class="brand" to="/">
          <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
          <span class="brand-text">Achar</span>
        </router-link>

        <nav class="top-links">
          <router-link to="/">Home</router-link>
          <router-link to="/about">About</router-link>
          <router-link to="/services/packages">Packages</router-link>
          <router-link to="/services/overall">Overall Service</router-link>
          <router-link to="/booking">My Booking</router-link>
          <router-link to="/favorite">Favorite</router-link>
          <router-link to="/contact" class="active">Contact</router-link>
        </nav>

        <div class="top-actions">
          <router-link class="top-logout top-signin" to="/legacy-app">Sign in</router-link>
        </div>
      </div>
    </header>

    <main class="shell contact-content">
      <section class="card contact-hero">
        <p class="contact-chip">Contact Achar Event Booking</p>
        <h1>Let us help you plan and book your next event with confidence.</h1>
        <p>
          Have questions about vendors, bookings, service packages, or account setup? Reach out to our team and
          we will guide you through the best way to use the platform.
        </p>
      </section>

      <section class="contact-layout">
        <article class="card contact-info">
          <h2>Platform Support</h2>
          <p>Our support team is available to assist with booking flow, vendor communication, and account issues.</p>

          <div class="contact-item">
            <small>Email</small>
            <strong>support@acharbooking.com</strong>
          </div>
          <div class="contact-item">
            <small>Phone</small>
            <strong>+1 (555) 123-4588</strong>
          </div>
          <div class="contact-item">
            <small>Office Hours</small>
            <strong>Monday - Friday, 9:00 AM - 6:00 PM</strong>
          </div>
          <div class="contact-item">
            <small>Address</small>
            <strong>1200 Market Street, Suite 410, San Francisco, CA</strong>
          </div>
        </article>

        <article class="card contact-form-card">
          <h2>Send a message</h2>
          <form class="contact-form" @submit.prevent="submitContactForm">
            <label>
              Full name
              <input v-model.trim="form.name" type="text" required />
            </label>
            <label>
              Email address
              <input v-model.trim="form.email" type="email" required />
            </label>
            <label>
              Topic
              <select v-model="form.topic">
                <option>General inquiry</option>
                <option>Booking support</option>
                <option>Vendor onboarding</option>
                <option>Technical issue</option>
              </select>
            </label>
            <label>
              Message
              <textarea
                v-model.trim="form.message"
                rows="5"
                required
                placeholder="Describe your question or concern"
              ></textarea>
            </label>
            <button type="submit" class="contact-submit">Send Message</button>
            <p v-if="successMessage" class="contact-success">{{ successMessage }}</p>
          </form>
        </article>
      </section>
    </main>
  </div>
</template>

<style scoped>
.contact-page-root {
  min-height: 100vh;
}

.top-signin {
  text-decoration: none;
  display: inline-flex;
  align-items: center;
}

.contact-content {
  padding: 1.2rem 0 2.2rem;
}

.contact-hero {
  padding: 1.4rem;
  background:
    radial-gradient(circle at 92% 8%, rgba(255, 106, 0, 0.13), transparent 42%),
    linear-gradient(180deg, #ffffff, #fbfdff);
}

.contact-chip {
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

.contact-hero h1 {
  margin: 0.7rem 0 0;
  font-size: clamp(2rem, 3vw, 3rem);
  line-height: 1.05;
}

.contact-hero p {
  margin: 0.75rem 0 0;
  max-width: 860px;
  color: #475569;
  line-height: 1.64;
}

.contact-layout {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 0.9rem;
}

.contact-info,
.contact-form-card {
  padding: 1.1rem;
}

.contact-info h2,
.contact-form-card h2 {
  margin: 0;
  font-size: 1.5rem;
}

.contact-info p {
  margin: 0.55rem 0 0.8rem;
  color: #64748b;
  line-height: 1.55;
}

.contact-item {
  border: 1px solid #dce5f3;
  border-radius: 12px;
  background: #f8fbff;
  padding: 0.7rem 0.8rem;
  display: grid;
  gap: 0.2rem;
  margin-top: 0.6rem;
}

.contact-item small {
  color: #64748b;
  text-transform: uppercase;
  font-size: 0.74rem;
  font-weight: 700;
}

.contact-item strong {
  color: #1e293b;
  font-size: 0.95rem;
}

.contact-form {
  margin-top: 0.7rem;
  display: grid;
  gap: 0.65rem;
}

.contact-form label {
  display: grid;
  gap: 0.3rem;
  color: #475569;
  font-size: 0.9rem;
}

.contact-form input,
.contact-form select,
.contact-form textarea {
  border: 1px solid #dce4f1;
  border-radius: 11px;
  padding: 0.62rem 0.74rem;
  font: inherit;
  background: #fff;
}

.contact-form textarea {
  resize: vertical;
}

.contact-submit {
  border: 0;
  border-radius: 11px;
  background: #ff6a00;
  color: #fff;
  font: inherit;
  font-weight: 700;
  padding: 0.68rem 0.9rem;
  cursor: pointer;
}

.contact-submit:hover {
  background: #e45800;
}

.contact-success {
  margin: 0.15rem 0 0;
  border: 1px solid #bde6d0;
  border-radius: 10px;
  background: #e9f8ef;
  color: #166534;
  padding: 0.58rem 0.66rem;
  font-size: 0.9rem;
}

@media (max-width: 980px) {
  .contact-layout {
    grid-template-columns: 1fr;
  }
}
</style>
