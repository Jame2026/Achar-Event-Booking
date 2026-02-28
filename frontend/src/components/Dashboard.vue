<template>
  <div class="dashboard-container">
    <nav class="feature-nav" aria-label="Feature Navigation">
      <router-link to="/">Home</router-link>
      <router-link to="/booking">Booking Form</router-link>
      <router-link to="/dashboard" class="active">Dashboard</router-link>
      <router-link to="/services">Services</router-link>
      <router-link to="/customization">Customization</router-link>
    </nav>

    <nav class="crumbs" aria-label="Breadcrumb">
      <router-link to="/">Home</router-link>
      <span aria-hidden="true">›</span>
      <span class="current">Dashboard</span>
      <span aria-hidden="true">›</span>
      <router-link to="/services">Services</router-link>
    </nav>

    <div class="header">
      <h1>Dashboard</h1>
      <p class="subtitle">Manage your services and track your business performance.</p>
    </div>

    <div class="dashboard-content">
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Services</h3>
          <p class="stat-number">12</p>
        </div>
        <div class="stat-card">
          <h3>Total Bookings</h3>
          <p class="stat-number">48</p>
        </div>
        <div class="stat-card">
          <h3>Revenue</h3>
          <p class="stat-number">$24,500</p>
        </div>
      </div>

      <div class="actions">
        <router-link to="/services" class="btn-primary">Manage Services</router-link>
        <router-link to="/booking" class="btn-secondary">Add New Service</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
</script>

<style scoped>
.feature-nav {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 14px;
}

.feature-nav a {
  text-decoration: none;
  border: 1px solid #d9e0ea;
  background: #fff;
  color: #1f2937;
  padding: 8px 12px;
  border-radius: 10px;
  font-weight: 600;
}

.feature-nav a.active {
  border-color: #ff6b00;
  color: #ff6b00;
  background: #fff4ec;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.stat-card {
  background: var(--color-surface);
  padding: 20px;
  border-radius: 8px;
  border: 1px solid var(--color-border);
  text-align: center;
}

.stat-number {
  font-size: 2rem;
  font-weight: bold;
  color: var(--color-primary);
  margin: 10px 0;
}

.actions {
  display: flex;
  gap: 15px;
  margin-top: 30px;
}

.btn-primary,
.btn-secondary {
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
</style>

