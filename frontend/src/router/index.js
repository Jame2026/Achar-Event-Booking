import { createRouter, createWebHistory } from "vue-router";
import LegacyAppShell from "../LegacyAppShell.vue";
import GuestPreview from "../components/GuestPreview.vue";
import AboutPage from "../components/AboutPage.vue";
import ContactPage from "../components/ContactPage.vue";
import VendorDetail from "../components/VendorDetail.vue";
import CheckoutPage from "../components/CheckoutPage.vue";
import CheckoutConfirmedPage from "../components/CheckoutConfirmedPage.vue";
import CheckoutReceiptPage from "../components/CheckoutReceiptPage.vue";
import Home from "@/components/Home.vue";
import ForgotPasswordForm from "../components/ForgotPasswordForm.vue";
import ResetPasswordForm from "../components/ResetPasswordForm.vue";

const AUTH_USER_STORAGE_KEY = 'achar_auth_user'

function getStoredRole() {
  try {
    const raw = localStorage.getItem(AUTH_USER_STORAGE_KEY)
    if (!raw) return 'guest'
    const user = JSON.parse(raw)
    return String(user?.role || 'guest').trim().toLowerCase()
  } catch {
    return 'guest'
  }
}

function isDashboardRole(role) {
  return role === 'vendor' || role === 'admin'
}

function dashboardRedirect() {
  return isDashboardRole(getStoredRole()) ? '/legacy-app?page=dashboard' : '/legacy-app?page=bookings'
}

function adminLoginRedirect() {
  return isDashboardRole(getStoredRole())
    ? '/legacy-app?page=dashboard'
    : '/legacy-app?view=login&page=dashboard'
}

const routes = [
  {
    path: "/",
    name: "Home",
    component: Home,
  },
  {
    path: "/home",
    redirect: "/",
  },
  {
    path: "/about",
    name: "About",
    component: AboutPage,
  },
  {
    path: "/contact",
    name: "Contact",
    component: ContactPage,
  },
  {
    path: "/booking",
    name: "BookingForm",
    component: GuestPreview,
    props: { section: "bookings" },
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: GuestPreview,
    props: { section: "dashboard" },
  },
  {
    path: "/services",
    name: "Services",
    component: GuestPreview,
    props: { section: "services-packages" },
  },
  {
    path: "/services/packages",
    name: "ServicePackages",
    component: GuestPreview,
    props: { section: "services-packages" },
  },
  {
    path: "/services/overall",
    name: "ServiceOverall",
    component: GuestPreview,
    props: { section: "services-overall" },
  },
  {
    path: "/legacy-app",
    name: "LegacyApp",
    component: LegacyAppShell,
  },
  {
    path: "/login",
    redirect: "/legacy-app?view=login",
  },
  {
    path: "/register",
    redirect: "/legacy-app?view=register",
  },
  {
    path: "/admin",
    redirect: adminLoginRedirect,
  },
  {
    path: "/admin/login",
    redirect: adminLoginRedirect,
  },
  {
    path: "/admin/dashboard",
    redirect: adminLoginRedirect,
  },
  {
    path: "/forgot-password",
    name: "ForgotPassword",
    component: ForgotPasswordForm,
  },
  {
    path: "/reset-password",
    name: "ResetPassword",
    component: ResetPasswordForm,
  },
  {
    path: "/vendor",
    name: "VendorDetail",
    component: VendorDetail,
  },
  {
    path: "/vendor/legacy",
    redirect: "/legacy-app?page=vendor",
  },
  {
    path: "/checkout",
    name: "Checkout",
    component: CheckoutPage,
  },
  {
    path: "/checkout/confirmed",
    name: "CheckoutConfirmed",
    component: CheckoutConfirmedPage,
  },
  {
    path: "/checkout/receipt",
    name: "CheckoutReceipt",
    component: CheckoutReceiptPage,
  },
  {
    path: '/vendor/dashboard',
    redirect: dashboardRedirect
  },
  {
    path: "/customization",
    name: "Customization",
    component: GuestPreview,
    props: { section: "customization" },
  },
  {
    path: "/favorite",
    name: "Favorite",
    component: GuestPreview,
    props: { section: "favorite" },
  },
  {
    path: "/availability",
    redirect: "/legacy-app?page=availability",
  },
  {
    path: "/my-bookings",
    redirect: "/legacy-app?page=bookings",
  },
  {
    path: "/messages",
    redirect: "/legacy-app?page=messages",
  },
  {
    path: "/profile",
    redirect: "/legacy-app?page=profile",
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    if (to.fullPath === from.fullPath) return undefined
    return { top: 0, left: 0 }
  },
});

export default router;
