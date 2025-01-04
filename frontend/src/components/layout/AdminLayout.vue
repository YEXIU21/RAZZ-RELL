<template>
    <div class="admin-layout">
      <nav class="admin-navbar">
        <div class="navbar-brand">
          <img :src="currentLogo" alt="Razz Rell Events" />
        </div>
      </nav>
      <div class="admin-container">
        <AdminSidebar />
        <main class="admin-content">
          <router-view></router-view>
        </main>
      </div>
    </div>
  </template>
  
  <script setup>
  import AdminSidebar from '@/components/admin/AdminSidebar.vue';
  import { computed } from 'vue';
  import { useTheme } from '@/composables/useTheme';
  
  const { isDarkMode } = useTheme();
  
  const currentLogo = computed(() => {
    return isDarkMode() ? new URL('@/assets/logo2.png', import.meta.url).href : new URL('@/assets/logo1.png', import.meta.url).href;
  });
  
  const userAvatar = computed(() => {
    const user = JSON.parse(localStorage.getItem('user_info'));
    return user?.avatar || '/default-avatar.png';
  });
  </script>
  
  <style scoped>
  .admin-layout {
    min-height: 60vh;
    background: var(--background-color);
  }
  
  .admin-navbar {
    height: 80px;
    background: var(--card-background);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    padding: 0 2rem;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
  }
  
  .navbar-brand {
    display: flex;
    align-items: center;
  }
  
  .navbar-brand img {
    height: 75px;
    width: auto;
    object-fit: contain;
    padding: 5px 0;
  }
  
  .navbar-menu {
    display: flex;
    gap: 2rem;
    margin-left: 4rem;
  }
  
  .navbar-menu a {
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
  }
  
  .navbar-menu a:hover {
    color: var(--text-color);
  }
  
  .navbar-end {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  
  .settings-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0.5rem;
  }
  
  .user-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
  }
  
  .user-menu img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--border-color);
  }
  
  .admin-container {
    display: flex;
    padding-top: 64px; /* Height of navbar */
    min-height: 100vh;
    background: var(--background-color);
  }
  
  .admin-content {
    flex: 1;
    margin-left: 280px; /* Width of the sidebar */
    padding: 2rem;
    overflow-y: auto;
    background: var(--background-color);
  }
  
  @media (max-width: 768px) {
    .navbar-menu {
      display: none;
    }
  
    .admin-content {
      margin-left: 0;
      padding: 1rem;
    }
  }
  </style>