<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useLanguageCopy } from '../../features/language'

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

const showImagePreview = ref(false)
const profileImageUrl = computed(() => props.bindings.userProfileDraft.value.profile_image_url || '')
const isAvatarClickable = computed(() => Boolean(profileImageUrl.value))

const openImagePreview = () => {
  if (profileImageUrl.value) {
    showImagePreview.value = true
  }
}

const closeImagePreview = () => {
  showImagePreview.value = false
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && showImagePreview.value) {
    closeImagePreview()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEscape)
})

const copyByLanguage = {
  en: {
    breadcrumbs: 'Dashboard > My Profile',
    title: 'Edit My Profile',
    fullName: 'Full Name',
    fullNamePlaceholder: 'Your full name',
    email: 'Email',
    emailPlaceholder: 'you@example.com',
    phone: 'Phone',
    location: 'Location',
    locationPlaceholder: 'City, State',
    detectingLocation: 'Detecting location...',
    useCurrentLocation: 'Use Current Location',
    openInMap: 'Open current location in map',
    logout: 'Logout',
    reset: 'Reset',
    saveProfile: 'Save Profile',
  },
  km: {
    breadcrumbs: 'ក្ដារត្រួតពិនិត្យ > ព័ត៌មានផ្ទាល់ខ្លួន',
    title: 'កែព័ត៌មានផ្ទាល់ខ្លួន',
    fullName: 'ឈ្មោះពេញ',
    fullNamePlaceholder: 'ឈ្មោះពេញរបស់អ្នក',
    email: 'អ៊ីមែល',
    emailPlaceholder: 'you@example.com',
    phone: 'ទូរស័ព្ទ',
    location: 'ទីតាំង',
    locationPlaceholder: 'ទីក្រុង, ខេត្ត',
    detectingLocation: 'កំពុងរកទីតាំង...',
    useCurrentLocation: 'ប្រើទីតាំងបច្ចុប្បន្ន',
    openInMap: 'បើកទីតាំងបច្ចុប្បន្នលើផែនទី',
    logout: 'ចេញពីប្រព័ន្ធ',
    reset: 'កំណត់ឡើងវិញ',
    saveProfile: 'រក្សាទុកព័ត៌មាន',
  },
  zh: {
    breadcrumbs: '仪表盘 > 我的资料',
    title: '编辑我的资料',
    fullName: '姓名',
    fullNamePlaceholder: '您的姓名',
    email: '邮箱',
    emailPlaceholder: 'you@example.com',
    phone: '电话',
    location: '位置',
    locationPlaceholder: '城市, 省份',
    detectingLocation: '正在定位...',
    useCurrentLocation: '使用当前位置',
    openInMap: '在地图中打开当前位置',
    logout: '退出登录',
    reset: '重置',
    saveProfile: '保存资料',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <main class="shell profile-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

    <section class="card profile-hero">
      <div class="hero-copy">
        <p class="eyebrow">Profile · Secure</p>
        <h1>{{ uiText.title }}</h1>
        <p class="hero-sub">
          Keep your booking profile crisp: photo, contact, and location in one place.
        </p>
        <div class="hero-tags">
          <span>Realtime sync</span>
          <span>Location aware</span>
          <span>Private by default</span>
        </div>
        <div class="hero-badges">
          <span class="pill success">Verified email</span>
          <span class="pill neutral">2FA recommended</span>
        </div>
        <p v-if="props.userProfileNotice" class="notice">{{ props.userProfileNotice }}</p>
      </div>
    </section>

    <div class="profile-layout">
      <section class="card profile-panel">
        <div class="profile-identity-card">
          <div class="profile-avatar-main">
            <div class="profile-avatar-preview" :class="{ 'clickable': isAvatarClickable }" @click="openImagePreview" role="button" tabindex="0" @keydown.enter="openImagePreview" @keydown.space.prevent="openImagePreview" :aria-label="isAvatarClickable ? 'Click to view full size' : 'No profile image'">
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
              <div class="profile-progress">
                <span>Profile completeness</span>
                <div class="progress-track">
                  <span class="progress-bar" style="width: 82%"></span>
                </div>
              </div>
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

        <div class="section-head">
          <div>
            <p class="eyebrow">Account</p>
            <h3 class="profile-section-title">{{ uiText.fullName }}</h3>
          </div>
          <button type="button" class="btn-ghost subtle" @click="props.resetUserProfile">{{ uiText.reset }}</button>
        </div>

        <div class="profile-grid">
          <label>
            {{ uiText.fullName }}
            <input
              type="text"
              :placeholder="uiText.fullNamePlaceholder"
              :value="props.bindings.userProfileDraft.value.name"
              @input="props.bindings.userProfileDraft.value.name = $event.target.value"
            />
          </label>
          <label>
            {{ uiText.email }}
            <input
              type="email"
              :placeholder="uiText.emailPlaceholder"
              :value="props.bindings.userProfileDraft.value.email"
              @input="props.bindings.userProfileDraft.value.email = $event.target.value"
            />
          </label>
          <label>
            {{ uiText.phone }}
            <input
              type="text"
              placeholder="+1 (555) 123-4567"
              :value="props.bindings.userProfileDraft.value.phone"
              @input="props.bindings.userProfileDraft.value.phone = $event.target.value"
            />
          </label>
          <label>
            {{ uiText.location }}
            <input
              type="text"
              :placeholder="uiText.locationPlaceholder"
              :value="props.bindings.userProfileDraft.value.location"
              @input="props.bindings.userProfileDraft.value.location = $event.target.value"
            />
          </label>
        </div>
        <p class="helper-note">Tip: use your legal name and a reachable phone so confirmations never miss you.</p>

        <div class="profile-actions">
          <button type="button" class="btn-logout" @click="props.logoutUser">{{ uiText.logout }}</button>
          <button
            type="button"
            class="btn-accent"
            :disabled="props.isSavingProfile"
            @click="props.saveUserProfile"
          >
            {{ props.isSavingProfile ? 'Saving...' : uiText.saveProfile }}
          </button>
        </div>
      </section>

      <div v-if="showImagePreview" class="image-preview-overlay" @click.self="closeImagePreview">
        <button class="image-preview-close" aria-label="Close preview" @click="closeImagePreview">×</button>
        <img class="image-preview-large" :src="profileImageUrl" alt="Large profile image" />
        <div class="image-preview-hint">Press Esc or click outside to close</div>
      </div>

      <aside class="card profile-side">
        <div class="section-head">
          <div>
            <p class="eyebrow">Location</p>
            <h3 class="profile-section-title">{{ uiText.location }}</h3>
          </div>
        </div>

        <div class="profile-location-tools">
          <button
            type="button"
            class="btn-light full"
            :disabled="props.isDetectingLocation"
            @click="props.detectCurrentLocation"
          >
            {{ props.isDetectingLocation ? uiText.detectingLocation : uiText.useCurrentLocation }}
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
            {{ uiText.openInMap }}
          </a>
        </div>

        <div class="tips-card">
          <p class="eyebrow">Tips</p>
          <ul>
            <li>Use a bright, centered headshot for best clarity.</li>
            <li>Keep phone reachable for booking confirmations.</li>
            <li>Turn on location so vendors can suggest nearby options.</li>
            <li>Save changes before leaving to keep everything synced.</li>
          </ul>
        </div>
      </aside>
    </div>
  </main>
</template>

<style scoped>
.profile-page {
  display: grid;
  gap: 18px;
  padding-bottom: 24px;
}

.profile-hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 18px 20px;
  border: 1px solid #eadff5;
  border-radius: 20px;
  background: radial-gradient(circle at 90% 20%, rgba(255, 130, 30, 0.14), transparent 40%), linear-gradient(135deg, #ffffff 0%, #f6f8ff 100%);
  box-shadow: 0 20px 40px rgba(28, 30, 64, 0.08);
}

.hero-copy h1 {
  margin: 4px 0 6px;
  font-size: clamp(1.8rem, 2.8vw, 2.8rem);
  letter-spacing: -0.01em;
  color: #111827;
}

.hero-sub {
  margin: 4px 0 10px;
  color: #475569;
  font-size: 1rem;
}

.hero-tags {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 6px;
}

.hero-tags span {
  padding: 6px 10px;
  border-radius: 999px;
  background: #fff;
  border: 1px solid #e5e7eb;
  color: #1f2937;
  font-weight: 700;
  font-size: 0.85rem;
}

.hero-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin: 6px 0 2px;
}

.pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 10px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.85rem;
}

.pill.success {
  background: #ecfeff;
  color: #0e7490;
  border: 1px solid #a5f3fc;
}

.pill.neutral {
  background: #f8fafc;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.profile-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 18px;
  align-items: start;
}

.profile-panel,
.profile-side {
  border: 1px solid #e6e9f2;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 16px 32px rgba(17, 24, 39, 0.06);
  padding: 16px 18px;
}

.profile-identity-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.1rem;
  padding: 1.1rem 1.2rem;
  border: 1px solid #e7d7ff;
  border-radius: 16px;
  background: linear-gradient(135deg, #fdfaff 0%, #fff7ef 100%);
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);
}

.profile-avatar-main {
  display: flex;
  align-items: center;
  gap: 1rem;
  min-width: 0;
}

.profile-avatar-preview {
  width: 96px;
  height: 96px;
  border-radius: 50%;
  border: 4px solid #f3e8ff;
  background: linear-gradient(135deg, #fff8ed, #ffe1c4);
  color: #9a3412;
  font-size: 1.9rem;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
}

.profile-avatar-preview.clickable {
  cursor: zoom-in;
}

.profile-avatar-preview.clickable:hover {
  opacity: 0.92;
}

.profile-avatar-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-preview-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(10, 12, 30, 0.7);
  z-index: 9999;
  padding: 1rem;
}

.image-preview-large {
  max-width: min(90vw, 550px);
  max-height: min(90vh, 550px);
  border-radius: 14px;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.45);
  object-fit: contain;
  background: #111;
}

.image-preview-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.9);
  color: #111;
  font-size: 1.4rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.35);
}

.image-preview-close:hover {
  background: #fff;
}

.image-preview-large {
  animation: scaleIn 220ms ease-out;
}

.image-preview-hint {
  position: absolute;
  bottom: 1.2rem;
  left: 50%;
  transform: translateX(-50%);
  color: #fff;
  font-size: 0.85rem;
  background: rgba(0, 0, 0, 0.45);
  border-radius: 999px;
  padding: 4px 10px;
  text-align: center;
}

@keyframes scaleIn {
  from {
    transform: scale(0.85);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}

.profile-avatar-copy {
  display: grid;
  gap: 6px;
  min-width: 0;
}

.profile-avatar-copy strong {
  color: #0f172a;
  font-size: clamp(1.4rem, 2.2vw, 2.1rem);
  font-weight: 800;
  line-height: 1.1;
}

.profile-avatar-copy small {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-progress span {
  display: block;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 700;
}

.progress-track {
  margin-top: 6px;
  width: 100%;
  height: 8px;
  border-radius: 999px;
  background: #e5e7eb;
  overflow: hidden;
}

.progress-bar {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #22d3ee, #6366f1, #f97316);
}

.profile-avatar-actions {
  display: flex;
  gap: 0.55rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}

.eyebrow {
  margin: 0;
  font-size: 0.85rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  font-weight: 700;
}

.profile-section-title {
  margin: 4px 0 0;
  color: #111827;
  font-size: 1.2rem;
  font-weight: 800;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.profile-grid label {
  display: grid;
  gap: 6px;
  color: #1f2937;
  font-weight: 700;
  font-size: 0.95rem;
}

.profile-grid input {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  padding: 0.72rem 0.82rem;
}

.profile-grid input:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
}

.helper-note {
  margin: -6px 0 12px;
  color: #64748b;
  font-weight: 600;
  font-size: 0.9rem;
}

.profile-location-tools {
  margin-top: 0.6rem;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
  padding: 0.9rem;
  display: grid;
  gap: 10px;
}

.profile-upload-btn input {
  display: none;
}

.btn-light {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  color: #0f172a;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 0.65rem 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.btn-light:hover {
  border-color: #c7d2fe;
  box-shadow: 0 6px 16px rgba(99, 102, 241, 0.15);
  transform: translateY(-1px);
}

.btn-light.full {
  width: 100%;
}

.btn-accent {
  background: linear-gradient(135deg, #ff8c1a 0%, #ea580c 100%);
  border: 1px solid #ee7a24;
  border-radius: 12px;
  color: #fff;
  font-weight: 800;
  padding: 0.65rem 1.1rem;
  box-shadow: 0 14px 26px rgba(234, 88, 12, 0.22);
}

.btn-accent:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(234, 88, 12, 0.3);
}

.btn-accent:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-ghost {
  border: 1px solid #e5e7eb;
  background: transparent;
  color: #0f172a;
  border-radius: 12px;
  font-weight: 700;
  padding: 0.62rem 0.9rem;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.btn-ghost.subtle {
  color: #475569;
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

.is-danger {
  border: 1px solid #f4c7c7;
  border-radius: 12px;
  background: linear-gradient(180deg, #ffffff 0%, #fff4f4 100%);
  color: #b42318;
  font-weight: 600;
  padding: 0.6rem 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.is-danger:hover {
  border-color: #eca3a3;
  background: linear-gradient(180deg, #fff 0%, #ffe7e7 100%);
  box-shadow: 0 4px 12px rgba(180, 35, 24, 0.15);
  transform: translateY(-1px);
}

.btn-logout {
  border: 1px solid #fecaca;
  border-radius: 12px;
  background: #fef2f2;
  color: #b91c1c;
  font: inherit;
  font-weight: 700;
  padding: 0.65rem 1.1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.btn-logout:hover {
  background: #fee2e2;
  box-shadow: 0 4px 12px rgba(185, 28, 28, 0.15);
  transform: translateY(-1px);
}

.profile-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  align-items: center;
}

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

.map-frame {
  height: 260px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(15, 23, 42, 0.06);
  transition: box-shadow 0.2s ease;
}

.map-frame:hover {
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
}

.location-coords {
  margin: 0;
  color: #475569;
  font-weight: 700;
  font-size: 0.95rem;
}

.notice {
  margin-top: 0.5rem;
  border: 1px solid #f4cfac;
  border-radius: 12px;
  background: #fff5ea;
  color: #9a3412;
  font-weight: 700;
  padding: 0.62rem 0.78rem;
}

.tips-card {
  margin-top: 12px;
  border: 1px dashed #e5e7eb;
  border-radius: 12px;
  background: #f8fafc;
  padding: 10px 12px;
}

.tips-card ul {
  margin: 8px 0 0;
  padding-left: 18px;
  color: #475569;
  display: grid;
  gap: 6px;
}

@media (max-width: 980px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }

  .hero-actions {
    width: 100%;
    justify-content: flex-start;
  }

  .profile-hero {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 640px) {
  .profile-avatar-preview {
    width: 78px;
    height: 78px;
    font-size: 1.4rem;
  }

  .profile-actions {
    flex-wrap: wrap;
    justify-content: flex-start;
  }
}
</style>