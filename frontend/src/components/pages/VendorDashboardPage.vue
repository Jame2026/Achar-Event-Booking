<script setup>
import { computed, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps([
  'appLogoSrc',
  'vendorDisplayName',
  'activeTab',
  'eventTypeOptions',
  'vendorEvents',
  'vendorBookings',
  'isLoadingEvents',
  'isLoadingVendorBookings',
  'vendorServiceForm',
  'isSubmittingVendorService',
  'vendorServiceNotice',
  'vendorIncome',
  'messagesSummary',
  'submitVendorService',
  'toggleVendorServiceActive',
  'deleteVendorService',
  'updateVendorBookingStatus',
  'goToMessages',
  'logoutUser',
])

const emit = defineEmits(['update:activeTab'])

const localActiveTab = ref(typeof props.activeTab === 'string' ? props.activeTab : 'overview')
const isCreateServiceModalOpen = ref(false)
const isDetectingVendorLocation = ref(false)
const vendorLocationNotice = ref('')

const navItems = [
  { key: 'overview', label: 'Overview', number: '01' },
  { key: 'services', label: 'My Services', number: '02' },
  { key: 'bookings', label: 'Bookings', number: '03' },
  { key: 'messages', label: 'Messages', number: '04' },
  { key: 'income', label: 'Analytics', number: '05' },
]

const safeIncome = computed(() => ({
  total: Number(props.vendorIncome?.total || 0),
  confirmedCount: Number(props.vendorIncome?.confirmedCount || 0),
  newBookings: Number(props.vendorIncome?.newBookings || 0),
  thisMonth: Number(props.vendorIncome?.thisMonth || 0),
  thisYear: Number(props.vendorIncome?.thisYear || 0),
  activeServices: Number(props.vendorIncome?.activeServices || 0),
}))

const safeMessagesSummary = computed(() => Number(props.messagesSummary || 0))
const safeVendorEvents = computed(() => (Array.isArray(props.vendorEvents) ? props.vendorEvents : []))
const safeVendorBookings = computed(() => (Array.isArray(props.vendorBookings) ? props.vendorBookings : []))
const vendorServiceNoticeTone = computed(() => {
  const message = String(props.vendorServiceNotice || '').trim().toLowerCase()
  if (!message) return ''
  if (
    message.includes('success') ||
    message.includes('created') ||
    message.includes('saved') ||
    message.includes('visible')
  ) {
    return 'success'
  }
  return 'error'
})
const eventOptions = computed(() =>
  Array.isArray(props.eventTypeOptions)
    ? props.eventTypeOptions.filter((item) => item?.value && item.value !== 'all')
    : [],
)

function setActiveTab(tabKey) {
  localActiveTab.value = tabKey
  emit('update:activeTab', tabKey)
}

function openCreateServiceModal() {
  isCreateServiceModalOpen.value = true
  setActiveTab('services')
}

function closeCreateServiceModal() {
  isCreateServiceModalOpen.value = false
}

function submitServiceForm() {
  if (typeof props.submitVendorService === 'function') {
    props.submitVendorService()
  }
}

function handleVendorServiceImageChange(event) {
  const [file] = Array.from(event?.target?.files || [])
  if (!props.vendorServiceForm) return

  if (!file) {
    props.vendorServiceForm.image_file = null
    return
  }

  props.vendorServiceForm.image_file = file

  const reader = new FileReader()
  reader.onload = () => {
    props.vendorServiceForm.image_url = typeof reader.result === 'string' ? reader.result : ''
  }
  reader.readAsDataURL(file)
}

function clearVendorServiceImage() {
  if (!props.vendorServiceForm) return
  props.vendorServiceForm.image_file = null
  props.vendorServiceForm.image_url = ''
}

function handleVendorServiceImageUrlInput(event) {
  if (!props.vendorServiceForm) return
  props.vendorServiceForm.image_file = null
  props.vendorServiceForm.image_url = event?.target?.value || ''
}

const vendorLocationMapEmbedUrl = computed(() => {
  const lat = Number(props.vendorServiceForm?.location_latitude)
  const lng = Number(props.vendorServiceForm?.location_longitude)
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return ''

  const safeLat = Number(lat.toFixed(6))
  const safeLng = Number(lng.toFixed(6))
  const delta = 0.012
  const left = (safeLng - delta).toFixed(6)
  const bottom = (safeLat - delta).toFixed(6)
  const right = (safeLng + delta).toFixed(6)
  const top = (safeLat + delta).toFixed(6)

  return `https://www.openstreetmap.org/export/embed.html?bbox=${left}%2C${bottom}%2C${right}%2C${top}&layer=mapnik&marker=${safeLat}%2C${safeLng}`
})

const vendorLocationMapLinkUrl = computed(() => {
  const lat = Number(props.vendorServiceForm?.location_latitude)
  const lng = Number(props.vendorServiceForm?.location_longitude)
  if (!Number.isFinite(lat) || !Number.isFinite(lng)) return ''

  const safeLat = Number(lat.toFixed(6))
  const safeLng = Number(lng.toFixed(6))
  return `https://www.openstreetmap.org/?mlat=${safeLat}&mlon=${safeLng}#map=14/${safeLat}/${safeLng}`
})

async function detectVendorLocation() {
  if (!props.vendorServiceForm) return

  if (!navigator.geolocation) {
    vendorLocationNotice.value = 'Geolocation is not supported by this browser.'
    return
  }

  isDetectingVendorLocation.value = true
  vendorLocationNotice.value = ''

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const lat = Number(position.coords.latitude.toFixed(6))
      const lng = Number(position.coords.longitude.toFixed(6))
      let placeName = ''

      try {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        const response = await fetch(url, {
          headers: {
            Accept: 'application/json',
            'Accept-Language': 'en',
          },
        })

        if (response.ok) {
          const data = await response.json()
          const address = data?.address || {}
          const streetNumber = address.house_number || address.house_name || address.building || ''
          const streetName = address.road || address.pedestrian || address.footway || ''
          const street = [streetNumber, streetName].filter(Boolean).join(' ').trim()
          const village = address.village || address.hamlet || address.neighbourhood || address.suburb || ''
          const district = address.city_district || address.district || address.borough || address.county || ''
          const city = address.city || address.town || address.municipality || address.state_district || ''
          const province = address.state || address.region || address.province || ''
          const parts = [street, village, district, city, province].filter(
            (value) => String(value).trim().length > 0,
          )
          const primaryName =
            data?.name ||
            address.shop ||
            address.amenity ||
            address.tourism ||
            address.building ||
            ''

          placeName = parts.length ? parts.join(', ') : String(primaryName || '').trim()
        }
      } catch {
        placeName = ''
      }

      props.vendorServiceForm.location_latitude = lat
      props.vendorServiceForm.location_longitude = lng
      props.vendorServiceForm.location = placeName || `${lat}, ${lng}`
      vendorLocationNotice.value = 'Current location captured.'
      isDetectingVendorLocation.value = false
    },
    () => {
      vendorLocationNotice.value = 'Could not access your current location.'
      isDetectingVendorLocation.value = false
    },
    {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 0,
    },
  )
}

function openMessages() {
  setActiveTab('messages')
  if (typeof props.goToMessages === 'function') {
    props.goToMessages()
  }
}

function bookingStatusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value === 'confirmed') return 'ok'
  if (value === 'cancelled') return 'bad'
  return 'wait'
}

watch(
  () => props.activeTab,
  (value) => {
    if (typeof value === 'string' && value && value !== localActiveTab.value) {
      localActiveTab.value = value
    }
  },
)
</script>

<template>
  <main class="vendor-dashboard">
    <aside class="sidebar">
      <div class="brand">
        <img :src="props.appLogoSrc || '/achar-logo.png'" alt="Achar logo" />
        <div>
          <strong>Achar</strong>
          <span>Vendor Portal</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <button
          v-for="item in navItems"
          :key="item.key"
          type="button"
          class="sidebar-link"
          :class="{ active: localActiveTab === item.key }"
          @click="setActiveTab(item.key)"
        >
          <span class="sidebar-number">{{ item.number }}</span>
          <span>{{ item.label }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <RouterLink class="side-utility home" to="/">Back to Home</RouterLink>
        <button type="button" class="side-utility">Settings</button>
        <button type="button" class="side-utility logout" @click="props.logoutUser">Logout</button>
        <div class="vendor-card">
          <span class="vendor-avatar">{{ (props.vendorDisplayName || 'V').slice(0, 1).toUpperCase() }}</span>
          <div>
            <strong>{{ props.vendorDisplayName || 'Vendor' }}</strong>
            <small>Verified vendor workspace</small>
          </div>
        </div>
      </div>
    </aside>

    <section class="main-panel">
      <header class="hero">
        <div>
          <p class="eyebrow">Vendor dashboard</p>
          <h1>Achar Vendor Dashboard</h1>
          <p class="hero-copy">
            Manage your services, booking requests, and customer messages from one workspace.
          </p>
        </div>

        <div class="hero-side">
          <button type="button" class="primary-button" @click="openCreateServiceModal">
            + New Service
          </button>
          <div class="signed-user">
            <span>Signed in as</span>
            <strong>{{ props.vendorDisplayName || 'Vendor' }}</strong>
          </div>
        </div>
      </header>

      <section class="stats-grid">
        <article class="stat-card">
          <small>Total Income</small>
          <strong>${{ safeIncome.total.toLocaleString() }}</strong>
          <span>Confirmed bookings revenue</span>
        </article>
        <article class="stat-card">
          <small>Total Booked Services</small>
          <strong>{{ safeIncome.confirmedCount }}</strong>
          <span>Completed and active confirmations</span>
        </article>
        <article class="stat-card">
          <small>New Requests</small>
          <strong>{{ safeIncome.newBookings }}</strong>
          <span>Waiting for your response</span>
        </article>
        <article class="stat-card accent">
          <small>Unread Messages</small>
          <strong>{{ safeMessagesSummary }}</strong>
          <span>Customer conversations needing attention</span>
        </article>
      </section>

      <section v-show="localActiveTab === 'overview'" class="content-grid">
        <article class="panel panel-wide">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Performance</p>
              <h2>Income Trend Overview</h2>
            </div>
            <span class="badge">Last 30 days</span>
          </div>
          <div class="chart-placeholder">
            <div class="chart-line chart-line-a"></div>
            <div class="chart-line chart-line-b"></div>
            <div class="chart-line chart-line-c"></div>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Quick actions</p>
              <h2>Service Control</h2>
            </div>
          </div>
          <p class="panel-copy">
            Add a new service now or jump to your customer inbox.
          </p>
          <div class="action-row">
            <button type="button" class="primary-button" @click="openCreateServiceModal">
              Add Service
            </button>
            <button type="button" class="secondary-button" @click="openMessages">
              Open Messages
            </button>
          </div>
        </article>
      </section>

      <section v-show="localActiveTab === 'services'" class="content-grid services-grid">
        <article class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Create service</p>
              <h2>Insert Service</h2>
            </div>
            <span class="badge">Visible to users when active</span>
          </div>

          <form class="service-form" @submit.prevent="submitServiceForm">
            <label class="field">
              <span>Service name</span>
              <input
                :value="props.vendorServiceForm?.title || ''"
                type="text"
                placeholder="Community Workshop"
                @input="props.vendorServiceForm.title = $event.target.value"
              />
            </label>

            <label class="field">
              <span>Types</span>
              <select
                :value="props.vendorServiceForm?.event_type || ''"
                @change="props.vendorServiceForm.event_type = $event.target.value"
              >
                <option v-for="option in eventOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </label>

            <label class="field">
              <span>Number of count</span>
              <input
                :value="props.vendorServiceForm?.capacity ?? 1"
                type="number"
                min="1"
                placeholder="50"
                @input="props.vendorServiceForm.capacity = Number($event.target.value)"
              />
            </label>

            <label class="field">
              <span>Price</span>
              <input
                :value="props.vendorServiceForm?.price ?? 0"
                type="number"
                min="0"
                step="0.01"
                placeholder="150"
                @input="props.vendorServiceForm.price = Number($event.target.value)"
              />
            </label>

            <label class="field">
              <span>Location</span>
              <input
                :value="props.vendorServiceForm?.location || ''"
                type="text"
                placeholder="Phnom Penh"
                @input="props.vendorServiceForm.location = $event.target.value"
              />
            </label>

            <div class="field">
              <span>Map location</span>
              <button
                type="button"
                class="secondary-button location-button"
                :disabled="isDetectingVendorLocation"
                @click="detectVendorLocation"
              >
                {{ isDetectingVendorLocation ? 'Detecting location...' : 'Use Current Location' }}
              </button>
            </div>

            <div class="field field-full location-tools">
              <p v-if="vendorLocationNotice" class="location-notice">{{ vendorLocationNotice }}</p>
              <p
                v-if="
                  props.vendorServiceForm?.location_latitude !== null &&
                  props.vendorServiceForm?.location_longitude !== null
                "
                class="location-coords"
              >
                Lat: {{ Number(props.vendorServiceForm.location_latitude).toFixed(6) }}, Lng:
                {{ Number(props.vendorServiceForm.location_longitude).toFixed(6) }}
              </p>
              <iframe
                v-if="vendorLocationMapEmbedUrl"
                class="location-map-frame"
                :src="vendorLocationMapEmbedUrl"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
              <a
                v-if="vendorLocationMapLinkUrl"
                class="location-map-link"
                :href="vendorLocationMapLinkUrl"
                target="_blank"
                rel="noopener noreferrer"
              >
                Open current location in map
              </a>
            </div>

            <label class="field">
              <span>Start date and time</span>
              <input
                :value="props.vendorServiceForm?.starts_at || ''"
                type="datetime-local"
                @input="props.vendorServiceForm.starts_at = $event.target.value"
              />
            </label>

            <label class="field field-full">
              <span>Picture of services</span>
              <input type="file" accept="image/*" @change="handleVendorServiceImageChange" />
              <small class="field-hint">Choose an image from your device.</small>
            </label>

            <label class="field field-full">
              <span>Or paste image link</span>
              <input
                :value="props.vendorServiceForm?.image_url || ''"
                type="url"
                placeholder="https://example.com/service-photo.jpg"
                @input="handleVendorServiceImageUrlInput"
              />
            </label>

            <div v-if="props.vendorServiceForm?.image_url" class="field field-full">
              <span>Preview</span>
              <div class="image-preview-card">
                <img
                  class="image-preview"
                  :src="props.vendorServiceForm.image_url"
                  alt="Selected service preview"
                />
                <button type="button" class="secondary-button" @click="clearVendorServiceImage">
                  Remove image
                </button>
              </div>
            </div>

            <label class="field field-full">
              <span>Service information</span>
              <textarea
                :value="props.vendorServiceForm?.description || ''"
                placeholder="Describe the service, what is included, and what the customer should know."
                @input="props.vendorServiceForm.description = $event.target.value"
              ></textarea>
            </label>

            <div class="form-actions">
              <button type="submit" class="primary-button" :disabled="props.isSubmittingVendorService">
                {{ props.isSubmittingVendorService ? 'Saving...' : 'Create Service' }}
              </button>
            </div>
          </form>

          <p
            v-if="props.vendorServiceNotice"
            class="notice"
            :class="{ 'notice-success': vendorServiceNoticeTone === 'success', 'notice-error': vendorServiceNoticeTone === 'error' }"
          >
            {{ props.vendorServiceNotice }}
          </p>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Current listings</p>
              <h2>My Services</h2>
            </div>
            <span class="badge">{{ safeVendorEvents.length }} services</span>
          </div>

          <p v-if="props.isLoadingEvents" class="notice">Loading services...</p>
          <p v-else-if="!safeVendorEvents.length" class="notice">
            No service yet. Create one from the form.
          </p>
          <ul v-else class="service-list">
            <li v-for="item in safeVendorEvents" :key="item.id" class="service-item">
              <img class="service-image" :src="item.image" :alt="item.title" />
              <div class="service-copy">
                <div class="service-header">
                  <strong>{{ item.title }}</strong>
                  <span class="service-state" :class="{ live: item.isActive }">
                    {{ item.isActive ? 'Active' : 'Paused' }}
                  </span>
                </div>
                <small>{{ item.eventTypeLabel }} | {{ item.priceLabel }}</small>
                <p>{{ item.description }}</p>
              </div>
              <div class="row-actions">
                <button type="button" @click="props.toggleVendorServiceActive(item)">
                  {{ item.isActive ? 'Pause' : 'Activate' }}
                </button>
                <button type="button" class="danger" @click="props.deleteVendorService(item)">
                  Delete
                </button>
              </div>
            </li>
          </ul>
        </article>
      </section>

      <section v-show="localActiveTab === 'bookings'" class="panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Requests</p>
            <h2>New Booking Requests</h2>
          </div>
        </div>

        <p v-if="props.isLoadingVendorBookings" class="notice">Loading bookings...</p>
        <p v-else-if="!safeVendorBookings.length" class="notice">
          No booking requests yet.
        </p>
        <table v-else class="table">
          <thead>
            <tr>
              <th>Service Name</th>
              <th>Client</th>
              <th>Date & Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in safeVendorBookings" :key="item.id">
              <td>{{ item.service_name }}</td>
              <td>{{ item.customer_name }}</td>
              <td>{{ item.date_label }}</td>
              <td>
                <span class="status-chip" :class="bookingStatusClass(item.status)">
                  {{ item.status }}
                </span>
              </td>
              <td class="row-actions">
                <button type="button" @click="props.updateVendorBookingStatus(item, 'confirmed')">
                  Confirm
                </button>
                <button
                  type="button"
                  class="danger"
                  @click="props.updateVendorBookingStatus(item, 'cancelled')"
                >
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <section v-show="localActiveTab === 'messages'" class="panel">
        <div class="panel-head">
          <div>
            <p class="eyebrow">Inbox</p>
            <h2>Customer Messages</h2>
          </div>
          <span class="badge">{{ safeMessagesSummary }} unread</span>
        </div>
        <p class="panel-copy">
          Respond quickly to customer questions and booking confirmations.
        </p>
        <button type="button" class="primary-button" @click="openMessages">Open Messages</button>
      </section>

      <section v-show="localActiveTab === 'income'" class="stats-grid">
        <article class="stat-card">
          <small>This Month</small>
          <strong>${{ safeIncome.thisMonth.toLocaleString() }}</strong>
          <span>Monthly income estimate</span>
        </article>
        <article class="stat-card">
          <small>This Year</small>
          <strong>${{ safeIncome.thisYear.toLocaleString() }}</strong>
          <span>Year-to-date income estimate</span>
        </article>
        <article class="stat-card">
          <small>Active Services</small>
          <strong>{{ safeIncome.activeServices }}</strong>
          <span>Public listings currently visible</span>
        </article>
      </section>
    </section>

    <div v-if="isCreateServiceModalOpen" class="modal-backdrop" @click="closeCreateServiceModal">
      <section class="modal-card" @click.stop>
        <div class="panel-head">
          <div>
            <p class="eyebrow">Create service</p>
            <h2>Add New Service</h2>
          </div>
          <button type="button" class="secondary-button" @click="closeCreateServiceModal">Close</button>
        </div>

        <form class="service-form" @submit.prevent="submitServiceForm">
          <label class="field">
            <span>Service name</span>
            <input
              :value="props.vendorServiceForm?.title || ''"
              type="text"
              placeholder="Community Workshop"
              @input="props.vendorServiceForm.title = $event.target.value"
            />
          </label>

          <label class="field">
            <span>Types</span>
            <select
              :value="props.vendorServiceForm?.event_type || ''"
              @change="props.vendorServiceForm.event_type = $event.target.value"
            >
              <option v-for="option in eventOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Number of count</span>
            <input
              :value="props.vendorServiceForm?.capacity ?? 1"
              type="number"
              min="1"
              placeholder="50"
              @input="props.vendorServiceForm.capacity = Number($event.target.value)"
            />
          </label>

          <label class="field">
            <span>Price</span>
            <input
              :value="props.vendorServiceForm?.price ?? 0"
              type="number"
              min="0"
              step="0.01"
              placeholder="150"
              @input="props.vendorServiceForm.price = Number($event.target.value)"
            />
          </label>

          <label class="field">
            <span>Location</span>
            <input
              :value="props.vendorServiceForm?.location || ''"
              type="text"
              placeholder="Phnom Penh"
              @input="props.vendorServiceForm.location = $event.target.value"
            />
          </label>

          <div class="field">
            <span>Map location</span>
            <button
              type="button"
              class="secondary-button location-button"
              :disabled="isDetectingVendorLocation"
              @click="detectVendorLocation"
            >
              {{ isDetectingVendorLocation ? 'Detecting location...' : 'Use Current Location' }}
            </button>
          </div>

          <div class="field field-full location-tools">
            <p v-if="vendorLocationNotice" class="location-notice">{{ vendorLocationNotice }}</p>
            <p
              v-if="
                props.vendorServiceForm?.location_latitude !== null &&
                props.vendorServiceForm?.location_longitude !== null
              "
              class="location-coords"
            >
              Lat: {{ Number(props.vendorServiceForm.location_latitude).toFixed(6) }}, Lng:
              {{ Number(props.vendorServiceForm.location_longitude).toFixed(6) }}
            </p>
            <iframe
              v-if="vendorLocationMapEmbedUrl"
              class="location-map-frame"
              :src="vendorLocationMapEmbedUrl"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
            <a
              v-if="vendorLocationMapLinkUrl"
              class="location-map-link"
              :href="vendorLocationMapLinkUrl"
              target="_blank"
              rel="noopener noreferrer"
            >
              Open current location in map
            </a>
          </div>

          <label class="field">
            <span>Start date and time</span>
            <input
              :value="props.vendorServiceForm?.starts_at || ''"
              type="datetime-local"
              @input="props.vendorServiceForm.starts_at = $event.target.value"
            />
          </label>

          <label class="field field-full">
            <span>Picture of services</span>
            <input type="file" accept="image/*" @change="handleVendorServiceImageChange" />
            <small class="field-hint">Choose an image from your device.</small>
          </label>

          <label class="field field-full">
            <span>Or paste image link</span>
            <input
              :value="props.vendorServiceForm?.image_url || ''"
              type="url"
              placeholder="https://example.com/service-photo.jpg"
              @input="handleVendorServiceImageUrlInput"
            />
          </label>

          <div v-if="props.vendorServiceForm?.image_url" class="field field-full">
            <span>Preview</span>
            <div class="image-preview-card">
              <img
                class="image-preview"
                :src="props.vendorServiceForm.image_url"
                alt="Selected service preview"
              />
              <button type="button" class="secondary-button" @click="clearVendorServiceImage">
                Remove image
              </button>
            </div>
          </div>

          <label class="field field-full">
            <span>Service information</span>
            <textarea
              :value="props.vendorServiceForm?.description || ''"
              placeholder="Describe the service, what is included, and what the customer should know."
              @input="props.vendorServiceForm.description = $event.target.value"
            ></textarea>
          </label>

          <div class="form-actions">
            <button type="submit" class="primary-button" :disabled="props.isSubmittingVendorService">
              {{ props.isSubmittingVendorService ? 'Saving...' : 'Create Service' }}
            </button>
          </div>
        </form>

        <p
          v-if="props.vendorServiceNotice"
          class="notice"
          :class="{ 'notice-success': vendorServiceNoticeTone === 'success', 'notice-error': vendorServiceNoticeTone === 'error' }"
        >
          {{ props.vendorServiceNotice }}
        </p>
      </section>
    </div>
  </main>
</template>

<style scoped>
.vendor-dashboard {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 310px minmax(0, 1fr);
  background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
  color: #0f172a;
}

.sidebar {
  position: sticky;
  top: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 22px 18px;
  background: rgba(255, 255, 255, 0.92);
  border-right: 1px solid rgba(15, 23, 42, 0.08);
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand img {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  object-fit: contain;
  background: #fff;
  border: 1px solid rgba(234, 88, 12, 0.15);
}

.brand strong {
  display: block;
  font-size: 28px;
  line-height: 1;
}

.brand span {
  display: block;
  margin-top: 6px;
  color: #ea580c;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.sidebar-nav {
  display: grid;
  gap: 12px;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 14px;
  width: 100%;
  padding: 16px;
  border: 1px solid transparent;
  border-radius: 18px;
  background: transparent;
  color: #475569;
  font-size: 16px;
  font-weight: 700;
  text-align: left;
}

.sidebar-link:hover,
.sidebar-link.active {
  background: #fff4e6;
  border-color: rgba(245, 158, 11, 0.28);
  color: #9a3412;
}

.sidebar-number {
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: rgba(148, 163, 184, 0.14);
  color: #475569;
  font-size: 15px;
  font-weight: 800;
}

.sidebar-link.active .sidebar-number {
  background: #ea580c;
  color: #fff;
}

.sidebar-footer {
  margin-top: auto;
  display: grid;
  gap: 12px;
}

.side-utility {
  display: block;
  width: 100%;
  padding: 14px 16px;
  border: 1px solid rgba(148, 163, 184, 0.24);
  border-radius: 16px;
  background: #fff;
  color: #0f172a;
  font-size: 16px;
  text-align: left;
  text-decoration: none;
}

.side-utility.home {
  background: #fff7ed;
  border-color: rgba(245, 158, 11, 0.3);
  color: #9a3412;
  font-weight: 700;
}

.side-utility.logout {
  color: #dc2626;
  border-color: rgba(248, 113, 113, 0.28);
}

.vendor-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px;
  border-radius: 18px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.vendor-avatar {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: #dbeafe;
  color: #1d4ed8;
  font-weight: 800;
}

.vendor-card small {
  color: #64748b;
}

.main-panel {
  display: grid;
  gap: 22px;
  padding: 28px;
}

.hero,
.panel,
.stat-card {
  border: 1px solid rgba(148, 163, 184, 0.14);
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.06);
}

.hero {
  display: flex;
  justify-content: space-between;
  gap: 24px;
  padding: 30px;
  background: linear-gradient(140deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 247, 237, 0.95) 100%);
}

.eyebrow {
  margin: 0 0 10px;
  color: #ea580c;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.hero h1,
.panel h2 {
  margin: 0;
  line-height: 1;
  letter-spacing: -0.04em;
}

.hero h1 {
  font-size: clamp(40px, 4vw, 64px);
}

.panel h2 {
  font-size: clamp(24px, 2vw, 34px);
}

.hero-copy,
.panel-copy,
.stat-card span,
.signed-user span {
  color: #64748b;
}

.hero-copy,
.panel-copy {
  max-width: 700px;
  font-size: 20px;
  line-height: 1.6;
}

.hero-side {
  display: grid;
  gap: 14px;
  justify-items: end;
  min-width: 220px;
}

.signed-user {
  text-align: right;
}

.signed-user span {
  display: block;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.signed-user strong {
  display: block;
  margin-top: 8px;
  font-size: 18px;
}

.primary-button,
.secondary-button,
.row-actions button {
  border-radius: 16px;
  padding: 14px 18px;
  font-size: 16px;
  font-weight: 700;
}

.primary-button {
  border: 1px solid #c2410c;
  background: linear-gradient(135deg, #f97316 0%, #d97706 100%);
  color: #fff;
}

.secondary-button,
.row-actions button {
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: #fff7ed;
  color: #9a3412;
}

.row-actions .danger {
  background: #fff;
  color: #dc2626;
  border-color: rgba(248, 113, 113, 0.28);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.stat-card {
  padding: 26px;
  display: grid;
  gap: 10px;
}

.stat-card small {
  color: #64748b;
  font-size: 15px;
}

.stat-card strong {
  font-size: clamp(32px, 3vw, 52px);
  line-height: 1;
}

.stat-card.accent {
  background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
  gap: 20px;
}

.services-grid {
  grid-template-columns: minmax(360px, 0.95fr) minmax(0, 1.05fr);
}

.panel {
  padding: 26px;
}

.panel-wide {
  min-height: 360px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.badge {
  padding: 10px 14px;
  border-radius: 999px;
  background: #fff7ed;
  color: #c2410c;
  font-size: 14px;
  font-weight: 700;
}

.chart-placeholder {
  height: 250px;
  position: relative;
  overflow: hidden;
  border-radius: 24px;
  background: linear-gradient(180deg, #fff7ed 0%, #fffbf5 100%);
}

.chart-line {
  position: absolute;
  left: 10%;
  right: 8%;
  height: 12px;
  border-radius: 999px;
  background: linear-gradient(90deg, #ea580c 0%, #f97316 100%);
}

.chart-line-a {
  top: 72%;
}

.chart-line-b {
  top: 62%;
  width: 78%;
}

.chart-line-c {
  top: 52%;
  width: 62%;
}

.action-row,
.form-actions,
.row-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.service-form {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
}

.field span {
  color: #475569;
  font-size: 14px;
  font-weight: 700;
}

.field-hint {
  color: #64748b;
  font-size: 13px;
}

.field input,
.field select,
.field textarea {
  width: 100%;
  padding: 13px 14px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  border-radius: 16px;
  background: #fff;
  color: #0f172a;
  font-size: 15px;
}

.field textarea {
  min-height: 120px;
  resize: vertical;
}

.field-full {
  grid-column: 1 / -1;
}

.image-preview-card {
  display: grid;
  gap: 12px;
  justify-items: start;
  padding: 14px;
  border: 1px solid rgba(148, 163, 184, 0.16);
  border-radius: 18px;
  background: #f8fafc;
}

.image-preview {
  width: min(280px, 100%);
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border-radius: 16px;
  background: #e2e8f0;
}

.location-button {
  align-self: end;
  width: 100%;
}

.location-tools {
  gap: 10px;
}

.location-notice,
.location-coords {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.location-map-frame {
  width: 100%;
  min-height: 240px;
  border: 0;
  border-radius: 18px;
  background: #e2e8f0;
}

.location-map-link {
  display: inline-flex;
  width: fit-content;
  align-items: center;
  color: #9a3412;
  font-weight: 700;
  text-decoration: none;
}

.location-map-link:hover {
  text-decoration: underline;
}

.service-list {
  display: grid;
  gap: 14px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.service-item {
  display: grid;
  grid-template-columns: 120px minmax(0, 1fr) auto;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 20px;
  background: #f8fafc;
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.service-image {
  width: 120px;
  height: 96px;
  border-radius: 18px;
  object-fit: cover;
  background: #e2e8f0;
}

.service-header {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.service-copy small,
.notice {
  color: #64748b;
}

.notice-success {
  color: #15803d;
}

.notice-error {
  color: #b91c1c;
}

.service-copy p {
  margin: 8px 0 0;
  color: #334155;
}

.service-state {
  padding: 8px 12px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #475569;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.service-state.live {
  background: #dcfce7;
  color: #15803d;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table th,
.table td {
  padding: 14px 12px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.16);
  text-align: left;
}

.table th {
  color: #64748b;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.status-chip {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
}

.status-chip.wait {
  background: #ffedd5;
  color: #c2410c;
}

.status-chip.ok {
  background: #dcfce7;
  color: #15803d;
}

.status-chip.bad {
  background: #fee2e2;
  color: #dc2626;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  padding: 24px;
  background: rgba(15, 23, 42, 0.38);
}

.modal-card {
  width: min(880px, 100%);
  max-height: 90vh;
  overflow: auto;
  padding: 26px;
  border-radius: 28px;
  background: #fff;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.22);
}

@media (max-width: 1180px) {
  .vendor-dashboard {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: static;
    min-height: auto;
  }

  .stats-grid,
  .content-grid,
  .services-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .main-panel {
    padding: 18px;
  }

  .hero {
    flex-direction: column;
  }

  .hero-side {
    width: 100%;
    justify-items: start;
  }

  .signed-user {
    text-align: left;
  }

  .service-form,
  .service-item {
    grid-template-columns: 1fr;
  }
}
</style>
