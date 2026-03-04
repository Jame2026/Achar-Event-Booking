<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const bankLogoError = ref({})

const bankPartners = [
  {
    name: 'ABA Pay',
    logo: '/ABA.png',
  },
  {
    name: 'Wing Bank',
    logo: '/wing.png',
  },
  {
    name: 'ACLEDA Bank',
    logo: '/Ac.png',
  },
]

function onAppLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

function onBankLogoError(name) {
  bankLogoError.value = {
    ...bankLogoError.value,
    [name]: true,
  }
}
</script>

<template>
  <footer class="site-footer">
    <div class="shell footer-main">
      <div class="brand-col">
        <div class="brand-head">
          <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onAppLogoError" />
          <div>
            <h3>Achar Event Booking</h3>
            <p class="brand-sub">Plan with confidence</p>
          </div>
        </div>
        <p>
          A trusted platform for planning, customizing, and booking event
          services with verified vendors.
        </p>
        <div class="trust-row">
          <span class="pill">Verified Vendors</span>
          <span class="pill">Secure Booking</span>
        </div>
      </div>

      <div class="links-col">
        <h4>Navigation</h4>
        <nav class="nav-links">
          <RouterLink to="/">Home</RouterLink>
          <RouterLink to="/about">About</RouterLink>
          <RouterLink to="/services/packages">Service</RouterLink>
          <RouterLink to="/booking">My Booking</RouterLink>
          <RouterLink to="/favorite">Favorite</RouterLink>
          <RouterLink to="/contact">Contact</RouterLink>
        </nav>
      </div>

      <div class="links-col">
        <h4>Payment Partners</h4>
        <p class="muted">Supported by trusted banking partners in Cambodia.</p>
        <div class="payments">
          <div v-for="bank in bankPartners" :key="bank.name" class="payment-card">
            <div class="payment-link">
              <img
                v-if="!bankLogoError[bank.name]"
                class="payment-logo"
                :src="bank.logo"
                :alt="`${bank.name} logo`"
                loading="lazy"
                @error="onBankLogoError(bank.name)"
              />
              <div v-else class="payment-logo-placeholder"></div>
              <span class="payment-name">{{ bank.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="links-col">
        <h4>Vendor Partnership</h4>
        <p class="muted">
          We collaborate with professional service providers to ensure reliable
          quality and responsive support for every booking.
        </p>
        <RouterLink class="partner-link" to="/services/packages">Explore Services</RouterLink>
      </div>
    </div>

    <div class="shell footer-bottom">
      <span class="copyright">
        &copy; {{ new Date().getFullYear() }} Achar Event Booking. All rights
        reserved.
      </span>
      <div class="legal-links">
        <RouterLink to="/about">Privacy Policy</RouterLink>
        <RouterLink to="/about">Terms of Service</RouterLink>
        <RouterLink to="/contact">Support</RouterLink>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  margin-top: 24px;
  border-top: 1px solid #d9e2ef;
  background:
    radial-gradient(circle at top right, rgba(255, 106, 0, 0.08), transparent 36%),
    linear-gradient(180deg, #ffffff, #f7faff);
}

.footer-main {
  display: grid;
  grid-template-columns: 1.3fr 1fr 1fr 1fr;
  gap: 18px;
  padding: 26px 0 18px;
}

.brand-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.brand-logo {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid #e5ebf4;
  background: #fff;
}

.brand-col h3 {
  margin: 0;
  color: #1e293b;
  font-size: 20px;
  line-height: 1.2;
}

.brand-sub {
  margin: 2px 0 0;
  font-size: 12px;
  color: #64748b;
}

.brand-col p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
  max-width: 370px;
}

.trust-row {
  margin-top: 14px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pill {
  border: 1px solid #ffcfb0;
  background: #fff6ef;
  color: #c2410c;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 12px;
  font-weight: 600;
}

.links-col h4 {
  margin: 0 0 10px;
  color: #334155;
  font-size: 15px;
}

.nav-links {
  display: grid;
  gap: 6px;
}

.nav-links a {
  color: #64748b;
  font-size: 13px;
  text-decoration: none;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  color: #c2410c;
}

.muted {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.55;
}

.payments {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
}

.payment-card {
  border: 1px solid #d9e2ef;
  border-radius: 10px;
  background: #fff;
  padding: 7px 10px;
  min-height: 42px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.payment-link {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  text-decoration: none;
}

.payment-logo {
  width: 42px;
  height: 26px;
  object-fit: contain;
  flex: 0 0 auto;
}

.payment-logo-placeholder {
  width: 42px;
  height: 26px;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  background: #f8fafc;
}

.payment-name {
  color: #334155;
  font-size: 13px;
  font-weight: 600;
}

.partner-link {
  display: inline-block;
  margin-top: 10px;
  color: #c2410c;
  font-size: 13px;
  text-decoration: none;
  font-weight: 600;
}

.footer-bottom {
  border-top: 1px solid #e5ebf4;
  padding: 12px 0 16px;
  color: #64748b;
  font-size: 12px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.copyright {
  color: #64748b;
}

.legal-links {
  display: flex;
  gap: 14px;
}

.legal-links a {
  color: #475569;
  text-decoration: none;
}

.legal-links a:hover {
  color: #c2410c;
}

@media (max-width: 980px) {
  .footer-main {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 680px) {
  .footer-main {
    grid-template-columns: 1fr;
    gap: 14px;
    padding-top: 20px;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
  }

  .legal-links {
    gap: 12px;
  }
}
</style>
