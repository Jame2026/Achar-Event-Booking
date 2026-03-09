<script setup>
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
  'logoutUser',
])

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
    breadcrumbs: 'ផ្ទាំងគ្រប់គ្រង > ប្រវត្តិរូបរបស់ខ្ញុំ',
    title: 'កែប្រែប្រវត្តិរូបរបស់ខ្ញុំ',
    fullName: 'ឈ្មោះពេញ',
    fullNamePlaceholder: 'ឈ្មោះពេញរបស់អ្នក',
    email: 'អ៊ីមែល',
    emailPlaceholder: 'you@example.com',
    phone: 'ទូរស័ព្ទ',
    location: 'ទីតាំង',
    locationPlaceholder: 'ទីក្រុង, ខេត្ត',
    detectingLocation: 'កំពុងស្វែងរកទីតាំង...',
    useCurrentLocation: 'ប្រើទីតាំងបច្ចុប្បន្ន',
    openInMap: 'បើកទីតាំងបច្ចុប្បន្នក្នុងផែនទី',
    logout: 'ចាកចេញ',
    reset: 'កំណត់ឡើងវិញ',
    saveProfile: 'រក្សាទុកប្រវត្តិរូប',
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
    locationPlaceholder: '城市，省份',
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

    <section class="card services profile-form">
      <h2>{{ uiText.title }}</h2>
      <p v-if="props.userProfileNotice" class="notice">{{ props.userProfileNotice }}</p>

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

      <div class="profile-location-tools">
        <button type="button" class="btn-light" :disabled="props.isDetectingLocation" @click="props.detectCurrentLocation">
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

      <div class="profile-actions">
        <button type="button" class="btn-logout" @click="props.logoutUser">{{ uiText.logout }}</button>
        <button type="button" class="btn-light" @click="props.resetUserProfile">{{ uiText.reset }}</button>
        <button type="button" class="btn-accent" @click="props.saveUserProfile">{{ uiText.saveProfile }}</button>
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
