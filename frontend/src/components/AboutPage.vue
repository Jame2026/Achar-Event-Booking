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
    detail:
      'Full-stage design, lighting, and on-site coordination so every procession looks cinematic. Includes vendor hand-off notes and a clear timeline.',
    image: '/W1.png',
  },
  {
    title: 'Vendor Network',
    text: 'Trusted venues, decorators, caterers, and planners in one platform.',
    detail:
      'Shortlisted partners with verified reviews, preferred rates, and defined scopes. Compare side-by-side and lock dates faster.',
    image: '/W2.png',
  },
  {
    title: 'Smart Booking Flow',
    text: 'From service discovery to checkout and confirmation without friction.',
    detail:
      'Guided steps with add-ons, payments, and reminders in one flow. Shared itineraries keep guests, vendors, and planners aligned.',
    image: '/RE.png',
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

const expandedCard = ref(null)

function toggleCard(index) {
  expandedCard.value = expandedCard.value === index ? null : index
}
</script>
<template>
  <div class="about-page-root">
    <PublicNavbar />
    <main class="shell about-content">
      <section class="about-banner card">
        <img class="about-banner-watermark" :src="appLogoSrc" alt="Achar emblem" @error="onLogoError" />
        <div class="about-banner-content">
          <p class="about-chip">{{ uiText.chip }}</p>
          <h1 class="about-banner-title">
            <span class="about-banner-line">
              {{ uiText.titleA }}
            </span>
            <span class="about-banner-line">
              <span>{{ uiText.titleB }}</span> {{ uiText.titleC }}
            </span>
          </h1>
          <p class="about-banner-lede">
            {{ uiText.intro }}
          </p>
          <div class="about-banner-signals" aria-label="Platform strengths">
            <span>{{ uiText.t2 }}</span>
            <span>{{ uiText.t1 }}</span>
            <span>{{ uiText.t3 }}</span>
          </div>

          <div class="about-hero-features" aria-label="Modern features">
            <article>
              <span class="feature-icon">🔒</span>
              <strong>Secure Booking</strong>
              <small>Encrypted payments and verified vendor protection.</small>
            </article>
            <article>
              <span class="feature-icon">⚡</span>
              <strong>Rapid Proposals</strong>
              <small>Instant quotes and real-time availability checks.</small>
            </article>
            <article>
              <span class="feature-icon">🧠</span>
              <strong>Smart Matches</strong>
              <small>AI-driven suggestions tailored for your event profile.</small>
            </article>
            <article>
              <span class="feature-icon">🤝</span>
              <strong>Trusted Partners</strong>
              <small>Verified vendors with high ratings across categories.</small>
            </article>
          </div>

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
          <div class="about-visual-image-wrapper">
            <img :src="card.image" :alt="card.title" loading="lazy" />
            <div class="about-visual-image-filter"></div>
            <div class="about-visual-category">{{ index === 0 ? uiText.featured : uiText.why }}</div>
          </div>
          <div class="about-visual-body">
            <h3>{{ card.title }}</h3>
            <p class="about-visual-summary">{{ card.text }}</p>
            <transition name="about-fade">
              <p v-if="expandedCard === index" class="about-visual-detail">
                {{ card.detail }}
              </p>
            </transition>
            <button class="about-visual-cta" type="button" @click="toggleCard(index)">
              {{ expandedCard === index ? 'Hide details' : 'Discover more' }}
              <span aria-hidden="true">→</span>
            </button>
          </div>
        </article>
      </section>

      <section class="about-story card">
        <div class="about-story-copy">
          <p class="about-hero-kicker">{{ uiText.why }}</p>
          <h2>{{ uiText.modernPlatform }}</h2>
          <p>
            {{ uiText.heroText }}
          </p>
          <div class="about-story-tags">
            <span>{{ uiText.t1 }}</span>
            <span>{{ uiText.t2 }}</span>
            <span>{{ uiText.t3 }}</span>
            <span>{{ uiText.t4 }}</span>
          </div>
        </div>

        <aside class="about-story-panel">
          <div class="about-story-orbit">
            <span></span>
            <span></span>
            <img class="about-story-banner-logo" :src="appLogoSrc" alt="" @error="onLogoError" />
          </div>
          <div class="about-story-badge">
            <strong>{{ highlights[0].value }}</strong>
            <p>{{ highlights[0].label }}</p>
          </div>
          <div class="about-story-note">
            <p>{{ uiText.missionTitle }}</p>
            <strong>{{ uiText.t2 }}</strong>
            <span>{{ uiText.t4 }}</span>
          </div>
        </aside>
      </section>

      <section class="about-grid about-grid-elevated">
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

      <section class="about-whats-new card">
        <h2>What’s New + Why clients love us</h2>
        <div class="about-whats-new-grid">
          <article>
            <span>🌈</span>
            <h3>Vibrant UI with clear actions</h3>
            <p>See the full process with interactive item spans and immediate feedback.</p>
          </article>
          <article>
            <span>⚙️</span>
            <h3>Real-time smart suggestions</h3>
            <p>Our app now recommends the best vendors and packages as you browse.</p>
          </article>
          <article>
            <span>📱</span>
            <h3>Mobile-friendly experience</h3>
            <p>The page adapts to any device with better spacing, scalable cards and bold colors.</p>
          </article>
        </div>
      </section>

      <section class="card about-mission">
        <div class="about-mission-copy">
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
        </div>
        <div class="about-mission-accent" aria-hidden="true">
          <div class="about-mission-ring"></div>
          <div class="about-mission-ring"></div>
          <div class="about-mission-core">
            <img :src="appLogoSrc" alt="" @error="onLogoError" />
          </div>
        </div>
      </section>
      <section class="about-stats">
        <article
          class="card stat-item"
          v-for="item in highlights"
          :key="item.label"
        >
          <span class="stat-line"></span>
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
  background: linear-gradient(180deg, #f9fbff 0%, #fbf6f0 100%);
}

.about-content {
  padding: 1.2rem 0 2.3rem;
  max-width: 1240px;
  margin: 0 auto;
}

.about-banner {
  position: relative;
  overflow: hidden;
  padding: 3.4rem 2rem 3.5rem;
  border-radius: 38px;
  background:
    radial-gradient(circle at 50% 18%, rgba(244, 189, 116, 0.08), transparent 22%),
    linear-gradient(180deg, #fbf6f1, #f9f4ee);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.82),
    0 10px 30px rgba(92, 72, 49, 0.04);
  animation: aboutFadeUp 0.55s ease both;
}

.about-banner-watermark {
  position: absolute;
  left: 50%;
  top: 50%;
  width: min(440px, 42%);
  transform: translate(-50%, -50%);
  opacity: 0.18;
  filter: saturate(1.08) contrast(1.02);
  pointer-events: none;
}

.about-banner-content {
  position: relative;
  z-index: 1;
  max-width: 980px;
  margin: 0 auto;
  display: grid;
  justify-items: center;
  text-align: center;
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
  border: 1px solid #f6caa7;
  border-radius: 999px;
  padding: 0.3rem 0.82rem;
  background: rgba(255, 244, 234, 0.92);
  color: #c45a12;
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.about-banner-title {
  margin: 0;
  font-family: Georgia, "Times New Roman", serif;
  font-size: clamp(2.0rem, 4.2vw, 3.7rem);
  line-height: 0.98;
  letter-spacing: -0.03em;
  max-width: 16ch;
  color: #0f172a;
  display: grid;
  justify-items: center;
  justify-content: center;
  margin-left: auto;
  margin-right: auto;
  text-align: center;
}

.about-banner-line {
  display: block;
  white-space: nowrap;
  width: fit-content;
  margin-left: auto;
  margin-right: auto;
}

.about-banner-line + .about-banner-line {
  margin-top: -0.03em;
}

.about-banner-title span span {
  color: #ff6a00;
  font-style: italic;
  text-shadow: 0 10px 24px rgba(255, 106, 0, 0.16);
}

.about-banner-lede {
  margin: 1.35rem 0 0;
  max-width: 640px;
  color: #66748b;
  font-size: clamp(1.02rem, 1.6vw, 1.2rem);
  line-height: 1.5;
}

.about-banner-signals {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
  justify-content: center;
}

.about-banner-signals span {
  border: 1px solid rgba(196, 90, 18, 0.12);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.7);
  color: #9a5d28;
  font-size: 0.74rem;
  font-weight: 700;
  padding: 0.32rem 0.68rem;
}

.about-hero-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 0.72rem;
  margin: 1rem auto 1.8rem;
  width: min(980px, 100%);
  justify-content: center;
}

.about-hero-features article {
  border-radius: 18px;
  border: 1px solid rgba(255, 136, 26, 0.2);
  padding: 0.86rem;
  background: rgba(255, 255, 255, 0.85);
  box-shadow: 0 8px 22px rgba(26, 26, 56, 0.1);
  text-align: center;
  transition: transform 0.24s ease, box-shadow 0.24s ease;
}

.about-hero-features article:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 34px rgba(26, 26, 56, 0.16);
}

.feature-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 999px;
  background: linear-gradient(135deg, #ff9f45, #ff6b00);
  color: #fff;
  font-size: 1rem;
  margin: 0 auto 0.45rem;
  box-shadow: 0 8px 14px rgba(255, 119, 31, 0.4);
}

.about-hero-features strong {
  display: block;
  color: #22304f;
  font-size: 0.93rem;
  margin-bottom: 0.25rem;
}

.about-hero-features small {
  color: #5f6f8a;
  font-size: 0.78rem;
}

.about-banner-cta {
  margin-top: 1.7rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  min-height: 64px;
  padding: 0 1rem 0 1.9rem;
  border-radius: 999px;
  background: linear-gradient(145deg, #ff8a2b, #ff6a00);
  color: #fff;
  font-weight: 800;
  font-size: 1.02rem;
  box-shadow: 0 18px 36px rgba(255, 106, 0, 0.22);
  position: relative;
  overflow: hidden;
}

.about-banner-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 18px 34px rgba(255, 106, 0, 0.34);
}

.about-banner-cta::before {
  content: '›';
  order: 2;
  display: inline-grid;
  place-items: center;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.22);
  font-size: 1.1rem;
  line-height: 1;
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

.about-story {
  position: relative;
  margin-top: 1.4rem;
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.7fr);
  align-items: stretch;
  gap: 1.2rem;
  padding: 1.5rem;
  border-radius: 34px;
  border: 1px solid rgba(219, 232, 246, 0.95);
  background:
    linear-gradient(115deg, rgba(255,242,227,0.88), rgba(249,250,255,0.93)),
    radial-gradient(circle at 100% 0%, rgba(255, 127, 0, 0.12), transparent 38%),
    radial-gradient(circle at 0% 100%, rgba(43, 122, 255, 0.15), transparent 30%);
  box-shadow: 0 16px 40px rgba(17, 24, 39, 0.14);
  overflow: hidden;
}

.about-story::before {
  content: '';
  position: absolute;
  inset: auto auto -110px -70px;
  width: 240px;
  height: 240px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 106, 0, 0.1), transparent 68%);
  pointer-events: none;
}

.about-story::after {
  content: '';
  position: absolute;
  top: 0;
  right: 28%;
  width: 1px;
  height: 100%;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(148, 163, 184, 0.25), rgba(255, 255, 255, 0));
  pointer-events: none;
}

.about-story-copy {
  position: relative;
  z-index: 1;
  display: grid;
  align-content: start;
  padding-right: 0.8rem;
}

.about-story h2 {
  margin: 0;
  font-size: clamp(1.8rem, 3vw, 2.9rem);
  line-height: 1.15;
  max-width: 14ch;
}

.about-story p {
  margin: 0.65rem 0 0;
  max-width: 62ch;
  color: #475569;
  line-height: 1.64;
}

.about-story-tags {
  margin-top: 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
}

.about-story-tags span {
  border: 1px solid rgba(255, 106, 0, 0.12);
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.82);
  color: #9a4f11;
  font-size: 0.78rem;
  font-weight: 800;
  padding: 0.35rem 0.75rem;
}

.about-story-panel {
  position: relative;
  border-radius: 28px;
  padding: 1.2rem;
  background:
    linear-gradient(150deg, rgba(29, 78, 216, 0.88), rgba(10, 14, 37, 0.85));
  color: #fff;
  display: grid;
  align-content: space-between;
  gap: 0.8rem;
  box-shadow: 0 22px 52px rgba(15, 23, 42, 0.2);
}

.about-story-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 50% 16%, rgba(255, 122, 38, 0.12), transparent 12%),
    repeating-radial-gradient(circle at 50% 16%, rgba(255, 255, 255, 0.07) 0 1px, transparent 1px 44px);
  opacity: 0.6;
  pointer-events: none;
}

.about-story-orbit {
  position: relative;
  display: grid;
  place-items: center;
  min-height: 170px;
}

.about-story-orbit span {
  position: absolute;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.16);
}

.about-story-orbit span:nth-child(1) {
  width: 164px;
  height: 164px;
}

.about-story-orbit span:nth-child(2) {
  width: 122px;
  height: 122px;
  border-color: rgba(255, 166, 102, 0.34);
}

.about-story-banner-logo {
  position: relative;
  z-index: 1;
  width: 86px;
  height: 86px;
  object-fit: contain;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.99), rgba(250, 244, 238, 0.95));
  padding: 12px;
  box-shadow:
    0 14px 30px rgba(0, 0, 0, 0.22),
    0 0 0 12px rgba(255, 255, 255, 0.04);
  filter: saturate(1.04) contrast(1.02);
}

.about-story-badge,
.about-story-note {
  position: relative;
  z-index: 1;
  border-radius: 18px;
  padding: 0.8rem 0.9rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.about-story-badge strong {
  display: block;
  font-size: 2rem;
  line-height: 1;
  color: #fff5eb;
}

.about-story-badge p,
.about-story-note p,
.about-story-note span {
  margin: 0.35rem 0 0;
  color: rgba(226, 232, 240, 0.8);
}

.about-story-note p {
  margin-top: 0;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.about-story-note strong {
  display: block;
  margin-top: 0.3rem;
  color: #fff;
  font-size: 1.1rem;
}

.about-visuals {
  margin-top: 1.4rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.2rem;
  align-items: stretch;
}

.about-visual-card {
  display: grid;
  grid-template-rows: 260px 1fr;
  height: 100%;
  overflow: hidden;
  border-radius: 30px;
  border: 1px solid rgba(204, 214, 231, 0.9);
  background: #fff;
  box-shadow: 0 12px 36px rgba(22, 34, 70, 0.08);
  transition: transform 0.24s ease, box-shadow 0.24s ease, border-color 0.24s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-visual-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255, 106, 0, 0.4);
  box-shadow: 0 18px 44px rgba(22, 34, 70, 0.18);
}

.about-visual-image-wrapper {
  position: relative;
  overflow: hidden;
  height: 100%;
  min-height: 220px;
}

.about-visual-image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s ease;
}

.about-visual-card:hover .about-visual-image-wrapper img {
  transform: scale(1.05);
}

.about-visual-image-filter {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(15, 23, 42, 0.45), rgba(23, 55, 105, 0.03));
}

.about-visual-category {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 0.2rem 0.62rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.8);
  color: #1f2d4b;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.about-visual-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  height: 100%;
}

.about-visual-body h3 {
  margin: 0;
  font-size: 1.55rem;
  line-height: 1.1;
  color: #122444;
}

.about-visual-summary {
  margin: 0;
  color: #5b708d;
  font-size: 0.95rem;
  line-height: 1.6;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.about-visual-detail {
  margin: 0.15rem 0 0;
  color: #3c4b68;
  font-size: 0.9rem;
  line-height: 1.55;
  background: #f8fbff;
  border: 1px solid #e5edf9;
  border-radius: 14px;
  padding: 0.6rem 0.75rem;
}

.about-visual-cta {
  margin-top: auto;
  align-self: flex-start;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.55rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 118, 0, 0.28);
  background: linear-gradient(135deg, #fff7ef, #fffaf5);
  color: #ff7300;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  text-transform: uppercase;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.about-visual-cta:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 118, 0, 0.5);
  box-shadow: 0 10px 26px rgba(255, 118, 0, 0.16);
}


.about-visual-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 48px rgba(17, 24, 39, 0.22);
  border-color: rgba(255, 118, 0, 0.34);
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
  font-size: 1.9rem;
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
  font-size: 1.85rem;
}

.about-visual-card:hover {
  transform: translateY(-4px);
  border-color: #cdddf2;
  box-shadow: 0 14px 34px rgba(15, 23, 42, 0.1);
}

.about-visual-card:hover img {
  transform: scale(1.03);
}

.about-fade-enter-active,
.about-fade-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.about-fade-enter-from,
.about-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

.about-grid {
  margin-top: 1.2rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.1rem;
  align-items: stretch;
}

.about-whats-new {
  margin-top: 1rem;
  padding: 1.2rem;
  border-radius: 28px;
  background: linear-gradient(135deg, #fff8f1, #f2f9ff);
  border: 1px solid #e3eaf7;
  box-shadow: 0 12px 28px rgba(17, 24, 39, 0.08);
}

.about-whats-new h2 {
  margin: 0 0 0.9rem;
  font-size: clamp(1.35rem, 2.2vw, 1.8rem);
  color: #1a2b54;
}

.about-whats-new-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
}

.about-whats-new-grid article {
  padding: 0.9rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid #dfe7f3;
  box-shadow: 0 10px 26px rgba(17, 24, 39, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.about-whats-new-grid article:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 30px rgba(17, 24, 39, 0.14);
}

.about-whats-new-grid article span {
  display: inline-flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #ff7f1c;
  color: white;
  margin-bottom: 0.45rem;
  font-size: 1.05rem;
}

.about-whats-new-grid article h3 {
  margin: 0;
  font-size: 1.03rem;
  color: #1b2e53;
}

.about-whats-new-grid article p {
  margin: 0.48rem 0 0;
  color: #4f627e;
  font-size: 0.92rem;
  line-height: 1.5;
}

.about-grid-elevated {
  margin-top: 0.55rem;
  position: relative;
  z-index: 1;
}

.about-block {
  position: relative;
  overflow: hidden;
  min-height: 230px;
  padding: 1.2rem;
  border-radius: 24px;
  border: 1px solid rgba(221, 231, 246, 0.9);
  background:
    radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.1), transparent 35%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 251, 255, 0.94));
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
}

.about-block::before {
  content: '';
  position: absolute;
  top: 0;
  left: 1.2rem;
  width: 52px;
  height: 4px;
  border-radius: 999px;
  background: linear-gradient(90deg, #ff9a3a, #ff6a00);
}

.about-block:nth-child(1) {
  padding-top: 1.5rem;
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.14), transparent 34%),
    linear-gradient(180deg, #fffdf9, #fff7ef);
}

.about-block:nth-child(2) {
  transform: none;
}

.about-block:nth-child(3) {
  transform: none;
  background:
    radial-gradient(circle at 100% 0%, rgba(37, 99, 235, 0.12), transparent 34%),
    linear-gradient(180deg, #fdfefe, #f4f9ff);
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
  position: relative;
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(220px, 0.7fr);
  align-items: center;
  gap: 1rem;
  padding: 1.35rem;
  border-radius: 26px;
  border: 1px solid #dde7f6;
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 106, 0, 0.14), transparent 28%),
    linear-gradient(135deg, #ffffff, #f9fcff 58%, #fff8f1);
  animation: aboutFadeUp 0.55s ease both;
  overflow: hidden;
}

.about-mission::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(120deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.55) 34%, rgba(255, 255, 255, 0) 68%);
  opacity: 0.45;
  pointer-events: none;
}

.about-mission-copy {
  position: relative;
  z-index: 1;
}

.about-mission h2 {
  margin: 0;
  font-size: clamp(1.7rem, 2.6vw, 2.2rem);
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

.about-mission-accent {
  position: relative;
  min-height: 210px;
  display: grid;
  place-items: center;
}

.about-mission-ring {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(255, 106, 0, 0.15);
}

.about-mission-ring:nth-child(1) {
  width: 210px;
  height: 210px;
}

.about-mission-ring:nth-child(2) {
  width: 138px;
  height: 138px;
  border-color: rgba(15, 23, 42, 0.12);
}

.about-mission-core {
  display: grid;
  place-items: center;
  width: 112px;
  height: 112px;
  border-radius: 50%;
  background:
    radial-gradient(circle at center, rgba(255, 255, 255, 0.98), rgba(255, 247, 239, 0.92));
  box-shadow: 0 18px 38px rgba(255, 106, 0, 0.24);
  overflow: hidden;
}

.about-mission-core img {
  width: 82%;
  height: 82%;
  object-fit: contain;
  filter: saturate(1.08);
}

.about-stats {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.8rem;
  align-items: start;
}
.stat-item {
  position: relative;
  padding: 1.15rem 0.85rem;
  text-align: center;
  border-radius: 22px;
  border: 1px solid #dce7f5;
  background:
    radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.1), transparent 42%),
    linear-gradient(180deg, #ffffff, #f8fbff);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  animation: aboutFadeUp 0.55s ease both;
  overflow: hidden;
}

.stat-item:nth-child(2) {
  transform: translateY(10px);
}

.stat-item:nth-child(3) {
  transform: translateY(20px);
}

.stat-item:nth-child(4) {
  transform: translateY(10px);
}

.stat-item:nth-child(1) {
  background:
    radial-gradient(circle at 50% 0%, rgba(255, 106, 0, 0.14), transparent 42%),
    linear-gradient(180deg, #ffffff, #fff9f3);
}

.stat-item:nth-child(2) {
  background:
    radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.12), transparent 42%),
    linear-gradient(180deg, #ffffff, #f6fbff);
}

.stat-item:nth-child(3) {
  background:
    radial-gradient(circle at 50% 0%, rgba(14, 165, 233, 0.12), transparent 42%),
    linear-gradient(180deg, #ffffff, #f5fcff);
}

.stat-item:nth-child(4) {
  background:
    radial-gradient(circle at 50% 0%, rgba(245, 158, 11, 0.14), transparent 42%),
    linear-gradient(180deg, #ffffff, #fffbf3);
}

.stat-line {
  display: block;
  width: 42px;
  height: 4px;
  margin: 0 auto 0.8rem;
  border-radius: 999px;
  background: linear-gradient(90deg, #ff9a3a, #ff6a00);
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
  margin-top: 1.2rem;
  padding: 1.25rem;
  border-radius: 26px;
  border: 1px solid #e3eefb;
  background: linear-gradient(180deg, #ffffff, #f8fbff);
  box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08);
}

.about-journey h2 {
  margin: 0;
  font-size: clamp(1.4rem, 2.1vw, 2rem);
  color: #1f2a46;
}

.about-journey-grid {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
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

.about-block:nth-child(2):hover {
  transform: translateY(-3px);
}

.about-block:nth-child(3):hover {
  transform: translateY(-3px);
}

.stat-item:nth-child(2):hover {
  transform: translateY(6px);
}

.stat-item:nth-child(3):hover {
  transform: translateY(16px);
}

.stat-item:nth-child(4):hover {
  transform: translateY(6px);
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
    padding: 2.5rem 1.2rem 2.6rem;
  }

  .about-story,
  .about-mission {
    grid-template-columns: 1fr;
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

  .about-grid-elevated {
    margin-top: 0.9rem;
  }

  .about-block,
  .about-block:nth-child(1),
  .about-block:nth-child(2),
  .about-block:nth-child(3),
  .stat-item,
  .stat-item:nth-child(2),
  .stat-item:nth-child(3),
  .stat-item:nth-child(4) {
    transform: none;
    min-height: auto;
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
    width: min(320px, 82%);
    opacity: 0.18;
  }

  .about-banner-title {
    line-height: 1.02;
    max-width: none;
  }

  .about-banner-signals {
    display: none;
  }

  .about-stats {
    grid-template-columns: 1fr;
  }
}
</style>
