<script setup>
const props = defineProps([
  'bindings',
  'monthLabel',
  'calendarCells',
  'selectedAvailabilityDateLabel',
  'availabilitySlots',
  'selectedAvailabilitySlotInfo',
  'availabilityContext',
  'availabilityBaseRate',
  'availabilityDeposit',
  'availabilityApplicationFee',
  'availabilityTotalEstimate',
  'previousAvailabilityMonth',
  'nextAvailabilityMonth',
  'isCalendarCellSelected',
  'selectAvailabilityDate',
  'selectAvailabilitySlot',
  'confirmAvailabilityRequest',
])
</script>

<template>
  <main class="shell availability-page">
    <div class="breadcrumbs">Home > Vendor Details > Check Availability</div>

    <section class="availability-layout">
      <article class="card availability-main">
        <header class="availability-head">
          <h1>Select an Event Date</h1>
          <p>Please pick your preferred date to see available time slots.</p>
        </header>

        <section class="card availability-calendar">
          <div class="availability-calendar-head">
            <h2>{{ props.monthLabel }}</h2>
            <div class="calendar-nav">
              <button type="button" @click="props.previousAvailabilityMonth">‹</button>
              <button type="button" @click="props.nextAvailabilityMonth">›</button>
            </div>
          </div>

          <div class="calendar-weekdays">
            <span v-for="label in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="label">{{ label }}</span>
          </div>

          <div class="calendar-grid">
            <button
              v-for="cell in props.calendarCells"
              :key="cell.id"
              type="button"
              class="calendar-day"
              :class="{
                muted: !cell.inMonth,
                selected: props.isCalendarCellSelected(cell),
                booked: cell.booked && cell.inMonth,
              }"
              :disabled="!cell.inMonth || cell.disabled || cell.booked"
              @click="props.selectAvailabilityDate(cell)"
            >
              {{ cell.day || '' }}
            </button>
          </div>

          <div class="calendar-legend">
            <span><i class="dot available"></i> Available</span>
            <span><i class="dot booked"></i> Booked</span>
            <span><i class="dot selected"></i> Selected</span>
          </div>
        </section>

        <section class="availability-slots">
          <div class="availability-slots-head">
            <h2>Available Time Slots</h2>
            <strong>{{ props.selectedAvailabilityDateLabel }}</strong>
          </div>

          <div v-for="group in props.availabilitySlots" :key="group.period" class="slot-group">
            <h3>{{ group.icon }} {{ group.period }}</h3>
            <div class="slot-row">
              <button
                v-for="slot in group.slots"
                :key="slot.value"
                type="button"
                class="slot-btn"
                :class="{ selected: props.bindings.selectedAvailabilitySlot.value === slot.value, booked: slot.booked }"
                :disabled="slot.booked"
                @click="props.selectAvailabilitySlot(slot)"
              >
                {{ slot.booked ? 'Booked' : slot.value }}
              </button>
            </div>
          </div>
        </section>
      </article>

      <aside class="card availability-summary">
        <h3>Quick Summary</h3>
        <p>{{ props.availabilityContext.subtitle }}</p>
        <div class="availability-summary-block">
          <small>Selected Date</small>
          <strong>{{ props.selectedAvailabilityDateLabel }}</strong>
        </div>
        <div class="availability-summary-block">
          <small>Estimated Time</small>
          <strong>{{ props.selectedAvailabilitySlotInfo ? props.selectedAvailabilitySlotInfo.value : 'Select a time slot' }}</strong>
        </div>
        <div class="availability-summary-block">
          <small>Base Rate</small>
          <strong>${{ props.availabilityBaseRate.toLocaleString() }} / hour</strong>
        </div>

        <div class="availability-fee-row">
          <span>Service Deposit (15%)</span>
          <strong>${{ props.availabilityDeposit.toLocaleString() }}</strong>
        </div>
        <div class="availability-fee-row">
          <span>Application Fee</span>
          <strong>${{ props.availabilityApplicationFee.toLocaleString() }}</strong>
        </div>
        <div class="availability-total-row">
          <span>Estimated Total</span>
          <strong>${{ props.availabilityTotalEstimate.toLocaleString() }}</strong>
        </div>

        <button
          type="button"
          class="confirm-selection"
          :disabled="!props.selectedAvailabilitySlotInfo"
          @click="props.confirmAvailabilityRequest"
        >
          Confirm & Proceed
        </button>
      </aside>
    </section>
  </main>
</template>
