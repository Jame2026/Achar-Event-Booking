<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const appLogoSrc = ref(localStorage.getItem("achar_brand_logo") || "/achar-logo.png");

function onLogoError() {
  appLogoSrc.value = "/favicon.ico";
}

const receipt = (() => {
  try {
    const raw = sessionStorage.getItem("achar_checkout_receipt");
    if (!raw) return null;
    return JSON.parse(raw);
  } catch {
    return null;
  }
})();

const booking = receipt?.booking || {};
const items = Array.isArray(receipt?.items) ? receipt.items : [];
const bookingTotal = Number(receipt?.bookingTotal || 0);
const processingFee = Number(receipt?.processingFee || 0);
const deposit = Number(receipt?.deposit || 0);
const remaining = Number(receipt?.remaining || 0);
const paidDateLabel = computed(() => {
  const value = receipt?.paidAt ? new Date(receipt.paidAt) : new Date();
  return value.toLocaleString();
});
const bookingId = computed(() => {
  const base = Date.now().toString().slice(-6);
  return `#AC-${new Date().getFullYear()}-${base}`;
});

function goDashboard() {
  router.push("/legacy-app?page=dashboard");
}

function goReceipt() {
  router.push("/checkout/receipt");
}
</script>

<template>
  <div class="confirmed-page"><main class="confirmed-shell">
      <div class="status-wrap">
        <div class="status-icon">&#10003;</div>
      </div>
      <h1>Your Celebration is Set!</h1>
      <p class="subtitle">Your booking has been confirmed. A copy of the receipt has been sent to your email.</p>

      <section class="receipt-card">
        <div class="receipt-head">
          <div>
            <small>Digital Receipt</small>
            <strong>Achar Event Planning</strong>
          </div>
          <div class="receipt-id">
            <small>Booking ID</small>
            <strong>{{ bookingId }}</strong>
          </div>
        </div>

        <div class="receipt-body">
          <div class="receipt-meta">
            <div>
              <small>Event Date</small>
              <strong>{{ booking.eventDate || "TBD" }}</strong>
            </div>
            <div class="paid-pill">Deposit Paid</div>
          </div>

          <div class="service-list">
            <article v-for="item in items" :key="`${item.type}-${item.name}`">
              <div>
                <strong>{{ item.name }}</strong>
                <p>{{ item.description || "Service item" }}</p>
              </div>
              <span>${{ Number(item.totalPrice || 0).toLocaleString() }}</span>
            </article>
          </div>

          <div class="totals-box">
            <div><span>Total Booking Value</span><strong>${{ bookingTotal.toLocaleString() }}</strong></div>
            <div><span>Processing Fees</span><strong>${{ processingFee.toLocaleString() }}</strong></div>
            <div class="deposit-row"><span>Deposit Paid (30%)</span><strong>${{ deposit.toLocaleString() }}</strong></div>
            <div><small>Remaining Balance</small><strong>${{ remaining.toLocaleString() }}</strong></div>
          </div>
        </div>

        <footer class="receipt-foot">
          <span>Secured by Achar Protection</span>
          <span>Issued on {{ paidDateLabel }}</span>
        </footer>
      </section>

      <div class="confirmed-actions">
        <button type="button" class="btn-light" @click="goReceipt">Download PDF Receipt</button>
        <button type="button" class="btn-primary" @click="goDashboard">Go to My Event Dashboard</button>
      </div>
    </main>
  </div>
</template>

<style scoped>
.confirmed-page {
  min-height: 100vh;
  background: #eff2f7;
}

.confirmed-header {
  height: 66px;
  background: #fff;
  border-bottom: 1px solid #d9e1ee;
  padding: 0 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.brand-logo {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid #f2d2bb;
  padding: 2px;
  background: #fff;
  object-fit: contain;
}

.brand strong {
  display: block;
  font-size: 26px;
  line-height: 1;
  color: #111827;
}

.brand p {
  margin: 1px 0 0;
  color: #7185a1;
  font-size: 13px;
}

.confirmed-header a {
  color: #1f2937;
  text-decoration: none;
  font-weight: 500;
  font-size: 13px;
}

.confirmed-shell {
  width: min(680px, calc(100% - 1.25rem));
  margin: 22px auto 14px;
  text-align: center;
}

.status-wrap {
  display: flex;
  justify-content: center;
}

.status-icon {
  width: 50px;
  height: 50px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 24px;
  font-weight: 700;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  box-shadow: 0 0 0 8px rgba(245, 158, 11, 0.14);
}

.confirmed-shell h1 {
  margin: 12px 0 6px;
  color: #0f172a;
  font-size: clamp(1.55rem, 2.6vw, 2.2rem);
  line-height: 1.1;
}

.subtitle {
  margin: 0;
  color: #5f728d;
  font-size: 13px;
}

.receipt-card {
  margin-top: 10px;
  background: #fff;
  border: 1px solid #d6deea;
  border-radius: 14px;
  overflow: hidden;
  text-align: left;
}

.receipt-head {
  padding: 12px 14px;
  background: linear-gradient(120deg,  #f59e0b 0%, #f97316 100%);
  color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 12px;
}

.receipt-head small {
  display: block;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-size: 10px;
  opacity: 0.88;
}

.receipt-head strong {
  display: block;
  margin-top: 3px;
  font-size: 24px;
  line-height: 1;
}

.receipt-id {
  text-align: right;
}

.receipt-id strong {
  color: #67e8f9;
  font-size: 22px;
}

.receipt-body {
  padding: 10px 14px 12px;
}

.receipt-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.receipt-meta small {
  display: block;
  color: #9aaac2;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-size: 10px;
}

.receipt-meta strong {
  display: block;
  margin-top: 2px;
  font-size: 18px;
  color: #0f172a;
}

.paid-pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: #d8f4df;
  color: #166534;
  font-weight: 700;
  font-size: 11px;
}

.service-list {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #dce3ef;
  display: grid;
  gap: 7px;
}

.service-list article {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.service-list strong {
  color: #0f172a;
  font-size: 16px;
}

.service-list p {
  margin: 2px 0 0;
  color: #64748b;
  font-size: 13px;
}

.service-list span {
  color: #334155;
  font-size: 18px;
  font-weight: 700;
}

.totals-box {
  margin-top: 10px;
  border: 1px solid #d6dfee;
  border-radius: 12px;
  padding: 10px 12px;
  background: #f6f9fd;
  display: grid;
  gap: 6px;
}

.totals-box div {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.totals-box span {
  color: #475569;
  font-size: 10px;
}

.totals-box strong {
  color: #0f172a;
  font-size: 18px;
}

.totals-box .deposit-row strong {
  color: #f97316;
  font-size: 24px;
}

.totals-box small {
  color: #97a8c0;
  text-transform: uppercase;
  font-size: 10px;
}

.receipt-foot {
  border-top: 1px solid #dce3ef;
  padding: 8px 16px;
  color: #8da0bb;
  font-size: 10px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.confirmed-actions {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.btn-light,
.btn-primary {
  min-height: 40px;
  border-radius: 11px;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
}

.btn-light {
  border: 1px solid #cfd8e6;
  background: #fff;
  color: #1f2937;
}

.btn-primary {
  border: 1px solid #ea580c;
  color: #fff;
  background: linear-gradient(135deg, #fb7b1d 0%, #ea580c 100%);
  box-shadow: 0 6px 12px rgba(234, 88, 12, 0.22);
}

@media (max-width: 760px) {
  .confirmed-header {
    height: 50px;
    padding: 0 12px;
  }

  .brand strong {
    font-size: 16px;
  }

  .brand p {
    font-size: 11px;
  }

  .confirmed-shell {
    width: calc(100% - 1rem);
    margin-top: 10px;
  }

  .status-icon {
    width: 52px;
    height: 52px;
    font-size: 28px;
  }

  .confirmed-shell h1 {
    font-size: 24px;
  }

  .subtitle {
    font-size: 13px;
  }

  .receipt-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .receipt-id {
    text-align: left;
  }

  .receipt-head strong {
    font-size: 38px;
  }

  .receipt-id strong {
    font-size: 28px;
  }

  .receipt-meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .receipt-meta strong {
    font-size: 30px;
  }

  .service-list article {
    flex-direction: column;
  }

  .service-list strong {
    font-size: 24px;
  }

  .service-list span {
    font-size: 20px;
  }

  .totals-box strong {
    font-size: 26px;
  }

  .totals-box .deposit-row strong {
    font-size: 30px;
  }

  .receipt-foot {
    flex-direction: column;
    gap: 4px;
  }

  .confirmed-actions {
    grid-template-columns: 1fr;
  }
}
</style>






