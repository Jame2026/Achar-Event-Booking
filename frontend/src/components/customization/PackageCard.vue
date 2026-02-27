<script setup>
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
    <img class="addon-card-image" :src="item.image" :alt="`${item.title} package`" loading="lazy" />

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
        {{ isSelected ? 'Selected' : 'Select Package' }}
      </button>
      <button type="button" class="read-more-btn" @click.stop="emit('toggle-details', item.id)">
        {{ isExpanded ? 'Hide Details' : 'View Details' }}
      </button>
      <button type="button" class="check-availability-btn" @click.stop="emit('check-availability', item)">
        Check Availability
      </button>
      <button type="button" class="message-vendor-btn" @click.stop="emit('message')">
        Message Vendor
      </button>
    </div>

    <div v-if="isExpanded" class="package-detail">
      <small><strong>Event Type:</strong> {{ item.eventTypeLabel }}</small>
      <small><strong>Location:</strong> {{ item.location }}</small>
      <small><strong>Date:</strong> {{ item.date }}</small>

      <div class="package-service-list">
        <p>Included Services</p>
        <ul>
          <li v-for="service in item.services" :key="`${item.id}-${service.name}`">
            <strong>{{ service.name }}:</strong> {{ service.detail }}
          </li>
        </ul>
      </div>

      <small v-if="!item.backingEventId">Preview package (no live vendor slot yet)</small>
    </div>
  </article>
</template>
