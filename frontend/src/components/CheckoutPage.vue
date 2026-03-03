<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const AUTH_USER_STORAGE_KEY = "achar_auth_user";
const appLogoSrc = ref(localStorage.getItem("achar_brand_logo") || "/achar-logo.png");

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

function goBack() {
  router.back();
}

function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}

function handleConfirmAndPay() {
  if (!agreedTerms.value) return;
  const stored = localStorage.getItem(AUTH_USER_STORAGE_KEY);
  if (!stored) {
    paymentNotice.value = "Please sign in or register to continue payment.";
    sessionStorage.setItem("achar_post_auth_redirect", "/checkout");
    router.push("/legacy-app");
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
  sessionStorage.setItem("achar_checkout_receipt", JSON.stringify(receiptPayload));
  router.push("/checkout/confirmed");
}
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
        <span>1 Services</span>
        <span class="active">2 Review & Payment</span>
      </div>
      <button type="button" class="close-btn" @click="goBack">x</button>
    </header>

    <main class="checkout-shell">
      <section class="checkout-main">
        <div class="section-head">
          <h1>Booking Summary</h1>
        </div>

        <article
          v-for="item in bookingItems"
          :key="`${item.type}-${item.name}`"
          class="line-item"
        >
          <div class="line-item-copy">
            <h3>{{ item.name }}</h3>
            <p>{{ booking.eventDate || "Date not selected" }} | {{ booking.location }}</p>
            <p>{{ item.type === "package" ? "Package" : "Service" }} | Qty {{ item.qty }}</p>
            <small>{{ item.description || "No additional details." }}</small>
          </div>
          <strong>${{ Number(item.totalPrice || 0).toLocaleString() }}</strong>
        </article>

        <article class="line-item customer-item">
          <div class="line-item-copy">
            <h3>Customer Details</h3>
            <p>{{ booking.fullName }} | {{ booking.phone || "No phone" }}</p>
            <p>{{ booking.email || "No email" }}</p>
            <small>{{ booking.notes || "No additional notes." }}</small>
          </div>
        </article>

        <article class="guarantee-card">
          <h3>Service Guarantees</h3>
          <div class="guarantee-grid">
            <p><strong>Vendor Verification</strong><br />All vendors are background checked.</p>
            <p><strong>Secure Escrow</strong><br />Your payment is held until service completion.</p>
          </div>
        </article>
      </section>

      <aside class="checkout-side">
        <article class="payment-card">
          <h2>Payment Summary</h2>
          <div class="row"><span>Booking Total</span><strong>${{ bookingTotal.toLocaleString() }}</strong></div>
          <div class="row"><span>Processing Fee (2%)</span><strong>${{ processingFee.toLocaleString() }}</strong></div>
          <div class="deposit-box">
            <p>Deposit Required (30%)</p>
            <div class="deposit-row">
              <strong>${{ deposit.toLocaleString() }}</strong>
              <span class="deposit-icon">c</span>
            </div>
          </div>
          <div class="row"><span>Remaining Balance</span><strong>${{ remaining.toLocaleString() }}</strong></div>
          <hr class="payment-divider" />

          <p class="payment-method-label">Select Payment Method</p>
          <button
            type="button"
            class="method-card"
            :class="{ active: selectedMethod === 'aba' }"
            @click="selectedMethod = 'aba'"
          >
            <span class="method-logo aba">ABA</span>
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
            <span class="method-logo wing">Wing</span>
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
            <span class="method-logo acleda">ACLEDA</span>
            <span class="method-copy">
              <strong>ACLEDA Bank</strong>
              <small>ACLEDA Mobile / QR</small>
            </span>
            <span class="method-radio"></span>
          </button>

          <div class="qr-panel">
            <div class="qr-placeholder" aria-label="Example QR code">
              <svg viewBox="0 0 120 120" role="img" aria-hidden="true">
                <rect x="0" y="0" width="120" height="120" fill="#ffffff" />
                <rect x="8" y="8" width="26" height="26" fill="#1e293b" />
                <rect x="12" y="12" width="18" height="18" fill="#ffffff" />
                <rect x="16" y="16" width="10" height="10" fill="#1e293b" />
                <rect x="86" y="8" width="26" height="26" fill="#1e293b" />
                <rect x="90" y="12" width="18" height="18" fill="#ffffff" />
                <rect x="94" y="16" width="10" height="10" fill="#1e293b" />
                <rect x="8" y="86" width="26" height="26" fill="#1e293b" />
                <rect x="12" y="90" width="18" height="18" fill="#ffffff" />
                <rect x="16" y="94" width="10" height="10" fill="#1e293b" />
                <rect x="46" y="46" width="6" height="6" fill="#1e293b" />
                <rect x="56" y="46" width="6" height="6" fill="#1e293b" />
                <rect x="66" y="46" width="6" height="6" fill="#1e293b" />
                <rect x="46" y="56" width="6" height="6" fill="#1e293b" />
                <rect x="66" y="56" width="6" height="6" fill="#1e293b" />
                <rect x="46" y="66" width="6" height="6" fill="#1e293b" />
                <rect x="56" y="66" width="6" height="6" fill="#1e293b" />
                <rect x="66" y="66" width="6" height="6" fill="#1e293b" />
                <rect x="80" y="52" width="6" height="6" fill="#1e293b" />
                <rect x="90" y="52" width="6" height="6" fill="#1e293b" />
                <rect x="80" y="62" width="6" height="6" fill="#1e293b" />
                <rect x="90" y="72" width="6" height="6" fill="#1e293b" />
                <rect x="52" y="80" width="6" height="6" fill="#1e293b" />
                <rect x="62" y="90" width="6" height="6" fill="#1e293b" />
              </svg>
            </div>
            <p>Scan the QR code within your banking app to authorize the deposit.</p>
          </div>

          <label class="terms-row">
            <input v-model="agreedTerms" type="checkbox" />
            <span>
              I agree to the <b>Terms of Service</b>, <b>Cancellation Policy</b> and
              <b>Privacy Policy</b> of Achar.
            </span>
          </label>

          <button type="button" class="pay-btn" :disabled="!agreedTerms" @click="handleConfirmAndPay">
            Confirm and Pay Deposit
          </button>
          <p v-if="paymentNotice" class="payment-notice">{{ paymentNotice }}</p>
          <p class="secure-note">Encrypted and secure payment processing</p>
        </article>
      </aside>
    </main>
  </div>
</template>

<style scoped>
.checkout-page {
  min-height: 100vh;
  background: #f3f5f9;
}

.checkout-header {
  position: sticky;
  top: 0;
  z-index: 40;
  background: #fff;
  border-bottom: 1px solid #dbe2ee;
  padding: 10px 20px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 16px;
}

.checkout-brand {
  display: inline-flex;
  align-items: center;
  gap: 14px;
}

.checkout-logo {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  border: 1px solid #f2d2bb;
  background: #fff;
  object-fit: contain;
  padding: 6px;
  box-shadow: 0 6px 14px rgba(198, 100, 14, 0.15);
}

.checkout-brand-name {
  display: block;
  font-size: clamp(26px, 3vw, 42px);
  line-height: 1.1;
  color: #c76316;
  font-weight: 800;
}

.checkout-steps {
  justify-self: center;
  color: #7c8ca2;
  font-weight: 600;
  display: inline-flex;
  gap: 16px;
}

.checkout-steps .active {
  color: #e67a00;
}

.close-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid #d6deeb;
  background: #fff;
  cursor: pointer;
}

.checkout-shell {
  width: min(1220px, calc(100% - 2rem));
  margin: 16px auto 0;
  display: grid;
  grid-template-columns: 1fr 420px;
  gap: 16px;
  align-items: start;
}

.checkout-main h1 {
  margin: 0;
  font-size: 18px;
}

.line-item,
.guarantee-card,
.payment-card {
  border: 1px solid #d5dfec;
  border-radius: 16px;
  background: #fff;
}

.line-item {
  margin-top: 12px;
  padding: 14px;
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.line-item h3 {
  margin: 0;
  font-size: 16px;
}

.line-item p {
  margin: 6px 0 0;
  font-size: 15px;
  color: #64748b;
}

.line-item small {
  display: block;
  margin-top: 8px;
  color: #94a3b8;
  font-size: 13px;
}

.line-item strong {
  color: #e67a00;
  font-size: 20px;
  white-space: nowrap;
}

.guarantee-card {
  margin-top: 12px;
  padding: 14px;
}

.guarantee-card h3 {
  margin: 0 0 8px;
  font-size: 15px;
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
  padding: 14px;
}

.payment-card h2 {
  margin: 0 0 10px;
  font-size: 16px;
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
  border: 1px solid #f2c995;
  border-radius: 12px;
  background: #fff8ef;
  padding: 10px;
}

.deposit-box p {
  margin: 0;
  color: #b45309;
  text-transform: uppercase;
  font-size: 13px;
  font-weight: 700;
}

.deposit-box strong {
  display: inline-block;
  font-size: 38px;
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
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.method-card {
  width: 100%;
  border: 1px solid #d8e2ef;
  border-radius: 14px;
  background: #fff;
  padding: 10px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
  margin-bottom: 8px;
  text-align: left;
  cursor: pointer;
}

.method-card.active {
  border-color: #f59b23;
  box-shadow: inset 0 0 0 1px rgba(245, 155, 35, 0.25);
}

.method-logo {
  min-width: 52px;
  height: 32px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  font-size: 12px;
  font-weight: 900;
}

.method-logo.aba {
  background: #e9f1ff;
  color: #003a75;
}

.method-logo.wing {
  background: #8bc53f;
  color: #fff;
}

.method-logo.acleda {
  background: #f3dd3d;
  color: #173b9f;
}

.method-copy strong {
  display: block;
  font-size: 14px;
  color: #0f172a;
}

.method-copy small {
  color: #64748b;
  font-size: 12px;
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
  border: 1px dashed #d3ddeb;
  border-radius: 14px;
  background: #f8fbff;
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

.qr-placeholder svg {
  width: 100%;
  height: 100%;
}

.qr-panel p {
  margin: 10px 0 0;
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
  background: #f59b23;
  color: #fff;
  font-size: 16px;
  font-weight: 800;
  padding: 12px;
  cursor: pointer;
}

.pay-btn:disabled {
  background: #cbd5e1;
  cursor: not-allowed;
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

  .checkout-main h1 {
    font-size: 30px;
  }

  .line-item h3 {
    font-size: 22px;
  }

  .line-item p {
    font-size: 16px;
  }

  .line-item small {
    font-size: 13px;
  }

  .line-item strong {
    font-size: 22px;
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

  .deposit-box strong {
    font-size: 30px;
  }
}
</style>
