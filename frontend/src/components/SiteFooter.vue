<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useLanguage } from '../features/language'

const appLogoSrc = ref(localStorage.getItem('achar_brand_logo') || '/achar-logo.png')
const bankLogoError = ref({})

const bankPartners = [
  {
    name: 'ABA Pay',
    logo: '/ABA.png',
  },
  {
    name: 'Wing Bank',
    logo: '/wing.png',
  },
  {
    name: 'ACLEDA Bank',
    logo: '/Ac.png',
  },
  { name: 'Visa card',
    logo: '/visa card.png',
  }
]

const { language } = useLanguage()

const copyByLanguage = {
  en: {
    brandTitle: 'Achar Event Booking',
    brandSub: 'Plan with confidence',
    brandBody: 'A trusted platform for planning, customizing, and booking event services with verified vendors.',
    verifiedVendors: 'Verified Vendors',
    secureBooking: 'Secure Booking',
    navigation: 'Navigation',
    home: 'Home',
    about: 'About',
    service: 'Service',
    myBooking: 'My Booking',
    favorite: 'Favorite',
    contact: 'Contact',
    paymentPartners: 'Payment Partners',
    paymentBody: 'Supported by trusted banking partners in Cambodia.',
    vendorPartnership: 'Vendor Partnership',
    vendorBody: 'We collaborate with professional service providers to ensure reliable quality and responsive support for every booking.',
    exploreServices: 'Explore Services',
    rightsReserved: 'All rights reserved.',
    privacyPolicy: 'Privacy Policy',
    termsOfService: 'Terms of Service',
    support: 'Support',
  },
  km: {
    brandTitle: 'Achar Event Booking',
    brandSub: 'រៀបចំដោយទំនុកចិត្ត',
    brandBody: 'វេទិកាដែលអាចទុកចិត្តបានសម្រាប់រៀបចំ ប្ដូរតាមតម្រូវការ និងកក់សេវាកម្មព្រឹត្តិការណ៍ជាមួយអ្នកផ្គត់ផ្គង់ដែលបានផ្ទៀងផ្ទាត់។',
    verifiedVendors: 'អ្នកផ្គត់ផ្គង់ដែលបានផ្ទៀងផ្ទាត់',
    secureBooking: 'ការកក់មានសុវត្ថិភាព',
    navigation: 'ការរុករក',
    home: 'ទំព័រដើម',
    about: 'អំពី',
    service: 'សេវាកម្ម',
    myBooking: 'ការកក់របស់ខ្ញុំ',
    favorite: 'ចូលចិត្ត',
    contact: 'ទាក់ទង',
    paymentPartners: 'ដៃគូទូទាត់ប្រាក់',
    paymentBody: 'គាំទ្រដោយដៃគូធនាគារដែលអាចទុកចិត្តបាននៅកម្ពុជា។',
    vendorPartnership: 'ភាពជាដៃគូអ្នកផ្គត់ផ្គង់',
    vendorBody: 'យើងសហការជាមួយអ្នកផ្តល់សេវាជំនាញ ដើម្បីធានាគុណភាពដែលអាចទុកចិត្តបាន និងការគាំទ្រឆាប់រហ័សសម្រាប់គ្រប់ការកក់។',
    exploreServices: 'ស្វែងរកសេវាកម្ម',
    rightsReserved: 'រក្សាសិទ្ធិគ្រប់យ៉ាង។',
    privacyPolicy: 'គោលការណ៍ឯកជនភាព',
    termsOfService: 'លក្ខខណ្ឌសេវាកម្ម',
    support: 'ជំនួយ',
  },
  zh: {
    brandTitle: 'Achar Event Booking',
    brandSub: '安心策划',
    brandBody: '一个值得信赖的平台，帮助您与已验证商家一起策划、定制并预订活动服务。',
    verifiedVendors: '认证商家',
    secureBooking: '安全预订',
    navigation: '导航',
    home: '首页',
    about: '关于',
    service: '服务',
    myBooking: '我的预订',
    favorite: '收藏',
    contact: '联系',
    paymentPartners: '支付合作伙伴',
    paymentBody: '由柬埔寨值得信赖的银行合作伙伴提供支持。',
    vendorPartnership: '商家合作',
    vendorBody: '我们与专业服务提供商合作，确保每一笔预订都具备可靠品质与及时支持。',
    exploreServices: '探索服务',
    rightsReserved: '保留所有权利。',
    privacyPolicy: '隐私政策',
    termsOfService: '服务条款',
    support: '支持',
  },
}

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en)

function onAppLogoError() {
  appLogoSrc.value = '/favicon.ico'
}

function onBankLogoError(name) {
  bankLogoError.value = {
    ...bankLogoError.value,
    [name]: true,
  }
}
</script>

<template>
  <footer class="site-footer">
    <div class="shell footer-main">
      <div class="brand-col">
        <div class="brand-head">
          <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onAppLogoError" />
          <div>
            <h3>{{ uiText.brandTitle }}</h3>
            <p class="brand-sub">{{ uiText.brandSub }}</p>
          </div>
        </div>
        <p>{{ uiText.brandBody }}</p>
        <div class="trust-row">
          <span class="pill">{{ uiText.verifiedVendors }}</span>
          <span class="pill">{{ uiText.secureBooking }}</span>
        </div>
      </div>

      <div class="links-col">
        <h4>{{ uiText.navigation }}</h4>
        <nav class="nav-links">
          <RouterLink to="/">{{ uiText.home }}</RouterLink>
          <RouterLink to="/about">{{ uiText.about }}</RouterLink>
          <RouterLink to="/services/packages">{{ uiText.service }}</RouterLink>
          <RouterLink to="/booking">{{ uiText.myBooking }}</RouterLink>
          <RouterLink to="/favorite">{{ uiText.favorite }}</RouterLink>
          <RouterLink to="/contact">{{ uiText.contact }}</RouterLink>
        </nav>
      </div>

      <div class="links-col">
        <h4>{{ uiText.paymentPartners }}</h4>
        <p class="muted">{{ uiText.paymentBody }}</p>
        <div class="payments">
          <div v-for="bank in bankPartners" :key="bank.name" class="payment-card">
            <div class="payment-link">
              <img
                v-if="!bankLogoError[bank.name]"
                class="payment-logo"
                :src="bank.logo"
                :alt="`${bank.name} logo`"
                loading="lazy"
                @error="onBankLogoError(bank.name)"
              />
              <div v-else class="payment-logo-placeholder"></div>
              <span class="payment-name">{{ bank.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="links-col">
        <h4>{{ uiText.vendorPartnership }}</h4>
        <p class="muted">{{ uiText.vendorBody }}</p>
        <RouterLink class="partner-link" to="/services/packages">{{ uiText.exploreServices }}</RouterLink>
      </div>
    </div>

    <div class="shell footer-bottom">
      <span class="copyright">
        &copy; {{ new Date().getFullYear() }} {{ uiText.brandTitle }}. {{ uiText.rightsReserved }}
      </span>
      <div class="legal-links">
        <RouterLink to="/about">{{ uiText.privacyPolicy }}</RouterLink>
        <RouterLink to="/about">{{ uiText.termsOfService }}</RouterLink>
        <RouterLink to="/contact">{{ uiText.support }}</RouterLink>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  margin-top: 24px;
  border-top: 1px solid #d9e2ef;
  background:
    radial-gradient(circle at top right, rgba(255, 106, 0, 0.08), transparent 36%),
    linear-gradient(180deg, #ffffff, #f7faff);
}

.footer-main {
  display: grid;
  grid-template-columns: 1.3fr 1fr 1fr 1fr;
  gap: 18px;
  padding: 26px 0 18px;
}

.brand-head {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.brand-logo {
  width: 96px;
  height: 96px;
  border-radius: 10px;
  object-fit: cover;
  border: 1px solid #e5ebf4;
  background: #fff;
}

.brand-col h3 {
  margin: 0;
  color: #1e293b;
  font-size: 20px;
  line-height: 1.2;
}

.brand-sub {
  margin: 2px 0 0;
  font-size: 12px;
  color: #64748b;
}

.brand-col p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.6;
  max-width: 305px;
}

.trust-row {
  margin-top: 14px;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pill {
  border: 1px solid #ffcfb0;
  background: #fff6ef;
  color: #c2410c;
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 12px;
  font-weight: 600;
}

.links-col h4 {
  margin: 0 0 10px;
  color: #334155;
  font-size: 20px;
}

.nav-links {
  display: grid;
  gap: 6px;
}

.nav-links a {
  color: #64748b;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
}

.nav-links a:hover,
.nav-links a.router-link-active {
  color: #c2410c;
}

.muted {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.55;
}

.payments {
  margin-top: 10px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 6px;
}

.payment-card {
  border: 1px solid #cbe0f1;
  border-radius: 16px;
  background: linear-gradient(135deg, #fff7ef, #fff);
  padding: 14px 18px;
  min-height: 56px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;

  /* smooth effect */
  transition: all 0.25s ease;

  /* light shadow */
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);

  cursor: pointer;
}

.payment-card:hover {
  border-color: #c2410c;
  background-color: #fff7ed;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transform: translateY(-2px);
}


.payment-link {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  text-decoration: none;
}

.payment-logo {
  width: 88px;
  height: 52px;
  object-fit: contain;
  flex: 0 0 auto;
}

.payment-logo-placeholder {
  width: 42px;
  height: 26px;
  border: 1px dashed #cbd5e1;
  border-radius: 6px;
  background: #f8fafc;
}

.payment-name {
  color: #334155;
  font-size: 13px;
  font-weight: 600;
}

.partner-link {
  display: inline-block;
  margin-top: 10px;
  color: #c2410c;
  font-size: 13px;
  text-decoration: none;
  font-weight: 600;
}

.footer-bottom {
  border-top: 1px solid #e5ebf4;
  padding: 12px 0 16px;
  color: #64748b;
  font-size: 12px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.copyright {
  color: #64748b;
}

.legal-links {
  display: flex;
  gap: 14px;
}

.legal-links a {
  color: #475569;
  text-decoration: none;
}

.legal-links a:hover {
  color: #c2410c;
}

@media (max-width: 980px) {
  .footer-main {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 680px) {
  .footer-main {
    grid-template-columns: 1fr;
    gap: 14px;
    padding-top: 20px;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
  }

  .legal-links {
    gap: 12px;
  }
}
</style>
