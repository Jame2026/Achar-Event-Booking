<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import html2pdf from "html2pdf.js";
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
const amountPaid = computed(() => Number((deposit + processingFee).toFixed(2)));
const grandTotal = computed(() => Number((bookingTotal + processingFee).toFixed(2)));
const issuedDate = computed(() => {
  const value = receipt?.paidAt ? new Date(receipt.paidAt) : new Date();
  return value.toLocaleDateString();
});
const receiptNo = computed(() => {
  const datePart = new Date().toISOString().slice(0, 10).replace(/-/g, "");
  const idPart = Date.now().toString().slice(-4);
  return `REC-${datePart}-${idPart}`;
});
const receiptSheetRef = ref(null);

function goDashboard() {
  router.push("/legacy-app?page=dashboard");
}

function printReceipt() {
  window.print();
}

function downloadPdf() {
  if (!receiptSheetRef.value) return;
  const options = {
    margin: [8, 8, 8, 8],
    filename: `${receiptNo.value}.pdf`,
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true },
    jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
  };
  html2pdf().set(options).from(receiptSheetRef.value).save();
}

const copyByLanguage = {
  en: {
    back: "Back to Dashboard",
    downloadPdf: "Download PDF",
    printReceipt: "Print Receipt",
    officialReceipt: "Official Receipt",
    receiptNo: "Receipt No.",
    dateIssued: "Date Issued",
    customerDetails: "Customer Details",
    guestUser: "Guest User",
    noPhone: "No phone provided",
    noEmail: "No email provided",
    eventInfo: "Event Information",
    selectedVendor: "Selected Vendor",
    dateTbd: "Date TBD",
    locationTbd: "Location TBD",
    serviceDescription: "Service Description",
    qty: "Qty",
    unitPrice: "Unit Price",
    amount: "Amount",
    serviceItem: "Service item",
    processingFee: "Service Fee",
    feeText: "Platform service fee paid during deposit checkout.",
    authenticity: "Authenticity",
    authenticityText: "Scan this QR code to verify this official receipt on the Achar network.",
    grandTotal: "Grand Total",
    amountPaid: "Amount Paid",
    balanceDue: "Balance Due",
    disclaimer: "This is a computer-generated document. No signature is required. For inquiries, contact support@achar.com.kh.",
  },
  km: {
    back: "ត្រឡប់ទៅផ្ទាំងគ្រប់គ្រង",
    downloadPdf: "ទាញយក PDF",
    printReceipt: "បោះពុម្ពបង្កាន់ដៃ",
    officialReceipt: "បង្កាន់ដៃផ្លូវការ",
    receiptNo: "លេខបង្កាន់ដៃ",
    dateIssued: "កាលបរិច្ឆេទចេញ",
    customerDetails: "ព័ត៌មានអតិថិជន",
    guestUser: "អ្នកប្រើភ្ញៀវ",
    noPhone: "គ្មានលេខទូរស័ព្ទ",
    noEmail: "គ្មានអ៊ីមែល",
    eventInfo: "ព័ត៌មានព្រឹត្តិការណ៍",
    selectedVendor: "អ្នកផ្គត់ផ្គង់ដែលបានជ្រើស",
    dateTbd: "កាលបរិច្ឆេទមិនទាន់កំណត់",
    locationTbd: "ទីតាំងមិនទាន់កំណត់",
    serviceDescription: "ពិពណ៌នាសេវាកម្ម",
    qty: "ចំនួន",
    unitPrice: "តម្លៃឯកតា",
    amount: "ចំនួនទឹកប្រាក់",
    serviceItem: "ធាតុសេវាកម្ម",
    processingFee: "កម្រៃដំណើរការ",
    feeText: "កម្រៃវេទិកា និងសម្របសម្រួល (2%)",
    authenticity: "ភាពត្រឹមត្រូវ",
    authenticityText: "ស្កេន QR នេះ ដើម្បីផ្ទៀងផ្ទាត់បង្កាន់ដៃផ្លូវការនេះនៅលើបណ្តាញ Achar។",
    grandTotal: "សរុបទាំងមូល",
    amountPaid: "បានបង់",
    balanceDue: "សមតុល្យត្រូវបង់",
    disclaimer: "នេះជាឯកសារបង្កើតដោយកុំព្យូទ័រ។ មិនចាំបាច់ហត្ថលេខាទេ។ សម្រាប់សំណួរ សូមទាក់ទង support@achar.com.kh។",
  },
  zh: {
    back: "返回控制台",
    downloadPdf: "下载 PDF",
    printReceipt: "打印收据",
    officialReceipt: "官方收据",
    receiptNo: "收据编号",
    dateIssued: "签发日期",
    customerDetails: "客户信息",
    guestUser: "访客用户",
    noPhone: "未提供电话",
    noEmail: "未提供邮箱",
    eventInfo: "活动信息",
    selectedVendor: "已选商家",
    dateTbd: "日期待定",
    locationTbd: "地点待定",
    serviceDescription: "服务说明",
    qty: "数量",
    unitPrice: "单价",
    amount: "金额",
    serviceItem: "服务项目",
    processingFee: "手续费",
    feeText: "平台与协调服务费 (2%)",
    authenticity: "真伪验证",
    authenticityText: "扫描此二维码以在 Achar 网络上验证该官方收据。",
    grandTotal: "总计",
    amountPaid: "已支付",
    balanceDue: "应付余额",
    disclaimer: "此为电脑生成文件，无需签名。如有疑问，请联系 support@achar.com.kh。",
  },
};
const uiText = computed(() => copyByLanguage[language.value] || copyByLanguage.en);
</script>

<template>
  <div class="receipt-page">
    <div class="receipt-toolbar no-print">
      <button type="button" class="back-link" @click="goDashboard">
        <span class="back-icon" aria-hidden="true">&larr;</span>
        <span>{{ uiText.back }}</span>
      </button>
      <div class="toolbar-actions">
        <button type="button" class="receipt-ghost-btn" @click="downloadPdf">{{ uiText.downloadPdf }}</button>
        <button type="button" class="receipt-primary-btn" @click="printReceipt">{{ uiText.printReceipt }}</button>
      </div>
    </div>

    <section ref="receiptSheetRef" class="receipt-sheet">
      <header class="sheet-head">
        <div class="brand-block">
          <div class="brand-top">
            <img class="brand-logo" :src="appLogoSrc" alt="Achar logo" @error="onLogoError" />
            <h1 class="brand-name">Achar</h1>
          </div>
          <div class="brand-meta">
            <p>Achar Event Solutions Co., Ltd.</p>
            <p>#124, Street 19, Sangkat Wat Phnom</p>
            <p>Khan Daun Penh, Phnom Penh, Cambodia</p>
          </div>
        </div>

        <div class="receipt-meta">
          <p class="meta-kicker">{{ uiText.officialReceipt }}</p>
          <div class="meta-item">
            <span>{{ uiText.receiptNo }}</span>
            <strong>{{ receiptNo }}</strong>
          </div>
          <div class="meta-item">
            <span>{{ uiText.dateIssued }}</span>
            <strong>{{ issuedDate }}</strong>
          </div>
        </div>
      </header>

      <div class="info-row">
        <article>
          <h3>{{ uiText.customerDetails }}</h3>
          <strong>{{ booking.fullName || uiText.guestUser }}</strong>
          <p>{{ booking.phone || uiText.noPhone }}</p>
          <p>{{ booking.email || uiText.noEmail }}</p>
        </article>
        <article>
          <h3>{{ uiText.eventInfo }}</h3>
          <strong>{{ booking.vendorTitle || uiText.selectedVendor }}</strong>
          <p>{{ booking.eventDate || uiText.dateTbd }}</p>
          <p>{{ booking.location || uiText.locationTbd }}</p>
        </article>
      </div>

      <div class="items-head">
        <span>{{ uiText.serviceDescription }}</span>
        <span>{{ uiText.qty }}</span>
        <span>{{ uiText.unitPrice }}</span>
        <span>{{ uiText.amount }}</span>
      </div>

      <div class="items-body">
        <article v-for="(item, index) in items" :key="`${item.type}-${item.name}-${index}`" class="item-row">
          <div>
            <strong>{{ item.name }}</strong>
            <p>{{ item.description || uiText.serviceItem }}</p>
          </div>
          <span>{{ Number(item.qty || 1) }}</span>
          <span>${{ Number(item.unitPrice || item.totalPrice || 0).toLocaleString() }}</span>
          <strong>${{ Number(item.totalPrice || 0).toLocaleString() }}</strong>
        </article>

        <article class="item-row fee-row">
          <div>
            <strong>{{ uiText.processingFee }}</strong>
            <p>{{ uiText.feeText }}</p>
          </div>
          <span>1</span>
          <span>${{ processingFee.toLocaleString() }}</span>
          <strong>${{ processingFee.toLocaleString() }}</strong>
        </article>
      </div>

      <footer class="sheet-foot">
        <div class="verify-block">
          <div class="qr-box" aria-hidden="true">
            <svg viewBox="0 0 120 120" role="img">
              <rect width="120" height="120" fill="#fff" />
              <rect x="8" y="8" width="26" height="26" fill="#111827" />
              <rect x="12" y="12" width="18" height="18" fill="#fff" />
              <rect x="16" y="16" width="10" height="10" fill="#111827" />
              <rect x="86" y="8" width="26" height="26" fill="#111827" />
              <rect x="90" y="12" width="18" height="18" fill="#fff" />
              <rect x="94" y="16" width="10" height="10" fill="#111827" />
              <rect x="8" y="86" width="26" height="26" fill="#111827" />
              <rect x="12" y="90" width="18" height="18" fill="#fff" />
              <rect x="16" y="94" width="10" height="10" fill="#111827" />
              <rect x="50" y="48" width="8" height="8" fill="#111827" />
              <rect x="62" y="48" width="8" height="8" fill="#111827" />
              <rect x="50" y="60" width="8" height="8" fill="#111827" />
              <rect x="62" y="72" width="8" height="8" fill="#111827" />
            </svg>
          </div>
          <div>
            <h4>{{ uiText.authenticity }}</h4>
            <p>{{ uiText.authenticityText }}</p>
          </div>
        </div>

        <div class="totals-panel">
          <p><span>{{ uiText.grandTotal }}</span><strong>${{ grandTotal.toLocaleString() }}</strong></p>
          <p class="paid"><span>{{ uiText.amountPaid }}</span><strong>-${{ amountPaid.toLocaleString() }}</strong></p>
          <p class="due"><span>{{ uiText.balanceDue }}</span><strong>${{ remaining.toLocaleString() }}</strong></p>
        </div>
      </footer>

      <div class="disclaimer">
        {{ uiText.disclaimer }}
      </div>
    </section>
  </div>
</template>

<style scoped>
.receipt-page {
  --receipt-accent: #f59e0b;
  --receipt-accent-strong: #d97706;
  min-height: 100vh;
  background:
    radial-gradient(circle at 8% 0%, rgba(245, 158, 11, 0.14), transparent 32%),
    radial-gradient(circle at 94% 10%, rgba(59, 130, 246, 0.09), transparent 34%),
    #eceff4;
  padding: 16px 10px 24px;
}

.receipt-toolbar {
  width: min(760px, calc(100% - 1rem));
  margin: 0 auto 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
  border: 1px solid #d9e2ef;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.88);
  backdrop-filter: blur(8px);
}

.back-link {
  border: 1px solid #cfd8e6;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  color: #1f2f49;
  min-height: 36px;
  padding: 0 12px 0 8px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.back-link:hover {
  border-color: #9eb1cd;
  box-shadow: 0 6px 14px rgba(17, 24, 39, 0.08);
  transform: translateY(-1px);
}

.back-icon {
  width: 22px;
  height: 22px;
  border-radius: 999px;
  background: #eef3fb;
  color: #334155;
  display: grid;
  place-items: center;
  font-size: 13px;
  line-height: 1;
}

.toolbar-actions {
  display: flex;
  gap: 10px;
}

.receipt-ghost-btn,
.receipt-primary-btn {
  min-height: 36px;
  border-radius: 9px;
  padding: 0 14px;
  font: inherit;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.receipt-ghost-btn {
  border: 1px solid #cfd8e6;
  background: #fff;
  color: #0f172a;
}

.receipt-primary-btn {
  border: 1px solid var(--receipt-accent);
  background: linear-gradient(120deg, var(--receipt-accent), var(--receipt-accent-strong));
  color: #fff;
}

.receipt-sheet {
  --sheet-gutter: 16px;
  width: min(760px, calc(100% - 1rem));
  margin: 0 auto;
  background: #fff;
  border: 1px solid #d8e2ee;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
  overflow: hidden;
}

.items-body {
  display: block;
}

.sheet-head {
  padding: var(--sheet-gutter);
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 14px;
  border-bottom: 1px solid #e4ebf3;
  background:
    radial-gradient(circle at 100% 0%, rgba(245, 158, 11, 0.08), transparent 45%),
    linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.brand-block {
  display: grid;
  gap: 8px;
  align-content: start;
}

.brand-top {
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.brand-logo {
  width: 72px;
  height: 72px;
  border-radius: 16px;
  border: 1px solid #f2d2bb;
  padding: 6px;
  background: #fff;
  object-fit: contain;
  box-shadow: 0 8px 16px rgba(209, 116, 28, 0.14);
}

.brand-name {
  margin: 0;
  font-size: 26px;
  line-height: 1;
  color: #cc620f;
  font-weight: 800;
}

.brand-meta {
  padding-left: 0;
}

.brand-meta p {
  margin: 2px 0;
  color: #64748b;
  font-size: 12px;
}

.receipt-meta {
  min-width: 270px;
  border: 1px solid #d7e2ef;
  border-radius: 12px;
  padding: 10px 12px;
  background: #ffffff;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
}

.meta-kicker {
  margin: 0 0 8px;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #64748b;
  font-weight: 700;
}

.meta-item {
  padding: 8px 0;
  display: grid;
  gap: 2px;
}

.meta-item + .meta-item {
  border-top: 1px solid #e7edf6;
}

.meta-item span {
  color: #94a3b8;
  font-size: 10px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  font-weight: 700;
}

.meta-item strong {
  font-size: 20px;
  color: #0f172a;
  line-height: 1.2;
}

.info-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-bottom: 1px solid #e4ebf3;
}

.info-row article {
  padding: 12px var(--sheet-gutter);
}

.info-row article + article {
  border-left: 1px solid #e4ebf3;
}

.info-row h3 {
  margin: 0 0 10px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 11px;
}

.info-row strong {
  display: block;
  color: #0f172a;
  font-size: 16px;
}

.info-row p {
  margin: 4px 0 0;
  color: #475569;
  font-size: 13px;
}

.items-head {
  padding: 10px var(--sheet-gutter);
  border-bottom: 1px solid #e4ebf3;
  display: grid;
  grid-template-columns: 1fr 56px 96px 96px;
  gap: 10px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 11px;
}

.item-row {
  padding: 10px var(--sheet-gutter);
  border-bottom: 1px solid #edf2f8;
  display: grid;
  grid-template-columns: 1fr 56px 96px 96px;
  gap: 10px;
  align-items: center;
}

.item-row > span,
.item-row > strong {
  text-align: right;
}

.item-row strong {
  font-size: 15px;
  color: #111827;
}

.item-row p {
  margin: 2px 0 0;
  color: #64748b;
  font-size: 12px;
}

.fee-row {
  background: #fbfdff;
}

.sheet-foot {
  padding: 12px var(--sheet-gutter);
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 12px;
  border-top: 1px solid #e4ebf3;
}

.verify-block {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 12px;
  align-items: start;
}

.qr-box {
  width: 64px;
  height: 64px;
  border: 1px solid #d4ddec;
  border-radius: 8px;
  background: #fff;
  padding: 6px;
}

.qr-box svg {
  width: 100%;
  height: 100%;
}

.verify-block h4 {
  margin: 0 0 4px;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 11px;
}

.verify-block p {
  margin: 0;
  color: #64748b;
  font-size: 12px;
  line-height: 1.5;
}

.totals-panel {
  display: grid;
  gap: 8px;
}

.totals-panel p {
  margin: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #334155;
}

.totals-panel p span {
  font-size: 13px;
}

.totals-panel p strong {
  font-size: 19px;
  color: #0f172a;
}

.totals-panel p.paid strong {
  color: #15803d;
}

.totals-panel p.due strong {
  color: #ea580c;
  font-size: 26px;
}

.disclaimer {
  border-top: 1px solid #edf2f8;
  text-align: center;
  color: #94a3b8;
  font-size: 11px;
  padding: 12px;
}

@media (max-width: 860px) {
  .receipt-toolbar {
    flex-direction: column;
    align-items: stretch;
    padding: 8px;
  }

  .toolbar-actions {
    justify-content: stretch;
  }

  .receipt-ghost-btn,
  .receipt-primary-btn {
    width: 100%;
    min-height: 40px;
  }

  .sheet-head,
  .info-row,
  .sheet-foot,
  .item-row,
  .items-head {
    grid-template-columns: 1fr;
  }

  .receipt-meta,
  .item-row > span,
  .item-row > strong {
    text-align: left;
  }

  .receipt-meta {
    min-width: 0;
    width: 100%;
  }

  .brand-logo {
    width: 58px;
    height: 58px;
    border-radius: 12px;
  }

  .brand-name {
    font-size: 22px;
  }

  .brand-meta {
    padding-left: 0;
  }

  .info-row article + article {
    border-left: 0;
    border-top: 1px solid #e4ebf3;
  }
}

@media print {
  @page {
    size: A4 portrait;
    margin: 10mm;
  }

  html,
  body {
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .no-print,
  .receipt-toolbar {
    display: none !important;
  }

  .receipt-page {
    background: #fff;
    padding: 0 !important;
    min-height: auto;
  }

  .receipt-sheet {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    border: 1px solid #d8e2ee;
    border-radius: 0;
    box-shadow: none;
    overflow: visible;
  }

  .sheet-head,
  .info-row article,
  .items-head,
  .item-row,
  .sheet-foot,
  .disclaimer {
    break-inside: avoid-page;
  }

  .sheet-head {
    padding: 8mm 8mm 6mm;
  }

  .items-head,
  .item-row {
    grid-template-columns: 1fr 18mm 28mm 28mm;
    gap: 3mm;
  }

  .items-head {
    font-size: 10px;
  }

  .item-row {
    font-size: 12px;
  }

  .item-row strong {
    font-size: 13px;
  }

  .sheet-foot {
    grid-template-columns: 1fr 72mm;
  }

  .totals-panel p strong {
    font-size: 16px;
  }

  .totals-panel p.due strong {
    font-size: 22px;
  }
}
</style>

