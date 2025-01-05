import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import AdminLayout from '@/components/layout/AdminLayout.vue';


// Views
import LandingPage from '@/views/LandingPage.vue';
import ServicesPage from '@/views/ServicesPage.vue';
import GalleryPage from '@/views/GalleryPage.vue';
import ContactPage from '@/views/ContactPage.vue';
import AboutUsPage from '@/views/AboutUs.vue';
import BookingPageView from '@/views/BookingPageView.vue';
import LoginPage from '@/views/auth/LoginPage.vue';
import bookingUserManage from '@/views/bookingUserManage.vue';
import RegisterPage from '@/views/auth/RegisterPage.vue';
import ForgotPasswordPage from '@/views/auth/ForgotPasswordPage.vue';
import ResetPasswordPage from '@/views/auth/ResetPasswordPage.vue';
import ProfilePage from '@/views/user/ProfilePage.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue';
import BookingsManagement from '@/views/admin/BookingsManagement.vue';
import PackagesManagement from '@/views/admin/PackagesManagement.vue';
import PortfoliosManagement from '@/views/admin/PortfolioManagement.vue';
import UsersManagement from '@/views/admin/UsersManagement.vue';
import ContactUsManagement from '@/views/admin/ContactUsManagement.vue';
import ReportsManagement from '@/views/admin/ReportsManagement.vue';
import GalleryManagement from '@/views/admin/GalleryManagement.vue';
import TestimonialsManagement from '@/views/admin/TestimonialsManagement.vue';
import FAQManagement from '@/views/admin/FAQManagement.vue';
import SettingsManagement from '@/views/admin/SettingsManagement.vue';
import TransactionsManagement from '@/views/admin/TransactionsManagement.vue';
import PaymentAnalytics from '@/views/admin/PaymentAnalytics.vue';
import ChatManagement from '@/views/admin/ChatManagement.vue';
import ChatView from '@/views/admin/ChatView.vue';
import UserChatView from '@/views/chat/UserChatView.vue';
import PackagesPage from '@/views/PackagesPage.vue';
import PackageDetailsPage from '@/views/PackageDetailsPage.vue';
import PaymentManagement from '@/components/admin/PaymentManagement.vue'
import BookingChat from '@/components/chat/BookingChat.vue'
import ArchivedBookings from '@/views/admin/ArchivedBookings.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: LandingPage
  },
  {
    path: '/services',
    name: 'services',
    component: ServicesPage
  },
  {
    path: '/gallery',
    name: 'gallery',
    component: GalleryPage
  },
  {
    path: '/contact',
    name: 'contact',
    component: ContactPage
  },
  {
    path: '/about-us',
    name: 'AboutUs',
    component: () => import('@/views/AboutUsPage.vue')
  },
  {
    path: '/book',
    name: 'book',
    component: BookingPageView,
    meta: { requiresAuth: true }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    meta: { guestOnly: true }
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
    meta: { guestOnly: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordPage,
    meta: { guestOnly: true }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: ResetPasswordPage,
    meta: { guestOnly: true }
  },
  {
    path: '/bookings',
    name: 'bookingUserManage',
    component: bookingUserManage,
    meta: { requiresAuth: true }
  },
  {
    path: '/bookings/:id',
    name: 'BookingPageView',
    component: BookingPageView,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfilePage,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    component: AdminLayout, 
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'bookings',
        name: 'admin-bookings', 
        component: BookingsManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'contact-us',
        name: 'admin-contact-us', 
        component: ContactUsManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'packages',
        name: 'admin-packages',
        component: PackagesManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'portfolios',
        name: 'admin-portfolios',
        component: PortfoliosManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'users',
        name: 'admin-users',
        component: UsersManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: ReportsManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'gallery',
        name: 'admin-gallery',
        component: GalleryManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'testimonials',
        name: 'admin-testimonials',
        component: TestimonialsManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'faqs',
        name: 'admin-faqs',
        component: FAQManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: SettingsManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: 'transactions',
        name: 'admin-transactions',
        component: TransactionsManagement,
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Transactions Management' }
      },
      {
        path: 'analytics/payments',
        name: 'admin-payment-analytics',
        component: PaymentAnalytics,
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Payment Analytics' }
      },
      {
        path: 'chats',
        name: 'admin-chats',
        component: ChatManagement,
        meta: {
          requiresAuth: true,
          requiresAdmin: true,
          title: 'Chat Management'
        }
      },
      {
        path: 'chats/:id',
        name: 'admin-chat-view',
        component: ChatView,
        meta: {
          requiresAuth: true,
          requiresAdmin: true
        }
      },
      {
        path: '/admin/settings',
        name: 'Settings',
        component: () => import('@/views/admin/SettingsPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/admin/payments',
        name: 'admin-payments',
        component: PaymentManagement,
        meta: { requiresAuth: true, requiresAdmin: true }
      },
      {
        path: '/admin/archived-bookings',
        name: 'ArchivedBookings',
        component: ArchivedBookings,
        meta: { requiresAuth: true, requiresAdmin: true }
      }
    ]
  },
  {
    path: '/packages',
    name: 'Packages',
    component: PackagesPage,
    meta: {
      title: 'Event Packages - Razz Rell Events'
    }
  },
  {
    path: '/packages/:id',
    name: 'PackageDetails',
    component: PackageDetailsPage,
    meta: {
      title: 'Package Details - Razz Rell Events'
    }
  },
  {
    path: '/chat',
    name: 'user-chat',
    component: UserChatView,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin/chat',
    name: 'admin-chat',
    component: ChatView,
    meta: { requiresAuth: true, requiresAdmin: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

router.beforeEach((to, from, next) => {
  const { isAuthenticated, isAdmin } = useAuth();
  const userRole = localStorage.getItem('user_role');

  // Check if the route requires authentication
  if (to.meta.requiresAuth && !isAuthenticated.value) {
    next({ 
      name: 'login', 
      query: { redirect: to.fullPath } 
    });
    return;
  }

  // Check if the route requires admin privileges
  if (to.meta.requiresAdmin && userRole !== 'admin' && userRole !== 'staff') {
    next('/');
    return;
  }

  // Prevent authenticated users from accessing login/register pages
  if (to.meta.guestOnly && isAuthenticated.value) {
    next(from.path);
    return;
  }

  next();
});



export default router; 