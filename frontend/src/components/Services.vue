<template>
  <div class="services-container">
    <nav class="crumbs" aria-label="Breadcrumb">
      <router-link to="/dashboard">Dashboard</router-link>
      <span aria-hidden="true">›</span>
      <span class="current">Services</span>
    </nav>

    <div class="header">
      <h1>Services</h1>
      <p class="subtitle">Manage all your event services and offerings.</p>
    </div>


    <!-- Edit Form Modal booking and Delete booking-->

    <div v-if="editingService" class="modal-overlay" @click="closeEditForm">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Edit Service</h2>
          <button class="close-btn" @click="closeEditForm">×</button>
        </div>
        <form @submit.prevent="updateService" class="edit-form">
          <div class="form-group">
            <label for="edit-name">Service Name *</label>
            <input
              id="edit-name"
              v-model.trim="editForm.name"
              type="text"
              required
            />
          </div>
          <div class="form-group">
            <label for="edit-price">Price per Guest (USD) *</label>
            <input
              id="edit-price"
              v-model.number="editForm.price"
              type="number"
              min="0"
              step="0.01"
              required
            />
          </div>
          <div class="form-group">
            <label for="edit-description">Description</label>
            <textarea
              id="edit-description"
              v-model="editForm.description"
              rows="3"
            ></textarea>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="closeEditForm">Cancel</button>
            <button type="submit" class="btn-save">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <div class="services-content">
      <div class="services-list">
        <div class="service-item" v-for="service in services" :key="service.id">
          <div class="service-info">
            <h3>{{ service.name }}</h3>
            <p>{{ service.description }}</p>
            <span class="price">${{ service.price }}/guest</span>
          </div>
          <div class="service-actions">
            <button class="btn-edit" @click="editService(service)">Edit</button>
            <button class="btn-delete" @click="deleteService(service.id)">Delete</button>
          </div>
        </div>
      </div>

      <div class="actions">
        <router-link to="/" class="btn-primary">Add New Service</router-link>
        <router-link to="/dashboard" class="btn-secondary">Back to Dashboard</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const services = ref([
  {
    id: 1,
    name: 'Premium Buffet Catering',
    description: 'Full-service buffet with premium menu options',
    price: 45
  },
  {
    id: 2,
    name: 'Wedding Photography',
    description: 'Professional photography for wedding events',
    price: 250
  },
  {
    id: 3,
    name: 'Event Decoration',
    description: 'Complete event decoration and setup',
    price: 75
  }
])

const editingService = ref(null)
const editForm = ref({
  name: '',
  description: '',
  price: null
})

function editService(service) {
  editingService.value = service
  editForm.value = {
    name: service.name,
    description: service.description,
    price: service.price
  }
}

function closeEditForm() {
  editingService.value = null
  editForm.value = {
    name: '',
    description: '',
    price: null
  }
}

function updateService() {
  if (!editingService.value) return
  
  const serviceIndex = services.value.findIndex(s => s.id === editingService.value.id)
  if (serviceIndex !== -1) {
    services.value[serviceIndex] = {
      ...services.value[serviceIndex],
      ...editForm.value
    }
  }
  
  closeEditForm()
}

function deleteService(serviceId) {
  if (confirm('Are you sure you want to delete this service?')) {
    services.value = services.value.filter(s => s.id !== serviceId)
  }
}
</script>

<style scoped>
.services-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.services-list {
  margin: 30px 0;
}

.service-item {
  background: var(--color-surface);
  padding: 20px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  margin-bottom: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.service-info h3 {
  margin: 0 0 10px 0;
  color: var(--color-text);
}

.service-info p {
  margin: 0 0 10px 0;
  color: var(--color-muted);
}

.price {
  font-weight: bold;
  color: var(--color-primary);
}

.service-actions {
  display: flex;
  gap: 10px;
}

.btn-edit, .btn-delete {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-edit {
  background: var(--color-border);
  color: var(--color-text);
}

.btn-delete {
  background: #ff4444;
  color: white;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
}

.btn-primary {
  background: var(--color-primary);
  color: white;
}

.btn-secondary {
  background: var(--color-border);
  color: var(--color-text);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--color-surface);
  border-radius: 8px;
  padding: 0;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid var(--color-border);
}

.modal-header h2 {
  margin: 0;
  color: var(--color-text);
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: var(--color-muted);
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: var(--color-text);
}

.edit-form {
  padding: 20px;
}

.edit-form .form-group {
  margin-bottom: 20px;
}

.edit-form label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--color-text);
}

.edit-form input,
.edit-form textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-family: inherit;
  font-size: 14px;
}

.edit-form input:focus,
.edit-form textarea:focus {
  outline: none;
  border-color: var(--color-primary);
}

.edit-form .form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-cancel {
  background: var(--color-border);
  color: var(--color-text);
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-save {
  background: var(--color-primary);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #d1d5db;
}

.btn-save:hover {
  background: #e55a00;
}
</style>
