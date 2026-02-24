<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const API_BASE = (import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api').replace(/\/$/, '')

const eventTypeOptions = [
  { value: 'all', label: 'All Event Types' },
  { value: 'wedding', label: 'Wedding' },
  { value: 'monk_ceremony', label: 'Monk Ceremony' },
  { value: 'house_blessing', label: 'House Blessing' },
  { value: 'company_party', label: 'Company Party' },
  { value: 'birthday', label: 'Birthday' },
  { value: 'school_event', label: 'School Event' },
  { value: 'engagement', label: 'Engagement' },
  { value: 'anniversary', label: 'Anniversary' },
  { value: 'baby_shower', label: 'Baby Shower' },
  { value: 'graduation', label: 'Graduation' },
  { value: 'festival', label: 'Festival' },
  { value: 'other', label: 'Other' },
]

const eventTypeMap = Object.fromEntries(eventTypeOptions.map((item) => [item.value, item.label]))

const currentPage = ref('dashboard')
const activeVendorTab = ref('about')
const bookingFilter = ref('Upcoming')

const globalSearch = ref('')
const conversationSearch = ref('')
const composerText = ref('')

const customerName = ref(localStorage.getItem('achar_customer_name') || '')
const customerEmail = ref(localStorage.getItem('achar_customer_email') || '')
const userPhone = ref(localStorage.getItem('achar_user_phone') || '')
const userLocation = ref(localStorage.getItem('achar_user_location') || '')
const userLatitude = ref(localStorage.getItem('achar_user_lat') ? Number(localStorage.getItem('achar_user_lat')) : null)
const userLongitude = ref(localStorage.getItem('achar_user_lng') ? Number(localStorage.getItem('achar_user_lng')) : null)
const isDetectingLocation = ref(false)
const userProfileNotice = ref('')

const userProfileDraft = ref({
  name: customerName.value,
  email: customerEmail.value,
  phone: userPhone.value,
  location: userLocation.value,
})

const selectedEventType = ref('all')
const bookingEventTypeFilter = ref('all')

const vendorEvents = ref([])
const bookings = ref([])
const selectedQuantities = ref({})
const availabilityByEvent = ref({})
const checkingAvailabilityEventId = ref(null)

const isLoadingEvents = ref(false)
const isLoadingBookings = ref(false)
const bookingSubmittingEventId = ref(null)
const notice = ref('')

const vendorProfile = {
  name: 'Luxe Bloom Florals',
  subtitle: 'Premium Wedding & Event Floral Design',
  rating: '4.9 (142 reviews)',
}

const stats = [
  { label: 'Years Exp.', value: '15+' },
  { label: 'Events Done', value: '1.2k' },
  { label: 'Eco-Friendly', value: '100%' },
  { label: 'Staff', value: '12' },
]

const reviews = [
  {
    name: 'Emma Wilson',
    text: 'Absolutely stunning floral designs for our wedding. Everyone kept asking who did them.',
  },
  {
    name: 'James Kovac',
    text: 'Professional and creative. They made our corporate gala look world-class.',
  },
]

const conversations = ref([
  {
    id: 'luxe-bloom',
    name: 'Luxe Bloom Florals',
    preview: 'The peonies for the centerpiece will be ready by Thursday.',
    time: 'Just now',
    online: true,
    image:
      'https://images.unsplash.com/photo-1477511801984-4ad318ed9846?auto=format&fit=crop&w=180&q=80',
    messages: [
      {
        id: 1,
        from: 'them',
        text: 'Hi Sarah! I have received the shipment of Dutch peonies for your wedding. They are stunning.',
        time: '10:24 AM',
      },
      {
        id: 2,
        from: 'me',
        text: 'That is wonderful news! Do you have a photo of the color? I want to make sure it matches.',
        time: '10:26 AM',
      },
      {
        id: 3,
        from: 'them',
        text: 'Of course! Here is a quick snap from the workshop now.',
        image:
          'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1400&q=80',
        time: '10:30 AM',
      },
      {
        id: 4,
        from: 'me',
        text: 'Perfect! They look exactly like the moodboard. The peonies for the centerpiece will be ready for final mockup Thursday then?',
        time: 'Just now',
      },
    ],
  },
  {
    id: 'grand-estates',
    name: 'Grand Estates Venue',
    preview: 'I have sent the updated floor plan for the gala.',
    time: '2h ago',
    online: false,
    image:
      'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=180&q=80',
    messages: [
      { id: 1, from: 'them', text: 'I have sent the updated floor plan for the gala.', time: '2h ago' },
    ],
  },
])

const selectedConversationId = ref(conversations.value[0].id)

const navSearchPlaceholder = computed(() =>
  currentPage.value === 'bookings' ? 'Search bookings...' : 'Search services...',
)
const userAvatarInitial = computed(() => (customerName.value.trim().charAt(0) || 'P').toUpperCase())
const userLocationMapUrl = computed(() => {
  if (userLatitude.value === null || userLongitude.value === null) return ''
  const lat = userLatitude.value.toFixed(6)
  const lng = userLongitude.value.toFixed(6)
  return `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=13&size=700x320&markers=${lat},${lng},orange-pushpin`
})

const filteredPackages = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  return vendorEvents.value.filter((item) => {
    const matchesType = selectedEventType.value === 'all' || item.eventType === selectedEventType.value
    const matchesSearch = !q || item.title.toLowerCase().includes(q) || item.description.toLowerCase().includes(q)
    return matchesType && matchesSearch
  })
})

const filteredBookings = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  return bookings.value.filter((item) => {
    const matchesFilter = item.type === bookingFilter.value
    const matchesType = bookingEventTypeFilter.value === 'all' || item.eventType === bookingEventTypeFilter.value
    const matchesSearch =
      !q || item.vendor.toLowerCase().includes(q) || item.service.toLowerCase().includes(q)
    return matchesFilter && matchesSearch && matchesType
  })
})

const filteredConversations = computed(() => {
  const q = conversationSearch.value.trim().toLowerCase()
  if (!q) return conversations.value
  return conversations.value.filter(
    (chat) => chat.name.toLowerCase().includes(q) || chat.preview.toLowerCase().includes(q),
  )
})

const activeConversation = computed(() => {
  const active = conversations.value.find((item) => item.id === selectedConversationId.value)
  return active || conversations.value[0]
})

const dashboardStats = computed(() => {
  const totalBookings = bookings.value.length
  const upcomingBookings = bookings.value.filter((item) => item.type === 'Upcoming').length
  const completedBookings = bookings.value.filter((item) => item.type === 'Completed').length
  const unreadMessages = conversations.value.filter((item) => item.time === 'Just now').length
  return { totalBookings, upcomingBookings, completedBookings, unreadMessages }
})

const recentBookings = computed(() => bookings.value.slice(0, 3))
const recentConversations = computed(() => conversations.value.slice(0, 3))

function formatDateTime(dateString) {
  if (!dateString) return 'Date TBD'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return 'Date TBD'
  return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
}

function deriveBookingType(status, startsAt) {
  if (status === 'cancelled') return 'Drafts'
  if (!startsAt) return status === 'pending' ? 'Upcoming' : 'Completed'
  const eventDate = new Date(startsAt)
  const now = new Date()
  if (!Number.isNaN(eventDate.getTime()) && eventDate < now) return 'Completed'
  return 'Upcoming'
}

function mapBooking(apiBooking) {
  const event = apiBooking.event || {}
  const status = (apiBooking.status || 'pending').toLowerCase()

  const statusText =
    status === 'confirmed' ? 'Confirmed' : status === 'cancelled' ? 'Cancelled' : 'Pending Approval'

  const statusClass =
    status === 'confirmed' ? 'confirmed' : status === 'cancelled' ? 'done' : 'pending'

  const type = deriveBookingType(status, event.starts_at)

  return {
    id: apiBooking.id,
    vendor: vendorProfile.name,
    service: event.title || 'Service Booking',
    date: formatDateTime(event.starts_at),
    metaLabel: 'Event Type',
    metaValue: eventTypeMap[event.event_type] || 'Other',
    placeLabel: 'Total',
    placeValue: `$${Number(apiBooking.total_amount || 0).toLocaleString()}`,
    status: statusText,
    statusClass,
    type,
    eventType: event.event_type || 'other',
    eventId: event.id,
    image:
      'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=760&q=80',
    primaryBtn: status === 'pending' ? 'Message Vendor' : 'View Details',
    secondaryBtn: 'Reschedule',
    note: `${apiBooking.customer_name} | ${apiBooking.customer_email}`,
  }
}

function mapEvent(apiEvent) {
  return {
    id: apiEvent.id,
    title: apiEvent.title,
    eventType: apiEvent.event_type || 'other',
    eventTypeLabel: eventTypeMap[apiEvent.event_type] || 'Other',
    description: apiEvent.description || 'Professional service package for your event.',
    location: apiEvent.location,
    date: formatDateTime(apiEvent.starts_at),
    priceLabel: `From $${Number(apiEvent.price || 0).toLocaleString()}`,
    image:
      'https://images.unsplash.com/photo-1477511801984-4ad318ed9846?auto=format&fit=crop&w=760&q=80',
  }
}

function getAvailability(item) {
  return availabilityByEvent.value[item.id] || null
}

function getAvailabilityTone(item) {
  const availability = getAvailability(item)
  if (!availability) return 'pending'
  if (!availability.service_available) return 'unavailable'
  if (!availability.vendor_available) return 'busy'
  return 'available'
}

function getAvailabilityLabel(item) {
  const availability = getAvailability(item)
  if (!availability) return 'Not checked'
  if (!availability.service_available) return 'Service Unavailable'
  if (!availability.vendor_available) return 'Vendor Busy'
  return 'Available'
}

async function apiGet(path, query = {}) {
  const url = new URL(`${API_BASE}/${path.replace(/^\//, '')}`)
  Object.entries(query).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      url.searchParams.set(key, value)
    }
  })
  const response = await fetch(url.toString())
  if (!response.ok) {
    throw new Error(`Request failed: ${response.status}`)
  }
  return response.json()
}

async function apiPost(path, payload) {
  const response = await fetch(`${API_BASE}/${path.replace(/^\//, '')}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  })

  if (!response.ok) {
    const errorBody = await response.json().catch(() => ({}))
    throw new Error(errorBody.message || 'Request failed')
  }

  return response.json()
}

async function loadEvents() {
  isLoadingEvents.value = true
  try {
    const result = await apiGet('events')
    const rows = Array.isArray(result.data) ? result.data : []
    vendorEvents.value = rows.map(mapEvent)
    availabilityByEvent.value = {}

    const initialQuantities = {}
    vendorEvents.value.forEach((item) => {
      initialQuantities[item.id] = selectedQuantities.value[item.id] || 1
    })
    selectedQuantities.value = initialQuantities
  } catch (error) {
    notice.value = 'Could not load events from backend API. Please start backend server.'
  } finally {
    isLoadingEvents.value = false
  }
}

async function checkEventAvailability(item) {
  checkingAvailabilityEventId.value = item.id
  try {
    const result = await apiGet(`events/${item.id}/availability`)
    availabilityByEvent.value = {
      ...availabilityByEvent.value,
      [item.id]: result,
    }
    notice.value = result.message || 'Availability checked.'
    return result
  } catch (error) {
    notice.value = 'Could not check availability right now.'
    return null
  } finally {
    checkingAvailabilityEventId.value = null
  }
}

async function loadBookings() {
  if (!customerEmail.value.trim()) {
    bookings.value = []
    return
  }

  isLoadingBookings.value = true
  try {
    const result = await apiGet('bookings', { customer_email: customerEmail.value.trim() })
    const rows = Array.isArray(result.data) ? result.data : []
    bookings.value = rows.map(mapBooking)
  } catch (error) {
    notice.value = 'Could not load bookings. Check backend API and database migrations.'
  } finally {
    isLoadingBookings.value = false
  }
}

function goToDashboard() {
  currentPage.value = 'dashboard'
}

function goToVendor(tab = 'about') {
  currentPage.value = 'vendor'
  activeVendorTab.value = tab
}

function goToProfile() {
  currentPage.value = 'profile'
  userProfileNotice.value = ''
  userProfileDraft.value = {
    name: customerName.value,
    email: customerEmail.value,
    phone: userPhone.value,
    location: userLocation.value,
  }
}

function saveUserProfile() {
  customerName.value = userProfileDraft.value.name.trim()
  customerEmail.value = userProfileDraft.value.email.trim()
  userPhone.value = userProfileDraft.value.phone.trim()
  userLocation.value = userProfileDraft.value.location.trim()
  userProfileNotice.value = 'User profile updated.'
  notice.value = 'Your profile changes were saved.'
}

function resetUserProfile() {
  customerName.value = ''
  customerEmail.value = ''
  userPhone.value = ''
  userLocation.value = ''
  userLatitude.value = null
  userLongitude.value = null
  userProfileDraft.value = { name: '', email: '', phone: '', location: '' }
  userProfileNotice.value = 'User profile reset.'
  notice.value = 'Your profile was reset.'
}

function detectCurrentLocation() {
  if (!navigator.geolocation) {
    userProfileNotice.value = 'Geolocation is not supported by this browser.'
    return
  }

  isDetectingLocation.value = true
  navigator.geolocation.getCurrentPosition(
    (position) => {
      const lat = Number(position.coords.latitude.toFixed(6))
      const lng = Number(position.coords.longitude.toFixed(6))
      userLatitude.value = lat
      userLongitude.value = lng
      userProfileDraft.value.location = `${lat}, ${lng}`
      userProfileNotice.value = 'Current location captured.'
      notice.value = 'Real location updated.'
      isDetectingLocation.value = false
    },
    () => {
      userProfileNotice.value = 'Could not access your current location.'
      isDetectingLocation.value = false
    },
    {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 300000,
    },
  )
}

function goToBookings() {
  currentPage.value = 'bookings'
}

function goToMessages(targetVendor) {
  currentPage.value = 'messages'
  if (!targetVendor) return
  const match = conversations.value.find((chat) =>
    chat.name.toLowerCase().includes(targetVendor.toLowerCase()),
  )
  if (match) selectedConversationId.value = match.id
}

function selectConversation(id) {
  selectedConversationId.value = id
}

function sendMessage() {
  const text = composerText.value.trim()
  if (!text) return

  const target = conversations.value.find((item) => item.id === selectedConversationId.value)
  if (!target) return

  target.messages.push({
    id: Date.now(),
    from: 'me',
    text,
    time: 'Just now',
  })
  target.preview = text
  target.time = 'Just now'
  composerText.value = ''
}

async function bookPackage(item) {
  const name = customerName.value.trim()
  const email = customerEmail.value.trim()

  if (!name || !email) {
    notice.value = 'Please enter your name and email before booking.'
    return
  }

  const quantity = Number(selectedQuantities.value[item.id] || 1)
  if (!Number.isFinite(quantity) || quantity < 1) {
    notice.value = 'Please select a valid quantity.'
    return
  }

  const availability = getAvailability(item) || (await checkEventAvailability(item))
  if (!availability || !availability.service_available || !availability.vendor_available) {
    notice.value = availability?.message || 'This service is not available at the moment.'
    return
  }

  bookingSubmittingEventId.value = item.id
  try {
    await apiPost('bookings', {
      event_id: item.id,
      quantity,
      customer_name: name,
      customer_email: email,
    })

    notice.value = `Booking created for ${item.title}.`
    await loadBookings()
    goToBookings()
    bookingFilter.value = 'Upcoming'
  } catch (error) {
    notice.value = error.message || 'Booking failed.'
  } finally {
    bookingSubmittingEventId.value = null
  }
}

function bookingPrimaryAction(item) {
  if (item.primaryBtn === 'Message Vendor') {
    goToMessages(item.vendor)
    return
  }
  if (item.primaryBtn === 'View Details') {
    goToVendor()
  }
}

function bookingSecondaryAction(item) {
  item.note = 'Reschedule request sent. Waiting for confirmation.'
}

watch([customerName, customerEmail, userPhone, userLocation, userLatitude, userLongitude], () => {
  localStorage.setItem('achar_customer_name', customerName.value)
  localStorage.setItem('achar_customer_email', customerEmail.value)
  localStorage.setItem('achar_user_phone', userPhone.value)
  localStorage.setItem('achar_user_location', userLocation.value)
  localStorage.setItem('achar_user_lat', userLatitude.value === null ? '' : String(userLatitude.value))
  localStorage.setItem('achar_user_lng', userLongitude.value === null ? '' : String(userLongitude.value))
})

watch(customerEmail, () => {
  if (currentPage.value === 'bookings' || currentPage.value === 'dashboard') {
    loadBookings()
  }
})

onMounted(async () => {
  await loadEvents()
  await loadBookings()
})
</script>

<template>
  <div class="page">
    <header class="topbar">
      <div class="shell topbar-inner">
        <div class="brand">
          <span class="brand-icon">E</span>
          <span class="brand-text">Evently</span>
        </div>

        <nav class="top-links">
          <a href="#" :class="{ active: currentPage === 'dashboard' }" @click.prevent="goToDashboard">Dashboard</a>
          <a href="#" :class="{ active: currentPage === 'vendor' }" @click.prevent="goToVendor">Find Vendors</a>
          <a href="#" :class="{ active: currentPage === 'bookings' }" @click.prevent="goToBookings">My Bookings</a>
          <a href="#" :class="{ active: currentPage === 'messages' }" @click.prevent="goToMessages()">Messages</a>
        </nav>

        <div class="top-actions">
          <input
            v-if="currentPage !== 'messages'"
            v-model="globalSearch"
            type="search"
            :placeholder="navSearchPlaceholder"
          />
          <button type="button" class="avatar avatar-btn" @click="goToProfile">{{ userAvatarInitial }}</button>
        </div>
      </div>
    </header>

    <main v-if="currentPage === 'dashboard'" class="shell dashboard-page">
      <div class="breadcrumbs">Home > Dashboard</div>

      <section class="dashboard-head">
        <div class="dashboard-head-main">
          <span class="dash-chip">Planner Workspace</span>
          <h1>Welcome back, {{ customerName || 'Planner' }}</h1>
          <p>Track your planning progress, follow updates, and move your events forward in one place.</p>
          <div class="dashboard-inline-stats">
            <span><strong>{{ dashboardStats.upcomingBookings }}</strong> upcoming</span>
            <span><strong>{{ dashboardStats.completedBookings }}</strong> completed</span>
            <span><strong>{{ dashboardStats.unreadMessages }}</strong> active chats</span>
          </div>
        </div>
        <div class="dashboard-actions">
          <button type="button" class="btn-light" @click="goToVendor">Find Vendors</button>
          <button type="button" class="btn-accent" @click="goToBookings">View Bookings</button>
        </div>
      </section>

      <p v-if="notice" class="notice">{{ notice }}</p>

      <section class="kpi-grid">
        <article class="card kpi-card">
          <p>Total Bookings</p>
          <strong>{{ dashboardStats.totalBookings }}</strong>
          <small>All planned events</small>
        </article>
        <article class="card kpi-card">
          <p>Upcoming</p>
          <strong>{{ dashboardStats.upcomingBookings }}</strong>
          <small>Next milestone focus</small>
        </article>
        <article class="card kpi-card">
          <p>Completed</p>
          <strong>{{ dashboardStats.completedBookings }}</strong>
          <small>Successfully delivered</small>
        </article>
        <article class="card kpi-card">
          <p>New Messages</p>
          <strong>{{ dashboardStats.unreadMessages }}</strong>
          <small>Unread this session</small>
        </article>
      </section>

      <section class="dashboard-grid">
        <article class="card dashboard-card">
          <div class="dashboard-card-head">
            <h2>Recent Bookings</h2>
            <a href="#" @click.prevent="goToBookings">See all</a>
          </div>
          <div class="dashboard-list">
            <div v-for="item in recentBookings" :key="item.id" class="dashboard-item">
              <img :src="item.image" :alt="item.vendor" />
              <div>
                <strong>{{ item.service }}</strong>
                <p>{{ item.metaValue }}</p>
              </div>
              <span class="booking-status" :class="item.statusClass">{{ item.status }}</span>
            </div>
          </div>
        </article>

        <article class="card dashboard-card">
          <div class="dashboard-card-head">
            <h2>Recent Messages</h2>
            <a href="#" @click.prevent="goToMessages()">Open inbox</a>
          </div>
          <div class="dashboard-list">
            <div
              v-for="chat in recentConversations"
              :key="chat.id"
              class="dashboard-item message"
              @click="goToMessages(chat.name)"
            >
              <img :src="chat.image" :alt="chat.name" />
              <div>
                <strong>{{ chat.name }}</strong>
                <p>{{ chat.preview }}</p>
              </div>
              <span class="dash-time">{{ chat.time }}</span>
            </div>
          </div>
        </article>

        <article class="card dashboard-card wide">
          <div class="dashboard-card-head">
            <h2>Quick Actions</h2>
          </div>
          <div class="quick-actions">
            <button type="button" @click="goToVendor">
              <strong>Browse Vendors</strong>
              <span>Explore categories and compare providers</span>
            </button>
            <button type="button" @click="goToVendor('services')">
              <strong>Book Event Package</strong>
              <span>Reserve a service in a few steps</span>
            </button>
            <button type="button" @click="bookingFilter = 'Upcoming'; goToBookings()">
              <strong>Manage Upcoming Events</strong>
              <span>Review confirmations and next actions</span>
            </button>
            <button type="button" @click="goToMessages()">
              <strong>Contact Vendors</strong>
              <span>Follow up and keep conversations moving</span>
            </button>
          </div>
        </article>
      </section>
    </main>

    <main v-else-if="currentPage === 'vendor'" class="shell content">
      <div class="breadcrumbs">Home > Vendors > {{ vendorProfile.name }}</div>

      <section class="hero">
        <img
          class="hero-bg"
          src="https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1600&q=80"
          alt="Luxe Bloom Florals banner"
        />
        <div class="hero-overlay"></div>
        <div class="hero-main">
          <div class="vendor-logo">LUXE<br />BLOOM</div>
          <div class="vendor-details">
            <h1>{{ vendorProfile.name }}</h1>
            <p>{{ vendorProfile.subtitle }}</p>
            <div class="rating">{{ vendorProfile.rating }}</div>
          </div>
        </div>
        <div class="hero-buttons">
          <button type="button" class="btn-light" @click="goToMessages(vendorProfile.name)">Message</button>
          <button type="button" class="btn-accent">Share</button>
        </div>
      </section>

      <nav class="section-tabs">
        <a href="#" :class="{ active: activeVendorTab === 'about' }" @click.prevent="activeVendorTab = 'about'">About</a>
        <a href="#" :class="{ active: activeVendorTab === 'services' }" @click.prevent="activeVendorTab = 'services'">Packages & Services</a>
        <a href="#" :class="{ active: activeVendorTab === 'reviews' }" @click.prevent="activeVendorTab = 'reviews'">Reviews</a>
      </nav>

      <section class="layout">
        <div class="left">
          <article v-if="activeVendorTab === 'about'" class="card about">
            <h2>About {{ vendorProfile.name }}</h2>
            <p>
              Luxe Bloom Florals is an award-winning boutique floral design studio specializing in
              creating breathtaking, immersive botanical experiences for weddings, corporate events,
              and high-profile cultural ceremonies.
            </p>
            <p>
              We support multiple event types including wedding, monk ceremony, house blessing,
              company party, birthday, school event, and engagement.
            </p>

            <div class="stats">
              <div v-for="stat in stats" :key="stat.label" class="stat-box">
                <p>{{ stat.label }}</p>
                <strong>{{ stat.value }}</strong>
              </div>
            </div>
          </article>

          <article v-else-if="activeVendorTab === 'services'" class="card services">
            <h2>Packages & Services</h2>

            <div class="booking-inline-form">
              <input v-model="customerName" type="text" placeholder="Your full name" />
              <input v-model="customerEmail" type="email" placeholder="Your email" />
              <select v-model="selectedEventType">
                <option v-for="option in eventTypeOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <button type="button" class="btn-light" @click="loadBookings">Load My Bookings</button>
            </div>

            <div v-if="isLoadingEvents" class="empty-state">Loading packages from API...</div>
            <div v-else-if="filteredPackages.length === 0" class="empty-state">
              No packages match your search and event type filter.
            </div>

            <div v-for="item in filteredPackages" :key="item.id" class="service-card">
              <img :src="item.image" :alt="item.title" />
              <div class="service-body">
                <div class="service-head">
                  <h3>{{ item.title }}</h3>
                  <span class="tag">{{ item.eventTypeLabel }}</span>
                </div>
                <p>{{ item.description }}</p>
                <ul>
                  <li>{{ item.location }}</li>
                  <li>{{ item.date }}</li>
                </ul>
                <div class="service-footer">
                  <strong>{{ item.priceLabel }}</strong>
                  <div class="service-book-actions">
                    <input
                      v-model.number="selectedQuantities[item.id]"
                      type="number"
                      min="1"
                      max="20"
                    />
                    <button
                      type="button"
                      :disabled="bookingSubmittingEventId === item.id"
                      @click="bookPackage(item)"
                    >
                      {{ bookingSubmittingEventId === item.id ? 'Booking...' : 'Book Now' }}
                    </button>
                    <button
                      type="button"
                      class="ghost"
                      :disabled="checkingAvailabilityEventId === item.id"
                      @click="checkEventAvailability(item)"
                    >
                      {{ checkingAvailabilityEventId === item.id ? 'Checking...' : 'Check Availability' }}
                    </button>
                  </div>
                </div>
                <div class="availability-row">
                  <span class="availability-pill" :class="getAvailabilityTone(item)">
                    {{ getAvailabilityLabel(item) }}
                  </span>
                  <p v-if="getAvailability(item)">{{ getAvailability(item).message }}</p>
                </div>
              </div>
            </div>
          </article>

          <article v-else-if="activeVendorTab === 'reviews'" class="card services">
            <h2>Recent Reviews</h2>
            <div v-for="review in reviews" :key="review.name" class="mini-review">
              <strong>{{ review.name }}</strong>
              <p>{{ review.text }}</p>
            </div>
          </article>

        </div>

        <aside class="right">
          <article class="card sidebar-card">
            <h3>Contact Details</h3>
            <p><strong>Phone</strong><br />+1 (555) 234-5678</p>
            <p><strong>Email</strong><br />hello@luxebloom.com</p>
            <p><strong>Website</strong><br />www.luxebloom.com</p>
          </article>

          <article class="card sidebar-card">
            <h3>Location</h3>
            <p>88 Flower District, New York, NY 10001</p>
            <img
              class="map"
              src="https://staticmap.openstreetmap.de/staticmap.php?center=40.7419,-73.9893&zoom=12&size=700x380&markers=40.7419,-73.9893,orange-pushpin"
              alt="Location map"
            />
          </article>

          <article class="card sidebar-card">
            <div class="review-header">
              <h3>Recent Reviews</h3>
              <a href="#" @click.prevent="activeVendorTab = 'reviews'">See all</a>
            </div>
            <div v-for="review in reviews" :key="review.name" class="mini-review">
              <strong>{{ review.name }}</strong>
              <p>{{ review.text }}</p>
            </div>
          </article>
        </aside>
      </section>
    </main>

    <main v-else-if="currentPage === 'bookings'" class="shell bookings-page">
      <div class="breadcrumbs">Home > My Bookings</div>

      <section class="bookings-head">
        <div>
          <h1>My Bookings</h1>
          <p>Manage your upcoming and past event services.</p>
        </div>
        <div class="booking-filter-wrap">
          <div class="booking-filter">
            <button
              v-for="tab in ['Upcoming', 'Completed', 'Drafts']"
              :key="tab"
              type="button"
              :class="{ active: bookingFilter === tab }"
              @click="bookingFilter = tab"
            >
              {{ tab }}
            </button>
          </div>
          <select v-model="bookingEventTypeFilter" class="event-type-select">
            <option v-for="option in eventTypeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>
      </section>

      <p v-if="notice" class="notice">{{ notice }}</p>

      <section class="bookings-layout">
        <aside class="bookings-sidebar">
          <a href="#" @click.prevent="goToDashboard">Dashboard</a>
          <a href="#" @click.prevent="goToVendor">Find Vendors</a>
          <a class="active" href="#">My Bookings</a>
          <a href="#" @click.prevent="goToMessages()">Messages</a>
          <a href="#">Saved Vendors</a>
          <hr />
          <a href="#" @click.prevent="goToProfile">Settings</a>
          <a class="danger" href="#">Sign Out</a>
        </aside>

        <div class="booking-list">
          <div v-if="isLoadingBookings" class="card empty-state">Loading bookings from API...</div>
          <div v-else-if="filteredBookings.length === 0" class="card empty-state">
            No bookings found for this filter. Use your email on vendor page and click "Load My Bookings".
          </div>

          <article v-for="item in filteredBookings" :key="item.id" class="booking-card card">
            <img :src="item.image" :alt="item.vendor" />
            <div class="booking-body">
              <div class="booking-row">
                <div>
                  <h3>{{ item.vendor }}</h3>
                  <p>{{ item.service }}</p>
                </div>
                <span class="booking-status" :class="item.statusClass">{{ item.status }}</span>
              </div>

              <div class="booking-meta">
                <div>
                  <small>Date</small>
                  <strong>{{ item.date }}</strong>
                </div>
                <div>
                  <small>{{ item.metaLabel }}</small>
                  <strong>{{ item.metaValue }}</strong>
                </div>
                <div v-if="item.placeLabel">
                  <small>{{ item.placeLabel }}</small>
                  <strong>{{ item.placeValue }}</strong>
                </div>
              </div>

              <div class="booking-actions">
                <p>{{ item.note }}</p>
                <div>
                  <button type="button" class="ghost" @click="bookingSecondaryAction(item)">{{ item.secondaryBtn }}</button>
                  <button
                    type="button"
                    :class="item.statusClass === 'done' ? 'accent-soft' : 'accent'"
                    @click="bookingPrimaryAction(item)"
                  >
                    {{ item.primaryBtn }}
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>

    <main v-else-if="currentPage === 'profile'" class="shell profile-page">
      <div class="breadcrumbs">Dashboard > My Profile</div>

      <section class="card services profile-form">
        <h2>Edit My Profile</h2>
        <p v-if="userProfileNotice" class="notice">{{ userProfileNotice }}</p>

        <div class="profile-grid">
          <label>
            Full Name
            <input v-model="userProfileDraft.name" type="text" placeholder="Your full name" />
          </label>
          <label>
            Email
            <input v-model="userProfileDraft.email" type="email" placeholder="you@example.com" />
          </label>
          <label>
            Phone
            <input v-model="userProfileDraft.phone" type="text" placeholder="+1 (555) 123-4567" />
          </label>
          <label>
            Location
            <input v-model="userProfileDraft.location" type="text" placeholder="City, State" />
          </label>
        </div>

        <div class="profile-location-tools">
          <button type="button" class="btn-light" :disabled="isDetectingLocation" @click="detectCurrentLocation">
            {{ isDetectingLocation ? 'Detecting location...' : 'Use Current Location' }}
          </button>
          <p v-if="userLatitude !== null && userLongitude !== null" class="location-coords">
            Lat: {{ userLatitude.toFixed(6) }}, Lng: {{ userLongitude.toFixed(6) }}
          </p>
          <img v-if="userLocationMapUrl" :src="userLocationMapUrl" alt="Your saved location map" class="map" />
        </div>

        <div class="profile-actions">
          <button type="button" class="btn-light" @click="resetUserProfile">Reset</button>
          <button type="button" class="btn-accent" @click="saveUserProfile">Save Profile</button>
        </div>
      </section>
    </main>

    <main v-else class="messages-page">
      <section class="messages-layout">
        <aside class="messages-sidebar">
          <h2>Messages</h2>
          <input v-model="conversationSearch" type="search" placeholder="Search conversations..." />

          <div class="conversation-list">
            <article
              v-for="chat in filteredConversations"
              :key="chat.id"
              class="conversation-item"
              :class="{ active: chat.id === selectedConversationId }"
              @click="selectConversation(chat.id)"
            >
              <img :src="chat.image" :alt="chat.name" />
              <div class="conversation-text">
                <div>
                  <h3>{{ chat.name }}</h3>
                  <span>{{ chat.time }}</span>
                </div>
                <p>{{ chat.preview }}</p>
              </div>
            </article>
          </div>
        </aside>

        <section class="chat-panel">
          <header class="chat-header">
            <div class="chat-person">
              <img :src="activeConversation.image" :alt="activeConversation.name" />
              <div>
                <h3>{{ activeConversation.name }}</h3>
                <p>{{ activeConversation.online ? 'Online' : 'Offline' }}</p>
              </div>
            </div>
          </header>

          <div class="chat-stream">
            <span class="chat-day">Today</span>

            <div
              v-for="msg in activeConversation.messages"
              :key="msg.id"
              class="bubble-row"
              :class="msg.from === 'me' ? 'right' : 'left'"
            >
              <div class="bubble" :class="msg.from === 'me' ? 'accent' : ''">
                <span>{{ msg.text }}</span>
                <img v-if="msg.image" :src="msg.image" alt="Shared attachment" />
              </div>
            </div>
          </div>

          <footer class="chat-composer">
            <button type="button" class="composer-icon">+</button>
            <input v-model="composerText" type="text" placeholder="Type your message..." @keyup.enter="sendMessage" />
            <button type="button" class="composer-send" :disabled="!composerText.trim()" @click="sendMessage">Send</button>
          </footer>
        </section>
      </section>
    </main>

    <footer v-if="currentPage !== 'messages'" class="footer">
      <div class="shell footer-grid">
        <div>
          <div class="brand">
            <span class="brand-icon">E</span>
            <span class="brand-text">Evently</span>
          </div>
          <p>Making event planning effortless and elegant for everyone, everywhere.</p>
        </div>
        <div>
          <h4>For Planners</h4>
          <a href="#" @click.prevent="goToVendor">Find Vendors</a>
          <a href="#" @click.prevent="goToDashboard">Planning Dashboard</a>
          <a href="#" @click.prevent="goToBookings">My Bookings</a>
        </div>
        <div>
          <h4>For Vendors</h4>
          <a href="#">List your Service</a>
          <a href="#">Vendor Portal</a>
          <a href="#">Success Stories</a>
        </div>
        <div>
          <h4>Support</h4>
          <a href="#">Help Center</a>
          <a href="#">Terms of Service</a>
          <a href="#">Contact Us</a>
        </div>
      </div>
      <div class="shell footer-bottom">
        <span>© 2024 Evently Inc. All rights reserved.</span>
        <div>
          <a href="#">Privacy Policy</a>
          <a href="#">Cookie Policy</a>
          <a href="#">Sitemap</a>
        </div>
      </div>
    </footer>
  </div>
</template>
