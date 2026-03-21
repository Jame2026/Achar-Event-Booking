import { computed, ref } from 'vue'
import { apiGet, apiPost } from './apiClient'

export function useProfileFeature(notice, loggedInUser) {
  const AUTH_USER_STORAGE_KEY = 'achar_auth_user'
  const MISSING_PROFILE_MESSAGE = 'User profile not found.'
  const customerName = ref(localStorage.getItem('achar_customer_name') || '')
  const customerEmail = ref(localStorage.getItem('achar_customer_email') || '')
  const userPhone = ref(localStorage.getItem('achar_user_phone') || '')
  const userLocation = ref(localStorage.getItem('achar_user_location') || '')
  const userProfileImageUrl = ref(localStorage.getItem('achar_user_profile_image') || '')
  const profileImageFile = ref(null)
  const isSavingProfile = ref(false)
  const userLatitude = ref(localStorage.getItem('achar_user_lat') ? Number(localStorage.getItem('achar_user_lat')) : null)
  const userLongitude = ref(localStorage.getItem('achar_user_lng') ? Number(localStorage.getItem('achar_user_lng')) : null)
  const isDetectingLocation = ref(false)
  const userProfileNotice = ref('')

  const userProfileDraft = ref({
    name: customerName.value,
    email: customerEmail.value,
    phone: userPhone.value,
    location: userLocation.value,
    profile_image_url: userProfileImageUrl.value,
  })

  const userAvatarInitial = computed(() => (customerName.value.trim().charAt(0) || 'P').toUpperCase())
  const userLocationMapUrl = computed(() => {
    if (userLatitude.value === null || userLongitude.value === null) return ''
    const lat = userLatitude.value.toFixed(6)
    const lng = userLongitude.value.toFixed(6)
    return `https://staticmap.openstreetmap.de/staticmap.php?center=${lat},${lng}&zoom=13&size=700x320&markers=${lat},${lng},orange-pushpin`
  })
  const userLocationMapEmbedUrl = computed(() => {
    if (userLatitude.value === null || userLongitude.value === null) return ''
    const lat = Number(userLatitude.value.toFixed(6))
    const lng = Number(userLongitude.value.toFixed(6))
    const delta = 0.012
    const left = (lng - delta).toFixed(6)
    const bottom = (lat - delta).toFixed(6)
    const right = (lng + delta).toFixed(6)
    const top = (lat + delta).toFixed(6)
    return `https://www.openstreetmap.org/export/embed.html?bbox=${left}%2C${bottom}%2C${right}%2C${top}&layer=mapnik&marker=${lat}%2C${lng}`
  })
  const userLocationMapLinkUrl = computed(() => {
    if (userLatitude.value === null || userLongitude.value === null) return ''
    const lat = Number(userLatitude.value.toFixed(6))
    const lng = Number(userLongitude.value.toFixed(6))
    return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=14/${lat}/${lng}`
  })

  async function resolveLocationName(lat, lng) {
    try {
      const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
      const response = await fetch(url, {
        headers: {
          Accept: 'application/json',
          'Accept-Language': 'en',
        },
      })
      if (!response.ok) return ''
      const data = await response.json()
      const address = data?.address || {}
      const streetNumber = address.house_number || address.house_name || address.building || ''
      const streetName = address.road || address.pedestrian || address.footway || ''
      const street = [streetNumber, streetName].filter(Boolean).join(' ').trim()
      const village = address.village || address.hamlet || address.neighbourhood || address.suburb || ''
      const district = address.city_district || address.district || address.borough || address.county || ''
      const city = address.city || address.town || address.municipality || address.state_district || ''
      const province = address.state || address.region || address.province || ''
      const parts = [street, village, district, city, province].filter((value) => String(value).trim().length > 0)
      if (parts.length) return parts.join(', ')

      const primaryName =
        data?.name || address.shop || address.amenity || address.tourism || address.building || ''
      return String(primaryName || '').trim()
    } catch {
      return ''
    }
  }

  function goToProfile(currentPage) {
    currentPage.value = 'profile'
    userProfileNotice.value = ''
    userProfileDraft.value = {
      name: customerName.value,
      email: customerEmail.value,
      phone: userPhone.value,
      location: userLocation.value,
      profile_image_url: userProfileImageUrl.value,
    }
  }

  function getProfileQuery() {
    const user = loggedInUser?.value || {}
    const userId = Number(user.id || 0)
    const email = String(user.email || '').trim().toLowerCase()
    const phone = String(user.phone || '').trim()
    if (userId > 0) return { user_id: userId }
    if (email) return { email }
    if (phone) return { phone }
    return null
  }

  function syncLocalAuthUser(patch = {}) {
    const current = loggedInUser?.value || {}
    const next = { ...current, ...patch }
    if (loggedInUser) loggedInUser.value = next
    localStorage.setItem(AUTH_USER_STORAGE_KEY, JSON.stringify(next))
    window.dispatchEvent(new Event('achar:auth-updated'))
  }

  async function loadUserProfile() {
    const query = getProfileQuery()
    if (!query) return

    try {
      const result = await apiGet('user/profile', query)
      const user = result?.user || {}
      customerName.value = String(user.name || '').trim()
      customerEmail.value = String(user.email || '').trim()
      userPhone.value = String(user.phone || '').trim()
      userLocation.value = String(user.location || '').trim()
      userProfileImageUrl.value = String(user.profile_image_url || '').trim()
      userProfileDraft.value = {
        name: customerName.value,
        email: customerEmail.value,
        phone: userPhone.value,
        location: userLocation.value,
        profile_image_url: userProfileImageUrl.value,
      }
      syncLocalAuthUser({
        id: Number(user.id || loggedInUser?.value?.id || 0),
        name: customerName.value,
        email: customerEmail.value,
        phone: userPhone.value,
        location: userLocation.value,
        profile_image_url: userProfileImageUrl.value,
        role: String(user.role || loggedInUser?.value?.role || 'user'),
      })
    } catch (error) {
      const message = String(error?.message || '').trim()
      if (message === MISSING_PROFILE_MESSAGE) {
        return
      }
      notice.value = message || 'Could not load profile data.'
    }
  }

  function updateProfileImageFile(file) {
    if (!(file instanceof File)) {
      profileImageFile.value = null
      return
    }
    profileImageFile.value = file
    const reader = new FileReader()
    reader.onload = () => {
      userProfileDraft.value.profile_image_url = typeof reader.result === 'string' ? reader.result : ''
    }
    reader.readAsDataURL(file)
  }

  function removeProfileImage() {
    profileImageFile.value = null
    userProfileDraft.value.profile_image_url = ''
  }

  async function saveUserProfile() {
    const query = getProfileQuery()
    if (!query) {
      notice.value = 'Please sign in again and try updating your profile.'
      return
    }

    const payload = new FormData()
    payload.append('name', userProfileDraft.value.name.trim())
    payload.append('phone', userProfileDraft.value.phone.trim())
    payload.append('location', userProfileDraft.value.location.trim())
    payload.append('user_id', String(query.user_id || ''))
    payload.append('email', String(query.email || ''))
    payload.append('remove_image', userProfileDraft.value.profile_image_url ? '0' : '1')
    if (profileImageFile.value instanceof File) payload.append('profile_image', profileImageFile.value)

    isSavingProfile.value = true
    try {
      const result = await apiPost('user/profile', payload)
      const user = result?.user || {}
      customerName.value = String(user.name || '').trim()
      customerEmail.value = String(user.email || '').trim()
      userPhone.value = String(user.phone || '').trim()
      userLocation.value = String(user.location || '').trim()
      userProfileImageUrl.value = String(user.profile_image_url || '').trim()
      profileImageFile.value = null
      userProfileDraft.value = {
        name: customerName.value,
        email: customerEmail.value,
        phone: userPhone.value,
        location: userLocation.value,
        profile_image_url: userProfileImageUrl.value,
      }
      localStorage.setItem('achar_user_profile_image', userProfileImageUrl.value)
      syncLocalAuthUser({
        id: Number(user.id || loggedInUser?.value?.id || 0),
        name: customerName.value,
        email: customerEmail.value,
        phone: userPhone.value,
        location: userLocation.value,
        profile_image_url: userProfileImageUrl.value,
        role: String(user.role || loggedInUser?.value?.role || 'user'),
      })
      userProfileNotice.value = 'User profile updated.'
      notice.value = 'Your profile changes were saved.'
    } catch (error) {
      userProfileNotice.value = error?.message || 'Could not save profile.'
      notice.value = userProfileNotice.value
    } finally {
      isSavingProfile.value = false
    }
  }

  function resetUserProfile() {
    customerName.value = ''
    customerEmail.value = ''
    userPhone.value = ''
    userLocation.value = ''
    userProfileImageUrl.value = ''
    profileImageFile.value = null
    userLatitude.value = null
    userLongitude.value = null
    userProfileDraft.value = { name: '', email: '', phone: '', location: '', profile_image_url: '' }
    userProfileNotice.value = 'User profile reset.'
    notice.value = 'Your profile was reset.'
  }

  function detectCurrentLocation() {
    if (!navigator.geolocation) {
      userProfileNotice.value = 'Geolocation is not supported by this browser.'
      return
    }

    isDetectingLocation.value = true
    navigator.geolocation.getCurrentPosition(
      async (position) => {
        const lat = Number(position.coords.latitude.toFixed(6))
        const lng = Number(position.coords.longitude.toFixed(6))
        const placeName = await resolveLocationName(lat, lng)
        userLatitude.value = lat
        userLongitude.value = lng
        userLocation.value = placeName || `${lat}, ${lng}`
        userProfileDraft.value.location = placeName || `${lat}, ${lng}`
        userProfileNotice.value = 'Current location captured.'
        notice.value = 'Real location updated.'
        isDetectingLocation.value = false
      },
      () => {
        userProfileNotice.value = 'Could not access your current location.'
        isDetectingLocation.value = false
      },
      {
        enableHighAccuracy: true,
        timeout: 12000,
        maximumAge: 300000,
      },
    )
  }

  return {
    customerName,
    customerEmail,
    userPhone,
    userLocation,
    userProfileImageUrl,
    profileImageFile,
    isSavingProfile,
    userLatitude,
    userLongitude,
    isDetectingLocation,
    userProfileNotice,
    userProfileDraft,
    userAvatarInitial,
    userLocationMapUrl,
    userLocationMapEmbedUrl,
    userLocationMapLinkUrl,
    loadUserProfile,
    updateProfileImageFile,
    removeProfileImage,
    goToProfile,
    saveUserProfile,
    resetUserProfile,
    detectCurrentLocation,
  }
}
