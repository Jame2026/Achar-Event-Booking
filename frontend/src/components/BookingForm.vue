
<!-- AddServiceForm.vue -->
<template>
  <div class="add-service-container">
    <nav class="crumbs" aria-label="Breadcrumb">
      <router-link to="/dashboard">Dashboard</router-link>
      <span aria-hidden="true">›</span>
      <router-link to="/services">Services</router-link>
      <span aria-hidden="true">›</span>
      <span class="current">Add New Service</span>
    </nav>

    <div class="header">
      <h1>Add New Service</h1>
      <p class="subtitle">Configure your service details, pricing, and scalability options below.</p>
    </div>

    <form @submit.prevent="handleSubmit" class="service-form">
      <!-- Section 1: Basic Information -->
      <section class="form-section">
        <h2><span class="section-icon">🗂️</span>Basic Information</h2>

        <div class="form-grid">
          <!-- Service Name -->
          <div class="form-group full-width">
            <label for="name">Service Name *</label>
            <input
              id="name"
              v-model.trim="form.name"
              type="text"
              placeholder="e.g. Premium Buffet Catering"
              required
              :class="{ 'input-error': errors.name }"
            />
            <div v-if="errors.name" class="error-text">{{ errors.name }}</div>
          </div>

          <!-- Price per Guest -->
          <div class="form-group">
            <label for="price">Price per Guest (USD) *</label>
            <div class="input-prefix">
              <span class="prefix">$</span>
              <input
                id="price"
                v-model.number="form.price"
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                required
                :class="{ 'input-error': errors.price }"
              />
            </div>
            <div v-if="errors.price" class="error-text">{{ errors.price }}</div>
          </div>

          <!-- Service Category -->
          <div class="form-group">
            <label for="category">Service Category *</label>
            <select
              id="category"
              v-model="form.category"
              required
              :class="{ 'input-error': errors.category }"
            >
              <option value="" disabled>Select Category</option>
              <option value="catering">Catering</option>
              <option value="event-planning">Event Planning</option>
              <option value="photography">Photography</option>
              <option value="decoration">Decoration</option>
              <option value="entertainment">Entertainment</option>
              <option value="other">Other</option>
            </select>
            <div v-if="errors.category" class="error-text">{{ errors.category }}</div>
          </div>

          <!-- Description -->
          <div class="form-group full-width">
            <label for="description">Service Description</label>
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              placeholder="Describe what makes this service special..."
            ></textarea>
          </div>
        </div>
      </section>

      <!-- Section 2: Scalability & Capacity -->
      <section class="form-section">
        <h2><span class="section-icon">📈</span>Scalability & Capacity</h2>

        <div class="form-grid two-columns">
          <div class="form-group">
            <label for="minGuests">Minimum Guests</label>
            <input
              id="minGuests"
              v-model.number="form.minGuests"
              type="number"
              min="1"
              placeholder="10"
            />
          </div>

          <div class="form-group">
            <label for="maxGuests">Maximum Capacity</label>
            <input
              id="maxGuests"
              v-model.number="form.maxGuests"
              type="number"
              min="1"
              placeholder="500"
            />
          </div>
        </div>

        <div class="hint-box">
          <div class="hint-icon">ℹ</div>
          <p>Setting a guest count range helps automate availability for client bookings. Large bookings can trigger custom pricing or staffing adjustments.</p>
        </div>
      </section>

      <!-- Section 3: Availability -->
      <section class="form-section">
        <h2><span class="section-icon">📅</span>Availability</h2>

        <div class="form-grid two-columns">
          <div class="form-group">
            <label for="startDate">Service Start Date *</label>
            <input
              id="startDate"
              v-model="form.startDate"
              type="date"
              :min="minDate"
              required
              :class="{ 'input-error': errors.startDate }"
            />
            <div v-if="errors.startDate" class="error-text">{{ errors.startDate }}</div>
          </div>

          <div class="form-group">
            <label for="endDate">Service End Date (Optional)</label>
            <input
              id="endDate"
              v-model="form.endDate"
              type="date"
              :min="form.startDate || minDate"
            />
          </div>
        </div>
      </section>

      <!-- Section 4: Automation Settings -->
      <section class="form-section">
        <h2><span class="section-icon">⚙️</span>Automation Settings</h2>

        <div class="toggle-group">
          <label class="toggle-label">
            <input type="checkbox" v-model="form.autoConfirm" />
            <span>Auto-confirm bookings within capacity limits</span>
          </label>
          <p class="toggle-hint">Automatically accept bookings that fit your min/max guest range.</p>
        </div>

        <div class="toggle-group">
          <label class="toggle-label">
            <input type="checkbox" v-model="form.notifyChatbot" />
            <span>Notify Chat-bot for new inquiries</span>
          </label>
          <p class="toggle-hint">Send instant alerts to your chatbot system for manual review.</p>
        </div>
      </section>

      <!-- Actions -->
      <div class="form-actions">
        <button type="button" class="btn-cancel" @click="handleCancel">Cancel</button>
        <button type="button" class="btn-save" :disabled="isSubmitting" @click="handleAddClick">
          {{ isSubmitting ? 'Saving...' : 'Add Service' }}
        </button>
      </div>

      <!-- Feedback -->
      <div v-if="successMessage" class="success-message">{{ successMessage }}</div>
      <div v-if="submitError" class="error-message">{{ submitError }}</div>
    </form>

    <!-- <div class="dashboard-hint">
      <p>Manage services, pricing, and track income trends - scalable solutions that adapt to every guest and client requirement.</p>
    </div> -->

    <div class="feature-grid">
      <div class="feature-card">
        <h4>Manage & Insert</h4>
        <p>Easily update your services and pricing through this unified management dashboard.</p>
      </div>
      <div class="feature-card">
        <h4>Track Income</h4>
        <p>View analytics, revenue trends, and booking history across months and years.</p>
      </div>
      <div class="feature-card">
        <h4>Scalable Solutions</h4>
        <p>Our system automatically adjusts based on guest count and client requirements.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import '@/assets/BookingForm.css'

const router = useRouter()
const STORAGE_KEY = 'achar_services'

const form = ref({
  name: '',
  price: null,
  category: '',
  description: '',
  minGuests: 10,
  maxGuests: 500,
  startDate: '',
  endDate: '',
  autoConfirm: true,
  notifyChatbot: false
})

const errors = ref({})
const isSubmitting = ref(false)
const successMessage = ref('')
const submitError = ref('')

const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

function validateForm() {
  errors.value = {}

  if (!form.value.name.trim()) errors.value.name = 'Service name is required'
  if (!form.value.price || form.value.price <= 0) errors.value.price = 'Valid price per guest is required'
  if (!form.value.category) errors.value.category = 'Please select a category'
  if (!form.value.startDate) errors.value.startDate = 'Start date is required'

  return Object.keys(errors.value).length === 0
}

// Load existing services from localStorage
function loadServices() {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored) {
    try {
      return JSON.parse(stored)
    } catch (e) {
      console.error('Error parsing stored services:', e)
    }
  }
  return []
}

// Save services to localStorage
function saveServices(servicesData) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(servicesData))
}

async function handleSubmit() {
  if (!validateForm()) return

  isSubmitting.value = true
  successMessage.value = ''
  submitError.value = ''

  try {
<<<<<<< HEAD
    // Create new service object
    const newService = {
      id: Date.now(),
      name: form.value.name.trim(),
      description: form.value.description ? form.value.description.trim() : '',
      price: Number(form.value.price),
      category: form.value.category,
      minGuests: form.value.minGuests,
      maxGuests: form.value.maxGuests,
      startDate: form.value.startDate,
      endDate: form.value.endDate,
      autoConfirm: form.value.autoConfirm,
      notifyChatbot: form.value.notifyChatbot
    }

    // Load existing services, add new one, and save to localStorage
    const existingServices = loadServices()
    existingServices.push(newService)
    saveServices(existingServices)
=======
    // Try to save to API first
    try {
      const response = await fetch('/api/services', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form.value)
      })

      if (response.ok) {
        const savedService = await response.json()
        // Also save to localStorage as backup
        saveToLocalStorage(savedService)
        successMessage.value = `Service "${form.value.name}" added successfully!`
        resetForm()
        // Redirect to Services page after successful add
        setTimeout(() => {
          router.push('/services')
        }, 1500)
        return
      }
    } catch (apiError) {
      console.log('API not available, using localStorage fallback')
    }
>>>>>>> user-booking-detail

    // Fallback: save to localStorage if API fails
    const services = getStoredServices()
    const newService = {
      ...form.value,
      id: Date.now(),
      created_at: new Date().toISOString()
    }
    services.push(newService)
    localStorage.setItem('achar_services', JSON.stringify(services))
    
    successMessage.value = `Service "${form.value.name}" added successfully!`
    resetForm()
    
    // Redirect to Services page after successful add
    setTimeout(() => {
      router.push('/services')
    }, 1500)
    
<<<<<<< HEAD
    // Reset form after successful submission
    resetForm()
    
    // Optionally redirect to services page after a delay
    setTimeout(() => {
      router.push('/services')
    }, 1500)
    
=======
>>>>>>> user-booking-detail
  } catch (err) {
    submitError.value = 'Failed to add service. Please try again or contact support.'
    console.error(err)
  } finally {
    isSubmitting.value = false
  }
}

<<<<<<< HEAD
=======
// Helper functions for localStorage
function getStoredServices() {
  const stored = localStorage.getItem('achar_services')
  if (!stored) return []
  try {
    return JSON.parse(stored)
  } catch {
    return []
  }
}

function saveToLocalStorage(service) {
  const services = getStoredServices()
  services.push({ ...service, id: service.id || Date.now() })
  localStorage.setItem('achar_services', JSON.stringify(services))
}

>>>>>>> user-booking-detail
function resetForm() {
  form.value = {
    name: '',
    price: null,
    category: '',
    description: '',
    minGuests: 10,
    maxGuests: 500,
    startDate: '',
    endDate: '',
    autoConfirm: true,
    notifyChatbot: false
  }
}
<<<<<<< HEAD

=======
>>>>>>> user-booking-detail
// set up cancel button booking

function cancel() {
  if (confirm('Discard changes and return to Services?')) {
    router.push('/services')
  }
}

function handleCancel() {
  console.log('Cancel button clicked')
  if (confirm('Discard changes and return to Services?')) {
    router.push('/services')
  }
}

function handleAddClick() {
  console.log('Add Service button clicked')
  handleSubmit()
}
</script>
