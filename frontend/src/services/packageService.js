import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export const packageService = {
  async addPackage(packageData) {
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

      const response = await axios.post(`${API_URL}/api/add-package`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error in addPackage:', error);
      throw error;
    }
  },

  async getAllPackages() {
    try {
      const response = await axios.get(`${API_URL}/api/get-all-packages`);
      return response.data;
    } catch (error) {
      console.error('Error in getAllPackages:', error);
      throw error;
    }
  },

  async getPackageById(id) {
    try {
      const response = await axios.get(`${API_URL}/api/get-package-by-id/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error in getPackageById:', error);
      throw error;
    }
  },

  async updatePackage(packageData) {
    try {
      const formData = new FormData();
      
      Object.keys(packageData).forEach(key => {
        if (key === 'inclusions') {
          formData.append(key, JSON.stringify(packageData[key]));
        } else if (key === 'package_image' && packageData[key]) {
          formData.append('package_image', packageData[key]);
        } else {
          formData.append(key, packageData[key]);
        }
      });

      const response = await axios.post(`${API_URL}/api/update-package`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      });

      return response.data;
    } catch (error) {
      console.error('Error in updatePackage:', error);
      throw error;
    }
  },

  async deletePackage(id) {
    try {
      const response = await axios.post(`${API_URL}/api/delete-package/${id}`);
      return response.data;
    } catch (error) {
      console.error('Error in deletePackage:', error);
      throw error;
    }
  }
};