import { computed, ref, watch } from 'vue'
import {
  eventTypeMap,
  serviceFeeRate,
} from './appData'

export function useCustomizationFeature({
  vendorEvents,
  customerName,
  customerEmail,
  notice,
  bookingSubmittingEventId,
  checkEventAvailability,
  createBooking,
  loadBookings,
  goToBookings,
  bookingFilter,
}) {
  const customizationSearch = ref('')
  const customizationEventType = ref('all')
  const selectedCustomizationPackageId = ref(null)
  const customizationQuantity = ref(1)
  const selectedServiceIds = ref([])
  const expandedPackageIds = ref([])
  const expandedServiceIds = ref([])

  const catalogPackages = computed(() => {
    return vendorEvents.value
      .filter((event) => event.serviceMode === 'package')
      .filter((event) => customizationEventType.value === 'all' || event.eventType === customizationEventType.value)
      .map((event) => ({
        id: String(event.id),
        title: event.title,
        description: event.description || 'Professional vendor service ready for booking.',
        price: Number(event.price || 0),
        image: event.image,
        services: [],
        eventType: event.eventType,
        eventTypeLabel: event.eventTypeLabel || eventTypeMap[event.eventType] || 'Other',
        location: event.location || 'Location TBD',
        date: event.date || 'Date TBD',
        backingEventId: event.id || null,
<<<<<<< HEAD
        qrCodeUrl: event.qrCodeUrl || '',
=======
        vendorId: event.vendorId || null,
        vendorName: event.vendorName || 'Vendor',
        vendorEmail: event.vendorEmail || '',
>>>>>>> 87ca84ae8e7012ec69b564224e506bf551722ee0
      }))
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
    return vendorEvents.value
      .filter((event) => event.serviceMode === 'overall')
      .filter((event) => {
        const matchesEvent =
          effectiveCustomizationEventType.value === 'all' ||
          event.eventType === effectiveCustomizationEventType.value
        const matchesSearch =
          !q ||
          event.title.toLowerCase().includes(q) ||
          String(event.description || '').toLowerCase().includes(q) ||
          String(event.location || '').toLowerCase().includes(q)
        return matchesEvent && matchesSearch
      })
      .map((event) => ({
        id: String(event.id),
        name: event.title,
        description: event.description || 'Professional vendor service ready for booking.',
        price: Number(event.price || 0),
        eventTypes: [event.eventType || 'other'],
        backingEventId: event.id || null,
        vendorId: event.vendorId || null,
        vendorName: event.vendorName || 'Vendor',
        vendorEmail: event.vendorEmail || '',
        location: event.location || 'Location TBD',
        qrCodeUrl: event.qrCodeUrl || '',
      }))
  })
  const selectedMatchingServices = computed(() =>
    filteredMatchingServices.value.filter((service) => selectedServiceIds.value.includes(service.id)),
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

  function goToPackageCustomization(currentPage, preferredEventType = 'all', preferredTitle = '') {
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

    selectedCustomizationPackageId.value = null
  }

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

  function selectCustomizationPackage(id) {
    selectedCustomizationPackageId.value = selectedCustomizationPackageId.value === id ? null : id
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

  async function confirmCustomization(getAvailability) {
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
      await createBooking({
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

  return {
    customizationSearch,
    customizationEventType,
    selectedCustomizationPackageId,
    customizationQuantity,
    selectedServiceIds,
    expandedPackageIds,
    expandedServiceIds,
    filteredCustomizationPackages,
    selectedCustomizationPackage,
    effectiveCustomizationEventType,
    filteredMatchingServices,
    selectedMatchingServices,
    selectedServicesSubtotal,
    customizationPackageSubtotal,
    serviceFeeAmount,
    customizationTotal,
    selectedServicesCount,
    goToPackageCustomization,
    isPackageExpanded,
    togglePackageDetails,
    isServiceExpanded,
    toggleServiceDetails,
    selectCustomizationPackage,
    isServiceSelected,
    toggleMatchingService,
    confirmCustomization,
  }
}
