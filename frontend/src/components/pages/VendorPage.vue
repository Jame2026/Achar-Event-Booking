<script setup>
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

    <section class="layout">
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
            class="booking-inline-form"
            style="grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 14px"
            @submit.prevent="props.submitVendorService"
          >
            <input
              :value="props.vendorServiceForm.value.title"
              type="text"
              :placeholder="uiText.serviceTitlePlaceholder"
              @input="props.vendorServiceForm.value.title = $event.target.value"
            />
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
            <input
              :value="props.vendorServiceForm.value.location"
              type="text"
              :placeholder="uiText.locationPlaceholder"
              @input="props.vendorServiceForm.value.location = $event.target.value"
            />
            <input
              :value="props.vendorServiceForm.value.starts_at"
              type="datetime-local"
              @input="props.vendorServiceForm.value.starts_at = $event.target.value"
            />
            <input
              :value="props.vendorServiceForm.value.ends_at"
              type="datetime-local"
              @input="props.vendorServiceForm.value.ends_at = $event.target.value"
            />
            <input
              :value="props.vendorServiceForm.value.price"
              type="number"
              min="0"
              step="0.01"
              :placeholder="uiText.pricePlaceholder"
              @input="props.vendorServiceForm.value.price = Number($event.target.value)"
            />
            <input
              :value="props.vendorServiceForm.value.capacity"
              type="number"
              min="0"
              :placeholder="uiText.capacityPlaceholder"
              @input="props.vendorServiceForm.value.capacity = Number($event.target.value)"
            />
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px">
              <input
                :checked="props.vendorServiceForm.value.is_active"
                type="checkbox"
                @change="props.vendorServiceForm.value.is_active = $event.target.checked"
              />
              {{ uiText.active }}
            </label>
            <textarea
              :value="props.vendorServiceForm.value.description"
              :placeholder="uiText.descriptionPlaceholder"
              style="grid-column: 1 / -1; min-height: 90px"
              @input="props.vendorServiceForm.value.description = $event.target.value"
            ></textarea>
            <button
              type="submit"
              class="btn-light"
              style="grid-column: 1 / -1"
              :disabled="props.isSubmittingVendorService"
            >
              {{ props.isSubmittingVendorService ? uiText.savingService : uiText.addService }}
            </button>
          </form>

          <p v-if="props.isVendorAccount && props.vendorServiceNotice" class="notice">
            {{ props.vendorServiceNotice }}
          </p>

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
