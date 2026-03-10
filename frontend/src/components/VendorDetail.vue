<script setup>
import { computed, onMounted, ref } from "vue";
import PublicNavbar from "./PublicNavbar.vue";
import {
  fallbackVendorLocation,
  reviews,
  stats,
  vendorProfile,
} from "../features/appData";
import { apiGet } from "../features/apiClient";

const liveServices = ref([]);
const catalogItems = computed(() =>
  liveServices.value.map((item) => ({
    id: `svc-${item.id}`,
    title: item.title || "Service Booking",
    description: item.description || "Professional vendor service ready for booking.",
  })),
);
const encodedLocation = encodeURIComponent(fallbackVendorLocation);
const mapEmbedUrl = `https://www.google.com/maps?q=${encodedLocation}&output=embed`;
const mapLinkUrl = `https://www.google.com/maps/search/?api=1&query=${encodedLocation}`;

async function loadVendorServices() {
  try {
    const result = await apiGet("events", { per_page: 80 });
    liveServices.value = Array.isArray(result?.data) ? result.data : [];
  } catch {
    liveServices.value = [];
  }
}

onMounted(() => {
  void loadVendorServices();
});
</script>

<template>
  <div class="vendor-page">
    <PublicNavbar />

    <main class="shell vendor-shell">
      <p class="breadcrumbs">Home > Vendors > {{ vendorProfile.name }}</p>

      <section class="vendor-hero">
        <img
          class="vendor-hero-bg"
          src="https://images.unsplash.com/photo-1508610048659-a06b669e3321?auto=format&fit=crop&w=1600&q=80"
          alt="Luxe Bloom Florals banner"
        />
        <div class="vendor-hero-overlay"></div>
        <div class="vendor-hero-main">
          <div class="vendor-badge">LUXE<br />BLOOM</div>
          <div class="vendor-hero-copy">
            <h1>{{ vendorProfile.name }}</h1>
            <p>{{ vendorProfile.subtitle }}</p>
            <strong>{{ vendorProfile.rating }}</strong>
          </div>
        </div>
      </section>

      <section class="vendor-grid">
        <div class="left-col">
          <article class="card vendor-card">
            <h2>About {{ vendorProfile.name }}</h2>
            <p>
              Luxe Bloom Florals is an award-winning boutique floral design studio creating
              botanical experiences for weddings, cultural ceremonies, and corporate events.
            </p>
            <p>
              We deliver planning support, floral direction, styling, and event-day
              coordination for both intimate and large-scale celebrations.
            </p>

            <div class="stat-grid">
              <div v-for="stat in stats" :key="stat.label" class="stat-item">
                <small>{{ stat.label }}</small>
                <strong>{{ stat.value }}</strong>
              </div>
            </div>
          </article>

          <article class="card vendor-card">
            <h2>Packages & Services</h2>
            <p v-if="catalogItems.length === 0">No live vendor services have been posted yet.</p>
            <div class="catalog-grid">
              <div v-for="item in catalogItems" :key="item.id" class="catalog-item">
                <strong>{{ item.title }}</strong>
                <p>{{ item.description }}</p>
              </div>
            </div>
          </article>
        </div>

        <aside class="right-col">
          <article class="card side-card">
            <h3>Contact Details</h3>
            <p><strong>Phone</strong><br />+1 (555) 234-5678</p>
            <p><strong>Email</strong><br />hello@luxebloom.com</p>
            <p><strong>Website</strong><br />www.luxebloom.com</p>
          </article>

          <article class="card side-card">
            <h3>Location</h3>
            <p>{{ fallbackVendorLocation }}</p>
            <iframe
              class="map-frame"
              :src="mapEmbedUrl"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
            <a class="map-link" :href="mapLinkUrl" target="_blank" rel="noopener noreferrer">
              Open in Google Maps
            </a>
          </article>

          <article class="card side-card review-card">
            <h3>Recent Reviews</h3>
            <div v-for="review in reviews" :key="review.name" class="review-item">
              <strong>{{ review.name }}</strong>
              <p>{{ review.text }}</p>
            </div>
          </article>
        </aside>
      </section>
    </main>
  </div>
</template>

<style scoped>
.vendor-page {
  min-height: 100vh;
}

.vendor-shell {
  width: min(1080px, calc(100% - 2rem));
  margin: 0 auto;
  padding: 12px 0 26px;
}

.breadcrumbs {
  margin: 0 0 12px;
  font-size: 14px;
  color: #7187a4;
}

.vendor-hero {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  height: 200px;
  border: 1px solid #c9d6e8;
}

.vendor-hero-bg {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.vendor-hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(10, 20, 35, 0.62), rgba(10, 20, 35, 0.24));
}

.vendor-hero-main {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: end;
  gap: 14px;
  padding: 18px;
  color: #fff;
}

.vendor-badge {
  width: 56px;
  height: 56px;
  border-radius: 9px;
  background: rgba(255, 255, 255, 0.9);
  color: #334155;
  font-size: 9px;
  font-weight: 800;
  line-height: 1.2;
  text-align: center;
  display: grid;
  place-items: center;
}

.vendor-hero-copy h1 {
  margin: 0;
  font-size: clamp(2rem, 4.2vw, 3.4rem);
  line-height: 1;
}

.vendor-hero-copy p {
  margin: 6px 0 4px;
  font-size: 17px;
  opacity: 0.96;
}

.vendor-hero-copy strong {
  font-size: 16px;
}

.vendor-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr 268px;
  gap: 12px;
  align-items: start;
}

.left-col,
.right-col {
  display: grid;
  gap: 12px;
}

.vendor-card,
.side-card {
  border-radius: 14px;
  border: 1px solid #ccd7e8;
  background: #fff;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
  padding: 14px;
}

.vendor-card h2,
.side-card h3 {
  margin: 0 0 10px;
  font-size: 20px;
  line-height: 1.1;
}

.vendor-card p,
.side-card p {
  margin: 0 0 10px;
  color: #64748b;
  font-size: 15px;
  line-height: 1.45;
}

.side-card p:last-child {
  margin-bottom: 0;
}

.side-card strong {
  color: #1e293b;
}

.map-frame {
  width: 100%;
  height: 170px;
  border: 1px solid #d7e1ef;
  border-radius: 10px;
  margin-top: 4px;
}

.map-link {
  display: inline-block;
  margin-top: 8px;
  font-size: 13px;
  font-weight: 700;
  color: #c2410c;
}

.stat-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 8px;
}

.stat-item {
  border: 1px solid #ccd7e8;
  border-radius: 10px;
  padding: 9px 10px;
  background: #fbfdff;
  text-align: center;
}

.stat-item small {
  display: block;
  font-size: 10px;
  color: #7b8ba3;
  text-transform: uppercase;
}

.stat-item strong {
  display: block;
  margin-top: 2px;
  font-size: 14px;
}

.catalog-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.catalog-item {
  border: 1px solid #ccd7e8;
  border-radius: 10px;
  background: #fff;
  padding: 10px 11px;
}

.catalog-item strong {
  display: block;
  font-size: 15px;
  line-height: 1.25;
  color: #1f2937;
}

.catalog-item p {
  margin: 5px 0 0;
  font-size: 13px;
  color: #64748b;
  line-height: 1.35;
}

.review-card {
  padding-bottom: 2px;
}

.review-item {
  padding: 10px 0;
  border-top: 1px solid #dde5f2;
}

.review-item strong {
  display: block;
  color: #1f2937;
}

.review-item p {
  margin: 4px 0 0;
  font-size: 13px;
}

@media (max-width: 980px) {
  .vendor-grid {
    grid-template-columns: 1fr;
  }

  .vendor-hero {
    height: 220px;
  }

  .vendor-card h2,
  .side-card h3 {
    font-size: 20px;
  }

  .vendor-card p,
  .side-card p {
    font-size: 15px;
  }

  .vendor-hero-copy strong {
    font-size: 16px;
  }

  .stat-grid,
  .catalog-grid {
    grid-template-columns: 1fr;
  }

  .vendor-hero-copy h1 {
    font-size: clamp(1.6rem, 9vw, 2.1rem);
  }

  .vendor-hero-copy p {
    font-size: 15px;
  }
}
</style>
