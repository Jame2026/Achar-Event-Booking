<script setup>
import { computed } from 'vue'
import { useLanguageCopy } from '../../features/language'

const props = defineProps({
  service: {
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
  eventTypeMap: {
    type: Object,
    required: true,
  },
  serviceFeeRate: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['toggle-service', 'toggle-details', 'message', 'toggle-favorite', 'view-vendor'])

const copyByLanguage = {
  en: {
    other: 'Other',
    selected: 'Selected',
    addService: 'Add Service',
    hideDetails: 'Hide Details',
    viewDetails: 'View Details',
    viewVendor: 'View Vendor',
    messageVendor: 'Message Vendor',
    recommendedFor: 'Recommended for:',
    serviceFee: 'Service fee:',
    estimatedTotal: 'Estimated total:',
    preBooking: 'Pre-booking:',
    preBookingValue: 'Available after selecting this service.',
  },
  km: {
    other: '????????',
    selected: '????????',
    addService: '??????????????',
    hideDetails: '?????????????????',
    viewDetails: '????????????????',
    viewVendor: '?????????????????',
    messageVendor: '?????????????????????????',
    recommendedFor: '?????????????:',
    serviceFee: '????????:',
    estimatedTotal: '???????????????:',
    preBooking: '???????????:',
    preBookingValue: '???????????????????????????????????',
  },
  zh: {
    other: '其他',
    selected: '已选择',
    addService: '添加服务',
    hideDetails: '隐藏详情',
    viewDetails: '查看详情',
    viewVendor: '查看商家',
    messageVendor: '联系商家',
    recommendedFor: '推荐用于：',
    serviceFee: '服务费：',
    estimatedTotal: '预计总价：',
    preBooking: '预订前提示：',
    preBookingValue: '选择该服务后可进行预订。',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)

const serviceEventTypesLabel = computed(() =>
  props.service.eventTypes.map((type) => props.eventTypeMap[type] || uiText.value.other).join(', '),
)

const serviceFeePrice = computed(() =>
  Number((props.service.price * props.serviceFeeRate).toFixed(2)),
)

const serviceFeePercentLabel = computed(() => `${(props.serviceFeeRate * 100).toFixed(1)}%`)

const serviceEstimatedTotal = computed(() =>
  Number((props.service.price + serviceFeePrice.value).toFixed(2)),
)

function formatCurrency(value) {
  return `$${Number(value || 0).toLocaleString()}`
}
</script>

<template>
  <article
    class="addon-card"
    :class="{ selected: isSelected }"
    role="button"
    tabindex="0"
    @click="emit('toggle-details', service.id)"
    @keyup.enter="emit('toggle-details', service.id)"
  >
    <div class="addon-card-row">
      <strong>{{ service.name }}</strong>
      <div class="addon-card-meta">
        <span>{{ formatCurrency(service.price) }}</span>
        <button
          type="button"
          class="favorite-btn"
          :class="{ active: isFavorite }"
          @click.stop="emit('toggle-favorite', service.id)"
        >
          {{ isFavorite ? '♥' : '♡' }}
        </button>
      </div>
    </div>

    <p>{{ service.description }}</p>

    <div class="addon-card-actions service-actions">
      <button type="button" class="choice-indicator" @click.stop="emit('toggle-service', service.id)">
        {{ isSelected ? uiText.selected : uiText.addService }}
      </button>
      <button type="button" class="read-more-btn" @click.stop="emit('toggle-details', service.id)">
        {{ isExpanded ? uiText.hideDetails : uiText.viewDetails }}
      </button>
      <button type="button" class="message-vendor-btn view-vendor-btn" @click.stop="emit('view-vendor', service.id)">
        {{ uiText.viewVendor }}
      </button>
      <button type="button" class="message-vendor-btn" @click.stop="emit('message')">
        {{ uiText.messageVendor }}
      </button>
    </div>

    <div v-if="isExpanded" class="service-detail">
      <small><strong>{{ uiText.recommendedFor }}</strong> {{ serviceEventTypesLabel }}</small>
      <small><strong>{{ uiText.serviceFee }}</strong> {{ formatCurrency(serviceFeePrice) }} ({{ serviceFeePercentLabel }})</small>
      <small><strong>{{ uiText.estimatedTotal }}</strong> {{ formatCurrency(serviceEstimatedTotal) }}</small>
      <small><strong>{{ uiText.preBooking }}</strong> {{ uiText.preBookingValue }}</small>
    </div>
  </article>
</template>
