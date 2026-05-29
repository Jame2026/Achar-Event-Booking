<script setup>
import { useLanguageCopy } from '../../features/language'

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

const copyByLanguage = {
  en: {
    breadcrumbs: 'Home > Vendor Details > Check Availability',
    title: 'Select an Event Date',
    subtitle: 'Please pick your preferred date to see available time slots.',
    available: 'Available',
    booked: 'Booked',
    selected: 'Selected',
    availableTimeSlots: 'Available Time Slots',
    quickSummary: 'Quick Summary',
    selectedDate: 'Selected Date',
    estimatedTime: 'Estimated Time',
    selectTimeSlot: 'Select a time slot',
    baseRate: 'Base Rate',
    perHour: '/ hour',
    serviceDeposit: 'Service Deposit (15%)',
    applicationFee: 'Application Fee',
    estimatedTotal: 'Estimated Total',
    confirm: 'Confirm & Proceed',
  },
  km: {
    breadcrumbs: 'ទំព័រដើម > ព័ត៌មានអ្នកផ្គត់ផ្គង់ > ពិនិត្យមើលពេលទំនេរ',
    title: 'ជ្រើសរើសកាលបរិច្ឆេទព្រឹត្តិការណ៍',
    subtitle: 'សូមជ្រើសរើសកាលបរិច្ឆេទដែលអ្នកចង់បាន ដើម្បីមើលម៉ោងទំនេរ។',
    available: 'ទំនេរ',
    booked: 'បានកក់',
    selected: 'បានជ្រើស',
    availableTimeSlots: 'ម៉ោងទំនេរ',
    quickSummary: 'សេចក្តីសង្ខេបរហ័ស',
    selectedDate: 'កាលបរិច្ឆេទដែលបានជ្រើស',
    estimatedTime: 'ម៉ោងប៉ាន់ស្មាន',
    selectTimeSlot: 'ជ្រើសរើសម៉ោង',
    baseRate: 'តម្លៃគោល',
    perHour: '/ ម៉ោង',
    serviceDeposit: 'ប្រាក់កក់សេវា (15%)',
    applicationFee: 'ថ្លៃស្នើសុំ',
    estimatedTotal: 'តម្លៃសរុបប៉ាន់ស្មាន',
    confirm: 'បញ្ជាក់ និងបន្ត',
  },
  zh: {
    breadcrumbs: '首页 > 商家详情 > 查看档期',
    title: '选择活动日期',
    subtitle: '请选择您偏好的日期以查看可用时间段。',
    available: '可用',
    booked: '已预订',
    selected: '已选择',
    availableTimeSlots: '可用时间段',
    quickSummary: '快速摘要',
    selectedDate: '已选日期',
    estimatedTime: '预计时间',
    selectTimeSlot: '请选择时间段',
    baseRate: '基础价格',
    perHour: '/ 小时',
    serviceDeposit: '服务定金 (15%)',
    applicationFee: '申请费',
    estimatedTotal: '预计总价',
    confirm: '确认并继续',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <main class="shell availability-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

    <section class="availability-layout">
      <article class="card availability-main">
        <header class="availability-head">
          <h1>{{ uiText.title }}</h1>
          <p>{{ uiText.subtitle }}</p>
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
            <span><i class="dot available"></i> {{ uiText.available }}</span>
            <span><i class="dot booked"></i> {{ uiText.booked }}</span>
            <span><i class="dot selected"></i> {{ uiText.selected }}</span>
          </div>
        </section>

        <section class="availability-slots">
          <div class="availability-slots-head">
            <h2>{{ uiText.availableTimeSlots }}</h2>
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
                {{ slot.booked ? uiText.booked : slot.value }}
              </button>
            </div>
          </div>
        </section>
      </article>

      <aside class="card availability-summary">
        <h3>{{ uiText.quickSummary }}</h3>
        <p>{{ props.availabilityContext.subtitle }}</p>
        <div class="availability-summary-block">
          <small>{{ uiText.selectedDate }}</small>
          <strong>{{ props.selectedAvailabilityDateLabel }}</strong>
        </div>
        <div class="availability-summary-block">
          <small>{{ uiText.estimatedTime }}</small>
          <strong>{{ props.selectedAvailabilitySlotInfo ? props.selectedAvailabilitySlotInfo.value : uiText.selectTimeSlot }}</strong>
        </div>
        <div class="availability-summary-block">
          <small>{{ uiText.baseRate }}</small>
          <strong>${{ props.availabilityBaseRate.toLocaleString() }} {{ uiText.perHour }}</strong>
        </div>

        <div class="availability-fee-row">
          <span>{{ uiText.serviceDeposit }}</span>
          <strong>${{ props.availabilityDeposit.toLocaleString() }}</strong>
        </div>
        <div class="availability-fee-row">
          <span>{{ uiText.applicationFee }}</span>
          <strong>${{ props.availabilityApplicationFee.toLocaleString() }}</strong>
        </div>
        <div class="availability-total-row">
          <span>{{ uiText.estimatedTotal }}</span>
          <strong>${{ props.availabilityTotalEstimate.toLocaleString() }}</strong>
        </div>

        <button
          type="button"
          class="confirm-selection"
          :disabled="!props.selectedAvailabilitySlotInfo"
          @click="props.confirmAvailabilityRequest"
        >
          {{ uiText.confirm }}
        </button>
      </aside>
    </section>
  </main>
</template>
