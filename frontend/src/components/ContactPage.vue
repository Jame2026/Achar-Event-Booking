<script setup>
import { computed, reactive, ref } from 'vue'
import PublicNavbar from './PublicNavbar.vue'
import { useLanguage } from '../features/language'

const successMessage = ref('')
const { language } = useLanguage()

const copyByLanguage = {
  en: {
    chip: 'Contact Achar Event Booking',
    title: 'Let us help you plan and book your next event with confidence.',
    intro:
      'Have questions about vendors, bookings, service packages, or account setup? Reach out to our team and we will guide you through the best way to use the platform.',
    supportTitle: 'Platform Support',
    supportText:
      'Our support team is available to assist with booking flow, vendor communication, and account issues.',
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
        <p class="contact-chip">💬 {{ uiText.chip }}</p>
        <h1>{{ uiText.title }}</h1>
        <p>
          {{ uiText.intro }}
        </p>
      </section>

      <section class="contact-layout">
        <article class="card contact-info">
          <h2>🛎️ {{ uiText.supportTitle }}</h2>
          <p>{{ uiText.supportText }}</p>

          <div class="contact-item">
            <small>📧 {{ uiText.email }}</small>
            <strong>support@acharbooking.com</strong>
          </div>
          <div class="contact-item">
            <small>📞 {{ uiText.phone }}</small>
            <strong>+1 (555) 123-4588</strong>
          </div>
          <div class="contact-item">
            <small>🕒 {{ uiText.hours }}</small>
            <strong>Monday - Friday, 9:00 AM - 6:00 PM</strong>
          </div>
          <div class="contact-item">
            <small>📍 {{ uiText.address }}</small>
            <strong>1200 Market Street, Suite 410, San Francisco, CA</strong>
          </div>
        </article>

        <article class="card contact-form-card">
          <h2>✉️ {{ uiText.sendMessage }}</h2>
          <form class="contact-form" @submit.prevent="submitContactForm">
            <label>
              👤 {{ uiText.fullName }}
              <input v-model.trim="form.name" type="text" required />
            </label>
            <label>
              📧 {{ uiText.emailAddress }}
              <input v-model.trim="form.email" type="email" required />
            </label>
            <label>
              🗂️ {{ uiText.topic }}
              <select v-model="form.topic">
                <option>{{ uiText.inquiry }}</option>
                <option>{{ uiText.bookingSupport }}</option>
                <option>{{ uiText.vendorOnboarding }}</option>
                <option>{{ uiText.technicalIssue }}</option>
              </select>
            </label>
            <label>
              💬 {{ uiText.message }}
              <textarea
                v-model.trim="form.message"
                rows="5"
                required
                :placeholder="uiText.placeholder"
              ></textarea>
            </label>
            <button type="submit" class="contact-submit">🚀 {{ uiText.submit }}</button>
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
  padding: 1.2rem 0 2.2rem;
}

.contact-hero {
  padding: 1.4rem;
  background:
    radial-gradient(circle at 92% 8%, rgba(255, 106, 0, 0.13), transparent 42%),
    linear-gradient(180deg, #ffffff, #fbfdff);
}

.contact-chip {
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

.contact-hero h1 {
  margin: 0.7rem 0 0;
  font-size: clamp(2rem, 3vw, 3rem);
  line-height: 1.05;
}

.contact-hero p {
  margin: 0.75rem 0 0;
  max-width: 860px;
  color: #475569;
  line-height: 1.64;
}

.contact-layout {
  margin-top: 0.9rem;
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 0.9rem;
}

.contact-info,
.contact-form-card {
  padding: 1.1rem;
}

.contact-info h2,
.contact-form-card h2 {
  margin: 0;
  font-size: 1.5rem;
}

.contact-info p {
  margin: 0.55rem 0 0.8rem;
  color: #64748b;
  line-height: 1.55;
}

.contact-item {
  border: 1px solid #dce5f3;
  border-radius: 12px;
  background: #f8fbff;
  padding: 0.7rem 0.8rem;
  display: grid;
  gap: 0.2rem;
  margin-top: 0.6rem;
}

.contact-item small {
  color: #64748b;
  text-transform: uppercase;
  font-size: 0.74rem;
  font-weight: 700;
}

.contact-item strong {
  color: #1e293b;
  font-size: 0.95rem;
}

.contact-form {
  margin-top: 0.7rem;
  display: grid;
  gap: 0.65rem;
}

.contact-form label {
  display: grid;
  gap: 0.3rem;
  color: #475569;
  font-size: 0.9rem;
}

.contact-form input,
.contact-form select,
.contact-form textarea {
  border: 1px solid #dce4f1;
  border-radius: 11px;
  padding: 0.62rem 0.74rem;
  font: inherit;
  background: #fff;
}

.contact-form textarea {
  resize: vertical;
}

.contact-submit {
  border: 0;
  border-radius: 11px;
  background: #ff6a00;
  color: #fff;
  font: inherit;
  font-weight: 700;
  padding: 0.68rem 0.9rem;
  cursor: pointer;
}

.contact-submit:hover {
  background: #e45800;
}

.contact-success {
  margin: 0.15rem 0 0;
  border: 1px solid #bde6d0;
  border-radius: 10px;
  background: #e9f8ef;
  color: #166534;
  padding: 0.58rem 0.66rem;
  font-size: 0.9rem;
}

@media (max-width: 980px) {
  .contact-layout {
    grid-template-columns: 1fr;
  }
}
</style>
