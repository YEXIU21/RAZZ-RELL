<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add New User</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="add-form">
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
                type="text"
                id="phone"
                v-model="formData.phone"
                @input="handlePhoneInput"
                required
                placeholder="Enter phone number (e.g., 09123456789)"
                maxlength="11"
              />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Account Settings</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="role">Role</label>
              <select id="role" v-model="formData.role" required>
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
            <div class="form-row">
              <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input">
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    id="password"
                    v-model="formData.password"
                    required
                    placeholder="Enter password"
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
                    required
                    placeholder="Confirm password"
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
            {{ isSubmitting ? 'Creating...' : 'Create User' }}
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

const emit = defineEmits(['close', 'update']);
const { token } = useAuth();

const isSubmitting = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordError = ref('');

const formData = reactive({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  role: 'staff', // Default to staff
  status: 'active', // Default to active
  password: '',
  confirmPassword: ''
});

// Password validation
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

const validateForm = () => {
  if (!formData.firstName.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please enter a first name'
    });
    return false;
  }

  if (!formData.lastName.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please enter a last name'
    });
    return false;
  }

  if (!formData.email.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please enter an email address'
    });
    return false;
  }

  if (!formData.phone.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please enter a phone number'
    });
    return false;
  }

  if (!formData.password) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please enter a password'
    });
    return false;
  }

  if (formData.password !== formData.confirmPassword) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Passwords do not match'
    });
    return false;
  }

  return true;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    isSubmitting.value = true;

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/add-user`, {
      first_name: formData.firstName,
      last_name: formData.lastName,
      email: formData.email,
      phone_number: formData.phone,
      role: formData.role,
      status: formData.status,
      password: formData.password,
      password_confirmation: formData.confirmPassword
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
        text: 'User added successfully'
      });
      emit('update');
      emit('close');
    } else {
      throw new Error(response.data.message || 'Failed to add user');
    }
  } catch (error) {
    console.error('Error adding user:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to add user'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handlePhoneInput = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  
  if (value.length > 0 && value[0] !== '0') {
    value = '0' + value;
  }
  
  if (value.length > 11) {
    value = value.slice(0, 11);
  }
  
  formData.phone = value;
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

.add-form {
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

input, select {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background, #fff);
  color: var(--text-color);
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