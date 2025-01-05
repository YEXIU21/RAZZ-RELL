<template>
  <nav class="navbar" :class="{ 'scrolled': isScrolled }" v-if="!isAdmin">
    <div class="nav-container">
      <div class="nav-brand">
        <router-link to="/" class="logo">
          <img :src="currentLogo" alt="Razz Rell Events" />
        </router-link>
      </div>

      <div class="nav-toggle" @click="toggleMenu">
        <i :class="isOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
      </div>

      <div class="nav-menu" :class="{ 'active': isOpen }">
        <div class="nav-links">
          <router-link to="/" class="nav-link" @click="closeMenu">Home</router-link>
          <router-link to="/packages" class="nav-link" @click="closeMenu">Packages</router-link>
          <router-link to="/gallery" class="nav-link" @click="closeMenu">Gallery</router-link>
          <router-link to="/about-us" class="nav-link" @click="closeMenu">About Us</router-link>
          <router-link to="/contact" class="nav-link" @click="closeMenu">Contact</router-link>
        </div>

        <div class="nav-auth">
          <button class="theme-toggle" @click="toggleTheme" :title="isDarkMode() ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <i :class="isDarkMode() ? 'fas fa-moon' : 'fas fa-sun'"></i>
          </button>
          <template v-if="userInfo">
            <div class="user-menu" @click="toggleUserMenu" ref="userMenuTrigger">
              <img 
                :src="userAvatar" 
                :alt="userDisplayName" 
                class="user-avatar" 
              />
              <span class="user-name">{{ userDisplayName }}</span>
              <i class="fas fa-chevron-down"></i>

              <div class="user-dropdown" v-show="isUserMenuOpen" ref="userMenu">
                <div class="user-info">
                  <img 
                    :src="userAvatar" 
                    :alt="userDisplayName" 
                    class="dropdown-avatar" 
                  />
                  <div class="user-details">
                    <span class="name">{{ userDisplayName }}</span>
                    <span class="email">{{ userInfo?.email }}</span>
                  </div>
                </div>

                <div class="dropdown-divider"></div>

                <router-link to="/profile" class="dropdown-item" @click="closeUserMenu">
                  <i class="fas fa-user"></i>
                  Profile
                </router-link>

                <router-link to="/bookings" class="dropdown-item" @click="closeUserMenu">
                  <i class="fas fa-calendar-alt"></i>
                  My Bookings
                </router-link>

                <template v-if="isAdmin">
                  <div class="dropdown-divider"></div>
                  
                  <router-link to="/admin/dashboard" class="dropdown-item" @click="closeUserMenu">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                  </router-link>
                </template>

                <div class="dropdown-divider"></div>

                <button class="dropdown-item logout" @click="handleLogout">
                  <i class="fas fa-sign-out-alt"></i>
                  Logout
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="auth-btn login" @click="closeMenu">Login</router-link>
            <router-link to="/register" class="auth-btn register" @click="closeMenu">Register</router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useConfirmation } from '@/composables/useConfirmation';
import { useTheme } from '@/composables/useTheme';
import defaultAvatar from '@/assets/default-profile.png';
import Swal from 'sweetalert2';

const router = useRouter();
const { user, isAuthenticated, logout } = useAuth();
const { confirm } = useConfirmation();
const { toggleTheme, isDarkMode } = useTheme();

const currentLogo = computed(() => {
  return isDarkMode() ? new URL('@/assets/logo2.png', import.meta.url).href : new URL('@/assets/logo1.png', import.meta.url).href;
});

const isOpen = ref(false);
const isScrolled = ref(false);
const isUserMenuOpen = ref(false);
const userMenuTrigger = ref(null);
const userMenu = ref(null);

const isAdmin = computed(() => localStorage.getItem('user_role') === 'admin' || localStorage.getItem('user_role') === 'staff');
const userInfo = computed(() => JSON.parse(localStorage.getItem('user_info')));

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/default-avatar.jpg';
  if (imagePath.startsWith('http')) return imagePath;
  if (imagePath.startsWith('images/')) return `/src/assets/${imagePath}`;
  return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
};

const userAvatar = computed(() => {
  const storedUser = userInfo.value;
  if (!storedUser?.avatar) return '/default-avatar.jpg';
  return getImageUrl(storedUser.avatar);
});

const userDisplayName = computed(() => {
  const storedUser = userInfo.value;
  if (storedUser?.first_name && storedUser?.last_name) {
    return `${storedUser.first_name} ${storedUser.last_name}`;
  }
  return 'User';
});

const toggleMenu = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = 'auto';
  }
};

const closeMenu = () => {
  isOpen.value = false;
  document.body.style.overflow = 'auto';
};

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = () => {
  isUserMenuOpen.value = false;
};

const handleLogout = async () => {
  const result = await Swal.fire({
    title: 'Logout Confirmation',
    text: 'Are you sure you want to logout?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Logout',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33'
  });

  if (result.isConfirmed) {
    await logout();
    closeUserMenu();
    router.push('/login');
  }
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50;
};

const handleClickOutside = (event) => {
  if (userMenuTrigger.value && userMenu.value) {
    if (!userMenuTrigger.value.contains(event.target) && !userMenu.value.contains(event.target)) {
      closeUserMenu();
    }
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: transparent;
  padding: 1.35rem 0;
  transition: all 0.3s ease;
  z-index: 1000;
}

.navbar.scrolled {
  background: var(--card-background);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 0.85rem 0;
  border-bottom: 1px solid var(--border-color);
}

/* Dark mode specific styles */
:root[data-theme="dark"] {
  .navbar.scrolled {
    background: var(--card-background);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }

  .nav-menu {
    background: var(--card-background);
  }

  .user-dropdown {
    background: var(--card-background);
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  .user-details .email {
    color: var(--text-muted);
  }

  @media (max-width: 768px) {
    .nav-menu {
      background: var(--card-background);
    }
  }
}

/* Update mobile menu background */
@media (max-width: 768px) {
  .nav-menu {
    background: var(--card-background);
  }
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-brand .logo img {
  height: 100px;
  width: auto;
  object-fit: contain;
  transition: height 0.3s ease;
}

.navbar.scrolled .nav-brand .logo img {
  height: 85px;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.nav-links {
  display: flex;
  gap: 1.5rem;
}

.nav-link {
  color: var(--text-color);
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s;
  padding: 0.75rem;
}

.nav-link:hover,
.nav-link.router-link-active {
  color: var(--primary-color);
}

.nav-auth {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.auth-btn {
  padding: 0.75rem 1.25rem;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
}

.auth-btn.login {
  color: var(--primary-color);
  border: 1px solid var(--primary-color);
}

.auth-btn.login:hover {
  background: var(--primary-color);
  color: white;
}

.auth-btn.register {
  background: var(--primary-color);
  color: white;
}

.auth-btn.register:hover {
  background: var(--primary-hover);
}

.user-menu {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.user-menu:hover {
  background: var(--hover-color);
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.user-name {
  font-weight: 500;
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 280px;
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  padding: 1rem;
  margin-top: 0.5rem;
  border: 1px solid var(--border-color);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem;
}

.dropdown-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-details .name {
  font-weight: 500;
}

.user-details .email {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.dropdown-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.5rem 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  color: var(--text-color);
  text-decoration: none;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.dropdown-item:hover {
  background: var(--hover-color);
}

.dropdown-item.logout {
  width: 100%;
  text-align: left;
  border: none;
  background: none;
  color: var(--danger-color);
  cursor: pointer;
}

.dropdown-item.logout:hover {
  background: var(--danger-light);
}

.nav-toggle {
  display: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.theme-toggle {
  padding: 0.5rem;
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.theme-toggle:hover {
  background: var(--hover-color);
}

@media (max-width: 768px) {
  .nav-toggle {
    display: block;
  }

  .nav-menu {
    position: fixed;
    top: 70px;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--card-background);
    flex-direction: column;
    padding: 2rem;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    border-top: 1px solid var(--border-color);
  }

  .nav-menu.active {
    transform: translateX(0);
  }

  .nav-links {
    flex-direction: column;
    align-items: center;
  }

  .nav-auth {
    flex-direction: column;
    width: 100%;
  }

  .auth-btn {
    width: 100%;
    text-align: center;
  }

  .user-dropdown {
    position: fixed;
    top: auto;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    border-radius: 12px 12px 0 0;
    margin: 0;
    background: var(--card-background);
    border-top: 1px solid var(--border-color);
    box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  }

  .theme-toggle {
    margin: 0 1rem;
  }
}
</style> 