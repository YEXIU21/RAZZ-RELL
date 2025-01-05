<template>
  <div class="users-management">
    
    <main class="management-content">
      <div class="page-header">
        <h1>Users Management</h1>
        <div class="header-actions">
          <button @click="showAddModal = true" class="btn-add">
            <i class="fas fa-user-plus"></i>
            Add New User
          </button>
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search users..."
              @input="handleSearch"
            />
          </div>
          <div class="filters">
            <select v-model="roleFilter">
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="user">User</option>
            </select>
            <select v-model="statusFilter">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="blocked">Blocked</option>
            </select>
          </div>
        </div>
      </div>

      <div class="users-table-container">
        <table class="users-table">
          <thead>
            <tr>
              <th>
                <div class="th-content" @click="sortBy('id')">
                  ID
                  <i class="fas" :class="getSortIcon('id')"></i>
                </div>
              </th>
              <th>User</th>
              <th>
                <div class="th-content" @click="sortBy('role')">
                  Role
                  <i class="fas" :class="getSortIcon('role')"></i>
                </div>
              </th>
              <th>
                <div class="th-content" @click="sortBy('status')">
                  Status
                  <i class="fas" :class="getSortIcon('status')"></i>
                </div>
              </th>
              <th>
                <div class="th-content" @click="sortBy('lastLogin')">
                  Last Login
                  <i class="fas" :class="getSortIcon('lastLogin')"></i>
                </div>
              </th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in sortedUsers" :key="user.id">
              <td>#{{ user.id }}</td>
              <td>
                <div class="user-info">
                  <div>
                    <span class="user-name">{{ user.name }}</span>
                    <span class="user-email">{{ user.email }}</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="role-badge" :class="user.role" style="text-transform: capitalize;">    
                  {{ user.role }}
                </span> 
              </td>
              <td>
                <span class="status-badge" style="text-transform: capitalize;" :class="user.flag === '1' ? 'banned' : user.status">
                  {{ user.flag === '1' ? 'Removed!' : user.status }}
                </span>
              </td>
              <td>
                <div class="last-login">
                  <span class="date">{{ formatDate(user.updated_at) }}</span>
                  <span class="time">{{ formatTime(user.updated_at) }}</span>
                </div>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="editUser(user)" class="btn-action edit" title="Edit User">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    v-if="user.status !== 'blocked'"
                    @click="blockUser(user)" 
                    class="btn-action block"
                    title="Block User"
                  >
                    <i class="fas fa-ban"></i>
                  </button>
                  <button 
                    v-else
                    @click="unblockUser(user)" 
                    class="btn-action unblock"
                    title="Unblock User"
                  >
                    <i class="fas fa-unlock"></i>
                  </button>
                  <button @click="deleteUser(user)" class="btn-action delete" title="Delete User">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination">
        <button 
          :disabled="currentPage === 1" 
          @click="changePage(currentPage - 1)"
          class="page-btn"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button 
          :disabled="currentPage === totalPages" 
          @click="changePage(currentPage + 1)"
          class="page-btn"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </main>

    <!-- Modals -->
    <AddUserModal
      v-if="showAddModal"
      @close="handleAddModalClose"
      @success="handleUserAdded"
    />

    <EditUserModal
      v-if="showEditModal"
      :user="selectedUser"
      @close="showEditModal = false"
      @update="handleUserUpdated"
    />

    <UserDetailsModal
      v-if="showDetailsModal"
      :user="selectedUser"
      @close="showDetailsModal = false"
    />

    <ConfirmationModal
      v-if="showDeleteModal"
      title="Delete User"
      message="Are you sure you want to delete this user? This action cannot be undone."
      type="danger"
      confirmText="Delete"
      @confirm="confirmDeleteUser"
      @close="showDeleteModal = false"
    />

    <ConfirmationModal
      v-if="showBlockModal"
      :title="selectedUser?.status === 'blocked' ? 'Unblock User' : 'Block User'"
      :message="selectedUser?.status === 'blocked' 
        ? 'Are you sure you want to unblock this user?' 
        : 'Are you sure you want to block this user? They will not be able to access their account.'"
      :type="selectedUser?.status === 'blocked' ? 'warning' : 'danger'"
      :confirmText="selectedUser?.status === 'blocked' ? 'Unblock' : 'Block'"
      :userid="selectedUser?.id"
      @confirm="confirmBlockAction"
      @close="showBlockModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuth } from '@/composables/useAuth';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AddUserModal from '@/components/admin/AddUserModal.vue';
import EditUserModal from '@/components/admin/EditUserModal.vue';
import UserDetailsModal from '@/components/admin/UserDetailsModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRoute, useRouter } from 'vue-router';

const { token } = useAuth();

// State
const users = ref([]);
const searchQuery = ref('');
const roleFilter = ref('');
const statusFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const sortField = ref('id');
const sortDirection = ref('desc');
const showAddModal = ref(false);
const showEditModal = ref(false);
const showDetailsModal = ref(false);
const showDeleteModal = ref(false);
const showBlockModal = ref(false);
const selectedUser = ref(null);

const route = useRoute();
const router = useRouter();

// Check for action query parameter on mount
onMounted(() => {
  if (route.query.action === 'add') {
    showAddModal.value = true;
  }
  fetchUsers();
});

// Watch for route query changes
watch(() => route.query.action, (newAction) => {
  if (newAction === 'add') {
    showAddModal.value = true;
  }
});

// Computed
const filteredUsers = computed(() => {
  return users.value.filter(user => {
    const matchesSearch = searchQuery.value === '' || 
      user.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    
    const matchesRole = roleFilter.value === '' || 
      user.role.toLowerCase() === roleFilter.value.toLowerCase();
    
    const matchesStatus = statusFilter.value === '' || 
      user.status.toLowerCase() === statusFilter.value.toLowerCase();
    
    return matchesSearch && matchesRole && matchesStatus;
  });
});

const sortedUsers = computed(() => {
  const sorted = [...filteredUsers.value].sort((a, b) => {
    if (sortField.value === 'lastLogin') {
      return new Date(a[sortField.value]) - new Date(b[sortField.value]);
    }
    if (sortField.value === 'bookingsCount') {
      return a[sortField.value] - b[sortField.value];
    }
    return String(a[sortField.value]).localeCompare(String(b[sortField.value]));
  });
  
  return sortDirection.value === 'desc' ? sorted.reverse() : sorted;
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / itemsPerPage);
});

// Methods
const fetchUsers = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-users`);
    users.value = response.data;
    if (response.ok) {
      users.value = await response.json();
    }
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const handleSearch = () => {
  currentPage.value = 1;
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};

const getSortIcon = (field) => {
  if (sortField.value !== field) return 'fa-sort';
  return sortDirection.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
};

const changePage = (page) => {
  currentPage.value = page;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const openAddUserModal = () => {
  showAddModal.value = true;
};

const viewUser = (user) => {
  selectedUser.value = user;
  showDetailsModal.value = true;
};

const editUser = (user) => {
  selectedUser.value = user;
  showEditModal.value = true;
};

const blockUser = (user) => {
  selectedUser.value = user;
  showBlockModal.value = true;
};

const unblockUser = (user) => {
  selectedUser.value = user;
  showBlockModal.value = true;
};

const deleteUser = (user) => {
  selectedUser.value = user;
  showDeleteModal.value = true;
};

const confirmBlockAction = async () => {
  try {
    const action = selectedUser.value.status === 'blocked' ? 'unblock' : 'block';
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/admin/users/${selectedUser.value.id}/${action}`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    });

    if (response.ok) {
      await fetchUsers();
      showBlockModal.value = false;
    }
  } catch (error) {
    console.error('Error updating user status:', error);
  }
};

const confirmDeleteUser = async () => {
  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/delete-user/${selectedUser.value.id}`);

    if(response.data.status == 200){
      // Show success animation
      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'User and all associated bookings have been deleted.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer)
          toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
      }).then(() => {
        window.location.reload();
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong!',
      });
    }
  } catch (error) {
    console.error('Error deleting user:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to delete user and associated bookings',
    });
  }
};

const handleUserAdded = async () => {
  await fetchUsers();
  showAddModal.value = false;
  // Remove the query parameter
  router.replace({ path: route.path });
};

const handleUserUpdated = async () => {
  await fetchUsers();
  showEditModal.value = false;
};

const handleAddModalClose = () => {
  showAddModal.value = false;
  // Remove the query parameter
  router.replace({ path: route.path });
};
</script>

<style scoped>
.users-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 1.5rem 2rem;
  margin-left: 250px;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.header-actions {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1.5rem;
  align-items: start;
}

.btn-add {
  padding: 0.75rem 1.25rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  white-space: nowrap;
}

.btn-add:hover {
  background: var(--primary-color-dark, #2563eb);
  transform: translateY(-1px);
}

.btn-add i {
  font-size: 0.9rem;
}

.search-bar {
  position: relative;
  width: 100%;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: border-color 0.2s;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  outline: none;
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.filters {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.filters select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  min-width: 140px;
  font-size: 0.95rem;
}

.users-table-container {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.users-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.th-content:hover {
  background: rgba(0, 0, 0, 0.05);
}

th {
  padding: 1rem 1.25rem;
  text-align: left;
  font-weight: 600;
  color: var(--text-color);
  background: var(--table-header-background, rgba(0, 0, 0, 0.02));
  border-bottom: 2px solid var(--border-color);
}

td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-color);
}

tr:last-child td {
  border-bottom: none;
}

tr:hover {
  background: rgba(0, 0, 0, 0.02);
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.user-name {
  font-weight: 500;
  color: var(--text-color);
}

.user-email {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.role-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
}

.role-badge.admin {
  background: #e3f2fd;
  color: #1565c0;
}

.role-badge.staff {
  background: #fff3e0;
  color: #ef6c00;
}

.role-badge.user {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
}

.status-badge.active {
  background: #e8f5e9;
  color: #2e7d32;
}

.status-badge.inactive {
  background: #fafafa;
  color: #757575;
}

.status-badge.blocked {
  background: #ffebee;
  color: #c62828;
}

.status-badge.banned {
  background: #ffebee;
  color: #c62828;
}

.last-login {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.last-login .time {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-action {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, opacity 0.2s;
}

.btn-action.edit {
  background-color: #3882F6;
}

.btn-action.block, 
.btn-action.unblock {
  background-color: #F59E0B;
}

.btn-action.delete {
  background-color: #EF4444;
}

.btn-action:hover {
  transform: translateY(-1px);
  opacity: 0.9;
}

.btn-action:active {
  transform: translateY(0);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  background: var(--card-background);
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:not(:disabled):hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: var(--text-color);
  font-weight: 500;
}

@media (max-width: 1200px) {
  .header-actions {
    grid-template-columns: 1fr;
  }
  
  .filters {
    flex-wrap: wrap;
  }
  
  .filters select {
    flex: 1;
    min-width: 120px;
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .page-header {
    padding: 1rem;
  }

  .header-actions {
    gap: 1rem;
  }

  .btn-add {
    width: 100%;
    justify-content: center;
  }

  .search-bar {
    width: 100%;
  }

  .filters {
    width: 100%;
  }

  .users-table-container {
    overflow-x: auto;
  }

  .users-table {
    min-width: 800px;
  }

  th, td {
    padding: 0.75rem 1rem;
  }

  .action-buttons {
    flex-wrap: wrap;
  }
}
</style> 