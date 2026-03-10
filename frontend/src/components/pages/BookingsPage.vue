<script setup>
import { computed } from 'vue'
import { useLanguageCopy } from '../../features/language'

const props = defineProps([
  'bindings',
  'eventTypeOptions',
  'notice',
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
    empty: 'No bookings found for this filter. Use your email on vendor page and click "Load My Bookings".',
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
const filterTabs = computed(() => [
  { value: 'Upcoming', label: uiText.value.upcoming },
  { value: 'Completed', label: uiText.value.completed },
  { value: 'Drafts', label: uiText.value.drafts },
])
</script>

<template>
  <main class="shell bookings-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

    <section class="bookings-head">
      <div>
        <h1>{{ uiText.title }}</h1>
        <p>{{ uiText.subtitle }}</p>
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

    <p v-if="props.notice" class="notice">{{ props.notice }}</p>

    <section>
      <div class="booking-list">
        <div v-if="props.isLoadingBookings" class="card empty-state">{{ uiText.loading }}</div>
        <div v-else-if="props.filteredBookings.length === 0" class="card empty-state">
          {{ uiText.empty }}
        </div>

        <article v-for="item in props.filteredBookings" :key="item.id" class="booking-card card">
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
                <small>{{ uiText.date }}</small>
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
