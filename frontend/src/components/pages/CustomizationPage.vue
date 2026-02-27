<script setup>
import PackageCard from '../customization/PackageCard.vue'
import ServiceCard from '../customization/ServiceCard.vue'

const props = defineProps([
  'eventTypeOptions',
  'eventTypeMap',
  'serviceFeeRate',
  'vendorProfile',
  'bindings',
  'filteredCustomizationPackages',
  'selectedServicesCount',
  'customizationTotal',
  'selectedCustomizationPackage',
  'selectedMatchingServices',
  'selectedServicesSubtotal',
  'customizationPackageSubtotal',
  'serviceFeeAmount',
  'bookingSubmittingEventId',
  'effectiveCustomizationEventType',
  'filteredMatchingServices',
  'isPackageExpanded',
  'togglePackageDetails',
  'goToAvailability',
  'goToMessages',
  'selectCustomizationPackage',
  'isServiceSelected',
  'isServiceExpanded',
  'toggleMatchingService',
  'toggleServiceDetails',
  'confirmCustomization',
])
</script>

<template>
  <main class="shell customization-page">
    <div class="breadcrumbs">Home > Vendor Details > Service Package Customization</div>

    <section class="customization-head card">
      <div class="customization-head-main">
        <h1>Service Package Customization</h1>
        <p>Select your event type first, then choose a matching package and confirm booking.</p>
        <div class="customization-filters">
          <select
            class="event-type-select"
            :value="props.bindings.customizationEventType.value"
            @change="props.bindings.customizationEventType.value = $event.target.value"
          >
            <option v-for="option in props.eventTypeOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <input
            class="customization-search"
            type="search"
            placeholder="Search package or location..."
            :value="props.bindings.customizationSearch.value"
            @input="props.bindings.customizationSearch.value = $event.target.value"
          />
        </div>
      </div>
      <div class="customization-metrics">
        <div>
          <small>Packages</small>
          <strong>{{ props.filteredCustomizationPackages.length }}</strong>
        </div>
        <div>
          <small>Services</small>
          <strong>{{ props.selectedServicesCount }}</strong>
        </div>
        <div>
          <small>Estimate</small>
          <strong>${{ props.customizationTotal.toLocaleString() }}</strong>
        </div>
      </div>
    </section>

    <section class="customization-layout">
      <div class="customization-list">
        <article class="customization-section">
          <div class="customization-section-head">
            <span>📦</span>
            <h2>Matching Packages</h2>
          </div>

          <div v-if="props.filteredCustomizationPackages.length === 0" class="card empty-state">
            No packages match this event type and search.
          </div>

          <div class="addon-grid">
            <PackageCard
              v-for="item in props.filteredCustomizationPackages"
              :key="item.id"
              :item="item"
              :is-selected="props.bindings.selectedCustomizationPackageId.value === item.id"
              :is-expanded="props.isPackageExpanded(item.id)"
              @select="props.selectCustomizationPackage"
              @toggle-details="props.togglePackageDetails"
              @check-availability="props.goToAvailability"
              @message="props.goToMessages(props.vendorProfile.name)"
            />
          </div>
        </article>

        <article class="customization-section">
          <div class="customization-section-head">
            <span>🧩</span>
            <h2>Matching Services For {{ props.eventTypeMap[props.effectiveCustomizationEventType] || 'Selected Event' }}</h2>
          </div>

          <div v-if="props.filteredMatchingServices.length === 0" class="card empty-state">
            No matching services for this event type.
          </div>

          <div class="addon-grid">
            <ServiceCard
              v-for="service in props.filteredMatchingServices"
              :key="service.id"
              :service="service"
              :is-selected="props.isServiceSelected(service.id)"
              :is-expanded="props.isServiceExpanded(service.id)"
              :event-type-map="props.eventTypeMap"
              :service-fee-rate="props.serviceFeeRate"
              @toggle-service="props.toggleMatchingService"
              @toggle-details="props.toggleServiceDetails"
              @message="props.goToMessages(props.vendorProfile.name)"
            />
          </div>
        </article>
      </div>

      <aside class="card customization-summary">
        <h2>Booking Summary</h2>
        <div class="summary-items">
          <h3>Selected Package</h3>
          <p v-if="!props.selectedCustomizationPackage">Choose one package from the list.</p>
          <div v-else class="summary-package">
            <strong>{{ props.selectedCustomizationPackage.title }}</strong>
            <p>{{ props.selectedCustomizationPackage.eventTypeLabel }} | {{ props.selectedCustomizationPackage.location }}</p>
          </div>
        </div>

        <div class="summary-row">
          <span>Quantity</span>
          <input
            type="number"
            min="1"
            max="20"
            :value="props.bindings.customizationQuantity.value"
            @input="props.bindings.customizationQuantity.value = Number($event.target.value)"
          />
        </div>
        <div class="summary-row">
          <span>Package Price</span>
          <strong>${{ props.customizationPackageSubtotal.toLocaleString() }}</strong>
        </div>
        <div class="summary-items">
          <h3>Selected Services</h3>
          <p v-if="props.selectedMatchingServices.length === 0">No additional services selected.</p>
          <div v-for="service in props.selectedMatchingServices" :key="service.id" class="summary-row">
            <span>{{ service.name }}</span>
            <strong>+${{ service.price.toLocaleString() }}</strong>
          </div>
        </div>
        <div class="summary-row">
          <span>Services Subtotal</span>
          <strong>${{ props.selectedServicesSubtotal.toLocaleString() }}</strong>
        </div>
        <div class="summary-row muted">
          <span>Service Fee (10%)</span>
          <strong>${{ props.serviceFeeAmount.toLocaleString() }}</strong>
        </div>

        <div class="summary-total">
          <span>Total Price</span>
          <strong>${{ props.customizationTotal.toLocaleString() }}</strong>
        </div>

        <button
          type="button"
          class="confirm-selection"
          :disabled="!props.selectedCustomizationPackage || props.bookingSubmittingEventId === (props.selectedCustomizationPackage && props.selectedCustomizationPackage.backingEventId)"
          @click="props.confirmCustomization"
        >
          {{
            props.bookingSubmittingEventId === (props.selectedCustomizationPackage && props.selectedCustomizationPackage.backingEventId)
              ? 'Confirming...'
              : 'Confirm Selection'
          }}
        </button>
      </aside>
    </section>
  </main>
</template>
