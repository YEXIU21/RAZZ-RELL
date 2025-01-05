import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export const addPackage = async (packageData) => {
  try {
    const formData = new FormData();
    
    // Append all package data to FormData
    Object.keys(packageData).forEach(key => {
      if (key === 'inclusions') {
        formData.append(key, JSON.stringify(packageData[key]));
      } else if (key === 'package_image' && packageData[key]) {
        formData.append('package_image', packageData[key]);
      } else {
        formData.append(key, packageData[key]);
      }
    });

    const token = localStorage.getItem('auth_token');
    if (!token) {
      throw new Error('Authentication token not found');
    }

    const response = await axios.post(`${API_URL}/api/add-package`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      withCredentials: true
    });

    return response.data;
  } catch (error) {
    console.error('Error in addPackage:', error);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
      console.error('Response headers:', error.response.headers);
    }
    throw error;
  }
};