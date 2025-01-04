<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <router-link to="/">
            <img :src="currentLogo" alt="Razz Rell Events" class="auth-logo" />
          </router-link>
          <h1>Create Account</h1>
          <p>Join us to start planning your events</p>
        </div>

        <form @submit.prevent="handleSubmit" class="auth-form">
          <div class="form-row">
            <div class="form-group">
              <label for="first_name">First Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="first_name"
                  v-model="values.first_name"
                  @blur="handleBlur('first_name')"
                  :class="{ 'error': getFieldError('first_name') }"
                  placeholder="Enter first name"
                  autocomplete="given-name"
                />
              </div>
              <span class="error-message" v-if="getFieldError('first_name')">
                {{ getFieldError('first_name') }}
              </span>
            </div>

            <div class="form-group">
              <label for="last_name">Last Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" id="last_name" v-model="values.last_name" @blur="handleBlur('last_name')" :class="{ 'error': getFieldError('last_name') }" placeholder="Enter last name" autocomplete="family-name" />
              </div>
              <span class="error-message" v-if="getFieldError('last_name')">
                {{ getFieldError('last_name') }}
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
                placeholder="Enter your email"
                autocomplete="email"
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
                v-model="values.phone_number"
                @blur="handleBlur('phone')"
                :class="{ 'error': getFieldError('phone_number') }"
                placeholder="Enter phone number (11 digits)"
                autocomplete="tel"
                pattern="[0-9]{11}"
                inputmode="numeric"
                maxlength="11"
                @input="values.phone_number = values.phone_number.replace(/\D/g, '')"
              />
            </div>
            <span class="error-message" v-if="getFieldError('phone')">
              {{ getFieldError('phone') }}
            </span>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
              <i class="fas fa-lock"></i>
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="values.password"
                @blur="handleBlur('password')"
                :class="{ 'error': getFieldError('password') }"
                placeholder="Create a password"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
              >
                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <span class="error-message" v-if="getFieldError('password')">
              {{ getFieldError('password') }}
            </span>
            <div class="password-requirements">
              <p class="requirements-title">Password must have:</p>
              <ul>
                <li v-for="(req, index) in passwordRequirements" 
                    :key="index"
                    :class="{ 'met': req.met }">
                  <i class="fas" :class="req.met ? 'fa-check' : 'fa-times'"></i>
                  {{ req.text }}
                </li>
              </ul>
            </div>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="input-group">
              <i class="fas fa-lock"></i>
              <input
                :type="showConfirmPassword ? 'text' : 'password'"
                id="confirmPassword"
                v-model="values.confirmPassword"
                @blur="handleBlur('confirmPassword')"
                :class="{ 'error': getFieldError('confirmPassword') }"
                placeholder="Confirm your password"
                autocomplete="new-password"
              />
              <button type="button" class="toggle-password" @click="showConfirmPassword = !showConfirmPassword" >
                <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <span class="error-message" v-if="getFieldError('confirmPassword')">
              {{ getFieldError('confirmPassword') }}
            </span>
          </div>

          <div class="form-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="values.termsAccepted"
                @blur="handleBlur('termsAccepted')"
                :disabled="!isValid"
              />
              I agree to the <a href="/terms" target="_blank">Terms of Service</a> and
              <a href="/privacy" target="_blank">Privacy Policy</a>
            </label>
            <span class="error-message" v-if="getFieldError('termsAccepted')">
              {{ getFieldError('termsAccepted') }}
            </span>
          </div>

          <button
            type="submit"
            class="submit-btn"
            :disabled="!values.termsAccepted"
          >
            <span>Create Account</span>
          </button>
        </form>

        <div class="auth-footer">
          <p>Already have an account?</p>
          <router-link to="/login" class="login-link">
            Sign in
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useValidation } from '@/composables/useValidation';
import { useNotifications } from '@/composables/useNotifications';
import { useTheme } from '@/composables/useTheme';
import Swal from 'sweetalert2';
// import { useLoading } from '@/composables/useLoading';

const router = useRouter();
const { register } = useAuth();
const { success, error: showError } = useNotifications();
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const {
  values,
  errors,
  touched,
  validate,
  validateAll,
  handleBlur,
  isValid,
  getFieldError,
} = useValidation({
  first_name: '',
  last_name: '',
  email: '',
  phone_number: '',
  password: '',
  confirmPassword: '',
  termsAccepted: false,
});

// Simplified validation rules
const validationRules = {
  first_name: ['required'],
  last_name: ['required'],
  email: ['required', 'email'],
  phone_number: ['required'],
  password: ['required'],
  confirmPassword: ['required'],
  termsAccepted: ['required']
};

const passwordRequirements = ref([
  { text: 'At least 6 characters', met: false },
  { text: 'Contains at least one letter', met: false },
  { text: 'Contains at least one number', met: false }
]);

const updatePasswordRequirements = (password) => {
  passwordRequirements.value[0].met = password.length >= 6;
  passwordRequirements.value[1].met = /[A-Za-z]/.test(password);
  passwordRequirements.value[2].met = /\d/.test(password);
};

watch(() => values.value.password, (newPassword) => {
  updatePasswordRequirements(newPassword || '');
});

const handleSubmit = async () => {
  try {
    // Basic validation check
    if (!values.value.first_name || !values.value.last_name || !values.value.email || 
        !values.value.phone_number || !values.value.password || !values.value.confirmPassword) {
      await Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: 'Please fill in all required fields',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Try Again'
      });
      return;
    }

    // Password validation
    if (values.value.password.length < 6) {
      await Swal.fire({
        icon: 'error',
        title: 'Password Error',
        text: 'Password must be at least 6 characters long',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Try Again'
      });
      return;
    }

    // Password match validation
    if (values.value.password !== values.value.confirmPassword) {
      await Swal.fire({
        icon: 'error',
        title: 'Password Error',
        text: 'Passwords do not match',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Try Again'
      });
      return;
    }

    // Terms acceptance check
    if (!values.value.termsAccepted) {
      await Swal.fire({
        icon: 'error',
        title: 'Terms & Conditions',
        text: 'Please accept the Terms and Conditions to continue',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Try Again'
      });
      return;
    }

    // Show loading state
    Swal.fire({
      title: 'Creating Account...',
      text: 'Please wait',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      }
    });

    // Prepare registration data
    const userData = {
      first_name: values.value.first_name,
      last_name: values.value.last_name,
      email: values.value.email,
      phone_number: values.value.phone_number,
      password: values.value.password,
      confirm_password: values.value.confirmPassword
    };

    const response = await register(userData);
    
    if (response.data && response.data.status === 200) {
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Your account has been created successfully.',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Okay'
      });
    } else {
      throw new Error(response.data?.message || 'Registration failed');
    }
  } catch (err) {
    console.error('Registration error:', err);
    
    let errorMessage = 'Failed to create account. Please try again.';
    if (err.response?.data?.message) {
      errorMessage = Array.isArray(err.response.data.message) 
        ? err.response.data.message.join('<br>')
        : err.response.data.message;
    }

    await Swal.fire({
      icon: 'error',
      title: 'Registration Failed',
      html: errorMessage,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Try Again'
    });
  }
};

const { isDarkMode } = useTheme();
const currentLogo = computed(() => {
  return isDarkMode() ? new URL('@/assets/logo2.png', import.meta.url).href : new URL('@/assets/logo1.png', import.meta.url).href;
});
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--background-color);
  padding: 2rem;
}

.auth-container {
  width: 100%;
  max-width: 520px;
}

.auth-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: var(--shadow-lg);
}

.auth-header {
  text-align: center;
  margin-bottom: 2rem;
}

.auth-logo {
  width: 120px;
  height: auto;
  margin-bottom: 1.5rem;
}

.auth-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.auth-header p {
  color: var(--text-muted);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
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
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group input {
  width: 100%;
  padding: 12px 40px 12px 40px;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
}

.input-group i:first-child {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
  font-size: 16px;
}

.toggle-password {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45px;
  height: 45px;
}

.toggle-password i {
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.error-message {
  font-size: 0.875rem;
  color: var(--error-color);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-color);
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 16px;
  height: 16px;
}

.checkbox-label a {
  color: var(--primary-color);
  text-decoration: none;
}

.checkbox-label a:hover {
  text-decoration: underline;
}

.submit-btn {
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.auth-footer {
  margin-top: 2rem;
  text-align: center;
  color: var(--text-muted);
}

.login-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  margin-left: 0.5rem;
}

.login-link:hover {
  text-decoration: underline;
}

@media (max-width: 640px) {
  .auth-page {
    padding: 1rem;
  }

  .auth-card {
    padding: 1.5rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

.password-requirements {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: var(--text-muted);
}

.requirements-title {
  margin-bottom: 0.25rem;
  font-weight: 500;
}

.password-requirements ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.password-requirements li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
  color: var(--error-color);
}

.password-requirements li.met {
  color: var(--success-color);
}

.password-requirements li i {
  font-size: 0.75rem;
}

.input-group input.error {
  border-color: var(--error-color);
}

.input-group input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.1);
}
</style> 