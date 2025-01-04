<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <router-link to="/">
            <img :src="currentLogo" alt="Razz Rell Events" class="auth-logo" />
          </router-link>
          <h1>Welcome Back</h1>
          <p>Sign in to your account to continue</p>
        </div>

        <form @submit.prevent="handleSubmit" class="auth-form">
          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-group">
              <i class="fas fa-envelope"></i>
              <input type="email" id="email" v-model="values.email" @blur="handleBlur('email')"
                :class="{ 'error': getFieldError('email') }" placeholder="Enter your email" autocomplete="email" />
            </div>
            <span class="error-message" v-if="getFieldError('email')">
              {{ getFieldError('email') }}
            </span>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-group">
              <i class="fas fa-lock"></i>
              <input :type="showPassword ? 'text' : 'password'" id="password" v-model="values.password"
                @blur="handleBlur('password')" :class="{ 'error': getFieldError('password') }"
                placeholder="Enter your password" autocomplete="current-password" />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>
            </div>
            <span class="error-message" v-if="getFieldError('password')">
              {{ getFieldError('password') }}
            </span>
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" v-model="values.rememberMe" />
              Remember me
            </label>
            <router-link to="/forgot-password" class="forgot-password">
              Forgot password?
            </router-link>
          </div>

          <button type="submit" class="submit-btn" >
            <span >Sign In</span>
          </button>
        </form>

        <div class="auth-footer">
          <p>Don't have an account?</p>
          <router-link to="/register" class="register-link">
            Create an account
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useValidation } from '@/composables/useValidation';
import { useNotifications } from '@/composables/useNotifications';
import { useTheme } from '@/composables/useTheme';
import Swal from 'sweetalert2';

const router = useRouter();
const { login } = useAuth();
const { success, error: showError } = useNotifications();
const { isDarkMode } = useTheme();

const currentLogo = computed(() => {
  return isDarkMode() ? new URL('@/assets/logo2.png', import.meta.url).href : new URL('@/assets/logo1.png', import.meta.url).href;
});

const showPassword = ref(false);

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
  email: '',
  password: '',
  rememberMe: false,
});

const validationRules = {
  email: ['required', 'email'],
  password: ['required', ['minLength', 6]],
};

const handleSubmit = async () => {
  if (!validateAll(validationRules)) return;

  try {
    const result = await login({
      email: values.value.email,
      password: values.value.password,
      remember: values.value.rememberMe,
    });

    if (result === false) {
      return;
    }
  } catch (err) {
    console.error('Unexpected error during login:', err);
  }
};
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
  max-width: 420px;
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

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.forgot-password {
  color: var(--primary-color);
  text-decoration: none;
  font-size: 0.875rem;
}

.forgot-password:hover {
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

.register-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  margin-left: 0.5rem;
}

.register-link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-page {
    padding: 1rem;
  }

  .auth-card {
    padding: 1.5rem;
  }

  .form-options {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
}
</style>