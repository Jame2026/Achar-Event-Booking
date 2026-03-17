<script setup>
import { computed, ref, watch } from 'vue'
import { useLanguageCopy } from '../../features/language'

const props = defineProps([
  'vendorProfile',
  'bindings',
  'isVendorAccount',
  'vendorServiceForm',
  'isSubmittingVendorService',
  'vendorServiceNotice',
  'stats',
  'reviews',
  'eventTypeOptions',
  'filteredPackages',
  'isLoadingEvents',
  'selectedQuantities',
  'bookingSubmittingEventId',
  'checkingAvailabilityEventId',
  'vendorLocationText',
  'vendorMapEmbedUrl',
  'loadBookings',
  'goToPackageCustomization',
  'goToMessages',
  'bookPackage',
  'goToAvailability',
  'getAvailabilityTone',
  'getAvailabilityLabel',
  'getAvailability',
  'submitVendorService',
])

const serviceMode = ref(
  props.vendorServiceForm?.value?.service_mode === 'package' ? 'package' : 'overall',
)

watch(
  () => props.vendorServiceForm?.value?.service_mode,
  (nextMode) => {
    if (!nextMode) return
    const normalized = nextMode === 'package' ? 'package' : 'overall'
    if (serviceMode.value !== normalized) {
      serviceMode.value = normalized
    }
  },
)

const vendorServicePackages = computed(() =>
  Array.isArray(props.vendorServiceForm?.value?.packages) ? props.vendorServiceForm.value.packages : [],
)

const showPackageBuilder = ref(false)
const newPackageFeature = ref('')
const packageDraft = ref({
  name: '',
  category: '',
  description: '',
  price: 0,
  discounted_price: 0,
  duration_value: '',
  duration_type: 'hours',
  features: [],
  image_url: '',
  image_file: null,
  is_active: true,
  is_featured: false,
})

function ensureVendorPackages() {
  if (!props.vendorServiceForm?.value) return null
  if (!Array.isArray(props.vendorServiceForm.value.packages)) {
    props.vendorServiceForm.value.packages = []
  }
  return props.vendorServiceForm.value.packages
}

function setServiceMode(nextMode) {
  serviceMode.value = nextMode
  if (props.vendorServiceForm?.value) {
    props.vendorServiceForm.value.service_mode = nextMode
  }
  if (nextMode === 'package') {
    ensureVendorPackages()
  }
}

function addVendorPackage() {
  openPackageBuilder()
}

function removeVendorPackage(index) {
  const packages = ensureVendorPackages()
  if (!packages) return
  packages.splice(index, 1)
}

function resetPackageDraft() {
  packageDraft.value = {
    name: '',
    category: '',
    description: '',
    price: 0,
    discounted_price: 0,
    duration_value: '',
    duration_type: 'hours',
    features: [],
    image_url: '',
    image_file: null,
    is_active: true,
    is_featured: false,
  }
  newPackageFeature.value = ''
}

function openPackageBuilder() {
  resetPackageDraft()
  showPackageBuilder.value = true
  if (serviceMode.value !== 'package') {
    setServiceMode('package')
  }
}

function closePackageBuilder() {
  showPackageBuilder.value = false
}

function addPackageFeature() {
  const value = newPackageFeature.value.trim()
  if (!value) return
  packageDraft.value.features.push(value)
  newPackageFeature.value = ''
}

function removePackageFeature(index) {
  packageDraft.value.features.splice(index, 1)
}

function handlePackageImageChange(event) {
  const [file] = Array.from(event?.target?.files || [])
  if (!file) {
    packageDraft.value.image_file = null
    return
  }

  packageDraft.value.image_file = file
  const reader = new FileReader()
  reader.onload = () => {
    packageDraft.value.image_url = typeof reader.result === 'string' ? reader.result : ''
  }
  reader.readAsDataURL(file)
}

function clearPackageImage() {
  packageDraft.value.image_file = null
  packageDraft.value.image_url = ''
}

function savePackageDraft() {
  const packages = ensureVendorPackages()
  if (!packages) return
  const payload = {
    name: String(packageDraft.value.name || '').trim(),
    price: Number(packageDraft.value.price || 0),
    details: String(packageDraft.value.description || '').trim(),
    description: String(packageDraft.value.description || '').trim(),
    category: String(packageDraft.value.category || '').trim(),
    discounted_price: Number(packageDraft.value.discounted_price || 0),
    duration_value: String(packageDraft.value.duration_value || '').trim(),
    duration_type: String(packageDraft.value.duration_type || '').trim(),
    features: Array.isArray(packageDraft.value.features) ? [...packageDraft.value.features] : [],
    image_url: String(packageDraft.value.image_url || '').trim(),
    is_active: Boolean(packageDraft.value.is_active),
    is_featured: Boolean(packageDraft.value.is_featured),
  }
  if (!payload.name) return
  packages.push(payload)
  closePackageBuilder()
}

function submitServiceForm() {
  if (props.vendorServiceForm?.value) {
    props.vendorServiceForm.value.service_mode = serviceMode.value
  }
  if (
    serviceMode.value === 'overall' &&
    props.vendorServiceForm?.value &&
    Array.isArray(props.vendorServiceForm.value.packages)
  ) {
    props.vendorServiceForm.value.packages = []
  }
  if (typeof props.submitVendorService === 'function') {
    props.submitVendorService()
  }
}

function handleVendorQrChange(event) {
  const [file] = Array.from(event?.target?.files || [])
  if (!props.vendorServiceForm?.value) return

  if (!file) {
    props.vendorServiceForm.value.qr_code_file = null
    return
  }

  props.vendorServiceForm.value.qr_code_file = file

  const reader = new FileReader()
  reader.onload = () => {
    props.vendorServiceForm.value.qr_code_url = typeof reader.result === 'string' ? reader.result : ''
  }
  reader.readAsDataURL(file)
}

function handleVendorQrUrlInput(event) {
  if (!props.vendorServiceForm?.value) return
  props.vendorServiceForm.value.qr_code_file = null
  props.vendorServiceForm.value.qr_code_url = event?.target?.value || ''
}

function clearVendorQrCode() {
  if (!props.vendorServiceForm?.value) return
  props.vendorServiceForm.value.qr_code_file = null
  props.vendorServiceForm.value.qr_code_url = ''
}

function handleVendorServiceImageChange(event) {
  const [file] = Array.from(event?.target?.files || [])
  if (!props.vendorServiceForm?.value) return

  if (!file) {
    props.vendorServiceForm.value.image_file = null
    return
  }

  props.vendorServiceForm.value.image_file = file

  const reader = new FileReader()
  reader.onload = () => {
    props.vendorServiceForm.value.image_url = typeof reader.result === 'string' ? reader.result : ''
  }
  reader.readAsDataURL(file)
}

function handleVendorServiceImageUrlInput(event) {
  if (!props.vendorServiceForm?.value) return
  props.vendorServiceForm.value.image_file = null
  props.vendorServiceForm.value.image_url = event?.target?.value || ''
}

function clearVendorServiceImage() {
  if (!props.vendorServiceForm?.value) return
  props.vendorServiceForm.value.image_file = null
  props.vendorServiceForm.value.image_url = ''
}

const copyByLanguage = {
  en: {
    breadcrumbs: 'Home > Vendors >',
    bannerAlt: 'Vendor banner',
    message: 'Message',
    share: 'Share',
    about: 'About',
    packagesServices: 'Packages & Services',
    reviews: 'Reviews',
    aboutTextA:
      'Luxe Bloom Florals is an award-winning boutique floral design studio specializing in creating breathtaking, immersive botanical experiences for weddings, corporate events, and high-profile cultural ceremonies.',
    aboutTextB:
      'We support multiple event types including wedding, monk ceremony, house blessing, company party, birthday, school event, and engagement.',
    serviceTitlePlaceholder: 'Service title',
    locationPlaceholder: 'Location',
    pricePlaceholder: 'Price',
    capacityPlaceholder: 'Capacity (0 = unlimited)',
    active: 'Active',
    descriptionPlaceholder: 'Short service description',
    savingService: 'Saving service...',
    addService: 'Add Service',
    fullNamePlaceholder: 'Your full name',
    emailPlaceholder: 'Your email',
    loadMyBookings: 'Load My Bookings',
    loadingPackages: 'Loading packages from API...',
    noPackages: 'No packages match your search and event type filter.',
    selectPackage: 'Select Package',
    messageVendor: 'Message Vendor',
    booking: 'Booking...',
    bookNow: 'Book Now',
    checking: 'Checking...',
    checkAvailability: 'Check Availability',
    recentReviews: 'Recent Reviews',
    contactDetails: 'Contact Details',
    phone: 'Phone',
    email: 'Email',
    website: 'Website',
    location: 'Location',
    seeAll: 'See all',
  },
  km: {
    breadcrumbs: 'ទំព័រដើម > អ្នកផ្គត់ផ្គង់ >',
    bannerAlt: 'ផ្ទាំងបង្ហាញអ្នកផ្គត់ផ្គង់',
    message: 'ផ្ញើសារ',
    share: 'ចែករំលែក',
    about: 'អំពី',
    packagesServices: 'កញ្ចប់ និងសេវាកម្ម',
    reviews: 'ការវាយតម្លៃ',
    aboutTextA:
      'Luxe Bloom Florals គឺជាស្ទូឌីយោរចនាផ្កាដ៏ល្បី ដែលមានជំនាញក្នុងការបង្កើតបរិយាកាសរុក្ខជាតិដ៏ស្រស់ស្អាតសម្រាប់ពិធីមង្គលការ ព្រឹត្តិការណ៍ក្រុមហ៊ុន និងពិធីសំខាន់ៗផ្សេងៗ។',
    aboutTextB:
      'យើងគាំទ្រព្រឹត្តិការណ៍ជាច្រើនប្រភេទ រួមមាន មង្គលការ ពិធីព្រះសង្ឃ ពិធីសូត្រមន្តផ្ទះ កម្មវិធីក្រុមហ៊ុន ខួបកំណើត កម្មវិធីសាលា និងពិធីភ្ជាប់ពាក្យ។',
    serviceTitlePlaceholder: 'ចំណងជើងសេវាកម្ម',
    locationPlaceholder: 'ទីតាំង',
    pricePlaceholder: 'តម្លៃ',
    capacityPlaceholder: 'ចំនួន (0 = គ្មានកំណត់)',
    active: 'សកម្ម',
    descriptionPlaceholder: 'ពិពណ៌នាសេវាកម្មខ្លីៗ',
    savingService: 'កំពុងរក្សាទុកសេវាកម្ម...',
    addService: 'បន្ថែមសេវាកម្ម',
    fullNamePlaceholder: 'ឈ្មោះពេញរបស់អ្នក',
    emailPlaceholder: 'អ៊ីមែលរបស់អ្នក',
    loadMyBookings: 'ផ្ទុកការកក់របស់ខ្ញុំ',
    loadingPackages: 'កំពុងផ្ទុកកញ្ចប់ពី API...',
    noPackages: 'មិនមានកញ្ចប់ដែលត្រូវនឹងការស្វែងរក និងតម្រងប្រភេទព្រឹត្តិការណ៍របស់អ្នកទេ។',
    selectPackage: 'ជ្រើសរើសកញ្ចប់',
    messageVendor: 'ផ្ញើសារទៅអ្នកផ្គត់ផ្គង់',
    booking: 'កំពុងកក់...',
    bookNow: 'កក់ឥឡូវ',
    checking: 'កំពុងពិនិត្យ...',
    checkAvailability: 'ពិនិត្យមើលពេលទំនេរ',
    recentReviews: 'ការវាយតម្លៃថ្មីៗ',
    contactDetails: 'ព័ត៌មានទំនាក់ទំនង',
    phone: 'ទូរស័ព្ទ',
    email: 'អ៊ីមែល',
    website: 'គេហទំព័រ',
    location: 'ទីតាំង',
    seeAll: 'មើលទាំងអស់',
  },
  zh: {
    breadcrumbs: '首页 > 商家 >',
    bannerAlt: '商家横幅',
    message: '联系',
    share: '分享',
    about: '关于',
    packagesServices: '套餐与服务',
    reviews: '评价',
    aboutTextA:
      'Luxe Bloom Florals 是一家屡获殊荣的精品花艺设计工作室，专注于为婚礼、企业活动和高端文化仪式打造沉浸式花艺体验。',
    aboutTextB:
      '我们支持多种活动类型，包括婚礼、僧侣仪式、乔迁祈福、公司活动、生日、校园活动和订婚仪式。',
    serviceTitlePlaceholder: '服务标题',
    locationPlaceholder: '地点',
    pricePlaceholder: '价格',
    capacityPlaceholder: '容量 (0 = 不限)',
    active: '启用',
    descriptionPlaceholder: '简短服务描述',
    savingService: '正在保存服务...',
    addService: '添加服务',
    fullNamePlaceholder: '您的姓名',
    emailPlaceholder: '您的邮箱',
    loadMyBookings: '加载我的预订',
    loadingPackages: '正在从 API 加载套餐...',
    noPackages: '没有符合搜索和活动类型筛选的套餐。',
    selectPackage: '选择套餐',
    messageVendor: '联系商家',
    booking: '预订中...',
    bookNow: '立即预订',
    checking: '检查中...',
    checkAvailability: '查看档期',
    recentReviews: '最近评价',
    contactDetails: '联系方式',
    phone: '电话',
    email: '邮箱',
    website: '网站',
    location: '位置',
    seeAll: '查看全部',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <main class="shell content">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }} {{ props.vendorProfile.name }}</div>

    <section class="hero">
      <img
        class="hero-bg"
        src="https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1600&q=80"
        :alt="uiText.bannerAlt"
      />
      <div class="hero-overlay"></div>
      <div class="hero-main">
        <div class="vendor-logo">LUXE<br />BLOOM</div>
        <div class="vendor-details">
          <h1>{{ props.vendorProfile.name }}</h1>
          <p>{{ props.vendorProfile.subtitle }}</p>
          <div class="rating">{{ props.vendorProfile.rating }}</div>
        </div>
      </div>
      <div class="hero-buttons">
        <button type="button" class="btn-light" @click="props.goToMessages(props.vendorProfile.name)">{{ uiText.message }}</button>
        <button type="button" class="btn-accent">{{ uiText.share }}</button>
      </div>
    </section>

    <nav class="section-tabs">
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'about' }" @click.prevent="props.bindings.activeVendorTab.value = 'about'">{{ uiText.about }}</a>
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'services' }" @click.prevent="props.bindings.activeVendorTab.value = 'services'">{{ uiText.packagesServices }}</a>
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'reviews' }" @click.prevent="props.bindings.activeVendorTab.value = 'reviews'">{{ uiText.reviews }}</a>
    </nav>

    <section class="layout" :class="{ 'layout-full': props.isVendorAccount && props.bindings.activeVendorTab.value === 'services' }">
      <div class="left">
        <article v-if="props.bindings.activeVendorTab.value === 'about'" class="card about">
          <h2>{{ uiText.about }} {{ props.vendorProfile.name }}</h2>
          <p>{{ uiText.aboutTextA }}</p>
          <p>{{ uiText.aboutTextB }}</p>

          <div class="stats">
            <div v-for="stat in props.stats" :key="stat.label" class="stat-box">
              <p>{{ stat.label }}</p>
              <strong>{{ stat.value }}</strong>
            </div>
          </div>
        </article>

        <article v-else-if="props.bindings.activeVendorTab.value === 'services'" class="card services">
          <h2>{{ uiText.packagesServices }}</h2>

          <form
            v-if="props.isVendorAccount"
            class="vendor-service-form"
            @submit.prevent="submitServiceForm"
          >
            <div class="vendor-form-grid">
              <div class="vendor-form-main">
                <section class="form-card">
                  <div class="form-card-head">
                    <span class="form-card-icon">i</span>
                    <h3>Service Information</h3>
                  </div>
                  <label class="form-field">
                    <span>Service Name</span>
                    <input
                      :value="props.vendorServiceForm.value.title"
                      type="text"
                      :placeholder="uiText.serviceTitlePlaceholder"
                      @input="props.vendorServiceForm.value.title = $event.target.value"
                    />
                  </label>
                  <div class="form-row">
                    <label class="form-field">
                      <span>Category</span>
                      <select
                        :value="props.vendorServiceForm.value.event_type"
                        @change="props.vendorServiceForm.value.event_type = $event.target.value"
                      >
                        <option
                          v-for="option in props.eventTypeOptions.filter((item) => item.value !== 'all')"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </option>
                      </select>
                    </label>
                    <label class="form-field">
                      <span>Start date and time</span>
                      <input
                        :value="props.vendorServiceForm.value.starts_at"
                        type="datetime-local"
                        @input="props.vendorServiceForm.value.starts_at = $event.target.value"
                      />
                    </label>
                  </div>
                  <div class="form-row">
                    <label class="form-field">
                      <span>End date and time</span>
                      <input
                        :value="props.vendorServiceForm.value.ends_at"
                        type="datetime-local"
                        @input="props.vendorServiceForm.value.ends_at = $event.target.value"
                      />
                    </label>
                    <label class="form-field">
                      <span>Location</span>
                      <input
                        :value="props.vendorServiceForm.value.location"
                        type="text"
                        :placeholder="uiText.locationPlaceholder"
                        @input="props.vendorServiceForm.value.location = $event.target.value"
                      />
                    </label>
                  </div>
                  <label class="form-field">
                    <span>Service Description</span>
                    <textarea
                      :value="props.vendorServiceForm.value.description"
                      :placeholder="uiText.descriptionPlaceholder"
                      @input="props.vendorServiceForm.value.description = $event.target.value"
                    ></textarea>
                  </label>
                </section>

                <section class="form-card">
                  <div class="form-card-head">
                    <span class="form-card-icon">%</span>
                    <h3>Pricing & Packages</h3>
                  </div>
                  <div class="pricing-toggle" role="tablist" aria-label="Pricing model">
                    <button
                      type="button"
                      class="toggle-chip"
                      :class="{ active: serviceMode === 'overall' }"
                      @click="setServiceMode('overall')"
                    >
                      Flat Fee
                    </button>
                    <button
                      type="button"
                      class="toggle-chip"
                      :class="{ active: serviceMode === 'package' }"
                      @click="setServiceMode('package')"
                    >
                      Package Tiers
                    </button>
                  </div>
                  <div class="form-row">
                    <label class="form-field">
                      <span>Starting Price ($)</span>
                      <input
                        :value="props.vendorServiceForm.value.price"
                        type="number"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        @input="props.vendorServiceForm.value.price = Number($event.target.value)"
                      />
                    </label>
                    <label class="form-field">
                      <span>Capacity</span>
                      <input
                        :value="props.vendorServiceForm.value.capacity"
                        type="number"
                        min="0"
                        :placeholder="uiText.capacityPlaceholder"
                        @input="props.vendorServiceForm.value.capacity = Number($event.target.value)"
                      />
                    </label>
                  </div>
                  <p class="form-helper">
                    Vendors who list clear pricing receive more booking inquiries.
                  </p>
                  <div v-if="serviceMode === 'package'" class="package-editor">
                    <div class="package-editor-head">
                      <p class="package-hint">Add package tiers so clients can choose the best fit.</p>
                      <button type="button" class="ghost-button" @click="addVendorPackage">
                        + Add package
                      </button>
                    </div>
                    <p v-if="!vendorServicePackages.length" class="package-empty">
                      No packages added yet.
                    </p>
                    <div
                      v-for="(pkg, index) in vendorServicePackages"
                      :key="`package-${index}`"
                      class="package-row"
                    >
                      <div class="package-row-head">
                        <strong>Package {{ index + 1 }}</strong>
                        <button type="button" class="text-button danger" @click="removeVendorPackage(index)">
                          Remove
                        </button>
                      </div>
                      <div class="package-row-grid">
                        <label class="form-field">
                          <span>Package name</span>
                          <input
                            :value="pkg?.name || ''"
                            type="text"
                            placeholder="Basic / Standard / Premium"
                            @input="pkg.name = $event.target.value"
                          />
                        </label>
                        <label class="form-field">
                          <span>Package price</span>
                          <input
                            :value="pkg?.price ?? 0"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="250"
                            @input="pkg.price = Number($event.target.value)"
                          />
                        </label>
                      </div>
                      <label class="form-field">
                        <span>What is included</span>
                        <textarea
                          :value="pkg?.details || ''"
                          placeholder="List what this package covers."
                          @input="pkg.details = $event.target.value"
                        ></textarea>
                      </label>
                    </div>
                  </div>
                </section>
              </div>

              <aside class="vendor-form-side">
                <section class="form-card media-card">
                  <div class="form-card-head">
                    <span class="form-card-icon">M</span>
                    <h3>Media</h3>
                  </div>
                  <label class="media-upload">
                    <input type="file" accept="image/*" @change="handleVendorServiceImageChange" />
                    <span>Click to upload cover photo</span>
                    <small>PNG, JPG (MAX. 5MB)</small>
                  </label>
                  <div v-if="props.vendorServiceForm.value.image_url" class="media-preview">
                    <img :src="props.vendorServiceForm.value.image_url" alt="Service preview" />
                    <button type="button" class="ghost-button" @click="clearVendorServiceImage">
                      Remove image
                    </button>
                  </div>
                  <label class="form-field">
                    <span>Or paste image link</span>
                    <input
                      :value="props.vendorServiceForm.value.image_url"
                      type="url"
                      placeholder="https://example.com/service-photo.jpg"
                      @input="handleVendorServiceImageUrlInput"
                    />
                  </label>
                  <label class="form-field">
                    <span>Payment QR code</span>
                    <input type="file" accept="image/*" @change="handleVendorQrChange" />
                  </label>
                  <label class="form-field">
                    <span>Or paste QR code link</span>
                    <input
                      :value="props.vendorServiceForm.value.qr_code_url"
                      type="url"
                      placeholder="https://example.com/payment-qr.png"
                      @input="handleVendorQrUrlInput"
                    />
                  </label>
                  <div v-if="props.vendorServiceForm.value.qr_code_url" class="media-preview">
                    <img :src="props.vendorServiceForm.value.qr_code_url" alt="Payment QR preview" />
                    <button type="button" class="ghost-button" @click="clearVendorQrCode">
                      Remove QR
                    </button>
                  </div>
                </section>

                <section class="form-card">
                  <div class="form-card-head">
                    <span class="form-card-icon">V</span>
                    <h3>Visibility</h3>
                  </div>
                  <div class="toggle-row">
                    <div>
                      <strong>Active listing</strong>
                      <p>Visible to users when enabled.</p>
                    </div>
                    <label class="switch">
                      <input
                        :checked="props.vendorServiceForm.value.is_active"
                        type="checkbox"
                        @change="props.vendorServiceForm.value.is_active = $event.target.checked"
                      />
                      <span class="slider"></span>
                    </label>
                  </div>
                </section>

                <section class="form-card help-card">
                  <h3>Need Help?</h3>
                  <p>Our vendor support team is here to help you optimize your listing.</p>
                  <button type="button" class="help-button">Chat with Support</button>
                </section>

                <div class="form-actions">
                  <button type="submit" class="primary-submit" :disabled="props.isSubmittingVendorService">
                    {{ props.isSubmittingVendorService ? uiText.savingService : uiText.addService }}
                  </button>
                </div>
              </aside>
            </div>
          </form>

          <p v-if="props.isVendorAccount && props.vendorServiceNotice" class="notice">
            {{ props.vendorServiceNotice }}
          </p>

          <div v-if="showPackageBuilder" class="package-modal">
            <div class="package-builder">
              <header class="package-builder-head">
                <div>
                  <h3>Add New Package</h3>
                  <p>Design a premium offering for your clients.</p>
                </div>
                <div class="package-builder-actions">
                  <button type="button" class="ghost-button" @click="closePackageBuilder">Discard</button>
                  <button type="button" class="primary-submit" @click="savePackageDraft">Publish Package</button>
                </div>
              </header>

              <div class="package-builder-grid">
                <div class="package-builder-main">
                  <section class="form-card">
                    <div class="form-card-head">
                      <span class="form-card-icon">i</span>
                      <h3>Package Basics</h3>
                    </div>
                    <label class="form-field">
                      <span>Package Name</span>
                      <input
                        :value="packageDraft.name"
                        type="text"
                        placeholder="e.g. Traditional Khmer Wedding Platinum"
                        @input="packageDraft.name = $event.target.value"
                      />
                    </label>
                    <label class="form-field">
                      <span>Category Selection</span>
                      <select
                        :value="packageDraft.category"
                        @change="packageDraft.category = $event.target.value"
                      >
                        <option value="" disabled>Select category</option>
                        <option
                          v-for="option in props.eventTypeOptions.filter((item) => item.value !== 'all')"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </option>
                      </select>
                    </label>
                    <label class="form-field">
                      <span>Detailed Description</span>
                      <textarea
                        :value="packageDraft.description"
                        placeholder="Describe the unique value of this package."
                        @input="packageDraft.description = $event.target.value"
                      ></textarea>
                    </label>
                  </section>

                  <section class="form-card">
                    <div class="form-card-head">
                      <span class="form-card-icon">%</span>
                      <h3>Pricing & Duration</h3>
                    </div>
                    <div class="form-row">
                      <label class="form-field">
                        <span>Standard Price ($)</span>
                        <input
                          :value="packageDraft.price"
                          type="number"
                          min="0"
                          step="0.01"
                          placeholder="0.00"
                          @input="packageDraft.price = Number($event.target.value)"
                        />
                      </label>
                      <label class="form-field">
                        <span>Discounted Price (Optional)</span>
                        <input
                          :value="packageDraft.discounted_price"
                          type="number"
                          min="0"
                          step="0.01"
                          placeholder="0.00"
                          @input="packageDraft.discounted_price = Number($event.target.value)"
                        />
                      </label>
                    </div>
                    <div class="form-row">
                      <label class="form-field">
                        <span>Duration Value</span>
                        <input
                          :value="packageDraft.duration_value"
                          type="text"
                          placeholder="e.g. 8"
                          @input="packageDraft.duration_value = $event.target.value"
                        />
                      </label>
                      <label class="form-field">
                        <span>Duration Type</span>
                        <select
                          :value="packageDraft.duration_type"
                          @change="packageDraft.duration_type = $event.target.value"
                        >
                          <option value="hours">Hours</option>
                          <option value="days">Days</option>
                          <option value="weeks">Weeks</option>
                        </select>
                      </label>
                    </div>
                  </section>

                  <section class="form-card">
                    <div class="package-builder-row">
                      <div class="form-card-head" style="margin-bottom: 0">
                        <span class="form-card-icon">+</span>
                        <h3>Features & Inclusions</h3>
                      </div>
                      <button type="button" class="ghost-button" @click="addPackageFeature">+ Add item</button>
                    </div>
                    <div class="package-feature-input">
                      <input
                        :value="newPackageFeature"
                        type="text"
                        placeholder="Add another inclusion..."
                        @input="newPackageFeature = $event.target.value"
                        @keyup.enter="addPackageFeature"
                      />
                    </div>
                    <div v-if="packageDraft.features.length" class="package-feature-list">
                      <div v-for="(feature, index) in packageDraft.features" :key="`feature-${index}`" class="package-feature-item">
                        <span>{{ feature }}</span>
                        <button type="button" class="text-button danger" @click="removePackageFeature(index)">Remove</button>
                      </div>
                    </div>
                  </section>
                </div>

                <aside class="package-builder-side">
                  <section class="form-card media-card">
                    <div class="form-card-head">
                      <span class="form-card-icon">M</span>
                      <h3>Package Media</h3>
                    </div>
                    <label class="media-upload">
                      <input type="file" accept="image/*" @change="handlePackageImageChange" />
                      <span>Upload Cover Image</span>
                      <small>PNG, JPG (MAX. 5MB)</small>
                    </label>
                    <div v-if="packageDraft.image_url" class="media-preview">
                      <img :src="packageDraft.image_url" alt="Package cover" />
                      <button type="button" class="ghost-button" @click="clearPackageImage">Remove image</button>
                    </div>
                  </section>

                  <section class="form-card">
                    <div class="form-card-head">
                      <span class="form-card-icon">V</span>
                      <h3>Availability</h3>
                    </div>
                    <div class="toggle-row">
                      <div>
                        <strong>Package Status</strong>
                        <p>Enable or disable this package.</p>
                      </div>
                      <label class="switch">
                        <input
                          :checked="packageDraft.is_active"
                          type="checkbox"
                          @change="packageDraft.is_active = $event.target.checked"
                        />
                        <span class="slider"></span>
                      </label>
                    </div>
                    <div class="toggle-row" style="margin-top: 12px">
                      <div>
                        <strong>Featured Package</strong>
                        <p>Show on top of your profile.</p>
                      </div>
                      <label class="switch">
                        <input
                          :checked="packageDraft.is_featured"
                          type="checkbox"
                          @change="packageDraft.is_featured = $event.target.checked"
                        />
                        <span class="slider"></span>
                      </label>
                    </div>
                  </section>

                  <section class="form-card help-card">
                    <h3>Vendor Tip</h3>
                    <p>
                      Packages with detailed descriptions and at least 3 high quality
                      inclusions convert better on our platform.
                    </p>
                  </section>
                </aside>
              </div>
            </div>
          </div>

          <div class="booking-inline-form">
            <input
              :value="props.bindings.customerName.value"
              type="text"
              :placeholder="uiText.fullNamePlaceholder"
              @input="props.bindings.customerName.value = $event.target.value"
            />
            <input
              :value="props.bindings.customerEmail.value"
              type="email"
              :placeholder="uiText.emailPlaceholder"
              @input="props.bindings.customerEmail.value = $event.target.value"
            />
            <select
              :value="props.bindings.selectedEventType.value"
              @change="props.bindings.selectedEventType.value = $event.target.value"
            >
              <option v-for="option in props.eventTypeOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
            <button type="button" class="btn-light" @click="props.loadBookings">{{ uiText.loadMyBookings }}</button>
          </div>

          <div v-if="props.isLoadingEvents" class="empty-state">{{ uiText.loadingPackages }}</div>
          <div v-else-if="props.filteredPackages.length === 0" class="empty-state">
            {{ uiText.noPackages }}
          </div>

          <div v-else class="service-cards">
            <div v-for="item in props.filteredPackages" :key="item.id" class="service-card">
              <img :src="item.image" :alt="item.title" />
              <div class="service-body">
                <div class="service-head">
                  <div>
                    <h3>{{ item.title }}</h3>
                    <small class="service-meta">{{ item.eventTypeLabel }}</small>
                  </div>
                  <strong class="service-price">{{ item.priceLabel }}</strong>
                </div>
                <p>{{ item.description }}</p>
                <ul>
                  <li>{{ item.location }}</li>
                  <li>{{ item.date }}</li>
                </ul>
                <div class="service-footer">
                  <div class="service-book-actions">
                    <button
                      type="button"
                      class="ghost"
                      @click="props.goToPackageCustomization(item.eventType, item.title)"
                    >
                      {{ uiText.selectPackage }}
                    </button>
                    <input
                      v-if="!item.isPreview"
                      :value="props.selectedQuantities[item.id]"
                      type="number"
                      min="1"
                      max="20"
                      @input="props.selectedQuantities[item.id] = Number($event.target.value)"
                    />
                    <button
                      type="button"
                      :disabled="!item.isPreview && props.bookingSubmittingEventId === item.id"
                      @click="item.isPreview ? props.goToMessages(props.vendorProfile.name) : props.bookPackage(item)"
                    >
                      {{
                        item.isPreview
                          ? uiText.messageVendor
                          : props.bookingSubmittingEventId === item.id
                            ? uiText.booking
                            : uiText.bookNow
                      }}
                    </button>
                    <button
                      type="button"
                      class="ghost"
                      :disabled="!item.isPreview && props.checkingAvailabilityEventId === item.id"
                      @click="props.goToAvailability(item)"
                    >
                      {{ !item.isPreview && props.checkingAvailabilityEventId === item.id ? uiText.checking : uiText.checkAvailability }}
                    </button>
                  </div>
                </div>
                <div v-if="!item.isPreview" class="availability-row">
                  <span class="availability-pill" :class="props.getAvailabilityTone(item)">
                    {{ props.getAvailabilityLabel(item) }}
                  </span>
                  <p v-if="props.getAvailability(item)">{{ props.getAvailability(item).message }}</p>
                </div>
              </div>
            </div>
          </div>
        </article>

        <article v-else-if="props.bindings.activeVendorTab.value === 'reviews'" class="card services">
          <h2>{{ uiText.recentReviews }}</h2>
          <div v-for="review in props.reviews" :key="review.name" class="mini-review">
            <strong>{{ review.name }}</strong>
            <p>{{ review.text }}</p>
          </div>
        </article>
      </div>

      <aside class="right">
        <article class="card sidebar-card">
          <h3>{{ uiText.contactDetails }}</h3>
          <p><strong>{{ uiText.phone }}</strong><br />+1 (555) 234-5678</p>
          <p><strong>{{ uiText.email }}</strong><br />hello@luxebloom.com</p>
          <p><strong>{{ uiText.website }}</strong><br />www.luxebloom.com</p>
        </article>

        <article class="card sidebar-card">
          <h3>{{ uiText.location }}</h3>
          <p>{{ props.vendorLocationText }}</p>
          <iframe class="map-frame" :src="props.vendorMapEmbedUrl" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </article>

        <article class="card sidebar-card">
          <div class="review-header">
            <h3>{{ uiText.recentReviews }}</h3>
            <a href="#" @click.prevent="props.bindings.activeVendorTab.value = 'reviews'">{{ uiText.seeAll }}</a>
          </div>
          <div v-for="review in props.reviews" :key="review.name" class="mini-review">
            <strong>{{ review.name }}</strong>
            <p>{{ review.text }}</p>
          </div>
        </article>
      </aside>
    </section>
  </main>
</template>

<style scoped>
.shell.content {
  width: 100%;
  max-width: none;
}

.vendor-service-form {
  margin-bottom: 18px;
  font-family: "Space Grotesk", "Segoe UI", system-ui, sans-serif;
}

.layout.layout-full {
  grid-template-columns: 1fr;
}

.layout.layout-full .right {
  display: none;
}

.layout.layout-full .services {
  display: block;
}

.service-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.vendor-form-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(280px, 0.75fr);
  gap: 32px;
  align-items: start;
}

.vendor-form-main {
  display: grid;
  gap: 18px;
}

.vendor-form-side {
  display: grid;
  gap: 20px;
  align-content: start;
}

.form-card {
  padding: 20px;
  border-radius: 20px;
  background: radial-gradient(circle at top left, rgba(44, 34, 22, 0.9), rgba(12, 12, 10, 0.98));
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow:
    0 24px 60px rgba(15, 23, 42, 0.22),
    inset 0 1px 0 rgba(255, 255, 255, 0.04);
  color: #f8fafc;
  position: relative;
  overflow: hidden;
}

.vendor-form-main > .form-card:first-child,
.vendor-form-side > .form-card:first-child {
  min-height: 360px;
}

.form-card-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.form-card-head h3 {
  font-size: 15px;
  margin: 0;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.form-card-icon {
  width: 26px;
  height: 26px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: rgba(251, 146, 60, 0.24);
  color: #fed7aa;
  font-weight: 800;
  font-size: 12px;
}

.form-field {
  display: grid;
  gap: 8px;
  margin-bottom: 14px;
}

.form-field span {
  font-size: 12px;
  color: rgba(226, 232, 240, 0.8);
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.form-field input,
.form-field select,
.form-field textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 16px;
  border: 1px solid rgba(148, 163, 184, 0.22);
  background: linear-gradient(180deg, #0b1220, #0f172a);
  color: #f1f5f9;
  font-size: 14px;
  transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}

.form-field input::placeholder,
.form-field textarea::placeholder {
  color: rgba(226, 232, 240, 0.55);
}

.form-field input:focus,
.form-field select:focus,
.form-field textarea:focus {
  outline: none;
  border-color: rgba(251, 146, 60, 0.6);
  box-shadow:
    0 0 0 3px rgba(251, 146, 60, 0.18),
    0 12px 28px rgba(15, 23, 42, 0.3);
  transform: translateY(-1px);
}

.form-field textarea {
  min-height: 120px;
  resize: vertical;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.pricing-toggle {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
  padding: 6px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.toggle-chip {
  border: 0;
  background: transparent;
  color: rgba(226, 232, 240, 0.8);
  font-size: 12px;
  font-weight: 700;
  padding: 8px 14px;
  border-radius: 999px;
  cursor: pointer;
}

.toggle-chip.active {
  background: linear-gradient(135deg, #fb923c, #f97316);
  color: #111827;
  box-shadow: 0 8px 18px rgba(249, 115, 22, 0.3);
}

.form-helper {
  margin: 0 0 12px;
  font-size: 12px;
  color: rgba(226, 232, 240, 0.7);
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.package-editor {
  display: grid;
  gap: 12px;
  padding: 14px;
  border-radius: 16px;
  background: rgba(11, 18, 32, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.package-editor-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.package-hint {
  margin: 0;
  font-size: 12px;
  color: rgba(226, 232, 240, 0.7);
}

.package-empty {
  margin: 0;
  font-size: 12px;
  color: rgba(226, 232, 240, 0.6);
}

.package-row {
  padding: 12px;
  border-radius: 14px;
  background: rgba(3, 7, 18, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.package-row-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.package-row-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 160px;
  gap: 12px;
}

.text-button {
  border: none;
  background: transparent;
  color: rgba(248, 250, 252, 0.7);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.text-button.danger {
  color: #fca5a5;
}

.ghost-button {
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: transparent;
  color: rgba(248, 250, 252, 0.8);
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 12px;
  cursor: pointer;
}

.media-card .form-field input[type="file"] {
  padding: 10px;
  background: transparent;
}

.media-upload {
  position: relative;
  display: grid;
  place-items: center;
  text-align: center;
  padding: 26px 16px;
  border-radius: 18px;
  border: 1px dashed rgba(148, 163, 184, 0.35);
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.7), rgba(2, 6, 23, 0.8));
  margin-bottom: 14px;
  cursor: pointer;
}

.media-upload:hover {
  border-color: rgba(251, 146, 60, 0.5);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.3);
}

.media-upload input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}

.media-upload span {
  font-size: 13px;
  font-weight: 700;
  color: #f8fafc;
}

.media-upload small {
  font-size: 11px;
  color: rgba(226, 232, 240, 0.6);
}

.media-preview {
  display: grid;
  gap: 10px;
  margin-bottom: 14px;
}

.media-preview img {
  width: 100%;
  border-radius: 16px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(2, 6, 23, 0.8);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 12px;
  border-radius: 16px;
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.toggle-row strong {
  display: block;
  font-size: 14px;
}

.toggle-row p {
  margin: 4px 0 0;
  font-size: 12px;
  color: rgba(226, 232, 240, 0.6);
}

.switch {
  position: relative;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  inset: 0;
  background: rgba(148, 163, 184, 0.35);
  border-radius: 999px;
  transition: 0.2s;
}

.slider::before {
  content: "";
  position: absolute;
  height: 18px;
  width: 18px;
  left: 3px;
  top: 3px;
  background: #fff;
  border-radius: 50%;
  transition: 0.2s;
}

.switch input:checked + .slider {
  background: linear-gradient(135deg, #fb923c, #f97316);
}

.switch input:checked + .slider::before {
  transform: translateX(20px);
}

.help-card {
  background: linear-gradient(150deg, #f97316, #ea580c);
  color: #0f172a;
  box-shadow: 0 18px 40px rgba(234, 88, 12, 0.25);
}

.help-card h3 {
  margin: 0 0 6px;
  font-size: 16px;
}

.help-card p {
  margin: 0 0 12px;
  font-size: 12px;
}

.help-button {
  border: none;
  background: #fff7ed;
  color: #9a3412;
  padding: 10px 14px;
  border-radius: 999px;
  font-weight: 700;
  cursor: pointer;
}

.package-modal {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: grid;
  place-items: center;
  padding: 24px;
  z-index: 60;
}

.package-builder {
  width: min(1100px, 100%);
  max-height: 90vh;
  overflow: auto;
  border-radius: 24px;
  padding: 22px;
  background: radial-gradient(circle at top, rgba(28, 24, 16, 0.98), rgba(8, 8, 7, 0.98));
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.4);
  color: #f8fafc;
}

.package-builder-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  margin-bottom: 18px;
}

.package-builder-head h3 {
  margin: 0;
  font-size: 20px;
}

.package-builder-head p {
  margin: 6px 0 0;
  color: rgba(226, 232, 240, 0.7);
  font-size: 13px;
}

.package-builder-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.package-builder-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(260px, 0.7fr);
  gap: 20px;
}

.package-builder-main,
.package-builder-side {
  display: grid;
  gap: 18px;
}

.package-builder-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.package-feature-input input {
  width: 100%;
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: linear-gradient(180deg, #0b1220, #0f172a);
  color: #f1f5f9;
}

.package-feature-list {
  display: grid;
  gap: 10px;
  margin-top: 12px;
}

.package-feature-item {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 12px;
  background: rgba(2, 6, 23, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.16);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
}

.primary-submit {
  border: none;
  background: linear-gradient(135deg, #fb923c, #f97316);
  color: #0f172a;
  padding: 12px 18px;
  border-radius: 12px;
  font-weight: 800;
  cursor: pointer;
  width: 100%;
}

.primary-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 960px) {
  .vendor-form-grid {
    grid-template-columns: 1fr;
  }

  .package-builder-grid {
    grid-template-columns: 1fr;
  }

  .package-builder-head {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 720px) {
  .form-row,
  .package-row-grid {
    grid-template-columns: 1fr;
  }
}
</style>
