import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const user = ref(null);
const token = ref(localStorage.getItem('auth_token'));
const isAuthenticated = computed(() => !!token.value);
const isAdmin = computed(() => localStorage.getItem('user_role') === 'admin' || localStorage.getItem('user_role') === 'staff');

export function useAuth() {
  const router = useRouter();
  const apiUrl = import.meta.env.VITE_API_URL;

  const login = async (credentials) => {
    try {
      const response = await axios.post(`${apiUrl}/api/login`, credentials);
      
      if (response.data.status === 200) {
        token.value = response.data.token;
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_role', response.data.user.role);
        localStorage.setItem('user_info', JSON.stringify(response.data.user));
        user.value = response.data.user;

        const redirectPath = router.currentRoute.value.query.redirect || (isAdmin.value ? '/admin' : '/');
        await router.push(redirectPath);
        
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'You are now logged in.',
          timer: 1500,
          showConfirmButton: false
        });
      }

    } catch (error) {
      console.error('Login error:', error);
      const errorMessage = error.response?.data?.message || 'An error occurred during login.';
      const errorType = error.response?.data?.error_type;

      let title = 'Error!';
      let text = errorMessage;
      let confirmButtonText = 'Okay';

      if (errorType === 'account_not_found') {
        title = 'Account Not Found';
        text = 'This account does not exist. Would you like to register?';
        confirmButtonText = 'Register Now';
      } else if (errorType === 'invalid_credentials') {
        title = 'Invalid Password';
        text = 'The password you entered is incorrect. Please try again.';
      }

      const result = await Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#3085d6',
        confirmButtonText: confirmButtonText,
        showCancelButton: errorType === 'account_not_found',
        cancelButtonText: 'Try Again',
        cancelButtonColor: '#d33'
      });

      if (errorType === 'account_not_found' && result.isConfirmed) {
        router.push('/register');
      }

      return false;
    }
  };

  const logout = async () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_info');
    localStorage.removeItem('user_role');
    localStorage.removeItem('user');
    
    await Swal.fire({
      icon: 'success',
      title: 'Logged Out!',
      text: 'You have been successfully logged out.',
      timer: 1500,
      showConfirmButton: false
    });
  };

  const fetchUser = async () => {
    if (!token.value) return;

    try {
      const response = await fetch(`${apiUrl}/api/my-info`);

      if (!response.ok) {
        throw new Error('Failed to fetch user');
      }

      user.value = localStorage.getItem('user_info');
    } catch (error) {
      console.error('Error fetching user:', error);
      logout();
    }
  };

  const register = async (userData) => {
    try {
      const response = await axios.post(`${apiUrl}/api/register`, userData);
      
      if (response.data.status === 200) {
        token.value = response.data.token;
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_role', response.data.user.role);
        localStorage.setItem('user_info', JSON.stringify(response.data.user));
        user.value = response.data.user;
        
        const redirectPath = router.currentRoute.value.query.redirect || (isAdmin.value ? '/admin' : '/');
        await router.push(redirectPath);
        return response;
      }

    } catch (error) {
      console.error('Registration error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Registration Failed',
        text: error.response?.data?.message || 'An error occurred during registration. Please try again.',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Okay'
      });
      return false;
    }
  };

  const contactUs = async (userData) => {
    try {
      const response = await axios.post(`${apiUrl}/api/contact-us`, userData);
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Your message has been sent successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
      } else {
        await Swal.fire({
          icon: 'error',
          title: 'Success!',
          text: 'Your account has been created successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
      }

    } catch (error) {
      console.error('Registration error:', error);
      return false;
    }
  };

  const updateProfile = async (profileData) => {
    try {
      const response = await fetch(`${apiUrl}/api/auth/profile`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token.value}`,
        },
        body: JSON.stringify(profileData),
      });

      if (!response.ok) {
        throw new Error('Failed to update profile');
      }

      const updatedUser = await response.json();
      user.value = updatedUser;
      
      return true;
    } catch (error) {
      console.error('Profile update error:', error);
      return false;
    }
  };

  const changePassword = async (passwordData) => {
    try {
      const response = await axios.post(`${apiUrl}/api/reset-password`, passwordData);
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Your password has been reset successfully.',
          confirmButtonColor: '#3085d6',
          confirmButtonText: 'Okay'
        });
        return true;
      }
      return false;
    } catch (error) {
      console.error('Password change error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'Failed to reset password. Please try again.',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Okay'
      });
      return false;
    }
  };

  const forgotPassword = async (email) => {
    try {
      const response = await axios.post(`${apiUrl}/api/forgot-password`, { email });
      
      if (response.data.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: 'Password reset instructions have been sent to your email.',
        });
        return true;
      }
    } catch (error) {
      console.error('Forgot password error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred while processing your request.',
      });
      return false;
    }
  };

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    register,
    updateProfile,
    changePassword,
    forgotPassword,
    contactUs,
    fetchUser,
  };
} 