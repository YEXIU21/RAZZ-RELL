import axios from 'axios';

const apiUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';

// Create an axios instance with the base URL
const api = axios.create({
  baseURL: apiUrl,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Add request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Helper function to get storage URL
export const getStorageUrl = (path) => {
  if (!path) return null;
  if (path.startsWith('http')) return path;
  if (path.startsWith('images/')) return `/src/assets/${path}`;
  return `${apiUrl}/storage/${path}`;
};

export { api, apiUrl }; 