<template>
  <MainLayout>
    <div class="settings-page">
      <div class="content-container">
        <div class="page-header">
          <h1>Profile Settings</h1>
          <button type="button" class="save-btn" @click="saveChanges">
            <i class="fas fa-save"></i>
            Save Changes
          </button>
        </div>

        <div class="settings-grid">
          <!-- Personal Information -->
          <div class="settings-card">
            <div class="card-header">
              <h2>Personal Information</h2>
            </div>

            <!-- Profile Picture Section -->
            <div class="profile-picture-section">
              <div class="profile-picture-container">
                <img 
                  :src="values.avatar ? (values.avatar.startsWith('images/') ? `/src/assets/${values.avatar}` : `${import.meta.env.VITE_API_URL}/storage/${values.avatar}`) : ''"
                  alt="Profile Picture"
                  class="profile-picture"
                />
                <div class="profile-picture-overlay">
                  <label for="profile-image" class="upload-button">
                    <i class="fas fa-camera"></i>
                    <span>Change Photo</span>
                  </label>
                  <input
                    type="file"
                    id="profile-image"
                    accept="image/*"
                    @change="handleImageUpload"
                    class="hidden-input"
                  />
                </div>
              </div>
              <div class="profile-picture-info">
                <p class="upload-hint">Allowed formats: JPG, PNG. Max size: 2MB</p>
                <button 
                  v-if="values.avatar"
                  @click="removeProfilePicture" 
                  class="remove-photo-btn"
                >
                  <i class="fas fa-trash"></i>
                  Remove Photo
                </button>
              </div>
            </div>

            <!-- Form Fields -->
            <div class="form-row">
              <div class="form-group">
                <label for="firstName">First Name</label>
                <div class="input-group">
                  <i class="fas fa-user"></i>
                  <input
                    type="text"
                    id="firstName"
                    v-model="values.firstName"
                    @blur="handleBlur('firstName')"
                    :class="{ 'error': getFieldError('firstName') }"
                    placeholder="Enter first name"
                  />
                </div>
                <span class="error-message" v-if="getFieldError('firstName')">
                  {{ getFieldError('firstName') }}
                </span>
              </div>

              <div class="form-group">
                <label for="lastName">Last Name</label>
                <div class="input-group">
                  <i class="fas fa-user"></i>
                  <input
                    type="text"
                    id="lastName"
                    v-model="values.lastName"
                    @blur="handleBlur('lastName')"
                    :class="{ 'error': getFieldError('lastName') }"
                    placeholder="Enter last name"
                  />
                </div>
                <span class="error-message" v-if="getFieldError('lastName')">
                  {{ getFieldError('lastName') }}
                </span>
              </div>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input
                  type="email"
                  id="email"
                  v-model="values.email"
                  @blur="handleBlur('email')"
                  :class="{ 'error': getFieldError('email') }"
                  placeholder="Enter email"
                />
              </div>
              <span class="error-message" v-if="getFieldError('email')">
                {{ getFieldError('email') }}
              </span>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <i class="fas fa-phone"></i>
                <input
                  type="tel"
                  id="phone"
                  v-model="values.phone"
                  @blur="handleBlur('phone')"
                  :class="{ 'error': getFieldError('phone') }"
                  placeholder="Enter phone number"
                />
              </div>
              <span class="error-message" v-if="getFieldError('phone')">
                {{ getFieldError('phone') }}
              </span>
            </div>
          </div>

          <!-- Preferences Card -->
          <div class="settings-card">
            <div class="card-header">
              <h2>Preferences</h2>
            </div>

            <div class="preferences-card">
              <!-- Theme Section -->
              <div class="preferences-section">
                <div class="section-title">Theme</div>
                <div class="theme-toggle">
                  <button
                    class="theme-btn"
                    :class="{ active: !isDarkMode }"
                    @click="setTheme('light')"
                  >
                    <i class="fas fa-sun"></i>
                    Light
                  </button>
                  <button
                    class="theme-btn"
                    :class="{ active: isDarkMode }"
                    @click="setTheme('dark')"
                  >
                    <i class="fas fa-moon"></i>
                    Dark
                  </button>
                </div>
              </div>

              <!-- Email Notifications Section -->
              <div class="preferences-section">
                <div class="section-title">Email Notifications</div>
                <div class="notification-settings">
                  <div class="checkbox-group">
                    <label class="checkbox-label">
                      <input
                        type="checkbox"
                        v-model="values.notifications.bookingUpdates"
                      />
                      Booking updates
                    </label>
                    <label class="checkbox-label">
                      <input
                        type="checkbox"
                        v-model="values.notifications.reminders"
                      />
                      Event reminders
                    </label>
                    <label class="checkbox-label">
                      <input
                        type="checkbox"
                        v-model="values.notifications.promotions"
                      />
                      Promotions and news
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Security -->
          <div class="settings-card">
            <div class="card-header">
              <h2>Security</h2>
            </div>

            <div class="security-settings">
              <div class="security-item">
                <div class="security-info">
                  <h3>{{ showChangePassword ? 'Security' : 'Password' }}</h3>
                  <p>{{ showChangePassword ? '' : 'Last Changed Password ' + lastChangePassword }} </p>
                </div>
                <button class="action-btn" @click="showChangePassword ? showChangePassword = false : showChangePassword = true">
                  {{ showChangePassword ? 'Go to Change Password' : 'Go to Security' }}
                </button>
              </div>

              <div class="security-item" v-if="showChangePassword == false">
                <div class="security-info">
                  <h3>New Password</h3>
                </div><br>
                <div class="input-group">
                  <i class="fas fa-lock"></i>
                  <input
                    type="password"
                    id="password"
                    v-model="values.password"
                    @blur="handleBlur('password')"
                    :class="{ 'error': getFieldError('password') }"
                    placeholder="Enter new password"
                  />
                </div>
                <span class="error-message" v-if="getFieldError('password')">
                  {{ getFieldError('password') }}
                </span>
              </div>

              <div class="security-item" v-if="showChangePassword == false">
                <div class="security-info">
                  <h3>Confirm Password</h3>
                </div><br>
                <div class="input-group">
                  <i class="fas fa-lock"></i>
                  <input
                    type="password"
                    id="cpassword"
                    v-model="values.cpassword"
                    @blur="handleBlur('cpassword')"
                    :class="{ 'error': getFieldError('cpassword') }"
                    placeholder="Enter new password"
                  />
                </div>
                <span class="error-message" v-if="getFieldError('password')">
                  {{ getFieldError('password') }}
                </span>
              </div>

              <div class="security-item" v-if="showChangePassword == true">
                <div class="security-info">
                  <h3>Two-Factor Authentication</h3>
                  <p>{{ values.twoFactorEnabled ? 'Enabled' : 'Disabled' }}</p>
                </div>
                <button
                  class="action-btn"
                  :class="{ 'btn-danger': values.twoFactorEnabled }"
                  @click="toggleTwoFactor"
                >
                  {{ values.twoFactorEnabled ? 'Disable' : 'Enable' }}
                </button>
              </div>

              <div class="security-item" v-if="showChangePassword == true">
                <div class="security-info">
                  <h3>Active Sessions</h3>
                  <p>{{ activeSessions.length }} devices</p>
                </div>
                <button class="action-btn btn-danger" @click="showActiveSessions">
                  Manage Sessionss
                </button>
              </div>
            </div>
          </div>

          <!-- Account -->
          <div class="settings-card">
            <div class="card-header">
              <h2>Account</h2>
            </div>

            <div class="account-actions">
              <div class="action-item">
                <div class="action-info">
                  <h3>Export Data</h3>
                  <p>Download a copy of system data and logs</p>
                </div>
                <button class="action-btn" @click="exportData">
                  Export Data
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <!-- <ChangePassword
      v-if="showChangePassword"
      @close="showChangePassword = false"
      @success="handlePasswordChanged"
    /> -->

    <!-- Active Sessions Modal -->
    <ActiveSessionsModal
      v-if="showSessionsModal"
      :sessions="activeSessions"
      @close="showSessionsModal = false"
      @revoke="revokeSession"
    />
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, resolveDynamicComponent } from 'vue';
import { useRouter } from 'vue-router';
import MainLayout from '@/components/ui/MainLayout.vue';
import ChangePassword from '@/components/user/ChangePassword.vue';
import ActiveSessionsModal from '@/components/user/ActiveSessionsModal.vue';
import { useAuth } from '@/composables/useAuth';
import { useValidation } from '@/composables/useValidation';
import { useNotifications } from '@/composables/useNotifications';
import { useLoading } from '@/composables/useLoading';
import { useConfirmation } from '@/composables/useConfirmation';
import { useTheme } from '@/composables/useTheme';
import { useApi } from '@/composables/useApi';
import axios from 'axios';
import Swal from 'sweetalert2'; // Import Swal if not already imported
import { useToast } from '@/composables/useToast.js';

const router = useRouter();
// const userInfo = localStorage.getItem('user_info');
const { user, updateProfile, logout } = useAuth();
const { success, error: showError } = useNotifications();
const { isLoading, withLoading } = useLoading();
const { confirm } = useConfirmation();
const { currentTheme, setTheme, isDarkMode } = useTheme();
const { token } = useAuth();
const { users } = useApi();
const { showToast } = useToast();

const showChangePassword = ref(false);
const showSessionsModal = ref(false);
const activeSessions = ref([]);
const lastChangePassword = ref(null);

const {
  values,
  validateAll,
  handleBlur,
  isValid,
  getFieldError,
} = useValidation({
  id: "",
  firstName: "",
  lastName: "",
  email: "",
  phone: "",
  email_verified_at: "",
  role: "",
  status: "",
  flag: "",
  password: '',
  confirmPassword: '',
  cpassword: '',
  notifications: {
    bookingUpdates: true,
    reminders: true,
    promotions: false,
  },
  twoFactorEnabled: false,
});

const validationRules = {
  firstName: ['required', ['minLength', 2]],
  lastName: ['required', ['minLength', 2]],
  email: ['required', 'email'],
  phone: ['required', 'phone'],
};

const lastPasswordChange = computed(() => {
  if (!user.value?.lastPasswordChange) return 'Never';
  return new Date(user.value.lastPasswordChange).toLocaleDateString();
});

const defaultProfileImage = '@/assets/default-profile.png';
const profileImagePreview = ref(null);
const profileImageFile = ref(null);
const image = ref(null);
const imagePreview = ref(null);

const handleImageUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Check file type
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    await Swal.fire({
      icon: 'error',
      title: 'Invalid file type',
      text: 'Please select a JPG or PNG image',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  // Check file size (2MB limit)
  if (file.size > 2 * 1024 * 1024) {
    await Swal.fire({
      icon: 'error',
      title: 'File too large',
      text: 'Please select an image under 2MB',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  image.value = file;
  uploadImage();
};

const uploadImage = async () => {
  if (!image.value) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'No image selected!',
      confirmButtonColor: '#3085d6'
    });
    return;
  }

  try {
    // Show loading state
    Swal.fire({
      title: 'Uploading...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    const formData = new FormData();
    formData.append('avatar', image.value);
    formData.append('id', values.value.id);

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/change-avatar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.data.status === 200) {
      localStorage.setItem('user_info', JSON.stringify(response.data.data));
      
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Profile photo updated successfully',
        timer: 1500,
        showConfirmButton: false
      });

      window.location.reload();
    }
  } catch (error) {
    console.error("Error uploading the image:", error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'Failed to upload profile photo',
      confirmButtonColor: '#3085d6'
    });
  }
};

const removeProfilePicture = async () => {
  try {
    // Get current user info
    const userInfo = JSON.parse(localStorage.getItem('user_info'));
    if (!userInfo) {
      throw new Error('User information not found');
    }

    // Show loading state
    Swal.fire({
      title: 'Removing...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Create form data with current user info
    const formData = new FormData();
    formData.append('id', userInfo.id);
    formData.append('first_name', userInfo.first_name || '');
    formData.append('last_name', userInfo.last_name || '');
    formData.append('email', userInfo.email || '');
    formData.append('phone_number', userInfo.phone_number || '');
    formData.append('role', userInfo.role || 'user');
    formData.append('status', userInfo.status || 'active');
    formData.append('flag', userInfo.flag || '0');
    formData.append('remove_profile_picture', 'true');

    // Make the API call
    // const response = await axios.post(
    //   'http://127.0.0.1:8000/api/update-user-info',
    //   formData,
    //   {
    //     headers: {
    //       'Authorization': `Bearer ${token.value}`,
    //       'Content-Type': 'multipart/form-data',
    //       'Accept': 'application/json'
    //     }
    //   }
    // );

    if (response.data) {
      // Update local storage
      userInfo.profile_picture = null;
      localStorage.setItem('user_info', JSON.stringify(userInfo));

      // Update UI
      profileImagePreview.value = defaultProfileImage;
      const input = document.getElementById('profile-image');
      if (input) input.value = '';

      // Show success message
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Profile photo removed successfully',
        timer: 1500,
        showConfirmButton: false
      });

      // Refresh the page to show updated data
      window.location.reload();
    }
  } catch (error) {
    console.error('Error removing profile picture:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to remove profile picture. Please try again.'
    });
  }
};

const saveChanges = async () => {
  try {
    // Show loading state
    Swal.fire({
      title: 'Saving Changes...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Get the token
    const token = localStorage.getItem('auth_token');
    if (!token) {
      throw new Error('Authentication token not found');
    }
    
    const userInfo = JSON.parse(localStorage.getItem('user_info'));

    // Create form data
    const formData = new FormData();
    
    // Add user data
    formData.append('id', userInfo.id);
    formData.append('firstName', userInfo.first_name || '');
    formData.append('lastName', userInfo.last_name || '');
    formData.append('email', userInfo.email || '');
    formData.append('phone', userInfo.phone_number || '');
    formData.append('role', userInfo.role || 'user');
    formData.append('status', userInfo.status || 'active');
    formData.append('flag', userInfo.flag || '0');

    // Add password if it exists and matches confirmation
    if (values.value.password && values.value.password === values.value.cpassword) {
      formData.append('password', values.value.password);
    }

    // Make the API call
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/update-user-info`,
      formData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
          'Accept': 'application/json'
        }
      }
    );

    // Handle success
    if (response.data) {
      // Update local storage
      const updatedUser = {
        ...JSON.parse(localStorage.getItem('user_info')),
        first_name: values.value.firstName,
        last_name: values.value.lastName,
        email: values.value.email,
        phone_number: values.value.phone
      };

      if (response.data.user?.profile_picture) {
        updatedUser.profile_picture = response.data.user.profile_picture;
      }

      localStorage.setItem('user_info', JSON.stringify(updatedUser));

      // Show success message
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Your profile has been updated successfully.',
        confirmButtonText: 'OK'
      });

      // Refresh the page to show updated data
      window.location.reload();
    }
  } catch (error) {
    console.error('Error saving changes:', error);
    
    // Show error message
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to save changes. Please try again.',
      confirmButtonText: 'OK'
    });
  }
};

const toggleTwoFactor = async () => {
  try {
    const confirmed = await confirm({
      title: values.value.twoFactorEnabled ? 'Disable 2FA' : 'Enable 2FA',
      message: values.value.twoFactorEnabled
        ? 'Are you sure you want to disable two-factor authentication? This will make your account less secure.'
        : 'Enable two-factor authentication to add an extra layer of security to your account.',
      confirmText: values.value.twoFactorEnabled ? 'Disable' : 'Enable',
      type: values.value.twoFactorEnabled ? 'danger' : 'warning',
    });

    if (confirmed) {
      await withLoading('toggle-2fa', () =>
        users.toggleTwoFactor(!values.value.twoFactorEnabled)
      );

      values.value.twoFactorEnabled = !values.value.twoFactorEnabled;
      success(
        `Two-factor authentication has been ${
          values.value.twoFactorEnabled ? 'enabled' : 'disabled'
        }`
      );
    }
  } catch (err) {
    showError('Failed to update two-factor authentication');
  }
};

const showActiveSessions = async () => {
  try {
    // const sessions = await withLoading('fetch-sessions', () =>
    //   users.getSessions()
    // );
    // activeSessions.value = sessions;
    // showSessionsModal.value = true;
  } catch (err) {
    showError('Failed to fetch active sessions');
  }
};

const revokeSession = async (sessionId) => {
  try {
    await withLoading(`revoke-session-${sessionId}`, () =>
      users.revokeSession(sessionId)
    );
    activeSessions.value = activeSessions.value.filter(
      (session) => session.id !== sessionId
    );
    success('Session revoked successfully');
  } catch (err) {
    showError('Failed to revoke session');
  }
};

const exportData = async () => {
  try {
    const data = await withLoading('export-data', () => users.exportData());
    const blob = new Blob([JSON.stringify(data, null, 2)], {
      type: 'application/json',
    });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'my-data.json';
    a.click();
    window.URL.revokeObjectURL(url);
    success('Data exported successfully');
  } catch (err) {
    showError('Failed to export data');
  }
};

const confirmDeleteAccount = async () => {
  try {
    const confirmed = await confirm({
      title: 'Delete Account',
      message:
        'Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently deleted.',
      confirmText: 'Delete Account',
      type: 'danger',
    });

    if (confirmed) {
      await withLoading('delete-account', () => users.deleteAccount());
      success('Account deleted successfully');
      router.push('/login');
    }
  } catch (err) {
    showError('Failed to delete account');
  }
};

const handlePasswordChanged = () => {
  showChangePassword.value = false;
  success('Password changed successfully');
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const takeUserInfo = () => {
  const userInfo = localStorage.getItem('user_info');
  if (!userInfo) {
    router.push('/login');
    return;
  }

  const user = JSON.parse(userInfo);
  values.value.id = user.id;
  values.value.firstName = user.first_name;
  values.value.lastName = user.last_name;
  values.value.email = user.email;
  values.value.phone = user.phone_number;
  values.value.avatar = user.avatar;
  
  // Set profile picture
  if (user.profile_picture) {
    profileImagePreview.value = user.profile_picture.startsWith('http') 
      ? user.profile_picture 
      : `${import.meta.env.VITE_API_URL}/storage/${user.profile_picture}`;
  } else {
    profileImagePreview.value = defaultProfileImage;
  }
};

onMounted(async () => {
  takeUserInfo();
  // try {
  //   const sessions = await users.getSessions();
  //   activeSessions.value = sessions;
  // } catch (err) {
  //   console.error('Failed to fetch initial sessions:', err);
  // }
});
</script>

<style scoped>
.settings-page {
  padding: 2rem;
  max-width: 1600px;
  margin: 0 auto;
  width: 100%;
  background: var(--background-color);
}

.content-container {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 0;
}

.page-header h1 {
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.save-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.save-btn:hover {
  background: var(--primary-hover);
}

.settings-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 2rem;
  padding: 1.5rem;
  width: 100%;
}

.settings-card {
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  overflow: hidden;
  padding-bottom: 1.5rem;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--hover-background);
  margin-bottom: 1.5rem;
}

.card-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  padding: 1.5rem;
  width: 100%;
}

.form-group {
  margin-bottom: 1.75rem;
  width: 100%;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.input-group {
  position: relative;
  width: 100%;
}

.input-group i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
  font-size: 1rem;
  z-index: 1;
}

.input-group input {
  width: 100%;
  height: 42px;
  padding: 0 1rem 0 2.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  color: var(--text-color);
  background: var(--card-background);
  transition: all 0.2s ease;
  box-shadow: var(--shadow-sm);
}

.input-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px var(--primary-light);
}

.input-group input::placeholder {
  color: var(--text-secondary);
}

/* Single column form groups (email, phone) */
.form-group:not(.form-row .form-group) {
  padding: 0 1.5rem;
  max-width: 100%;
}

/* Security section input styles */
.security-settings {
  padding: 1.5rem;
}

.security-item {
  padding: 1.25rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  background: var(--card-background);
}

.security-item .input-group {
  margin-top: 0.75rem;
}

.security-item .input-group input {
  padding-right: 1rem;
}

.security-info h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 0.25rem 0;
}

.security-info p {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

/* Error states */
.input-group input.error {
  border-color: #dc2626;
}

.error-message {
  font-size: 0.875rem;
  color: var(--error-color);
  margin-top: 0.5rem;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
    padding: 1rem;
    gap: 1rem;
  }

  .form-group:not(.form-row .form-group) {
    padding: 0 1rem;
  }

  .security-settings {
    padding: 1rem;
  }

  .input-group input {
    height: 40px;
  }
}

.profile-picture-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem 1.5rem;
  text-align: center;
}

.profile-picture-container {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  margin-bottom: 1rem;
  box-shadow: var(--shadow-sm);
  position: relative;
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-picture-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  max-width: 300px;
}

.upload-hint {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.remove-photo-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: var(--card-background);
  border: 1px solid var(--error-color);
  border-radius: 8px;
  color: var(--error-color);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  width: auto;
  min-width: 140px;
}

.remove-photo-btn:hover {
  background: var(--error-color);
  color: white;
}

.profile-picture-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.6);
  padding: 0.75rem;
  transform: translateY(100%);
  transition: transform 0.3s ease;
}

.profile-picture-container:hover .profile-picture-overlay {
  transform: translateY(0);
}

.upload-button {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: white;
  cursor: pointer;
  font-size: 0.875rem;
}

.upload-button i {
  font-size: 1rem;
}

.hidden-input {
  display: none;
}

@media (max-width: 768px) {
  .profile-picture-section {
    padding: 1.5rem 1rem;
  }

  .profile-picture-container {
    width: 100px;
    height: 100px;
  }

  .profile-picture-info {
    max-width: 250px;
  }

  .remove-photo-btn {
    width: 100%;
  }
}

.security-settings,
.account-actions {
  padding: 1.5rem;
}

.security-item,
.action-item {
  padding: 1.25rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  background: var(--card-background);
}

.security-info h3,
.action-info h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 0.25rem 0;
}

.security-info p,
.action-info p {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
}

.action-btn {
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:not(.btn-danger) {
  background: var(--primary-color);
  color: white;
  border: none;
}

.action-btn:not(.btn-danger):hover {
  background: var(--primary-hover);
}

.btn-danger {
  background: transparent;
  border: 1px solid var(--error-color);
  color: var(--error-color);
}

.btn-danger:hover {
  background: var(--error-color);
  color: white;
}

.theme-toggle {
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
}

.theme-btn {
  flex: 1;
  height: 45px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.95rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.theme-btn:hover:not(.active) {
  background: var(--hover-background);
}

.notification-settings {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.notification-title {
  font-size: 0.95rem;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  padding: 0.625rem;
  color: var(--text-color);
  font-size: 0.95rem;
  cursor: pointer;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.checkbox-label:hover {
  background-color: var(--hover-background);
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-right: 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: 4px;
  cursor: pointer;
  accent-color: var(--primary-color);
}

/* Preferences section styles */
.preferences-card {
  padding: 1.5rem;
}

.preferences-section {
  margin-bottom: 2rem;
}

.preferences-section:last-child {
  margin-bottom: 0;
}

.section-title {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 1rem;
}

/* Theme toggle section */
.theme-toggle {
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.theme-btn {
  flex: 1;
  height: 45px;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.95rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.theme-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.theme-btn:hover:not(.active) {
  background: var(--hover-background);
}

/* Input placeholder color in dark mode */
:root[data-theme="dark"] .input-group input::placeholder {
  color: var(--text-secondary);
  opacity: 0.7;
}

/* Ensure text visibility in dark mode */
:root[data-theme="dark"] {
  .security-info h3,
  .action-info h3,
  .section-title,
  .checkbox-label,
  .form-group label,
  .page-header h1,
  .card-header h2 {
    color: var(--text-color);
  }

  .security-info p,
  .action-info p,
  .upload-hint {
    color: var(--text-secondary);
  }

  .input-group input {
    color: var(--text-color);
    background: var(--card-background);
  }
}
</style> 