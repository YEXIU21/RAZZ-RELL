<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Rate Booking</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="portfolio-form">
        <div class="form-group">
          <label>Rate your experience</label>
          <div class="star-rating">
            <div class="stars">
              <span
                v-for="star in 5"
                :key="star"
                class="star"
                :class="{ 'active': star <= formData.rating }"
                @click="formData.rating = star"
                @mouseover="hoverRating = star"
                @mouseleave="hoverRating = 0"
              >
                <i class="fas fa-star"></i>
              </span>
            </div>
            <span class="rating-text">{{ getRatingText }}</span>
          </div>
        </div>

        <div class="form-group">
          <label for="description">Write Review</label>
          <textarea
            id="description"
            v-model="formData.review"
            required
            rows="4"
            placeholder="Enter Review"
          ></textarea>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Adding...' : 'Submit Rating' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
});

const { token } = useAuth();

const emit = defineEmits(['close', 'success']);

const formData = reactive({
  package_id: props.booking.package_id,
  rating: 1,
  review: null,
  user_id: props.booking.user_id,
  booking_id: props.booking.id
});

const allGuests = ref([]);
const isSubmitting = ref(false);
const hoverRating = ref(0);

const getAllGuessst = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-guest`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    allGuests.value = response.data;
  } catch (error) {
    console.error('Error fetching guests:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to fetch guest information'
    });
  }
};

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    const formDataToSend = new FormData();
    formDataToSend.append('package_id', formData.package_id);
    formDataToSend.append('rating', formData.rating);
    formDataToSend.append('review', formData.review);
    formDataToSend.append('user_id', formData.user_id);
    formDataToSend.append('booking_id', formData.booking_id);

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/add-rating`, formDataToSend, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token.value}`
      }
    });

    if(response.status === 200){
      emit('rating-added', response.data.package);
      
      await Swal.fire({
        title: 'Success',
        text: response.data.message,
        icon: 'success'
      });
      emit('close');
    } else {
      throw new Error(response.data.message || 'Failed to add rating. Please try again.');
    }
    
  } catch (error) {
    console.error('Error adding rating:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to add rating. Please try again.'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const getRatingText = computed(() => {
  const rating = hoverRating.value || formData.rating;
  switch (rating) {
    case 1: return 'Poor';
    case 2: return 'Fair';
    case 3: return 'Good';
    case 4: return 'Very Good';
    case 5: return 'Excellent';
    default: return 'Rate your experience';
  }
});

onMounted(() => {
  getAllGuessst();
}); 
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--overlay-background);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--modal-background);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
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

.portfolio-form {
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

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
}

textarea {
  resize: vertical;
}

.inclusions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.inclusion-item {
  display: flex;
  gap: 0.5rem;
}

.remove-btn {
  padding: 0.5rem;
  background: var(--danger-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.add-btn {
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.image-upload {
  position: relative;
  height: 200px;
  border: 2px dashed var(--border-color);
  border-radius: 6px;
  overflow: hidden;
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

.upload-placeholder {
  position: absolute;
  top: 0;
  left: 0;
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
  font-size: 2rem;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--secondary-color);
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

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
  }
}

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
  cursor: pointer;
}

.form-select:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px var(--primary-light);
}

.star-rating {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1rem 0;
}

.stars {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.star {
  font-size: 2rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: color 0.2s, transform 0.2s;
}

.star:hover,
.star.active {
  color: var(--warning-color);
}

.star:hover ~ .star {
  color: var(--text-muted);
}

.stars:hover .star {
  color: var(--warning-color);
}

.stars:hover .star:hover ~ .star {
  color: var(--text-muted);
}

.rating-text {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin-top: 0.5rem;
}

.star i {
  transition: transform 0.2s ease;
}

.star:hover i,
.star.active i {
  transform: scale(1.2);
}

@media (max-width: 768px) {
  .star {
    font-size: 1.75rem;
  }
  
  .stars {
    justify-content: center;
  }
  
  .rating-text {
    text-align: center;
  }
}
</style> 