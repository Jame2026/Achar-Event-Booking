<script setup>
const props = defineProps([
  'bindings',
  'userProfileNotice',
  'isDetectingLocation',
  'userLatitude',
  'userLongitude',
  'userLocationMapUrl',
  'detectCurrentLocation',
  'resetUserProfile',
  'saveUserProfile',
])
</script>

<template>
  <main class="shell profile-page">
    <div class="breadcrumbs">Dashboard > My Profile</div>

    <section class="card services profile-form">
      <h2>Edit My Profile</h2>
      <p v-if="props.userProfileNotice" class="notice">{{ props.userProfileNotice }}</p>

      <div class="profile-grid">
        <label>
          Full Name
          <input
            type="text"
            placeholder="Your full name"
            :value="props.bindings.userProfileDraft.value.name"
            @input="props.bindings.userProfileDraft.value.name = $event.target.value"
          />
        </label>
        <label>
          Email
          <input
            type="email"
            placeholder="you@example.com"
            :value="props.bindings.userProfileDraft.value.email"
            @input="props.bindings.userProfileDraft.value.email = $event.target.value"
          />
        </label>
        <label>
          Phone
          <input
            type="text"
            placeholder="+1 (555) 123-4567"
            :value="props.bindings.userProfileDraft.value.phone"
            @input="props.bindings.userProfileDraft.value.phone = $event.target.value"
          />
        </label>
        <label>
          Location
          <input
            type="text"
            placeholder="City, State"
            :value="props.bindings.userProfileDraft.value.location"
            @input="props.bindings.userProfileDraft.value.location = $event.target.value"
          />
        </label>
      </div>

      <div class="profile-location-tools">
        <button type="button" class="btn-light" :disabled="props.isDetectingLocation" @click="props.detectCurrentLocation">
          {{ props.isDetectingLocation ? 'Detecting location...' : 'Use Current Location' }}
        </button>
        <p v-if="props.userLatitude !== null && props.userLongitude !== null" class="location-coords">
          Lat: {{ props.userLatitude.toFixed(6) }}, Lng: {{ props.userLongitude.toFixed(6) }}
        </p>
        <img v-if="props.userLocationMapUrl" :src="props.userLocationMapUrl" alt="Your saved location map" class="map" />
      </div>

      <div class="profile-actions">
        <button type="button" class="btn-light" @click="props.resetUserProfile">Reset</button>
        <button type="button" class="btn-accent" @click="props.saveUserProfile">Save Profile</button>
      </div>
    </section>
  </main>
</template>
