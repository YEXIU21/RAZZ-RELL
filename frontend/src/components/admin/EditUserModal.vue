<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit User</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="edit-form">
        <div class="form-section">
          <h3>Basic Information</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="firstName">First Name</label>
              <input
                type="text"
                id="firstName"
                v-model="formData.firstName"
                required
                placeholder="Enter first name"
              />
            </div>

            <div class="form-group">
              <label for="lastName">Last Name</label>
              <input
                type="text"
                id="lastName"
                v-model="formData.lastName"
                required
                placeholder="Enter last name"
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email">Email</label>
              <input
                type="email"
                id="email"
                v-model="formData.email"
                required
                placeholder="Enter email address"
              />
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <input
                type="tel"
                id="phone"
                v-model="formData.phone"
                required
                placeholder="Enter phone number"
              />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Account Settings</h3>
          
          <div class="form-row">
            <div class="form-group" v-if="formData.role !== 'user'">
              <label for="role">Role</label>
              <select id="role" v-model="formData.role" required disabled>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" v-model="formData.status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="password-section">
            <div class="section-header">
              <h4>Change Password</h4>
              <button 
                type="button" 
                class="toggle-btn"
                @click="showPasswordFields = !showPasswordFields"
              >
                {{ showPasswordFields ? 'Cancel' : 'Change Password' }}
              </button>
            </div>

            <div v-if="showPasswordFields" class="form-row">
              <div class="form-group">
                <label for="password">New Password</label>
                <div class="password-input">
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    v-model="formData.password"
                    placeholder="Enter new password"
                  />
                  <button 
                    type="button"
                    class="toggle-password"
                    @click="showPassword = !showPassword"
                  >
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-input">
                  <input
                    :type="showConfirmPassword ? 'text' : 'password'"
                    id="confirmPassword"
                    v-model="formData.confirmPassword"
                    placeholder="Confirm new password"
                  />
                  <button 
                    type="button"
                    class="toggle-password"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>
            </div>

            <div v-if="passwordError" class="error-message">
              {{ passwordError }}
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting || !!passwordError">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update']);
const { token } = useAuth();
const currentUserRole = localStorage.getItem('user_role');

const isSubmitting = ref(false);
const showPasswordFields = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const imagePreview = ref(null);
const passwordError = ref('');

const formData = reactive({
  id: props.user.id,
  firstName: props.user.first_name,
  lastName: props.user.last_name,
  email: props.user.email,
  phone: props.user.phone_number,
  email_verified_at: props.user.email_verified_at,
  role: props.user.role,
  status: props.user.status,
  flag: props.user.flag,
  password: '',
  confirmPassword: ''
});

watch([() => formData.password, () => formData.confirmPassword], ([password, confirm]) => {
  if (!password && !confirm) {
    passwordError.value = '';
    return;
  }
  
  if (password && password.length < 8) {
    passwordError.value = 'Password must be at least 8 characters long';
  } else if (password && confirm && password !== confirm) {
    passwordError.value = 'Passwords do not match';
  } else {
    passwordError.value = '';
  }
});

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    formData.avatar = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/update-user`, {
      id: props.user.id,
      name: formData.firstName + ' ' + formData.lastName,
      email: formData.email,
      role: formData.role,
      status: formData.status
    }, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      }
    });

    if (response.data.status === 'success') {
      await Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'User updated successfully'
      });
      emit('update');
      emit('close');
    } else {
      throw new Error(response.data.message || 'Failed to update user');
    }
  } catch (error) {
    console.error('Error updating user:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to update user'
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--modal-background, #fff);
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-color);
  cursor: pointer;
  padding: 0.5rem;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.form-section h3 {
  font-size: 1.2rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.section-header h4 {
  font-size: 1.1rem;
  color: var(--text-color);
}

.toggle-btn {
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background, #fff);
  color: var(--text-color);
}

textarea {
  resize: vertical;
}

.password-input {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.25rem;
}

.error-message {
  color: var(--danger-color, #dc3545);
  font-size: 0.9rem;
  margin-top: 0.5rem;
}

.image-upload {
  position: relative;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-preview {
  width: 100%;
  height: 200px;
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  overflow: hidden;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.upload-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-muted);
}

.upload-placeholder i {
  font-size: 3rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--secondary-color, #6c757d);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
  }
}
</style> 