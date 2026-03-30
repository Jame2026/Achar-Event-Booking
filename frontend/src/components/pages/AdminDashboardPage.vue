<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiPatch } from "../../features/apiClient";
import { serviceFeeRate } from "../../features/appData";
import { getStoredLanguage, setStoredLanguage, useLanguageCopy } from "../../features/language";
import { useAdminDataStore } from "../../features/useAdminDataStore";

const props = defineProps({
  appLogoSrc: {
    type: String,
    default: "",
  },
  adminDisplayName: {
    type: String,
    default: "Admin",
  },
  adminUser: {
    type: Object,
    default: null,
  },
  activePage: {
    type: String,
    default: "dashboard",
  },
  updateAdminUser: {
    type: Function,
    default: null,
  },
  logoutUser: {
    type: Function,
    default: null,
  },
});

const copyByLanguage = {
  en: {
    nav: {
      dashboard: "Dashboard",
      events: "Events",
      bookings: "Bookings",
      vendors: "Vendors",
      customers: "Customers",
      revenue: "Revenue",
      settings: "Settings",
    },
    settingsSections: {
      security: "Security",
      notifications: "Notifications",
      system: "System Preferences",
    },
    languageNames: {
      en: "English",
      km: "Khmer",
      zh: "Chinese",
    },
    currencyNames: {
      USD: "USD - US Dollar",
      KHR: "KHR - Khmer Riel",
      EUR: "EUR - Euro",
    },
    roleNames: {
      admin: "Admin",
      vendor: "Vendor",
      user: "User",
    },
    statusNames: {
      active: "Active",
      inactive: "Inactive",
    },
    brandKicker: "Operations Console",
    brandTitle: "Achar Admin",
    brandSubtitle: "Operations workspace",
    navigation: "Navigation",
    adminModules: "Admin modules",
    searchInsights: "Search insights...",
    notificationsButton: "Notifications",
    helpButton: "Help",
    dashboardEyebrow: "Achar Event Admin",
    dashboardTitle: "Admin Dashboard",
    dashboardSubtitle: "Track bookings, vendors, and revenue at a glance.",
    exportReport: "Export Report",
    totalEvents: "Total Events",
    totalBookings: "Total Bookings",
    totalAccounts: "Total Accounts",
    serviceFeeTotal: "Service Fee Total",
    newBadge: "New",
    accountsBreakdown: "{vendors} vendors, {customers} customers",
    noAccounts: "No customer or vendor accounts",
    monthlyReport: "Monthly Report",
    downloadPdf: "Download PDF",
    reportDirectionUp: "up",
    reportDirectionDown: "down",
    reportMessage:
      "Service fee income is {direction} {amount}% compared to last month.",
    reportPending: "Service fee insights will appear once bookings are confirmed.",
    systemStatus: "System Status",
    apiHealth: "API Health",
    cacheSync: "Cache Sync",
    healthy: "Healthy",
    degraded: "Degraded",
    unknown: "Unknown",
    cacheOk: "Cache OK",
    cacheIssue: "Cache Issue",
    cacheUnknown: "Cache Unknown",
    settingsTitle: "Settings",
    settingsSubtitle: "Manage your account preferences and notifications.",
    joinedPrefix: "Joined {date}",
    joinDateUnavailable: "Join date unavailable",
    lastActivePrefix: "Last active {time}",
    recentlyActive: "Recently active",
    save: "Save",
    securityTitle: "Security & Privacy",
    securitySubtitle: "Manage your password and authentication.",
    secure: "Secure",
    currentPassword: "Current Password",
    currentPasswordPlaceholder: "Enter current password",
    newPassword: "New Password",
    newPasswordPlaceholder: "Enter new password",
    confirmPassword: "Confirm Password",
    confirmPasswordPlaceholder: "Confirm new password",
    lastActive: "Last Active",
    twoFactorTitle: "Two-Factor Authentication",
    twoFactorSubtitle: "Add an extra layer of security to your account.",
    updateSecurity: "Update Security",
    updating: "Updating...",
    notificationsTitle: "Notification Preferences",
    notificationsSubtitle: "Choose how you want to be notified.",
    alerts: "Alerts",
    emailNotifications: "Email Notifications",
    emailNotificationsSubtitle: "Receive summaries of bookings and revenue.",
    smsAlerts: "SMS Alerts",
    smsAlertsSubtitle: "Urgent event cancellations or security alerts.",
    pushNotifications: "Push Notifications",
    pushNotificationsSubtitle: "Browser and mobile app push alerts.",
    systemPreferencesTitle: "System Preferences",
    systemPreferencesSubtitle: "Set language, currency, and theme.",
    global: "Global",
    interfaceLanguage: "Interface Language",
    defaultCurrency: "Default Currency",
    interfaceTheme: "Interface Theme",
    interfaceThemeSubtitle: "Switch between light and dark display modes.",
    light: "Light",
    dark: "Dark",
    searchUsers: "Search users by name, email...",
    totalUsers: "Total Users",
    newUsersMonth: "New Users (Month)",
    activeUsers: "Active Users",
    churnRate: "Churn Rate",
    userDirectory: "User Directory",
    userDirectorySubtitle: "Managing all registered accounts across the platform.",
    filter: "Filter",
    exportCsv: "Export CSV",
    user: "User",
    contactInfo: "Contact Info",
    joinedDate: "Joined Date",
    bookings: "Bookings",
    status: "Status",
    action: "Action",
    loadingUsers: "Loading users...",
    noUsersFound: "No users found.",
    totalSuffix: "Total",
    showingUsers: "Showing {start}-{shown} of {total} users",
    showingZeroUsers: "Showing 0 of 0 users",
    selectUser: "Select a user",
    noEmailAvailable: "No email available",
    spent: "Spent",
    lastLogin: "Last Login",
    recentActivity: "Recent Activity",
    viewAll: "View All",
    loadingLatestActivity: "Loading latest activity...",
    noRecentPlatformActivity: "No recent customer or vendor activity yet.",
    noRecentActivity: "No recent activity",
    noRecentActivitySubtitle:
      "Activity will appear when the user books or updates their account.",
    edit: "Edit",
    reset: "Reset",
    suspendUserAccount: "Suspend User Account",
    eliteMember: "Elite Member",
    eliteMemberMessage:
      "{name} is in the top 2% of contributors in the Siem Reap region.",
    thisUser: "This user",
    viewFullEngagementReport: "View full engagement report",
    fillPasswords:
      "Fill in current, new, and confirm password to update security settings.",
    passwordTooShort: "New password must be at least 8 characters.",
    passwordMismatch: "Password confirmation does not match.",
    passwordUpdated: "Password updated successfully.",
    passwordUpdateFailed: "Could not update password.",
    securitySaved: "Security preferences saved.",
    securityReset: "Security preferences reset.",
    notificationsSaved: "Notification preferences saved.",
    notificationsReset: "Notification preferences reset to default.",
    systemSaved: "System preferences saved.",
    systemReset: "System preferences reset to default.",
    guestUser: "Guest User",
    noEmail: "No email",
    noPhone: "No phone",
    notAvailable: "Not available",
    justNow: "Just now",
    dateTbd: "Date TBD",
    noData: "No data",
    na: "N/A",
    newThisMonth: "{count} new this month",
    ofTotal: "{percent}% of total",
    retention: "{percent}% retention",
    inactiveCount: "{count} inactive",
    bookingFallback: "Booking",
    idLabel: "ID",
  },
  km: {
    nav: {
      dashboard: "ផ្ទាំងគ្រប់គ្រង",
      events: "ព្រឹត្តិការណ៍",
      bookings: "ការកក់",
      vendors: "អ្នកផ្គត់ផ្គង់",
      customers: "អតិថិជន",
      revenue: "ចំណូល",
      settings: "ការកំណត់",
    },
    settingsSections: {
      security: "សុវត្ថិភាព",
      notifications: "ការជូនដំណឹង",
      system: "ចំណូលចិត្តប្រព័ន្ធ",
    },
    languageNames: {
      en: "អង់គ្លេស",
      km: "ខ្មែរ",
      zh: "ចិន",
    },
    currencyNames: {
      USD: "USD - ដុល្លារអាមេរិក",
      KHR: "KHR - រៀលខ្មែរ",
      EUR: "EUR - អឺរ៉ូ",
    },
    roleNames: {
      admin: "អ្នកគ្រប់គ្រង",
      vendor: "អ្នកផ្គត់ផ្គង់",
      user: "អ្នកប្រើ",
    },
    statusNames: {
      active: "សកម្ម",
      inactive: "មិនសកម្ម",
    },
    brandKicker: "ផ្ទាំងប្រតិបត្តិការ",
    brandTitle: "Achar Admin",
    brandSubtitle: "កន្លែងធ្វើការគ្រប់គ្រង",
    navigation: "ការរុករក",
    adminModules: "មុខងារអ្នកគ្រប់គ្រង",
    searchInsights: "ស្វែងរកទិន្នន័យសង្ខេប...",
    notificationsButton: "ការជូនដំណឹង",
    helpButton: "ជំនួយ",
    dashboardEyebrow: "អ្នកគ្រប់គ្រង Achar Event",
    dashboardTitle: "ផ្ទាំងគ្រប់គ្រងអ្នកគ្រប់គ្រង",
    dashboardSubtitle: "តាមដានការកក់ អ្នកផ្គត់ផ្គង់ និងចំណូលក្នុងមួយភ្លែត។",
    exportReport: "នាំចេញរបាយការណ៍",
    totalEvents: "ព្រឹត្តិការណ៍សរុប",
    totalBookings: "ការកក់សរុប",
    totalAccounts: "គណនីសរុប",
    serviceFeeTotal: "ថ្លៃសេវាសរុប",
    newBadge: "ថ្មី",
    accountsBreakdown: "{vendors} អ្នកផ្គត់ផ្គង់, {customers} អតិថិជន",
    noAccounts: "មិនទាន់មានគណនីអតិថិជន ឬអ្នកផ្គត់ផ្គង់ទេ",
    monthlyReport: "របាយការណ៍ប្រចាំខែ",
    downloadPdf: "ទាញយក PDF",
    reportDirectionUp: "កើនឡើង",
    reportDirectionDown: "ថយចុះ",
    reportMessage:
      "ចំណូលថ្លៃសេវា {direction} {amount}% បើប្រៀបធៀបនឹងខែមុន។",
    reportPending: "ទិន្នន័យថ្លៃសេវានឹងបង្ហាញនៅពេលមានការកក់បានបញ្ជាក់។",
    systemStatus: "ស្ថានភាពប្រព័ន្ធ",
    apiHealth: "សុខភាព API",
    cacheSync: "សមកាលកម្ម Cache",
    healthy: "ល្អ",
    degraded: "ថយចុះ",
    unknown: "មិនស្គាល់",
    cacheOk: "Cache ល្អ",
    cacheIssue: "Cache មានបញ្ហា",
    cacheUnknown: "មិនស្គាល់ស្ថានភាព Cache",
    settingsTitle: "ការកំណត់",
    settingsSubtitle: "គ្រប់គ្រងចំណូលចិត្តគណនី និងការជូនដំណឹងរបស់អ្នក។",
    joinedPrefix: "បានចូលរួម {date}",
    joinDateUnavailable: "មិនមានថ្ងៃចូលរួម",
    lastActivePrefix: "សកម្មចុងក្រោយ {time}",
    recentlyActive: "សកម្មថ្មីៗនេះ",
    save: "រក្សាទុក",
    securityTitle: "សុវត្ថិភាព និងឯកជនភាព",
    securitySubtitle: "គ្រប់គ្រងពាក្យសម្ងាត់ និងការផ្ទៀងផ្ទាត់របស់អ្នក។",
    secure: "មានសុវត្ថិភាព",
    currentPassword: "ពាក្យសម្ងាត់បច្ចុប្បន្ន",
    currentPasswordPlaceholder: "បញ្ចូលពាក្យសម្ងាត់បច្ចុប្បន្ន",
    newPassword: "ពាក្យសម្ងាត់ថ្មី",
    newPasswordPlaceholder: "បញ្ចូលពាក្យសម្ងាត់ថ្មី",
    confirmPassword: "បញ្ជាក់ពាក្យសម្ងាត់",
    confirmPasswordPlaceholder: "បញ្ជាក់ពាក្យសម្ងាត់ថ្មី",
    lastActive: "សកម្មចុងក្រោយ",
    twoFactorTitle: "ការផ្ទៀងផ្ទាត់ពីរជំហាន",
    twoFactorSubtitle: "បន្ថែមស្រទាប់សុវត្ថិភាពបន្ថែមសម្រាប់គណនីរបស់អ្នក។",
    updateSecurity: "ធ្វើបច្ចុប្បន្នភាពសុវត្ថិភាព",
    updating: "កំពុងធ្វើបច្ចុប្បន្នភាព...",
    notificationsTitle: "ចំណូលចិត្តការជូនដំណឹង",
    notificationsSubtitle: "ជ្រើសរើសរបៀបដែលអ្នកចង់ទទួលការជូនដំណឹង។",
    alerts: "ការជូនដំណឹង",
    emailNotifications: "ការជូនដំណឹងតាមអ៊ីមែល",
    emailNotificationsSubtitle: "ទទួលសេចក្តីសង្ខេបអំពីការកក់ និងចំណូល។",
    smsAlerts: "ការជូនដំណឹង SMS",
    smsAlertsSubtitle: "ការលុបព្រឹត្តិការណ៍បន្ទាន់ ឬការជូនដំណឹងសុវត្ថិភាព។",
    pushNotifications: "ការជូនដំណឹង Push",
    pushNotificationsSubtitle: "ការជូនដំណឹង Push តាមកម្មវិធី និងកម្មវិធីរុករក។",
    systemPreferencesTitle: "ចំណូលចិត្តប្រព័ន្ធ",
    systemPreferencesSubtitle: "កំណត់ភាសា រូបិយប័ណ្ណ និងរូបរាង។",
    global: "សកល",
    interfaceLanguage: "ភាសាផ្ទៃមុខ",
    defaultCurrency: "រូបិយប័ណ្ណលំនាំដើម",
    interfaceTheme: "រូបរាងផ្ទៃមុខ",
    interfaceThemeSubtitle: "ប្ដូររវាងរបៀបបង្ហាញពណ៌ភ្លឺ និងងងឹត។",
    light: "ភ្លឺ",
    dark: "ងងឹត",
    searchUsers: "ស្វែងរកអ្នកប្រើតាមឈ្មោះ អ៊ីមែល...",
    totalUsers: "អ្នកប្រើសរុប",
    newUsersMonth: "អ្នកប្រើថ្មី (ខែនេះ)",
    activeUsers: "អ្នកប្រើសកម្ម",
    churnRate: "អត្រាចាកចេញ",
    userDirectory: "បញ្ជីអ្នកប្រើ",
    userDirectorySubtitle: "គ្រប់គ្រងគណនីដែលបានចុះឈ្មោះទាំងអស់លើវេទិកា។",
    filter: "តម្រង",
    exportCsv: "នាំចេញ CSV",
    user: "អ្នកប្រើ",
    contactInfo: "ព័ត៌មានទំនាក់ទំនង",
    joinedDate: "ថ្ងៃចូលរួម",
    bookings: "ការកក់",
    status: "ស្ថានភាព",
    action: "សកម្មភាព",
    loadingUsers: "កំពុងផ្ទុកអ្នកប្រើ...",
    noUsersFound: "រកមិនឃើញអ្នកប្រើ។",
    totalSuffix: "សរុប",
    showingUsers: "កំពុងបង្ហាញ {start}-{shown} នៃអ្នកប្រើ {total}",
    showingZeroUsers: "កំពុងបង្ហាញ 0 នៃអ្នកប្រើ 0",
    selectUser: "ជ្រើសរើសអ្នកប្រើម្នាក់",
    noEmailAvailable: "មិនមានអ៊ីមែល",
    spent: "បានចំណាយ",
    lastLogin: "ចូលចុងក្រោយ",
    recentActivity: "សកម្មភាពថ្មីៗ",
    viewAll: "មើលទាំងអស់",
    loadingLatestActivity: "កំពុងផ្ទុកសកម្មភាពថ្មីៗ...",
    noRecentPlatformActivity: "មិនទាន់មានសកម្មភាពថ្មីៗពីអតិថិជន ឬអ្នកផ្គត់ផ្គង់ទេ។",
    noRecentActivity: "មិនទាន់មានសកម្មភាពថ្មីៗ",
    noRecentActivitySubtitle:
      "សកម្មភាពនឹងបង្ហាញនៅពេលអ្នកប្រើកក់ ឬធ្វើបច្ចុប្បន្នភាពគណនី។",
    edit: "កែប្រែ",
    reset: "កំណត់ឡើងវិញ",
    suspendUserAccount: "ផ្អាកគណនីអ្នកប្រើ",
    eliteMember: "សមាជិកឆ្នើម",
    eliteMemberMessage:
      "{name} ស្ថិតនៅក្នុងអ្នករួមចំណែកកំពូល 2% នៅតំបន់សៀមរាប។",
    thisUser: "អ្នកប្រើនេះ",
    viewFullEngagementReport: "មើលរបាយការណ៍ចូលរួមពេញលេញ",
    fillPasswords:
      "សូមបំពេញពាក្យសម្ងាត់បច្ចុប្បន្ន ថ្មី និងបញ្ជាក់ ដើម្បីធ្វើបច្ចុប្បន្នភាពការកំណត់សុវត្ថិភាព។",
    passwordTooShort: "ពាក្យសម្ងាត់ថ្មីត្រូវមានយ៉ាងតិច 8 តួអក្សរ។",
    passwordMismatch: "ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នា។",
    passwordUpdated: "បានធ្វើបច្ចុប្បន្នភាពពាក្យសម្ងាត់ដោយជោគជ័យ។",
    passwordUpdateFailed: "មិនអាចធ្វើបច្ចុប្បន្នភាពពាក្យសម្ងាត់បានទេ។",
    securitySaved: "បានរក្សាទុកចំណូលចិត្តសុវត្ថិភាព។",
    securityReset: "បានកំណត់ចំណូលចិត្តសុវត្ថិភាពឡើងវិញ។",
    notificationsSaved: "បានរក្សាទុកចំណូលចិត្តការជូនដំណឹង។",
    notificationsReset: "បានកំណត់ចំណូលចិត្តការជូនដំណឹងឡើងវិញទៅលំនាំដើម។",
    systemSaved: "បានរក្សាទុកចំណូលចិត្តប្រព័ន្ធ។",
    systemReset: "បានកំណត់ចំណូលចិត្តប្រព័ន្ធឡើងវិញទៅលំនាំដើម។",
    guestUser: "អ្នកប្រើភ្ញៀវ",
    noEmail: "មិនមានអ៊ីមែល",
    noPhone: "មិនមានលេខទូរស័ព្ទ",
    notAvailable: "មិនមាន",
    justNow: "មុននេះបន្តិច",
    dateTbd: "មិនទាន់កំណត់ថ្ងៃ",
    noData: "មិនមានទិន្នន័យ",
    na: "មិនមាន",
    newThisMonth: "ថ្មី {count} ក្នុងខែនេះ",
    ofTotal: "{percent}% នៃសរុប",
    retention: "ការរក្សាទុក {percent}%",
    inactiveCount: "មិនសកម្ម {count}",
    bookingFallback: "ការកក់",
    idLabel: "អត្តសញ្ញាណ",
  },
  zh: {
    nav: {
      dashboard: "仪表盘",
      events: "活动",
      bookings: "预订",
      vendors: "商家",
      customers: "客户",
      revenue: "收入",
      settings: "设置",
    },
    settingsSections: {
      security: "安全",
      notifications: "通知",
      system: "系统偏好",
    },
    languageNames: {
      en: "英文",
      km: "高棉文",
      zh: "中文",
    },
    currencyNames: {
      USD: "USD - 美元",
      KHR: "KHR - 柬埔寨瑞尔",
      EUR: "EUR - 欧元",
    },
    roleNames: {
      admin: "管理员",
      vendor: "商家",
      user: "用户",
    },
    statusNames: {
      active: "活跃",
      inactive: "未活跃",
    },
    brandKicker: "运营控制台",
    brandTitle: "Achar Admin",
    brandSubtitle: "运营工作区",
    navigation: "导航",
    adminModules: "管理员模块",
    searchInsights: "搜索洞察...",
    notificationsButton: "通知",
    helpButton: "帮助",
    dashboardEyebrow: "Achar Event 管理后台",
    dashboardTitle: "管理员仪表盘",
    dashboardSubtitle: "一目了然地跟踪预订、商家和收入。",
    exportReport: "导出报告",
    totalEvents: "活动总数",
    totalBookings: "预订总数",
    totalAccounts: "账户总数",
    serviceFeeTotal: "服务费总额",
    newBadge: "新增",
    accountsBreakdown: "{vendors} 个商家，{customers} 位客户",
    noAccounts: "暂无客户或商家账户",
    monthlyReport: "月度报告",
    downloadPdf: "下载 PDF",
    reportDirectionUp: "上升",
    reportDirectionDown: "下降",
    reportMessage: "服务费收入较上月{direction} {amount}%。",
    reportPending: "预订确认后，这里会显示服务费洞察。",
    systemStatus: "系统状态",
    apiHealth: "API 状态",
    cacheSync: "缓存同步",
    healthy: "健康",
    degraded: "下降",
    unknown: "未知",
    cacheOk: "缓存正常",
    cacheIssue: "缓存异常",
    cacheUnknown: "缓存未知",
    settingsTitle: "设置",
    settingsSubtitle: "管理您的账户偏好和通知设置。",
    joinedPrefix: "加入于 {date}",
    joinDateUnavailable: "暂无加入日期",
    lastActivePrefix: "最后活跃 {time}",
    recentlyActive: "最近活跃",
    save: "保存",
    securityTitle: "安全与隐私",
    securitySubtitle: "管理您的密码和身份验证。",
    secure: "安全",
    currentPassword: "当前密码",
    currentPasswordPlaceholder: "输入当前密码",
    newPassword: "新密码",
    newPasswordPlaceholder: "输入新密码",
    confirmPassword: "确认密码",
    confirmPasswordPlaceholder: "确认新密码",
    lastActive: "最后活跃",
    twoFactorTitle: "双重身份验证",
    twoFactorSubtitle: "为您的账户增加一层额外安全保护。",
    updateSecurity: "更新安全设置",
    updating: "更新中...",
    notificationsTitle: "通知偏好",
    notificationsSubtitle: "选择您希望接收通知的方式。",
    alerts: "提醒",
    emailNotifications: "邮件通知",
    emailNotificationsSubtitle: "接收预订和收入摘要。",
    smsAlerts: "短信提醒",
    smsAlertsSubtitle: "紧急活动取消或安全提醒。",
    pushNotifications: "推送通知",
    pushNotificationsSubtitle: "浏览器和移动端推送提醒。",
    systemPreferencesTitle: "系统偏好",
    systemPreferencesSubtitle: "设置语言、货币和主题。",
    global: "全局",
    interfaceLanguage: "界面语言",
    defaultCurrency: "默认货币",
    interfaceTheme: "界面主题",
    interfaceThemeSubtitle: "在浅色和深色显示模式之间切换。",
    light: "浅色",
    dark: "深色",
    searchUsers: "按姓名、邮箱搜索用户...",
    totalUsers: "用户总数",
    newUsersMonth: "本月新增用户",
    activeUsers: "活跃用户",
    churnRate: "流失率",
    userDirectory: "用户目录",
    userDirectorySubtitle: "管理平台上的所有注册账户。",
    filter: "筛选",
    exportCsv: "导出 CSV",
    user: "用户",
    contactInfo: "联系信息",
    joinedDate: "加入日期",
    bookings: "预订",
    status: "状态",
    action: "操作",
    loadingUsers: "正在加载用户...",
    noUsersFound: "未找到用户。",
    totalSuffix: "总计",
    showingUsers: "显示 {start}-{shown} / 共 {total} 位用户",
    showingZeroUsers: "显示 0 / 共 0 位用户",
    selectUser: "选择一位用户",
    noEmailAvailable: "暂无邮箱",
    spent: "消费",
    lastLogin: "最后登录",
    recentActivity: "最近活动",
    viewAll: "查看全部",
    loadingLatestActivity: "正在加载最近活动...",
    noRecentPlatformActivity: "暂无客户或商家的最近活动。",
    noRecentActivity: "暂无最近活动",
    noRecentActivitySubtitle: "当用户预订或更新账户时，这里会显示活动。",
    edit: "编辑",
    reset: "重置",
    suspendUserAccount: "暂停用户账户",
    eliteMember: "精英会员",
    eliteMemberMessage: "{name} 位于暹粒地区贡献者前 2%。",
    thisUser: "该用户",
    viewFullEngagementReport: "查看完整互动报告",
    fillPasswords: "请填写当前密码、新密码和确认密码以更新安全设置。",
    passwordTooShort: "新密码至少需要 8 个字符。",
    passwordMismatch: "两次输入的密码不一致。",
    passwordUpdated: "密码更新成功。",
    passwordUpdateFailed: "无法更新密码。",
    securitySaved: "安全偏好已保存。",
    securityReset: "安全偏好已重置。",
    notificationsSaved: "通知偏好已保存。",
    notificationsReset: "通知偏好已恢复默认。",
    systemSaved: "系统偏好已保存。",
    systemReset: "系统偏好已恢复默认。",
    guestUser: "访客用户",
    noEmail: "暂无邮箱",
    noPhone: "暂无电话",
    notAvailable: "暂无",
    justNow: "刚刚",
    dateTbd: "日期待定",
    noData: "暂无数据",
    na: "无",
    newThisMonth: "本月新增 {count}",
    ofTotal: "占总数的 {percent}%",
    retention: "留存率 {percent}%",
    inactiveCount: "{count} 位未活跃",
    bookingFallback: "预订",
    idLabel: "编号",
  },
};

const { language, uiText } = useLanguageCopy(copyByLanguage);
const localeByLanguage = {
  en: "en-US",
  km: "km-KH",
  zh: "zh-CN",
};
const reportText = computed(() => {
  if (language.value === "km") {
    return {
      yearlyReport: "របាយការណ៍ប្រចាំឆ្នាំ",
      serviceFeeReport: "របាយការណ៍ថ្លៃសេវា",
      reportPeriod: "រយៈពេលរបាយការណ៍",
      generatedOn: "បានបង្កើតនៅ",
      serviceFeeRateLabel: "អត្រាថ្លៃសេវា",
      confirmedBookings: "ការកក់ដែលបានបញ្ជាក់",
      grossRevenue: "ចំណូលសរុប",
      averageServiceFee: "ថ្លៃសេវាមធ្យម",
      periodChange: "ការប្រែប្រួលរយៈពេល",
      exportingReport: "កំពុងនាំចេញ...",
      reportNoData: "មិនទាន់មានការកក់ដែលបានបញ្ជាក់ក្នុងរយៈពេលនេះទេ។",
    };
  }
  if (language.value === "zh") {
    return {
      yearlyReport: "年度报告",
      serviceFeeReport: "服务费报告",
      reportPeriod: "报告周期",
      generatedOn: "生成时间",
      serviceFeeRateLabel: "服务费率",
      confirmedBookings: "已确认预订",
      grossRevenue: "总收入",
      averageServiceFee: "平均服务费",
      periodChange: "周期变化",
      exportingReport: "导出中...",
      reportNoData: "该周期内暂无已确认预订。",
    };
  }
  return {
    yearlyReport: "Yearly Report",
    serviceFeeReport: "Service Fee Report",
    reportPeriod: "Report Period",
    generatedOn: "Generated On",
    serviceFeeRateLabel: "Service Fee Rate",
    confirmedBookings: "Confirmed Bookings",
    grossRevenue: "Gross Revenue",
    averageServiceFee: "Average Service Fee",
    periodChange: "Period Change",
    exportingReport: "Exporting...",
    reportNoData: "No confirmed bookings in this period yet.",
  };
});

const searchQuery = ref("");
const usersSearchQuery = ref("");
const adminPageKeys = ["dashboard", "events", "admin-bookings", "vendors", "customers", "revenue", "settings"];
const router = useRouter();
const route = useRoute();
const activeKey = ref("dashboard");
const surfaceKey = computed(() => (activeKey.value === "settings" ? "settings" : "dashboard"));
const adminStore = useAdminDataStore();
const isLoading = computed(() => adminStore.loading.all);
const loadError = computed(() => adminStore.error);
const adminSummary = computed(() => adminStore.state.summary);
const bookingRows = computed(() => adminStore.state.bookings);
const eventRows = computed(() => adminStore.state.events);
const userRows = computed(() => adminStore.state.users);
const healthStatus = computed(() => adminStore.state.health);
const isUsersLoading = computed(() => adminStore.loading.all || adminStore.loading.users);
const usersLoadError = computed(() => adminStore.errors.users);
const initials = computed(() => {
  const pieces = String(props.adminDisplayName || "Admin")
    .split(" ")
    .filter(Boolean);
  const first = pieces[0]?.[0] || "A";
  const second = pieces[1]?.[0] || "";
  return `${first}${second}`.toUpperCase();
});
const ADMIN_NOTIFICATION_SETTINGS_KEY = "achar_admin_notification_settings_v1";
const ADMIN_SECURITY_SETTINGS_KEY = "achar_admin_security_settings_v1";
const ADMIN_SYSTEM_SETTINGS_KEY = "achar_admin_system_settings_v1";
const activeLocale = computed(() => localeByLanguage[language.value] || "en-US");
const interpolate = (template, values = {}) =>
  String(template || "").replace(/\{(\w+)\}/g, (_, key) => String(values[key] ?? ""));
const navItems = computed(() => [
  { key: "dashboard", label: uiText.value.nav.dashboard, icon: "dashboard" },
  { key: "events", label: uiText.value.nav.events, icon: "events" },
  { key: "admin-bookings", label: uiText.value.nav.bookings, icon: "bookings" },
  { key: "vendors", label: uiText.value.nav.vendors, icon: "vendors" },
  { key: "customers", label: uiText.value.nav.customers, icon: "users" },
  { key: "revenue", label: uiText.value.nav.revenue, icon: "revenue" },
  { key: "settings", label: uiText.value.nav.settings, icon: "settings" },
]);
const settingsSections = computed(() => [
  { key: "security", label: uiText.value.settingsSections.security },
  { key: "notifications", label: uiText.value.settingsSections.notifications },
  { key: "system", label: uiText.value.settingsSections.system },
]);
const languageOptions = computed(() => [
  { value: "en", label: uiText.value.languageNames.en },
  { value: "km", label: uiText.value.languageNames.km },
  { value: "zh", label: uiText.value.languageNames.zh },
]);
const currencyOptions = computed(() => [
  { value: "USD", label: uiText.value.currencyNames.USD },
  { value: "KHR", label: uiText.value.currencyNames.KHR },
  { value: "EUR", label: uiText.value.currencyNames.EUR },
]);

const readStoredObject = (key, fallback) => {
  try {
    const raw = localStorage.getItem(key);
    if (!raw) return { ...fallback };
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === "object" ? { ...fallback, ...parsed } : { ...fallback };
  } catch {
    return { ...fallback };
  }
};

const writeStoredObject = (key, value) => {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    // Ignore storage failures and continue with in-memory settings.
  }
};

const getDefaultNotificationSettings = () => ({
  email: true,
  sms: false,
  push: true,
});

const getDefaultSecuritySettings = () => ({
  twoFactor: false,
});

const getDefaultSystemSettings = () => ({
  language: getStoredLanguage(),
  currency: "USD",
  theme: "light",
});

const activeSettingsSection = ref("security");
const settingsNotice = ref("");
const settingsNoticeTone = ref("info");
const isSavingSettings = ref(false);
const passwordForm = reactive({
  current: "",
  next: "",
  confirm: "",
  saving: false,
  notice: "",
  error: "",
});
const notificationSettings = reactive(readStoredObject(ADMIN_NOTIFICATION_SETTINGS_KEY, getDefaultNotificationSettings()));
const securitySettings = reactive(readStoredObject(ADMIN_SECURITY_SETTINGS_KEY, getDefaultSecuritySettings()));
const systemSettings = reactive(readStoredObject(ADMIN_SYSTEM_SETTINGS_KEY, getDefaultSystemSettings()));
const preferredCurrency = computed(() =>
  currencyOptions.value.some((option) => option.value === systemSettings.currency) ? systemSettings.currency : "USD",
);
const setSettingsNotice = (message, tone = "info") => {
  settingsNotice.value = String(message || "").trim();
  settingsNoticeTone.value = tone;
};

const clearSettingsNotice = () => {
  settingsNotice.value = "";
  settingsNoticeTone.value = "info";
};

const persistNotificationSettings = () => {
  writeStoredObject(ADMIN_NOTIFICATION_SETTINGS_KEY, {
    email: Boolean(notificationSettings.email),
    sms: Boolean(notificationSettings.sms),
    push: Boolean(notificationSettings.push),
  });
};

const persistSecuritySettings = () => {
  writeStoredObject(ADMIN_SECURITY_SETTINGS_KEY, {
    twoFactor: Boolean(securitySettings.twoFactor),
  });
};

const persistSystemSettings = () => {
  const nextLanguage = setStoredLanguage(systemSettings.language);
  systemSettings.language = nextLanguage;
  systemSettings.currency = preferredCurrency.value;
  systemSettings.theme = systemSettings.theme === "dark" ? "dark" : "light";
  writeStoredObject(ADMIN_SYSTEM_SETTINGS_KEY, {
    language: nextLanguage,
    currency: systemSettings.currency,
    theme: systemSettings.theme,
  });
};

const activateSettingsSection = (key) => {
  if (!settingsSections.value.some((section) => section.key === key)) return;
  activeSettingsSection.value = key;
  clearSettingsNotice();
  passwordForm.notice = "";
  passwordForm.error = "";
};

const getRoutePage = () => {
  const raw = route.query.page;
  return Array.isArray(raw) ? raw[0] : raw;
};

const syncActiveKey = () => {
  const nextPage = String(props.activePage || getRoutePage() || "").trim();
  activeKey.value = adminPageKeys.includes(nextPage) ? nextPage : "dashboard";
};

const navigateTo = (key) => {
  activeKey.value = key;
  router.replace({ path: "/legacy-app", query: { page: key } }).catch(() => {});
};

const formatNumber = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat(activeLocale.value).format(Number.isFinite(amount) ? amount : 0);
};

const formatCompactNumber = (value) => {
  const amount = Number(value || 0);
  if (!Number.isFinite(amount)) return "0";
  return new Intl.NumberFormat(activeLocale.value, {
    notation: "compact",
    maximumFractionDigits: 1,
  }).format(amount);
};

const formatCurrency = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat(activeLocale.value, {
    style: "currency",
    currency: preferredCurrency.value,
    minimumFractionDigits: 2,
  }).format(Number.isFinite(amount) ? amount : 0);
};

const formatPercentDelta = (current, previous) => {
  if (!previous) return uiText.value.newBadge;
  const delta = ((current - previous) / Math.abs(previous)) * 100;
  const sign = delta >= 0 ? "+" : "";
  return `${sign}${delta.toFixed(1)}%`;
};

const timeAgo = (value) => {
  const date = value ? new Date(value) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return uiText.value.justNow;
  const diffMs = Date.now() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  if (diffMins < 2) return uiText.value.justNow;
  const formatter = new Intl.RelativeTimeFormat(activeLocale.value, {
    numeric: "always",
  });
  if (diffMins < 60) return formatter.format(-diffMins, "minute");
  const diffHours = Math.floor(diffMins / 60);
  if (diffHours < 24) return formatter.format(-diffHours, "hour");
  const diffDays = Math.floor(diffHours / 24);
  return formatter.format(-diffDays, "day");
};

const formatDate = (value) => {
  const date = value ? new Date(value) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return uiText.value.dateTbd;
  return date.toLocaleDateString(activeLocale.value, {
    month: "short",
    day: "2-digit",
    year: "numeric",
  });
};

const formatDateTime = (value) => {
  const date = value ? new Date(value) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return uiText.value.dateTbd;
  return date.toLocaleString(activeLocale.value, {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
};

const formatRoleLabel = (value) => {
  const normalized = String(value || "").trim().toLowerCase();
  return uiText.value.roleNames[normalized] || uiText.value.roleNames.admin;
};

const normalizeUserRole = (value) => {
  const normalized = String(value || "")
    .trim()
    .toLowerCase();

  if (["admin", "vendor", "user"].includes(normalized)) return normalized;
  return "user";
};

const getInitials = (value) =>
  String(value || uiText.value.user)
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();

const normalizeUserStatus = (user) => {
  const status = String(user?.status || "").toLowerCase();
  if (status === "inactive" || status === "banned" || user?.is_active === false) return "inactive";
  return "active";
};

const formatUserStatus = (value) => uiText.value.statusNames[value] || uiText.value.statusNames.active;

const bookingCountForUser = (user) => {
  const id = Number(user?.id || 0);
  const email = String(user?.email || user?.customer_email || "").toLowerCase();
  const rows = bookingRows.value.filter((row) => {
    if (id && Number(row?.user_id || row?.user?.id || 0) === id) return true;
    const rowEmail = String(row?.customer_email || row?.user?.email || "").toLowerCase();
    return email && rowEmail === email;
  });
  return rows.length;
};

const spentForUser = (user) => {
  const id = Number(user?.id || 0);
  const email = String(user?.email || user?.customer_email || "").toLowerCase();
  return bookingRows.value.reduce((sum, row) => {
    const status = normalizeBookingStatus(row);
    if (status !== "confirmed") return sum;
    const matchesId = id && Number(row?.user_id || row?.user?.id || 0) === id;
    const rowEmail = String(row?.customer_email || row?.user?.email || "").toLowerCase();
    const matchesEmail = email && rowEmail === email;
    if (!matchesId && !matchesEmail) return sum;
    return sum + Number(row?.total_amount || 0);
  }, 0);
};

const normalizedUsers = computed(() =>
  userRows.value.map((user) => {
    const createdAt = user?.created_at || user?.registered_at || user?.createdAt || null;
    const lastLogin = user?.last_login_at || user?.last_active_at || user?.updated_at || null;
    const bookingsCount = Number(user?.bookings_count || user?.bookings_total || bookingCountForUser(user));
    const spent = Number(user?.total_spent || spentForUser(user));
    const statusKey = normalizeUserStatus(user);
    return {
      id: user?.id ? `#${user.id}` : uiText.value.na,
      rawId: Number(user?.id || 0),
      role: normalizeUserRole(user?.role),
      name: user?.name || user?.full_name || uiText.value.guestUser,
      email: user?.email || user?.customer_email || uiText.value.noEmail,
      phone: user?.phone || user?.phone_number || uiText.value.noPhone,
      createdAt,
      joinedLabel: formatDate(createdAt),
      statusKey,
      status: formatUserStatus(statusKey),
      initials: getInitials(user?.name || user?.full_name),
      bookingsCount,
      eventsCount: Number(user?.events_count || 0),
      spent,
      lastLogin,
      lastSeenAt: lastLogin || createdAt,
      lastLoginLabel: timeAgo(lastLogin),
    };
  }),
);

const matchedAdminUser = computed(() => {
  const authId = Number(props.adminUser?.id || 0);
  const authEmail = String(props.adminUser?.email || "")
    .trim()
    .toLowerCase();

  return (
    userRows.value.find((user) => {
      const userId = Number(user?.id || 0);
      const userEmail = String(user?.email || user?.customer_email || "")
        .trim()
        .toLowerCase();

      if (authId && userId === authId) return true;
      return authEmail && userEmail === authEmail;
    }) || null
  );
});

const adminSettingsProfile = computed(() => {
  const authUser = props.adminUser || {};
  const matchedUser = matchedAdminUser.value || {};
  const merged = { ...matchedUser, ...authUser };
  const createdAt =
    merged?.created_at ||
    matchedUser?.created_at ||
    matchedUser?.registered_at ||
    authUser?.created_at ||
    null;
  const lastActiveAt =
    merged?.last_login_at ||
    matchedUser?.last_login_at ||
    matchedUser?.last_active_at ||
    authUser?.last_login_at ||
    authUser?.updated_at ||
    matchedUser?.updated_at ||
    null;
  const roleRaw = merged?.role || authUser?.role || "admin";
  const email = String(merged?.email || matchedUser?.email || authUser?.email || "").trim();
  const phone = String(merged?.phone || matchedUser?.phone || authUser?.phone || "").trim();
  const location = String(merged?.location || matchedUser?.location || authUser?.location || "").trim();
  const profileImageUrl = String(
    merged?.profile_image_url || matchedUser?.profile_image_url || authUser?.profile_image_url || "",
  ).trim();
  const name =
    String(merged?.name || matchedUser?.full_name || authUser?.name || props.adminDisplayName || uiText.value.roleNames.admin)
      .trim() || uiText.value.roleNames.admin;
  const statusKey = normalizeUserStatus(merged);

  return {
    id: Number(merged?.id || authUser?.id || matchedUser?.id || 0),
    name,
    email,
    phone,
    location,
    profileImageUrl,
    initials: getInitials(name),
    roleLabel: formatRoleLabel(roleRaw),
    status: formatUserStatus(statusKey),
    joinedLabel: createdAt
      ? interpolate(uiText.value.joinedPrefix, { date: formatDate(createdAt) })
      : uiText.value.joinDateUnavailable,
    joinedDate: createdAt ? formatDate(createdAt) : uiText.value.notAvailable,
    lastActiveLabel: lastActiveAt ? timeAgo(lastActiveAt) : uiText.value.recentlyActive,
    lastActiveDisplay: lastActiveAt
      ? interpolate(uiText.value.lastActivePrefix, { time: timeAgo(lastActiveAt) })
      : uiText.value.recentlyActive,
  };
});

const resetPasswordForm = () => {
  passwordForm.current = "";
  passwordForm.next = "";
  passwordForm.confirm = "";
  passwordForm.notice = "";
  passwordForm.error = "";
};

const saveSecuritySettings = async (showNotice = true) => {
  persistSecuritySettings();
  passwordForm.notice = "";
  passwordForm.error = "";

  const current = String(passwordForm.current || "").trim();
  const next = String(passwordForm.next || "").trim();
  const confirm = String(passwordForm.confirm || "").trim();
  const hasPasswordInput = Boolean(current || next || confirm);

  if (!hasPasswordInput) {
    if (showNotice) {
      setSettingsNotice(uiText.value.securitySaved, "success");
    }
    return true;
  }

  if (!current || !next || !confirm) {
    const message = uiText.value.fillPasswords;
    passwordForm.error = message;
    setSettingsNotice(message, "error");
    return false;
  }

  if (next.length < 8) {
    const message = uiText.value.passwordTooShort;
    passwordForm.error = message;
    setSettingsNotice(message, "error");
    return false;
  }

  if (next !== confirm) {
    const message = uiText.value.passwordMismatch;
    passwordForm.error = message;
    setSettingsNotice(message, "error");
    return false;
  }

  passwordForm.saving = true;
  try {
    const result = await apiPatch("user/password", {
      user_id: adminSettingsProfile.value.id || undefined,
      email: adminSettingsProfile.value.email || undefined,
      phone: adminSettingsProfile.value.phone || undefined,
      current_password: current,
      new_password: next,
      new_password_confirmation: confirm,
    });

    const message = result?.message || uiText.value.passwordUpdated;
    resetPasswordForm();
    passwordForm.notice = message;
    if (showNotice) {
      setSettingsNotice(message, "success");
    }
    return true;
  } catch (error) {
    const message = error?.message || uiText.value.passwordUpdateFailed;
    passwordForm.error = message;
    setSettingsNotice(message, "error");
    return false;
  } finally {
    passwordForm.saving = false;
  }
};

const resetSecuritySettings = () => {
  Object.assign(securitySettings, getDefaultSecuritySettings());
  persistSecuritySettings();
  resetPasswordForm();
  setSettingsNotice(uiText.value.securityReset, "success");
};

const saveNotificationPreferences = (showNotice = true) => {
  persistNotificationSettings();
  if (showNotice) {
    setSettingsNotice(uiText.value.notificationsSaved, "success");
  }
  return true;
};

const resetNotificationPreferences = () => {
  Object.assign(notificationSettings, getDefaultNotificationSettings());
  persistNotificationSettings();
  setSettingsNotice(uiText.value.notificationsReset, "success");
};

const saveSystemPreferences = (showNotice = true) => {
  persistSystemSettings();
  if (showNotice) {
    setSettingsNotice(uiText.value.systemSaved, "success");
  }
  return true;
};

const resetSystemPreferences = () => {
  Object.assign(systemSettings, getDefaultSystemSettings());
  persistSystemSettings();
  setSettingsNotice(uiText.value.systemReset, "success");
};

const saveCurrentSettingsSection = async () => {
  clearSettingsNotice();

  switch (activeSettingsSection.value) {
    case "security":
      await saveSecuritySettings(true);
      return;
    case "notifications":
      saveNotificationPreferences(true);
      return;
    case "system":
      saveSystemPreferences(true);
      return;
    default:
      return;
  }
};

const filteredUsers = computed(() => {
  const query = usersSearchQuery.value.trim().toLowerCase();
  const base = query
    ? normalizedUsers.value.filter((user) =>
        [user.name, user.email, user.phone, user.id].join(" ").toLowerCase().includes(query),
      )
    : normalizedUsers.value;
  return [...base].sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));
});

const selectedUserId = ref("");

watch(
  filteredUsers,
  (rows) => {
    if (!rows.length) {
      selectedUserId.value = "";
      return;
    }
    if (!rows.some((row) => row.id === selectedUserId.value)) {
      selectedUserId.value = rows[0].id;
    }
  },
  { immediate: true },
);

const selectedUser = computed(() => filteredUsers.value.find((row) => row.id === selectedUserId.value) || null);

const usersStats = computed(() => {
  const total = normalizedUsers.value.length;
  const monthAgo = Date.now() - 30 * 24 * 60 * 60 * 1000;
  const newUsers = normalizedUsers.value.filter((user) => new Date(user.createdAt || 0).getTime() >= monthAgo).length;
  const active = normalizedUsers.value.filter((user) => user.statusKey === "active").length;
  const retention = total ? Math.round((active / total) * 100) : 0;
  const inactive = total - active;
  const churn = total ? (inactive / total) * 100 : 0;
  return {
    totalLabel: formatNumber(total),
    totalDelta: interpolate(uiText.value.newThisMonth, {
      count: formatNumber(newUsers),
    }),
    newUsersLabel: formatNumber(newUsers),
    newUsersDelta: total
      ? interpolate(uiText.value.ofTotal, {
          percent: ((newUsers / total) * 100).toFixed(1),
        })
      : uiText.value.noData,
    activeLabel: formatNumber(active),
    activeDelta: interpolate(uiText.value.retention, {
      percent: retention,
    }),
    churnLabel: total ? `${churn.toFixed(1)}%` : uiText.value.na,
    churnDelta: interpolate(uiText.value.inactiveCount, {
      count: formatNumber(inactive),
    }),
  };
});

const usersFooterLabel = computed(() => {
  const total = normalizedUsers.value.length;
  const shown = filteredUsers.value.length;
  if (!total) return uiText.value.showingZeroUsers;
  return interpolate(uiText.value.showingUsers, {
    start: 1,
    shown,
    total: formatNumber(total),
  });
});

const selectedUserActivities = computed(() => {
  if (!selectedUser.value) return [];
  const email = String(selectedUser.value.email || "").toLowerCase();
  const id = Number(selectedUser.value.rawId || 0);
  const rows = bookingRows.value
    .filter((row) => {
      if (id && Number(row?.user_id || row?.user?.id || 0) === id) return true;
      const rowEmail = String(row?.customer_email || row?.user?.email || "").toLowerCase();
      return email && rowEmail === email;
    })
    .sort((a, b) => {
      const left = getRowDate(a, a?.event?.starts_at)?.getTime() || 0;
      const right = getRowDate(b, b?.event?.starts_at)?.getTime() || 0;
      return right - left;
    })
    .slice(0, 3);

  return rows.map((row) => ({
    title: row?.event?.title || row?.service_name || uiText.value.bookingFallback,
    time: timeAgo(getRowDate(row, row?.event?.starts_at)),
    meta: formatCurrency(row?.total_amount || 0),
  }));
});

const normalizeBookingStatus = (row) => {
  const status = String(row?.status || "").toLowerCase();
  const payment = String(row?.payment_status || "").toLowerCase();
  if (status === "cancelled") return "cancelled";
  if (status === "confirmed" || payment === "confirmed") return "confirmed";
  return "pending";
};

const getRowDate = (row, fallback) => {
  const raw = row?.created_at || row?.requested_event_date || row?.updated_at || fallback || null;
  const date = raw ? new Date(raw) : null;
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return null;
  return date;
};

const getMonthRange = (offset = 0) => {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth() + offset, 1);
  const end = new Date(now.getFullYear(), now.getMonth() + offset + 1, 0, 23, 59, 59, 999);
  return { start, end };
};

const getYearRange = (offset = 0) => {
  const now = new Date();
  const year = now.getFullYear() + offset;
  const start = new Date(year, 0, 1);
  const end = new Date(year, 11, 31, 23, 59, 59, 999);
  return { start, end };
};

const calculateServiceFee = (amount) => Number((Math.max(0, Number(amount || 0)) * serviceFeeRate).toFixed(2));

const sumServiceFees = (rows, range) =>
  rows.reduce((sum, row) => {
    const status = normalizeBookingStatus(row);
    if (status !== "confirmed") return sum;
    const date = getRowDate(row, row?.event?.starts_at);
    if (!date || (range && (date < range.start || date > range.end))) return sum;
    return sum + calculateServiceFee(row?.total_amount || 0);
  }, 0);

const getConfirmedBookingsInRange = (rows, range) =>
  rows
    .filter((row) => {
      if (normalizeBookingStatus(row) !== "confirmed") return false;
      const date = getRowDate(row, row?.event?.starts_at);
      return Boolean(date && (!range || (date >= range.start && date <= range.end)));
    })
    .sort((left, right) => {
      const leftTime = getRowDate(left, left?.event?.starts_at)?.getTime() || 0;
      const rightTime = getRowDate(right, right?.event?.starts_at)?.getTime() || 0;
      return rightTime - leftTime;
    });

const countByDate = (rows, range, dateField = "created_at") =>
  rows.reduce((sum, row) => {
    const date = getRowDate(row, row?.[dateField]);
    if (!date || (range && (date < range.start || date > range.end))) return sum;
    return sum + 1;
  }, 0);

const customerAccountsTotal = computed(
  () => normalizedUsers.value.filter((user) => user.role === "user").length,
);

const vendorAccountsTotal = computed(
  () => normalizedUsers.value.filter((user) => user.role === "vendor").length,
);

const trackedAccountsTotal = computed(() => customerAccountsTotal.value + vendorAccountsTotal.value);

const dashboardStats = computed(() => {
  const totalEvents = adminSummary.value?.events_total || eventRows.value.length;
  const totalBookings = adminSummary.value?.bookings_total || bookingRows.value.length;
  const totalCustomers = adminSummary.value?.customers_total || customerAccountsTotal.value;
  const totalVendors = adminSummary.value?.vendors_total || vendorAccountsTotal.value;
  const totalAccounts = adminSummary.value?.accounts_total || trackedAccountsTotal.value;
  const totalServiceFees = adminSummary.value?.service_fee_total || sumServiceFees(bookingRows.value);

  const currentMonth = getMonthRange(0);
  const previousMonth = getMonthRange(-1);
  const eventsDelta = formatPercentDelta(
    countByDate(eventRows.value, currentMonth, "created_at"),
    countByDate(eventRows.value, previousMonth, "created_at"),
  );
  const bookingsDelta = formatPercentDelta(
    countByDate(bookingRows.value, currentMonth, "created_at"),
    countByDate(bookingRows.value, previousMonth, "created_at"),
  );
  const accountsDelta =
    totalAccounts > 0
      ? interpolate(uiText.value.accountsBreakdown, {
          vendors: formatNumber(totalVendors),
          customers: formatNumber(totalCustomers),
        })
      : uiText.value.noAccounts;
  const serviceFeeDelta = formatPercentDelta(
    sumServiceFees(bookingRows.value, currentMonth),
    sumServiceFees(bookingRows.value, previousMonth),
  );

  return [
    { label: uiText.value.totalEvents, value: formatNumber(totalEvents), delta: eventsDelta, tone: "up", icon: "events" },
    { label: uiText.value.totalBookings, value: formatNumber(totalBookings), delta: bookingsDelta, tone: "up", icon: "bookings" },
    {
      label: uiText.value.totalAccounts,
      value: formatNumber(totalAccounts),
      delta: accountsDelta,
      tone: "neutral",
      icon: "users",
    },
    {
      label: uiText.value.serviceFeeTotal,
      value: formatCurrency(totalServiceFees),
      delta: serviceFeeDelta,
      tone: "solid",
      icon: "revenue",
    },
  ];
});

const monthlyReport = computed(() => {
  const currentMonth = getMonthRange(0);
  const previousMonth = getMonthRange(-1);
  const currentServiceFees = sumServiceFees(bookingRows.value, currentMonth);
  const previousServiceFees = sumServiceFees(bookingRows.value, previousMonth);
  const growth = previousServiceFees ? ((currentServiceFees - previousServiceFees) / previousServiceFees) * 100 : 0;
  return {
    growthLabel: `${growth >= 0 ? "+" : ""}${growth.toFixed(1)}%`,
    message:
      previousServiceFees > 0
        ? interpolate(uiText.value.reportMessage, {
            direction: growth >= 0 ? uiText.value.reportDirectionUp : uiText.value.reportDirectionDown,
            amount: Math.abs(growth).toFixed(1),
          })
        : uiText.value.reportPending,
  };
});

const reportSheetRef = ref(null);
const reportExportMode = ref("combined");
const isExportingReport = ref(false);
const reportGeneratedAt = ref(new Date());

const formatMonthPeriod = (range) =>
  new Intl.DateTimeFormat(activeLocale.value, {
    month: "long",
    year: "numeric",
  }).format(range.start);

const formatYearPeriod = (range) =>
  new Intl.DateTimeFormat(activeLocale.value, {
    year: "numeric",
  }).format(range.start);

const buildServiceFeeSummary = ({ key, title, range, previousRange, periodLabel }) => {
  const rows = getConfirmedBookingsInRange(bookingRows.value, range);
  const grossRevenue = Number(
    rows.reduce((sum, row) => sum + Number(row?.total_amount || 0), 0).toFixed(2),
  );
  const serviceFees = Number(
    rows.reduce((sum, row) => sum + calculateServiceFee(row?.total_amount || 0), 0).toFixed(2),
  );
  const previousFees = Number(sumServiceFees(bookingRows.value, previousRange).toFixed(2));
  const averageFee = rows.length ? Number((serviceFees / rows.length).toFixed(2)) : 0;

  return {
    key,
    title,
    periodLabel,
    bookingCount: rows.length,
    bookingCountLabel: formatNumber(rows.length),
    grossRevenueLabel: formatCurrency(grossRevenue),
    serviceFeesLabel: formatCurrency(serviceFees),
    averageFeeLabel: formatCurrency(averageFee),
    growthLabel: formatPercentDelta(serviceFees, previousFees),
  };
};

const monthlyServiceFeeSummary = computed(() => {
  const range = getMonthRange(0);
  return buildServiceFeeSummary({
    key: "monthly",
    title: uiText.value.monthlyReport,
    range,
    previousRange: getMonthRange(-1),
    periodLabel: formatMonthPeriod(range),
  });
});

const yearlyServiceFeeSummary = computed(() => {
  const range = getYearRange(0);
  return buildServiceFeeSummary({
    key: "yearly",
    title: reportText.value.yearlyReport,
    range,
    previousRange: getYearRange(-1),
    periodLabel: formatYearPeriod(range),
  });
});

const exportReportSections = computed(() => {
  if (reportExportMode.value === "monthly") return [monthlyServiceFeeSummary.value];
  if (reportExportMode.value === "yearly") return [yearlyServiceFeeSummary.value];
  return [monthlyServiceFeeSummary.value, yearlyServiceFeeSummary.value];
});

const reportDocumentTitle = computed(() => {
  if (reportExportMode.value === "monthly") {
    return `${uiText.value.monthlyReport} - ${reportText.value.serviceFeeReport}`;
  }
  if (reportExportMode.value === "yearly") {
    return `${reportText.value.yearlyReport} - ${reportText.value.serviceFeeReport}`;
  }
  return reportText.value.serviceFeeReport;
});

const reportGeneratedAtLabel = computed(() => formatDateTime(reportGeneratedAt.value));
const reportFeeRateLabel = computed(() => `${(serviceFeeRate * 100).toFixed(1)}%`);

const buildReportFilename = (mode) => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");

  if (mode === "monthly") return `service-fee-report-monthly-${year}-${month}.pdf`;
  if (mode === "yearly") return `service-fee-report-yearly-${year}.pdf`;
  return `service-fee-report-${year}-${month}-${day}.pdf`;
};

const downloadServiceFeeReport = async (mode = "combined") => {
  if (isExportingReport.value) return;
  reportExportMode.value = mode;
  reportGeneratedAt.value = new Date();
  await nextTick();
  if (!reportSheetRef.value) return;

  isExportingReport.value = true;
  try {
    const { default: html2pdf } = await import("html2pdf.js");
    await html2pdf()
      .set({
        margin: [8, 8, 8, 8],
        filename: buildReportFilename(mode),
        image: { type: "jpeg", quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: "#ffffff" },
        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
      })
      .from(reportSheetRef.value)
      .save();
  } catch (error) {
    console.error("Unable to export service fee report.", error);
  } finally {
    isExportingReport.value = false;
  }
};

const systemStatus = computed(() => {
  const status = healthStatus.value?.status || "unknown";
  const cacheRoundTrip = healthStatus.value?.cache_round_trip;
  const isHealthy = status === "ok";
  return {
    apiLabel: isHealthy ? uiText.value.healthy : status === "degraded" ? uiText.value.degraded : uiText.value.unknown,
    apiPercent: isHealthy ? 99.9 : status === "degraded" ? 92.5 : 85,
    cacheLabel:
      cacheRoundTrip === true
        ? uiText.value.cacheOk
        : cacheRoundTrip === false
          ? uiText.value.cacheIssue
          : uiText.value.cacheUnknown,
    cachePercent: cacheRoundTrip === true ? 88 : cacheRoundTrip === false ? 54 : 70,
  };
});

syncActiveKey();
watch(() => props.activePage, syncActiveKey);
watch(() => route.query.page, syncActiveKey);
watch(
  () => props.adminUser?.id,
  (adminUserId) => {
    if (!adminUserId || userRows.value.length) return;
    void adminStore.loadAll({
      force: true,
      adminUserId,
    });
  },
);
onMounted(() =>
  void adminStore.loadAll({
    adminUserId: props.adminUser?.id || null,
  }),
);
</script>

<template>
  <section class="admin-shell" :class="{ 'theme-dark': systemSettings.theme === 'dark' }">
    <aside class="admin-sidebar">
      <div class="brand-card">
        <div class="brand">
          <div class="brand-logo">
            <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
            <div v-else class="brand-mark">A</div>
          </div>
          <div>
            <p class="brand-kicker">{{ uiText.brandKicker }}</p>
            <p class="brand-title">{{ uiText.brandTitle }}</p>
            <p class="brand-subtitle">{{ uiText.brandSubtitle }}</p>
          </div>
        </div>
      </div>

      <section class="sidebar-block">
        <div class="sidebar-block-head">
          <span class="sidebar-section-label">{{ uiText.navigation }}</span>
          <span class="sidebar-section-caption">{{ uiText.adminModules }}</span>
        </div>

        <nav class="admin-nav">
          <button
            v-for="item in navItems"
            :key="item.key"
            type="button"
            class="nav-item"
            :class="{ active: activeKey === item.key }"
            @click="navigateTo(item.key)"
          >
            <span class="nav-icon" aria-hidden="true">
              <svg v-if="item.icon === 'dashboard'" viewBox="0 0 24 24">
                <path
                  d="M4 12.5 11.5 4 20 12.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1z"
                />
              </svg>
              <svg v-else-if="item.icon === 'events'" viewBox="0 0 24 24">
                <path
                  d="M7 3v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3zm12 6H5v10h14z"
                />
              </svg>
              <svg v-else-if="item.icon === 'bookings'" viewBox="0 0 24 24">
                <path
                  d="M6 4h12a2 2 0 0 1 2 2v4H4V6a2 2 0 0 1 2-2zm-2 8h16v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"
                />
              </svg>
              <svg v-else-if="item.icon === 'vendors'" viewBox="0 0 24 24">
                <path
                  d="M4 10h16l-1.5 9a2 2 0 0 1-2 1H7.5a2 2 0 0 1-2-1L4 10zm4-6h8l1 4H7z"
                />
              </svg>
              <svg v-else-if="item.icon === 'users'" viewBox="0 0 24 24">
                <path
                  d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z"
                />
              </svg>
              <svg v-else-if="item.icon === 'revenue'" viewBox="0 0 24 24">
                <path
                  d="M4 18h16v2H4zm2-4h3v3H6zm5-6h3v9h-3zm5-3h3v12h-3z"
                />
              </svg>
              <svg v-else viewBox="0 0 24 24">
                <path
                  d="M12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4zm8.5 4a6.5 6.5 0 0 0-.08-1l2.08-1.6-2-3.46-2.45 1a6.86 6.86 0 0 0-1.73-1L14 2h-4l-.32 2.94a6.86 6.86 0 0 0-1.73 1l-2.45-1-2 3.46L5.58 11a6.5 6.5 0 0 0 0 2l-2.08 1.6 2 3.46 2.45-1a6.86 6.86 0 0 0 1.73 1L10 22h4l.32-2.94a6.86 6.86 0 0 0 1.73-1l2.45 1 2-3.46L20.42 13a6.5 6.5 0 0 0 .08-1z"
                />
              </svg>
            </span>
            <span>{{ item.label }}</span>
          </button>
        </nav>
      </section>

    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <label class="search">
          <span class="search-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24">
              <path
                d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z"
              />
            </svg>
          </span>
          <input v-model="searchQuery" type="search" :placeholder="uiText.searchInsights" />
        </label>
        <div class="topbar-actions">
          <button
            class="icon-btn"
            type="button"
            :title="uiText.notificationsButton"
            :aria-label="uiText.notificationsButton"
          >
            <svg viewBox="0 0 24 24">
              <path
                d="M12 22a2.5 2.5 0 0 1-2.45-2h4.9A2.5 2.5 0 0 1 12 22Zm7-6v-5a7 7 0 1 0-14 0v5l-2 2v1h18v-1l-2-2Zm-2 1H7v-6a5 5 0 0 1 10 0v6Z"
              />
            </svg>
          </button>
          <button class="icon-btn" type="button" :title="uiText.helpButton" :aria-label="uiText.helpButton">
            <svg viewBox="0 0 24 24">
              <path
                d="M12 19a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 19Zm0-15a6 6 0 0 1 6 6c0 2.455-1.812 3.498-2.83 4.085-.87.505-1.17.75-1.17 1.415v.5h-2v-.5c0-1.86 1.126-2.512 2.17-3.115C14.98 11.83 16 11.235 16 10a4 4 0 0 0-8 0H6a6 6 0 0 1 6-6Z"
              />
            </svg>
          </button>
          <div class="topbar-avatar">{{ initials }}</div>
        </div>
      </header>

      <section v-if="surfaceKey === 'dashboard'" class="admin-hero">
        <p class="eyebrow">{{ uiText.dashboardEyebrow }}</p>
        <h1 class="hero-title">{{ uiText.dashboardTitle }}</h1>
        <p class="hero-subtitle">{{ uiText.dashboardSubtitle }}</p>
        <div class="hero-actions">
          <button class="primary-btn" type="button" :disabled="isExportingReport" @click="downloadServiceFeeReport('combined')">
            {{ isExportingReport ? reportText.exportingReport : uiText.exportReport }}
          </button>
        </div>
      </section>

      <section v-if="surfaceKey === 'dashboard'" class="admin-stats">
        <article
          v-for="card in dashboardStats"
          :key="card.label"
          class="stat-card"
          :class="card.tone"
        >
          <div class="stat-icon" aria-hidden="true">
            <svg v-if="card.icon === 'events'" viewBox="0 0 24 24">
              <path
                d="M7 3v2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2V3h-2v2H9V3zm12 6H5v10h14z"
              />
            </svg>
            <svg v-else-if="card.icon === 'bookings'" viewBox="0 0 24 24">
              <path
                d="M6 4h12a2 2 0 0 1 2 2v4H4V6a2 2 0 0 1 2-2zm-2 8h16v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"
              />
            </svg>
            <svg v-else-if="card.icon === 'users'" viewBox="0 0 24 24">
              <path
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z"
              />
            </svg>
            <svg v-else viewBox="0 0 24 24">
              <path d="M4 18h16v2H4zm2-4h3v3H6zm5-6h3v9h-3zm5-3h3v12h-3z" />
            </svg>
          </div>
          <p class="stat-label">{{ card.label }}</p>
          <p class="stat-value">{{ card.value }}</p>
          <span class="stat-delta" :class="card.tone">{{ card.delta }}</span>
        </article>
      </section>

      <section v-if="surfaceKey === 'dashboard'" class="admin-grid">
        <article v-if="false" class="activity-card">
          <header>
            <h3>{{ uiText.recentActivity }}</h3>
            <button class="link-btn" type="button">{{ uiText.viewAll }} →</button>
          </header>
          <div class="activity-list">
            <div v-if="isLoading" class="activity-empty">{{ uiText.loadingLatestActivity }}</div>
            <div v-else-if="loadError" class="activity-empty">{{ loadError }}</div>
            <div v-else-if="!recentActivity.length" class="activity-empty">
              {{ uiText.noRecentPlatformActivity }}
            </div>
            <template v-else>
              <div v-for="item in recentActivity" :key="item.key" class="activity-item">
                <div class="activity-icon" :class="`is-${item.type || 'activity'}`">
                  {{ item.type === "vendor" ? "V" : item.type === "customer" ? "C" : "A" }}
                </div>
                <div>
                  <p class="activity-text">
                    <strong>{{ item.name }}</strong>
                    {{ item.detail }}
                  </p>
                  <p class="activity-time">{{ item.time }}</p>
                </div>
              </div>
            </template>
          </div>
        </article>

        <div class="side-stack">
          <article class="report-card">
            <div class="report-head">
              <h3>{{ uiText.monthlyReport }}</h3>
              <span class="report-pill">{{ monthlyReport.growthLabel }}</span>
            </div>
            <p>{{ monthlyReport.message }}</p>
            <div class="report-metrics">
              <div class="report-metric">
                <span>{{ uiText.monthlyReport }}</span>
                <strong>{{ monthlyServiceFeeSummary.serviceFeesLabel }}</strong>
              </div>
              <div class="report-metric">
                <span>{{ reportText.yearlyReport }}</span>
                <strong>{{ yearlyServiceFeeSummary.serviceFeesLabel }}</strong>
              </div>
            </div>
            <div class="report-actions">
              <button class="primary-btn" type="button" :disabled="isExportingReport" @click="downloadServiceFeeReport('monthly')">
                {{ isExportingReport ? reportText.exportingReport : uiText.downloadPdf }}
              </button>
              <button class="secondary-btn" type="button" :disabled="isExportingReport" @click="downloadServiceFeeReport('yearly')">
                {{ isExportingReport ? reportText.exportingReport : reportText.yearlyReport }}
              </button>
            </div>
          </article>

          <article class="status-card">
            <h3>{{ uiText.systemStatus }}</h3>
            <div class="status-row">
              <div>
                <p>{{ uiText.apiHealth }}</p>
                <span>{{ systemStatus.apiLabel }} · {{ systemStatus.apiPercent }}%</span>
              </div>
              <div class="bar">
                <span class="bar-fill" :style="{ width: `${systemStatus.apiPercent}%` }"></span>
              </div>
            </div>
            <div class="status-row">
              <div>
                <p>{{ uiText.cacheSync }}</p>
                <span>{{ systemStatus.cacheLabel }} · {{ systemStatus.cachePercent }}%</span>
              </div>
              <div class="bar">
                <span class="bar-fill warning" :style="{ width: `${systemStatus.cachePercent}%` }"></span>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section v-else-if="surfaceKey === 'settings'" class="settings-page">
        <div class="settings-header">
          <div class="settings-title">
            <div class="settings-icon">
              <svg viewBox="0 0 24 24">
                <path
                  d="M12 8a4 4 0 1 0 4 4 4 4 0 0 0-4-4zm8.5 4a6.5 6.5 0 0 0-.08-1l2.08-1.6-2-3.46-2.45 1a6.86 6.86 0 0 0-1.73-1L14 2h-4l-.32 2.94a6.86 6.86 0 0 0-1.73 1l-2.45-1-2 3.46L5.58 11a6.5 6.5 0 0 0 0 2l-2.08 1.6 2 3.46 2.45-1a6.86 6.86 0 0 0 1.73 1L10 22h4l.32-2.94a6.86 6.86 0 0 0 1.73-1l2.45 1 2-3.46L20.42 13a6.5 6.5 0 0 0 .08-1z"
                />
              </svg>
            </div>
            <div>
              <h2>{{ uiText.settingsTitle }}</h2>
              <p>{{ uiText.settingsSubtitle }}</p>
            </div>
          </div>
          <div class="settings-quick">
            <span class="pill alt">{{ adminSettingsProfile.joinedLabel }}</span>
            <span class="pill alt">{{ adminSettingsProfile.lastActiveDisplay }}</span>
            <button
              class="primary-btn"
              type="button"
              :disabled="isSavingSettings || passwordForm.saving"
              @click="saveCurrentSettingsSection"
            >
              {{ uiText.save }}
            </button>
          </div>
        </div>
        <p
          v-if="settingsNotice"
          class="settings-feedback"
          :class="{
            success: settingsNoticeTone === 'success',
            error: settingsNoticeTone === 'error',
          }"
        >
          {{ settingsNotice }}
        </p>

        <div class="settings-layout">
          <aside class="settings-menu">
            <button
              v-for="section in settingsSections"
              :key="section.key"
              type="button"
              class="settings-link"
              :class="{ active: activeSettingsSection === section.key }"
              @click="activateSettingsSection(section.key)"
            >
              <span class="dot"></span>
              {{ section.label }}
            </button>
          </aside>

          <div class="settings-content">
            <article v-if="activeSettingsSection === 'security'" class="settings-card">
              <div class="card-header">
                <div>
                  <h3>{{ uiText.securityTitle }}</h3>
                  <p>{{ uiText.securitySubtitle }}</p>
                </div>
                <span class="pill alt">{{ uiText.secure }}</span>
              </div>
              <div class="form-grid">
                <label>
                  <span>{{ uiText.currentPassword }}</span>
                  <input
                    v-model="passwordForm.current"
                    type="password"
                    autocomplete="current-password"
                    :placeholder="uiText.currentPasswordPlaceholder"
                  />
                </label>
                <label>
                  <span>{{ uiText.newPassword }}</span>
                  <input
                    v-model="passwordForm.next"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="uiText.newPasswordPlaceholder"
                  />
                </label>
                <label>
                  <span>{{ uiText.confirmPassword }}</span>
                  <input
                    v-model="passwordForm.confirm"
                    type="password"
                    autocomplete="new-password"
                    :placeholder="uiText.confirmPasswordPlaceholder"
                  />
                </label>
                <label class="full">
                  <span>{{ uiText.lastActive }}</span>
                  <input type="text" :value="adminSettingsProfile.lastActiveLabel" readonly />
                </label>
              </div>
              <div class="toggle-row">
                <div>
                  <p>{{ uiText.twoFactorTitle }}</p>
                  <span>{{ uiText.twoFactorSubtitle }}</span>
                </div>
                <label class="switch">
                  <input v-model="securitySettings.twoFactor" type="checkbox" />
                  <span></span>
                </label>
              </div>
              <div class="settings-card-actions">
                <button
                  class="primary-btn"
                  type="button"
                  :disabled="passwordForm.saving"
                  @click="saveSecuritySettings(true)"
                >
                  {{ passwordForm.saving ? uiText.updating : uiText.updateSecurity }}
                </button>
                <p v-if="passwordForm.notice" class="inline-feedback success">{{ passwordForm.notice }}</p>
                <p v-else-if="passwordForm.error" class="inline-feedback error">{{ passwordForm.error }}</p>
              </div>
            </article>

            <article v-else-if="activeSettingsSection === 'notifications'" class="settings-card">
              <div class="card-header">
                <div>
                  <h3>{{ uiText.notificationsTitle }}</h3>
                  <p>{{ uiText.notificationsSubtitle }}</p>
                </div>
                <span class="pill">{{ uiText.alerts }}</span>
              </div>
              <div class="toggle-list">
                <div class="toggle-row">
                  <div>
                    <p>{{ uiText.emailNotifications }}</p>
                    <span>{{ uiText.emailNotificationsSubtitle }}</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationSettings.email" type="checkbox" />
                    <span></span>
                  </label>
                </div>
                <div class="toggle-row">
                  <div>
                    <p>{{ uiText.smsAlerts }}</p>
                    <span>{{ uiText.smsAlertsSubtitle }}</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationSettings.sms" type="checkbox" />
                    <span></span>
                  </label>
                </div>
                <div class="toggle-row">
                  <div>
                    <p>{{ uiText.pushNotifications }}</p>
                    <span>{{ uiText.pushNotificationsSubtitle }}</span>
                  </div>
                  <label class="switch">
                    <input v-model="notificationSettings.push" type="checkbox" />
                    <span></span>
                  </label>
                </div>
              </div>
            </article>

            <article v-else-if="activeSettingsSection === 'system'" class="settings-card">
              <div class="card-header">
                <div>
                  <h3>{{ uiText.systemPreferencesTitle }}</h3>
                  <p>{{ uiText.systemPreferencesSubtitle }}</p>
                </div>
                <span class="pill alt">{{ uiText.global }}</span>
              </div>
              <div class="form-grid">
                <label>
                  <span>{{ uiText.interfaceLanguage }}</span>
                  <select v-model="systemSettings.language">
                    <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </label>
                <label>
                  <span>{{ uiText.defaultCurrency }}</span>
                  <select v-model="systemSettings.currency">
                    <option v-for="option in currencyOptions" :key="option.value" :value="option.value">
                      {{ option.label }}
                    </option>
                  </select>
                </label>
              </div>
              <div class="toggle-row theme-row">
                <div>
                  <p>{{ uiText.interfaceTheme }}</p>
                  <span>{{ uiText.interfaceThemeSubtitle }}</span>
                </div>
                <div class="theme-toggle">
                  <button
                    type="button"
                    :class="{ active: systemSettings.theme === 'light' }"
                    @click="systemSettings.theme = 'light'"
                  >
                    {{ uiText.light }}
                  </button>
                  <button
                    type="button"
                    :class="{ active: systemSettings.theme === 'dark' }"
                    @click="systemSettings.theme = 'dark'"
                  >
                    {{ uiText.dark }}
                  </button>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>

      <section v-else-if="activeKey === 'customers'" class="users-page">
        <div class="users-toolbar">
          <label class="search users-search">
            <span class="search-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path
                  d="M11 19a8 8 0 1 1 5.292-14.001A8 8 0 0 1 11 19Zm0-14a6 6 0 1 0 3.964 10.5A6 6 0 0 0 11 5Zm9.707 15.293-4.35-4.35 1.414-1.414 4.35 4.35-1.414 1.414Z"
                />
              </svg>
            </span>
            <input v-model="usersSearchQuery" type="search" :placeholder="uiText.searchUsers" />
          </label>
        </div>

        <section class="users-stats">
          <article class="users-stat">
            <div class="users-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-7 8a7 7 0 0 1 14 0z" />
              </svg>
            </div>
            <p>{{ uiText.totalUsers }}</p>
            <h3>{{ usersStats.totalLabel }}</h3>
            <span class="delta up">{{ usersStats.totalDelta }}</span>
          </article>
          <article class="users-stat">
            <div class="users-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path
                  d="M9 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm6.5 1H19v2h-3.5v3h-2v-3H10v-2h3.5V9h2v3ZM4 19a5 5 0 0 1 10 0H4Z"
                />
              </svg>
            </div>
            <p>{{ uiText.newUsersMonth }}</p>
            <h3>{{ usersStats.newUsersLabel }}</h3>
            <span class="delta up">{{ usersStats.newUsersDelta }}</span>
          </article>
          <article class="users-stat">
            <div class="users-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path d="M20.285 6.709 9 18l-5.285-5.291 1.414-1.414L9 15.172l9.871-9.877z" />
              </svg>
            </div>
            <p>{{ uiText.activeUsers }}</p>
            <h3>{{ usersStats.activeLabel }}</h3>
            <span class="delta neutral">{{ usersStats.activeDelta }}</span>
          </article>
          <article class="users-stat">
            <div class="users-stat-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path d="M5 7h10l-3-3 1.414-1.414L19.828 9l-6.414 6.414L12 14l3-3H5V7zm14 10H9l3 3-1.414 1.414L4.172 15l6.414-6.414L12 10l-3 3h10v4z" />
              </svg>
            </div>
            <p>{{ uiText.churnRate }}</p>
            <h3>{{ usersStats.churnLabel }}</h3>
            <span class="delta down">{{ usersStats.churnDelta }}</span>
          </article>
        </section>

        <div class="users-layout">
          <article class="users-directory">
            <header class="directory-header">
              <div>
                <h2>{{ uiText.userDirectory }}</h2>
                <p>{{ uiText.userDirectorySubtitle }}</p>
              </div>
              <div class="directory-actions">
                <button class="ghost-btn" type="button">{{ uiText.filter }}</button>
                <button class="ghost-btn" type="button">{{ uiText.exportCsv }}</button>
              </div>
            </header>

            <div class="directory-table">
              <div class="table-head">
                <span>{{ uiText.user }}</span>
                <span>{{ uiText.contactInfo }}</span>
                <span>{{ uiText.joinedDate }}</span>
                <span>{{ uiText.bookings }}</span>
                <span>{{ uiText.status }}</span>
                <span>{{ uiText.action }}</span>
              </div>
              <div v-if="isUsersLoading" class="table-empty">{{ uiText.loadingUsers }}</div>
              <div v-else-if="usersLoadError" class="table-empty">{{ usersLoadError }}</div>
              <div v-else-if="!filteredUsers.length" class="table-empty">{{ uiText.noUsersFound }}</div>
              <div
                v-else
                v-for="user in filteredUsers"
                :key="user.id + user.email"
                class="table-row"
                @click="selectedUserId = user.id"
              >
                <div class="user-cell">
                  <div class="user-avatar">{{ user.initials }}</div>
                  <div>
                    <p>{{ user.name }}</p>
                    <span>{{ uiText.idLabel }}: {{ user.id }}</span>
                  </div>
                </div>
                <div class="contact-cell">
                  <p>{{ user.email }}</p>
                  <span>{{ user.phone }}</span>
                </div>
                <div>{{ user.joinedLabel }}</div>
                <div><span class="tag">{{ formatCompactNumber(user.bookingsCount) }} {{ uiText.totalSuffix }}</span></div>
                <div><span class="status" :class="user.statusKey">{{ user.status }}</span></div>
                <div class="dots">• • •</div>
              </div>

            </div>

            <footer class="directory-footer">
              <span>{{ usersFooterLabel }}</span>
              <div class="pager">
                <button class="pager-btn" type="button">1</button>
                <button class="pager-btn active" type="button">2</button>
                <button class="pager-btn" type="button">3</button>
                <span class="dots">…</span>
              </div>
            </footer>
          </article>

          <aside class="users-profile">
            <div class="profile-card">
              <div class="profile-hero"></div>
              <div class="profile-body">
                <div class="profile-photo">{{ selectedUser?.initials || "?" }}</div>
                <h3>{{ selectedUser?.name || uiText.selectUser }}</h3>
                <p class="profile-email">{{ selectedUser?.email || uiText.noEmailAvailable }}</p>
                <div class="profile-stats">
                  <div>
                    <span>{{ uiText.spent }}</span>
                    <strong>{{ selectedUser ? formatCurrency(selectedUser.spent) : "$0.00" }}</strong>
                  </div>
                  <div>
                    <span>{{ uiText.bookings }}</span>
                    <strong>{{ selectedUser ? formatNumber(selectedUser.bookingsCount) : "0" }}</strong>
                  </div>
                  <div>
                    <span>{{ uiText.lastLogin }}</span>
                    <strong>{{ selectedUser?.lastLoginLabel || uiText.na }}</strong>
                  </div>
                </div>
                <div class="profile-activity">
                  <p>{{ uiText.recentActivity }}</p>
                  <div v-if="!selectedUserActivities.length" class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                      <strong>{{ uiText.noRecentActivity }}</strong>
                      <span>{{ uiText.noRecentActivitySubtitle }}</span>
                    </div>
                  </div>
                  <div
                    v-else
                    v-for="activity in selectedUserActivities"
                    :key="activity.title + activity.time"
                    class="activity-item"
                  >
                    <div class="activity-dot"></div>
                    <div>
                      <strong>{{ activity.title }}</strong>
                      <span>{{ activity.time }} • {{ activity.meta }}</span>
                    </div>
                  </div>
                </div>
                <div class="profile-actions">
                  <button class="ghost-btn" type="button">{{ uiText.edit }}</button>
                  <button class="ghost-btn" type="button">{{ uiText.reset }}</button>
                </div>
                <button class="danger-btn" type="button">{{ uiText.suspendUserAccount }}</button>
              </div>
            </div>
            <div class="elite-card">
              <h4>{{ uiText.eliteMember }}</h4>
              <p>{{ interpolate(uiText.eliteMemberMessage, { name: selectedUser?.name || uiText.thisUser }) }}</p>
              <button class="link-btn" type="button">{{ uiText.viewFullEngagementReport }}</button>
            </div>
          </aside>
        </div>
      </section>

      <div class="report-export-surface" aria-hidden="true">
        <section ref="reportSheetRef" class="report-sheet">
          <header class="report-sheet__header">
            <div class="report-sheet__brand">
              <div class="report-sheet__logo">
                <img v-if="appLogoSrc" :src="appLogoSrc" alt="Achar" />
                <span v-else>A</span>
              </div>
              <div>
                <p class="report-sheet__brand-kicker">{{ uiText.brandTitle }}</p>
                <h2>{{ reportDocumentTitle }}</h2>
              </div>
            </div>
            <div class="report-sheet__meta">
              <div>
                <span>{{ reportText.generatedOn }}</span>
                <strong>{{ reportGeneratedAtLabel }}</strong>
              </div>
              <div>
                <span>{{ reportText.serviceFeeRateLabel }}</span>
                <strong>{{ reportFeeRateLabel }}</strong>
              </div>
            </div>
          </header>

          <section
            v-for="summary in exportReportSections"
            :key="summary.key"
            class="report-sheet__section"
          >
            <div class="report-sheet__section-head">
              <div>
                <p class="report-sheet__section-kicker">{{ reportText.reportPeriod }}</p>
                <h3>{{ summary.title }}</h3>
                <p class="report-sheet__section-period">{{ summary.periodLabel }}</p>
              </div>
              <span class="report-sheet__pill">{{ summary.growthLabel }}</span>
            </div>

            <div class="report-sheet__stats">
              <article>
                <span>{{ reportText.confirmedBookings }}</span>
                <strong>{{ summary.bookingCountLabel }}</strong>
              </article>
              <article>
                <span>{{ reportText.grossRevenue }}</span>
                <strong>{{ summary.grossRevenueLabel }}</strong>
              </article>
              <article>
                <span>{{ uiText.serviceFeeTotal }}</span>
                <strong>{{ summary.serviceFeesLabel }}</strong>
              </article>
              <article>
                <span>{{ reportText.averageServiceFee }}</span>
                <strong>{{ summary.averageFeeLabel }}</strong>
              </article>
              <article>
                <span>{{ reportText.periodChange }}</span>
                <strong>{{ summary.growthLabel }}</strong>
              </article>
            </div>

            <p v-if="!summary.bookingCount" class="report-sheet__empty">{{ reportText.reportNoData }}</p>
          </section>
        </section>
      </div>
    </main>
  </section>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap");

.admin-shell {
  --ink: #0f172a;
  --muted: #5f6b7a;
  --accent: #ff7a1a;
  --accent-strong: #f15b2a;
  --accent-soft: #ffe7d2;
  --accent-cool: #3b82f6;
  --surface: rgba(255, 255, 255, 0.9);
  --surface-strong: rgba(255, 255, 255, 0.96);
  --stroke: rgba(17, 24, 39, 0.08);
  --shadow: 0 30px 72px rgba(15, 23, 42, 0.14);
  --shadow-soft: 0 16px 34px rgba(15, 23, 42, 0.1);
  display: grid;
  grid-template-columns: minmax(300px, 360px) 1fr;
  min-height: calc(100vh - 90px);
  font-family: "Space Grotesk", "Segoe UI", sans-serif;
  background: radial-gradient(circle at 8% 0%, #fff1e6 0%, #f7f2ee 35%, #eef6f9 100%);
  color: var(--ink);
  position: relative;
  overflow: hidden;
}

.admin-shell.theme-dark {
  --ink: #e5eef9;
  --muted: #b7c4d4;
  --surface: rgba(15, 23, 42, 0.82);
  --surface-strong: rgba(15, 23, 42, 0.92);
  --stroke: rgba(148, 163, 184, 0.18);
  --shadow: 0 30px 72px rgba(2, 6, 23, 0.42);
  --shadow-soft: 0 16px 34px rgba(2, 6, 23, 0.3);
  background:
    radial-gradient(circle at 12% 12%, rgba(255, 122, 26, 0.14), transparent 45%),
    radial-gradient(circle at 78% 18%, rgba(59, 130, 246, 0.16), transparent 46%),
    linear-gradient(135deg, #0f172a 0%, #162033 52%, #102033 100%);
}

.admin-shell::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 10%, rgba(255, 122, 26, 0.16), transparent 45%),
    radial-gradient(circle at 80% 12%, rgba(255, 154, 77, 0.16), transparent 55%),
    radial-gradient(circle at 60% 85%, rgba(255, 122, 26, 0.12), transparent 45%);
  pointer-events: none;
}

.admin-shell.theme-dark::before {
  background:
    radial-gradient(circle at 14% 70%, rgba(255, 122, 26, 0.12), transparent 45%),
    radial-gradient(circle at 78% 78%, rgba(99, 102, 241, 0.1), transparent 48%);
}

.admin-shell > * {
  position: relative;
  z-index: 1;
}

.admin-sidebar {
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(244, 247, 252, 0.98) 100%);
  border-right: 1px solid var(--stroke);
  padding: 28px 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  backdrop-filter: blur(16px);
  position: relative;
}

.admin-shell.theme-dark .admin-sidebar {
  background: linear-gradient(180deg, rgba(15, 23, 42, 0.96) 0%, rgba(15, 23, 42, 0.88) 100%);
}

.admin-sidebar::after {
  content: "";
  position: absolute;
  inset: 20px 18px;
  border-radius: 30px;
  background:
    linear-gradient(165deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.24)),
    radial-gradient(circle at top left, rgba(255, 122, 26, 0.12), transparent 58%);
  pointer-events: none;
  border: 1px solid rgba(255, 255, 255, 0.48);
}

.admin-sidebar > * {
  position: relative;
  z-index: 1;
}

.brand-card,
.sidebar-block {
  border: 1px solid rgba(15, 23, 42, 0.07);
  background: rgba(255, 255, 255, 0.78);
  box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
  backdrop-filter: blur(14px);
}

.admin-shell.theme-dark .brand-card,
.admin-shell.theme-dark .sidebar-block {
  background: rgba(15, 23, 42, 0.56);
  border-color: rgba(148, 163, 184, 0.16);
}

.brand-card {
  display: grid;
  gap: 16px;
  padding: 18px;
  border-radius: 28px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  width: 52px;
  height: 52px;
  border-radius: 18px;
  background: linear-gradient(135deg, #fff5eb 0%, #ffd7b5 100%);
  display: grid;
  place-items: center;
  box-shadow: 0 16px 30px rgba(255, 122, 26, 0.2);
  border: 1px solid rgba(255, 122, 26, 0.16);
}

.brand-logo img {
  width: 30px;
  height: 30px;
  object-fit: contain;
}

.brand-mark {
  font-weight: 700;
  color: var(--accent);
}

.brand-kicker {
  margin: 0 0 4px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #c86423;
}

.admin-shell.theme-dark .brand-kicker,
.admin-shell.theme-dark .sidebar-section-label {
  color: #f4b17b;
}

.brand-title {
  font-weight: 700;
  margin: 0;
  font-family: "Fraunces", serif;
  font-size: 22px;
  color: #132238;
}

.admin-shell.theme-dark .brand-title {
  color: #f8fafc;
}

.brand-subtitle {
  margin: 3px 0 0;
  font-size: 13px;
  line-height: 1.5;
  color: #66758d;
}

.admin-shell.theme-dark .brand-subtitle {
  color: #c6d4e3;
}

.sidebar-section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #9a6a4b;
}

.sidebar-block {
  display: grid;
  gap: 14px;
  padding: 16px;
  border-radius: 26px;
}

.sidebar-block-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 2px 4px 0;
}

.sidebar-section-caption {
  font-size: 12px;
  color: #7b8ba2;
}

.admin-shell.theme-dark .sidebar-section-caption {
  color: #9fb2c8;
}

.admin-nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid transparent;
  background: rgba(255, 255, 255, 0.44);
  padding: 12px 14px;
  border-radius: 20px;
  font-size: 15px;
  cursor: pointer;
  color: #314258;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease,
    background-color 0.2s ease;
}

.admin-shell.theme-dark .nav-item {
  color: #d7e2ee;
  background: rgba(15, 23, 42, 0.34);
}

.nav-icon {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, #ffffff, #eef3f9);
  color: #7c8ba3;
  transition: all 0.2s ease;
  border: 1px solid rgba(148, 163, 184, 0.14);
  flex-shrink: 0;
}

.admin-shell.theme-dark .nav-icon {
  background: radial-gradient(circle at 30% 20%, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.88) 70%);
  color: #9fb2c8;
  border-color: rgba(148, 163, 184, 0.12);
}

.nav-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.8);
  border-color: rgba(255, 122, 26, 0.12);
  transform: translateX(3px);
  box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
}

.nav-item.active {
  background:
    linear-gradient(135deg, rgba(255, 244, 234, 0.98), rgba(255, 228, 207, 0.96));
  color: #d05f17;
  border-color: rgba(255, 122, 26, 0.22);
  box-shadow:
    inset 3px 0 0 var(--accent),
    0 14px 28px rgba(255, 122, 26, 0.12);
}

.nav-item.active .nav-icon {
  background: linear-gradient(135deg, rgba(255, 122, 26, 0.24), rgba(255, 122, 26, 0.08));
  color: #d7641d;
  border-color: rgba(255, 122, 26, 0.24);
}

.admin-main {
  padding: 28px 36px 40px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  position: relative;
  overflow: hidden;
}

.admin-main::before {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 16% 8%, rgba(255, 122, 26, 0.08), transparent 42%),
    radial-gradient(circle at 80% 18%, rgba(74, 144, 226, 0.12), transparent 50%),
    radial-gradient(circle at 60% 80%, rgba(255, 122, 26, 0.06), transparent 45%);
  pointer-events: none;
}

.admin-main > * {
  position: relative;
  z-index: 1;
}

.admin-topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.search {
  flex: 1;
  max-width: 360px;
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 16px;
  padding: 10px 14px;
  gap: 8px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search:focus-within {
  transform: translateY(-1px);
  box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
}

.search-icon svg {
  width: 16px;
  height: 16px;
  fill: #94a3b8;
}

.search input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 14px;
  outline: none;
  color: var(--ink);
}

.topbar-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-btn {
  border: 1px solid var(--stroke);
  background: #fff;
  width: 38px;
  height: 38px;
  border-radius: 14px;
  box-shadow: var(--shadow-soft);
  cursor: pointer;
  display: grid;
  place-items: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.icon-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
}

.icon-btn svg {
  width: 16px;
  height: 16px;
  fill: #6b7280;
}

.topbar-avatar {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #ffb98b 0%, #ff8b3d 100%);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 600;
}

.admin-hero {
  display: grid;
  gap: 12px;
  padding: 26px 28px;
  border-radius: 28px;
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 244, 236, 0.9) 65%, rgba(236, 244, 255, 0.92) 100%);
  border: 1px solid rgba(255, 255, 255, 0.75);
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.14);
  position: relative;
  overflow: hidden;
}

.admin-hero::before {
  content: "";
  position: absolute;
  inset: -40% 50% 30% -10%;
  background: radial-gradient(circle at top, rgba(255, 122, 26, 0.28), transparent 60%);
  opacity: 0.55;
  pointer-events: none;
}

.admin-hero::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.55), transparent 55%);
  pointer-events: none;
}

.admin-hero > * {
  position: relative;
  z-index: 1;
}

.admin-hero .eyebrow {
  font-size: 13px;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin: 0;
  color: #c45a12;
}

.hero-title {
  margin: 0;
  font-size: 40px;
  font-weight: 720;
  font-family: "Fraunces", serif;
  color: var(--ink);
}

.hero-subtitle {
  margin: 0;
  color: var(--muted);
  font-size: 16px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 6px;
}

.hero-actions .ghost-btn,
.hero-actions .primary-btn {
  min-width: 160px;
}

.admin-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.stat-card {
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(245, 248, 255, 0.92));
  padding: 22px 24px 20px;
  border-radius: 22px;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.9);
  transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
  animation: stat-pop 0.6s ease both;
}

.stat-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top right, rgba(255, 122, 26, 0.16), transparent 50%),
    linear-gradient(135deg, rgba(255, 255, 255, 0.6), transparent 55%);
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.stat-card::before {
  content: "";
  position: absolute;
  left: 20px;
  top: 18px;
  width: 54px;
  height: 5px;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(255, 122, 26, 0.9), rgba(255, 154, 77, 0.5));
  opacity: 0.7;
}

.stat-card:hover::after {
  opacity: 0.9;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.18);
  border-color: rgba(255, 122, 26, 0.2);
}

.stat-card:nth-child(1)::before {
  background: linear-gradient(90deg, #ff7a1a, #ffb26b);
}

.stat-card:nth-child(2)::before {
  background: linear-gradient(90deg, #3b82f6, #67e8f9);
}

.stat-card:nth-child(3)::before {
  background: linear-gradient(90deg, #10b981, #6ee7b7);
}

.stat-card:nth-child(4)::before {
  background: linear-gradient(90deg, #f15b2a, #ff9a4d);
}

.stat-card:nth-child(1) {
  animation-delay: 0.02s;
}

.stat-card:nth-child(2) {
  animation-delay: 0.08s;
}

.stat-card:nth-child(3) {
  animation-delay: 0.14s;
}

.stat-card:nth-child(4) {
  animation-delay: 0.2s;
}

.stat-card.solid {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 52%, #ff9a4d 100%);
  color: #fff;
  border-color: transparent;
  box-shadow: 0 30px 70px rgba(241, 91, 42, 0.35);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(255, 226, 206, 0.5));
  margin-bottom: 14px;
  border: 1px solid rgba(255, 122, 26, 0.22);
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08), inset 0 0 0 1px rgba(255, 255, 255, 0.6);
  display: grid;
  place-items: center;
  color: #ff7a1a;
}

.stat-card.solid .stat-icon {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.stat-icon svg {
  width: 20px;
  height: 20px;
  fill: currentColor;
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: inherit;
  opacity: 0.65;
  text-transform: uppercase;
  letter-spacing: 0.14em;
}

.stat-value {
  margin: 6px 0 0;
  font-size: 28px;
  font-weight: 720;
}

.stat-delta {
  position: absolute;
  top: 16px;
  right: 16px;
  font-size: 12px;
  padding: 5px 10px;
  border-radius: 999px;
  background: #eaf8ef;
  color: #2f9e5f;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.stat-card.solid::after {
  opacity: 0.35;
}

.stat-card.solid .stat-label {
  opacity: 0.9;
  letter-spacing: 0.08em;
}

.stat-card.solid .stat-value {
  font-size: 30px;
}

@keyframes stat-pop {
  0% {
    opacity: 0;
    transform: translateY(8px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-delta.down {
  background: #ffe8e5;
  color: #e2553f;
}

.stat-delta.neutral {
  background: #eef2f6;
  color: #64748b;
}

.stat-card.solid .stat-delta {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
}

.admin-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  align-items: start;
}

.activity-card,
.report-card,
.status-card {
  background: var(--surface);
  border-radius: 18px;
  padding: 20px;
  box-shadow: var(--shadow);
  border: 1px solid var(--stroke);
}

.activity-card header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.activity-card h3,
.report-card h3,
.status-card h3 {
  margin: 0;
  font-family: "Fraunces", serif;
  font-weight: 600;
  color: var(--ink);
}

.report-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.report-pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.35);
  font-size: 12px;
  font-weight: 600;
  color: #fff;
}

.activity-list {
  display: grid;
  gap: 14px;
}

.activity-empty {
  padding: 16px;
  border-radius: 14px;
  border: 1px dashed rgba(15, 23, 42, 0.12);
  background: rgba(255, 255, 255, 0.9);
  color: var(--muted);
  font-size: 13px;
}

.activity-item {
  display: flex;
  gap: 12px;
  align-items: center;
  padding-bottom: 12px;
  border-bottom: 1px solid #f1f4f8;
  transition: transform 0.2s ease;
}

.activity-item:hover {
  transform: translateX(4px);
}

.activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.activity-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #ffe6d1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #c45a18;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
}

.activity-icon.is-customer {
  background: #e7f5ff;
  color: #1d4ed8;
}

.activity-icon.is-vendor {
  background: #ecfdf3;
  color: #15803d;
}

.activity-text {
  margin: 0;
  font-size: 14px;
}

.activity-time {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--muted);
}

.link-btn {
  border: none;
  background: transparent;
  color: var(--accent);
  cursor: pointer;
  font-weight: 600;
}

.side-stack {
  display: grid;
  gap: 18px;
}

.report-card {
  background: linear-gradient(135deg, #ff7a1a 0%, #f15b2a 60%, #ff9a4d 100%);
  color: #fff;
  border: none;
  position: relative;
  overflow: hidden;
}

.report-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 45%);
  opacity: 0.7;
}

.report-card > * {
  position: relative;
  z-index: 1;
}

.report-card p {
  margin: 12px 0 16px;
  opacity: 0.9;
}

.report-metrics {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.report-metric {
  padding: 12px 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.14);
  backdrop-filter: blur(10px);
}

.report-metric span {
  display: block;
  font-size: 12px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  opacity: 0.78;
}

.report-metric strong {
  display: block;
  margin-top: 6px;
  font-size: 18px;
}

.report-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.primary-btn {
  border: none;
  background: linear-gradient(135deg, #ff8a3c 0%, #f15b2a 100%);
  color: #fff;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(241, 91, 42, 0.25);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.primary-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(241, 91, 42, 0.3);
}

.primary-btn:disabled,
.secondary-btn:disabled {
  cursor: wait;
  opacity: 0.78;
  transform: none;
  box-shadow: none;
}

.ghost-btn {
  border: 1px solid rgba(255, 122, 26, 0.3);
  background: rgba(255, 255, 255, 0.8);
  color: #c65300;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.ghost-btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-soft);
}

.report-card .primary-btn {
  background: #fff;
  color: #f15b2a;
  box-shadow: none;
}

.secondary-btn {
  border: 1px solid rgba(255, 255, 255, 0.45);
  background: transparent;
  color: #fff;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s ease, background 0.2s ease;
}

.secondary-btn:hover {
  transform: translateY(-1px);
  background: rgba(255, 255, 255, 0.12);
}

.report-export-surface {
  position: fixed;
  left: -99999px;
  top: 0;
  width: 210mm;
  pointer-events: none;
  opacity: 0;
}

.report-sheet {
  width: 210mm;
  min-height: 297mm;
  padding: 18mm 16mm;
  background: #ffffff;
  color: #0f172a;
  display: grid;
  gap: 18px;
}

.report-sheet__header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: flex-start;
}

.report-sheet__brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.report-sheet__logo {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  background: #fff1e4;
  display: grid;
  place-items: center;
  overflow: hidden;
}

.report-sheet__logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.report-sheet__logo span {
  font-family: "Fraunces", serif;
  font-size: 24px;
  color: #f15b2a;
}

.report-sheet__brand-kicker,
.report-sheet__section-kicker {
  margin: 0 0 4px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 11px;
  color: #b66f34;
}

.report-sheet__header h2,
.report-sheet__section-head h3 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.report-sheet__meta {
  display: grid;
  gap: 10px;
  min-width: 200px;
}

.report-sheet__meta div,
.report-sheet__stats article {
  padding: 12px 14px;
  border-radius: 16px;
  background: #fff8f2;
  border: 1px solid #f4dfcf;
}

.report-sheet__meta span,
.report-sheet__stats span {
  display: block;
  font-size: 12px;
  color: #7a8799;
  margin-bottom: 6px;
}

.report-sheet__meta strong,
.report-sheet__stats strong {
  font-size: 16px;
  color: #0f172a;
}

.report-sheet__section {
  display: grid;
  gap: 14px;
  padding: 16px;
  border-radius: 24px;
  border: 1px solid #f1dfd0;
  background: linear-gradient(135deg, #ffffff 0%, #fff7f0 100%);
}

.report-sheet__section-head {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
}

.report-sheet__section-period {
  margin: 6px 0 0;
  color: #64748b;
}

.report-sheet__pill {
  padding: 8px 12px;
  border-radius: 999px;
  background: #fff1e4;
  color: #f15b2a;
  font-weight: 700;
  white-space: nowrap;
}

.report-sheet__stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.report-sheet__empty {
  margin: 0;
  color: #64748b;
}

.status-row {
  display: grid;
  gap: 10px;
  margin-top: 12px;
}

.status-row p {
  margin: 0;
  font-size: 13px;
}

.status-row span {
  font-weight: 600;
}

.bar {
  height: 6px;
  border-radius: 999px;
  background: #eef2f6;
  overflow: hidden;
}

.bar-fill {
  display: block;
  height: 100%;
  background: #2f9e5f;
}

.bar-fill.warning {
  background: #f59f00;
}

.settings-page {
  display: grid;
  gap: 24px;
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.settings-title {
  display: flex;
  align-items: center;
  gap: 16px;
}

.settings-title h2 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.settings-title p {
  margin: 4px 0 0;
  color: var(--muted);
  font-size: 14px;
}

.settings-icon {
  width: 52px;
  height: 52px;
  border-radius: 18px;
  background: linear-gradient(135deg, #fff1e4 0%, #ffe2cb 100%);
  border: 1px solid rgba(255, 122, 26, 0.2);
  display: grid;
  place-items: center;
  color: var(--accent);
  box-shadow: 0 12px 24px rgba(255, 122, 26, 0.2);
}

.settings-icon svg {
  width: 22px;
  height: 22px;
  fill: currentColor;
}

.settings-quick {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.settings-feedback {
  margin: 0;
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid rgba(17, 24, 39, 0.08);
  background: rgba(255, 255, 255, 0.82);
  color: var(--muted);
  font-size: 14px;
  box-shadow: var(--shadow-soft);
}

.settings-feedback.success {
  border-color: rgba(47, 158, 95, 0.22);
  color: #1f7a4a;
}

.settings-feedback.error {
  border-color: rgba(226, 85, 63, 0.24);
  color: #c53f28;
}

.settings-layout {
  display: grid;
  grid-template-columns: minmax(220px, 260px) 1fr;
  gap: 24px;
}

.settings-menu {
  background: var(--surface-strong);
  border-radius: 20px;
  padding: 18px;
  display: grid;
  gap: 12px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow-soft);
  height: fit-content;
}

.settings-link {
  border: none;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 14px;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.settings-link .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #e2e8f0;
}

.settings-link.active,
.settings-link:hover {
  background: rgba(255, 122, 26, 0.12);
  color: var(--accent);
}

.settings-link.active .dot {
  background: var(--accent);
}

.settings-content {
  display: grid;
  gap: 20px;
}

.settings-card {
  background: var(--surface);
  border-radius: 20px;
  padding: 22px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.card-header h3 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.card-header p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: #fff2e6;
  color: #c65300;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.pill.alt {
  background: #eef6ff;
  color: #2f5aa8;
}

.profile-row {
  display: grid;
  gap: 20px;
}

.profile-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 14px;
}

.form-grid label {
  display: grid;
  gap: 6px;
  font-size: 12px;
  color: var(--muted);
}

.form-grid label span {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 600;
  color: #6b7280;
}

.form-grid input,
.form-grid select {
  border: 1px solid var(--stroke);
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 14px;
  background: #fff;
  color: var(--ink);
}

.form-grid input:disabled {
  background: #f1f5f9;
  color: #94a3b8;
}

.form-grid input[readonly] {
  background: #f8fafc;
  color: #334155;
}

.form-grid .full {
  grid-column: 1 / -1;
}

.toggle-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid var(--stroke);
  background: rgba(255, 255, 255, 0.9);
  margin-top: 12px;
  flex-wrap: wrap;
}

.toggle-row p {
  margin: 0;
  font-weight: 600;
}

.toggle-row span {
  display: block;
  color: var(--muted);
  font-size: 12px;
  margin-top: 4px;
}

.toggle-list {
  display: grid;
  gap: 12px;
}

.settings-card-actions {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.inline-feedback {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
}

.inline-feedback.success {
  color: #1f7a4a;
}

.inline-feedback.error {
  color: #c53f28;
}

.switch {
  position: relative;
  width: 46px;
  height: 26px;
  flex-shrink: 0;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch span {
  position: absolute;
  inset: 0;
  background: #e2e8f0;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.switch span::after {
  content: "";
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.2s ease;
  box-shadow: 0 6px 12px rgba(15, 23, 42, 0.2);
}

.switch input:checked + span {
  background: #ff7a1a;
}

.switch input:checked + span::after {
  transform: translateX(20px);
}

.theme-row {
  margin-top: 16px;
}

.theme-toggle {
  display: inline-flex;
  background: #f1f5f9;
  border-radius: 999px;
  padding: 4px;
  gap: 4px;
}

.theme-toggle button {
  border: none;
  background: transparent;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  color: #6b7280;
}

.theme-toggle button.active {
  background: #fff;
  color: #c65300;
  box-shadow: var(--shadow-soft);
}

.link-btn.danger {
  color: #e2553f;
}

.admin-shell.theme-dark .settings-menu,
.admin-shell.theme-dark .settings-card,
.admin-shell.theme-dark .toggle-row,
.admin-shell.theme-dark .settings-feedback {
  background: rgba(15, 23, 42, 0.78);
  border-color: rgba(148, 163, 184, 0.16);
}

.admin-shell.theme-dark .settings-link {
  color: #d7e2ee;
}

.admin-shell.theme-dark .settings-link .dot {
  background: rgba(148, 163, 184, 0.3);
}

.admin-shell.theme-dark .pill {
  background: rgba(255, 122, 26, 0.16);
  color: #ffb98b;
}

.admin-shell.theme-dark .pill.alt {
  background: rgba(59, 130, 246, 0.16);
  color: #bfd8ff;
}

.admin-shell.theme-dark .form-grid input,
.admin-shell.theme-dark .form-grid select {
  background: rgba(15, 23, 42, 0.88);
  color: var(--ink);
  border-color: rgba(148, 163, 184, 0.18);
}

.admin-shell.theme-dark .form-grid input::placeholder {
  color: #7f8ea3;
}

.admin-shell.theme-dark .form-grid input[readonly] {
  background: rgba(30, 41, 59, 0.78);
  color: #d7e2ee;
}

.admin-shell.theme-dark .theme-toggle {
  background: rgba(30, 41, 59, 0.88);
}

.admin-shell.theme-dark .theme-toggle button.active {
  background: rgba(255, 255, 255, 0.14);
  color: #ffb98b;
}

.users-page {
  display: grid;
  gap: 22px;
}

.users-toolbar {
  display: flex;
  justify-content: flex-end;
}

.users-search {
  max-width: 520px;
  width: 100%;
  border-radius: 18px;
  padding: 12px 16px;
  border: 1px solid rgba(255, 255, 255, 0.75);
  background: rgba(255, 255, 255, 0.96);
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
}

.users-search:focus-within {
  box-shadow: 0 22px 48px rgba(15, 23, 42, 0.16);
}

.users-search .search-icon svg {
  fill: #94a3b8;
}

.users-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 18px;
}

.users-stat {
  background: linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(246, 249, 255, 0.92));
  border-radius: 20px;
  padding: 18px;
  border: 1px solid rgba(255, 255, 255, 0.85);
  box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
  position: relative;
  overflow: hidden;
}

.users-stat::after {
  content: "";
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 55%);
  opacity: 0.6;
  pointer-events: none;
}

.users-stat:nth-child(1)::after {
  background: radial-gradient(circle at top right, rgba(255, 122, 26, 0.16), transparent 55%);
}

.users-stat:nth-child(2)::after {
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.16), transparent 55%);
}

.users-stat:nth-child(3)::after {
  background: radial-gradient(circle at top right, rgba(16, 185, 129, 0.16), transparent 55%);
}

.users-stat:nth-child(4)::after {
  background: radial-gradient(circle at top right, rgba(239, 68, 68, 0.16), transparent 55%);
}

.users-stat > * {
  position: relative;
  z-index: 1;
}

.users-stat h3 {
  margin: 8px 0;
  font-size: 24px;
  font-weight: 700;
}

.users-stat p {
  margin: 0;
  font-size: 12px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

.users-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.1);
  color: #ff7a1a;
  margin-bottom: 10px;
}

.users-stat-icon svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

.users-stat:nth-child(2) .users-stat-icon {
  color: #3b82f6;
}

.users-stat:nth-child(3) .users-stat-icon {
  color: #10b981;
}

.users-stat:nth-child(4) .users-stat-icon {
  color: #ef4444;
}

.delta {
  font-size: 12px;
}

.delta.up {
  color: #f15b2a;
}

.delta.down {
  color: #e2553f;
}

.delta.neutral {
  color: #64748b;
}

.users-layout {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
  gap: 20px;
  align-items: start;
  min-width: 0;
}

.users-directory {
  background: var(--surface);
  border-radius: 20px;
  padding: 20px;
  border: 1px solid var(--stroke);
  box-shadow: var(--shadow);
  min-width: 0;
  overflow: hidden;
}

.directory-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

.directory-header h2 {
  margin: 0;
  font-family: "Fraunces", serif;
}

.directory-header p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.directory-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.directory-actions .ghost-btn {
  white-space: nowrap;
}

.directory-table {
  display: grid;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.table-empty {
  padding: 16px;
  border-radius: 14px;
  border: 1px dashed rgba(15, 23, 42, 0.12);
  background: #fff;
  color: var(--muted);
  font-size: 13px;
}

.table-head,
.table-row {
  display: grid;
  grid-template-columns: 1.2fr 1.2fr 0.8fr 0.7fr 0.7fr 0.4fr;
  align-items: center;
  gap: 10px;
  padding: 10px 8px;
  min-width: 680px;
}

.table-head {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #6b7280;
}

.table-row {
  background: #fff;
  border-radius: 14px;
  border: 1px solid var(--stroke);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-cell p {
  margin: 0;
  font-weight: 600;
}

.user-cell span,
.contact-cell span {
  font-size: 12px;
  color: var(--muted);
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #ffe7d2;
  display: grid;
  place-items: center;
  color: #c65300;
  font-weight: 700;
}

.user-avatar.dark {
  background: #0f172a;
  color: #fff;
}

.contact-cell p {
  margin: 0;
  font-size: 13px;
}

.tag {
  background: #fff2e6;
  color: #c65300;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
}

.status.active {
  background: #eaf8ef;
  color: #2f9e5f;
}

.status.inactive {
  background: #eef2f6;
  color: #64748b;
}

.dots {
  color: #94a3b8;
  font-weight: 700;
}

.directory-footer {
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 13px;
  color: var(--muted);
}

.pager {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pager-btn {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: 1px solid var(--stroke);
  background: #fff;
  cursor: pointer;
}

.pager-btn.active {
  background: var(--accent);
  color: #fff;
  border-color: transparent;
}

.users-profile {
  display: grid;
  gap: 16px;
  min-width: 0;
  width: 100%;
  max-width: 360px;
  justify-self: stretch;
}

.profile-card {
  background: var(--surface-strong);
  border-radius: 22px;
  border: 1px solid var(--stroke);
  overflow: hidden;
  box-shadow: var(--shadow);
  width: 100%;
}

.profile-hero {
  height: 90px;
  background: linear-gradient(135deg, #ff7a1a 0%, #ff9a4d 100%);
}

.profile-body {
  padding: 18px;
  display: grid;
  gap: 14px;
}

.profile-photo {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  background: #111827;
  color: #fff;
  display: grid;
  place-items: center;
  font-weight: 700;
  margin-top: -40px;
  border: 4px solid #fff;
}

.profile-body h3 {
  margin: 0;
}

.profile-email {
  margin: 0;
  color: var(--muted);
  font-size: 13px;
}

.profile-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  text-align: center;
  font-size: 12px;
}

.profile-stats strong {
  display: block;
  margin-top: 4px;
}

.profile-activity {
  display: grid;
  gap: 10px;
}

.profile-activity p {
  margin: 0;
  font-weight: 600;
}

.profile-activity .activity-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}

.profile-activity .activity-item span {
  display: block;
  font-size: 12px;
  color: var(--muted);
  margin-top: 2px;
}

.activity-dot {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  background: #fff2e6;
}

.profile-actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
  width: 100%;
}

.profile-actions .ghost-btn {
  width: 100%;
  white-space: nowrap;
}

.danger-btn {
  border: 1px solid #ffb4a9;
  background: #fff5f5;
  color: #e2553f;
  padding: 10px 14px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  width: 100%;
}

.elite-card {
  background: linear-gradient(135deg, #ff7a1a 0%, #ff9a4d 100%);
  border-radius: 20px;
  padding: 18px;
  color: #fff;
  box-shadow: var(--shadow-soft);
  width: 100%;
}

.admin-shell :is(
    .stat-card,
    .activity-card,
    .report-card,
    .status-card,
    .settings-card,
    .users-directory,
    .profile-card,
    .elite-card,
    .users-stat
  ) {
  outline: 1px solid rgba(255, 122, 26, 0.2);
  outline-offset: -10px;
}

.admin-shell :is(.stat-card.solid, .report-card, .elite-card) {
  outline-color: rgba(255, 255, 255, 0.35);
}

.elite-card h4 {
  margin: 0 0 8px;
}

.elite-card p {
  margin: 0 0 12px;
  font-size: 13px;
}

@media (max-width: 1100px) {
  .users-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .directory-actions {
    width: 100%;
  }

  .directory-actions .ghost-btn {
    flex: 1 1 140px;
  }

  .profile-actions .ghost-btn,
  .danger-btn {
    width: 100%;
  }

  .profile-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    text-align: left;
  }
}

@media (max-width: 520px) {
  .profile-stats {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .admin-sidebar {
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    padding: 20px;
    gap: 16px;
  }

  .admin-nav {
    flex-direction: row;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .nav-item {
    min-width: 220px;
  }

  .admin-grid {
    grid-template-columns: 1fr;
  }

  .settings-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .admin-main {
    padding: 24px;
  }

  .admin-sidebar {
    padding: 20px 16px;
  }

  .sidebar-block-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .admin-topbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search {
    max-width: none;
  }

  .hero-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .hero-actions .ghost-btn,
  .hero-actions .primary-btn {
    width: 100%;
    text-align: center;
  }

  .report-metrics,
  .report-sheet__stats {
    grid-template-columns: 1fr;
  }

  .report-actions {
    flex-direction: column;
  }

  .report-actions .primary-btn,
  .report-actions .secondary-btn {
    width: 100%;
  }

  .nav-item {
    flex: 0 0 auto;
    white-space: nowrap;
  }
}
</style>


