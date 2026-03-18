<script setup lang="ts">
defineProps<{
  user: {
    id: number;
    name: string;
    email: string;
    role: string;
  };
}>();

defineEmits<{
  logout: [];
}>();

const planningSteps = [
  {
    eyebrow: "01",
    title: "Explore services",
    text: "Browse venues, decorators, catering, and ceremony specialists in one place.",
  },
  {
    eyebrow: "02",
    title: "Shortlist faster",
    text: "Compare trusted vendors, pricing, and styles before you make decisions.",
  },
  {
    eyebrow: "03",
    title: "Confirm your plan",
    text: "Move from inspiration to booking with a cleaner, more organized workflow.",
  },
];

const highlightStats = [
  { value: "All-in-one", label: "Booking flow" },
  { value: "Trusted", label: "Vendor discovery" },
  { value: "Faster", label: "Event planning" },
];
</script>

<template>
  <section class="home-shell">
    <div class="home-backdrop"></div>

    <header class="home-topbar">
      <div class="brand-row">
        <span class="brand-dot"></span>
        <div>
          <p class="brand-kicker">Achar Event Booking</p>
          <strong class="brand-name">Member Dashboard</strong>
        </div>
      </div>

      <button class="logout-btn" type="button" @click="$emit('logout')">
        Logout
      </button>
    </header>

    <main class="home-content">
      <section class="hero-card">
        <div class="hero-copy">
          <p class="hero-chip">Welcome back</p>
          <h1>
            Plan your next event with
            <span>{{ user.name }}</span>
            in control.
          </h1>
          <p class="hero-text">
            Keep your bookings, vendor research, and event ideas in one polished
            workspace built for smoother planning.
          </p>

          <div class="hero-actions">
            <RouterLink to="/services/packages" class="primary-action">
              Explore Services
            </RouterLink>
            <RouterLink to="/about" class="secondary-action">
              See How Achar Works
            </RouterLink>
          </div>

          <div class="hero-stats" aria-label="Platform highlights">
            <article
              v-for="item in highlightStats"
              :key="item.label"
              class="stat-pill"
            >
              <strong>{{ item.value }}</strong>
              <span>{{ item.label }}</span>
            </article>
          </div>
        </div>

        <aside class="profile-panel">
          <p class="profile-label">Your account</p>
          <h2>{{ user.name }}</h2>
          <div class="profile-grid">
            <article class="profile-item">
              <span>Email</span>
              <strong>{{ user.email }}</strong>
            </article>
            <article class="profile-item">
              <span>Role</span>
              <strong>{{ user.role }}</strong>
            </article>
            <article class="profile-item">
              <span>Status</span>
              <strong>Ready to book</strong>
            </article>
            <article class="profile-item">
              <span>Workspace</span>
              <strong>Achar member area</strong>
            </article>
          </div>
        </aside>
      </section>

      <section class="journey-section">
        <div class="section-heading">
          <p>Planning made simpler</p>
          <h2>A cleaner path from browsing to booking</h2>
        </div>

        <div class="journey-grid">
          <article
            v-for="step in planningSteps"
            :key="step.eyebrow"
            class="journey-card"
          >
            <span class="journey-step">{{ step.eyebrow }}</span>
            <h3>{{ step.title }}</h3>
            <p>{{ step.text }}</p>
          </article>
        </div>
      </section>
    </main>
  </section>
</template>

<style scoped>
.home-shell {
  position: relative;
  min-height: 100vh;
  overflow: hidden;
  padding: 32px;
  background:
    radial-gradient(circle at top left, rgba(255, 120, 24, 0.18), transparent 28%),
    radial-gradient(circle at right center, rgba(13, 24, 61, 0.08), transparent 30%),
    linear-gradient(180deg, #f5efe7 0%, #fffaf4 48%, #f4f0eb 100%);
}

.home-backdrop {
  position: absolute;
  inset: 0;
  background:
    linear-gradient(135deg, rgba(255, 255, 255, 0.78), rgba(255, 247, 238, 0.54)),
    repeating-linear-gradient(
      135deg,
      rgba(183, 153, 120, 0.05) 0,
      rgba(183, 153, 120, 0.05) 1px,
      transparent 1px,
      transparent 24px
    );
  pointer-events: none;
}

.home-topbar,
.home-content {
  position: relative;
  z-index: 1;
  max-width: 1280px;
  margin: 0 auto;
}

.home-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 28px;
}

.brand-row {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-dot {
  width: 14px;
  height: 14px;
  border-radius: 999px;
  background: linear-gradient(135deg, #ff8f2a, #ff5a00);
  box-shadow: 0 0 0 8px rgba(255, 143, 42, 0.14);
}

.brand-kicker {
  margin: 0;
  font-size: 0.72rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #8e745a;
}

.brand-name {
  color: #0f1a36;
  font-size: 1.1rem;
}

.logout-btn {
  border: 1px solid rgba(15, 26, 54, 0.12);
  background: rgba(255, 255, 255, 0.72);
  color: #0f1a36;
  border-radius: 999px;
  padding: 12px 18px;
  font-weight: 700;
  cursor: pointer;
  backdrop-filter: blur(10px);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
}

.logout-btn:hover {
  transform: translateY(-1px);
  border-color: rgba(255, 106, 0, 0.3);
  box-shadow: 0 16px 28px rgba(15, 26, 54, 0.08);
}

.home-content {
  display: grid;
  gap: 28px;
}

.hero-card {
  display: grid;
  grid-template-columns: minmax(0, 1.6fr) minmax(300px, 0.9fr);
  gap: 28px;
  padding: 36px;
  border-radius: 32px;
  border: 1px solid rgba(183, 153, 120, 0.16);
  background:
    linear-gradient(125deg, rgba(255, 255, 255, 0.92), rgba(255, 246, 236, 0.9)),
    #fff;
  box-shadow:
    0 28px 80px rgba(133, 92, 51, 0.12),
    inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.hero-copy {
  display: grid;
  align-content: start;
  gap: 20px;
  padding-right: 8px;
}

.hero-chip {
  width: fit-content;
  margin: 0;
  padding: 10px 16px;
  border-radius: 999px;
  border: 1px solid rgba(255, 136, 34, 0.28);
  background: rgba(255, 143, 42, 0.08);
  color: #9b5c20;
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0;
  max-width: 11ch;
  color: #0c1732;
  font-size: clamp(3rem, 6vw, 5.4rem);
  line-height: 0.95;
  letter-spacing: -0.05em;
}

.hero-copy h1 span {
  color: #ff6a00;
  font-style: italic;
}

.hero-text {
  margin: 0;
  max-width: 62ch;
  color: #56627c;
  font-size: 1.1rem;
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.primary-action,
.secondary-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 52px;
  padding: 0 22px;
  border-radius: 999px;
  font-weight: 700;
  text-decoration: none;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;
}

.primary-action {
  background: linear-gradient(135deg, #ff932f, #ff6200);
  color: #fff;
  box-shadow: 0 18px 34px rgba(255, 106, 0, 0.24);
}

.secondary-action {
  border: 1px solid rgba(15, 26, 54, 0.1);
  background: rgba(255, 255, 255, 0.75);
  color: #102041;
}

.primary-action:hover,
.secondary-action:hover {
  transform: translateY(-2px);
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
  margin-top: 10px;
}

.stat-pill {
  display: grid;
  gap: 4px;
  padding: 18px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.7);
  border: 1px solid rgba(15, 26, 54, 0.08);
}

.stat-pill strong {
  color: #122041;
  font-size: 1.1rem;
}

.stat-pill span {
  color: #7a879f;
  font-size: 0.92rem;
}

.profile-panel {
  position: relative;
  display: grid;
  align-content: start;
  gap: 18px;
  padding: 26px;
  border-radius: 26px;
  background:
    linear-gradient(180deg, rgba(14, 25, 53, 0.97), rgba(20, 33, 68, 0.92)),
    #101b39;
  color: #fff;
  overflow: hidden;
}

.profile-panel::before {
  content: "";
  position: absolute;
  width: 220px;
  height: 220px;
  top: -80px;
  right: -70px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 143, 42, 0.35), transparent 68%);
}

.profile-label {
  position: relative;
  margin: 0;
  color: rgba(255, 209, 173, 0.82);
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.75rem;
  font-weight: 700;
}

.profile-panel h2 {
  position: relative;
  margin: 0;
  font-size: 1.85rem;
  line-height: 1.1;
}

.profile-grid {
  position: relative;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.profile-item {
  display: grid;
  gap: 8px;
  min-height: 92px;
  padding: 16px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.09);
}

.profile-item span {
  color: rgba(233, 237, 247, 0.74);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.profile-item strong {
  color: #fff4ea;
  font-size: 1rem;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.journey-section {
  padding: 8px 4px 0;
}

.section-heading {
  display: grid;
  gap: 8px;
  margin-bottom: 18px;
}

.section-heading p {
  margin: 0;
  color: #a96b34;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 0.78rem;
  font-weight: 800;
}

.section-heading h2 {
  margin: 0;
  color: #0f1a36;
  font-size: clamp(1.8rem, 3vw, 2.7rem);
  letter-spacing: -0.03em;
}

.journey-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.journey-card {
  padding: 24px;
  border-radius: 24px;
  border: 1px solid rgba(15, 26, 54, 0.08);
  background: rgba(255, 255, 255, 0.74);
  box-shadow: 0 18px 44px rgba(15, 26, 54, 0.06);
}

.journey-step {
  display: inline-flex;
  width: 42px;
  height: 42px;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  border-radius: 14px;
  background: rgba(255, 106, 0, 0.12);
  color: #ff6a00;
  font-weight: 800;
}

.journey-card h3 {
  margin: 0 0 10px;
  color: #102041;
  font-size: 1.2rem;
}

.journey-card p {
  margin: 0;
  color: #65728c;
  line-height: 1.7;
}

@media (max-width: 1040px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-copy h1 {
    max-width: 12ch;
  }
}

@media (max-width: 760px) {
  .home-shell {
    padding: 18px;
  }

  .home-topbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .hero-card {
    padding: 22px;
    border-radius: 24px;
  }

  .hero-copy h1 {
    max-width: none;
    font-size: clamp(2.5rem, 16vw, 4rem);
  }

  .hero-stats,
  .profile-grid,
  .journey-grid {
    grid-template-columns: 1fr;
  }

  .profile-panel {
    padding: 22px;
  }
}
</style>
