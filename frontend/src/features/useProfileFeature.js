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
      (position) => {
        const lat = Number(position.coords.latitude.toFixed(6))
        const lng = Number(position.coords.longitude.toFixed(6))
        userLatitude.value = lat
        userLongitude.value = lng
        userLocation.value = `${lat}, ${lng}`
        userProfileDraft.value.location = `${lat}, ${lng}`
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
    goToProfile,
    saveUserProfile,
    resetUserProfile,
    detectCurrentLocation,
  }
}
