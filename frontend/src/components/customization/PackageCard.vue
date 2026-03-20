<script setup>
import { useLanguageCopy } from '../../features/language'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  isSelected: {
    type: Boolean,
    default: false,
  },
  isExpanded: {
    type: Boolean,
    default: false,
  },
  isFavorite: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['select', 'toggle-details', 'check-availability', 'message', 'toggle-favorite'])

function formatCurrency(value) {
  return `$${Number(value || 0).toLocaleString()}`
}

const copyByLanguage = {
  en: {
    selected: 'Selected',
    selectPackage: 'Select Package',
    hideDetails: 'Hide Details',
    viewDetails: 'View Details',
    checkAvailability: 'Check Availability',
    messageVendor: 'Message Vendor',
    eventType: 'Event Type:',
    location: 'Location:',
    date: 'Date:',
    includedServices: 'Included Services',
    previewPackage: 'Preview package (no live vendor slot yet)',
  },
  km: {
    selected: 'បានជ្រើស',
    selectPackage: 'ជ្រើសរើសកញ្ចប់',
    hideDetails: 'លាក់ព័ត៌មានលម្អិត',
    viewDetails: 'មើលព័ត៌មានលម្អិត',
    checkAvailability: 'ពិនិត្យមើលពេលទំនេរ',
    messageVendor: 'ផ្ញើសារទៅអ្នកផ្គត់ផ្គង់',
    eventType: 'ប្រភេទព្រឹត្តិការណ៍:',
    location: 'ទីតាំង:',
    date: 'កាលបរិច្ឆេទ:',
    includedServices: 'សេវាកម្មដែលរួមបញ្ចូល',
    previewPackage: 'កញ្ចប់បង្ហាញជាមុន (មិនទាន់មានពេលពីអ្នកផ្គត់ផ្គង់ផ្ទាល់)',
  },
  zh: {
    selected: '已选择',
    selectPackage: '选择套餐',
    hideDetails: '隐藏详情',
    viewDetails: '查看详情',
    checkAvailability: '查看档期',
    messageVendor: '联系商家',
    eventType: '活动类型：',
    location: '地点：',
    date: '日期：',
    includedServices: '包含服务',
    previewPackage: '预览套餐（暂无实时商家档期）',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <article
    class="addon-card package-card"
    :class="{ selected: isSelected }"
    role="button"
    tabindex="0"
    @click="emit('toggle-details', item.id)"
    @keyup.enter="emit('toggle-details', item.id)"
  >
    <div class="addon-card-row">
      <strong>{{ item.title }}</strong>
      <div class="addon-card-meta">
        <span>{{ formatCurrency(item.price) }}</span>
        <button
          type="button"
          class="favorite-btn"
          :class="{ active: isFavorite }"
          @click.stop="emit('toggle-favorite', item.id)"
        >
          {{ isFavorite ? '♥' : '♡' }}
        </button>
      </div>
    </div>

    <p>{{ item.description }}</p>

    <div class="addon-card-actions package-actions">
      <button type="button" class="choice-indicator" @click.stop="emit('select', item.id)">
        {{ isSelected ? uiText.selected : uiText.selectPackage }}
      </button>
      <button type="button" class="read-more-btn" @click.stop="emit('toggle-details', item.id)">
        {{ isExpanded ? uiText.hideDetails : uiText.viewDetails }}
      </button>
      <button type="button" class="check-availability-btn" @click.stop="emit('check-availability', item)">
        {{ uiText.checkAvailability }}
      </button>
      <button type="button" class="message-vendor-btn" @click.stop="emit('message')">
        {{ uiText.messageVendor }}
      </button>
    </div>

    <div v-if="isExpanded" class="package-detail">
      <small><strong>{{ uiText.eventType }}</strong> {{ item.eventTypeLabel }}</small>
      <small><strong>{{ uiText.location }}</strong> {{ item.location }}</small>
      <small><strong>{{ uiText.date }}</strong> {{ item.date }}</small>

      <div class="package-service-list">
        <p>{{ uiText.includedServices }}</p>
        <ul>
          <li v-for="service in item.services" :key="`${item.id}-${service.name}`">
            <strong>{{ service.name }}:</strong> {{ service.detail }}
          </li>
        </ul>
      </div>

      <small v-if="!item.backingEventId">{{ uiText.previewPackage }}</small>
    </div>
  </article>
</template>

<style scoped>
.package-card {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.package-card .addon-card-image {
  display: none;
}

.package-card .addon-card-row {
  align-items: center;
}

.package-card .addon-card-meta {
  gap: 10px;
}

.package-card .addon-card-meta span {
  color: #d46613;
  font-weight: 800;
}

.package-card .addon-card-actions {
  flex-wrap: nowrap;
}

.package-card .addon-card-actions > * {
  flex: 1 1 0;
  min-width: 0;
}
</style>
