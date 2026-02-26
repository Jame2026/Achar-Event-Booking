<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import PackageCard from './components/customization/PackageCard.vue'
import ServiceCard from './components/customization/ServiceCard.vue'

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

const serviceFeeRate = 0.1
const customizationSearch = ref('')
const customizationEventType = ref('all')
const selectedCustomizationPackageId = ref(null)
const customizationQuantity = ref(1)
const selectedServiceIds = ref([])
const expandedPackageIds = ref([])
const expandedServiceIds = ref([])
const matchingServicesCatalog = [
  { id: 'mc-host', name: 'Achar MC', description: 'Traditional ceremony host and flow management.', price: 250, eventTypes: ['wedding', 'engagement', 'anniversary'] },
  { id: 'monk-set', name: 'Monk Ceremony Set', description: 'Complete ritual setup for monk blessing.', price: 320, eventTypes: ['monk_ceremony', 'house_blessing'] },
  { id: 'sound-light', name: 'Sound & Lighting', description: 'PA system, DJ support, and stage lighting.', price: 450, eventTypes: ['company_party', 'birthday', 'school_event', 'festival', 'graduation'] },
  { id: 'photo-video', name: 'Photo & Video', description: 'Event photography and highlight video coverage.', price: 850, eventTypes: ['wedding', 'engagement', 'birthday', 'company_party', 'school_event', 'graduation', 'anniversary'] },
  { id: 'premium-catering', name: 'Premium Catering', description: 'Buffet and service crew package.', price: 1500, eventTypes: ['wedding', 'company_party', 'birthday', 'festival', 'anniversary'] },
  { id: 'decor-pack', name: 'Decoration Package', description: 'Theme design, floral setup, and backdrops.', price: 1200, eventTypes: ['wedding', 'engagement', 'baby_shower', 'birthday', 'school_event'] },
  { id: 'cake-dessert', name: 'Cake & Dessert Bar', description: 'Customized cake and dessert station.', price: 380, eventTypes: ['birthday', 'anniversary', 'baby_shower', 'graduation'] },
  { id: 'cultural-band', name: 'Khmer Cultural Band', description: 'Live traditional music performance.', price: 600, eventTypes: ['wedding', 'festival', 'engagement', 'other'] },
]
const packageCatalogByEventType = {
  wedding: [
    { id: 'wed-royal', title: 'Royal Wedding Signature', description: 'Elegant ceremony + reception package.', basePrice: 2800 },
    { id: 'wed-classic', title: 'Classic Wedding Essentials', description: 'Balanced package for medium-size weddings.', basePrice: 1900 },
  ],
  monk_ceremony: [
    { id: 'monk-blessing', title: 'Monk Blessing Complete', description: 'Full monk ceremony setup and coordination.', basePrice: 1250 },
    { id: 'monk-traditional', title: 'Traditional Blessing Basic', description: 'Core ritual set with support team.', basePrice: 900 },
  ],
  house_blessing: [
    { id: 'house-premium', title: 'House Blessing Premium', description: 'Blessing flow, altar setup, and host support.', basePrice: 1100 },
    { id: 'house-basic', title: 'House Blessing Basic', description: 'Essential ceremonial package for new homes.', basePrice: 780 },
  ],
  company_party: [
    { id: 'corp-gala', title: 'Corporate Gala Package', description: 'Production-ready package for company celebrations.', basePrice: 3200 },
    { id: 'corp-social', title: 'Corporate Social Night', description: 'Smart setup for networking and social events.', basePrice: 2100 },
  ],
  birthday: [
    { id: 'bday-deluxe', title: 'Birthday Deluxe Party', description: 'Fun and vibrant premium birthday setup.', basePrice: 1500 },
    { id: 'bday-family', title: 'Birthday Family Package', description: 'Budget-friendly birthday arrangement.', basePrice: 980 },
  ],
  school_event: [
    { id: 'school-annual', title: 'School Annual Event', description: 'Stage, sound, and logistics for school functions.', basePrice: 2600 },
    { id: 'school-ceremony', title: 'School Ceremony Package', description: 'Graduation or ceremony focused setup.', basePrice: 1800 },
  ],
  engagement: [
    { id: 'eng-gold', title: 'Engagement Gold Package', description: 'Elegant engagement decor and hosting support.', basePrice: 1700 },
    { id: 'eng-intimate', title: 'Engagement Intimate Package', description: 'Compact setup for close-family events.', basePrice: 1200 },
  ],
  anniversary: [
    { id: 'anni-romance', title: 'Anniversary Romance Package', description: 'Decor and ambiance tailored for anniversaries.', basePrice: 1400 },
    { id: 'anni-family', title: 'Anniversary Family Package', description: 'Warm and simple anniversary celebration setup.', basePrice: 1050 },
  ],
  baby_shower: [
    { id: 'baby-soft', title: 'Baby Shower Soft Theme', description: 'Themed decor and coordinated setup.', basePrice: 1300 },
    { id: 'baby-classic', title: 'Baby Shower Classic', description: 'Essential baby shower arrangement package.', basePrice: 920 },
  ],
  graduation: [
    { id: 'grad-stage', title: 'Graduation Stage Package', description: 'Photo zone, stage setup, and sound support.', basePrice: 2100 },
    { id: 'grad-party', title: 'Graduation Party Package', description: 'Post-ceremony celebration package.', basePrice: 1450 },
  ],
  festival: [
    { id: 'fest-premium', title: 'Festival Premium Package', description: 'Large-scale festival production support.', basePrice: 4200 },
    { id: 'fest-community', title: 'Community Festival Package', description: 'Reliable setup for community events.', basePrice: 2600 },
  ],
  other: [
    { id: 'other-custom', title: 'Custom Event Package', description: 'Flexible package for unique requirements.', basePrice: 1600 },
    { id: 'other-essentials', title: 'Event Essentials', description: 'Core setup for miscellaneous events.', basePrice: 1100 },
  ],
}
const packageImageByEventType = {
  wedding:
    'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=900&q=80',
  monk_ceremony:
    'https://images.unsplash.com/photo-1529699211952-734e80c4d42b?auto=format&fit=crop&w=900&q=80',
  house_blessing:
    'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=900&q=80',
  company_party:
    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=900&q=80',
  birthday:
    'https://images.unsplash.com/photo-1464349153735-7db50ed83c84?auto=format&fit=crop&w=900&q=80',
  school_event:
    'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=900&q=80',
  engagement:
    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=900&q=80',
  anniversary:
    'https://images.unsplash.com/photo-1519225421980-715cb0215aed?auto=format&fit=crop&w=900&q=80',
  baby_shower:
    'https://images.unsplash.com/photo-1478144592103-25e218a04891?auto=format&fit=crop&w=900&q=80',
  graduation:
    'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=900&q=80',
  festival:
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80',
  other:
    'https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=900&q=80',
}
const packageServiceTemplatesByEventType = {
  wedding: [
    { name: 'Ceremony Styling', detail: 'Aisle decor, floral focal points, and coordinated welcome area setup.' },
    { name: 'Reception Decor', detail: 'Head table styling, centerpiece arrangement, and backdrop dressing.' },
    { name: 'Guest Flow Support', detail: 'Timeline guidance for seating, grand entry, and reception transitions.' },
  ],
  monk_ceremony: [
    { name: 'Ritual Setup', detail: 'Prepared altar area, monk seating, and ceremonial layout support.' },
    { name: 'Offerings Coordination', detail: 'Arrangement guidance for offerings and family participation sequence.' },
    { name: 'Ceremony Timing', detail: 'On-site support to keep the blessing flow smooth and respectful.' },
  ],
  house_blessing: [
    { name: 'Home Altar Arrangement', detail: 'Main blessing corner styled with practical ceremonial essentials.' },
    { name: 'Family Guidance', detail: 'Clear run-of-show for host family and invited relatives.' },
    { name: 'Cleanup & Reset', detail: 'Light post-ceremony reset to restore the space quickly.' },
  ],
  company_party: [
    { name: 'Stage & Layout Plan', detail: 'Event floor mapping for seating, stage, and circulation paths.' },
    { name: 'Audio Visual Support', detail: 'Mic, speaker, and show-flow coordination with your event team.' },
    { name: 'Brand Styling', detail: 'Visual elements aligned to your company event identity.' },
  ],
  birthday: [
    { name: 'Theme Decor', detail: 'Color-matched backdrop, table styling, and photo corner setup.' },
    { name: 'Program Support', detail: 'Birthday agenda support for cake moment and activity timing.' },
    { name: 'Guest Comfort Setup', detail: 'Seating, flow, and presentation arranged for all age groups.' },
  ],
  school_event: [
    { name: 'Event Logistics', detail: 'Structured area zoning for students, staff, and guest movement.' },
    { name: 'Stage Management', detail: 'Cue support for speeches, performances, and recognition segments.' },
    { name: 'Safety-first Layout', detail: 'Clear traffic setup to maintain order during peak attendance.' },
  ],
  engagement: [
    { name: 'Ceremony Scene Design', detail: 'Elegant setup for ring exchange and family photo moments.' },
    { name: 'Guest Welcome Styling', detail: 'Entrance decor and hospitality table arrangement support.' },
    { name: 'Timeline Coordination', detail: 'Assistance for smooth transition between rituals and reception.' },
  ],
  anniversary: [
    { name: 'Memory-focused Decor', detail: 'Romantic setup with personalized visual storytelling elements.' },
    { name: 'Dinner Ambience', detail: 'Table styling and lighting mood planning for evening comfort.' },
    { name: 'Celebration Flow', detail: 'Support for speeches, toast moments, and media presentation.' },
  ],
  baby_shower: [
    { name: 'Soft Theme Styling', detail: 'Pastel-forward decor with coordinated photo-friendly corners.' },
    { name: 'Gift & Activity Station', detail: 'Practical arrangement for gifts, games, and family interactions.' },
    { name: 'Guest Seating Plan', detail: 'Comfortable layout optimized for relatives and close friends.' },
  ],
  graduation: [
    { name: 'Recognition Stage Setup', detail: 'Background, podium area, and graduate photo capture zone.' },
    { name: 'Program Sequence Support', detail: 'Agenda coordination for speeches and certificate segments.' },
    { name: 'Photo Session Planning', detail: 'Quick-flow arrangement to reduce waiting during photo rounds.' },
  ],
  festival: [
    { name: 'Large Area Zoning', detail: 'Segmented setup for booths, stage, and visitor circulation.' },
    { name: 'Performance Support', detail: 'Production-ready coordination for performers and technical teams.' },
    { name: 'Crowd Flow Management', detail: 'Entry/exit route planning and hotspot balancing guidance.' },
  ],
  other: [
    { name: 'Consultation & Scoping', detail: 'Requirements review and adaptable plan based on your event goal.' },
    { name: 'Core Setup Package', detail: 'Essential decor, layout, and sequence support for custom events.' },
    { name: 'On-site Coordination', detail: 'Operational support to keep key moments on schedule.' },
  ],
}

function buildPackageServiceDescriptions(eventType, title) {
  const baseServices = packageServiceTemplatesByEventType[eventType] || packageServiceTemplatesByEventType.other
  const normalizedTitle = title.toLowerCase()
  const premiumTier = /(royal|signature|premium|gold|deluxe|gala|annual|romance|stage|custom)/.test(normalizedTitle)
  const essentialTier = /(classic|basic|family|intimate|community|essentials)/.test(normalizedTitle)

  let levelDetail = 'Balanced service coverage for standard event requirements.'
  if (premiumTier) levelDetail = 'Premium-level coordination with extended setup depth and enhanced finishing touches.'
  if (essentialTier) levelDetail = 'Essential-focused coverage optimized for practical budgets and compact timelines.'

  return [...baseServices, { name: 'Coordination Level', detail: levelDetail }]
}

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
const fallbackVendorLocation = '88 Flower District, New York, NY 10001'
const userLocationMapUrl = computed(() => {
  if (userLatitude.value === null || userLongitude.value === null) return ''
  const lat = userLatitude.value.toFixed(6)
  const lng = userLongitude.value.toFixed(6)
  return `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=13&size=700x320&markers=${lat},${lng},orange-pushpin`
})
const vendorLocationText = computed(() => {
  const firstWithLocation = vendorEvents.value.find((item) => item.location && item.location.trim())
  return firstWithLocation?.location || fallbackVendorLocation
})
const vendorMapEmbedUrl = computed(
  () => `https://www.google.com/maps?q=${encodeURIComponent(vendorLocationText.value)}&output=embed`,
)

const vendorFallbackPackages = computed(() => {
  const packages = []
  Object.entries(packageCatalogByEventType).forEach(([type, entries]) => {
    entries.forEach((entry) => {
      const price = Number(entry.basePrice || 0)
      packages.push({
        id: `preview-${type}-${entry.id}`,
        title: entry.title,
        eventType: type,
        eventTypeLabel: eventTypeMap[type] || 'Other',
        description: entry.description,
        location: fallbackVendorLocation,
        date: 'Date TBD',
        price,
        priceLabel: `From $${price.toLocaleString()}`,
        image: packageImageByEventType[type] || packageImageByEventType.other,
        isPreview: true,
      })
    })
  })
  return packages
})

const filteredPackages = computed(() => {
  const q = globalSearch.value.trim().toLowerCase()
  const sourcePackages = vendorEvents.value.length ? vendorEvents.value : vendorFallbackPackages.value
  return sourcePackages.filter((item) => {
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
const fallbackBookingEvent = computed(() => vendorEvents.value[0] || null)
const catalogPackages = computed(() => {
  const types = customizationEventType.value === 'all'
    ? eventTypeOptions.map((opt) => opt.value).filter((value) => value !== 'all')
    : [customizationEventType.value]

  const packages = []
  types.forEach((type) => {
    const entries = packageCatalogByEventType[type] || []
    entries.forEach((entry) => {
      const exactEvent = vendorEvents.value.find((event) => event.eventType === type)
      const fallbackEvent = fallbackBookingEvent.value
      const backing = exactEvent || fallbackEvent
      packages.push({
        id: `${type}-${entry.id}`,
        title: entry.title,
        description: entry.description,
        price: entry.basePrice,
        image: packageImageByEventType[type] || packageImageByEventType.other,
        services: buildPackageServiceDescriptions(type, entry.title),
        eventType: type,
        eventTypeLabel: eventTypeMap[type] || 'Other',
        location: backing?.location || fallbackVendorLocation,
        date: backing?.date || 'Date TBD',
        backingEventId: backing?.id || null,
      })
    })
  })
  return packages
})

const filteredCustomizationPackages = computed(() => {
  const q = customizationSearch.value.trim().toLowerCase()
  return catalogPackages.value.filter((item) => {
    const matchesSearch =
      !q ||
      item.title.toLowerCase().includes(q) ||
      item.description.toLowerCase().includes(q) ||
      item.location.toLowerCase().includes(q)
    return matchesSearch
  })
})
const selectedCustomizationPackage = computed(
  () => catalogPackages.value.find((item) => item.id === selectedCustomizationPackageId.value) || null,
)
const effectiveCustomizationEventType = computed(() => {
  if (customizationEventType.value !== 'all') return customizationEventType.value
  return selectedCustomizationPackage.value?.eventType || 'all'
})
const filteredMatchingServices = computed(() => {
  const q = customizationSearch.value.trim().toLowerCase()
  return matchingServicesCatalog.filter((service) => {
    const matchesEvent =
      effectiveCustomizationEventType.value === 'all' ||
      service.eventTypes.includes(effectiveCustomizationEventType.value) ||
      service.eventTypes.includes('other')
    const matchesSearch =
      !q || service.name.toLowerCase().includes(q) || service.description.toLowerCase().includes(q)
    return matchesEvent && matchesSearch
  })
})
const selectedMatchingServices = computed(() =>
  matchingServicesCatalog.filter((service) => selectedServiceIds.value.includes(service.id)),
)
const selectedServicesSubtotal = computed(() =>
  selectedMatchingServices.value.reduce((sum, service) => sum + service.price, 0),
)
const customizationPackageSubtotal = computed(() => {
  if (!selectedCustomizationPackage.value) return 0
  const unitPrice = Number(selectedCustomizationPackage.value.price || 0)
  return Number.isFinite(unitPrice) ? unitPrice * customizationQuantity.value : 0
})
const serviceFeeAmount = computed(() =>
  Number(((customizationPackageSubtotal.value + selectedServicesSubtotal.value) * serviceFeeRate).toFixed(2)),
)
const customizationTotal = computed(() =>
  customizationPackageSubtotal.value + selectedServicesSubtotal.value + serviceFeeAmount.value,
)
const selectedServicesCount = computed(() => selectedMatchingServices.value.length)

function isPackageExpanded(id) {
  return expandedPackageIds.value.includes(id)
}

function togglePackageDetails(id) {
  if (isPackageExpanded(id)) {
    expandedPackageIds.value = expandedPackageIds.value.filter((itemId) => itemId !== id)
    return
  }
  expandedPackageIds.value = [...expandedPackageIds.value, id]
}

function isServiceExpanded(id) {
  return expandedServiceIds.value.includes(id)
}

function toggleServiceDetails(id) {
  if (isServiceExpanded(id)) {
    expandedServiceIds.value = expandedServiceIds.value.filter((itemId) => itemId !== id)
    return
  }
  expandedServiceIds.value = [...expandedServiceIds.value, id]
}

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
    service: apiBooking.service_name || event.title || 'Service Booking',
    date: formatDateTime(event.starts_at),
    metaLabel: 'Event Type',
    metaValue: eventTypeMap[apiBooking.requested_event_type || event.event_type] || 'Other',
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
  const price = Number(apiEvent.price || 0)
  return {
    id: apiEvent.id,
    title: apiEvent.title,
    eventType: apiEvent.event_type || 'other',
    eventTypeLabel: eventTypeMap[apiEvent.event_type] || 'Other',
    description: apiEvent.description || 'Professional service package for your event.',
    location: apiEvent.location,
    date: formatDateTime(apiEvent.starts_at),
    price,
    priceLabel: `From $${price.toLocaleString()}`,
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

function goToPackageCustomization(preferredEventType = 'all', preferredTitle = '') {
  currentPage.value = 'customization'
  customizationEventType.value = preferredEventType || 'all'
  customizationSearch.value = ''
  customizationQuantity.value = 1
  selectedServiceIds.value = []

  const normalizedTitle = preferredTitle.trim().toLowerCase()
  const typeScopedPackages = catalogPackages.value.filter((pkg) =>
    customizationEventType.value === 'all' ? true : pkg.eventType === customizationEventType.value,
  )

  if (normalizedTitle) {
    const matchedByTitle = typeScopedPackages.find((pkg) => pkg.title.toLowerCase().includes(normalizedTitle))
    if (matchedByTitle) {
      selectedCustomizationPackageId.value = matchedByTitle.id
      return
    }
  }

  selectedCustomizationPackageId.value = typeScopedPackages[0]?.id || null
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
      userLocation.value = `${lat}, ${lng}`
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

function selectCustomizationPackage(id) {
  selectedCustomizationPackageId.value = id
}

function isServiceSelected(id) {
  return selectedServiceIds.value.includes(id)
}

function toggleMatchingService(id) {
  if (isServiceSelected(id)) {
    selectedServiceIds.value = selectedServiceIds.value.filter((serviceId) => serviceId !== id)
    return
  }
  selectedServiceIds.value = [...selectedServiceIds.value, id]
}

async function confirmCustomization() {
  const name = customerName.value.trim()
  const email = customerEmail.value.trim()

  if (!name || !email) {
    notice.value = 'Please enter your name and email before confirming package.'
    return
  }

  if (!selectedCustomizationPackage.value) {
    notice.value = 'Please select a package first.'
    return
  }
  if (!selectedCustomizationPackage.value.backingEventId) {
    notice.value = 'No vendor event is available for booking right now.'
    return
  }

  const qty = Number(customizationQuantity.value || 1)
  if (!Number.isFinite(qty) || qty < 1) {
    notice.value = 'Please select a valid quantity.'
    return
  }

  const backingEvent = vendorEvents.value.find(
    (event) => event.id === selectedCustomizationPackage.value.backingEventId,
  )
  if (!backingEvent) {
    notice.value = 'Could not find a valid vendor event for this package.'
    return
  }

  const availability = getAvailability(backingEvent) || (await checkEventAvailability(backingEvent))

  if (!availability || !availability.service_available || !availability.vendor_available) {
    notice.value = availability?.message || 'This package is not available right now.'
    return
  }

  bookingSubmittingEventId.value = backingEvent.id
  try {
    await apiPost('bookings', {
      event_id: backingEvent.id,
      quantity: qty,
      customer_name: name,
      customer_email: email,
      service_name: selectedCustomizationPackage.value.title,
      requested_event_type: selectedCustomizationPackage.value.eventType,
    })

    notice.value = `Package booked successfully. Total estimate: $${customizationTotal.value.toLocaleString()}.`
    await loadBookings()
    bookingFilter.value = 'Upcoming'
    goToBookings()
  } catch (error) {
    notice.value = error.message || 'Could not confirm this package.'
  } finally {
    bookingSubmittingEventId.value = null
  }
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

watch([customizationEventType, customizationSearch, vendorEvents], () => {
  if (!selectedCustomizationPackageId.value) return
  const existsInFiltered = filteredCustomizationPackages.value.some(
    (item) => item.id === selectedCustomizationPackageId.value,
  )
  if (!existsInFiltered) {
    selectedCustomizationPackageId.value = null
  }
})

watch(filteredCustomizationPackages, (packages) => {
  const validIds = new Set(packages.map((item) => item.id))
  expandedPackageIds.value = expandedPackageIds.value.filter((id) => validIds.has(id))
})

watch(filteredMatchingServices, (services) => {
  const validIds = new Set(services.map((item) => item.id))
  expandedServiceIds.value = expandedServiceIds.value.filter((id) => validIds.has(id))
})

watch([customizationEventType, selectedCustomizationPackageId], () => {
  const allowedIds = new Set(filteredMatchingServices.value.map((service) => service.id))
  selectedServiceIds.value = selectedServiceIds.value.filter((id) => allowedIds.has(id))
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
          <a href="#" :class="{ active: currentPage === 'vendor' || currentPage === 'customization' }" @click.prevent="goToVendor">View Vendors</a>
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
            <button type="button" @click="goToPackageCustomization()">
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
        <a href="#" :class="{ active: activeVendorTab === 'services' }" @click.prevent="goToPackageCustomization()">Packages & Services</a>
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
                    <button
                      type="button"
                      class="ghost"
                      @click="goToPackageCustomization(item.eventType, item.title)"
                    >
                      Select Package
                    </button>
                    <input
                      v-if="!item.isPreview"
                      v-model.number="selectedQuantities[item.id]"
                      type="number"
                      min="1"
                      max="20"
                    />
                    <button
                      type="button"
                      :disabled="!item.isPreview && bookingSubmittingEventId === item.id"
                      @click="item.isPreview ? goToMessages(vendorProfile.name) : bookPackage(item)"
                    >
                      {{
                        item.isPreview
                          ? 'Message Vendor'
                          : bookingSubmittingEventId === item.id
                            ? 'Booking...'
                            : 'Book Now'
                      }}
                    </button>
                    <button
                      v-if="!item.isPreview"
                      type="button"
                      class="ghost"
                      :disabled="checkingAvailabilityEventId === item.id"
                      @click="checkEventAvailability(item)"
                    >
                      {{ checkingAvailabilityEventId === item.id ? 'Checking...' : 'Check Availability' }}
                    </button>
                  </div>
                </div>
                <div v-if="!item.isPreview" class="availability-row">
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
            <p>{{ vendorLocationText }}</p>
            <iframe class="map-frame" :src="vendorMapEmbedUrl" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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

    <main v-else-if="currentPage === 'customization'" class="shell customization-page">
      <div class="breadcrumbs">Home > Vendor Details > Service Package Customization</div>

      <section class="customization-head card">
        <div class="customization-head-main">
          <h1>Service Package Customization</h1>
          <p>Select your event type first, then choose a matching package and confirm booking.</p>
          <div class="customization-filters">
            <select v-model="customizationEventType" class="event-type-select">
              <option v-for="option in eventTypeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <input
              v-model="customizationSearch"
              class="customization-search"
              type="search"
              placeholder="Search package or location..."
            />
          </div>
        </div>
        <div class="customization-metrics">
          <div>
            <small>Packages</small>
            <strong>{{ filteredCustomizationPackages.length }}</strong>
          </div>
          <div>
            <small>Services</small>
            <strong>{{ selectedServicesCount }}</strong>
          </div>
          <div>
            <small>Estimate</small>
            <strong>${{ customizationTotal.toLocaleString() }}</strong>
          </div>
        </div>
      </section>

      <section class="customization-layout">
        <div class="customization-list">
          <article class="customization-section">
            <div class="customization-section-head">
              <span>📦</span>
              <h2>Matching Packages</h2>
            </div>

            <div v-if="filteredCustomizationPackages.length === 0" class="card empty-state">
              No packages match this event type and search.
            </div>

            <div class="addon-grid">
              <PackageCard
                v-for="item in filteredCustomizationPackages"
                :key="item.id"
                :item="item"
                :is-selected="selectedCustomizationPackageId === item.id"
                :is-expanded="isPackageExpanded(item.id)"
                @select="selectCustomizationPackage"
                @toggle-details="togglePackageDetails"
                @message="goToMessages(vendorProfile.name)"
              />
            </div>
          </article>

          <article class="customization-section">
            <div class="customization-section-head">
              <span>🧩</span>
              <h2>Matching Services For {{ eventTypeMap[effectiveCustomizationEventType] || 'Selected Event' }}</h2>
            </div>

            <div v-if="filteredMatchingServices.length === 0" class="card empty-state">
              No matching services for this event type.
            </div>

            <div class="addon-grid">
              <ServiceCard
                v-for="service in filteredMatchingServices"
                :key="service.id"
                :service="service"
                :is-selected="isServiceSelected(service.id)"
                :is-expanded="isServiceExpanded(service.id)"
                :event-type-map="eventTypeMap"
                :service-fee-rate="serviceFeeRate"
                @toggle-service="toggleMatchingService"
                @toggle-details="toggleServiceDetails"
                @message="goToMessages(vendorProfile.name)"
              />
            </div>
          </article>
        </div>

        <aside class="card customization-summary">
          <h2>Booking Summary</h2>
          <div class="summary-items">
            <h3>Selected Package</h3>
            <p v-if="!selectedCustomizationPackage">Choose one package from the list.</p>
            <div v-else class="summary-package">
              <strong>{{ selectedCustomizationPackage.title }}</strong>
              <p>{{ selectedCustomizationPackage.eventTypeLabel }} | {{ selectedCustomizationPackage.location }}</p>
            </div>
          </div>

          <div class="summary-row">
            <span>Quantity</span>
            <input v-model.number="customizationQuantity" type="number" min="1" max="20" />
          </div>
          <div class="summary-row">
            <span>Package Price</span>
            <strong>${{ customizationPackageSubtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-items">
            <h3>Selected Services</h3>
            <p v-if="selectedMatchingServices.length === 0">No additional services selected.</p>
            <div v-for="service in selectedMatchingServices" :key="service.id" class="summary-row">
              <span>{{ service.name }}</span>
              <strong>+${{ service.price.toLocaleString() }}</strong>
            </div>
          </div>
          <div class="summary-row">
            <span>Services Subtotal</span>
            <strong>${{ selectedServicesSubtotal.toLocaleString() }}</strong>
          </div>
          <div class="summary-row muted">
            <span>Service Fee (10%)</span>
            <strong>${{ serviceFeeAmount.toLocaleString() }}</strong>
          </div>

          <div class="summary-total">
            <span>Total Price</span>
            <strong>${{ customizationTotal.toLocaleString() }}</strong>
          </div>

          <button
            type="button"
            class="confirm-selection"
            :disabled="!selectedCustomizationPackage || bookingSubmittingEventId === (selectedCustomizationPackage && selectedCustomizationPackage.backingEventId)"
            @click="confirmCustomization"
          >
            {{
              bookingSubmittingEventId === (selectedCustomizationPackage && selectedCustomizationPackage.backingEventId)
                ? 'Confirming...'
                : 'Confirm Selection'
            }}
          </button>
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
