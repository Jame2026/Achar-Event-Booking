import { computed, ref } from 'vue'
import { isDateLikelyBooked, isSlotLikelyBooked } from './availabilityUtils'

export function useAvailabilityFeature({ currentPage, notice, vendorName, goToMessages }) {
  const availabilityMonthCursor = ref(new Date(new Date().getFullYear(), new Date().getMonth(), 1))
  const selectedAvailabilityDate = ref(
    `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`,
  )
  const selectedAvailabilitySlot = ref('')
  const availabilityContext = ref({
    title: 'Service Package',
    subtitle: 'Vendor Availability Check',
    baseRate: 350,
    eventId: null,
  })

  const monthLabel = computed(() =>
    availabilityMonthCursor.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }),
  )
  const selectedAvailabilityDateLabel = computed(() => {
    const date = new Date(selectedAvailabilityDate.value)
    if (Number.isNaN(date.getTime())) return 'Date not selected'
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
  })
  const availabilitySlots = computed(() => {
    const periods = [
      { period: 'Morning', icon: '☀', items: ['08:00 AM', '09:30 AM', '11:00 AM'] },
      { period: 'Afternoon', icon: '☀', items: ['01:00 PM', '02:30 PM', '04:00 PM', '05:30 PM'] },
      { period: 'Evening', icon: '☾', items: ['07:00 PM', '08:30 PM'] },
    ]

    return periods.map((group) => ({
      ...group,
      slots: group.items.map((value) => {
        const booked = isSlotLikelyBooked(selectedAvailabilityDate.value, value)
        return { value, booked }
      }),
    }))
  })
  const selectedAvailabilitySlotInfo = computed(() => {
    const slot = availabilitySlots.value.find((item) => item.value === selectedAvailabilitySlot.value)
    return slot || null
  })
  const availabilityEstimatedHours = computed(() => 4)
  const availabilityBaseRate = computed(() => Number(availabilityContext.value.baseRate || 350))
  const availabilityDeposit = computed(() =>
    Number((availabilityBaseRate.value * availabilityEstimatedHours.value * 0.15).toFixed(2)),
  )
  const availabilityApplicationFee = computed(() =>
    Number((availabilityBaseRate.value * availabilityEstimatedHours.value * 0.035).toFixed(2)),
  )
  const availabilityTotalEstimate = computed(() =>
    Number((availabilityBaseRate.value * availabilityEstimatedHours.value).toFixed(2)),
  )

  const calendarCells = computed(() => {
    const year = availabilityMonthCursor.value.getFullYear()
    const month = availabilityMonthCursor.value.getMonth()
    const firstDay = new Date(year, month, 1)
    const firstWeekday = firstDay.getDay()
    const daysInMonth = new Date(year, month + 1, 0).getDate()

    const cells = []
    for (let i = 0; i < firstWeekday; i += 1) {
      cells.push({ id: `blank-${i}`, day: null, date: '', inMonth: false, booked: false, disabled: true })
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
      const date = new Date(year, month, day)
      const iso = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      const disabled = date < today
      cells.push({
        id: iso,
        day,
        date: iso,
        inMonth: true,
        booked: isDateLikelyBooked(iso),
        disabled,
      })
    }

    return cells
  })

  function isCalendarCellSelected(cell) {
    return cell.inMonth && cell.date === selectedAvailabilityDate.value
  }

  function selectAvailabilityDate(cell) {
    if (!cell?.inMonth || cell.disabled || cell.booked) return
    selectedAvailabilityDate.value = cell.date
    selectedAvailabilitySlot.value = ''
  }

  function previousAvailabilityMonth() {
    availabilityMonthCursor.value = new Date(
      availabilityMonthCursor.value.getFullYear(),
      availabilityMonthCursor.value.getMonth() - 1,
      1,
    )
  }

  function nextAvailabilityMonth() {
    availabilityMonthCursor.value = new Date(
      availabilityMonthCursor.value.getFullYear(),
      availabilityMonthCursor.value.getMonth() + 1,
      1,
    )
  }

  function selectAvailabilitySlot(slot) {
    if (slot.booked) return
    selectedAvailabilitySlot.value = slot.value
  }

  function goToAvailability(item = null) {
    currentPage.value = 'availability'
    const today = new Date()
    availabilityMonthCursor.value = new Date(today.getFullYear(), today.getMonth(), 1)
    selectedAvailabilityDate.value = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
    selectedAvailabilitySlot.value = ''

    if (item) {
      const rate = Math.max(120, Math.round(Number(item.price || 0) / 4) || 350)
      availabilityContext.value = {
        title: item.title || 'Service Package',
        subtitle: vendorName,
        baseRate: rate,
        eventId: item.isPreview ? null : item.id,
      }
      return
    }

    availabilityContext.value = {
      title: 'Service Package',
      subtitle: vendorName,
      baseRate: 350,
      eventId: null,
    }
  }

  function confirmAvailabilityRequest() {
    if (!selectedAvailabilitySlotInfo.value) {
      notice.value = 'Please select an available time slot before proceeding.'
      return
    }
    notice.value = `Availability request sent for ${availabilityContext.value.title} on ${selectedAvailabilityDateLabel.value} at ${selectedAvailabilitySlotInfo.value.value}.`
    goToMessages(vendorName)
  }

  return {
    availabilityMonthCursor,
    selectedAvailabilityDate,
    selectedAvailabilitySlot,
    availabilityContext,
    monthLabel,
    selectedAvailabilityDateLabel,
    selectedAvailabilitySlotInfo,
    availabilityBaseRate,
    availabilityDeposit,
    availabilityApplicationFee,
    availabilityTotalEstimate,
    calendarCells,
    availabilitySlots,
    isCalendarCellSelected,
    selectAvailabilityDate,
    previousAvailabilityMonth,
    nextAvailabilityMonth,
    selectAvailabilitySlot,
    goToAvailability,
    confirmAvailabilityRequest,
  }
}
