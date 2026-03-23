import {
  formatEventPriceLabel,
  normalizePackageServices,
  resolveEventPrice,
} from './packagePricing'

export function formatDateTime(dateString) {
  const raw = String(dateString || '').trim()
  if (!raw) return 'Date TBD'

  const dateOnlyMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (dateOnlyMatch) {
    const [, year, month, day] = dateOnlyMatch
    const literalDate = new Date(Number(year), Number(month) - 1, Number(day))
    return literalDate.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
  }

  const date = new Date(raw)
  if (Number.isNaN(date.getTime())) return 'Date TBD'
  return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
}

export function normalizeBookingDateKey(value) {
  const raw = String(value || '').trim()
  if (!raw) return ''

  const isoDateMatch = raw.match(/^(\d{4}-\d{2}-\d{2})(?:[T\s].*)?$/)
  if (isoDateMatch) {
    return isoDateMatch[1]
  }

  const parsed = new Date(raw)
  if (Number.isNaN(parsed.getTime())) {
    return raw.toLowerCase()
  }

  const year = parsed.getFullYear()
  const month = String(parsed.getMonth() + 1).padStart(2, '0')
  const day = String(parsed.getDate()).padStart(2, '0')

  return `${year}-${month}-${day}`
}

export function getBookingMatchKey(row) {
  const eventId = row?.eventId || row?.event_id || row?.event?.id || null
  const date = normalizeBookingDateKey(
    row?.requestedEventDate ||
      row?.requested_event_date ||
      row?.date ||
      row?.dateLabel ||
      row?.eventDate ||
      row?.event?.starts_at ||
      '',
  )

  if (eventId) {
    return `event:${eventId}|${date}`
  }

  const service = row?.service || row?.service_name || row?.name || ''
  const total = row?.total ?? row?.total_amount ?? row?.servicePrice ?? 0

  return `service:${String(service).trim().toLowerCase()}|${Number(total || 0)}|${date}`
}

export function normalizeBookingEventTypeKey(...candidates) {
  for (const candidate of candidates) {
    const normalized = String(candidate || '').trim().toLowerCase()
    if (normalized && normalized !== 'all') {
      return normalized
    }
  }

  return 'other'
}

export function summarizeBookedServices(bookedItems, fallbackService = 'Service Booking') {
  const items = Array.isArray(bookedItems) ? bookedItems : []
  const uniqueNames = items
    .map((item) => String(item?.name || '').trim())
    .filter(Boolean)
    .filter((name, index, rows) => rows.findIndex((candidate) => candidate.toLowerCase() === name.toLowerCase()) === index)

  if (uniqueNames.length === 0) {
    return fallbackService
  }

  if (uniqueNames.length === 1) {
    return uniqueNames[0]
  }

  return `${uniqueNames[0]} + ${uniqueNames.length - 1} more`
}

export function deriveBookingType(status, startsAt) {
  if (status === 'cancelled') return 'Drafts'
  if (!startsAt) return status === 'pending' ? 'Upcoming' : 'Completed'
  const eventDate = new Date(startsAt)
  const now = new Date()
  if (!Number.isNaN(eventDate.getTime()) && eventDate < now) return 'Completed'
  return 'Upcoming'
}

export function mapBooking(apiBooking, options) {
  const { vendorName, eventTypeMap } = options
  const event = apiBooking.event || {}
  const vendor = event.vendor || {}
  const bookedItems = Array.isArray(apiBooking.booked_items) ? apiBooking.booked_items : []
  const resolvedVendorName =
    vendor.name ||
    apiBooking.vendor_name ||
    vendorName ||
    'Selected Vendor'
  const resolvedEventType = normalizeBookingEventTypeKey(
    apiBooking.requested_event_type,
    event.event_type,
  )
  const fallbackServiceName = apiBooking.service_name || event.title || 'Service Booking'
  const bookingDate = apiBooking.requested_event_date || event.starts_at
  const status = (apiBooking.status || 'pending').toLowerCase()

  const statusText =
    status === 'confirmed' ? 'Confirmed' : status === 'cancelled' ? 'Cancelled' : 'Pending Approval'

  const statusClass =
    status === 'confirmed' ? 'confirmed' : status === 'cancelled' ? 'done' : 'pending'

  const type = deriveBookingType(status, bookingDate)

  return {
    id: apiBooking.id,
    vendor: resolvedVendorName,
    service: summarizeBookedServices(bookedItems, fallbackServiceName),
    servicePrice: Number(apiBooking.total_amount || 0),
    quantity: Number(apiBooking.quantity || 1),
    date: formatDateTime(bookingDate),
    metaLabel: 'Event Type',
    metaValue: eventTypeMap[resolvedEventType] || 'Other',
    placeLabel: 'Total',
    placeValue: `$${Number(apiBooking.total_amount || 0).toLocaleString()}`,
    status: statusText,
    statusClass,
    type,
    eventType: resolvedEventType,
    eventId: event.id,
    vendorId: event.vendor_id || vendor.id || null,
    vendorEmail: vendor.email || apiBooking.vendor_email || '',
    image:
      event.image_url ||
      'https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=760&q=80',
    bookedItems,
    canDelete: type === 'Completed',
    primaryBtn: status === 'pending' ? 'Message Vendor' : 'View Details',
    secondaryBtn: 'Reschedule',
    note: [apiBooking.customer_name, apiBooking.customer_email || apiBooking.customer_phone]
      .filter(Boolean)
      .join(' | '),
  }
}

export function mapEvent(apiEvent, eventTypeMap) {
  const packages = normalizePackageServices(apiEvent.packages)
  const serviceMode = apiEvent.service_mode || apiEvent.serviceMode || 'overall'
  const price = resolveEventPrice({
    packages,
    serviceMode,
    fallbackPrice: apiEvent.price,
  })

  return {
    id: apiEvent.id,
    vendorId: apiEvent.vendor_id || null,
    vendorName: apiEvent.vendor?.name || apiEvent.vendor_name || '',
    vendorEmail: apiEvent.vendor?.email || apiEvent.vendor_email || '',
    title: apiEvent.title,
    eventType: apiEvent.event_type || 'other',
    eventTypeLabel: eventTypeMap[apiEvent.event_type] || 'Other',
    description: apiEvent.description || 'Professional service package for your event.',
    packages,
    qrCodeUrl: apiEvent.qr_code_url || apiEvent.qrCodeUrl || '',
    imageUrl: apiEvent.image_url || '',
    startsAt: apiEvent.starts_at || '',
    endsAt: apiEvent.ends_at || '',
    capacity: Number(apiEvent.capacity || 0),
    serviceMode,
    location: apiEvent.location,
    locationLatitude: apiEvent.location_latitude ?? null,
    locationLongitude: apiEvent.location_longitude ?? null,
    date: formatDateTime(apiEvent.starts_at),
    price,
    priceLabel: formatEventPriceLabel(price, serviceMode),
    isActive: Boolean(apiEvent.is_active),
    image:
      apiEvent.image_url ||
      'https://images.unsplash.com/photo-1477511801984-4ad318ed9846?auto=format&fit=crop&w=760&q=80',
  }
}
