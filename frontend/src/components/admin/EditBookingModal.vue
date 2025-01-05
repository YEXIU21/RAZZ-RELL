<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Booking #{{ booking.id }}</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Payment Tracking Section -->
      <div class="payment-section">
        <h3>Payment Tracking</h3>
        
        <!-- Payment Summary -->
        <div class="payment-summary">
          <div class="summary-row">
            <span>Total Amount:</span>
            <span class="amount">₱{{ formatNumber(booking.total_price) }}</span>
          </div>
          <div class="summary-row">
            <span>Total Paid:</span>
            <span class="amount paid">₱{{ formatNumber(totalPaid) }}</span>
          </div>
          <div class="summary-row">
            <span>Remaining Balance:</span>
            <span class="amount remaining">₱{{ formatNumber(remainingBalance) }}</span>
          </div>
        </div>

        <!-- Payment History -->
        <div class="payment-history">
          <h4>Payment History</h4>
          <div class="payment-list">
            <div v-for="payment in payments" :key="payment.id" class="payment-item">
              <div class="payment-info">
                <span class="payment-amount">₱{{ formatNumber(payment.amount) }}</span>
                <span class="payment-date">{{ formatDate(payment.created_at) }}</span>
              </div>
              <p v-if="payment.notes" class="payment-notes">{{ payment.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Add New Payment -->
        <div class="add-payment">
          <h4>Add Payment</h4>
          <div class="payment-form">
            <div class="form-group">
              <label>Amount</label>
              <input 
                type="number" 
                v-model="newPayment.amount"
                :max="remainingBalance"
                min="0"
                step="0.01"
                :disabled="isFullyPaid"
                :class="{ 'input-disabled': isFullyPaid }"
                @input="validatePaymentAmount"
              >
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea 
                v-model="newPayment.notes"
                placeholder="Add payment notes..."
                :disabled="isFullyPaid"
                :class="{ 'input-disabled': isFullyPaid }"
              ></textarea>
            </div>
            <div class="info-message" :class="{ 'warning': isFullyPaid }">
              <i class="fas" :class="isFullyPaid ? 'fa-check-circle' : 'fa-info-circle'"></i>
              <span>{{ isFullyPaid ? 'Payment has been fully paid' : 'Payment will be recorded when you click "Save Changes"' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Update Section -->
      <form @submit.prevent="handleSubmit" class="edit-form">
        <div class="form-section">
          <div class="form-group">
            <label for="status">Status</label>
            <select v-model="formData.status" id="status">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Cancellation Message Field -->
          <div class="form-group" v-if="formData.status === 'cancelled'">
            <label for="cancellationMessage">Reason for Cancellation</label>
            <textarea
              v-model="formData.cancellationMessage"
              id="cancellationMessage"
              rows="4"
              placeholder="Please provide a reason for cancellation..."
              required
            ></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Close</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useAuth } from '@/composables/useAuth';

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update']);
const isSubmitting = ref(false);
const payments = ref([]);
const totalPaid = ref(0);
const { token } = useAuth();

const formData = ref({
  status: props.booking.status.toLowerCase(),
  cancellationMessage: props.booking.cancellationMessage || ''
});

const newPayment = reactive({
  amount: '',
  notes: ''
});

// Fetch all payments for this booking
const fetchPayments = async () => {
  try {
    console.log('Fetching payments for booking:', props.booking.id);
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/bookings/${props.booking.id}/payments`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    console.log('Payments response:', response.data);
    
    if (response.data.status === 'success') {
      payments.value = response.data.payments;
      totalPaid.value = response.data.total_paid;
    } else {
      throw new Error(response.data.message || 'Failed to fetch payments');
    }
  } catch (error) {
    console.error('Error fetching payments:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to fetch payment history'
    });
  }
};

const validatePayment = () => {
  if (!newPayment.amount || parseFloat(newPayment.amount) <= 0) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Amount',
      text: 'Please enter a valid payment amount'
    });
    return false;
  }

  if (parseFloat(newPayment.amount) > remainingBalance.value) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Amount',
      text: 'Payment amount cannot exceed the remaining balance'
    });
    return false;
  }

  return true;
};

// Add new payment
const addPayment = async () => {
  if (!validatePayment()) return;

  isSubmitting.value = true;
  try {
    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/payments`, {
      booking_id: props.booking.id,
      amount: parseFloat(newPayment.amount),
      notes: newPayment.notes
    }, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      }
    });

    if (response.data.status === 'success') {
      newPayment.amount = '';
      newPayment.notes = '';
      await fetchPayments();
      emit('update');

      await Swal.fire({
        icon: 'success',
        title: 'Payment Added',
        text: 'Payment has been recorded successfully'
      });
    } else {
      throw new Error(response.data.message || 'Failed to record payment');
    }
  } catch (error) {
    console.error('Payment error details:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    });

    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to record payment'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const formatNumber = (num) => {
  return Number(num).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const isFullyPaid = computed(() => {
  return totalPaid.value >= props.booking.total_price;
});

const remainingBalance = computed(() => {
  return Math.max(0, props.booking.total_price - totalPaid.value);
});

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;

    // Only process payment if amount is entered and not exceeding remaining balance
    if (newPayment.amount && parseFloat(newPayment.amount) > 0) {
      if (!validatePayment()) {
        isSubmitting.value = false;
        return;
      }

      await axios.post(`${import.meta.env.VITE_API_URL}/api/payments`, {
        booking_id: props.booking.id,
        amount: parseFloat(newPayment.amount),
        notes: newPayment.notes || 'Payment recorded during status update'
      }, {
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Content-Type': 'application/json'
        }
      });
    }

    const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/update-booking`, {
      id: props.booking.id,
      status: formData.value.status,
      cancellationMessage: formData.value.status === 'cancelled' ? formData.value.cancellationMessage : null
    }, {
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      }
    });
    
    if (response.data.status === 200) {
      await fetchPayments(); // Refresh payments after successful update
      await Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Booking updated successfully'
      });
      emit('update');
      emit('close');
    } else {
      throw new Error(response.data.message || 'Failed to update booking');
    }
  } catch (error) {
    console.error('Error updating booking:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || error.message || 'Failed to update booking'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const validatePaymentAmount = (event) => {
  const value = parseFloat(event.target.value);
  if (value > remainingBalance.value) {
    newPayment.amount = remainingBalance.value;
  } else if (value < 0) {
    newPayment.amount = 0;
  }
};

onMounted(() => {
  fetchPayments();
});
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
  background: var(--modal-background, #1a1a1a);
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
  color: var(--text-color);
  border: 1px solid var(--border-color, #2d2d2d);
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

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: var(--card-background, #242424);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  border: 1px solid var(--border-color, #2d2d2d);
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

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #2d2d2d);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background, #1a1a1a);
  color: var(--text-color);
}

textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--secondary-color, #374151);
  color: var(--text-color);
  border: 1px solid var(--border-color, #2d2d2d);
  border-radius: 6px;
  cursor: pointer;
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: var(--text-color);
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
  background: var(--disabled-background, #1f1f1f);
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

.payment-section {
  background: var(--card-background, #242424);
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  border: 1px solid var(--border-color, #2d2d2d);
}

.payment-summary {
  background: var(--card-background-secondary, #2a2a2a);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  border: 1px solid var(--border-color, #2d2d2d);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border-color, #2d2d2d);
  color: var(--text-color);
}

.summary-row:last-child {
  border-bottom: none;
}

.amount {
  font-weight: 600;
}

.amount.paid {
  color: #059669;
}

.amount.remaining {
  color: #dc2626;
}

.payment-history {
  margin-bottom: 1.5rem;
}

.payment-list {
  max-height: 200px;
  overflow-y: auto;
}

.payment-item {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #2d2d2d);
  background: var(--card-background-secondary, #2a2a2a);
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.payment-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.25rem;
}

.payment-amount {
  font-weight: 600;
  color: #059669;
}

.payment-date {
  color: var(--text-muted, #888);
  font-size: 0.875rem;
}

.payment-notes {
  color: var(--text-muted, #888);
  font-size: 0.875rem;
  margin: 0;
}

.add-payment {
  background: var(--card-background-secondary, #2a2a2a);
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-color, #2d2d2d);
}

.payment-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.btn-add-payment {
  background: #059669;
  color: white;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-add-payment:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.input-disabled {
  background-color: var(--disabled-background, #1f1f1f);
  cursor: not-allowed;
  opacity: 0.7;
}

.info-message {
  background: var(--card-background-secondary, #2a2a2a);
  padding: 1rem;
  border-radius: 6px;
  border: 1px solid var(--border-color, #2d2d2d);
  color: var(--text-color);
}

.info-message.warning {
  background: var(--success-background, #132f21);
  border-color: var(--success-color, #059669);
  color: var(--success-text, #10b981);
}

.info-message.warning i {
  color: #059669;
}
</style> 