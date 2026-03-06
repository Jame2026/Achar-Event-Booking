<script setup>
const props = defineProps([
  'bindings',
  'userProfileNotice',
  'isDetectingLocation',
  'userLatitude',
  'userLongitude',
  'userLocationMapUrl',
  'userLocationMapEmbedUrl',
  'userLocationMapLinkUrl',
  'detectCurrentLocation',
  'resetUserProfile',
  'saveUserProfile',
  'logoutUser',
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
        <iframe
          v-if="props.userLocationMapEmbedUrl"
          class="map-frame"
          :src="props.userLocationMapEmbedUrl"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <a
          v-if="props.userLocationMapLinkUrl"
          class="map-open-link"
          :href="props.userLocationMapLinkUrl"
          target="_blank"
          rel="noopener noreferrer"
        >
          Open current location in map
        </a>
      </div>

      <div class="profile-actions">
        <button type="button" class="btn-logout" @click="props.logoutUser">Logout</button>
        <button type="button" class="btn-light" @click="props.resetUserProfile">Reset</button>
        <button type="button" class="btn-accent" @click="props.saveUserProfile">Save Profile</button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.btn-logout {
  border: 1px solid #fecaca;
  border-radius: 10px;
  background: #fef2f2;
  color: #b91c1c;
  font: inherit;
  font-weight: 700;
  padding: 0.6rem 1rem;
  cursor: pointer;
}

.btn-logout:hover {
  background: #fee2e2;
}

.map-open-link {
  display: inline-block;
  font-size: 0.92rem;
  font-weight: 600;
  color: #c25c13;
  text-decoration: none;
}

.map-open-link:hover {
  text-decoration: underline;
}

.map-frame {
  height: 280px;
}
</style>
