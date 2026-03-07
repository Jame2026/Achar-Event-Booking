<script setup>
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
</script>

<template>
  <main class="shell content">
    <div class="breadcrumbs">Home > Vendors > {{ props.vendorProfile.name }}</div>

    <section class="hero">
      <img
        class="hero-bg"
        src="https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1600&q=80"
        alt="Luxe Bloom Florals banner"
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
        <button type="button" class="btn-light" @click="props.goToMessages(props.vendorProfile.name)">Message</button>
        <button type="button" class="btn-accent">Share</button>
      </div>
    </section>

    <nav class="section-tabs">
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'about' }" @click.prevent="props.bindings.activeVendorTab.value = 'about'">About</a>
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'services' }" @click.prevent="props.bindings.activeVendorTab.value = 'services'">Packages & Services</a>
      <a href="#" :class="{ active: props.bindings.activeVendorTab.value === 'reviews' }" @click.prevent="props.bindings.activeVendorTab.value = 'reviews'">Reviews</a>
    </nav>

    <section class="layout">
      <div class="left">
        <article v-if="props.bindings.activeVendorTab.value === 'about'" class="card about">
          <h2>About {{ props.vendorProfile.name }}</h2>
          <p>
            Luxe Bloom Florals is an award-winning boutique floral design studio specializing in
            creating breathtaking, immersive botanical experiences for weddings, corporate events,
            and high-profile cultural ceremonies.
          </p>
          <p>
            We support multiple event types including wedding, monk ceremony, house blessing,
            company party, birthday, school event, and engagement.
          </p>

          <div class="stats">
            <div v-for="stat in props.stats" :key="stat.label" class="stat-box">
              <p>{{ stat.label }}</p>
              <strong>{{ stat.value }}</strong>
            </div>
          </div>
        </article>

        <article v-else-if="props.bindings.activeVendorTab.value === 'services'" class="card services">
          <h2>Packages & Services</h2>

          <form
            v-if="props.isVendorAccount"
            class="booking-inline-form"
            style="grid-template-columns: repeat(2, minmax(0, 1fr)); margin-bottom: 14px"
            @submit.prevent="props.submitVendorService"
          >
            <input
              :value="props.vendorServiceForm.value.title"
              type="text"
              placeholder="Service title"
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
              placeholder="Location"
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
              placeholder="Price"
              @input="props.vendorServiceForm.value.price = Number($event.target.value)"
            />
            <input
              :value="props.vendorServiceForm.value.capacity"
              type="number"
              min="0"
              placeholder="Capacity (0 = unlimited)"
              @input="props.vendorServiceForm.value.capacity = Number($event.target.value)"
            />
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px">
              <input
                :checked="props.vendorServiceForm.value.is_active"
                type="checkbox"
                @change="props.vendorServiceForm.value.is_active = $event.target.checked"
              />
              Active
            </label>
            <textarea
              :value="props.vendorServiceForm.value.description"
              placeholder="Short service description"
              style="grid-column: 1 / -1; min-height: 90px"
              @input="props.vendorServiceForm.value.description = $event.target.value"
            ></textarea>
            <button
              type="submit"
              class="btn-light"
              style="grid-column: 1 / -1"
              :disabled="props.isSubmittingVendorService"
            >
              {{ props.isSubmittingVendorService ? "Saving service..." : "Add Service" }}
            </button>
          </form>

          <p v-if="props.isVendorAccount && props.vendorServiceNotice" class="notice">
            {{ props.vendorServiceNotice }}
          </p>

          <div class="booking-inline-form">
            <input
              :value="props.bindings.customerName.value"
              type="text"
              placeholder="Your full name"
              @input="props.bindings.customerName.value = $event.target.value"
            />
            <input
              :value="props.bindings.customerEmail.value"
              type="email"
              placeholder="Your email"
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
            <button type="button" class="btn-light" @click="props.loadBookings">Load My Bookings</button>
          </div>

          <div v-if="props.isLoadingEvents" class="empty-state">Loading packages from API...</div>
          <div v-else-if="props.filteredPackages.length === 0" class="empty-state">
            No packages match your search and event type filter.
          </div>

          <div v-for="item in props.filteredPackages" :key="item.id" class="service-card">
            <img :src="item.image" :alt="item.title" />
            <div class="service-body">
              <div class="service-head">
                <h3>{{ item.title }}</h3>
                <span class="tag">{{ item.eventTypeLabel }}</span>
              </div>
              <p>{{ item.description }}</p>
              <ul>
                <li>{{ item.location }}</li>
                <li>{{ item.date }}</li>
              </ul>
              <div class="service-footer">
                <strong>{{ item.priceLabel }}</strong>
                <div class="service-book-actions">
                  <button
                    type="button"
                    class="ghost"
                    @click="props.goToPackageCustomization(item.eventType, item.title)"
                  >
                    Select Package
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
                        ? 'Message Vendor'
                        : props.bookingSubmittingEventId === item.id
                          ? 'Booking...'
                          : 'Book Now'
                    }}
                  </button>
                  <button
                    type="button"
                    class="ghost"
                    :disabled="!item.isPreview && props.checkingAvailabilityEventId === item.id"
                    @click="props.goToAvailability(item)"
                  >
                    {{ !item.isPreview && props.checkingAvailabilityEventId === item.id ? 'Checking...' : 'Check Availability' }}
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
          <h2>Recent Reviews</h2>
          <div v-for="review in props.reviews" :key="review.name" class="mini-review">
            <strong>{{ review.name }}</strong>
            <p>{{ review.text }}</p>
          </div>
        </article>
      </div>

      <aside class="right">
        <article class="card sidebar-card">
          <h3>Contact Details</h3>
          <p><strong>Phone</strong><br />+1 (555) 234-5678</p>
          <p><strong>Email</strong><br />hello@luxebloom.com</p>
          <p><strong>Website</strong><br />www.luxebloom.com</p>
        </article>

        <article class="card sidebar-card">
          <h3>Location</h3>
          <p>{{ props.vendorLocationText }}</p>
          <iframe class="map-frame" :src="props.vendorMapEmbedUrl" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </article>

        <article class="card sidebar-card">
          <div class="review-header">
            <h3>Recent Reviews</h3>
            <a href="#" @click.prevent="props.bindings.activeVendorTab.value = 'reviews'">See all</a>
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
