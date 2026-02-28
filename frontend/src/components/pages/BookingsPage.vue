<script setup>
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
</script>

<template>
  <main class="shell bookings-page">
    <div class="breadcrumbs">Home > My Bookings</div>

    <section class="bookings-head">
      <div>
        <h1>My Bookings</h1>
        <p>Manage your upcoming and past event services.</p>
      </div>
      <div class="booking-filter-wrap">
        <div class="booking-filter">
          <button
            v-for="tab in ['Upcoming', 'Completed', 'Drafts']"
            :key="tab"
            type="button"
            :class="{ active: props.bindings.bookingFilter.value === tab }"
            @click="props.bindings.bookingFilter.value = tab"
          >
            {{ tab }}
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

    <section class="bookings-layout">
      <aside class="bookings-sidebar">
        <a href="#" @click.prevent="props.goToDashboard">Dashboard</a>
        <a href="#" @click.prevent="props.goToVendor()">View Vendors</a>
        <a class="active" href="#">My Bookings</a>
        <a href="#" @click.prevent="props.goToMessages()">Messages</a>
        <a href="#">Saved Vendors</a>
        <hr />
        <a href="#" @click.prevent="props.goToProfile">Settings</a>
        <a class="danger" href="#">Sign Out</a>
      </aside>

      <div class="booking-list">
        <div v-if="props.isLoadingBookings" class="card empty-state">Loading bookings from API...</div>
        <div v-else-if="props.filteredBookings.length === 0" class="card empty-state">
          No bookings found for this filter. Use your email on vendor page and click "Load My Bookings".
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
                <small>Date</small>
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
