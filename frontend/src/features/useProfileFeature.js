import { computed, ref } from 'vue'

export function useProfileFeature(notice) {
  const customerName = ref(localStorage.getItem('achar_customer_name') || '')
  const customerEmail = ref(localStorage.getItem('achar_customer_email') || '')
  const userPhone = ref(localStorage.getItem('achar_user_phone') || '')
  const userLocation = ref(localStorage.getItem('achar_user_location') || '')
  const userLatitude = ref(localStorage.getItem('achar_user_lat') ? Number(localStorage.getItem('achar_user_lat')) : null)
  const userLongitude = ref(localStorage.getItem('achar_user_lng') ? Number(localStorage.getItem('achar_user_lng')) : null)
  const isDetectingLocation = ref(false)
  const userProfileNotice = ref('')

  const userProfileDraft = ref({
    name: customerName.value,
    email: customerEmail.value,
    phone: userPhone.value,
    location: userLocation.value,
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
    }
  }

  function saveUserProfile() {
    customerName.value = userProfileDraft.value.name.trim()
    customerEmail.value = userProfileDraft.value.email.trim()
    userPhone.value = userProfileDraft.value.phone.trim()
    userLocation.value = userProfileDraft.value.location.trim()
    userProfileNotice.value = 'User profile updated.'
    notice.value = 'Your profile changes were saved.'
  }

  function resetUserProfile() {
    customerName.value = ''
    customerEmail.value = ''
    userPhone.value = ''
    userLocation.value = ''
    userLatitude.value = null
    userLongitude.value = null
    userProfileDraft.value = { name: '', email: '', phone: '', location: '' }
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
    userLatitude,
    userLongitude,
    isDetectingLocation,
    userProfileNotice,
    userProfileDraft,
    userAvatarInitial,
    userLocationMapUrl,
    userLocationMapEmbedUrl,
    userLocationMapLinkUrl,
    goToProfile,
    saveUserProfile,
    resetUserProfile,
    detectCurrentLocation,
  }
}
