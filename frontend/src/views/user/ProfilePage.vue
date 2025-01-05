<template>
  <MainLayout>
    <div class="profile-page">
      <div class="page-header">
        <h1>Profile Settings</h1>
        <button type="button" class="save-btn" @click="saveChanges">
          <i class="fas fa-save"></i>
          Save Changes
        </button>
      </div>

      <div class="profile-grid">
        <!-- Personal Information -->
        <div class="profile-card">
          <div class="card-header">
            <h2>Personal Information</h2>
          </div>

          <!-- Profile Picture Section -->
          <div class="profile-picture-section">
            <div class="profile-picture-container">
              <img 
                :src="getAvatarUrl" 
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
                <img :src="imagePreview" alt="Profile Image Preview" v-if="imagePreview" />
              </div>
            </div>
            <div class="profile-picture-info">
              <p class="upload-hint">Allowed formats: JPG, PNG. Max size: 2MB</p>
              <button 
                v-if="profileImagePreview && profileImagePreview !== defaultProfileImage"
                @click="removeProfilePicture" 
                class="remove-photo-btn"
              >
                <i class="fas fa-trash"></i>
                Remove Photo
              </button>
            </div>
          </div>

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

        <!-- Preferences -->
        <div class="profile-card">
          <div class="card-header">
            <h2>Preferences</h2>
          </div>

          <div class="preferences-form">
            <div class="form-group">
              <label>Theme</label>
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

            <div class="form-group">
              <label>Email Notifications</label>
              <div class="notification-settings">
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

        <!-- Security -->
        <div class="profile-card">
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
        <div class="profile-card danger-zone">
          <div class="card-header">
            <h2>Account</h2>
          </div>

          <div class="account-actions">
            <div class="action-item">
              <div class="action-info">
                <h3>Export Data</h3>
                <p>Download a copy of your personal data</p>
              </div>
              <button class="action-btn" @click="exportData">
                Export Data
              </button>
            </div>

            <div class="action-item">
              <div class="action-info">
                <h3>Delete Account</h3>
                <p>Permanently delete your account and all data</p>
              </div>
              <button class="action-btn btn-danger" @click="confirmDeleteAccount">
                Delete Account
              </button>
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

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file && file.type.startsWith('image/')) {
    image.value = file;
    imagePreview.value = URL.createObjectURL(file);
    uploadImage();
  } else {
    alert("Please select a valid image file.");
  }
};

const uploadImage = async () => {
  if (!image.value) {
    alert("No image selected!");
    return;
  }

  const formData = new FormData();
  formData.append('avatar', image.value);
  formData.append('id', values.value.id);

  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/change-avatar`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
      },
    });

    if (response.data.status === 200) {
      const userData = response.data.data;
      userData.avatar_url = response.data.avatar_url;
      localStorage.setItem('user_info', JSON.stringify(userData));
      window.location.reload();
    }
  } catch (error) {
    console.error("Error uploading the image:", error);
    alert("There was an error uploading the image.");
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
  values.value.avatar = user.avatar || 'portfolios/defaultAvatar.png';
  
  // Set profile picture
  if (user.profile_picture) {
    profileImagePreview.value = user.profile_picture.startsWith('http') 
      ? user.profile_picture 
      : `${import.meta.env.VITE_API_URL}/storage/${user.profile_picture}`;
  } else {
    profileImagePreview.value = defaultProfileImage;
  }
};

const getAvatarUrl = computed(() => {
  if (!values.value?.avatar) {
    return '/src/assets/DefaultProfile/defaultAvatar.png';
  }
  const storageUrl = import.meta.env.VITE_STORAGE_URL || import.meta.env.VITE_API_URL;
  return `${storageUrl}/storage/${values.value.avatar}`;
});

const avatarUrl = computed(() => {
  if (user.value?.profile_picture) {
    return `${import.meta.env.VITE_API_URL}/storage/${user.value.profile_picture}`;
  }
  return null;
});

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/default-avatar.jpg';
  if (imagePath.startsWith('http')) return imagePath;
  if (imagePath.startsWith('images/')) return `/src/assets/${imagePath}`;
  return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
};

const userAvatar = computed(() => {
  const user = JSON.parse(localStorage.getItem('user_info') || '{}');
  return getImageUrl(user.avatar || user.profile_picture);
});

const handleAvatarChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
  if (!allowedTypes.includes(file.type)) {
    await Swal.fire({
      icon: 'error',
      title: 'Invalid File Type',
      text: 'Please upload a valid image file (JPEG, PNG, or GIF)'
    });
    return;
  }

  // Validate file size (max 5MB)
  const maxSize = 5 * 1024 * 1024; // 5MB in bytes
  if (file.size > maxSize) {
    await Swal.fire({
      icon: 'error',
      title: 'File Too Large',
      text: 'Please upload an image smaller than 5MB'
    });
    return;
  }

  try {
    isLoading.value = true;
    const formData = new FormData();
    formData.append('avatar', file);

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/change-avatar`, formData, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'multipart/form-data'
      }
    });

    if (response.data.status === 'success') {
      // Update the user info in localStorage
      const updatedUser = {
        ...JSON.parse(localStorage.getItem('user_info')),
        avatar: response.data.avatar
      };
      localStorage.setItem('user_info', JSON.stringify(updatedUser));

      await Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Profile picture updated successfully'
      });

      // Update the preview
      profileImagePreview.value = getImageUrl(response.data.avatar);
    } else {
      throw new Error(response.data.message || 'Failed to update profile picture');
    }
  } catch (error) {
    console.error('Error updating avatar:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to update profile picture'
    });
  } finally {
    isLoading.value = false;
  }
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    isSubmitting.value = true;

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/update-profile`, {
      name: formData.name,
      email: formData.email,
      current_password: formData.current_password,
      new_password: formData.new_password,
      new_password_confirmation: formData.new_password_confirmation
    }, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      }
    });

    if (response.data.status === 'success') {
      // Update the user info in localStorage
      const updatedUser = {
        ...JSON.parse(localStorage.getItem('user_info')),
        name: formData.name,
        email: formData.email
      };
      localStorage.setItem('user_info', JSON.stringify(updatedUser));

      await Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Profile updated successfully'
      });

      // Clear password fields
      formData.current_password = '';
      formData.new_password = '';
      formData.new_password_confirmation = '';
    } else {
      throw new Error(response.data.message || 'Failed to update profile');
    }
  } catch (error) {
    console.error('Error updating profile:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to update profile'
    });
  } finally {
    isSubmitting.value = false;
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
.profile-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 4rem 2rem 2rem 2rem;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2.5rem;
  padding: 1.25rem 0;
  border-bottom: 1px solid var(--border-color);
  position: relative;
  margin-top: 1.25rem;
}

.page-header h1 {
  font-size: 2rem;
  color: var(--text-color);
  margin: 0;
}

.save-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.save-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.save-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.profile-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 2rem;
}

.profile-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.card-header h2 {
  font-size: 1.4rem;
  color: var(--text-color);
  margin: 0;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
  font-size: 0.95rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 1rem;
  color: var(--text-muted);
  font-size: 1rem;
}

.input-group input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 2.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: all 0.2s ease;
}

.input-group input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.1);
}

.security-settings,
.account-actions {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.security-item,
.action-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--background);
}

.security-info,
.action-info {
  flex: 1;
}

.security-info h3,
.action-info h3 {
  font-size: 1.1rem;
  color: var(--text-color);
  margin: 0 0 0.5rem 0;
}

.security-info p,
.action-info p {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin: 0;
}

.action-btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 8px;
  background: var(--primary-color);
  color: white;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  margin-left: 1.5rem;
}

.action-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.btn-danger {
  background: var(--error-color);
}

.btn-danger:hover {
  background: var(--error-hover);
}

.theme-toggle {
  display: flex;
  gap: 1rem;
}

.theme-btn {
  flex: 1;
  padding: 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
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
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 0.5rem 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-color);
  cursor: pointer;
  padding: 0.5rem 0;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  border-radius: 4px;
  border: 2px solid var(--border-color);
  cursor: pointer;
}

@media (max-width: 768px) {
  .profile-page {
    padding: 2rem 1rem;
  }

  .profile-grid {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    margin-top: 2rem;
  }

  .security-item,
  .action-item {
    flex-direction: column;
    gap: 1rem;
    text-align: left;
  }

  .action-btn {
    width: 100%;
    justify-content: center;
  }

  .security-info,
  .action-info {
    width: 100%;
  }
}

/* Profile Picture Styles */
.profile-picture-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.profile-picture-container {
  position: relative;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  margin-bottom: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.profile-picture {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
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
  transition: all 0.2s ease;
}

.upload-button i {
  font-size: 1.2rem;
}

.hidden-input {
  display: none;
}

.profile-picture-info {
  text-align: center;
}

.upload-hint {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-bottom: 0.75rem;
}

.remove-photo-btn {
  padding: 0.5rem 1rem;
  background: var(--error-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.remove-photo-btn:hover {
  background: var(--error-hover);
  transform: translateY(-1px);
}

.remove-photo-btn i {
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .profile-picture-container {
    width: 120px;
    height: 120px;
  }

  .profile-picture-overlay {
    transform: translateY(0);
    background: rgba(0, 0, 0, 0.5);
  }
}
</style> 