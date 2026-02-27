
<template>
  <nav class="navbar">
    <div class="logo-section">
      <div class="logo-box">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.1046 21 21 20.1046 21 19V7C21 5.89543 20.1046 5 19 5H5C3.89543 5 3 5.89543 3 7V19C3 20.1046 3.89543 21 5 21Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <span class="brand-name">Achar</span>
    </div>

    <ul class="nav-links">
      <li><a href="#" class="active">Home</a></li>
      <li><a href="#about">About Us</a></li>
      <li><a href="#package">Package</a></li>
      <li><a href="#view">View</a></li>
    </ul>

    <div class="auth-section">
      <button class="btn-login">Login</button>
      <button class="btn-signup">Sign Up</button>
    </div>
  </nav>
</template>

<script setup>
// Logic for active states or routing would go here
</script>

<style scoped>
/* Main Container */
.navbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 4rem;
  background-color: #ffffff;
  font-family: Arial, Helvetica, sans-serif;
}

/* Logo Styling */
.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-box {
  background-color: #ff4d15; /* The specific orange from image */
  padding: 8px;
  border-radius: 8px;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e293b;
}

.dot {
  color: #ff4d15;
}

/* Navigation Links */
.nav-links {
  display: flex;
  list-style: none;
  gap: 2.5rem;
  margin: 0;
  padding: 0;
}

.nav-links a {
  text-decoration: none;
  color: #334155;
  font-weight: 600;
  font-size: 0.95rem;
  transition: color 0.2s ease;
}

.nav-links a.active {
  color: #ff4d15;
}

.nav-links a:hover {
  color: #ff4d15;
}

/* Auth Buttons */
.auth-section {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.btn-login {
  background: none;
  border: none;
  font-weight: 700;
  color: #1e293b;
  cursor: pointer;
  font-size: 0.95rem;
}

.btn-signup {
  background-color: #ff4d15;
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 15px -3px rgba(255, 77, 21, 0.3);
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.btn-signup:hover {
  background-color: #e64412;
  transform: translateY(-1px);
}

/* Responsive adjustment */
@media (max-width: 768px) {
  .nav-links {
    display: none; /* Typical mobile behavior */
  }
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background-color: #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  height: 70px;
}
</style>
