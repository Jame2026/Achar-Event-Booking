<template>
  <About />
  <main class="page-content">
    <Home />
    <Package />
    <ViewSection />
  </main>
</template>

<script>
import About from './components/about.vue'
import Home from './components/home.vue'
import Package from './components/package.vue'
import ViewSection from './components/view.vue'

export default {
  components: {
    About,
    Home,
    Package,
    ViewSection
  }
}
</script>

<style scoped>
.page-content {
  padding: 110px 1.25rem 2.5rem;
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  gap: 1.25rem;
}

@media (max-width: 768px) {
  .page-content {
    padding-top: 90px;
  }
}
</style>
