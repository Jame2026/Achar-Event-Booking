<script setup>
import PackageCard from '../customization/PackageCard.vue'
import ServiceCard from '../customization/ServiceCard.vue'
import { useLanguageCopy } from '../../features/language'

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
  'isPackageFavorite',
  'isServiceFavorite',
  'toggleFavoritePackage',
  'toggleFavoriteService',
])

const copyByLanguage = {
  en: {
    breadcrumbs: 'Home > Vendor Details > Service Package Customization',
    title: 'Service Package Customization',
    subtitle: 'Select your event type first, then choose a matching package and confirm booking.',
    searchPlaceholder: 'Search package or location...',
    packages: 'Packages',
    services: 'Services',
    estimate: 'Estimate',
    matchingPackages: 'Matching Packages',
    noPackages: 'No packages match this event type and search.',
    matchingServicesFor: 'Matching Services For',
    selectedEvent: 'Selected Event',
    noServices: 'No matching services for this event type.',
    bookingSummary: 'Booking Summary',
    selectedPackage: 'Selected Package',
    chooseOne: 'Choose one package from the list.',
    quantity: 'Quantity',
    packagePrice: 'Package Price',
    selectedServices: 'Selected Services',
    noAdditionalServices: 'No additional services selected.',
    servicesSubtotal: 'Services Subtotal',
    serviceFee: 'Service Fee (3.5%)',
    totalPrice: 'Total Price',
    confirming: 'Confirming...',
    confirmSelection: 'Confirm Selection',
  },
  km: {
    breadcrumbs: 'ទំព័រដើម > ព័ត៌មានអ្នកផ្គត់ផ្គង់ > កំណត់កញ្ចប់សេវាកម្ម',
    title: 'កំណត់កញ្ចប់សេវាកម្ម',
    subtitle: 'ជ្រើសរើសប្រភេទព្រឹត្តិការណ៍របស់អ្នកជាមុន បន្ទាប់មកជ្រើសរើសកញ្ចប់ដែលសមស្រប ហើយបញ្ជាក់ការកក់។',
    searchPlaceholder: 'ស្វែងរកកញ្ចប់ ឬទីតាំង...',
    packages: 'កញ្ចប់',
    services: 'សេវាកម្ម',
    estimate: 'ប៉ាន់ស្មាន',
    matchingPackages: 'កញ្ចប់ដែលត្រូវគ្នា',
    noPackages: 'មិនមានកញ្ចប់ដែលត្រូវនឹងប្រភេទព្រឹត្តិការណ៍ និងការស្វែងរកនេះទេ។',
    matchingServicesFor: 'សេវាកម្មដែលត្រូវគ្នាសម្រាប់',
    selectedEvent: 'ព្រឹត្តិការណ៍ដែលបានជ្រើស',
    noServices: 'មិនមានសេវាកម្មដែលត្រូវនឹងប្រភេទព្រឹត្តិការណ៍នេះទេ។',
    bookingSummary: 'សេចក្តីសង្ខេបការកក់',
    selectedPackage: 'កញ្ចប់ដែលបានជ្រើស',
    chooseOne: 'ជ្រើសរើសកញ្ចប់មួយពីបញ្ជី។',
    quantity: 'ចំនួន',
    packagePrice: 'តម្លៃកញ្ចប់',
    selectedServices: 'សេវាកម្មដែលបានជ្រើស',
    noAdditionalServices: 'មិនមានសេវាកម្មបន្ថែមដែលបានជ្រើសទេ។',
    servicesSubtotal: 'សរុបសេវាកម្ម',
    serviceFee: 'ថ្លៃសេវា (3.5%)',
    totalPrice: 'តម្លៃសរុប',
    confirming: 'កំពុងបញ្ជាក់...',
    confirmSelection: 'បញ្ជាក់ការជ្រើស',
  },
  zh: {
    breadcrumbs: '首页 > 商家详情 > 服务套餐定制',
    title: '服务套餐定制',
    subtitle: '先选择您的活动类型，再选择匹配套餐并确认预订。',
    searchPlaceholder: '搜索套餐或地点...',
    packages: '套餐',
    services: '服务',
    estimate: '预估',
    matchingPackages: '匹配套餐',
    noPackages: '没有符合该活动类型和搜索条件的套餐。',
    matchingServicesFor: '匹配服务适用于',
    selectedEvent: '已选活动',
    noServices: '该活动类型没有匹配的服务。',
    bookingSummary: '预订摘要',
    selectedPackage: '已选套餐',
    chooseOne: '请从列表中选择一个套餐。',
    quantity: '数量',
    packagePrice: '套餐价格',
    selectedServices: '已选服务',
    noAdditionalServices: '未选择额外服务。',
    servicesSubtotal: '服务小计',
    serviceFee: '服务费 (3.5%)',
    totalPrice: '总价',
    confirming: '确认中...',
    confirmSelection: '确认选择',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <main class="shell customization-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

    <section class="customization-head card">
      <div class="customization-head-main">
        <h1>{{ uiText.title }}</h1>
        <p>{{ uiText.subtitle }}</p>
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
            :placeholder="uiText.searchPlaceholder"
            :value="props.bindings.customizationSearch.value"
            @input="props.bindings.customizationSearch.value = $event.target.value"
          />
        </div>
      </div>
      <div class="customization-metrics">
        <div>
          <small>{{ uiText.packages }}</small>
          <strong>{{ props.filteredCustomizationPackages.length }}</strong>
        </div>
        <div>
          <small>{{ uiText.services }}</small>
          <strong>{{ props.selectedServicesCount }}</strong>
        </div>
        <div>
          <small>{{ uiText.estimate }}</small>
          <strong>${{ props.customizationTotal.toLocaleString() }}</strong>
        </div>
      </div>
    </section>

    <section class="customization-layout">
      <div class="customization-list">
        <article class="customization-section">
          <div class="customization-section-head">
            <span>📦</span>
            <h2>{{ uiText.matchingPackages }}</h2>
          </div>

          <div v-if="props.filteredCustomizationPackages.length === 0" class="card empty-state">
            {{ uiText.noPackages }}
          </div>

          <div class="addon-grid">
            <PackageCard
              v-for="item in props.filteredCustomizationPackages"
              :key="item.id"
              :item="item"
              :is-selected="props.bindings.selectedCustomizationPackageId.value === item.id"
              :is-expanded="props.isPackageExpanded(item.id)"
              :is-favorite="props.isPackageFavorite ? props.isPackageFavorite(item.id) : false"
              @select="props.selectCustomizationPackage"
              @toggle-details="props.togglePackageDetails"
              @check-availability="props.goToAvailability"
              @message="props.goToMessages({ vendorId: item.vendorId, vendorName: item.vendorName, vendorEmail: item.vendorEmail, serviceName: item.title, eventId: item.backingEventId })"
              @toggle-favorite="props.toggleFavoritePackage ? props.toggleFavoritePackage(item.id) : null"
            />
          </div>
        </article>

        <article class="customization-section">
          <div class="customization-section-head">
            <span>🧩</span>
            <h2>{{ uiText.matchingServicesFor }} {{ props.eventTypeMap[props.effectiveCustomizationEventType] || uiText.selectedEvent }}</h2>
          </div>

          <div v-if="props.filteredMatchingServices.length === 0" class="card empty-state">
            {{ uiText.noServices }}
          </div>

          <div class="addon-grid">
            <ServiceCard
              v-for="service in props.filteredMatchingServices"
              :key="service.id"
              :service="service"
              :is-selected="props.isServiceSelected(service.id)"
              :is-expanded="props.isServiceExpanded(service.id)"
              :is-favorite="props.isServiceFavorite ? props.isServiceFavorite(service.id) : false"
              :event-type-map="props.eventTypeMap"
              :service-fee-rate="props.serviceFeeRate"
              @toggle-service="props.toggleMatchingService"
              @toggle-details="props.toggleServiceDetails"
              @message="props.goToMessages({ vendorId: service.vendorId, vendorName: service.vendorName, vendorEmail: service.vendorEmail, serviceName: service.name, eventId: service.backingEventId })"
              @toggle-favorite="props.toggleFavoriteService ? props.toggleFavoriteService(service.id) : null"
            />
          </div>
        </article>
      </div>

      <aside class="card customization-summary">
        <h2>{{ uiText.bookingSummary }}</h2>
        <div class="summary-items">
          <h3>{{ uiText.selectedPackage }}</h3>
          <p v-if="!props.selectedCustomizationPackage">{{ uiText.chooseOne }}</p>
          <div v-else class="summary-package">
            <strong>{{ props.selectedCustomizationPackage.title }}</strong>
            <p>{{ props.selectedCustomizationPackage.eventTypeLabel }} | {{ props.selectedCustomizationPackage.location }}</p>
          </div>
        </div>

        <div class="summary-row">
          <span>{{ uiText.quantity }}</span>
          <input
            type="number"
            min="1"
            max="20"
            :value="props.bindings.customizationQuantity.value"
            @input="props.bindings.customizationQuantity.value = Number($event.target.value)"
          />
        </div>
        <div class="summary-row">
          <span>{{ uiText.packagePrice }}</span>
          <strong>${{ props.customizationPackageSubtotal.toLocaleString() }}</strong>
        </div>
        <div class="summary-items">
          <h3>{{ uiText.selectedServices }}</h3>
          <p v-if="props.selectedMatchingServices.length === 0">{{ uiText.noAdditionalServices }}</p>
          <div v-for="service in props.selectedMatchingServices" :key="service.id" class="summary-row">
            <span>{{ service.name }}</span>
            <strong>+${{ (Number(service.price || 0) * Math.max(1, Number(props.bindings.customizationQuantity.value || 1))).toLocaleString() }}</strong>
          </div>
        </div>
        <div class="summary-row">
          <span>{{ uiText.servicesSubtotal }}</span>
          <strong>${{ props.selectedServicesSubtotal.toLocaleString() }}</strong>
        </div>
        <div class="summary-row muted">
          <span>{{ uiText.serviceFee }}</span>
          <strong>${{ props.serviceFeeAmount.toLocaleString() }}</strong>
        </div>

        <div class="summary-total">
          <span>{{ uiText.totalPrice }}</span>
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
              ? uiText.confirming
              : uiText.confirmSelection
          }}
        </button>
      </aside>
    </section>
  </main>
</template>
