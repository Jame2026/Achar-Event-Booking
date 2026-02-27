<script setup>
import { computed, ref } from 'vue'
import DashboardPage from './pages/DashboardPage.vue'
import CustomizationPage from './pages/CustomizationPage.vue'
import BookingsPage from './pages/BookingsPage.vue'
import {
  buildPackageServiceDescriptions,
  eventTypeMap,
  eventTypeOptions,
  matchingServicesCatalog,
  packageCatalogByEventType,
  packageImageByEventType,
  serviceFeeRate,
  vendorProfile,
} from '../features/appData'
import { useRouter } from 'vue-router'

const props = defineProps({
  section: {
    type: String,
    default: 'dashboard',
  },
})
const section = computed(() => props.section)
const FAVORITES_STORAGE_KEY = 'achar_guest_favorites'

const pageContent = computed(() => {
  if (props.section === 'favorite') {
    return {
      title: 'Favorite',
      subtitle: 'Your saved packages and services.',
      text: 'Favorites are saved on this device. Sign in to sync across your account.',
    }
  }

  if (props.section === 'bookings') {
    return {
      title: 'My Booking',
      subtitle: 'No booking data yet.',
      text: 'Sign in to view your booking history, upcoming events, and confirmations.',
    }
  }

  if (props.section === 'services-packages') {
    return {
      title: 'Service Packages',
      subtitle: 'Browse available packages by event type.',
      text: 'Click any package card to see full details and included services.',
    }
  }

  if (props.section === 'services-overall') {
    return {
      title: 'Overall Service',
      subtitle: 'No overall service data yet.',
      text: 'Sign in to select multiple services and packages for pre-booking.',
    }
  }

  if (props.section === 'customization') {
    return {
      title: 'Customization',
      subtitle: 'No customization data yet.',
      text: 'Sign in to customize your package and save your selected services.',
    }
  }

  return {
    title: 'Dashboard',
    subtitle: 'No dashboard data yet.',
    text: 'Sign in to view your dashboard, activity, and quick actions.',
  }
})

const bookingFilter = ref('Upcoming')
const bookingEventTypeFilter = ref('all')
const customizationEventType = ref('all')
const customizationSearch = ref('')
const selectedCustomizationPackageId = ref(null)
const customizationQuantity = ref(1)

const bookingBindings = { bookingFilter, bookingEventTypeFilter }
const customizationBindings = {
  customizationEventType,
  customizationSearch,
  selectedCustomizationPackageId,
  customizationQuantity,
}
const router = useRouter()

const emptyDashboardStats = {
  totalBookings: 0,
  upcomingBookings: 0,
  completedBookings: 0,
  unreadMessages: 0,
}
const savedFavorites = (() => {
  try {
    const raw = localStorage.getItem(FAVORITES_STORAGE_KEY)
    if (!raw) return { packageIds: [], serviceIds: [] }
    const parsed = JSON.parse(raw)
    return {
      packageIds: Array.isArray(parsed.packageIds) ? parsed.packageIds : [],
      serviceIds: Array.isArray(parsed.serviceIds) ? parsed.serviceIds : [],
    }
  } catch {
    return { packageIds: [], serviceIds: [] }
  }
})()
const favoritePackageIds = ref(savedFavorites.packageIds)
const favoriteServiceIds = ref(savedFavorites.serviceIds)

const guestPreviewPackages = computed(() => {
  const rows = []
  Object.entries(packageCatalogByEventType).forEach(([eventType, entries]) => {
    entries.forEach((entry) => {
      const price = Number(entry.basePrice || 0)
      rows.push({
        id: `guest-${eventType}-${entry.id}`,
        title: entry.title,
        eventType,
        eventTypeLabel: eventTypeMap[eventType] || 'Other',
        description: entry.description,
        location: 'Location available after sign in',
        date: 'Date TBD',
        price,
        priceLabel: `From $${price.toLocaleString()}`,
        image: packageImageByEventType[eventType] || packageImageByEventType.other,
        services: buildPackageServiceDescriptions(eventType, entry.title),
        isPreview: true,
      })
    })
  })
  return rows
})
const activePackageId = ref(null)
const activePackage = computed(
  () => guestPreviewPackages.value.find((item) => item.id === activePackageId.value) || null,
)

function openPackageDetails(id) {
  activePackageId.value = id
}

function closePackageDetails() {
  activePackageId.value = null
}

function persistFavorites() {
  localStorage.setItem(
    FAVORITES_STORAGE_KEY,
    JSON.stringify({
      packageIds: favoritePackageIds.value,
      serviceIds: favoriteServiceIds.value,
    }),
  )
}

function toggleFavoritePackage(id) {
  if (favoritePackageIds.value.includes(id)) {
    favoritePackageIds.value = favoritePackageIds.value.filter((item) => item !== id)
  } else {
    favoritePackageIds.value = [...favoritePackageIds.value, id]
  }
  persistFavorites()
}

function toggleFavoriteService(id) {
  if (favoriteServiceIds.value.includes(id)) {
    favoriteServiceIds.value = favoriteServiceIds.value.filter((item) => item !== id)
  } else {
    favoriteServiceIds.value = [...favoriteServiceIds.value, id]
  }
  persistFavorites()
}

function isPackageFavorite(id) {
  return favoritePackageIds.value.includes(id)
}

function isServiceFavorite(id) {
  return favoriteServiceIds.value.includes(id)
}

const favoritePackages = computed(() =>
  guestPreviewPackages.value.filter((item) => favoritePackageIds.value.includes(item.id)),
)

const favoriteServices = computed(() =>
  matchingServicesCatalog.filter((service) => favoriteServiceIds.value.includes(service.id)),
)

function goToSignIn() {
  router.push('/legacy-app')
}

function goToSection(nextSection) {
  if (nextSection === 'bookings') router.push('/booking')
  if (nextSection === 'services-packages') router.push('/services/packages')
  if (nextSection === 'services-overall') router.push('/services/overall')
  if (nextSection === 'dashboard') router.push('/dashboard')
  if (nextSection === 'customization') router.push('/customization')
  if (nextSection === 'favorite') router.push('/favorite')
}

function noop() {}
</script>

<template>
  <div class="guest-page">
    <header class="topbar">
      <div class="shell topbar-inner">
        <router-link class="brand" to="/">
          <img class="brand-logo" src="/achar-logo.png" alt="Achar logo" />
          <span class="brand-text">Achar</span>
        </router-link>

        <nav class="top-links">
          <router-link to="/">Home</router-link>
          <router-link to="/about">About</router-link>
          <div class="service-menu">
            <button type="button" class="service-menu-trigger" :class="{ active: section === 'services-packages' || section === 'services-overall' }">Service</button>
            <div class="service-menu-dropdown">
              <router-link to="/services/packages" :class="{ active: section === 'services-packages' }">Packages</router-link>
              <router-link to="/services/overall" :class="{ active: section === 'services-overall' }">Overall Service</router-link>
            </div>
          </div>
          <router-link to="/booking" :class="{ active: section === 'bookings' }">My Booking</router-link>
          <router-link to="/favorite" :class="{ active: section === 'favorite' }">Favorite</router-link>
          <router-link to="/contact">Contact</router-link>
        </nav>

        <div class="top-actions">
          <input type="search" placeholder="Search services..." disabled />
          <router-link class="top-logout top-signin" to="/legacy-app">Sign in</router-link>
        </div>
      </div>
    </header>

    <main class="shell guest-content">
      <section class="guest-panel">
        <h1>{{ pageContent.title }}</h1>
        <p class="guest-subtitle">{{ pageContent.subtitle }}</p>
        <p class="guest-text">{{ pageContent.text }}</p>
      </section>

      <DashboardPage
        v-if="section === 'dashboard'"
        :notice="'Sign in to load your dashboard data.'"
        :customer-name="''"
        :dashboard-stats="emptyDashboardStats"
        :recent-bookings="[]"
        :recent-conversations="[]"
        :go-to-vendor="() => goToSection('services-packages')"
        :go-to-bookings="() => goToSection('bookings')"
        :go-to-messages="goToSignIn"
        :go-to-package-customization="() => goToSection('customization')"
        :open-upcoming-bookings="() => goToSection('bookings')"
      />

      <section v-else-if="section === 'services-packages'" class="package-catalog">
        <div class="package-grid">
          <article
            v-for="item in guestPreviewPackages"
            :key="item.id"
            class="package-product-card"
            role="button"
            tabindex="0"
            @click="openPackageDetails(item.id)"
            @keyup.enter="openPackageDetails(item.id)"
          >
            <img :src="item.image" :alt="item.title" />
            <div class="package-product-body">
              <p class="package-product-type">{{ item.eventTypeLabel }}</p>
              <h3>{{ item.title }}</h3>
              <p class="package-product-desc">{{ item.description }}</p>
              <div class="package-product-footer">
                <strong>{{ item.priceLabel }}</strong>
                <div class="package-product-actions">
                  <button
                    type="button"
                    class="favorite-btn"
                    :class="{ active: isPackageFavorite(item.id) }"
                    @click.stop="toggleFavoritePackage(item.id)"
                  >
                    {{ isPackageFavorite(item.id) ? '♥' : '♡' }}
                  </button>
                  <span>View Details</span>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>

      <CustomizationPage
        v-else-if="section === 'services-overall' || section === 'customization'"
        :event-type-options="eventTypeOptions"
        :event-type-map="eventTypeMap"
        :service-fee-rate="serviceFeeRate"
        :vendor-profile="vendorProfile"
        :bindings="customizationBindings"
        :filtered-customization-packages="guestPreviewPackages"
        :selected-services-count="0"
        :customization-total="0"
        :selected-customization-package="null"
        :selected-matching-services="[]"
        :selected-services-subtotal="0"
        :customization-package-subtotal="0"
        :service-fee-amount="0"
        :booking-submitting-event-id="null"
        :effective-customization-event-type="'all'"
        :filtered-matching-services="matchingServicesCatalog"
        :is-package-expanded="() => false"
        :toggle-package-details="noop"
        :go-to-availability="goToSignIn"
        :go-to-messages="goToSignIn"
        :select-customization-package="noop"
        :is-service-selected="() => false"
        :is-service-expanded="() => false"
        :toggle-matching-service="noop"
        :toggle-service-details="noop"
        :confirm-customization="goToSignIn"
        :is-package-favorite="isPackageFavorite"
        :is-service-favorite="isServiceFavorite"
        :toggle-favorite-package="toggleFavoritePackage"
        :toggle-favorite-service="toggleFavoriteService"
      />

      <section v-else-if="section === 'favorite'" class="guest-panel guest-favorite-block">
        <div class="favorite-layout">
          <article class="favorite-card">
            <h3>Favorite Packages</h3>
            <p v-if="favoritePackages.length === 0" class="guest-text">No packages added yet.</p>
            <ul v-else class="favorite-list">
              <li v-for="item in favoritePackages" :key="item.id">
                <div>
                  <strong>{{ item.title }}</strong>
                  <small>{{ item.eventTypeLabel }} | {{ item.priceLabel }}</small>
                </div>
                <button type="button" class="favorite-remove" @click="toggleFavoritePackage(item.id)">Remove</button>
              </li>
            </ul>
          </article>

          <article class="favorite-card">
            <h3>Favorite Services</h3>
            <p v-if="favoriteServices.length === 0" class="guest-text">No services added yet.</p>
            <ul v-else class="favorite-list">
              <li v-for="service in favoriteServices" :key="service.id">
                <div>
                  <strong>{{ service.name }}</strong>
                  <small>${{ Number(service.price || 0).toLocaleString() }}</small>
                </div>
                <button type="button" class="favorite-remove" @click="toggleFavoriteService(service.id)">Remove</button>
              </li>
            </ul>
          </article>
        </div>
      </section>

      <BookingsPage
      v-else
        :bindings="bookingBindings"
        :event-type-options="eventTypeOptions"
        :notice="'Sign in to load your bookings.'"
        :is-loading-bookings="false"
        :filtered-bookings="[]"
        :go-to-dashboard="() => goToSection('dashboard')"
        :go-to-vendor="() => goToSection('services-packages')"
        :go-to-messages="goToSignIn"
        :go-to-profile="goToSignIn"
        :booking-secondary-action="noop"
        :booking-primary-action="noop"
      />
    </main>

    <div v-if="activePackage" class="package-modal-overlay" @click="closePackageDetails">
      <div class="package-modal" @click.stop>
        <div class="package-modal-head">
          <div>
            <p class="package-product-type">{{ activePackage.eventTypeLabel }}</p>
            <h3>{{ activePackage.title }}</h3>
          </div>
          <button type="button" class="package-modal-close" @click="closePackageDetails">×</button>
        </div>
        <img class="package-modal-image" :src="activePackage.image" :alt="activePackage.title" />
        <p class="package-modal-desc">{{ activePackage.description }}</p>
        <div class="package-modal-meta">
          <p><strong>Price:</strong> {{ activePackage.priceLabel }}</p>
          <p><strong>Location:</strong> {{ activePackage.location }}</p>
          <p><strong>Date:</strong> {{ activePackage.date }}</p>
        </div>
        <div class="package-modal-services">
          <h4>Included Services</h4>
          <ul>
            <li v-for="service in activePackage.services" :key="`${activePackage.id}-${service.name}`">
              <strong>{{ service.name }}:</strong> {{ service.detail }}
            </li>
          </ul>
        </div>
        <div class="package-modal-actions">
          <button
            type="button"
            class="choice-indicator"
            @click="toggleFavoritePackage(activePackage.id)"
          >
            {{ isPackageFavorite(activePackage.id) ? 'Remove Favorite' : 'Add to Favorite' }}
          </button>
          <button type="button" class="top-logout" @click="goToSignIn">Sign in to Pre-book</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.guest-page {
  min-height: 100vh;
}

.guest-content {
  padding-top: 14px;
}

.guest-panel {
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: #fff;
  padding: 18px 22px;
  margin-bottom: 14px;
}

.guest-panel h1 {
  margin: 0;
  font-size: 30px;
}

.guest-subtitle {
  margin: 10px 0 0;
  color: #6b7280;
  font-weight: 700;
}

.guest-text {
  margin: 8px 0 0;
  color: #6b7280;
}

.guest-favorite-block {
  margin-top: 0;
}

.package-catalog {
  margin-top: 6px;
}

.package-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 14px;
}

.package-product-card {
  border: 1px solid #dbe4f1;
  border-radius: 16px;
  background: #fff;
  overflow: hidden;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.07);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.package-product-card:hover {
  transform: translateY(-3px);
  border-color: #cbd7ea;
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
}

.package-product-card img {
  width: 100%;
  height: 164px;
  object-fit: cover;
}

.package-product-body {
  padding: 12px;
  display: grid;
  gap: 6px;
}

.package-product-type {
  margin: 0;
  color: #e45800;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.package-product-body h3 {
  margin: 0;
  font-size: 18px;
  line-height: 1.25;
}

.package-product-desc {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.45;
}

.package-product-footer {
  margin-top: 6px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.package-product-actions {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.package-product-footer strong {
  color: #1e293b;
  font-size: 15px;
}

.package-product-footer span {
  color: #e45800;
  font-weight: 700;
  font-size: 13px;
}

.package-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 90;
}

.package-modal {
  width: min(760px, 100%);
  max-height: 88vh;
  overflow-y: auto;
  border: 1px solid #dde5f2;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.3);
  padding: 16px;
}

.package-modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.package-modal-head h3 {
  margin: 2px 0 0;
  font-size: 24px;
}

.package-modal-close {
  border: 1px solid #dbe4f1;
  border-radius: 10px;
  background: #fff;
  width: 34px;
  height: 34px;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.package-modal-image {
  width: 100%;
  height: 280px;
  object-fit: cover;
  border-radius: 12px;
  border: 1px solid #e2e8f3;
  margin-top: 10px;
}

.package-modal-desc {
  margin: 12px 0 0;
  color: #475569;
  line-height: 1.5;
}

.package-modal-meta {
  margin-top: 10px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

.package-modal-meta p {
  margin: 0;
  border: 1px solid #e2e8f3;
  background: #f8fafc;
  border-radius: 10px;
  padding: 10px;
  font-size: 13px;
}

.package-modal-services {
  margin-top: 14px;
}

.package-modal-services h4 {
  margin: 0;
}

.package-modal-services ul {
  margin: 8px 0 0;
  padding-left: 18px;
  display: grid;
  gap: 6px;
}

.package-modal-services li {
  color: #5a6e88;
  line-height: 1.4;
}

.package-modal-actions {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.favorite-layout {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.favorite-card {
  border: 1px solid #e2e8f3;
  border-radius: 14px;
  background: #fbfdff;
  padding: 12px;
}

.favorite-card h3 {
  margin: 0;
}

.favorite-list {
  list-style: none;
  margin: 10px 0 0;
  padding: 0;
  display: grid;
  gap: 8px;
}

.favorite-list li {
  border: 1px solid #e2e8f3;
  border-radius: 10px;
  padding: 9px 10px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.favorite-list strong {
  display: block;
  font-size: 14px;
}

.favorite-list small {
  color: #64748b;
}

.favorite-remove {
  border: 1px solid #d6e0ef;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 8px;
  cursor: pointer;
}

.favorite-remove:hover {
  background: #f8fafc;
}

.top-signin {
  text-decoration: none;
  display: inline-flex;
  align-items: center;
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

.service-menu-trigger.active,
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

.service-menu-dropdown a.active,
.service-menu-dropdown a:hover {
  background: #f8fafc;
}

@media (max-width: 720px) {
  .favorite-layout {
    grid-template-columns: 1fr;
  }

  .package-modal-meta {
    grid-template-columns: 1fr;
  }

  .package-modal-image {
    height: 200px;
  }

  .top-signin {
    justify-content: center;
  }
}
</style>
