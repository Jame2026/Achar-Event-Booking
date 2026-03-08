<script setup>
import { computed, ref } from 'vue'
import PublicNavbar from './PublicNavbar.vue'
import { useLanguage } from '../features/language'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const { language } = useLanguage()

const copyByLanguage = {
  en: {
    chip: 'About Achar Event Booking',
    titleA: 'Your Perfect Event,',
    titleB: 'Orchestrated',
    titleC: 'to Perfection',
    intro:
      'Discover and book trusted vendors, venues, and specialists for both traditional and modern celebrations.',
    cta: 'Explore Services & Packages',
    featured: 'Featured Experience',
    why: 'Why Achar',
    modernPlatform: 'A modern platform for seamless event planning',
    heroText:
      'Achar helps people plan celebrations and professional events by matching them with the right services. Instead of handling vendors across scattered channels, users can discover options, compare packages, customize services, and confirm bookings in one workflow.',
    missionTitle: 'What this platform delivers',
    missionText:
      'The goal is to make event planning reliable, transparent, and less stressful. Clients get better control over budget, schedule, and service quality. Vendors get a clearer channel to showcase offerings, engage customers, and convert interest into confirmed bookings.',
    t1: 'Transparent Pricing',
    t2: 'Reliable Vendors',
    t3: 'Faster Booking',
    t4: 'Better Visibility',
    journey: 'How Achar Works',
  },
  km: {
    chip: 'អំពី Achar Event Booking',
    titleA: 'ព្រឹត្តិការណ៍ដ៏ល្អឥតខ្ចោះរបស់អ្នក,',
    titleB: 'រៀបចំ',
    titleC: 'យ៉ាងល្អឥតខ្ចោះ',
    intro:
      'ស្វែងរក និងកក់អ្នកផ្គត់ផ្គង់ ទីតាំង និងអ្នកជំនាញដែលទុកចិត្តបាន សម្រាប់ពិធីប្រពៃណី និងទំនើប។',
    cta: 'ស្វែងរកសេវាកម្ម និងកញ្ចប់',
    featured: 'បទពិសោធន៍ពិសេស',
    why: 'ហេតុអ្វី Achar',
    modernPlatform: 'វេទិកាទំនើបសម្រាប់រៀបចំព្រឹត្តិការណ៍បានរលូន',
    heroText:
      'Achar ជួយមនុស្សរៀបចំពិធី និងព្រឹត្តិការណ៍អាជីព ដោយផ្គូផ្គងជាមួយសេវាកម្មត្រឹមត្រូវ។ អ្នកអាចស្វែងរក ប្រៀបធៀប កែតម្រូវ និងបញ្ជាក់ការកក់បានក្នុងដំណើរការតែមួយ។',
    missionTitle: 'អ្វីដែលវេទិកានេះផ្តល់ជូន',
    missionText:
      'គោលដៅគឺធ្វើឱ្យការរៀបចំព្រឹត្តិការណ៍មានភាពជឿទុកចិត្ត បើកចំហ និងកាត់បន្ថយសម្ពាធ។ អតិថិជនគ្រប់គ្រងថវិកា និងគុណភាពបានល្អជាងមុន។ អ្នកផ្គត់ផ្គង់ក៏មានឆានែលច្បាស់ក្នុងការបង្ហាញសេវាកម្មផងដែរ។',
    t1: 'តម្លៃបើកចំហ',
    t2: 'អ្នកផ្គត់ផ្គង់គួរឱ្យទុកចិត្ត',
    t3: 'កក់បានលឿន',
    t4: 'មើលឃើញបានច្បាស់',
    journey: 'របៀបដំណើរការ Achar',
  },
  zh: {
    chip: '关于 Achar Event Booking',
    titleA: '您的完美活动，',
    titleB: '精心策划',
    titleC: '到位',
    intro: '发现并预订值得信赖的供应商、场地和专家，适用于传统与现代庆典。',
    cta: '探索服务与套餐',
    featured: '精选体验',
    why: '为什么选择 Achar',
    modernPlatform: '现代化活动策划平台',
    heroText:
      'Achar 通过匹配合适服务，帮助用户高效筹备庆典和专业活动。您可以在一个流程中完成发现、比较、定制与确认预订。',
    missionTitle: '平台带来的价值',
    missionText:
      '我们的目标是让活动策划更可靠、更透明、更省心。客户能更好掌控预算与时间，商家也能更清晰展示服务并提升转化。',
    t1: '透明定价',
    t2: '可靠商家',
    t3: '更快预订',
    t4: '更高可见性',
    journey: 'Achar 如何运作',
  },
}

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en)

function onLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

const platformPillars = [
  {
    title: 'Curated Vendors',
    text: 'Achar connects clients with trusted venues, planners, decorators, caterers, photographers, and more in one place.',
  },
  {
    title: 'Smart Planning Flow',
    text: 'From discovery to customization and final booking, the platform keeps every step organized and easy to manage.',
  },
  {
    title: 'Built for Hosts and Vendors',
    text: 'Clients plan faster while vendors manage inquiries, packages, and bookings with better visibility.',
  },
]

const highlights = [
  { value: '10k+', label: 'Bookings supported' },
  { value: '500+', label: 'Vendor profiles' },
  { value: '25+', label: 'Event categories' },
  { value: '24/7', label: 'Platform access' },
]

const aboutGallery = [
  {
    title: 'Premium Wedding Setup',
    text: 'Elegant stage styling and vendor coordination for memorable celebrations.',
    image: '/event-cards/wedding-stage.jpg',
  },
  {
    title: 'Vendor Network',
    text: 'Trusted venues, decorators, caterers, and planners in one platform.',
    image: '/W1.png',
  },
  {
    title: 'Smart Booking Flow',
    text: 'From service discovery to checkout and confirmation without friction.',
    image: '/event-cards/ceremony-hall.jpg',
  },
]

const journeyCards = [
  {
    step: '01',
    title: 'Discover',
    text: 'Browse packages, services, and professional vendors.',
    image: '/event-cards/engagement-attire.jpg',
  },
  {
    step: '02',
    title: 'Customize',
    text: 'Adjust quantity, add-ons, and event preferences in one flow.',
    image: '/W5.png',
  },
  {
    step: '03',
    title: 'Book',
    text: 'Confirm your event plan with transparent summary and checkout.',
    image: '/event-cards/orange-flowers.jpg',
  },
]

const localizedPillars = computed(() => {
  if (language.value === 'km') {
    return [
      {
        title: 'អ្នកផ្គត់ផ្គង់ជ្រើសរើស',
        text: 'Achar ភ្ជាប់អតិថិជនជាមួយទីតាំង អ្នករៀបចំ ការតុបតែង អាហារ និងថតរូបដែលទុកចិត្តបាន។',
      },
      {
        title: 'ដំណើរការរៀបចំឆ្លាតវៃ',
        text: 'ចាប់ពីស្វែងរកដល់កែតម្រូវ និងកក់ចុងក្រោយ វេទិកានេះធ្វើឱ្យការងារទាំងអស់មានរបៀបរៀបរយ។',
      },
      {
        title: 'សម្រាប់ម្ចាស់ពិធី និងអ្នកផ្គត់ផ្គង់',
        text: 'អតិថិជនរៀបចំបានលឿន ខណៈអ្នកផ្គត់ផ្គង់គ្រប់គ្រងសំណើ និងការកក់បានច្បាស់។',
      },
    ]
  }
  if (language.value === 'zh') {
    return [
      {
        title: '精选商家',
        text: 'Achar 将客户与场地、策划、装饰、餐饮、摄影等可信服务方连接在一个平台。',
      },
      {
        title: '智能策划流程',
        text: '从发现到定制再到最终预订，平台让每一步都清晰易管理。',
      },
      {
        title: '面向客户与商家',
        text: '客户更快完成筹备，商家更清楚地管理咨询、套餐与订单。',
      },
    ]
  }
  return platformPillars
})

const localizedJourneyCards = computed(() => {
  if (language.value === 'km') {
    return [
      { step: '01', title: 'ស្វែងរក', text: 'រកមើលកញ្ចប់ សេវាកម្ម និងអ្នកជំនាញ។', image: '/event-cards/engagement-attire.jpg' },
      { step: '02', title: 'កែតម្រូវ', text: 'កំណត់ចំនួន សេវាបន្ថែម និងចំណូលចិត្ត។', image: '/W5.png' },
      { step: '03', title: 'កក់', text: 'បញ្ជាក់ផែនការ ដោយសង្ខេបច្បាស់ និងការទូទាត់សុវត្ថិភាព។', image: '/event-cards/orange-flowers.jpg' },
    ]
  }
  if (language.value === 'zh') {
    return [
      { step: '01', title: '发现', text: '浏览套餐、服务与专业商家。', image: '/event-cards/engagement-attire.jpg' },
      { step: '02', title: '定制', text: '在同一流程中调整数量、附加项和偏好。', image: '/W5.png' },
      { step: '03', title: '预订', text: '通过清晰摘要与结账确认活动方案。', image: '/event-cards/orange-flowers.jpg' },
    ]
  }
  return journeyCards
})
</script>
<template>
  <div class="about-page-root">
    <PublicNavbar />
    <main class="shell about-content">
      <section class="about-banner card">
        <img class="about-banner-watermark" :src="appLogoSrc" alt="Achar emblem" @error="onLogoError" />
        <div class="about-banner-content">
          <p class="about-chip">{{ uiText.chip }}</p>
          <h1>
            {{ uiText.titleA }}
            <span>{{ uiText.titleB }}</span> {{ uiText.titleC }}
          </h1>
          <p>
            {{ uiText.intro }}
          </p>
          <RouterLink to="/services/packages" class="about-banner-cta">
            {{ uiText.cta }}
          </RouterLink>
        </div>
      </section>

      <section class="about-visuals">
        <article
          v-for="(card, index) in aboutGallery"
          :key="card.title"
          class="card about-visual-card"
          :class="{ 'about-visual-featured': index === 0 }"
        >
          <img :src="card.image" :alt="card.title" loading="lazy" />
          <div>
            <p v-if="index === 0" class="about-chip">{{ uiText.featured }}</p>
            <h3>{{ card.title }}</h3>
            <p>{{ card.text }}</p>
          </div>
        </article>
      </section>

      <section class="about-hero card">
        <p class="about-hero-kicker">{{ uiText.why }}</p>
        <h2>{{ uiText.modernPlatform }}</h2>
        <p>
          {{ uiText.heroText }}
        </p>
      </section>

      <section class="about-grid">
        <article
          class="card about-block"
          v-for="(pillar, index) in localizedPillars"
          :key="pillar.title"
        >
          <span class="about-block-index">0{{ index + 1 }}</span>
          <h2>{{ pillar.title }}</h2>
          <p>{{ pillar.text }}</p>
        </article>
      </section>
      <section class="card about-mission">
        <h2>{{ uiText.missionTitle }}</h2>
        <p>
          {{ uiText.missionText }}
        </p>
        <div class="about-mission-tags">
          <span>{{ uiText.t1 }}</span>
          <span>{{ uiText.t2 }}</span>
          <span>{{ uiText.t3 }}</span>
          <span>{{ uiText.t4 }}</span>
        </div>
      </section>
      <section class="about-stats">
        <article
          class="card stat-item"
          v-for="item in highlights"
          :key="item.label"
        >
          <strong>{{ item.value }}</strong>
          <p>{{ item.label }}</p>
        </article>
      </section>

      <section class="card about-journey">
        <h2>{{ uiText.journey }}</h2>
        <div class="about-journey-grid">
          <article v-for="item in localizedJourneyCards" :key="item.step" class="about-journey-card">
            <img :src="item.image" :alt="item.title" loading="lazy" />
            <div>
              <span>{{ item.step }}</span>
              <h3>{{ item.title }}</h3>
              <p>{{ item.text }}</p>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>
<style scoped>
.about-page-root {
  min-height: 100vh;
}

.about-content {
  padding: 1rem 0 2.2rem;
}

.about-banner {
  position: relative;
  overflow: hidden;
  padding: 2.4rem 2rem 2.2rem;
  border-radius: 30px;
  background:
    radial-gradient(circle at 12% 0%, rgba(255, 106, 0, 0.12), transparent 34%),
    radial-gradient(circle at 86% 0%, rgba(255, 173, 112, 0.18), transparent 36%),
    linear-gradient(180deg, #fffefc, #fff8f2);
  animation: aboutFadeUp 0.55s ease both;
}

.about-banner-watermark {
  position: absolute;
  left: 50%;
  top: 50%;
  width: min(460px, 70%);
  transform: translate(-50%, -50%);
  opacity: 0.16;
  filter: saturate(1.2);
  pointer-events: none;
}

.about-banner-content {
  position: relative;
  z-index: 1;
  max-width: 980px;
}

.about-hero {
  margin-top: 1rem;
  padding: 1.35rem 1.4rem;
  border-radius: 22px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 95% 0%, rgba(255, 106, 0, 0.14), transparent 42%),
    linear-gradient(180deg, #ffffff, #f8fbff);
}

.about-hero-kicker {
  width: fit-content;
  margin: 0;
  border-radius: 999px;
  padding: 0.2rem 0.58rem;
  border: 1px solid #ffd1af;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.about-chip {
  width: fit-content;
  margin: 0;
  border: 1px solid #ffd3b1;
  border-radius: 999px;
  padding: 0.2rem 0.6rem;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.76rem;
  font-weight: 800;
  text-transform: uppercase;
}

.about-banner h1 {
  margin: 0.8rem 0 0;
  font-size: clamp(2.4rem, 5vw, 5.15rem);
  line-height: 0.98;
  letter-spacing: -0.02em;
  max-width: 980px;
  color: #0f172a;
}

.about-banner h1 span {
  color: #ff6a00;
  font-style: italic;
  text-shadow: 0 8px 24px rgba(255, 106, 0, 0.2);
}

.about-banner p {
  margin: 1rem 0 0;
  max-width: 720px;
  color: #475569;
  font-size: clamp(1rem, 2vw, 1.15rem);
  line-height: 1.6;
}

.about-banner-cta {
  margin-top: 1.2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 50px;
  padding: 0 1.5rem;
  border-radius: 999px;
  background: linear-gradient(145deg, #ff8a2b, #ff6a00);
  color: #fff;
  font-weight: 800;
  box-shadow: 0 14px 30px rgba(255, 106, 0, 0.28);
  position: relative;
  overflow: hidden;
}

.about-banner-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 18px 34px rgba(255, 106, 0, 0.34);
}

.about-banner-cta::after {
  content: '';
  position: absolute;
  top: 0;
  left: -120%;
  width: 70%;
  height: 100%;
  background: linear-gradient(100deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0));
  transform: skewX(-22deg);
  transition: left 0.55s ease;
}

.about-banner-cta:hover::after {
  left: 140%;
}

.about-hero h2 {
  margin: 0;
  font-size: clamp(1.6rem, 2.6vw, 2.3rem);
  line-height: 1.15;
}

.about-hero p {
  margin: 0.65rem 0 0;
  max-width: 940px;
  color: #475569;
  line-height: 1.64;
}

.about-visuals {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 0.8rem;
}

.about-visual-card {
  display: grid;
  grid-template-rows: 300px auto;
  align-content: start;
  overflow: hidden;
  padding: 0.9rem;
  border-radius: 28px;
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-visual-featured {
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.12), transparent 30%),
    #fff;
}

.about-visual-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 20px;
  transition: transform 0.35s ease;
}

.about-visual-card h3 {
  margin: 0.75rem 0 0;
  font-size: 2rem;
  line-height: 1.12;
  letter-spacing: -0.01em;
}

.about-visual-card p {
  margin: 0.35rem 0 0;
  color: #64748b;
  font-size: 0.98rem;
  line-height: 1.5;
}

.about-visual-card:not(.about-visual-featured) h3 {
  font-size: 2rem;
}

.about-visual-card:hover {
  transform: translateY(-4px);
  border-color: #cdddf2;
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.1);
}

.about-visual-card:hover img {
  transform: scale(1.03);
}

.about-grid {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}
.about-block {
  padding: 1.1rem;
  border-radius: 20px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.08), transparent 35%),
    linear-gradient(180deg, #ffffff, #f9fcff);
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-block-index {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 24px;
  border-radius: 999px;
  border: 1px solid #ffd3b1;
  background: #fff6ee;
  color: #c2410c;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.03em;
}

.about-block h2 {
  margin: 0.65rem 0 0;
  font-size: 1.35rem;
}

.about-block p {
  margin: 0.5rem 0 0;
  color: #64748b;
  line-height: 1.55;
}
.about-mission {
  margin-top: 0.9rem;
  padding: 1.25rem;
  border-radius: 22px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.12), transparent 32%),
    linear-gradient(180deg, #ffffff, #f9fcff);
  animation: aboutFadeUp 0.55s ease both;
}

.about-mission h2 {
  margin: 0;
  font-size: 1.6rem;
}

.about-mission p {
  margin: 0.6rem 0 0;
  color: #64748b;
  line-height: 1.6;
}

.about-mission-tags {
  margin-top: 0.8rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.about-mission-tags span {
  border: 1px solid #d8e3f4;
  border-radius: 999px;
  background: #fff;
  color: #334155;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.22rem 0.62rem;
}
.about-stats {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.8rem;
}
.stat-item {
  padding: 1.05rem 0.85rem;
  text-align: center;
  border-radius: 18px;
  border: 1px solid #dce7f5;
  background:
    radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.08), transparent 42%),
    #fff;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.stat-item strong {
  display: block;
  font-size: 1.8rem;
}

.stat-item p {
  margin: 0.25rem 0 0;
  color: #64748b;
}

.about-journey {
  margin-top: 0.9rem;
  padding: 1.1rem;
}

.about-journey h2 {
  margin: 0;
  font-size: 1.5rem;
}

.about-journey-grid {
  margin-top: 0.8rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}

.about-journey-card {
  border: 1px solid #e2e8f3;
  border-radius: 14px;
  overflow: hidden;
  background: #fbfdff;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-journey-card img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.about-journey-card div {
  padding: 0.7rem 0.8rem 0.9rem;
}

.about-journey-card span {
  display: inline-flex;
  border-radius: 999px;
  border: 1px solid #ffd0b0;
  background: #fff4ea;
  color: #c2410c;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 0.12rem 0.5rem;
}

.about-journey-card h3 {
  margin: 0.45rem 0 0;
  font-size: 1.08rem;
}

.about-journey-card p {
  margin: 0.4rem 0 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
}

.about-block:hover,
.about-mission:hover,
.stat-item:hover,
.about-journey-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.09);
}

.about-journey-card:hover img {
  transform: scale(1.03);
}

.about-visual-card:nth-child(1),
.about-block:nth-child(1),
.stat-item:nth-child(1),
.about-journey-card:nth-child(1) {
  animation-delay: 0.04s;
}

.about-visual-card:nth-child(2),
.about-block:nth-child(2),
.stat-item:nth-child(2),
.about-journey-card:nth-child(2) {
  animation-delay: 0.1s;
}

.about-visual-card:nth-child(3),
.about-block:nth-child(3),
.stat-item:nth-child(3),
.about-journey-card:nth-child(3) {
  animation-delay: 0.16s;
}

.stat-item:nth-child(4) {
  animation-delay: 0.22s;
}

@keyframes aboutFadeUp {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 980px) {
  .about-banner {
    padding: 1.8rem 1.2rem;
  }

  .about-visuals {
    grid-template-columns: 1fr;
  }

  .about-visual-card {
    grid-template-rows: 220px auto;
  }

  .about-visual-card h3,
  .about-visual-card:not(.about-visual-featured) h3 {
    font-size: 1.65rem;
  }

  .about-grid {
    grid-template-columns: 1fr;
  }

  .about-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .about-journey-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 680px) {
  .about-banner-watermark {
    width: min(360px, 84%);
    opacity: 0.12;
  }

  .about-banner h1 {
    line-height: 1.02;
  }

  .about-stats {
    grid-template-columns: 1fr;
  }
}
</style>
