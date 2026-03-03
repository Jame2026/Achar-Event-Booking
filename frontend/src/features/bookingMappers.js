export function formatDateTime(dateString) {
  if (!dateString) return 'Date TBD'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return 'Date TBD'
  return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
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
  const status = (apiBooking.status || 'pending').toLowerCase()

  const statusText =
    status === 'confirmed' ? 'Confirmed' : status === 'cancelled' ? 'Cancelled' : 'Pending Approval'

  const statusClass =
    status === 'confirmed' ? 'confirmed' : status === 'cancelled' ? 'done' : 'pending'

  const type = deriveBookingType(status, event.starts_at)

  return {
    id: apiBooking.id,
    vendor: vendorName,
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

export function mapEvent(apiEvent, eventTypeMap) {
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
