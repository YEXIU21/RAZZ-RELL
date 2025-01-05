<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Booking Details</h2>
        <div class="action-buttons">
          <button 
            v-if="canDownloadInvoice" 
            @click="downloadInvoice" 
            class="btn btn-secondary"
          >
            <i class="fas fa-file-invoice"></i>
            Download Invoice
          </button>
          <button @click="$emit('close')" class="btn btn-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <div class="booking-info" v-if="booking">
        <!-- Event Details -->
        <div class="info-section">
          <h3>Event Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Event Date:</span>
              <span class="value">{{ formatDate(booking.eventDate) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Event Time:</span>
              <span class="value">{{ formatTime(booking.eventTime) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Venue:</span>
              <span class="value">{{ booking.venueName }}</span>
            </div>
            <div class="info-item">
              <span class="label">Guest Count:</span>
              <span class="value">{{ booking.guestCount }} persons</span>
            </div>
          </div>
        </div>

        <!-- Package Details -->
        <div class="info-section">
          <h3>Package Details</h3>
          <div class="package-details">
            <img :src="booking.package?.image" :alt="booking.package?.name">
            <div class="package-info">
              <h4>{{ booking.package?.name }}</h4>
              <p class="price">₱{{ booking.package?.price.toLocaleString() }}</p>
              <p class="description">{{ booking.package?.description }}</p>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="info-section">
          <h3>Contact Information</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Full Name:</span>
              <span class="value">{{ booking.fullName }}</span>
            </div>
            <div class="info-item">
              <span class="label">Email:</span>
              <span class="value">{{ booking.email }}</span>
            </div>
            <div class="info-item">
              <span class="label">Phone:</span>
              <span class="value">{{ booking.phone }}</span>
            </div>
          </div>
        </div>

        <!-- Payment Information -->
        <div class="info-section">
          <h3>Payment Information</h3>
          <div class="payment-info">
            <div class="info-item">
              <span class="label">Payment Method:</span>
              <span class="value">{{ formatPaymentMethod(booking.paymentMethod) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Payment Status:</span>
              <span :class="['status', booking.paymentStatus.toLowerCase()]">
                {{ booking.paymentStatus }}
              </span>
            </div>
            <div class="info-item total">
              <span class="label">Total Amount:</span>
              <span class="value">₱{{ booking.totalAmount.toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <!-- Special Requests -->
        <div class="info-section" v-if="booking.specialRequests">
          <h3>Special Requests</h3>
          <p class="special-requests">{{ booking.specialRequests }}</p>
        </div>

        <!-- Booking Status -->
        <div class="info-section">
          <h3>Booking Status</h3>
          <div class="status-timeline">
            <div 
              v-for="(status, index) in bookingStatuses" 
              :key="status"
              :class="['status-item', { active: isStatusActive(status) }]"
            >
              <div class="status-dot"></div>
              <span class="status-text">{{ status }}</span>
              <div 
                v-if="index < bookingStatuses.length - 1" 
                class="status-line"
              ></div>
            </div>
          </div>
          
          <!-- Cancellation Message -->
          <div v-if="booking.status === 'Cancelled' && booking.cancellationMessage" class="cancellation-message">
            <h4>Reason for Cancellation:</h4>
            <p>{{ booking.cancellationMessage }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="modal-actions">
          <button 
            v-if="canCancel" 
            class="cancel-btn"
            @click="handleCancel"
          >
            Cancel Booking
          </button>
          <button 
            v-if="canModify" 
            class="modify-btn"
            @click="handleModify"
          >
            Modify Booking
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add this new feedback modal -->
  <div v-if="showFeedbackModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Before You Cancel</h2>
        <button class="close-btn" @click="showFeedbackModal = false">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="feedback-form">
        <p class="feedback-intro">Please help us improve our services by sharing your feedback:</p>
        
        <div class="form-group">
          <label>How would you rate your overall experience?</label>
          <div class="rating-group">
            <div 
              v-for="star in 5" 
              :key="star"
              class="star"
              :class="{ active: star <= satisfaction }"
              @click="satisfaction = star"
            >
              <i class="fas fa-star"></i>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label>Reason for cancellation</label>
          <select v-model="cancellationReason" required>
            <option value="">Select a reason</option>
            <option value="schedule_conflict">Schedule Conflict</option>
            <option value="change_of_plans">Change of Plans</option>
            <option value="budget_constraints">Budget Constraints</option>
            <option value="service_quality">Service Quality Concerns</option>
            <option value="found_alternative">Found Alternative Service</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group" v-if="cancellationReason === 'other'">
          <label>Please specify your reason</label>
          <input 
            type="text" 
            v-model="otherReason"
            placeholder="Enter your specific reason"
            required
          />
        </div>

        <div class="form-group">
          <label>Additional Comments (Optional)</label>
          <textarea
            v-model="feedbackMessage"
            placeholder="Please share any additional feedback or suggestions..."
            rows="4"
          ></textarea>
        </div>

        <div class="modal-actions">
          <button class="btn-secondary" @click="showFeedbackModal = false">
            Back
          </button>
          <button 
            class="btn-primary" 
            @click="submitFeedbackAndCancel"
            :disabled="!isValidFeedback"
          >
            Submit & Cancel Booking
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  show: Boolean,
  booking: Object
});

const emit = defineEmits(['close', 'cancel', 'modify']);

const bookingStatuses = [
  'Pending',
  'Confirmed',
  'In Progress',
  'Completed',
  'Cancelled'
];

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatTime = (time) => {
  return new Date(`2000-01-01T${time}`).toLocaleTimeString('en-PH', {
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
  });
};

const formatPaymentMethod = (method) => {
  return method.charAt(0).toUpperCase() + method.slice(1);
};

const isStatusActive = (status) => {
  const currentIndex = bookingStatuses.indexOf(props.booking?.status);
  const statusIndex = bookingStatuses.indexOf(status);
  return statusIndex <= currentIndex;
};

const canCancel = computed(() => {
  return props.booking?.status === 'Pending' || props.booking?.status === 'Confirmed';
});

const canModify = computed(() => {
  return props.booking?.status === 'Pending';
});

// New refs for feedback form
const showFeedbackModal = ref(false);
const satisfaction = ref(0);
const cancellationReason = ref('');
const otherReason = ref('');
const feedbackMessage = ref('');

// Computed property to validate feedback
const isValidFeedback = computed(() => {
  return satisfaction.value > 0 && cancellationReason.value;
});

const handleCancel = () => {
  showFeedbackModal.value = true; // Show feedback form instead of direct cancellation
};

const handleModify = () => {
  emit('modify', props.booking);
};

// Add new method for feedback submission and cancellation
const submitFeedbackAndCancel = async () => {
  if (!isValidFeedback.value) {
    Swal.fire({
      icon: 'error',
      title: 'Incomplete Feedback',
      text: 'Please provide a satisfaction rating and reason for cancellation'
    });
    return;
  }

  try {
    // First submit the feedback
    await axios.post(`${import.meta.env.VITE_API_URL}/api/booking-feedback`, {
      booking_id: props.booking.id,
      satisfaction_rating: satisfaction.value,
      cancellation_reason: cancellationReason.value === 'other' ? otherReason.value : cancellationReason.value,
      feedback_message: feedbackMessage.value
    });

    // Then cancel the booking with the reason
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/update-booking`,
      {
        id: props.booking.id,
        status: 'cancelled',
        cancellation_reason: cancellationReason.value === 'other' ? otherReason.value : cancellationReason.value
      }
    );

    if (response.status === 200) {
      showFeedbackModal.value = false;
      
      // Reset form
      satisfaction.value = 0;
      cancellationReason.value = '';
      otherReason.value = '';
      feedbackMessage.value = '';

      Swal.fire({
        icon: 'success',
        title: 'Thank You for Your Feedback',
        text: 'Your booking has been cancelled and your feedback has been recorded.'
      });

      emit('close');
    }
  } catch (error) {
    console.error('Error processing cancellation:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to process your request. Please try again.'
    });
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: var(--overlay-background);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
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

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: color 0.2s;
}

.close-btn:hover {
  color: var(--text-color);
}

.info-section {
  margin-bottom: 2rem;
}

.info-section h3 {
  color: var(--text-color);
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.label {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.value {
  color: var(--text-color);
  font-weight: 500;
}

.package-details {
  display: flex;
  gap: 1.5rem;
  background: var(--hover-background);
  padding: 1rem;
  border-radius: 8px;
}

.package-details img {
  width: 150px;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
}

.package-info h4 {
  margin-bottom: 0.5rem;
  color: var(--text-color);
}

.price {
  color: var(--success-color);
  font-weight: bold;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.description {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.payment-info {
  background: var(--hover-background);
  padding: 1rem;
  border-radius: 8px;
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.status.pending {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status.preparing {
  background: var(--warning-light);
  color: var(--warning-color);
}

.status.confirmed {
  background: var(--info-light);
  color: var(--info-color);
}

.status.ongoing {
  background: var(--info-light);
  color: var(--info-color);
}

.status.completed {
  background: var(--success-light);
  color: var(--success-color);
}

.status.cancelled {
  background: var(--error-light);
  color: var(--error-color);
}

.total {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.total .value {
  color: var(--success-color);
  font-size: 1.2rem;
}

.special-requests {
  background: var(--hover-background);
  padding: 1rem;
  border-radius: 8px;
  color: var(--text-muted);
  font-style: italic;
}

.status-timeline {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding: 0 1rem;
}

.status-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
}

.status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: var(--border-color);
  margin-bottom: 0.5rem;
}

.status-line {
  position: absolute;
  top: 6px;
  left: 50%;
  width: 100%;
  height: 2px;
  background: var(--border-color);
  z-index: -1;
}

.status-text {
  font-size: 0.8rem;
  color: var(--text-muted);
  text-align: center;
}

.status-item.active .status-dot {
  background: var(--success-color);
}

.status-item.active .status-line {
  background: var(--success-color);
}

.status-item.active .status-text {
  color: var(--success-color);
  font-weight: 500;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  justify-content: flex-end;
}

.cancel-btn, .modify-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
  border: none;
  color: white;
}

.cancel-btn {
  background: var(--error-color);
}

.cancel-btn:hover {
  background: var(--error-dark, #b91c1c);
}

.modify-btn {
  background: var(--info-color);
}

.modify-btn:hover {
  background: var(--info-dark, #1976D2);
}

.cancellation-message {
  margin-top: 1.5rem;
  padding: 1rem;
  background: var(--error-light);
  border-radius: 8px;
  border-left: 4px solid var(--error-color);
}

.cancellation-message h4 {
  color: var(--error-color);
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
}

.cancellation-message p {
  color: var(--text-color);
  font-size: 0.9rem;
  margin: 0;
  line-height: 1.5;
}

.feedback-form {
  padding: 1.5rem;
}

.feedback-intro {
  margin-bottom: 1.5rem;
  color: var(--text-color);
}

.rating-group {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.star {
  cursor: pointer;
  color: var(--text-muted);
  font-size: 1.5rem;
}

.star.active {
  color: var(--warning-color);
}

.radio-group {
  display: flex;
  gap: 2rem;
  margin-top: 0.5rem;
}

.radio-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  color: var(--text-color);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  cursor: pointer;
  color: var(--text-color);
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 6px;
  background: var(--primary-color);
  color: white;
  cursor: pointer;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .package-details {
    flex-direction: column;
  }

  .package-details img {
    width: 100%;
    height: 150px;
  }

  .status-timeline {
    flex-direction: column;
    gap: 1rem;
  }

  .status-item {
    flex-direction: row;
    gap: 1rem;
  }

  .status-line {
    display: none;
  }

  .modal-actions {
    flex-direction: column;
  }

  .cancel-btn, .modify-btn {
    width: 100%;
  }
}
</style> 