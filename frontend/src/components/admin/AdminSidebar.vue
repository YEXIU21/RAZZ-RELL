<template>
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <h2>{{ user?.role === 'staff' ? 'Staff Panel' : 'Admin Panel' }}</h2>
    </div>
    
    <nav class="sidebar-nav">
      <router-link 
        v-for="item in navigationItems" 
        :key="item.path"
        :to="item.path"
        class="nav-item"
        :class="{ active: isActive(item.path) }"
      >
        <i :class="item.icon"></i>
        <span>{{ item.name }}</span>
        <span v-if="item.badge" class="badge">{{ item.badge }}</span>
      </router-link>
      <router-link 
        to="/admin/archived-bookings" 
        class="sidebar-item"
        active-class="active"
      >
        <!-- <i class="fas fa-archive"></i>
        <span>Archived Bookings</span> -->
      </router-link>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info">
        <img 
          :src="userAvatar" 
          alt="User Avatar" 
          class="user-avatar"
        />
        <div>
          <p class="user-name" style="text-transform: capitalize;"> {{ user?.last_name }}, {{ user?.first_name }}</p>
          <p class="user-role" style="text-transform: capitalize;">{{ user?.role || 'Administrator' }}</p>
        </div>
      </div>
      <button @click="handleLogout" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        
        Logout
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import axios from 'axios';

const route = useRoute()
const router = useRouter()
const { logout } = useAuth();
const user = JSON.parse(localStorage.getItem('user_info'));
const token = ref(localStorage.getItem('token'));
const stats = ref({
  totalBookings: 0,
});

const defaultAvatar = '/src/assets/logo.png';

const handleLogout = async () => {
  await logout();
  router.push('/login');
};

const navigationItems = computed(() => {
  const userRole = localStorage.getItem('user_role');
  
  if (userRole === 'admin') {
    return [
      {
        name: 'Dashboard',
        path: '/admin',
        icon: 'fas fa-home'
      },
      {
        name: 'Bookings', 
        path: '/admin/bookings',
        icon: 'fas fa-calendar-alt',
        badge: stats.value.totalBookings
      },
      {
        name: 'Archived', 
        path: '/admin/archived-bookings',
        icon: 'fas fa-calendar-alt',
        badge: stats.value.totalArchivedBookings
      },
      {
        name: 'Packages',
        path: '/admin/packages',
        icon: 'fas fa-box'
      },
      {
        name: 'User Management',
        path: '/admin/users', 
        icon: 'fas fa-users'
      },
      {
        name: 'Portfolios',
        path: '/admin/portfolios',
        icon: 'fas fa-images'
      },
      {
        name: 'Reports',
        path: '/admin/reports',
        icon: 'fas fa-chart-bar'
      },
      {
        name: 'Chat',
        path: '/admin/chat',
        icon: 'fas fa-comment'
      },
      {
        name: 'Contact Us',
        path: '/admin/contact-us', 
        icon: 'fas fa-envelope'
      },
      {
        name: 'Settings',
        path: '/admin/settings',
        icon: 'fas fa-cog'
      },
    ];
  } else if (userRole === 'staff') {
    return [
      {
        name: 'Dashboard',
        path: '/admin',
        icon: 'fas fa-home'
      },
      {
        name: 'Bookings', 
        path: '/admin/bookings',
        icon: 'fas fa-calendar-alt',
        badge: stats.value.totalBookings
      },
      {
        name: 'Reports',
        path: '/admin/reports',
        icon: 'fas fa-chart-bar'
      },
    ];
  }
  
  return [];
});

const isActive = (path) => {
  return route.path === path || (path !== '/admin' && route.path.startsWith(path + '/'));
};

const fetchBookings = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-bookings`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    });
    
    // Filter out Completed and Cancelled bookings
    const activeBookings = response.data.filter(booking => 
      booking.status.toLowerCase() !== 'completed' && 
      booking.status.toLowerCase() !== 'cancelled'
    );
    
    console.log('Total bookings:', response.data.length);
    console.log('Active bookings:', activeBookings.length);
    console.log('Filtered bookings:', activeBookings);
    
    stats.value.totalBookings = activeBookings.length;
  } catch (error) {
    console.error('Error fetching bookings:', error);
    stats.value.totalBookings = 0;
  }
};

// Set up polling to refresh bookings count every minute
onMounted(async () => {
  await fetchBookings();
  setInterval(fetchBookings, 60000); // Refresh every minute
});

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/default-avatar.jpg';
  if (imagePath.startsWith('http')) return imagePath;
  if (imagePath.startsWith('images/')) return `/src/assets/${imagePath}`;
  return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
};

const userAvatar = computed(() => {
  if (!user.value?.avatar) return '/default-avatar.jpg';
  return getImageUrl(user.value.avatar);
});
</script>

<style scoped>
.admin-sidebar {
  width: 280px;
  background: var(--card-background);
  border-right: 1px solid var(--border-color);
  position: fixed;
  top: 64px; /* Height of navbar */
  left: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  z-index: 50;
  overflow-y: auto;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.sidebar-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  display: flex;
  flex-direction: column;
}

.nav-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: var(--text-color);
  text-decoration: none;
  transition: all 0.2s ease;
  border-radius: 8px;
  margin: 0.25rem 0.75rem;
  font-weight: 500;
}

.nav-item i {
  width: 20px;
  margin-right: 0.75rem;
  font-size: 1.1rem;
}

.nav-item span {
  flex: 1;
}

.nav-item:hover {
  background: var(--hover-background);
  color: var(--text-color);
}

.nav-item.active {
  background: var(--primary-color);
  color: white;
}

.nav-item.active .badge {
  background: white;
  color: var(--primary-color);
}

.badge {
  background: var(--primary-color);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  min-width: 20px;
  height: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  margin-left: auto;
  transition: all 0.2s ease;
}

.nav-item:hover .badge {
  transform: scale(1.1);
  background: var(--primary-hover);
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-color);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.user-info img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.user-name {
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
  font-size: 0.9rem;
}

.user-role {
  color: var(--text-secondary);
  margin: 0;
  font-size: 0.8rem;
}

.logout-btn {
  width: 100%;
  padding: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: var(--hover-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  background: var(--hover-color);
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }

  .admin-sidebar.open {
    transform: translateX(0);
  }
}
</style>