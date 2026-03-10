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
  'isSavingProfile',
  'logoutUser',
])
</script>

<template>
  <main class="shell profile-page">
    <div class="breadcrumbs">Dashboard > My Profile</div>

    <section class="card services profile-form">
      <div class="profile-head">
        <h2>🧑‍💼 Edit My Profile</h2>
        <p>Manage your account details and profile photo shown across the platform.</p>
      </div>
      <p v-if="props.userProfileNotice" class="notice">{{ props.userProfileNotice }}</p>

      <div class="profile-identity-card">
        <div class="profile-avatar-main">
          <div class="profile-avatar-preview">
            <img
              v-if="props.bindings.userProfileDraft.value.profile_image_url"
              :src="props.bindings.userProfileDraft.value.profile_image_url"
              alt="Profile image"
            />
            <span v-else>
              {{ (props.bindings.userProfileDraft.value.name || 'U').trim().charAt(0).toUpperCase() || 'U' }}
            </span>
          </div>
          <div class="profile-avatar-copy">
            <strong>{{ props.bindings.userProfileDraft.value.name || 'Your Profile' }}</strong>
            <small>{{ props.bindings.userProfileDraft.value.email || 'No email yet' }}</small>
          </div>
        </div>
        <div class="profile-avatar-actions">
          <label class="btn-light profile-upload-btn">
            <span class="btn-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 16V4" />
                <path d="m7 9 5-5 5 5" />
                <path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
              </svg>
            </span>
            Upload Photo
            <input
              type="file"
              accept="image/*"
              @change="props.bindings.updateProfileImageFile($event.target.files?.[0] || null)"
            />
          </label>
          <button type="button" class="btn-light is-danger" @click="props.bindings.removeProfileImage">
            <span class="btn-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 6h18" />
                <path d="M8 6V4h8v2" />
                <path d="M19 6l-1 14H6L5 6" />
              </svg>
            </span>
            Remove Photo
          </button>
        </div>
      </div>

      <h3 class="profile-section-title">Account Information</h3>
      <div class="profile-grid">
        <label>
          👤 Full Name
          <input
            type="text"
            placeholder="Your full name"
            :value="props.bindings.userProfileDraft.value.name"
            @input="props.bindings.userProfileDraft.value.name = $event.target.value"
          />
        </label>
        <label>
          📧 Email
          <input
            type="email"
            placeholder="you@example.com"
            :value="props.bindings.userProfileDraft.value.email"
            @input="props.bindings.userProfileDraft.value.email = $event.target.value"
          />
        </label>
        <label>
          📱 Phone
          <input
            type="text"
            placeholder="+1 (555) 123-4567"
            :value="props.bindings.userProfileDraft.value.phone"
            @input="props.bindings.userProfileDraft.value.phone = $event.target.value"
          />
        </label>
        <label>
          📍 Location
          <input
            type="text"
            placeholder="City, State"
            :value="props.bindings.userProfileDraft.value.location"
            @input="props.bindings.userProfileDraft.value.location = $event.target.value"
          />
        </label>
      </div>

      <h3 class="profile-section-title">🗺️ Location</h3>
      <div class="profile-location-tools">
        <button type="button" class="btn-light" :disabled="props.isDetectingLocation" @click="props.detectCurrentLocation">
          {{ props.isDetectingLocation ? 'Detecting location...' : '📡 Use Current Location' }}
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
        <button type="button" class="btn-logout" @click="props.logoutUser">🚪 Logout</button>
        <button type="button" class="btn-light" @click="props.resetUserProfile">♻️ Reset</button>
        <button type="button" class="btn-accent" :disabled="props.isSavingProfile" @click="props.saveUserProfile">
          {{ props.isSavingProfile ? 'Saving...' : '💾 Save Profile' }}
        </button>
      </div>
    </section>
  </main>
</template>

<style scoped>
.profile-form {
  border: 1px solid #efd5bf;
  border-radius: 22px;
  background:
    radial-gradient(circle at 100% 0%, rgba(212, 102, 19, 0.1) 0%, rgba(212, 102, 19, 0) 42%),
    linear-gradient(180deg, #ffffff 0%, #fff8f2 100%);
  box-shadow: 0 20px 40px rgba(146, 64, 14, 0.08);
  padding: 1.5rem;
}

.profile-head {
  display: grid;
  gap: 0.45rem;
}

.profile-head p {
  margin: 0;
  color: #7d6650;
  font-size: 0.95rem;
}

.profile-form h2 {
  margin: 0;
  color: #1f2937;
  font-size: clamp(1.85rem, 3vw, 2.5rem);
  line-height: 1.1;
}

.notice {
  margin-top: 0.7rem;
  border: 1px solid #f4cfac;
  border-radius: 12px;
  background: #fff5ea;
  color: #9a3412;
  font-weight: 700;
  padding: 0.62rem 0.78rem;
}
.profile-identity-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;

  margin: 1.2rem 0 1.4rem;
  padding: 1.1rem 1.2rem;

  border: 1px solid #f1d5c4;
  border-radius: 18px;

  background:
    radial-gradient(circle at top right, rgba(234, 88, 12, 0.10), transparent 45%),
    linear-gradient(180deg, #ffffff 0%, #fff7f0 100%);

  box-shadow: 
    0 6px 16px rgba(0,0,0,0.05),
    0 12px 28px rgba(124, 45, 18, 0.08);

  transition: all 0.25s ease;
}

.profile-identity-card:hover {
  transform: translateY(-3px);
  border-color: #ea580c;

  box-shadow: 
    0 10px 22px rgba(0,0,0,0.08),
    0 18px 36px rgba(234, 88, 12, 0.15);
}
.profile-avatar-main {
  display: flex;
  align-items: center;
  gap: 1.1rem;
  min-width: 0;
}

.profile-avatar-preview {
  width: 94px;
  height: 94px;
  border-radius: 50%;
  border: 4px solid #f3cdb3;

  background: linear-gradient(135deg, #fff7f1, #ffe8d9);
  color: #9a3412;

  font-size: 1.9rem;
  font-weight: 800;

  display: inline-flex;
  align-items: center;
  justify-content: center;

  overflow: hidden;
  flex-shrink: 0;

  box-shadow: 0 6px 14px rgba(0,0,0,0.08);
  transition: all 0.25s ease;
}

.profile-avatar-preview:hover {
  transform: scale(1.05);
  border-color: #ea580c;
  box-shadow: 0 10px 20px rgba(234, 88, 12, 0.2);
}

.profile-avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-avatar-copy {
  display: grid;
  gap: 0.3rem;
  min-width: 0;
}

.profile-avatar-copy strong {
  color: #1e293b;
  font-size: clamp(1.45rem, 2.2vw, 2.1rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.01em;
}

.profile-avatar-copy small {
  color: #7a5a3d;
  font-size: clamp(0.92rem, 1.7vw, 1.2rem);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-avatar-actions {
  display: flex;
  gap: 0.65rem;
  flex-wrap: wrap;
  align-self: flex-end;
}

.profile-section-title {
  margin: 1.2rem 0 0.6rem;
  color: #9a3412;
  font-size: 0.95rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;

  display: flex;
  align-items: center;
  gap: 8px;
}

.profile-section-title::before {
  content: "";
  width: 4px;
  height: 14px;
  background: #ea580c;
  border-radius: 4px;
}
.profile-grid {
  margin-top: 0.3rem;
}

.profile-grid label {
  color: #6b4a2b;
  font-weight: 600;
  font-size: 0.9rem;
  letter-spacing: 0.02em;

  /* display: block; */
  margin-bottom: 4px;
}

.profile-grid input {
  border: 1px solid #edd7c4;
  border-radius: 12px;
  background: #ffffff;
  color: #1f2937;
  padding: 0.7rem 0.8rem;
}

.profile-grid input:focus {
  outline: none;
  border-color: #e5a66f;
  box-shadow: 0 0 0 3px rgba(212, 102, 19, 0.14);
}

.profile-location-tools {
  margin-top: 0.2rem;
  border: 1px solid #f1decc;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.72);
  padding: 0.9rem;
}

.profile-upload-btn input {
  display: none;
}

.btn-light {
  border: 1px solid #f0ceaf;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #fff2e4 100%);
  color: #9a3412;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 0.65rem 1rem;
  
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;

  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.btn-light:hover {
  border-color: #e5b88e;
  background: linear-gradient(180deg, #ffffff 0%, #ffe6cc 100%);
  box-shadow: 0 4px 14px rgba(234, 88, 12, 0.15);
  transform: translateY(-2px);
}

.btn-accent {
  background: linear-gradient(135deg, #ff8c1a 0%, #ea580c 100%);
  border: 1px solid #ee7a24;
  border-radius: 10px;
  color: #fff;
  font-weight: 800;
  padding: 0.62rem 1.02rem;
  box-shadow: 0 14px 26px rgba(234, 88, 12, 0.25);
}

.btn-accent:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(234, 88, 12, 0.3);
}

.btn-accent:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-icon {
  width: 16px;
  height: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-icon svg {
  width: 16px;
  height: 16px;
}
/* Danger / Error Button */
.is-danger {
  border: 1px solid #f4c7c7;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #fff4f4 100%);
  color: #b42318;
  font-weight: 600;
  padding: 0.6rem 1rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.is-danger:hover {
  border-color: #eca3a3;
  background: linear-gradient(180deg, #fff 0%, #ffe7e7 100%);
  box-shadow: 0 4px 12px rgba(180, 35, 24, 0.15);
  transform: translateY(-2px);
}

/* Logout Button */
.btn-logout {
  border: 1px solid #fecaca;
  border-radius: 12px;
  background: #fef2f2;
  color: #b91c1c;
  font: inherit;
  font-weight: 700;
  padding: 0.65rem 1.1rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.btn-logout:hover {
  background: #fee2e2;
  box-shadow: 0 4px 12px rgba(185, 28, 28, 0.15);
  transform: translateY(-2px);
}

/* Map Link */
.map-open-link {
  display: inline-block;
  font-size: 0.93rem;
  font-weight: 600;
  color: #c25c13;
  text-decoration: none;
  transition: color 0.2s ease;
}

.map-open-link:hover {
  text-decoration: underline;
  color: #a3470f;
}

/* Map Frame */
.map-frame {
  height: 280px;
  border: 1px solid #f0d8c1;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(124, 72, 18, 0.08);
  transition: box-shadow 0.25s ease;
}

.map-frame:hover {
  box-shadow: 0 6px 20px rgba(124, 72, 18, 0.15);
}

@media (max-width: 860px) {
  .profile-form {
    padding: 1rem;
  }

  .profile-identity-card {
    flex-direction: column;
    align-items: flex-start;
    border-radius: 18px;
    padding: 1rem;
  }

  .profile-avatar-preview {
    width: 76px;
    height: 76px;
    border-width: 3px;
    font-size: 1.4rem;
  }

  .profile-avatar-copy strong {
    font-size: 1.8rem;
  }

  .profile-avatar-copy small {
    font-size: 1rem;
    white-space: normal;
  }

  .profile-actions {
    flex-wrap: wrap;
    justify-content: flex-start;
  }
}
</style>
