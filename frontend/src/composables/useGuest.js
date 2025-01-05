import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export function useGuest() {
  const router = useRouter();

  const addGuest = async (credentials) => {
    try {
      const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/add-guest`, credentials);

      if(response.data.status === 200){
        Swal.fire({
          title: 'Success',
          text: 'Guest limitations added successfully',
          icon: 'success'
        }); 
      }else{
        throw new Error('Failed to get all guests');
      }

    } catch (error) {
      console.error('Adding guest error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred during guest addition.',
      });
      return false;
    }
  };

  const getAllGuests = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-guest`);
      console.log(response.data);  
    } catch (error) {
      console.error('Adding guest error:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'An error occurred during guest addition.',
      });
      return false;
    }
  };

  return {
    addGuest,
    getAllGuests
  };
} 