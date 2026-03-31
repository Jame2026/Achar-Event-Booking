<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useLanguage } from "../features/language";

const router = useRouter();
const { language } = useLanguage();
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
const amountPaidToday = computed(() => Number((deposit + processingFee).toFixed(2)));
const bookingId = computed(() => {
  const base = Date.now().toString().slice(-6);
  return `#AC-${new Date().getFullYear()}-${base}`;
});
const pageTitle = computed(() => "Deposit Paid and Booking Sent!");
const pageSubtitle = computed(
  () => "Your 30% deposit and service fee were received. The vendor can now approve or cancel this booking.",
);
const badgeLabel = computed(() => "Awaiting Vendor Approval");
const issuedLabel = computed(() => {
  const value = receipt?.paidAt || receipt?.requestedAt;
  const date = value ? new Date(value) : new Date();
  return date.toLocaleString();
});

function goDashboard() {
  router.push("/legacy-app?page=dashboard");
}

function goReceipt() {
  router.push("/checkout/receipt");
}

const copyByLanguage = {
  en: {
    title: "Deposit Paid and Booking Sent!",
    subtitle: "Your 30% deposit and service fee were received. The vendor can now review this booking.",
    digitalReceipt: "Digital Receipt",
    bookingId: "Booking ID",
    eventDate: "Event Date",
    depositPaid: "Deposit Paid",
    serviceItem: "Service item",
    totalBooking: "Total Booking Value",
    processingFees: "Processing Fees",
    deposit30: "Deposit Paid (30%)",
    remaining: "Remaining Balance",
    secured: "Secured by Achar Protection",
    issuedOn: "Issued on",
    downloadPdf: "Download PDF Receipt",
    dashboard: "Go to My Event Dashboard",
  },
  km: {
    title: "ព្រឹត្តិការណ៍របស់អ្នករួចរាល់ហើយ!",
    subtitle: "ការកក់របស់អ្នកត្រូវបានបញ្ជាក់។ ច្បាប់ចម្លងបង្កាន់ដៃត្រូវបានផ្ញើទៅអ៊ីមែលរបស់អ្នក។",
    digitalReceipt: "បង្កាន់ដៃឌីជីថល",
    bookingId: "លេខកក់",
    eventDate: "កាលបរិច្ឆេទព្រឹត្តិការណ៍",
    depositPaid: "បានបង់ប្រាក់កក់",
    serviceItem: "ធាតុសេវាកម្ម",
    totalBooking: "តម្លៃសរុបការកក់",
    processingFees: "កម្រៃដំណើរការ",
    deposit30: "ប្រាក់កក់បានបង់ (30%)",
    remaining: "សមតុល្យនៅសល់",
    secured: "ការពារដោយ Achar",
    issuedOn: "ចេញនៅ",
    downloadPdf: "ទាញយកបង្កាន់ដៃ PDF",
    dashboard: "ទៅផ្ទាំងគ្រប់គ្រងព្រឹត្តិការណ៍របស់ខ្ញុំ",
  },
  zh: {
    title: "您的庆典已安排完成！",
    subtitle: "您的预订已确认，收据副本已发送到您的邮箱。",
    digitalReceipt: "电子收据",
    bookingId: "预订编号",
    eventDate: "活动日期",
    depositPaid: "定金已支付",
    serviceItem: "服务项目",
    totalBooking: "订单总额",
    processingFees: "手续费",
    deposit30: "已付定金 (30%)",
    remaining: "剩余尾款",
    secured: "由 Achar 提供保障",
    issuedOn: "签发时间",
    downloadPdf: "下载 PDF 收据",
    dashboard: "前往我的活动控制台",
  },
};
const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);
</script>

<template>
  <div class="confirmed-page"><main class="confirmed-shell">
      <div class="status-wrap">
        <div class="status-icon">&#10003;</div>
      </div>
      <h1>{{ pageTitle }}</h1>
      <p class="subtitle">{{ pageSubtitle }}</p>

      <section class="receipt-card">
        <div class="receipt-head">
          <div>
            <small>{{ uiText.digitalReceipt }}</small>
            <strong>Achar Event Planning</strong>
          </div>
          <div class="receipt-id">
            <small>{{ uiText.bookingId }}</small>
            <strong>{{ bookingId }}</strong>
          </div>
        </div>

        <div class="receipt-body">
          <div class="receipt-meta">
            <div>
              <small>{{ uiText.eventDate }}</small>
              <strong>{{ booking.eventDate || "TBD" }}</strong>
            </div>
            <div class="paid-pill pending">{{ badgeLabel }}</div>
          </div>

          <div class="service-list">
            <article v-for="item in items" :key="`${item.type}-${item.name}`">
              <div>
                <strong>{{ item.name }}</strong>
                <p>{{ item.description || uiText.serviceItem }}</p>
              </div>
              <span>${{ Number(item.totalPrice || 0).toLocaleString() }}</span>
            </article>
          </div>

          <div class="totals-box">
            <div><span>{{ uiText.totalBooking }}</span><strong>${{ bookingTotal.toLocaleString() }}</strong></div>
            <div><span>{{ uiText.processingFees }}</span><strong>${{ processingFee.toLocaleString() }}</strong></div>
            <div class="deposit-row"><span>{{ uiText.deposit30 }}</span><strong>${{ deposit.toLocaleString() }}</strong></div>
            <div><span>Paid Today</span><strong>${{ amountPaidToday.toLocaleString() }}</strong></div>
            <div><small>{{ uiText.remaining }}</small><strong>${{ remaining.toLocaleString() }}</strong></div>
          </div>
        </div>

        <footer class="receipt-foot">
          <span>{{ uiText.secured }}</span>
          <span>{{ uiText.issuedOn }} {{ issuedLabel }}</span>
        </footer>
      </section>

      <div class="confirmed-actions">
        <button type="button" class="btn-light" @click="goReceipt">{{ uiText.downloadPdf }}</button>
        <button type="button" class="btn-primary" @click="goDashboard">{{ uiText.dashboard }}</button>
      </div>
    </main>
  </div>
</template>

<style scoped>
.confirmed-page {
  --flow-bg: #eef2f8;
  --flow-border: #d8e2ef;
  --flow-muted: #64748b;
  --flow-accent: #f97316;
  min-height: 100vh;
  background:
    radial-gradient(circle at 12% 0%, rgba(249, 115, 22, 0.12), transparent 36%),
    radial-gradient(circle at 90% 10%, rgba(14, 165, 233, 0.12), transparent 32%),
    var(--flow-bg);
}

.confirmed-shell {
  width: min(760px, calc(100% - 1.25rem));
  margin: 28px auto 18px;
  text-align: center;
}

.status-wrap {
  display: flex;
  justify-content: center;
}

.status-icon {
  width: 64px;
  height: 64px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  color: #fff;
  font-size: 30px;
  font-weight: 700;
  background: linear-gradient(135deg, #f59e0b, #f97316);
  box-shadow: 0 0 0 10px rgba(245, 158, 11, 0.14);
}

.confirmed-shell h1 {
  margin: 14px 0 6px;
  color: #0f172a;
  font-size: clamp(2rem, 3vw, 2.8rem);
  line-height: 1.1;
  letter-spacing: -0.02em;
}

.subtitle {
  margin: 0;
  color: #5f728d;
  font-size: 15px;
}

.receipt-card {
  margin-top: 16px;
  background: #fff;
  border: 1px solid var(--flow-border);
  border-radius: 18px;
  overflow: hidden;
  text-align: left;
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
}

.receipt-head {
  padding: 14px 16px;
  background: linear-gradient(120deg, #f59e0b 0%, #f97316 100%);
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
  font-size: 28px;
  line-height: 1;
  letter-spacing: -0.02em;
}

.receipt-id {
  text-align: right;
}

.receipt-id strong {
  color: #67e8f9;
  font-size: 22px;
}

.receipt-body {
  padding: 14px 16px;
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
  font-size: 20px;
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

.paid-pill.pending {
  background: #ffedd5;
  color: #c2410c;
}

.service-list {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #dce3ef;
  display: grid;
  gap: 10px;
}

.service-list article {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.service-list strong {
  color: #0f172a;
  font-size: 17px;
}

.service-list p {
  margin: 2px 0 0;
  color: #64748b;
  font-size: 13px;
}

.service-list span {
  color: #334155;
  font-size: 19px;
  font-weight: 700;
}

.totals-box {
  margin-top: 12px;
  border: 1px solid #d6dfee;
  border-radius: 12px;
  padding: 12px;
  background: #f6f9fd;
  display: grid;
  gap: 8px;
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
  font-size: 19px;
}

.totals-box .deposit-row strong {
  color: #f97316;
  font-size: 26px;
}

.totals-box small {
  color: #97a8c0;
  text-transform: uppercase;
  font-size: 10px;
}

.receipt-foot {
  border-top: 1px solid #dce3ef;
  padding: 10px 16px;
  color: #8da0bb;
  font-size: 11px;
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.confirmed-actions {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.btn-light,
.btn-primary {
  min-height: 44px;
  border-radius: 12px;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
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

.btn-light:hover,
.btn-primary:hover {
  transform: translateY(-1px);
}

@media (max-width: 760px) {
  .confirmed-shell {
    width: calc(100% - 1rem);
    margin-top: 10px;
  }

  .status-icon {
    width: 56px;
    height: 56px;
    font-size: 26px;
  }

  .confirmed-shell h1 {
    font-size: 30px;
  }

  .subtitle {
    font-size: 14px;
  }

  .receipt-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .receipt-id {
    text-align: left;
  }

  .receipt-meta {
    flex-direction: column;
    align-items: flex-start;
  }

  .service-list article {
    flex-direction: column;
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






