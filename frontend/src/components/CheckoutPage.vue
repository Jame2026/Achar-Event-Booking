<script setup>
import { computed, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { apiPost } from "../features/apiClient";
import { useLanguage } from "../features/language";

const router = useRouter();
const { language } = useLanguage();
const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const POST_AUTH_REDIRECT_KEY = "achar_post_auth_redirect";
const POST_AUTH_REDIRECT_AT_KEY = "achar_post_auth_redirect_at";
const LOCAL_BOOKINGS_STORAGE_KEY = "achar_local_bookings";
const CHECKOUT_FLOW_FLAG_KEY = "achar_checkout_flow_active";
const appLogoSrc = ref(localStorage.getItem("achar_brand_logo") || "/achar-logo.png");
const paymentLogoError = ref({
  aba: false,
  wing: false,
  acleda: false,
});
const qrCodeImageSrc = `${import.meta.env.BASE_URL}qrcode.jpg`;

const fallback = {
  vendorTitle: "Selected Vendor",
  fullName: "Guest User",
  email: "",
  phone: "",
  location: "Location not provided",
  eventDate: "",
  guests: 1,
  notes: "",
  items: [],
};

const booking = (() => {
  try {
    const raw = sessionStorage.getItem("achar_prebook_checkout");
    if (!raw) return fallback;
    return { ...fallback, ...JSON.parse(raw) };
  } catch {
    return fallback;
  }
})();

const bookingItems = computed(() => {
  if (Array.isArray(booking.items) && booking.items.length) return booking.items;
  return [
    {
      type: "package",
      name: booking.vendorTitle,
      description: booking.notes || "Selected booking item",
      qty: 1,
      unitPrice: 0,
      totalPrice: 0,
    },
  ];
});
const itemsSubtotal = computed(() =>
  bookingItems.value.reduce((sum, item) => sum + Number(item.totalPrice || 0), 0),
);
const bookingTotal = computed(() =>
  itemsSubtotal.value > 0 ? Number(itemsSubtotal.value.toFixed(2)) : Number((booking.guests * 35 + 380).toFixed(2)),
);
const processingFee = computed(() => Number((bookingTotal.value * 0.02).toFixed(2)));
const deposit = computed(() => Number((bookingTotal.value * 0.3).toFixed(2)));
const remaining = computed(() =>
  Number((bookingTotal.value + processingFee.value - deposit.value).toFixed(2)),
);
const selectedMethod = ref("aba");
const agreedTerms = ref(false);
const paymentNotice = ref("");
const isAwaitingPayment = ref(false);
const cardForm = ref({
  holderName: "",
  cardNumber: "",
  expiry: "",
  cvv: "",
});

function goBack() {
  router.back();
}

function goToServices() {
  const firstPackage = bookingItems.value.find((item) => item.type === "package");
  const firstService = bookingItems.value.find((item) => item.type === "service");
  const requestedEventType = String(booking.requestedEventType || "").trim();
  const query = { from: "checkout" };
  if (requestedEventType) query.event = requestedEventType;

  const hasService = Boolean(firstService?.name);
  if (hasService) {
    query.service = firstService.name;
    query.q = firstService.name;
  } else if (firstPackage?.name) {
    query.package = firstPackage.name;
    query.q = firstPackage.name;
  }

  sessionStorage.setItem(CHECKOUT_FLOW_FLAG_KEY, "1");
  router.push({
    path: hasService ? "/services/overall" : "/services/packages",
    query,
  });
}

function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}

function onPaymentLogoError(key) {
  paymentLogoError.value = {
    ...paymentLogoError.value,
    [key]: true,
  };
}

function saveLocalBooking(user) {
  try {
    const raw = localStorage.getItem(LOCAL_BOOKINGS_STORAGE_KEY);
    const existing = raw ? JSON.parse(raw) : [];
    const rows = Array.isArray(existing) ? existing : [];
    const email = String(booking.email || user?.email || "").trim().toLowerCase();
    if (!email) return;
    const firstItem = bookingItems.value[0] || {};
    rows.unshift({
      id: `local-${Date.now()}`,
      eventId: booking.eventId || null,
      customerEmail: email,
      customerName: booking.fullName || user?.name || "Guest User",
      vendor: booking.vendorName || booking.vendorTitle || "Selected Vendor",
      service: firstItem.name || booking.vendorTitle || "Service Booking",
      image: booking.image || "",
      dateLabel: booking.eventDate || "Date TBD",
      eventType: "other",
      total: Number(bookingTotal.value || 0),
      status: "Confirmed",
      statusClass: "confirmed",
      type: "Upcoming",
      createdAt: new Date().toISOString(),
    });
    localStorage.setItem(LOCAL_BOOKINGS_STORAGE_KEY, JSON.stringify(rows.slice(0, 100)));
  } catch {
    // Ignore local-storage issues and continue checkout flow.
  }
}

async function handleConfirmAndPay() {
  if (!agreedTerms.value) return;
  if (selectedMethod.value !== "card" && !isAwaitingPayment.value) {
    isAwaitingPayment.value = true;
    paymentNotice.value = uiText.value.scanQrNotice;
    return;
  }

  if (selectedMethod.value === "card") {
    const holder = String(cardForm.value.holderName || "").trim();
    const digits = String(cardForm.value.cardNumber || "").replace(/\D/g, "");
    const expiry = String(cardForm.value.expiry || "").trim();
    const cvv = String(cardForm.value.cvv || "").trim();
    const expiryMatch = expiry.match(/^(\d{2})\/(\d{2})$/);

    if (!holder || digits.length < 13 || digits.length > 19 || !expiryMatch || !/^\d{3,4}$/.test(cvv)) {
      paymentNotice.value = uiText.value.validCardDetails;
      return;
    }

    const mm = Number(expiryMatch[1]);
    const yy = Number(expiryMatch[2]);
    const now = new Date();
    const currentYY = Number(String(now.getFullYear()).slice(-2));
    const currentMM = now.getMonth() + 1;
    if (mm < 1 || mm > 12 || yy < currentYY || (yy === currentYY && mm < currentMM)) {
      paymentNotice.value = uiText.value.invalidCardExpiry;
      return;
    }
  }
  const stored = localStorage.getItem(AUTH_USER_STORAGE_KEY);
  if (!stored) {
    paymentNotice.value = uiText.value.signInToContinuePayment;
    sessionStorage.setItem(POST_AUTH_REDIRECT_KEY, "/checkout");
    sessionStorage.setItem(POST_AUTH_REDIRECT_AT_KEY, String(Date.now()));
    router.push("/legacy-app");
    return;
  }
  let user = null;
  try {
    user = stored ? JSON.parse(stored) : null;
  } catch {
    user = null;
  }
  const customerEmail = String(booking.email || user?.email || "").trim();
  const customerName = String(booking.fullName || user?.name || uiText.value.guestUser).trim();
  if (!customerEmail) {
    paymentNotice.value = uiText.value.addEmailBeforePayment;
    return;
  }
  const firstItem = bookingItems.value[0] || {};
  const quantity = Math.max(1, Number(firstItem.qty || 1));
  try {
    await apiPost("bookings", {
      event_id: booking.eventId || null,
      quantity,
      customer_name: customerName,
      customer_email: customerEmail,
      service_name: firstItem.name || booking.vendorTitle || uiText.value.serviceBooking,
      requested_event_type: booking.requestedEventType || "other",
      requested_event_date: booking.eventDate || null,
      total_amount: bookingTotal.value,
      booked_items: bookingItems.value.map((item) => ({
        type: item.type || "service",
        name: item.name || "",
        description: item.description || "",
        qty: Math.max(1, Number(item.qty || 1)),
        unitPrice: Number(item.unitPrice || 0),
        totalPrice: Number(item.totalPrice || 0),
      })),
    });
  } catch (error) {
    paymentNotice.value = error?.message || uiText.value.unableSaveBooking;
    return;
  }
  const receiptPayload = {
    booking,
    items: bookingItems.value,
    bookingTotal: bookingTotal.value,
    processingFee: processingFee.value,
    deposit: deposit.value,
    remaining: remaining.value,
    paidMethod: selectedMethod.value,
    paidAt: new Date().toISOString(),
  };
  saveLocalBooking(user);
  sessionStorage.setItem("achar_checkout_receipt", JSON.stringify(receiptPayload));
  router.push("/checkout/confirmed");
}

watch(selectedMethod, () => {
  isAwaitingPayment.value = false;
  paymentNotice.value = "";
});

const copyByLanguage = {
  en: {
    servicesStep: "1 Services",
    reviewStep: "2 Review & Payment",
    bookingSummary: "Booking Summary",
    bookingSubtitle: "Review selected items and customer details before payment.",
    dateNotSelected: "Date not selected",
    package: "Package",
    service: "Service",
    qty: "Qty",
    customerDetails: "Customer Details",
    noPhone: "No phone",
    noEmail: "No email",
    noNotes: "No additional notes.",
    serviceGuarantees: "Service Guarantees",
    vendorVerification: "Vendor Verification",
    vendorsChecked: "All vendors are background checked.",
    secureEscrow: "Secure Escrow",
    escrowText: "Your payment is held until service completion.",
    paymentSummary: "Payment Summary",
    paymentSubtitle: "Choose a payment method and confirm your deposit securely.",
    bookingTotal: "Booking Total",
    processingFee: "Processing Fee (2%)",
    depositRequired: "Deposit Required (30%)",
    remainingBalance: "Remaining Balance",
    selectPaymentMethod: "Select Payment Method",
    cardholderName: "Cardholder Name",
    cardNumber: "Card Number",
    expiry: "Expiry (MM/YY)",
    cardSecure: "Your card details are encrypted and processed securely.",
    agreeTerms: "I agree to the Terms of Service, Cancellation Policy and Privacy Policy of Achar.",
    confirmPay: "Confirm and Pay Deposit",
    securePayment: "Encrypted and secure payment processing",
    qrText: "Scan this QR code with your banking app to pay the deposit.",
    back: "Back",
    completePayment: "Complete Payment",
    scanQrNotice: "Please scan the QR code and complete payment, then click Complete Payment.",
    validCardDetails: "Please enter valid card details to continue.",
    invalidCardExpiry: "Your card expiry date is invalid or already expired.",
    signInToContinuePayment: "Please sign in or register to continue payment.",
    guestUser: "Guest User",
    addEmailBeforePayment: "Please add your email before confirming payment.",
    serviceBooking: "Service Booking",
    unableSaveBooking: "Unable to save booking to database.",
    noAdditionalDetails: "No additional details.",
  },
  km: {
    servicesStep: "1 សេវាកម្ម",
    reviewStep: "2 ពិនិត្យ និងទូទាត់",
    bookingSummary: "សេចក្តីសង្ខេបការកក់",
    bookingSubtitle: "ពិនិត្យធាតុដែលបានជ្រើស និងព័ត៌មានអតិថិជនមុនទូទាត់។",
    dateNotSelected: "មិនទាន់ជ្រើសកាលបរិច្ឆេទ",
    package: "កញ្ចប់",
    service: "សេវាកម្ម",
    qty: "ចំនួន",
    customerDetails: "ព័ត៌មានអតិថិជន",
    noPhone: "គ្មានលេខទូរស័ព្ទ",
    noEmail: "គ្មានអ៊ីមែល",
    noNotes: "មិនមានកំណត់ចំណាំបន្ថែម។",
    serviceGuarantees: "ការធានាសេវាកម្ម",
    vendorVerification: "ផ្ទៀងផ្ទាត់អ្នកផ្គត់ផ្គង់",
    vendorsChecked: "អ្នកផ្គត់ផ្គង់ទាំងអស់ត្រូវបានពិនិត្យប្រវត្តិ។",
    secureEscrow: "ការធានាលុយសុវត្ថិភាព",
    escrowText: "ការទូទាត់របស់អ្នកត្រូវបានរក្សាទុករហូតដល់សេវាកម្មបញ្ចប់។",
    paymentSummary: "សេចក្តីសង្ខេបទូទាត់",
    paymentSubtitle: "ជ្រើសវិធីទូទាត់ ហើយបញ្ជាក់ប្រាក់កក់ដោយសុវត្ថិភាព។",
    bookingTotal: "សរុបការកក់",
    processingFee: "កម្រៃសេវា (2%)",
    depositRequired: "ប្រាក់កក់ត្រូវការ (30%)",
    remainingBalance: "សមតុល្យនៅសល់",
    selectPaymentMethod: "ជ្រើសវិធីទូទាត់",
    cardholderName: "ឈ្មោះម្ចាស់កាត",
    cardNumber: "លេខកាត",
    expiry: "ផុតកំណត់ (MM/YY)",
    cardSecure: "ព័ត៌មានកាតរបស់អ្នកត្រូវបានអ៊ិនគ្រីប និងដំណើរការដោយសុវត្ថិភាព។",
    agreeTerms: "ខ្ញុំយល់ព្រមនឹងលក្ខខណ្ឌ ការលុបចោល និងគោលការណ៍ឯកជនភាពរបស់ Achar។",
    confirmPay: "បញ្ជាក់ និងទូទាត់ប្រាក់កក់",
    securePayment: "ការទូទាត់មានការអ៊ិនគ្រីប និងសុវត្ថិភាព",
    qrText: "ស្កេន QR នេះក្នុងកម្មវិធីធនាគាររបស់អ្នក ដើម្បីទូទាត់ប្រាក់កក់។",
    back: "ត្រឡប់",
    completePayment: "បញ្ចប់ការទូទាត់",
    scanQrNotice: "សូមស្កេនកូដ QR និងបញ្ចប់ការទូទាត់ រួចចុច បញ្ចប់ការទូទាត់។",
    validCardDetails: "សូមបញ្ចូលព័ត៌មានកាតឱ្យត្រឹមត្រូវ ដើម្បីបន្ត។",
    invalidCardExpiry: "កាលបរិច្ឆេទផុតកំណត់កាតរបស់អ្នកមិនត្រឹមត្រូវ ឬផុតកំណត់ហើយ។",
    signInToContinuePayment: "សូមចូលគណនី ឬចុះឈ្មោះ ដើម្បីបន្តការទូទាត់។",
    guestUser: "ភ្ញៀវ",
    addEmailBeforePayment: "សូមបន្ថែមអ៊ីមែលរបស់អ្នកមុនពេលបញ្ជាក់ការទូទាត់។",
    serviceBooking: "ការកក់សេវាកម្ម",
    unableSaveBooking: "មិនអាចរក្សាទុកការកក់ទៅមូលដ្ឋានទិន្នន័យបានទេ។",
    noAdditionalDetails: "មិនមានព័ត៌មានលម្អិតបន្ថែមទេ។",
  },
  zh: {
    servicesStep: "1 服务",
    reviewStep: "2 审核与支付",
    bookingSummary: "预订摘要",
    bookingSubtitle: "支付前请先确认所选项目与客户信息。",
    dateNotSelected: "未选择日期",
    package: "套餐",
    service: "服务",
    qty: "数量",
    customerDetails: "客户信息",
    noPhone: "无电话",
    noEmail: "无邮箱",
    noNotes: "无附加备注。",
    serviceGuarantees: "服务保障",
    vendorVerification: "商家核验",
    vendorsChecked: "所有商家均已完成背景审核。",
    secureEscrow: "资金托管",
    escrowText: "您的付款将在服务完成后再结算。",
    paymentSummary: "支付摘要",
    paymentSubtitle: "选择支付方式并安全确认定金。",
    bookingTotal: "订单总额",
    processingFee: "手续费 (2%)",
    depositRequired: "需支付定金 (30%)",
    remainingBalance: "剩余尾款",
    selectPaymentMethod: "选择支付方式",
    cardholderName: "持卡人姓名",
    cardNumber: "卡号",
    expiry: "有效期 (MM/YY)",
    cardSecure: "您的卡片信息将被加密并安全处理。",
    agreeTerms: "我同意 Achar 的服务条款、取消政策与隐私政策。",
    confirmPay: "确认并支付定金",
    securePayment: "加密并安全的支付处理",
    qrText: "请使用银行应用扫描此二维码支付定金。",
    back: "返回",
    completePayment: "完成支付",
    scanQrNotice: "请扫描二维码并完成支付，然后点击“完成支付”。",
    validCardDetails: "请输入有效的银行卡信息后再继续。",
    invalidCardExpiry: "您的卡片有效期无效或已过期。",
    signInToContinuePayment: "请先登录或注册再继续支付。",
    guestUser: "访客",
    addEmailBeforePayment: "请在确认支付前添加您的邮箱。",
    serviceBooking: "服务预订",
    unableSaveBooking: "无法将预订保存到数据库。",
    noAdditionalDetails: "无其他详细信息。",
  },
};

const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);
</script>

<template>
  <div class="checkout-page">
    <header class="checkout-header">
      <div class="checkout-brand">
        <img class="checkout-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
        <div>
          <strong class="checkout-brand-name">Achar</strong>
        </div>
      </div>
      <div class="checkout-steps">
        <button type="button" class="step-link" @click="goToServices">🧩 {{ uiText.servicesStep }}</button>
        <span class="active">💳 {{ uiText.reviewStep }}</span>
      </div>
      <button type="button" class="close-btn" @click="goBack">x</button>
    </header>

    <main class="checkout-shell">
      <section class="checkout-main paper-canvas">
        <div class="section-head">
          <h1>📘 {{ uiText.bookingSummary }}</h1>
          <p class="section-subtitle">{{ uiText.bookingSubtitle }}</p>
        </div>

        <article
          v-for="item in bookingItems"
          :key="`${item.type}-${item.name}`"
          class="line-item"
        >
          <div class="line-item-copy">
            <h3>{{ item.name }}</h3>
            <p>{{ booking.eventDate || uiText.dateNotSelected }} | {{ booking.location }}</p>
            <p>{{ item.type === "package" ? uiText.package : uiText.service }} | {{ uiText.qty }} {{ item.qty }}</p>
            <small>{{ item.description || uiText.noAdditionalDetails }}</small>
          </div>
          <strong>${{ Number(item.totalPrice || 0).toLocaleString() }}</strong>
        </article>

        <article class="line-item customer-item">
          <div class="line-item-copy">
            <h3>👤 {{ uiText.customerDetails }}</h3>
            <p>{{ booking.fullName }} | {{ booking.phone || uiText.noPhone }}</p>
            <p>{{ booking.email || uiText.noEmail }}</p>
            <small>{{ booking.notes || uiText.noNotes }}</small>
          </div>
        </article>

        <article class="guarantee-card">
          <h3>🛡️ {{ uiText.serviceGuarantees }}</h3>
          <div class="guarantee-grid">
            <p><strong>✅ {{ uiText.vendorVerification }}</strong><br />{{ uiText.vendorsChecked }}</p>
            <p><strong>🔒 {{ uiText.secureEscrow }}</strong><br />{{ uiText.escrowText }}</p>
          </div>
        </article>
      </section>

      <aside class="checkout-side">
        <article class="payment-card">
          <h2>💰 {{ uiText.paymentSummary }}</h2>
          <p class="payment-subtitle">{{ uiText.paymentSubtitle }}</p>
          <div class="row"><span>{{ uiText.bookingTotal }}</span><strong>${{ bookingTotal.toLocaleString() }}</strong></div>
          <div class="row"><span>{{ uiText.processingFee }}</span><strong>${{ processingFee.toLocaleString() }}</strong></div>
          <div class="deposit-box">
            <p>{{ uiText.depositRequired }}</p>
            <div class="deposit-row">
              <strong>${{ deposit.toLocaleString() }}</strong>
              <span class="deposit-icon">💵</span>
            </div>
          </div>
          <div class="row"><span>{{ uiText.remainingBalance }}</span><strong>${{ remaining.toLocaleString() }}</strong></div>
          <hr class="payment-divider" />

          <p class="payment-method-label">🏦 {{ uiText.selectPaymentMethod }}</p>
          <button
            type="button"
            class="method-card"
            :class="{ active: selectedMethod === 'aba' }"
            @click="selectedMethod = 'aba'"
          >
            <span class="method-logo aba">
              <img
                v-if="!paymentLogoError.aba"
                src="/ABA.png"
                alt="ABA logo"
                loading="lazy"
                @error="onPaymentLogoError('aba')"
              />
              <span v-else>ABA</span>
            </span>
            <span class="method-copy">
              <strong>ABA Pay</strong>
              <small>Instant QR payment</small>
            </span>
            <span class="method-radio"></span>
          </button>
          <button
            type="button"
            class="method-card"
            :class="{ active: selectedMethod === 'wing' }"
            @click="selectedMethod = 'wing'"
          >
            <span class="method-logo wing">
              <img
                v-if="!paymentLogoError.wing"
                src="/wing.png"
                alt="Wing logo"
                loading="lazy"
                @error="onPaymentLogoError('wing')"
              />
              <span v-else>Wing</span>
            </span>
            <span class="method-copy">
              <strong>Wing Bank</strong>
              <small>Pay via Wing App or Account</small>
            </span>
            <span class="method-radio"></span>
          </button>
          <button
            type="button"
            class="method-card"
            :class="{ active: selectedMethod === 'acleda' }"
            @click="selectedMethod = 'acleda'"
          >
            <span class="method-logo acleda">
              <img
                v-if="!paymentLogoError.acleda"
                src="/Ac.png"
                alt="ACLEDA logo"
                loading="lazy"
                @error="onPaymentLogoError('acleda')"
              />
              <span v-else>ACLEDA</span>
            </span>
            <span class="method-copy">
              <strong>ACLEDA Bank</strong>
              <small>ACLEDA Mobile / QR</small>
            </span>
            <span class="method-radio"></span>
          </button>
          <button
            type="button"
            class="method-card"
            :class="{ active: selectedMethod === 'card' }"
            @click="selectedMethod = 'card'"
          >
            <span class="method-logo card">
              <span>VISA</span>
            </span>
            <span class="method-copy">
              <strong>Credit / Visa Card</strong>
              <small>Secure card payment</small>
            </span>
            <span class="method-radio"></span>
          </button>

          <div v-if="selectedMethod === 'card'" class="card-panel">
              <label class="card-field">
                <span>{{ uiText.cardholderName }}</span>
              <input
                v-model.trim="cardForm.holderName"
                type="text"
                autocomplete="cc-name"
                placeholder="Name on card"
              />
            </label>
              <label class="card-field">
                <span>{{ uiText.cardNumber }}</span>
              <input
                v-model="cardForm.cardNumber"
                type="text"
                inputmode="numeric"
                autocomplete="cc-number"
                maxlength="23"
                placeholder="1234 5678 9012 3456"
              />
            </label>
            <div class="card-row">
              <label class="card-field">
                <span>{{ uiText.expiry }}</span>
                <input
                  v-model="cardForm.expiry"
                  type="text"
                  inputmode="numeric"
                  autocomplete="cc-exp"
                  maxlength="5"
                  placeholder="MM/YY"
                />
              </label>
              <label class="card-field">
                <span>CVV</span>
                <input
                  v-model="cardForm.cvv"
                  type="password"
                  inputmode="numeric"
                  autocomplete="cc-csc"
                  maxlength="4"
                  placeholder="123"
                />
              </label>
            </div>
            <p class="card-help">{{ uiText.cardSecure }}</p>
          </div>

          <label class="terms-row">
            <input v-model="agreedTerms" type="checkbox" />
            <span>
              {{ uiText.agreeTerms }}
            </span>
          </label>

          <button type="button" class="pay-btn" :disabled="!agreedTerms" @click="handleConfirmAndPay">
            ✅ {{ uiText.confirmPay }}
          </button>
          <p v-if="paymentNotice" class="payment-notice">{{ paymentNotice }}</p>
          <p class="secure-note">{{ uiText.securePayment }}</p>
        </article>
      </aside>
    </main>

    <div
      v-if="selectedMethod !== 'card' && isAwaitingPayment"
      class="qr-fullscreen-overlay"
      role="dialog"
      aria-modal="true"
      aria-label="QR payment"
    >
      <div class="qr-fullscreen-panel">
        <div class="qr-fullscreen-image-wrap">
          <img :src="qrCodeImageSrc" alt="QR code for payment" loading="lazy" />
        </div>
        <p>{{ uiText.qrText }}</p>
        <div class="qr-fullscreen-actions">
          <button type="button" class="modal-btn ghost" @click="isAwaitingPayment = false">
            ⬅️ {{ uiText.back }}
          </button>
          <button type="button" class="modal-btn primary" @click="handleConfirmAndPay">
            ✔️ {{ uiText.completePayment }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.checkout-page {
  --checkout-bg: #e9eef5;
  --checkout-text: #0f172a;
  --checkout-muted: #64748b;
  --checkout-border: #d8e2ef;
  --checkout-accent: #f97316;
  --checkout-accent-strong: #ea580c;
  --checkout-soft: #fff7ed;
  min-height: 100vh;
  background:
    radial-gradient(circle at 6% 0%, rgba(249, 115, 22, 0.12), transparent 36%),
    radial-gradient(circle at 96% 10%, rgba(59, 130, 246, 0.08), transparent 34%),
    var(--checkout-bg);
}

.checkout-header {
  position: sticky;
  top: 0;
  z-index: 70;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid #dbe2ee;
  padding: 10px 18px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 14px;
}

.checkout-brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.checkout-logo {
  width: 54px;
  height: 54px;
  border-radius: 12px;
  border: 1px solid #f2d2bb;
  background: #fff;
  object-fit: contain;
  padding: 5px;
  box-shadow: 0 8px 16px rgba(198, 100, 14, 0.16);
}

.checkout-brand-name {
  display: block;
  font-size: clamp(26px, 3vw, 34px);
  line-height: 1;
  color: #c76316;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.checkout-steps {
  justify-self: center;
  color: #7c8ca2;
  font-weight: 700;
  display: inline-flex;
  gap: 14px;
  font-size: 14px;
  background: #f8fbff;
  border: 1px solid #dbe4f1;
  border-radius: 999px;
  padding: 7px 12px;
}

.checkout-steps .active {
  color: #fff;
  background: linear-gradient(120deg, var(--checkout-accent), #fb923c);
  border-radius: 999px;
  padding: 4px 10px;
  box-shadow: 0 8px 16px rgba(249, 115, 22, 0.24);
}

.checkout-steps .step-link {
  border: 0;
  background: transparent;
  color: #7c8ca2;
  font: inherit;
  font-weight: 800;
  padding: 0;
  cursor: pointer;
}

.checkout-steps .step-link:hover {
  color: #475569;
}

.close-btn {
  width: 38px;
  height: 38px;
  border-radius: 999px;
  border: 1px solid #d6deeb;
  background: #fff;
  cursor: pointer;
  color: #334155;
  font-size: 18px;
  transition:
    border-color 0.2s ease,
    background 0.2s ease,
    transform 0.2s ease;
}

.close-btn:hover {
  border-color: #fdba74;
  background: #fff7ed;
  transform: translateY(-1px);
}

.checkout-shell {
  width: min(1220px, calc(100% - 2rem));
  margin: 20px auto 0;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 405px;
  gap: 18px;
  align-items: start;
}

.checkout-main.paper-canvas {
  border: 1px solid #d7dee9;
  border-radius: 22px;
  padding: 14px;
  background:
    radial-gradient(circle at 95% 2%, rgba(217, 119, 6, 0.06), transparent 30%),
    repeating-linear-gradient(
      180deg,
      rgba(148, 163, 184, 0.05) 0,
      rgba(148, 163, 184, 0.05) 1px,
      transparent 1px,
      transparent 34px
    ),
    linear-gradient(180deg, #ffffff, #fbfcff);
  box-shadow: 0 14px 32px rgba(15, 23, 42, 0.06);
}

.section-head {
  border: 1px solid var(--checkout-border);
  border-radius: 18px;
  background: #fff;
  padding: 16px 18px;
}

.checkout-main h1 {
  margin: 0;
  font-size: clamp(28px, 2.4vw, 36px);
  line-height: 1.05;
  letter-spacing: -0.02em;
}

.section-subtitle {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 14px;
}

.line-item,
.guarantee-card,
.payment-card {
  border: 1px solid var(--checkout-border);
  border-radius: 18px;
  background: #fff;
}

.line-item {
  margin-top: 12px;
  padding: 16px 18px;
  display: flex;
  justify-content: space-between;
  gap: 14px;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.04);
}

.line-item h3 {
  margin: 0;
  font-size: 17px;
  color: var(--checkout-text);
}

.line-item p {
  margin: 5px 0 0;
  font-size: 14px;
  color: var(--checkout-muted);
}

.line-item small {
  display: block;
  margin-top: 6px;
  color: #94a3b8;
  font-size: 12px;
}

.line-item strong {
  color: #c2410c;
  font-size: 22px;
  white-space: nowrap;
  letter-spacing: -0.01em;
}

.guarantee-card {
  margin-top: 12px;
  padding: 16px 18px;
  background: #fff;
}

.guarantee-card h3 {
  margin: 0 0 8px;
  font-size: 15px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #475569;
}

.guarantee-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.guarantee-grid p {
  margin: 0;
  color: #475569;
  font-size: 13px;
  line-height: 1.4;
}

.payment-card {
  padding: 16px;
  position: sticky;
  top: 84px;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
  background: linear-gradient(180deg, #ffffff, #fdfefe);
}

.payment-card h2 {
  margin: 0;
  font-size: 30px;
  letter-spacing: -0.02em;
  line-height: 1.04;
}

.payment-subtitle {
  margin: 5px 0 12px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.45;
}

.row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin: 10px 0;
  color: #475569;
  font-size: 14px;
}

.row strong {
  color: #0f172a;
}

.deposit-box {
  margin-top: 10px;
  border: 1px solid #f8c99f;
  border-radius: 14px;
  background: linear-gradient(180deg, #fff8ef, #fff2e4);
  padding: 10px 11px;
}

.deposit-box p {
  margin: 0;
  color: #b45309;
  text-transform: uppercase;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.06em;
}

.deposit-box strong {
  display: inline-block;
  font-size: 34px;
  line-height: 1;
  color: #c2410c;
}

.deposit-row {
  margin-top: 4px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.deposit-icon {
  width: 28px;
  height: 28px;
  border-radius: 7px;
  background: #f59b23;
  color: #fff;
  display: grid;
  place-items: center;
  font-weight: 900;
}

.payment-divider {
  border: 0;
  border-top: 1px solid #e0e7f2;
  margin: 12px 0;
}

.payment-method-label {
  margin: 0 0 8px;
  color: #94a3b8;
  text-transform: uppercase;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.method-card {
  width: 100%;
  border: 1px solid #d8e2ef;
  border-radius: 14px;
  background: #fff;
  padding: 11px;
  display: grid;
  grid-template-columns: 70px minmax(0, 1fr) auto;
  gap: 10px;
  align-items: center;
  margin-bottom: 8px;
  text-align: left;
  cursor: pointer;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.method-card:hover {
  border-color: #f5c39d;
  box-shadow: 0 8px 18px rgba(249, 115, 22, 0.12);
  transform: translateY(-1px);
}

.method-card.active {
  border-color: #f59b23;
  box-shadow: inset 0 0 0 1px rgba(245, 155, 35, 0.25);
}

.method-logo {
  width: 64px;
  height: 40px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 900;
  overflow: hidden;
  border: 1px solid #dbe4f1;
  background: #fff;
  padding: 3px;
}

.method-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.method-logo.aba img {
  transform: scale(1.12);
}

.method-logo.aba {
  color: #003a75;
}

.method-logo.wing {
  color: #4d7f18;
}

.method-logo.acleda {
  color: #173b9f;
}

.method-logo.card {
  background: linear-gradient(135deg, #1e3a8a, #2563eb);
  color: #fff;
  border-color: #1d4ed8;
  padding: 0;
  font-size: 13px;
  letter-spacing: 0.03em;
}

.method-copy strong {
  display: block;
  font-size: 15px;
  line-height: 1.2;
  color: #0f172a;
}

.method-copy small {
  color: #64748b;
  font-size: 12px;
  line-height: 1.35;
}

.method-radio {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  border: 2px solid #c9d5e6;
}

.method-card.active .method-radio {
  border-color: #f59b23;
  box-shadow: inset 0 0 0 4px #fff, inset 0 0 0 10px #f59b23;
}

.qr-panel {
  margin-top: 8px;
  border: 1px dashed #cfdceb;
  border-radius: 14px;
  background: linear-gradient(180deg, #f8fbff, #f3f8ff);
  padding: 12px;
  text-align: center;
}

.qr-placeholder {
  width: 140px;
  height: 140px;
  margin: 0 auto;
  border-radius: 10px;
  border: 1px solid #d6dfec;
  background: #fff;
  display: grid;
  place-items: center;
  padding: 8px;
}

.qr-placeholder img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.qr-panel p {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 12px;
}

.card-panel {
  margin-top: 8px;
  border: 1px solid #d8e2ef;
  border-radius: 14px;
  background: linear-gradient(180deg, #fbfdff, #f7faff);
  padding: 12px;
  display: grid;
  gap: 11px;
}

.card-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.card-field {
  display: grid;
  gap: 5px;
}

.card-field span {
  color: #64748b;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.card-field input {
  width: 100%;
  border: 1px solid #d7e1ef;
  border-radius: 12px;
  background: #fff;
  color: #0f172a;
  font: inherit;
  padding: 10px 11px;
}

.card-help {
  margin: -2px 0 0;
  color: #64748b;
  font-size: 12px;
}

.terms-row {
  margin-top: 10px;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 8px;
  align-items: start;
}

.terms-row input {
  margin-top: 3px;
}

.terms-row span {
  color: #64748b;
  font-size: 12px;
  line-height: 1.45;
}

.terms-row b {
  color: #d97706;
  font-weight: 700;
}

.pay-btn {
  width: 100%;
  margin-top: 14px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(120deg, var(--checkout-accent), var(--checkout-accent-strong));
  color: #fff;
  font-size: 16px;
  font-weight: 800;
  padding: 12px;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(249, 115, 22, 0.28);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
}

.pay-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 14px 24px rgba(249, 115, 22, 0.32);
}

.pay-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
  box-shadow: none;
}

.secure-note {
  margin: 10px 0 0;
  text-align: center;
  color: #94a3b8;
  font-size: 12px;
}

.payment-notice {
  margin: 8px 0 0;
  color: #b45309;
  font-size: 12px;
  font-weight: 700;
  text-align: center;
}

.qr-fullscreen-overlay {
  position: fixed;
  inset: 0;
  z-index: 140;
  background: rgba(15, 23, 42, 0.84);
  display: grid;
  place-items: center;
  padding: 18px;
}

.qr-fullscreen-panel {
  width: min(560px, 100%);
  border: 1px solid #d8e2ef;
  border-radius: 18px;
  background: #fff;
  padding: 18px;
  display: grid;
  gap: 14px;
  text-align: center;
}

.qr-fullscreen-image-wrap {
  margin: 0 auto;
  width: min(86vw, 420px);
  aspect-ratio: 1 / 1;
  border-radius: 16px;
  border: 1px solid #d8e2ef;
  background: #fff;
  padding: 14px;
  display: grid;
  place-items: center;
}

.qr-fullscreen-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.qr-fullscreen-panel p {
  margin: 0;
  color: #475569;
  font-weight: 600;
}

.qr-fullscreen-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.modal-btn {
  min-height: 44px;
  border-radius: 12px;
  border: 1px solid #d8e2ef;
  background: #fff;
  color: #334155;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.modal-btn.primary {
  background: #f97316;
  border-color: #f97316;
  color: #fff;
}

@media (max-width: 980px) {
  .checkout-header {
    grid-template-columns: auto;
    justify-items: start;
    gap: 8px;
  }

  .checkout-logo {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    padding: 5px;
  }

  .checkout-brand-name {
    font-size: 28px;
  }

  .checkout-shell {
    grid-template-columns: 1fr;
  }

  .payment-card {
    position: static;
    top: auto;
  }

  .checkout-main h1 {
    font-size: 30px;
  }

  .line-item h3 {
    font-size: 17px;
  }

  .line-item p {
    font-size: 14px;
  }

  .line-item small {
    font-size: 13px;
  }

  .line-item strong {
    font-size: 20px;
  }

  .guarantee-card h3,
  .payment-card h2 {
    font-size: 22px;
  }

  .guarantee-grid p,
  .row,
  .deposit-box p,
  .pay-btn {
    font-size: 15px;
  }

  .qr-fullscreen-overlay {
    padding: 10px;
  }

  .qr-fullscreen-panel {
    padding: 14px;
    border-radius: 14px;
  }

  .qr-fullscreen-actions {
    grid-template-columns: 1fr;
  }

  .deposit-box strong {
    font-size: 30px;
  }
}

@media (max-width: 680px) {
  .checkout-header {
    padding: 8px 12px;
  }

  .checkout-steps {
    width: 100%;
    justify-content: center;
    font-size: 12px;
    padding: 6px 8px;
  }

  .checkout-logo {
    width: 46px;
    height: 46px;
  }

  .checkout-brand-name {
    font-size: 24px;
  }

  .checkout-shell {
    width: calc(100% - 1rem);
    margin-top: 12px;
    gap: 10px;
  }

  .line-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .line-item strong {
    font-size: 20px;
  }

  .guarantee-grid {
    grid-template-columns: 1fr;
  }

  .deposit-box strong {
    font-size: 28px;
  }
}
</style>
