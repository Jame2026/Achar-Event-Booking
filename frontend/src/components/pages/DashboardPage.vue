<script setup>
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
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)
</script>

<template>
  <main class="shell dashboard-page">
    <div class="breadcrumbs">{{ uiText.breadcrumbs }}</div>

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
