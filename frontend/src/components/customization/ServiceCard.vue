<script setup>
import { computed } from 'vue'

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

const emit = defineEmits(['toggle-service', 'toggle-details', 'message', 'toggle-favorite'])

const serviceEventTypesLabel = computed(() =>
  props.service.eventTypes.map((type) => props.eventTypeMap[type] || 'Other').join(', '),
)

const serviceFeePrice = computed(() =>
  Number((props.service.price * props.serviceFeeRate).toFixed(2)),
)

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
        {{ isSelected ? 'Selected' : 'Add Service' }}
      </button>
      <button type="button" class="read-more-btn" @click.stop="emit('toggle-details', service.id)">
        {{ isExpanded ? 'Hide Details' : 'View Details' }}
      </button>
      <button type="button" class="message-vendor-btn" @click.stop="emit('message')">
        Message Vendor
      </button>
    </div>

    <div v-if="isExpanded" class="service-detail">
      <small><strong>Recommended for:</strong> {{ serviceEventTypesLabel }}</small>
      <small><strong>Service fee:</strong> {{ formatCurrency(serviceFeePrice) }} (10%)</small>
      <small><strong>Estimated total:</strong> {{ formatCurrency(serviceEstimatedTotal) }}</small>
      <small><strong>Pre-booking:</strong> Available after selecting this service.</small>
    </div>
  </article>
</template>
