<template>
  <div class="bookings-management">
    <main class="management-content">
      <div class="page-header">
        <h1>Bookings Management</h1>
        <div class="header-actions">
          <div class="archive-toggle">
            <button 
              class="btn-archive" 
              :class="{ active: showArchived }"
              @click="toggleArchived"
            >
              <i class="fas fa-archive"></i>
              {{ showArchived ? 'Show Active' : 'Show Archived' }}
            </button>
          </div>
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
            <button class="filter-btn" @click="applyFilters">
              <i class="fas fa-filter"></i>
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <div class="bookings-table-container">
        <table class="bookings-table">
          <thead>
            <tr>
              <th class="booking-id-col">
                <div class="th-content" @click="sortBy('id')">
                  Booking ID
                  <i class="fas" :class="getSortIcon('id')"></i>
                </div>
              </th>
              <th class="client-col">
                <div class="th-content" @click="sortBy('fullName')">
                  Client
                  <i class="fas" :class="getSortIcon('fullName')"></i>
                </div>
              </th>
              <th class="event-type-col">
                <div class="th-content" @click="sortBy('eventType')">
                  Event Type
                  <i class="fas" :class="getSortIcon('eventType')"></i>
                </div>
              </th>
              <th class="event-date-col">
                <div class="th-content" @click="sortBy('eventDate')">
                  Event Date
                  <i class="fas" :class="getSortIcon('eventDate')"></i>
                </div>
              </th>
              <th class="status-col">
                <div class="th-content" @click="sortBy('status')">
                  Status
                  <i class="fas" :class="getSortIcon('status')"></i>
                </div>
              </th>
              <th class="package-col">
                <div class="th-content" @click="sortBy('packageName')">
                  Package Name
                  <i class="fas" :class="getSortIcon('packageName')"></i>
                </div>
              </th>
              <th class="actions-col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="booking in paginatedBookings" :key="booking.id">
              <td class="booking-id-col">{{ booking.id }}</td>
              <td class="client-col">
                <div class="client-info">
                  <div class="client-details">
                    <span class="client-name">{{ booking.full_name }}</span>
                    <span class="client-email">{{ booking.email }}</span>
                  </div>
                </div>
              </td>
              <td class="event-type-col">
                <span class="event-type" :class="booking.package?.package_type?.toLowerCase()">
                  {{ booking.package?.package_type }}
                </span>
              </td>
              <td class="event-date-col">
                <div class="date-info">
                  <span>{{ formatDate(booking.event_date) }}</span>
                  <span class="time">{{ formatTime(booking.event_time) }}</span>
                </div>
              </td>
              <td class="status-col">
                <span class="status" :class="booking.status?.toLowerCase()">
                  {{ booking.status }}
                </span>
              </td>
              <td class="package-col">{{ booking.package?.package_name }}</td>
              <td class="actions-col">
                <div class="action-buttons">
                  <button class="btn-action view" @click="viewBooking(booking)" title="View Details">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button 
                    class="btn-action edit" 
                    @click="editBooking(booking)"
                    :disabled="booking.status === 'completed'"
                    :title="booking.status === 'completed' ? 'Cannot edit completed bookings' : 'Edit Booking'"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    class="btn-action delete"
                    @click="confirmDelete(booking)"
                    title="Archive Booking"
                  >
                    <i class="fas fa-archive"></i>
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

    <EditBookingModal
      v-if="showEditModal"
      :booking="selectedBooking"
      @close="showEditModal = false"
      @update="handleBookingUpdate"
    />

    <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Booking Cancellation Feedback</h2>
          <button class="close-btn" @click="showConfirmModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="feedback-form">
          <p class="feedback-intro">Please help us improve our services by providing feedback before cancellation:</p>
          
          <div class="form-group">
            <label>How satisfied were you with our service?</label>
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
            <label>Primary reason for cancellation</label>
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
            <label>Additional Comments</label>
            <textarea
              v-model="feedbackMessage"
              placeholder="Please share any additional feedback or suggestions..."
              rows="4"
            ></textarea>
          </div>

          <div class="form-group">
            <label>Would you consider using our services in the future?</label>
            <div class="radio-group">
              <label>
                <input type="radio" v-model="wouldReturn" value="yes" />
                Yes
              </label>
              <label>
                <input type="radio" v-model="wouldReturn" value="maybe" />
                Maybe
              </label>
              <label>
                <input type="radio" v-model="wouldReturn" value="no" />
                No
              </label>
            </div>
          </div>

          <div class="modal-actions">
            <button class="btn-secondary" @click="showConfirmModal = false">
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
import BookingDetailsModal from '@/components/admin/ViewBookingsModal.vue';
import EditBookingModal from '@/components/admin/EditBookingModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import FloatingChatButton from '@/components/chat/FloatingChatButton.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const { token } = useAuth();

// State
const bookings = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const eventTypeFilter = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const sortField = ref('eventDate');
const sortDirection = ref('desc');
const showBookingModal = ref(false);
const showEditModal = ref(false);
const showConfirmModal = ref(false);
const selectedBooking = ref(null);
const satisfaction = ref(0);
const cancellationReason = ref('');
const otherReason = ref('');
const feedbackMessage = ref('');
const wouldReturn = ref('');
const showArchived = ref(false);

// Computed
const filteredBookings = computed(() => {
  let filtered = [...bookings.value];
  
  // Apply status filter
  if (statusFilter.value) {
    filtered = filtered.filter(booking => booking.status === statusFilter.value);
  }

  // Apply event type filter
  if (eventTypeFilter.value) {
    filtered = filtered.filter(booking => booking.package?.package_type?.toLowerCase() === eventTypeFilter.value.toLowerCase());
  }

  // Apply search filter if there's a search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(booking => 
      booking.full_name?.toLowerCase().includes(query) ||
      booking.email?.toLowerCase().includes(query) ||
      booking.venue_name?.toLowerCase().includes(query) ||
      booking.package?.package_name?.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

const sortedBookings = computed(() => {
  return [...filteredBookings.value].sort((a, b) => {
    let aValue, bValue;

    switch (sortField.value) {
      case 'id':
        aValue = a.id;
        bValue = b.id;
        break;
      case 'fullName':
        aValue = a.full_name?.toLowerCase();
        bValue = b.full_name?.toLowerCase();
        break;
      case 'eventType':
        aValue = a.package?.package_type?.toLowerCase();
        bValue = b.package?.package_type?.toLowerCase();
        break;
      case 'eventDate':
        aValue = new Date(a.event_date);
        bValue = new Date(b.event_date);
        break;
      case 'status':
        aValue = a.status?.toLowerCase();
        bValue = b.status?.toLowerCase();
        break;
      case 'packageName':
        aValue = a.package?.package_name?.toLowerCase();
        bValue = b.package?.package_name?.toLowerCase();
        break;
      default:
        return 0;
    }

    // Handle null/undefined values
    if (aValue === undefined || aValue === null) return 1;
    if (bValue === undefined || bValue === null) return -1;
    if (aValue === bValue) return 0;

    // Sort based on direction
    return sortDirection.value === 'asc' 
      ? (aValue < bValue ? -1 : 1)
      : (aValue < bValue ? 1 : -1);
  });
});

const paginatedBookings = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return sortedBookings.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
  return Math.ceil(sortedBookings.value.length / itemsPerPage);
});

// Add computed property for authentication
const isAuthenticated = computed(() => {
  return !!localStorage.getItem('token');
});

// Add currentUserId computed property
const currentUserId = computed(() => {
  const userInfo = JSON.parse(localStorage.getItem('user_info') || '{}');
  return userInfo.id;
});

// Add this computed property
const isValidFeedback = computed(() => {
  return satisfaction.value > 0 && cancellationReason.value;
});

// Methods
const fetchBookings = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-bookings`);
    bookings.value = response.data;
  } catch (error) {
    console.error('Error fetching bookings:', error);
  }
};

const handleSearch = () => {
  currentPage.value = 1;
};

const applyFilters = () => {
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
  showBookingModal.value = true;
};

const editBooking = (booking) => {
  selectedBooking.value = booking;
  showEditModal.value = true;
};

const cancelBooking = (booking) => {
  selectedBooking.value = booking;
  showConfirmModal.value = true;
};

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
      booking_id: selectedBooking.value.id,
      satisfaction_rating: satisfaction.value,
      cancellation_reason: cancellationReason.value === 'other' ? otherReason.value : cancellationReason.value,
      feedback_message: feedbackMessage.value,
      would_return: wouldReturn.value
    });

    // Then cancel the booking
    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/bookings/${selectedBooking.value.id}/cancel`,
      {
        status: 'cancelled',
        feedback_submitted: true
      }
    );

    if (response.status === 200) {
      showConfirmModal.value = false;
      
      // Reset form
      satisfaction.value = 0;
      cancellationReason.value = '';
      otherReason.value = '';
      feedbackMessage.value = '';
      wouldReturn.value = '';

      await fetchBookings();

      Swal.fire({
        icon: 'success',
        title: 'Thank You for Your Feedback',
        text: 'Your booking has been cancelled and your feedback has been recorded.'
      });
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

const handleBookingUpdate = async () => {
  await fetchBookings();
  showEditModal.value = false;
};

const confirmDelete = async (booking) => {
  try {
    const result = await Swal.fire({
      title: 'Are you sure?',
      text: "This booking will be moved to archives",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, archive it!'
    });

    if (result.isConfirmed) {
      const response = await axios.post(
        `${import.meta.env.VITE_API_URL}/api/bookings/${booking.id}/archive`,
        {},
        {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        }
      );

      if (response.data.status === 200) {
        Swal.fire('Archived!', 'Booking has been archived.', 'success');
        await fetchBookings();
      }
    }
  } catch (error) {
    console.error('Error archiving booking:', error);
    Swal.fire('Error!', 'Failed to archive booking.', 'error');
  }
};

const toggleArchived = () => {
  showArchived.value = !showArchived.value;
};

onMounted(async () => {
  await fetchBookings();
});
</script>

<style scoped>
.bookings-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 1.5rem 2rem;
  margin-left: 250px;
  max-width: 1600px;
  margin: 0 auto;
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.header-actions {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1.5rem;
  align-items: start;
}

.search-bar {
  position: relative;
  max-width: 400px;
}

.search-bar i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.95rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: border-color 0.2s;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  outline: none;
}

.filters {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.filters select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  color: var(--text-color);
  min-width: 140px;
  font-size: 0.95rem;
}

.filter-btn {
  padding: 0.75rem 1.25rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  transition: background-color 0.2s;
}

.filter-btn:hover {
  background: var(--primary-color-dark, #1a56db);
}

.bookings-table-container {
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  margin-bottom: 1.5rem;
}

.bookings-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.th-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.th-content:hover {
  background: rgba(0, 0, 0, 0.05);
}

th {
  padding: 1rem 0;
  text-align: left;
  font-weight: 600;
  color: var(--text-color);
  background: var(--table-header-background, rgba(0, 0, 0, 0.02));
  border-bottom: 2px solid var(--border-color);
}

td {
  padding: 1rem 0;
  vertical-align: middle;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

tr:last-child td {
  border-bottom: none;
}

.client-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  max-width: 250px;
  padding-right: 1rem;
}

.client-details {
  display: flex;
  flex-direction: column;
}

.client-name {
  font-weight: 500;
  color: var(--text-color);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.client-email {
  font-size: 0.85rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.event-type {
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
  margin-left: 0;
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

.event-type.party {
  background: #f3e5f5;
  color: #7b1fa2;
}

.event-type.other {
  background: #f5e6ff;
  color: #6200ea;
}

.date-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding-left: 0;
}

.time {
  font-size: 0.875rem;
  color: var(--text-muted);
}

.status {
  padding: 0.35rem 0.5rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
  min-width: 80px;
  margin: 0 auto;
}

.status.pending {
  background: #fff3cd;
  color: #856404;
}

.status.completed {
  background: #e8f5e9;
  color: #2e7d32;
}

.status.cancelled {
  background: #ffebee;
  color: #c62828;
}

.status.confirmed {
  background: #e3f2fd;
  color: #1565c0;
}

.status.ongoing {
  background: #e0f7fa;
  color: #006064;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-action {
  padding: 0.5rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, opacity 0.2s;
}

.btn-action.view {
  background-color: #3882F6;
}

.btn-action.edit {
  background-color: #F59E0B;
}

.btn-action:hover {
  transform: translateY(-1px);
  opacity: 0.9;
}

.btn-action:active {
  transform: translateY(0);
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
  background: var(--card-background);
  padding: 1rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:not(:disabled):hover {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: var(--text-color);
  font-weight: 500;
}

@media (max-width: 1200px) {
  .header-actions {
    grid-template-columns: 1fr;
  }
  
  .filters {
    flex-wrap: wrap;
  }
  
  .filters select {
    flex: 1;
    min-width: 120px;
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .page-header {
    padding: 1rem;
  }

  .header-actions {
    gap: 1rem;
  }

  .search-bar {
    max-width: none;
  }

  .filters {
    width: 100%;
  }

  .filter-btn {
    width: 100%;
    justify-content: center;
  }

  .bookings-table-container {
    border-radius: 8px;
  }

  th, td {
    padding: 0.75rem 1rem;
  }
}

.bookings-table th:last-child {
  text-align: center;
}

.action-btn.chat {
  background: #4F46E5;
  color: white;
}

.action-btn.chat:hover {
  background: #4338CA;
}

.cancellation-form {
  padding: 1rem 0;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--text-color);
}

.form-group select,
.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
}

.cancellation-message {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  color: var(--text-color);
  resize: vertical;
  min-height: 100px;
  font-family: inherit;
}

.form-group select:focus,
.form-group input:focus,
.cancellation-message:focus {
  outline: none;
  border-color: var(--primary-color);
}

.invalid-date-warning {
  color: #dc2626;
  font-size: 0.75rem;
  display: block;
  margin-top: 0.25rem;
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
  color: #ddd;
  font-size: 1.5rem;
}

.star.active {
  color: #ffc107;
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
  background: white;
  cursor: pointer;
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

.btn-action.delete {
  background: #dc2626;
  color: white;
}

.btn-action.delete:hover {
  background: #b91c1c;
}

.btn-archive {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  color: var(--text-color);
  transition: all 0.2s;
}

.btn-archive:hover {
  background: var(--hover-background);
}

.btn-archive.active {
  background: var(--error-color);
  color: white;
  border-color: var(--error-color);
}

.archive-toggle {
  margin-right: 1rem;
}

.bookings-table td {
  padding: 1rem 0;
  vertical-align: middle;
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.bookings-table th {
  padding: 1rem 0;
  font-weight: 600;
  text-align: left;
  background: var(--table-header-background);
  color: var(--text-color);
}

.bookings-table th.booking-id-col,
.bookings-table td.booking-id-col {
  width: 100px;
  padding-left: 1.5rem;
  text-align: center;
}

.bookings-table th.client-col,
.bookings-table td.client-col {
  width: 250px;
  padding-left: 2.5rem;
}

.bookings-table th.event-type-col,
.bookings-table td.event-type-col {
  width: 150px;
  padding-left: 2rem;
  text-align: left;
}

.bookings-table th.event-date-col,
.bookings-table td.event-date-col {
  width: 160px;
  padding-left: 2rem;
  text-align: left;
}

.bookings-table th.status-col,
.bookings-table td.status-col {
  width: 110px;
  padding: 0;
  text-align: center;
}

.bookings-table th.package-col,
.bookings-table td.package-col {
  width: 200px;
  padding-left: 4rem;
  text-align: left;
}

.bookings-table th.actions-col,
.bookings-table td.actions-col {
  width: 150px;
  text-align: center;
  padding-left: 0;
  padding-right: 0;
}

.client-col .th-content,
.event-type-col .th-content,
.event-date-col .th-content {
  justify-content: flex-start;
  padding-left: 0;
}

.status-col .th-content {
  justify-content: center;
  padding: 0;
}

.package-col .th-content {
  justify-content: flex-start;
  padding-left: 0;
}

.date-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding-left: 0;
}

.status {
  padding: 0.35rem 0.5rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 500;
  text-align: center;
  display: inline-block;
  min-width: 80px;
  margin: 0 auto;
}
</style> 