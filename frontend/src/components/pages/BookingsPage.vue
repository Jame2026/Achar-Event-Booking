<script setup>
import { computed, ref } from 'vue'
import { useLanguageCopy } from '../../features/language'

const props = defineProps([
  'bindings',
  'eventTypeOptions',
  'notice',
  'isGuest',
  'isLoadingBookings',
  'filteredBookings',
  'goToDashboard',
  'goToVendor',
  'goToMessages',
  'goToProfile',
  'bookingSecondaryAction',
  'bookingPrimaryAction',
])

const copyByLanguage = {
  en: {
    breadcrumbs: 'Home > My Bookings',
    title: 'My Bookings',
    subtitle: 'Manage your upcoming and past event services.',
    upcoming: 'Upcoming',
    completed: 'Completed',
    drafts: 'Drafts',
    loading: 'Loading bookings from API...',
    signIn: 'Sign in',
    browseVendors: 'Browse vendors',
    resetFilters: 'Reset filters',
    emptyTitle: 'No bookings yet',
    empty: 'No bookings found for this filter. Use your email on vendor page and click "Load My Bookings".',
    searchPlaceholder: 'Search bookings (vendor, service, status)...',
    clearSearch: 'Clear',
    showing: 'Showing',
    totalValue: 'Total value',
    nextUp: 'Next',
    noResultsTitle: 'No matches',
    noResults: 'Try clearing search or adjusting filters.',
    date: 'Date',
  },
  km: {
    breadcrumbs: 'ទំព័រដើម > ការកក់របស់ខ្ញុំ',
    title: 'ការកក់របស់ខ្ញុំ',
    subtitle: 'គ្រប់គ្រងសេវាព្រឹត្តិការណ៍នាពេលខាងមុខ និងកន្លងមករបស់អ្នក។',
    upcoming: 'នាពេលខាងមុខ',
    completed: 'បានបញ្ចប់',
    drafts: 'ព្រាង',
    loading: 'កំពុងផ្ទុកការកក់ពី API...',
    empty: 'រកមិនឃើញការកក់សម្រាប់តម្រងនេះទេ។ ប្រើអ៊ីមែលរបស់អ្នកនៅទំព័រអ្នកផ្គត់ផ្គង់ ហើយចុច "ផ្ទុកការកក់របស់ខ្ញុំ"។',
    date: 'កាលបរិច្ឆេទ',
  },
  zh: {
    breadcrumbs: '首页 > 我的预订',
    title: '我的预订',
    subtitle: '管理您即将开始和过去的活动服务。',
    upcoming: '即将开始',
    completed: '已完成',
    drafts: '草稿',
    loading: '正在从 API 加载预订...',
    empty: '当前筛选条件下没有预订。请在商家页面使用您的邮箱并点击“加载我的预订”。',
    date: '日期',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
const t = computed(() => ({ ...copyByLanguage.en, ...(uiText.value || {}) }))
const filterTabs = computed(() => [
  { value: 'Upcoming', label: t.value.upcoming },
  { value: 'Completed', label: t.value.completed },
  { value: 'Drafts', label: t.value.drafts },
])

const searchQuery = ref('')
const baseBookings = computed(() => (Array.isArray(props.filteredBookings) ? props.filteredBookings : []))
const visibleBookings = computed(() => {
  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (!q) return baseBookings.value
  return baseBookings.value.filter((item) => {
    const haystack = [
      item?.vendor,
      item?.service,
      item?.status,
      item?.metaValue,
      item?.placeValue,
      item?.note,
    ]
      .map((value) => String(value || '').toLowerCase())
      .join(' | ')
    return haystack.includes(q)
  })
})

const visibleTotalValue = computed(() =>
  visibleBookings.value.reduce((sum, item) => sum + Number(item?.servicePrice || 0), 0),
)

const nextBookingLabel = computed(() => {
  const dates = visibleBookings.value
    .map((item) => {
      const parsed = new Date(String(item?.date || ''))
      return Number.isNaN(parsed.getTime()) ? null : parsed
    })
    .filter(Boolean)
    .sort((a, b) => a.getTime() - b.getTime())

  if (!dates.length) return ''
  return dates[0].toLocaleDateString(undefined, { month: 'short', day: '2-digit' })
})

function formatMoney(value) {
  const num = Number(value || 0)
  if (!Number.isFinite(num)) return '$0'
  const abs = Math.abs(num)
  const hasCents = Math.round(abs * 100) % 100 !== 0
  return `$${num.toLocaleString(undefined, {
    minimumFractionDigits: hasCents ? 2 : 0,
    maximumFractionDigits: 2,
  })}`
}

function resetFilters() {
  props.bindings.bookingFilter.value = 'Upcoming'
  props.bindings.bookingEventTypeFilter.value = 'all'
  searchQuery.value = ''
}
</script>

<template>
  <main class="shell bookings-page">
    <div class="breadcrumbs">{{ t.breadcrumbs }}</div>

    <section class="bookings-head">
      <div>
        <h1>{{ t.title }}</h1>
        <p>{{ t.subtitle }}</p>
      </div>
      <div class="booking-filter-wrap">
        <div class="booking-filter">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            type="button"
            :class="{ active: props.bindings.bookingFilter.value === tab.value }"
            @click="props.bindings.bookingFilter.value = tab.value"
          >
            {{ tab.label }}
          </button>
        </div>
        <select
          class="event-type-select"
          :value="props.bindings.bookingEventTypeFilter.value"
          @change="props.bindings.bookingEventTypeFilter.value = $event.target.value"
        >
          <option v-for="option in props.eventTypeOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </section>

    <section v-if="props.notice" class="bookings-callout">
      <div class="bookings-callout-main">
        <div class="bookings-callout-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path
              d="M12 8.25v5.25m0 3h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
        <div class="bookings-callout-text">
          <p class="bookings-callout-message">{{ props.notice }}</p>
        </div>
      </div>

      <div v-if="props.isGuest" class="bookings-callout-actions">
        <button type="button" class="bookings-btn-primary" @click="props.goToProfile()">{{ t.signIn }}</button>
        <button type="button" class="bookings-btn-secondary" @click="props.goToVendor()">{{ t.browseVendors }}</button>
      </div>
    </section>

    <section v-if="props.isLoadingBookings || baseBookings.length" class="bookings-tools card">
      <div class="bookings-search">
        <div class="bookings-search-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none">
            <path
              d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
        <input v-model="searchQuery" type="search" :placeholder="t.searchPlaceholder" />
        <button v-if="searchQuery" type="button" class="bookings-clear" @click="searchQuery = ''">
          {{ t.clearSearch }}
        </button>
      </div>

      <div class="bookings-kpis">
        <div class="bookings-kpi">
          <small>{{ t.showing }}</small>
          <strong>{{ visibleBookings.length }}</strong>
        </div>
        <div class="bookings-kpi">
          <small>{{ t.totalValue }}</small>
          <strong>{{ formatMoney(visibleTotalValue) }}</strong>
        </div>
        <div v-if="nextBookingLabel" class="bookings-kpi">
          <small>{{ t.nextUp }}</small>
          <strong>{{ nextBookingLabel }}</strong>
        </div>
      </div>
    </section>

    <section>
      <div class="booking-list">
        <div v-if="props.isLoadingBookings" class="card empty-state">{{ t.loading }}</div>
        <div v-else-if="baseBookings.length === 0" class="bookings-empty card">
          <div class="bookings-empty-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path
                d="M7 4h10a2 2 0 0 1 2 2v14l-4-2-4 2-4-2-4 2V6a2 2 0 0 1 2-2Z"
                stroke="currentColor"
                stroke-width="2"
                stroke-linejoin="round"
              />
              <path d="M9 8h6M9 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
          </div>
          <div class="bookings-empty-body">
            <h3>{{ t.emptyTitle }}</h3>
            <p>{{ t.empty }}</p>
            <div class="bookings-empty-actions">
              <button
                v-if="props.isGuest && !props.notice"
                type="button"
                class="bookings-btn-primary"
                @click="props.goToProfile()"
              >
                {{ t.signIn }}
              </button>
              <button
                v-if="props.isGuest"
                type="button"
                class="bookings-btn-secondary"
                @click="props.goToVendor()"
              >
                {{ t.browseVendors }}
              </button>
              <button v-else type="button" class="bookings-btn-primary" @click="props.goToVendor()">
                {{ t.browseVendors }}
              </button>
              <button type="button" class="bookings-btn-secondary" @click="resetFilters">{{ t.resetFilters }}</button>
            </div>
          </div>
        </div>
        <div v-else-if="visibleBookings.length === 0" class="bookings-empty card">
          <div class="bookings-empty-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none">
              <path
                d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <div class="bookings-empty-body">
            <h3>{{ t.noResultsTitle }}</h3>
            <p>{{ t.noResults }}</p>
            <div class="bookings-empty-actions">
              <button type="button" class="bookings-btn-primary" @click="searchQuery = ''">{{ t.clearSearch }}</button>
              <button type="button" class="bookings-btn-secondary" @click="resetFilters">{{ t.resetFilters }}</button>
            </div>
          </div>
        </div>

        <article v-for="item in visibleBookings" :key="item.id" class="booking-card card">
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
                <small>{{ t.date }}</small>
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
                <button type="button" class="ghost" @click="props.bookingSecondaryAction(item)">{{ item.secondaryBtn }}</button>
                <button
                  type="button"
                  :class="item.statusClass === 'done' ? 'accent-soft' : 'accent'"
                  @click="props.bookingPrimaryAction(item)"
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
</template>
