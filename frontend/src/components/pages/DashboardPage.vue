<script setup>
import { computed } from 'vue'
import { useLanguageCopy } from '../../features/language'

const props = defineProps([
  'notice',
  'customerName',
  'dashboardStats',
  'recentBookings',
  'recentConversations',
  'goToVendor',
  'goToBookings',
  'goToMessages',
  'goToPackageCustomization',
  'openUpcomingBookings',
  'logoutUser',
])

const copyByLanguage = {
  en: {
    breadcrumbs: 'Home > Dashboard',
    workspace: 'Planner Workspace',
    welcome: 'Welcome back,',
    planner: 'Planner',
    subtitle: 'Track your planning progress, follow updates, and move your events forward in one place.',
    upcoming: 'upcoming',
    completed: 'completed',
    activeChats: 'active chats',
    viewVendors: 'View Vendors',
    viewBookings: 'View Bookings',
    totalBookings: 'Total Bookings',
    allPlannedEvents: 'All planned events',
    nextMilestone: 'Next milestone focus',
    successfullyDelivered: 'Successfully delivered',
    newMessages: 'New Messages',
    unreadThisSession: 'Unread this session',
    recentBookings: 'Recent Bookings',
    seeAll: 'See all',
    recentMessages: 'Recent Messages',
    openInbox: 'Open inbox',
    quickActions: 'Quick Actions',
    browseVendors: 'Browse Vendors',
    browseVendorsSub: 'Explore categories and compare providers',
    bookEventPackage: 'Book Event Package',
    bookEventPackageSub: 'Reserve a service in a few steps',
    manageUpcoming: 'Manage Upcoming Events',
    manageUpcomingSub: 'Review confirmations and next actions',
    contactVendors: 'Contact Vendors',
    contactVendorsSub: 'Follow up and keep conversations moving',
    quickNav: 'Quick access',
    backHome: 'Back to Home',
    settings: 'Settings',
    logout: 'Logout',
    verifiedWorkspace: 'Verified member workspace',
  },
  km: {
    breadcrumbs: 'ទំព័រដើម > ផ្ទាំងគ្រប់គ្រង',
    workspace: 'កន្លែងធ្វើការអ្នករៀបចំ',
    welcome: 'សូមស្វាគមន៍មកវិញ,',
    planner: 'អ្នករៀបចំ',
    subtitle: 'តាមដានវឌ្ឍនភាព ការធ្វើបច្ចុប្បន្នភាព និងជំរុញព្រឹត្តិការណ៍របស់អ្នកនៅកន្លែងតែមួយ។',
    upcoming: 'នាពេលខាងមុខ',
    completed: 'បានបញ្ចប់',
    activeChats: 'ការជជែកសកម្ម',
    viewVendors: 'មើលអ្នកផ្គត់ផ្គង់',
    viewBookings: 'មើលការកក់',
    totalBookings: 'ការកក់សរុប',
    allPlannedEvents: 'ព្រឹត្តិការណ៍ដែលបានរៀបចំទាំងអស់',
    nextMilestone: 'គោលដៅបន្ទាប់',
    successfullyDelivered: 'បានបញ្ចប់ដោយជោគជ័យ',
    newMessages: 'សារថ្មី',
    unreadThisSession: 'មិនទាន់អានក្នុងសម័យនេះ',
    recentBookings: 'ការកក់ថ្មីៗ',
    seeAll: 'មើលទាំងអស់',
    recentMessages: 'សារថ្មីៗ',
    openInbox: 'បើកប្រអប់សារ',
    quickActions: 'សកម្មភាពរហ័ស',
    browseVendors: 'រកមើលអ្នកផ្គត់ផ្គង់',
    browseVendorsSub: 'ស្វែងរកប្រភេទ និងប្រៀបធៀបអ្នកផ្តល់សេវា',
    bookEventPackage: 'កក់កញ្ចប់ព្រឹត្តិការណ៍',
    bookEventPackageSub: 'កក់សេវាកម្មក្នុងពីរបីជំហាន',
    manageUpcoming: 'គ្រប់គ្រងព្រឹត្តិការណ៍នាពេលខាងមុខ',
    manageUpcomingSub: 'ពិនិត្យការបញ្ជាក់ និងជំហានបន្ទាប់',
    contactVendors: 'ទាក់ទងអ្នកផ្គត់ផ្គង់',
    contactVendorsSub: 'តាមដាន និងរក្សាការសន្ទនាឲ្យដំណើរការ',
    quickNav: 'សកម្មភាពរហ័ស',
    backHome: 'ត្រឡប់ទៅទំព័រដើម',
    settings: 'ការកំណត់',
    logout: 'ចេញពីប្រព័ន្ធ',
    verifiedWorkspace: 'ពិព័រណ៍ធ្វើការ ដែលបានផ្ទៀងផ្ទាត់',
  },
  zh: {
    breadcrumbs: '首页 > 仪表盘',
    workspace: '策划工作台',
    welcome: '欢迎回来，',
    planner: '策划人',
    subtitle: '在一个地方跟踪策划进度、查看更新并推进您的活动。',
    upcoming: '即将开始',
    completed: '已完成',
    activeChats: '活跃对话',
    viewVendors: '查看商家',
    viewBookings: '查看预订',
    totalBookings: '预订总数',
    allPlannedEvents: '所有已规划活动',
    nextMilestone: '下一阶段重点',
    successfullyDelivered: '已顺利完成',
    newMessages: '新消息',
    unreadThisSession: '本次会话未读',
    recentBookings: '最近预订',
    seeAll: '查看全部',
    recentMessages: '最近消息',
    openInbox: '打开收件箱',
    quickActions: '快捷操作',
    browseVendors: '浏览商家',
    browseVendorsSub: '探索分类并比较服务商',
    bookEventPackage: '预订活动套餐',
    bookEventPackageSub: '几步内完成服务预订',
    manageUpcoming: '管理即将到来的活动',
    manageUpcomingSub: '查看确认信息和下一步',
    contactVendors: '联系商家',
    contactVendorsSub: '及时跟进并保持沟通',
    quickNav: '快捷入口',
    backHome: '返回首页',
    settings: '设置',
    logout: '退出登录',
    verifiedWorkspace: '已认证成员工作区',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)

const profileInitials = computed(() => {
  const base = String(props.customerName || uiText.value.planner || 'Achar User').trim()
  if (!base) return 'A'
  const parts = base.split(/\s+/).filter(Boolean)
  const first = parts[0]?.charAt(0) || ''
  const second = parts[1]?.charAt(0) || ''
  return (first + second || first || 'A').toUpperCase()
})

const profileName = computed(() => props.customerName || uiText.value.planner)

function handleLogout() {
  if (typeof props.logoutUser === 'function') {
    props.logoutUser()
  }
}
</script>

<template>
  <main class="shell dashboard-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

    <section class="dashboard-hero-layout">
      <section class="dashboard-head">
        <div class="dashboard-head-main">
          <span class="dash-chip">{{ uiText.workspace }}</span>
          <h1>{{ uiText.welcome }} {{ props.customerName || uiText.planner }}</h1>
          <p>{{ uiText.subtitle }}</p>
          <div class="dashboard-inline-stats">
            <span><strong>{{ props.dashboardStats.upcomingBookings }}</strong> {{ uiText.upcoming }}</span>
            <span><strong>{{ props.dashboardStats.completedBookings }}</strong> {{ uiText.completed }}</span>
            <span><strong>{{ props.dashboardStats.unreadMessages }}</strong> {{ uiText.activeChats }}</span>
          </div>
        </div>
        <div class="dashboard-actions">
          <button type="button" class="btn-light" @click="props.goToVendor()">{{ uiText.viewVendors }}</button>
          <button type="button" class="btn-accent" @click="props.goToBookings">{{ uiText.viewBookings }}</button>
        </div>
      </section>

      <aside class="dashboard-side">
        <div class="dash-quick-card">
          <p class="dash-quick-title">{{ uiText.quickNav }}</p>
          <div class="dash-quick-grid">
            <router-link to="/" class="dash-quick-btn back">
              <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 12L12 3l9 9" />
                  <path d="M5 10v10h5v-6h4v6h5V10" />
                </svg>
              </span>
              <span>{{ uiText.backHome }}</span>
            </router-link>
            <router-link to="/profile" class="dash-quick-btn settings">
              <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3" />
                  <path
                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 8 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 3.6 15a1.65 1.65 0 0 0-1.51-1H2a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 3.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 8 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09A1.65 1.65 0 0 0 15 4.6a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.7 0 1.32.4 1.6 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"
                  />
                </svg>
              </span>
              <span>{{ uiText.settings }}</span>
            </router-link>
            <button type="button" class="dash-quick-btn logout" @click="handleLogout">
              <span class="icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                  <polyline points="10 17 15 12 10 7" />
                  <line x1="15" y1="12" x2="3" y2="12" />
                </svg>
              </span>
              <span>{{ uiText.logout }}</span>
            </button>
          </div>
        </div>

        <div class="dash-profile-card">
          <div class="dash-profile-top">
            <div class="dash-avatar" aria-hidden="true">{{ profileInitials }}</div>
            <div class="dash-profile-text">
              <p class="dash-profile-name">{{ profileName }}</p>
              <p class="dash-profile-role">{{ uiText.workspace }}</p>
            </div>
            <span class="dash-verify" aria-label="Verified workspace">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5" />
              </svg>
            </span>
          </div>
          <p class="dash-profile-sub">{{ uiText.verifiedWorkspace }}</p>
        </div>
      </aside>
    </section>

    <p v-if="props.notice" class="notice">{{ props.notice }}</p>

    <section class="kpi-grid">
      <article class="card kpi-card">
        <p>{{ uiText.totalBookings }}</p>
        <strong>{{ props.dashboardStats.totalBookings }}</strong>
        <small>{{ uiText.allPlannedEvents }}</small>
      </article>
      <article class="card kpi-card">
        <p>{{ uiText.upcoming }}</p>
        <strong>{{ props.dashboardStats.upcomingBookings }}</strong>
        <small>{{ uiText.nextMilestone }}</small>
      </article>
      <article class="card kpi-card">
        <p>{{ uiText.completed }}</p>
        <strong>{{ props.dashboardStats.completedBookings }}</strong>
        <small>{{ uiText.successfullyDelivered }}</small>
      </article>
      <article class="card kpi-card">
        <p>{{ uiText.newMessages }}</p>
        <strong>{{ props.dashboardStats.unreadMessages }}</strong>
        <small>{{ uiText.unreadThisSession }}</small>
      </article>
    </section>

    <section class="dashboard-grid">
      <article class="card dashboard-card">
        <div class="dashboard-card-head">
          <h2>{{ uiText.recentBookings }}</h2>
          <a href="#" @click.prevent="props.goToBookings">{{ uiText.seeAll }}</a>
        </div>
        <div class="dashboard-list">
          <div v-for="item in props.recentBookings" :key="item.id" class="dashboard-item">
            <img :src="item.image" :alt="item.vendor" />
            <div>
              <strong>{{ item.service }}</strong>
              <p>{{ item.metaValue }}</p>
            </div>
            <span class="booking-status" :class="item.statusClass">{{ item.status }}</span>
          </div>
        </div>
      </article>

      <article class="card dashboard-card">
        <div class="dashboard-card-head">
          <h2>{{ uiText.recentMessages }}</h2>
          <a href="#" @click.prevent="props.goToMessages()">{{ uiText.openInbox }}</a>
        </div>
        <div class="dashboard-list">
          <div
            v-for="chat in props.recentConversations"
            :key="chat.id"
            class="dashboard-item message"
            @click="props.goToMessages(chat.name)"
          >
            <img :src="chat.image" :alt="chat.name" />
            <div>
              <strong>{{ chat.name }}</strong>
              <p>{{ chat.preview }}</p>
            </div>
            <span class="dash-time">{{ chat.time }}</span>
          </div>
        </div>
      </article>

      <article class="card dashboard-card wide">
        <div class="dashboard-card-head">
          <h2>{{ uiText.quickActions }}</h2>
        </div>
        <div class="quick-actions">
          <button type="button" @click="props.goToVendor()">
            <strong>{{ uiText.browseVendors }}</strong>
            <span>{{ uiText.browseVendorsSub }}</span>
          </button>
          <button type="button" @click="props.goToPackageCustomization()">
            <strong>{{ uiText.bookEventPackage }}</strong>
            <span>{{ uiText.bookEventPackageSub }}</span>
          </button>
          <button type="button" @click="props.openUpcomingBookings">
            <strong>{{ uiText.manageUpcoming }}</strong>
            <span>{{ uiText.manageUpcomingSub }}</span>
          </button>
          <button type="button" @click="props.goToMessages()">
            <strong>{{ uiText.contactVendors }}</strong>
            <span>{{ uiText.contactVendorsSub }}</span>
          </button>
        </div>
      </article>
    </section>
  </main>
</template>

<style scoped>
.dashboard-hero-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.75fr);
  gap: 14px;
  align-items: stretch;
  margin-bottom: 12px;
}

.dashboard-head {
  margin-bottom: 0;
}

.dashboard-side {
  display: grid;
  gap: 12px;
  align-content: start;
}

.dash-quick-card {
  border: 1px solid var(--line, #e5e7eb);
  border-radius: 18px;
  padding: 12px;
  background:
    radial-gradient(circle at 20% 0%, rgba(255, 148, 84, 0.12), transparent 45%),
    radial-gradient(circle at 100% 80%, rgba(14, 165, 233, 0.08), transparent 45%),
    rgba(255, 255, 255, 0.92);
  box-shadow: var(--shadow-sm, 0 8px 22px rgba(15, 23, 42, 0.06));
}

.dash-quick-title {
  margin: 0 0 8px;
  color: #475569;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  font-size: 0.75rem;
}

.dash-quick-grid {
  display: grid;
  gap: 10px;
}

.dash-quick-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  border-radius: 14px;
  border: 1px solid transparent;
  padding: 10px 12px;
  font-weight: 700;
  font-size: 0.95rem;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
}

.dash-quick-btn .icon {
  width: 32px;
  height: 32px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.78);
  color: inherit;
}

.dash-quick-btn.back {
  color: #b45309;
  background: linear-gradient(135deg, #fff7ed, #ffedd5);
  border-color: rgba(245, 158, 11, 0.4);
}

.dash-quick-btn.settings {
  color: #0f172a;
  background: linear-gradient(135deg, #ffffff, #f8fafc);
  border-color: rgba(148, 163, 184, 0.4);
}

.dash-quick-btn.logout {
  color: #b91c1c;
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-color: rgba(248, 113, 113, 0.5);
}

.dash-quick-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
}

.dash-profile-card {
  border-radius: 18px;
  padding: 14px;
  border: 1px solid rgba(59, 130, 246, 0.24);
  background:
    radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.18), transparent 48%),
    radial-gradient(circle at 100% 100%, rgba(52, 211, 153, 0.18), transparent 48%),
    linear-gradient(135deg, #ffffff, #f8fafc);
  box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
}

.dash-profile-top {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 10px;
  align-items: center;
}

.dash-avatar {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  color: #0f172a;
  font-weight: 800;
  font-size: 1rem;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.dash-profile-text {
  min-width: 0;
}

.dash-profile-name {
  margin: 0;
  color: #0f172a;
  font-weight: 800;
  font-size: 1rem;
}

.dash-profile-role {
  margin: 2px 0 0;
  color: #475569;
  font-size: 0.88rem;
}

.dash-verify {
  width: 28px;
  height: 28px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  background: #22c55e;
  color: #fff;
  box-shadow: 0 10px 18px rgba(34, 197, 94, 0.35);
}

.dash-profile-sub {
  margin: 10px 0 0;
  color: #0f172a;
  font-weight: 700;
  font-size: 0.95rem;
}

@media (max-width: 960px) {
  .dashboard-hero-layout {
    grid-template-columns: 1fr;
  }

  .dashboard-side {
    grid-template-columns: 1fr;
  }
}
</style>
