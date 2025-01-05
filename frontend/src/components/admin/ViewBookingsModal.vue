<template>
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Booking Details</h2>
          <button class="close-btn" @click="$emit('close')">
            <i class="fas fa-times"></i>
          </button>
        </div>
  
        <form class="edit-form">
          <div class="form-section">
            <img 
              :src="getImageUrl(packages?.package_image)" 
              class="details-image" 
              :alt="packages?.package_name || 'Package Image'"
              @error="handleImageError"
              ref="packageImage"
            />
            
            <div class="section">
              <h3 class="section-title">Event Details</h3>
              <div class="details">
                <h4>Event Type: <span class="event-type" :class="packages?.package_type?.toLowerCase()">{{ packages?.package_type }}</span></h4>
                <h4>Event Date: <span>{{ formatDate(bookings.event_date) }}</span></h4>
                <h4>Event Time: <span>{{ formatTime(bookings.event_time) }}</span></h4>
                <h4>Venue: <span>{{ bookings.venue_name }}</span></h4>
              </div>
            </div>

            <div class="section">
              <h3 class="section-title">Package Details</h3>
              <div class="details">
                <h4>Package Price: ₱{{ formatNumber(packages.package_price) }}</h4>
                <h4>Total Price: ₱{{ formatNumber(bookings.total_price) }}</h4>
                <h4>Package Inclusions:</h4>
                <ul>
                  <li v-for="(inclusion, index) in getPackageInclusions" :key="index">
                    - {{ inclusion }}
                  </li>
                </ul>
              </div>
            </div>

            <div class="section">
              <h3 class="section-title">Customer Details</h3>
              <div class="details">
                <h4>Name: {{ bookings.full_name }}</h4>
                <h4>Email: {{ bookings.email }}</h4>
                <h4>Phone: {{ bookings.phone }}</h4>
              </div>
            </div>

            <div class="section">
              <h3>Payment History</h3>
              <div class="payment-summary">
                <div class="summary-item">
                  <span>Total Amount:</span>
                  <span>₱{{ formatNumber(bookings.total_price || 0) }}</span>
                </div>
                <div class="summary-item">
                  <span>Total Paid:</span>
                  <span class="text-success">₱{{ formatNumber(totalPaid || 0) }}</span>
                </div>
                <div class="summary-item" v-if="isOverpaid">
                  <span>Tip Amount:</span>
                  <span class="text-success">₱{{ formatNumber(tipAmount) }}</span>
                </div>
                <div class="summary-item" v-else>
                  <span>Remaining Balance:</span>
                  <span class="text-danger">₱{{ formatNumber(remainingBalance) }}</span>
                </div>
              </div>

              <div class="payment-history">
                <table v-if="payments.length > 0">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Amount</th>
                      <th>Type</th>
                      <th>Notes</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="payment in payments" :key="payment.id">
                      <td>{{ formatDate(payment.created_at) }}</td>
                      <td>₱{{ formatNumber(payment.amount) }}</td>
                      <td>{{ payment.payment_type }}</td>
                      <td>{{ payment.notes || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="no-payments">
                  No payments recorded yet
                </div>
              </div>
            </div>

            <div class="section" v-if="showCancellationDetails">
              <h3 class="section-title">Cancellation Details</h3>
              <div class="details">
                <div class="cancellation-info">
                  <div class="info-row">
                    <h4>Status:</h4>
                    <span class="status-badge cancelled">{{ bookings.status }}</span>
                  </div>
                  <div class="info-row">
                    <h4>Cancelled On:</h4>
                    <span>{{ bookings.cancelled_at ? formatDate(bookings.cancelled_at) : 'Not specified' }}</span>
                  </div>
                  <div class="info-row">
                    <h4>Reason for Cancellation:</h4>
                    <div class="cancellation-reason" v-if="bookings.cancellation_reason">
                      {{ bookings.cancellation_reason }}
                    </div>
                    <div class="cancellation-reason no-reason" v-else>
                      No reason provided
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
  
          <div class="form-actions">
            <button 
              v-if="bookings.status?.toLowerCase() === 'pending'"
              type="button" 
              class="cancel-booking-btn" 
              @click="handleCancelBooking"
            >
              Cancel Booking
            </button>
            <button type="button" class="cancel-btn" @click="$emit('close')">Okay</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, reactive, computed, onMounted, watch } from 'vue';
  import { useAuth } from '@/composables/useAuth';
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  const props = defineProps({
    booking: {
      type: Object,
      required: true
    }
  });
  
  const emit = defineEmits(['close', 'update']);
  const { token } = useAuth();
  
  const bookings = ref([]);
  const packages = ref([]);
  const isSubmitting = ref(false);
  const payments = ref([]);
  const totalPaid = ref(0);
  const isOverpaid = computed(() => {
    return totalPaid.value > bookings.value.total_price;
  });

  const tipAmount = computed(() => {
    if (isOverpaid.value) {
      return totalPaid.value - bookings.value.total_price;
    }
    return 0;
  });

  const remainingBalance = computed(() => {
    if (!bookings.value.total_price) return 0;
    if (isOverpaid.value) {
      return 0;
    }
    return bookings.value.total_price - totalPaid.value;
  });
  
  const selectedPackage = computed(() => {
    return bookings.value.find(pkg => pkg.id === props.booking.id);
  });
  

    const formatNumber = (num) => {
        if (num === null || num === undefined || isNaN(num)) return '0.00';
        return Number(num).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const defaultImageUrl = '/images/gcash-logo.png';
    const packageImage = ref(null);

    const handleImageError = () => {
      if (packageImage.value) {
        packageImage.value.src = defaultImageUrl;
      }
    };

    const getImageUrl = (imagePath) => {
      if (!imagePath) return defaultImageUrl;
      if (imagePath.startsWith('http')) return imagePath;
      
      try {
        return `${import.meta.env.VITE_API_URL}/storage/${imagePath.replace(/^\/storage\//, '')}`;
      } catch (error) {
        console.error('Error getting image URL:', error);
        return defaultImageUrl;
      }
    };

  const fetchBooking = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-booking-by-id/${props.booking.id}`);
      console.log('Booking data:', response.data);
      
      bookings.value = {
        ...response.data,
        status: response.data.status || '',
        cancellation_reason: response.data.cancellation_reason || '',
        cancelled_at: response.data.cancelled_at || null
      };
      
      packages.value = response.data.package || {
        package_name: '',
        package_image: '',
        package_price: 0,
        package_type: '',
        package_inclusion: []
      };
      
      console.log('Processed booking data:', bookings.value);
      console.log('Processed package data:', packages.value);
    } catch (error) {
      console.error('Error fetching booking:', error);
      bookings.value = {};
      packages.value = {
        package_name: '',
        package_image: '',
        package_price: 0,
        package_type: '',
        package_inclusion: []
      };
    }
  };

  const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const formatTime = (time) => {
    if (!time) return '';
    // Extract time portion if it's a full datetime string
    const timeStr = time.includes('T') ? time.split('T')[1] : time;
    return new Date(`2000-01-01T${timeStr}`).toLocaleTimeString('en-US', {
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  };

  const fetchPayments = async () => {
    try {
      const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/bookings/${props.booking.id}/payments`, {
        headers: {
          'Authorization': `Bearer ${token.value}`
        }
      });
      
      if (response.data.status === 'success') {
        payments.value = response.data.payments;
        totalPaid.value = response.data.total_paid;
      } else {
        throw new Error(response.data.message || 'Failed to fetch payments');
      }
    } catch (error) {
      console.error('Error fetching payments:', error);
      await Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || error.message || 'Failed to fetch payment history'
      });
    }
  };

  const handleCancelBooking = async () => {
    try {
      // First, show confirmation dialog with feedback form
      const { value: formValues } = await Swal.fire({
        title: 'Cancel Booking',
        html: `
          <div class="feedback-form">
            <label for="cancellation-reason">Please provide a reason for cancellation:</label>
            <textarea 
              id="cancellation-reason" 
              class="swal2-textarea" 
              placeholder="Enter detailed reason for cancellation..."
              rows="4"
            ></textarea>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Yes, cancel booking',
        cancelButtonText: 'No, keep booking',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const reason = document.getElementById('cancellation-reason').value;
          if (!reason.trim()) {
            Swal.showValidationMessage('Please provide a reason for cancellation');
            return false;
          }
          return {
            reason: reason.trim()
          };
        }
      });

      // If user confirms and provides reason
      if (formValues) {
        console.log('Sending cancellation request with reason:', formValues.reason);

        const response = await axios.post(
          `${import.meta.env.VITE_API_URL}/api/update-booking`,
          {
            id: props.booking.id,
            status: 'cancelled',
            cancellation_reason: formValues.reason,
            cancelled_at: new Date().toISOString()
          },
          {
            headers: {
              'Content-Type': 'application/json'
            }
          }
        );

        if (response.status === 200) {
          // Refresh the booking data immediately
          await fetchBooking();

          // Show success message
          await Swal.fire({
            icon: 'success',
            title: 'Booking Cancelled Successfully',
            html: `
              <div class="cancellation-summary">
                <p>The booking has been cancelled.</p>
                <p><strong>Cancellation Reason:</strong></p>
                <p class="reason-text">${formValues.reason}</p>
              </div>
            `,
            confirmButtonColor: '#3085d6'
          });

          emit('update');
        }
      }
    } catch (error) {
      console.error('Error cancelling booking:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to cancel booking. Please try again.',
        confirmButtonColor: '#dc3545'
      });
    }
  };

  onMounted(async () => {
    await fetchBooking();
    await fetchPayments();
  });

  // Add this watch to debug the bookings data
  watch(bookings, (newValue) => {
    console.log('Bookings data changed:', newValue);
  }, { deep: true });

  // Update the computed property
  const showCancellationDetails = computed(() => {
    const isCancelled = bookings.value?.status?.toLowerCase() === 'cancelled';
    console.log('Is booking cancelled?', isCancelled);
    return isCancelled;
  });

  const getPackageInclusions = computed(() => {
    if (!packages.value?.package_inclusion) return [];
    
    try {
      if (Array.isArray(packages.value.package_inclusion)) {
        return packages.value.package_inclusion;
      }
      return JSON.parse(packages.value.package_inclusion);
    } catch (error) {
      console.error('Error parsing package inclusions:', error);
      return [];
    }
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
    align-items: flex-start;
    justify-content: center;
    z-index: 9999;
    padding: 7rem 1rem 1rem 1rem;
  }
  
  .modal-content {
    background: var(--modal-background, #fff);
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: calc(100vh - 10rem);
    overflow-y: auto;
    padding: 0;
    margin: 0 auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    scroll-behavior: smooth;
    &::-webkit-scrollbar {
      width: 8px;
    }
    &::-webkit-scrollbar-track {
      background: transparent;
    }
    &::-webkit-scrollbar-thumb {
      background: var(--border-color);
      border-radius: 4px;
    }
  }
  
  .modal-header {
    position: sticky;
    top: 0;
    background: var(--modal-background, #fff);
    z-index: 10;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    margin: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
    z-index: 11;
    position: relative;
  }
  
  .close-btn:hover {
    opacity: 0.8;
  }
  
  
  .form-section {
    background: var(--card-background);
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin: 0 2rem 2rem 2rem;
    padding: 2rem;
  }
  
  .form-section:first-of-type {
    margin-top: 2rem;
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
    border: 1px solid var(--border-color, #ddd);
    border-radius: 6px;
    font-size: 1rem;
    background: var(--input-background, #fff);
    color: var(--text-color);
  }
  
  textarea {
    resize: vertical;
  }
  
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1rem 2rem;
    background: var(--modal-background, #fff);
    border-top: 1px solid var(--border-color);
    position: sticky;
    bottom: 0;
    z-index: 10;
  }
  
  .form-section:last-of-type {
    margin-bottom: 0;
    padding-bottom: 1rem;
  }
  
  .cancel-booking-btn {
    padding: 0.75rem 1.5rem;
    background: var(--danger-color, #dc3545);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
  }
  
  .cancel-booking-btn:hover {
    background: var(--danger-dark-color, #c82333);
  }
  
  .cancel-btn {
    padding: 0.75rem 1.5rem;
    background: var(--primary-color, #3085d6);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
  }
  
  .cancel-btn:hover {
    background: var(--primary-dark-color, #2572b4);
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
  .details{
    margin-left: 1rem;
    margin-bottom: 1rem;
  }
  .details-image{
    width: 100%;
    height: 300px;
    border-radius: 10px;
    margin: 0 0 2rem 0;
    object-fit: cover;
    border: 1px solid var(--border-color, #ddd);
    display: block;
  }
  .payment-summary {
    background: var(--card-background, #f8f9fa);
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-color, #dee2e6);
  }

  .summary-item:last-child {
    border-bottom: none;
  }

  .text-success {
    color: #10B981;
  }

  .text-danger {
    color: #EF4444;
  }

  .payment-history {
    margin-top: 1rem;
  }

  .payment-history table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
  }

  .payment-history th,
  .payment-history td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color, #dee2e6);
  }

  .payment-history th {
    background-color: var(--table-header-bg, #f8f9fa);
    font-weight: 600;
  }

  .payment-history tr:hover {
    background-color: var(--table-hover-bg, #f8f9fa);
  }

  .no-payments {
    text-align: center;
    padding: 2rem;
    color: var(--text-secondary, #6b7280);
    background: var(--card-background, #f8f9fa);
    border-radius: 8px;
    margin-top: 1rem;
  }

  @media (max-width: 768px) {
    .payment-history {
      overflow-x: auto;
    }
    
    .payment-history table {
      min-width: 500px;
    }
  }

  .details h4 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    color: var(--text-muted, #666);
    font-weight: 500;
  }

  .details h4 span {
    color: var(--text-color, #333);
    font-weight: 400;
  }

  .event-type {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
    display: inline-block;
  }

  /* Event type styles from the existing codebase */
  .event-type.wedding {
    background: #e8f5e9;
    color: #2e7d32;
  }

  .event-type.debut {
    background: #fff3e0;
    color: #ef6c00;
  }

  .event-type.christening {
    background: #e3f2fd;
    color: #1565c0;
  }

  .event-type.party {
    background: #f3e5f5;
    color: #7b1fa2;
  }

  .event-type.other {
    background: #f5e6ff;
    color: #6200ea;
  }

  .cancel-booking-btn {
    padding: 0.75rem 1.5rem;
    background: var(--danger-color, #dc3545);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-right: 1rem;
  }

  .cancel-booking-btn:hover {
    background: var(--danger-dark-color, #c82333);
  }

  .section {
    padding: 2rem;
    border-bottom: 1px solid var(--border-color, #eee);
    margin-bottom: 0;
  }

  .section:last-child {
    border-bottom: none;
  }

  .section-title {
    font-size: 1.25rem;
    color: var(--text-color);
    margin-bottom: 1.5rem;
    text-align: left;
    font-weight: 600;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary-color);
    display: inline-block;
  }

  .details {
    margin: 0;
    max-width: none;
    text-align: left;
  }

  .details h4 {
    display: flex;
    align-items: baseline;
    justify-content: flex-start;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--text-muted, #666);
    font-weight: 500;
    line-height: 1.5;
  }

  .details h4 span {
    color: var(--text-color, #333);
    font-weight: 400;
  }

  .details-image {
    width: 100%;
    height: 300px;
    border-radius: 10px;
    margin: 0 0 2rem 0;
    object-fit: cover;
    border: 1px solid var(--border-color, #ddd);
    display: block;
  }

  .details ul {
    text-align: left;
    list-style: none;
    padding: 0;
    margin: 0.5rem 0 0 1.5rem;
  }

  .details ul li {
    margin-bottom: 0.5rem;
    position: relative;
    padding-left: 1rem;
  }

  .details ul li:before {
    content: "•";
    position: absolute;
    left: -1rem;
    color: var(--primary-color);
  }

  .payment-summary {
    background: var(--card-background, #f8f9fa);
    padding: 1.5rem;
    border-radius: 8px;
    margin: 1rem 0;
    border: 1px solid var(--border-color, #eee);
  }

  .summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-color, #eee);
  }

  .summary-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }

  .summary-item span:first-child {
    font-weight: 500;
    color: var(--text-muted, #666);
  }

  .payment-history table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .payment-history th {
    background-color: var(--table-header-bg, #f8f9fa);
    font-weight: 600;
    text-align: left;
    padding: 1rem;
    border-bottom: 2px solid var(--border-color, #eee);
  }

  .payment-history td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color, #eee);
  }

  .payment-history tr:last-child td {
    border-bottom: none;
  }

  .event-type {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
    min-width: 100px;
    text-align: center;
  }

  @media (max-width: 768px) {
    .section {
      padding: 1.5rem;
    }

    .details h4 {
      flex-direction: column;
      align-items: flex-start;
      gap: 0.25rem;
    }

    .details-image {
      width: 150px;
      height: 150px;
    }

    .payment-history {
      margin: 1rem -1.5rem;
    }
  }

  /* Add responsive adjustments for the image */
  @media (max-width: 768px) {
    .details-image {
      height: 200px; /* Slightly smaller height on mobile */
      border-radius: 8px; /* Slightly smaller border radius on mobile */
    }
  }

  /* Add this if you want to animate the image loading */
  .details-image {
    transition: opacity 0.3s ease;
  }

  .details-image:not([src]) {
    opacity: 0;
  }

  .details-image[src] {
    opacity: 1;
  } 

  /* Add these new styles */
  :deep(.feedback-form) {
    text-align: left;
    margin: 1rem 0;
  }

  :deep(.feedback-form label) {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-color);
  }

  :deep(.swal2-textarea) {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    min-height: 100px;
    margin: 0.5rem 0;
  }

  :deep(.cancellation-summary) {
    text-align: left;
    margin: 1rem 0;
  }

  :deep(.reason-text) {
    background: var(--warning-color-light);
    padding: 1rem;
    border-left: 4px solid var(--warning-color);
    border-radius: 4px;
    margin-top: 0.5rem;
    white-space: pre-wrap;
  }

  .status-badge.cancelled {
    background: var(--danger-color-light, #ffebee);
    color: var(--danger-color, #dc3545);
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
  }

  .cancellation-info {
    background: var(--background-color, #fff);
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .info-row {
    margin-bottom: 1.5rem;
  }

  .info-row:last-child {
    margin-bottom: 0;
  }

  .info-row h4 {
    color: var(--text-muted, #666);
    font-weight: 500;
    margin-bottom: 0.5rem;
  }

  .status-badge.cancelled {
    background: var(--danger-color-light, #ffebee);
    color: var(--danger-color, #dc3545);
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
  }

  .cancellation-reason {
    background: var(--warning-color-light, #fff3e0);
    border-left: 4px solid var(--warning-color, #ff9800);
    padding: 1rem;
    border-radius: 4px;
    color: var(--text-color);
    line-height: 1.5;
    white-space: pre-wrap;
    margin-top: 0.5rem;
  }

  .cancellation-reason.no-reason {
    background: var(--background-color-light, #f5f5f5);
    border-left-color: var(--text-muted, #666);
    color: var(--text-muted, #666);
    font-style: italic;
  }
  </style> 
