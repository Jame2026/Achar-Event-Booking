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

function dashboardRedirect() {
  return getStoredRole() === 'vendor' ? '/legacy-app?page=dashboard' : '/legacy-app?page=bookings'
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
<<<<<<< HEAD
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
=======
    path: '/booking',
    redirect: '/legacy-app?page=bookings'
  },
  {
    path: '/dashboard',
    redirect: dashboardRedirect
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
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
<<<<<<< HEAD
    path: "/checkout/receipt",
    name: "CheckoutReceipt",
    component: CheckoutReceiptPage,
=======
    path: '/vendor/dashboard',
    redirect: dashboardRedirect
  },
  {
    path: '/checkout',
    name: 'Checkout',
    component: CheckoutPage
>>>>>>> 63503f0662789d10e8d251f94e2aa105ea2ac22f
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
});

export default router;
