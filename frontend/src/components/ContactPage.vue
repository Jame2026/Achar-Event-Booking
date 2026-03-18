<script setup>
import { computed, reactive, ref } from 'vue'
import PublicNavbar from './PublicNavbar.vue'
import { useLanguage } from '../features/language'

const successMessage = ref('')
const { language } = useLanguage()

const copyByLanguage = {
  en: {
    chip: 'Contact Achar Event Booking',
    title: 'Plan and book your next event with confidence.',
    intro:
      'Questions about bookings, vendors, or your account? Send us a message and we’ll help you get started.',
    supportTitle: 'Platform Support',
    supportText: 'We can help with bookings, vendor communication, and account issues.',
    email: 'Email',
    phone: 'Phone',
    hours: 'Office Hours',
    address: 'Address',
    sendMessage: 'Send a message',
    fullName: 'Full name',
    emailAddress: 'Email address',
    topic: 'Topic',
    message: 'Message',
    inquiry: 'General inquiry',
    bookingSupport: 'Booking support',
    vendorOnboarding: 'Vendor onboarding',
    technicalIssue: 'Technical issue',
    placeholder: 'Describe your question or concern',
    submit: 'Send Message',
    success: 'Thank you. Your message has been received and our team will contact you soon.',
  },
  km: {
    chip: 'ទំនាក់ទំនង Achar Event Booking',
    title: 'ឱ្យយើងជួយអ្នករៀបចំ និងកក់ព្រឹត្តិការណ៍បន្ទាប់របស់អ្នកដោយទំនុកចិត្ត។',
    intro:
      'មានសំណួរអំពីអ្នកផ្គត់ផ្គង់ ការកក់ កញ្ចប់សេវាកម្ម ឬការបង្កើតគណនី? សូមទាក់ទងក្រុមការងារ។',
    supportTitle: 'ជំនួយវេទិកា',
    supportText: 'ក្រុមគាំទ្ររបស់យើងអាចជួយលំហូរកក់ ការទំនាក់ទំនងអ្នកផ្គត់ផ្គង់ និងបញ្ហាគណនី។',
    email: 'អ៊ីមែល',
    phone: 'ទូរស័ព្ទ',
    hours: 'ម៉ោងការិយាល័យ',
    address: 'អាសយដ្ឋាន',
    sendMessage: 'ផ្ញើសារ',
    fullName: 'ឈ្មោះពេញ',
    emailAddress: 'អាសយដ្ឋានអ៊ីមែល',
    topic: 'ប្រធានបទ',
    message: 'សារ',
    inquiry: 'សំណួរទូទៅ',
    bookingSupport: 'ជំនួយការកក់',
    vendorOnboarding: 'ការចូលរួមអ្នកផ្គត់ផ្គង់',
    technicalIssue: 'បញ្ហាបច្ចេកទេស',
    placeholder: 'ពិពណ៌នាសំណួរ ឬបញ្ហារបស់អ្នក',
    submit: 'ផ្ញើសារ',
    success: 'សូមអរគុណ។ យើងបានទទួលសាររបស់អ្នក ហើយក្រុមការងារនឹងទាក់ទងឆាប់ៗនេះ។',
  },
  zh: {
    chip: '联系 Achar Event Booking',
    title: '让我们帮助您更有信心地策划并预订下一场活动。',
    intro: '若您对商家、预订、服务套餐或账户设置有疑问，请联系我们的团队。',
    supportTitle: '平台支持',
    supportText: '我们的支持团队可协助预订流程、商家沟通和账户问题。',
    email: '邮箱',
    phone: '电话',
    hours: '办公时间',
    address: '地址',
    sendMessage: '发送消息',
    fullName: '姓名',
    emailAddress: '邮箱地址',
    topic: '主题',
    message: '消息',
    inquiry: '一般咨询',
    bookingSupport: '预订支持',
    vendorOnboarding: '商家入驻',
    technicalIssue: '技术问题',
    placeholder: '请描述您的问题或诉求',
    submit: '发送消息',
    success: '感谢您，消息已收到，我们的团队将尽快联系您。',
  },
}

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en)

const form = reactive({
  name: '',
  email: '',
  topic: '',
  message: '',
})

form.topic = uiText.value.inquiry

function submitContactForm() {
  successMessage.value = uiText.value.success
  form.name = ''
  form.email = ''
  form.topic = uiText.value.inquiry
  form.message = ''
}
</script>

<template>
  <div class="contact-page-root">
    <PublicNavbar />

    <main class="shell contact-content">
      <section class="card contact-hero">
        <div class="contact-hero-inner">
          <div class="contact-hero-copy">
            <p class="contact-chip">
              <span class="contact-chip-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path
                    d="M7.5 9.5h9M7.5 12.5h6M9 20.5l-3.5 1.5.9-3.7A8.5 8.5 0 1 1 20.5 12 8.5 8.5 0 0 1 6.4 18.3"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <span>{{ uiText.chip }}</span>
            </p>

            <h1>{{ uiText.title }}</h1>
            <p class="contact-hero-intro">
              {{ uiText.intro }}
            </p>
          </div>
        </div>
      </section>

      <section class="contact-layout">
        <article class="card contact-info">
          <div class="contact-card-head">
            <span class="contact-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M12 3.5a6.5 6.5 0 0 0-6.5 6.5v3.2L4 14.7v.8h16v-.8l-1.5-1.5V10A6.5 6.5 0 0 0 12 3.5Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linejoin="round"
                />
                <path
                  d="M9.5 18a2.5 2.5 0 0 0 5 0"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
              </svg>
            </span>
            <div>
              <h2>{{ uiText.supportTitle }}</h2>
            </div>
          </div>

          <div class="contact-item">
            <span class="contact-item-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M4.5 7.5h15v10h-15v-10Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linejoin="round"
                />
                <path
                  d="m5.5 8.5 6.5 5 6.5-5"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div class="contact-item-copy">
              <small>{{ uiText.email }}</small>
              <strong>support@acharbooking.com</strong>
            </div>
          </div>
          <div class="contact-item">
            <span class="contact-item-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M8.5 5.5 6.8 7.2c-.7.7-.9 1.7-.4 2.6 1.1 2.1 3 4 5.1 5.1.9.5 2 .4 2.6-.4l1.7-1.7c.5-.5 1.3-.6 2-.3l2.3 1.1c.9.4 1.2 1.4.7 2.3l-.7 1.4c-.4.8-1.2 1.3-2.1 1.3-8 0-14.5-6.5-14.5-14.5 0-.9.5-1.7 1.3-2.1l1.4-.7c.9-.5 1.9-.2 2.3.7l1.1 2.3c.3.7.2 1.5-.3 2Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div class="contact-item-copy">
              <small>{{ uiText.phone }}</small>
              <strong>+1 (555) 123-4588</strong>
            </div>
          </div>
          <div class="contact-item">
            <span class="contact-item-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                />
                <path
                  d="M12 7v5l3 2"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div class="contact-item-copy">
              <small>{{ uiText.hours }}</small>
              <strong>Monday - Friday, 9:00 AM - 6:00 PM</strong>
            </div>
          </div>
          <div class="contact-item">
            <span class="contact-item-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M12 21s6-5.1 6-11a6 6 0 0 0-12 0c0 5.9 6 11 6 11Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linejoin="round"
                />
                <path
                  d="M12 12.3a2.3 2.3 0 1 0 0-4.6 2.3 2.3 0 0 0 0 4.6Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                />
              </svg>
            </span>
            <div class="contact-item-copy">
              <small>{{ uiText.address }}</small>
              <strong>1200 Market Street, Suite 410, San Francisco, CA</strong>
            </div>
          </div>
        </article>

        <article class="card contact-form-card">
          <div class="contact-card-head">
            <span class="contact-card-icon contact-card-icon-primary" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path
                  d="M4.5 12.2 20 4.8l-3.6 15.7-5.4-4-3.1 3.2v-4.8l9.6-6.1-11 5.4Z"
                  stroke="currentColor"
                  stroke-width="1.8"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div>
              <h2>{{ uiText.sendMessage }}</h2>
            </div>
          </div>
          <form class="contact-form" @submit.prevent="submitContactForm">
            <div class="contact-form-grid">
              <label class="contact-field">
                <span>{{ uiText.fullName }}</span>
                <input v-model.trim="form.name" type="text" autocomplete="name" placeholder="Your name" required />
              </label>
              <label class="contact-field">
                <span>{{ uiText.emailAddress }}</span>
                <input
                  v-model.trim="form.email"
                  type="email"
                  autocomplete="email"
                  placeholder="you@example.com"
                  required
                />
              </label>
              <label class="contact-field contact-span-2">
                <span>{{ uiText.topic }}</span>
                <select v-model="form.topic">
                  <option>{{ uiText.inquiry }}</option>
                  <option>{{ uiText.bookingSupport }}</option>
                  <option>{{ uiText.vendorOnboarding }}</option>
                  <option>{{ uiText.technicalIssue }}</option>
                </select>
              </label>
              <label class="contact-field contact-span-2">
                <span>{{ uiText.message }}</span>
                <textarea
                  v-model.trim="form.message"
                  rows="5"
                  required
                  :placeholder="uiText.placeholder"
                ></textarea>
              </label>
            </div>

            <button type="submit" class="contact-submit">
              <span class="contact-submit-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <path
                    d="M4.5 12.2 20 4.8l-3.6 15.7-5.4-4-3.1 3.2v-4.8l9.6-6.1-11 5.4Z"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <span>{{ uiText.submit }}</span>
            </button>
            <p v-if="successMessage" class="contact-success">{{ successMessage }}</p>
          </form>
        </article>
      </section>
    </main>
  </div>
</template>

<style scoped>
.contact-page-root {
  min-height: 100vh;
}

.contact-content {
  padding: 1.4rem 0 2.4rem;
}

.contact-hero {
  position: relative;
  overflow: hidden;
  padding: 1.55rem;
  border-radius: 22px;
  border: 1px solid rgba(229, 231, 235, 0.95);
  background:
    radial-gradient(circle at 0% 0%, rgba(255, 107, 0, 0.18), transparent 55%),
    radial-gradient(circle at 100% 100%, rgba(59, 130, 246, 0.1), transparent 55%),
    linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92));
  box-shadow:
    0 34px 86px rgba(15, 23, 42, 0.1),
    0 18px 44px rgba(255, 107, 0, 0.1);
}

.contact-hero::before {
  content: "";
  position: absolute;
  inset: -60px -80px auto auto;
  width: 280px;
  height: 280px;
  border-radius: 999px;
  background: radial-gradient(circle, rgba(255, 107, 0, 0.24), transparent 60%);
  filter: blur(2px);
  pointer-events: none;
}

.contact-hero::after {
  content: "";
  position: absolute;
  inset: auto auto -90px -90px;
  width: 340px;
  height: 340px;
  border-radius: 999px;
  background: radial-gradient(circle, rgba(59, 130, 246, 0.16), transparent 60%);
  pointer-events: none;
}

.contact-hero-inner {
  position: relative;
  z-index: 1;
  display: block;
}

.contact-hero-copy {
  min-width: 0;
}

.contact-chip {
  width: fit-content;
  margin: 0;
  border: 1px solid rgba(255, 107, 0, 0.24);
  border-radius: 999px;
  padding: 0.3rem 0.7rem;
  background: rgba(255, 247, 237, 0.92);
  color: #c2410c;
  font-size: 0.78rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.contact-chip-icon {
  width: 18px;
  height: 18px;
  display: grid;
  place-items: center;
}

.contact-chip-icon svg {
  width: 18px;
  height: 18px;
  display: block;
}

.contact-hero h1 {
  margin: 0.7rem 0 0;
  font-size: clamp(2rem, 3vw, 3rem);
  line-height: 1.05;
}

.contact-hero-intro {
  margin: 0.75rem 0 0;
  max-width: 860px;
  color: var(--muted);
  line-height: 1.7;
  font-size: 1.03rem;
}

.contact-layout {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 1rem;
}

.contact-info,
.contact-form-card {
  padding: 1.2rem;
}

.contact-card-head {
  display: flex;
  gap: 12px;
  align-items: flex-start;
}

.contact-card-icon {
  width: 46px;
  height: 46px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  background:
    linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7)) padding-box,
    linear-gradient(135deg, rgba(59, 130, 246, 0.25), rgba(229, 231, 235, 0.8)) border-box;
  border: 1px solid transparent;
  color: rgba(31, 41, 55, 0.9);
  box-shadow:
    0 18px 40px rgba(15, 23, 42, 0.08),
    0 1px 0 rgba(255, 255, 255, 0.85) inset;
  flex-shrink: 0;
}

.contact-card-icon-primary {
  background:
    linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7)) padding-box,
    linear-gradient(135deg, rgba(255, 107, 0, 0.6), rgba(59, 130, 246, 0.16)) border-box;
}

.contact-card-icon svg {
  width: 20px;
  height: 20px;
  display: block;
}

.contact-info h2,
.contact-form-card h2 {
  margin: 0;
  font-size: 1.55rem;
  line-height: 1.2;
}

.contact-item {
  margin-top: 12px;
  border: 1px solid rgba(229, 231, 235, 0.95);
  border-radius: 18px;
  background:
    radial-gradient(circle at 0% 0%, rgba(255, 107, 0, 0.08), transparent 60%),
    rgba(248, 250, 252, 0.86);
  padding: 12px 12px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  box-shadow: 0 18px 46px rgba(15, 23, 42, 0.06);
}

.contact-item-icon {
  width: 44px;
  height: 44px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background:
    linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.72)) padding-box,
    linear-gradient(135deg, rgba(255, 107, 0, 0.4), rgba(59, 130, 246, 0.14)) border-box;
  border: 1px solid transparent;
  color: rgba(31, 41, 55, 0.9);
  flex-shrink: 0;
}

.contact-item-icon svg {
  width: 18px;
  height: 18px;
  display: block;
}

.contact-item-copy {
  min-width: 0;
  display: grid;
  gap: 6px;
}

.contact-item-copy small {
  color: rgba(107, 114, 128, 0.95);
  text-transform: uppercase;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.1em;
}

.contact-item-copy strong {
  color: rgba(15, 23, 42, 0.92);
  font-size: 1rem;
  font-weight: 800;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contact-form {
  margin-top: 14px;
  display: grid;
  gap: 12px;
}

.contact-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.contact-span-2 {
  grid-column: 1 / -1;
}

.contact-field {
  display: grid;
  gap: 8px;
}

.contact-field span {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(107, 114, 128, 0.95);
}

.contact-form input,
.contact-form select,
.contact-form textarea {
  width: 100%;
  min-height: 48px;
  border: 1px solid rgba(229, 231, 235, 0.95);
  border-radius: 16px;
  padding: 12px 14px;
  font: inherit;
  background: rgba(255, 255, 255, 0.92);
  color: rgba(15, 23, 42, 0.92);
  outline: none;
  box-shadow:
    0 12px 24px rgba(15, 23, 42, 0.06),
    0 1px 0 rgba(255, 255, 255, 0.9) inset;
  transition:
    border-color 0.18s ease,
    box-shadow 0.18s ease,
    transform 0.18s ease;
}

.contact-form input:focus,
.contact-form select:focus,
.contact-form textarea:focus {
  border-color: rgba(255, 107, 0, 0.6);
  box-shadow:
    0 0 0 4px rgba(255, 107, 0, 0.16),
    0 18px 34px rgba(15, 23, 42, 0.1);
}

.contact-form select:hover {
  transform: translateY(-1px);
}

.contact-form select:focus {
  background:
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M7 10l5 5 5-5' stroke='%23c2410c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E")
      no-repeat right 14px center / 18px 18px,
    linear-gradient(rgba(255, 255, 255, 0.98), rgba(255, 255, 255, 0.92)) padding-box,
    linear-gradient(135deg, rgba(255, 107, 0, 0.7), rgba(59, 130, 246, 0.22)) border-box;
}

.contact-form select {
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 46px;
  background:
    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none'%3E%3Cpath d='M7 10l5 5 5-5' stroke='%23334155' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E")
      no-repeat right 14px center / 18px 18px,
    linear-gradient(rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.86)) padding-box,
    linear-gradient(135deg, rgba(255, 107, 0, 0.45), rgba(59, 130, 246, 0.18)) border-box;
  border: 1px solid transparent;
}

.contact-form textarea {
  resize: vertical;
  min-height: 140px;
}

.contact-submit {
  width: 100%;
  min-height: 54px;
  border-radius: 16px;
  border: 1px solid transparent;
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0)) padding-box,
    linear-gradient(90deg, #ff6b00 0%, #ff7a1f 50%, #ff6b00 100%) border-box;
  color: #fff;
  font: inherit;
  font-weight: 800;
  letter-spacing: 0.01em;
  padding: 12px 16px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  box-shadow: 0 18px 44px rgba(255, 107, 0, 0.24);
}

.contact-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 22px 56px rgba(255, 107, 0, 0.28);
}

.contact-submit-icon {
  width: 20px;
  height: 20px;
  display: grid;
  place-items: center;
}

.contact-submit-icon svg {
  width: 20px;
  height: 20px;
  display: block;
}

.contact-success {
  margin: 0;
  border: 1px solid rgba(34, 197, 94, 0.24);
  border-radius: 16px;
  background: rgba(240, 253, 244, 0.92);
  color: #166534;
  padding: 12px 14px;
  font-size: 0.95rem;
  font-weight: 700;
}

@media (max-width: 980px) {
  .contact-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 680px) {
  .contact-hero {
    padding: 1.1rem;
  }

  .contact-hero h1 {
    font-size: 2rem;
  }

  .contact-form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
