<template>
  <div class="booking-history" style="margin-top: 5rem;">
    <main class="history-content">
      <div class="page-header">
        <h1>My Booking History</h1>
        <div class="header-actions">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search bookings..."
              @input="handleSearch"
            />
          </div>
          <div class="filters">
            <select v-model="statusFilter">
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <select v-model="eventTypeFilter">
              <option value="">All Event Types</option>
              <option value="wedding">Wedding</option>
              <option value="debut">Debut</option>
              <option value="christening">Christening</option>
              <option value="party">Party</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th @click="sortBy('id')">
                Booking ID
                <i :class="['fas', getSortIcon('id')]"></i>
              </th>
              <th @click="sortBy('eventType')">
                Event Type
                <i :class="['fas', getSortIcon('eventType')]"></i>
              </th>
              <th @click="sortBy('eventDate')">
                Event Date
                <i :class="['fas', getSortIcon('eventDate')]"></i>
              </th>
              <th @click="sortBy('status')">
                Status
                <i :class="['fas', getSortIcon('status')]"></i>
              </th>
              <th @click="sortBy('amount')">
                Amount
                <i :class="['fas', getSortIcon('amount')]"></i>
              </th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="booking in paginatedBookings" :key="booking.id">
              <td>BOOKING ID: #{{ booking.id }}</td>
              <td>
                <span class="event-type" :class="booking.package.package_type.toLowerCase()">
                  {{ booking.package.package_type }}
                </span>
              </td>
              <td>
                <div class="date-info">
                  <span class="date">{{ formatDate(booking.event_date) }}</span>
                  <span v-if="booking.event_time" class="time">{{ formatTime(booking.event_time) }}</span>
                </div>
                <span v-if="booking.event_date && isNaN(new Date(booking.event_date).getTime())" class="invalid-date-warning">
                  Please contact admin to update event date
                </span>
              </td>
              <td>
                <span class="status text-capitalize" :class="booking.status.toLowerCase()">
                  {{ booking.status }}
                </span>
              </td>
              <td>₱{{ formatNumber(booking.total_price) }}</td>
              <td>
                <div class="action-buttons">
                  <button @click="viewBooking(booking)" class="btn-action view">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button 
                    v-if="booking.status.toLowerCase() === 'completed'"
                    @click="rateBooking(booking)" 
                    class="btn-action check"
                  >
                    <i class="fas fa-star"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination">
        <button 
          :disabled="currentPage === 1" 
          @click="changePage(currentPage - 1)"
          class="page-btn"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        <span class="page-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button 
          :disabled="currentPage === totalPages" 
          @click="changePage(currentPage + 1)"
          class="page-btn"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </main>

    <!-- Modals -->
    <BookingDetailsModal
      v-if="showBookingModal"
      :booking="selectedBooking"
      @close="showBookingModal = false"
    />

    <ConfirmationModal
      v-if="showConfirmModal"
      :title="'Cancel Booking'"
      :message="'Are you sure you want to cancel this booking? This action cannot be undone.'"
      @confirm="confirmCancelBooking"
      @close="showConfirmModal = false"
    />

    <RateBookingModal
      v-if="showRateModal"
      :booking="selectedBooking"
      @close="showRateModal = false"
      @rating-added="handleRatingAdded"
    />

    <ViewBookingsModal
      v-if="showViewModal"
      :booking="selectedBooking"
      @close="showViewModal = false"
    />

    <!-- Add floating chat button component -->
    <FloatingChatButton 
      v-if="isAuthenticated"
      :current-user-id="currentUserId"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import BookingDetailsModal from '@/components/bookings/BookingDetailsModal.vue';
import RateBookingModal from '@/components/bookings/RateBookingModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import ViewBookingsModal from '@/components/admin/ViewBookingsModal.vue';
import FloatingChatButton from '@/components/chat/FloatingChatButton.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';

const { user, token } = useAuth();
const router = useRouter();

// State
const bookings = ref([]);
const ratings = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const eventTypeFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const sortField = ref('eventDate');
const sortDirection = ref('desc');
const showBookingModal = ref(false);
const showConfirmModal = ref(false);
const showRateModal = ref(false);
const showViewModal = ref(false);
const selectedBooking = ref(null);

// Computed
const filteredBookings = computed(() => {
  return bookings.value.filter(booking => {
    const matchesSearch = searchQuery.value === '' || 
      booking.id.toString().includes(searchQuery.value.toLowerCase());
    
    const matchesStatus = statusFilter.value === '' || 
      booking.status.toLowerCase() === statusFilter.value.toLowerCase();
    
    const matchesEventType = eventTypeFilter.value === '' || 
      booking.package.package_type.toLowerCase() === eventTypeFilter.value.toLowerCase();
    
    return matchesSearch && matchesStatus && matchesEventType;
  });
});

const sortedBookings = computed(() => {
  const sorted = [...filteredBookings.value].sort((a, b) => {
    if (sortField.value === 'eventDate') {
      return new Date(a[sortField.value]) - new Date(b[sortField.value]);
    }
    if (sortField.value === 'amount') {
      return a[sortField.value] - b[sortField.value];
    }
    return a[sortField.value].localeCompare(b[sortField.value]);
  });
  
  return sortDirection.value === 'desc' ? sorted.reverse() : sorted;
});

const paginatedBookings = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return sortedBookings.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredBookings.value.length / itemsPerPage);
});

// Methods
const fetchBookings = async () => {
  try {
    const userInfo = JSON.parse(localStorage.getItem('user_info'));
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/bookings/user/${userInfo.id}`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    bookings.value = response.data.bookings;
    console.log('Bookings fetched:', bookings.value);
  } catch (error) {
    console.error('Error fetching bookings:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to fetch bookings. Please try again.'
    });
  }
};

const fetchRatings = async () => {
  try {
    const userInfo = JSON.parse(localStorage.getItem('user_info'));
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-ratings/${userInfo.id}`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    ratings.value = response.data.ratings;
  } catch (error) {
    console.error('Error fetching ratings:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to fetch ratings. Please try again.'
    });
  }
};

const handleSearch = () => {
  currentPage.value = 1;
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};

const getSortIcon = (field) => {
  if (sortField.value !== field) return 'fa-sort';
  return sortDirection.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
};

const changePage = (page) => {
  currentPage.value = page;
};

const formatDate = (date) => {
  if (!date) return 'No date set';
  try {
    const dateObj = new Date(date);
    if (isNaN(dateObj.getTime())) return 'Invalid date';
    
    return dateObj.toLocaleDateString('en-PH', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch (error) {
    console.error('Error formatting date:', error);
    return 'Invalid date';
  }
};

const formatTime = (time) => {
  if (!time) return '';
  try {
    // Check if time is in HH:mm:ss format
    if (/^\d{2}:\d{2}:\d{2}$/.test(time)) {
      const [hours, minutes] = time.split(':');
      const date = new Date();
      date.setHours(parseInt(hours, 10));
      date.setMinutes(parseInt(minutes, 10));
      
      return date.toLocaleTimeString('en-PH', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    }
    
    // If time includes date information
    const dateObj = new Date(`2000-01-01T${time}`);
    if (isNaN(dateObj.getTime())) return '';
    
    return dateObj.toLocaleTimeString('en-PH', {
      hour: '2-digit',
      minute: '2-digit',
      hour12: true
    });
  } catch (error) {
    console.error('Error formatting time:', error);
    return '';
  }
};

const formatNumber = (num) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const viewBooking = (booking) => {
  selectedBooking.value = booking;
  showViewModal.value = true;
};

const cancelBooking = (booking) => {
  selectedBooking.value = booking;
  showConfirmModal.value = true;
};

const rateBooking = (booking) => {
  const packid = booking.package.id;
  const userid = booking.user_id;
  const bookingid = booking.id;
  if(ratings.value.some(rating => rating.package_id === packid && rating.user_id === userid && rating.booking_id === bookingid)){
    Swal.fire({
      title: 'Error',
      text: 'You have already rated this booking',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  }else{
    selectedBooking.value = booking;
    showRateModal.value = true;
  }
};

const confirmCancelBooking = async () => {
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
      const response = await axios.post(
        `${import.meta.env.VITE_API_URL}/api/update-booking`,
        {
          id: selectedBooking.value.id,
          status: 'cancelled',
          cancellation_reason: formValues.reason,
          cancelled_at: new Date().toISOString()
        },
        {
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token.value}`
          }
        }
      );

      if (response.status === 200) {
        await Swal.fire({
          icon: 'success',
          title: 'Booking Cancelled',
          html: `
            <div class="cancellation-summary">
              <p>Your booking has been cancelled successfully.</p>
              <p><strong>Cancellation Reason:</strong></p>
              <p class="reason-text">${formValues.reason}</p>
            </div>
          `
        });
        await fetchBookings();
        showConfirmModal.value = false;
      }
    }
  } catch (error) {
    console.error('Error cancelling booking:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'Failed to cancel booking. Please try again.'
    });
  }
};

const handleRatingAdded = (updatedPackage) => {
  // Update the package data in the booking
  if (selectedBooking.value) {
    selectedBooking.value.package = {
      ...selectedBooking.value.package,
      rating: updatedPackage.rating,
      reviews_count: updatedPackage.reviews_count
    };
  }
  showRateModal.value = false;
};

// Add computed property for authentication
const isAuthenticated = computed(() => {
  return !!localStorage.getItem('token');
});

// Add currentUserId computed property
const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

onMounted(async () => {
  await fetchBookings();
  await fetchRatings();
});
</script>

<style scoped>
.booking-history {
  padding: 2rem;
  background: var(--background-color);
  min-height: 100vh;
}

.history-content {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-bar {
  position: relative;
  flex: 1;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.filters {
  display: flex;
  gap: 1rem;
}

.filters select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
  min-width: 150px;
}

.table-container {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow-x: auto;
  margin-bottom: 2rem;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th {
  padding: 1rem;
  text-align: left;
  color: var(--text-color);
  font-weight: 600;
  border-bottom: 2px solid var(--border-color);
  cursor: pointer;
  white-space: nowrap;
}

th i {
  margin-left: 0.5rem;
}

td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
  color: var(--text-color);
}

.event-type {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

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

.event-type.platinum {
  background: #e5e7f8;
  color: #3949ab;
}

.event-type.party {
  background: #f3e5f5;
  color: #7b1fa2;
}

.event-type.other {
  background: #fce4ec;
  color: #c2185b;
}

.date-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.time {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.status {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.status.pending {
  background: #fff3cd;
  color: #856404;
}

.status.preparing {
  background: #fff3e0;
  color: #ef6c00;
}

.status.confirmed {
  background: #e3f2fd;
  color: #1565c0;
}

.status.ongoing {
  background: #e0f7fa;
  color: #006064;
}

.status.completed {
  background: #e8f5e9;
  color: #2e7d32;
}

.status.cancelled {
  background: #ffebee;
  color: #c62828;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-action {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 36px;
  height: 36px;
}

.btn-action.view {
  background: var(--primary-color);
}

.btn-action.cancel {
  background: #dc3545;
  color: white;
}

.btn-action.cancel:hover {
  background: #c82333;
}

.btn-action.check {
  background: white;
  color: #ffc107;
  border: 1px solid #ffc107;
}

.btn-action.check:hover {
  background: #fff3cd;
}

.btn-action i {
  font-size: 1rem;
}

.text-capitalize {
  text-transform: capitalize;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: var(--text-color);
}

.invalid-date-warning {
  color: #dc2626;
  font-size: 0.75rem;
  display: block;
  margin-top: 0.25rem;
}

@media (max-width: 768px) {
  .booking-history {
    padding: 1rem;
  }

  .header-actions {
    flex-direction: column;
  }

  .search-bar {
    width: 100%;
  }

  .filters {
    width: 100%;
    flex-direction: column;
  }

  .filters select {
    width: 100%;
  }
}
</style> 