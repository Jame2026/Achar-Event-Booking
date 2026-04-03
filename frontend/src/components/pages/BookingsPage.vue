<script setup>
import { computed, ref, watch } from 'vue'
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
  'submitBookingRating',
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
    searchPlaceholder: 'Search bookings (vendor, service, status)...',
    clearSearch: 'Clear',
    showing: 'Showing',
    totalValue: 'Total value',
    nextUp: 'Next',
    noResultsTitle: 'No matches',
    noResults: 'Try clearing search or adjusting filters.',
    date: 'Date',
    chatVendor: 'Chat Vendor',
    rateService: 'Rate Service',
    editRating: 'Edit Rating',
    yourRating: 'Your Rating',
    notRatedYet: 'Not rated yet',
    selectRating: 'Select stars',
    reviewOptional: 'Review',
    reviewPlaceholder: 'Share a short review other customers can trust.',
    saveRating: 'Save Rating',
    savingRating: 'Saving...',
    cancelEdit: 'Cancel',
    ratingRequired: 'Please choose a rating from 1 to 5.',
  },
  km: {
    breadcrumbs: 'áž‘áŸ†áž–áŸážšážŠáž¾áž˜ > áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹ážáŸ’áž‰áž»áŸ†',
    title: 'áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹ážáŸ’áž‰áž»áŸ†',
    subtitle: 'áž‚áŸ’ážšáž”áŸ‹áž‚áŸ’ážšáž„ážŸáŸážœáž¶áž–áŸ’ážšáž¹ážáŸ’ážáž·áž€áž¶ážšážŽáŸáž“áž¶áž–áŸáž›ážáž¶áž„áž˜áž»áž áž“áž·áž„áž€áž“áŸ’áž›áž„áž˜áž€ážšáž”ážŸáŸ‹áž¢áŸ’áž“áž€áŸ”',
    upcoming: 'áž“áž¶áž–áŸáž›ážáž¶áž„áž˜áž»áž',
    completed: 'áž”áž¶áž“áž”áž‰áŸ’áž…áž”áŸ‹',
    drafts: 'áž–áŸ’ážšáž¶áž„',
    loading: 'áž€áŸ†áž–áž»áž„áž•áŸ’áž‘áž»áž€áž€áž¶ážšáž€áž€áŸ‹áž–áž¸ API...',
    empty: 'ážšáž€áž˜áž·áž“ážƒáž¾áž‰áž€áž¶ážšáž€áž€áŸ‹ážŸáž˜áŸ’ážšáž¶áž”áŸ‹ážáž˜áŸ’ážšáž„áž“áŸáŸ‡áž‘áŸáŸ” áž”áŸ’ážšáž¾áž¢áŸŠáž¸áž˜áŸ‚áž›ážšáž”ážŸáŸ‹áž¢áŸ’áž“áž€áž“áŸ…áž‘áŸ†áž–áŸážšáž¢áŸ’áž“áž€áž•áŸ’áž‚ážáŸ‹áž•áŸ’áž‚áž„áŸ‹ áž áž¾áž™áž…áž»áž… "áž•áŸ’áž‘áž»áž€áž€áž¶ážšáž€áž€áŸ‹ážšáž”ážŸáŸ‹ážáŸ’áž‰áž»áŸ†"áŸ”',
    date: 'áž€áž¶áž›áž”ážšáž·áž…áŸ’áž†áŸáž‘',
    chatVendor: 'áž‡áž‡áŸ‚áž€áž‡áž¶áž˜áž½áž™áž¢áŸ’áž“áž€áž•áŸ’áž‚ážáŸ‹áž•áŸ’áž‚áž„áŸ‹',
  },
  zh: {
    breadcrumbs: 'é¦–é¡µ > æˆ‘çš„é¢„è®¢',
    title: 'æˆ‘çš„é¢„è®¢',
    subtitle: 'ç®¡ç†æ‚¨å³å°†å¼€å§‹å’Œè¿‡åŽ»çš„æ´»åŠ¨æœåŠ¡ã€‚',
    upcoming: 'å³å°†å¼€å§‹',
    completed: 'å·²å®Œæˆ',
    drafts: 'è‰ç¨¿',
    loading: 'æ­£åœ¨ä»Ž API åŠ è½½é¢„è®¢...',
    empty: 'å½“å‰ç­›é€‰æ¡ä»¶ä¸‹æ²¡æœ‰é¢„è®¢ã€‚è¯·åœ¨å•†å®¶é¡µé¢ä½¿ç”¨æ‚¨çš„é‚®ç®±å¹¶ç‚¹å‡»â€œåŠ è½½æˆ‘çš„é¢„è®¢â€ã€‚',
    date: 'æ—¥æœŸ',
    chatVendor: 'è”ç³»å•†å®¶',
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
const ratingDrafts = ref({})
const ratingErrors = ref({})
const submittingRatingId = ref(null)
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
      item?.customerReview,
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

function normalizeRatingValue(value) {
  const numeric = Math.round(Number(value || 0))
  return numeric >= 1 && numeric <= 5 ? numeric : 0
}

function createRatingDraft(item, existing = {}) {
  const persistedRating = normalizeRatingValue(item?.customerRating)
  return {
    open: Boolean(existing?.open),
    rating: normalizeRatingValue(existing?.rating) || persistedRating || 5,
    review: existing?.open ? String(existing?.review || '') : String(item?.customerReview || ''),
  }
}

watch(
  baseBookings,
  (rows) => {
    const nextDrafts = {}
    const nextErrors = {}

    rows.forEach((item) => {
      const existing = ratingDrafts.value[item.id]
      nextDrafts[item.id] = createRatingDraft(item, existing)
      if (ratingErrors.value[item.id]) {
        nextErrors[item.id] = ratingErrors.value[item.id]
      }
    })

    ratingDrafts.value = nextDrafts
    ratingErrors.value = nextErrors
  },
  { immediate: true, deep: true },
)

function getRatingDraft(item) {
  return ratingDrafts.value[item.id] || createRatingDraft(item)
}

function canEditRating(item) {
  return Boolean(item?.canRate && typeof props.submitBookingRating === 'function')
}

function shouldShowRating(item) {
  return Boolean(item?.hasRating || canEditRating(item))
}

function isRatingEditorOpen(item) {
  return Boolean(getRatingDraft(item)?.open)
}

function openRatingEditor(item) {
  ratingErrors.value = {
    ...ratingErrors.value,
    [item.id]: '',
  }
  ratingDrafts.value = {
    ...ratingDrafts.value,
    [item.id]: {
      ...createRatingDraft(item, ratingDrafts.value[item.id]),
      open: true,
    },
  }
}

function closeRatingEditor(item) {
  ratingErrors.value = {
    ...ratingErrors.value,
    [item.id]: '',
  }
  ratingDrafts.value = {
    ...ratingDrafts.value,
    [item.id]: {
      ...createRatingDraft(item),
      open: false,
    },
  }
}

function setDraftRating(item, value) {
  ratingDrafts.value = {
    ...ratingDrafts.value,
    [item.id]: {
      ...getRatingDraft(item),
      rating: normalizeRatingValue(value) || 1,
    },
  }
}

async function saveRating(item) {
  if (!canEditRating(item)) return

  const draft = getRatingDraft(item)
  const rating = normalizeRatingValue(draft.rating)

  if (!rating) {
    ratingErrors.value = {
      ...ratingErrors.value,
      [item.id]: t.value.ratingRequired,
    }
    return
  }

  submittingRatingId.value = item.id
  ratingErrors.value = {
    ...ratingErrors.value,
    [item.id]: '',
  }

  try {
    await props.submitBookingRating(item, rating, draft.review)
    ratingDrafts.value = {
      ...ratingDrafts.value,
      [item.id]: {
        ...draft,
        open: false,
      },
    }
  } catch (error) {
    ratingErrors.value = {
      ...ratingErrors.value,
      [item.id]: error?.message || 'Could not save your rating.',
    }
  } finally {
    submittingRatingId.value = null
  }
}

function rateButtonLabel(item) {
  return item?.hasRating ? t.value.editRating : t.value.rateService
}

function customerRatingLabel(item) {
  const rating = normalizeRatingValue(item?.customerRating)
  return rating ? `${rating}/5` : t.value.notRatedYet
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

    <section v-if="props.isLoadingBookings || baseBookings.length">
      <div class="booking-list">
        <div v-if="props.isLoadingBookings" class="card empty-state">{{ t.loading }}</div>
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
                <button
                  v-if="item.primaryBtn !== 'Message Vendor'"
                  type="button"
                  class="ghost"
                  @click="props.goToMessages({ vendorId: item.vendorId, vendorName: item.vendor, vendorEmail: item.vendorEmail, serviceName: item.service, eventId: item.eventId })"
                >
                  {{ uiText.chatVendor }}
                </button>
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

            <div v-if="shouldShowRating(item)" class="booking-rating">
              <div class="booking-rating-summary">
                <div>
                  <small>{{ t.yourRating }}</small>
                  <strong>{{ customerRatingLabel(item) }}</strong>
                </div>
                <button
                  v-if="canEditRating(item)"
                  type="button"
                  class="ghost booking-rating-toggle"
                  @click="isRatingEditorOpen(item) ? closeRatingEditor(item) : openRatingEditor(item)"
                >
                  {{ isRatingEditorOpen(item) ? t.cancelEdit : rateButtonLabel(item) }}
                </button>
              </div>

              <p v-if="item.customerReview && !isRatingEditorOpen(item)" class="booking-rating-review">
                {{ item.customerReview }}
              </p>

              <div v-if="isRatingEditorOpen(item)" class="booking-rating-editor">
                <div class="booking-rating-stars">
                  <span>{{ t.selectRating }}</span>
                  <div>
                    <button
                      v-for="star in 5"
                      :key="`${item.id}-star-${star}`"
                      type="button"
                      class="booking-star-btn"
                      :class="{ active: getRatingDraft(item).rating >= star }"
                      @click="setDraftRating(item, star)"
                    >
                      {{ getRatingDraft(item).rating >= star ? '★' : '☆' }}
                    </button>
                  </div>
                </div>

                <label class="booking-rating-textarea">
                  <span>{{ t.reviewOptional }}</span>
                  <textarea
                    v-model="getRatingDraft(item).review"
                    rows="3"
                    :placeholder="t.reviewPlaceholder"
                  ></textarea>
                </label>

                <p v-if="ratingErrors[item.id]" class="booking-rating-error">{{ ratingErrors[item.id] }}</p>

                <div class="booking-rating-actions">
                  <button type="button" class="ghost" @click="closeRatingEditor(item)">{{ t.cancelEdit }}</button>
                  <button
                    type="button"
                    class="accent"
                    :disabled="submittingRatingId === item.id"
                    @click="saveRating(item)"
                  >
                    {{ submittingRatingId === item.id ? t.savingRating : t.saveRating }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>
  </main>
</template>
