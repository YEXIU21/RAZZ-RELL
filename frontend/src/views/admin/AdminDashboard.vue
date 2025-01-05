<template>
  <div class="admin-dashboard">
    
    <main class="dashboard-content">
      <div class="overview-cards">
        <div class="card">
          <div class="card-info">
            <h3>Total Bookings</h3>
            <div class="card-value">{{ stats.totalBookings }}</div>
            <div class="card-trend" :class="{ up: stats.bookingTrend > 0 }">
              <i class="fas" :class="stats.bookingTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
              {{ Math.abs(stats.bookingTrend) }}%
            </div>
          </div>
          <div class="card-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
        </div>

        <div class="card">
          <div class="card-info">
            <h3>Revenue ({{ currentYear }})</h3>
            <div class="card-value">₱{{ formatNumber(stats.totalRevenue) }}</div>
            <div class="card-trend" :class="{ up: stats.revenueTrend > 0 }">
              <i class="fas" :class="stats.revenueTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
              {{ Math.abs(stats.revenueTrend) }}% 
            </div>
          </div>
          <div class="card-icon">
            <i class="fas fa-coins"></i>
          </div>
        </div>

        <div class="card">
          <div class="card-info">
            <h3>Active Users</h3>
            <div class="card-value">{{ stats.activeUsers }}</div>
            <div class="card-trend" :class="{ up: stats.userTrend > 0 }">
              <i class="fas" :class="stats.userTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
              {{ Math.abs(stats.userTrend) }}%
            </div>
          </div>
          <div class="card-icon">
            <i class="fas fa-users"></i>
          </div>
        </div>

        <div class="card">
          <div class="card-info">
            <h3>Satisfaction Rate</h3>
            <div class="card-value">{{ satisfactionRate }}%</div>
            <div class="card-trend" :class="{ up: satisfactionTrend > 0 }">
              <i class="fas" :class="satisfactionTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
              {{ Math.abs(satisfactionTrend) }}%
            </div>
          </div>
          <div class="card-icon">
            <i class="fas fa-smile"></i>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div class="chart-container">
          <h3>Revenue Overview ({{ currentYear }})</h3>
          <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-container">
          <h3>Bookings by Event Type</h3>
          <canvas id="eventTypeChart"></canvas>
        </div>
      </div>

      <!-- Recent Bookings -->
      <div class="recent-bookings">
        <div class="section-header">
          <h3>Recent Bookings</h3>
          <a href="/admin/bookings" class="btn-view-all">View All</a>
        </div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Event Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in recentBookings" :key="booking.id">
                <td>#{{ booking.id }}</td>
                <td>{{ booking.full_name }}</td>
                <td>{{ booking.package.package_type }}</td>
                <td>{{ formatDate(booking.event_date) }}</td>
                <td>
                  <span style="text-transform: capitalize;" :class="['status', booking.status.toLowerCase()]">
                    {{ booking.status }}
                  </span>
                </td>
                <td>₱{{ formatNumber(booking.package.package_price) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="quick-actions">
        <button @click="openAddPackage" v-if="user?.role === 'admin'" class="action-btn">
          <i class="fas fa-plus"></i>
          Add Package
        </button>
        <button @click="openReports" class="action-btn">
          <i class="fas fa-file-alt"></i>
          Generate Report
        </button>
        <button @click="openSettings" class="action-btn">
          <i class="fas fa-cog"></i>
          Settings
        </button>
      </div>
    </main>

    <!-- Modals -->
    <BookingDetailsModal
      v-if="showBookingModal"
      :booking="selectedBooking"
      @close="showBookingModal = false"
    />

    <AddPackageModal
      v-if="showAddPackageModal"
      @close="showAddPackageModal = false"
      @success="handlePackageAdded"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import BookingDetailsModal from '@/components/bookings/BookingDetailsModal.vue';
import AddPackageModal from '@/components/admin/AddPackageModal.vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const { token } = useAuth();
const stats = ref({
  totalBookings: 0,
  totalRevenue: '',
  activeUsers: 0,
  bookingTrend: 0,
  revenueTrend: 0,
  userTrend: 0
});

const satisfactionRate = ref(0);
const satisfactionTrend = ref(0);

const recentBookings = ref([]);
const currentYear = ref(new Date().getFullYear());
const user = ref(JSON.parse(localStorage.getItem('user_info')));
const showBookingModal = ref(false);
const showAddPackageModal = ref(false);
const selectedBooking = ref(null);

const router = useRouter();

const updateInterval = ref(null);

// Chart data
const revenueData = {
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  datasets: [{
    label: 'Revenue',
    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    borderColor: '#4CAF50',
    tension: 0.4
  }]
};

const eventTypeData = {
  labels: ['Wedding', 'Debut', 'Christening', 'Party', 'Other'],
  datasets: [{
    data: [],
    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
  }]
};

const formatNumber = (num) => {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Fetch monthly revenue data
const fetchMonthlyRevenue = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-monthly-revenue`);
    if (response.data.success) {
      revenueData.datasets[0].data = response.data.data;
      if (revenueChart) {
        revenueChart.update();
      }
    }
  } catch (error) {
    console.error('Error fetching monthly revenue:', error);
  }
};

let revenueChart = null;
let eventTypeChart = null;

const fetchBookings = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-bookings`);
    
    // Get today and yesterday dates
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    // Filter bookings for today and yesterday
    const todayBookings = response.data.filter(booking => {
      const bookingDate = new Date(booking.created_at);
      return bookingDate.toDateString() === today.toDateString();
    });

    const yesterdayBookings = response.data.filter(booking => {
      const bookingDate = new Date(booking.created_at);
      return bookingDate.toDateString() === yesterday.toDateString();
    });

    // Calculate total bookings and trend
    stats.value.totalBookings = response.data.length;
    if (yesterdayBookings.length > 0) {
      const bookingChange = ((todayBookings.length - yesterdayBookings.length) / yesterdayBookings.length) * 100;
      stats.value.bookingTrend = Math.round(bookingChange);
    } else if (todayBookings.length > 0) {
      stats.value.bookingTrend = 100; // 100% increase if we only have today's bookings
    } else {
      stats.value.bookingTrend = 0;
    }

    // Calculate revenue and trend
    const todayRevenue = todayBookings.reduce((total, booking) => {
      return total + parseFloat(booking.package.package_price);
    }, 0);

    const yesterdayRevenue = yesterdayBookings.reduce((total, booking) => {
      return total + parseFloat(booking.package.package_price);
    }, 0);

    stats.value.totalRevenue = response.data.reduce((total, booking) => {
      return total + parseFloat(booking.package.package_price);
    }, 0);

    if (yesterdayRevenue > 0) {
      const revenueChange = ((todayRevenue - yesterdayRevenue) / yesterdayRevenue) * 100;
      stats.value.revenueTrend = Math.round(revenueChange);
    } else if (todayRevenue > 0) {
      stats.value.revenueTrend = 100;
    } else {
      stats.value.revenueTrend = 0;
    }

    // Calculate bookings by event type
    const standardEventTypes = ['Wedding', 'Debut', 'Christening', 'Party'];
    const eventTypeCounts = response.data.reduce((acc, booking) => {
      const type = booking.package.package_type;
      // Check if it's a standard event type
      if (standardEventTypes.includes(type)) {
        acc[type] = (acc[type] || 0) + 1;
      } else {
        // Count as "Other" if it's a custom event type
        acc['Other'] = (acc['Other'] || 0) + 1;
      }
      return acc;
    }, {});

    // Update event type chart data
    eventTypeData.datasets[0].data = [
      eventTypeCounts['Wedding'] || 0,
      eventTypeCounts['Debut'] || 0,
      eventTypeCounts['Christening'] || 0,
      eventTypeCounts['Party'] || 0,
      eventTypeCounts['Other'] || 0
    ];

    if (eventTypeChart) {
      eventTypeChart.update();
    }

    recentBookings.value = response.data;
  } catch (error) {
    console.error('Error fetching bookings:', error);
  }
};

const fetchUser = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-users`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    });

    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    // Filter active users for today and yesterday
    const todayUsers = response.data.filter(user => {
      const userDate = new Date(user.created_at);
      return user.status === 'active' && 
             userDate.toDateString() === today.toDateString();
    });

    const yesterdayUsers = response.data.filter(user => {
      const userDate = new Date(user.created_at);
      return user.status === 'active' && 
             userDate.toDateString() === yesterday.toDateString();
    });

    // Update active users count
    stats.value.activeUsers = response.data.filter(user => user.status === 'active').length;

    // Calculate user trend
    if (yesterdayUsers.length > 0) {
      const userChange = ((todayUsers.length - yesterdayUsers.length) / yesterdayUsers.length) * 100;
      stats.value.userTrend = Math.round(userChange);
    } else if (todayUsers.length > 0) {
      stats.value.userTrend = 100;
    } else {
      stats.value.userTrend = 0;
    }
  } catch (error) {
    console.error('Error fetching users:', error);
  }
};

const fetchSatisfactionRate = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-ratings`);
    
    if (response.data) {
      const ratings = response.data;
      
      if (ratings.length > 0) {
        // Calculate overall satisfaction rate
        const totalPossible = ratings.length * 5;
        const actualTotal = ratings.reduce((sum, rating) => sum + parseInt(rating.rating), 0);
        satisfactionRate.value = Math.round((actualTotal / totalPossible) * 100);

        // Get today and yesterday dates
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        // Filter ratings for today and yesterday
        const todayRatings = ratings.filter(r => {
          const ratingDate = new Date(r.created_at);
          return ratingDate.toDateString() === today.toDateString();
        });

        const yesterdayRatings = ratings.filter(r => {
          const ratingDate = new Date(r.created_at);
          return ratingDate.toDateString() === yesterday.toDateString();
        });

        if (yesterdayRatings.length > 0) {
          const todayAvg = todayRatings.reduce((sum, r) => sum + parseInt(r.rating), 0) / todayRatings.length;
          const yesterdayAvg = yesterdayRatings.reduce((sum, r) => sum + parseInt(r.rating), 0) / yesterdayRatings.length;
          satisfactionTrend.value = Math.round(((todayAvg - yesterdayAvg) / yesterdayAvg) * 100);
        } else if (todayRatings.length > 0) {
          satisfactionTrend.value = 100;
        } else {
          satisfactionTrend.value = 0;
        }
      } else {
        satisfactionRate.value = 0;
        satisfactionTrend.value = 0;
      }
    }
  } catch (error) {
    console.error('Error fetching satisfaction rate:', error);
  }
};

const viewBooking = (booking) => {
  selectedBooking.value = booking;
  showBookingModal.value = true;
};

const updateStatus = async (booking) => {
  // Implementation for updating booking status
};

const openAddPackage = () => {
  showAddPackageModal.value = true;
};

const handlePackageAdded = () => {
  showAddPackageModal.value = false;
  // Refresh dashboard data
  // fetchDashboardData();
};

const openSettings = () => {
  router.push('/admin/settings');
};

const openReports = () => {
  // Create a new window for the report
  const printWindow = window.open('', '_blank');
  
  // Get current date for the report header
  const currentDate = new Date().toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  // Create the report content
  const reportContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Dashboard Report - ${currentDate}</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          line-height: 1.6;
          padding: 20px;
          max-width: 1200px;
          margin: 0 auto;
        }
        .header {
          text-align: center;
          margin-bottom: 30px;
          padding-bottom: 20px;
          border-bottom: 2px solid #333;
        }
        .section {
          margin-bottom: 30px;
        }
        .section-title {
          font-size: 18px;
          font-weight: bold;
          margin-bottom: 15px;
          color: #333;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 20px;
        }
        th, td {
          border: 1px solid #ddd;
          padding: 12px;
          text-align: left;
        }
        th {
          background-color: #f5f5f5;
        }
        .stats-grid {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 20px;
          margin-bottom: 30px;
        }
        .stat-item {
          padding: 15px;
          background: #f9f9f9;
          border-radius: 8px;
        }
        .chart-section {
          page-break-before: always;
        }
        @media print {
          body {
            padding: 0;
          }
          .chart-section {
            page-break-before: always;
          }
        }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>Dashboard Report</h1>
        <p>Generated on ${currentDate}</p>
      </div>

      <div class="section">
        <div class="section-title">Overview Statistics</div>
        <div class="stats-grid">
          <div class="stat-item">
            <h3>Total Bookings</h3>
            <p>${stats.value.totalBookings}</p>
            <small>Trend: ${stats.value.bookingTrend}%</small>
          </div>
          <div class="stat-item">
            <h3>Total Revenue</h3>
            <p>₱${formatNumber(stats.value.totalRevenue)}</p>
            <small>Trend: ${stats.value.revenueTrend}%</small>
          </div>
          <div class="stat-item">
            <h3>Active Users</h3>
            <p>${stats.value.activeUsers}</p>
            <small>Trend: ${stats.value.userTrend}%</small>
          </div>
          <div class="stat-item">
            <h3>Satisfaction Rate</h3>
            <p>${satisfactionRate.value}%</p>
            <small>Trend: ${satisfactionTrend.value}%</small>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-title">Recent Bookings</div>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Event Type</th>
              <th>Date</th>
              <th>Status</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            ${recentBookings.value.map(booking => `
              <tr>
                <td>#${booking.id}</td>
                <td>${booking.full_name}</td>
                <td>${booking.package.package_type}</td>
                <td>${formatDate(booking.event_date)}</td>
                <td>${booking.status}</td>
                <td>₱${formatNumber(booking.package.package_price)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      <div class="section chart-section">
        <div class="section-title">Monthly Revenue Data</div>
        <table>
          <thead>
            <tr>
              <th>Month</th>
              <th>Revenue</th>
            </tr>
          </thead>
          <tbody>
            ${revenueData.labels.map((month, index) => `
              <tr>
                <td>${month}</td>
                <td>₱${formatNumber(revenueData.datasets[0].data[index])}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>

      <div class="section">
        <div class="section-title">Event Type Distribution</div>
        <table>
          <thead>
            <tr>
              <th>Event Type</th>
              <th>Number of Bookings</th>
            </tr>
          </thead>
          <tbody>
            ${eventTypeData.labels.map((type, index) => `
              <tr>
                <td>${type}</td>
                <td>${eventTypeData.datasets[0].data[index]}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    </body>
    </html>
  `;

  // Write the content to the new window and print
  printWindow.document.write(reportContent);
  printWindow.document.close();
  
  // Wait for images and styles to load
  printWindow.onload = function() {
    printWindow.print();
    // Optional: Close the window after printing
    // printWindow.close();
  };
};

onMounted(async () => {
  await Promise.all([
    fetchMonthlyRevenue(),
    fetchBookings(),
    fetchUser(),
    fetchSatisfactionRate()
  ]);

  // Initialize Revenue Chart
  const revenueCtx = document.querySelector('#revenueChart')?.getContext('2d');
  if (revenueCtx) {
    revenueChart = new Chart(revenueCtx, {
      type: 'line',
      data: revenueData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return '₱' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              display: true,
              color: 'rgba(0, 0, 0, 0.1)'
            },
            ticks: {
              callback: function(value) {
                return '₱' + value.toLocaleString();
              }
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // Initialize Event Type Chart
  const eventTypeCtx = document.querySelector('#eventTypeChart')?.getContext('2d');
  if (eventTypeCtx) {
    eventTypeChart = new Chart(eventTypeCtx, {
      type: 'doughnut',
      data: eventTypeData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              padding: 20,
              usePointStyle: true,
              font: {
                size: 12
              }
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.dataset.data.reduce((acc, curr) => acc + curr, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
        cutout: '60%'
      }
    });
  }

  // Set up auto-refresh interval (every 5 minutes)
  updateInterval.value = setInterval(async () => {
    await fetchMonthlyRevenue();
  }, 5 * 60 * 1000);
});

// Add cleanup on component unmount
onUnmounted(() => {
  if (updateInterval.value) {
    clearInterval(updateInterval.value);
  }
  if (revenueChart) {
    revenueChart.destroy();
  }
  if (eventTypeChart) {
    eventTypeChart.destroy();
  }
});
</script>

<style scoped>
.admin-dashboard {
  display: flex;
  background: var(--background-color);
  position: relative; /* Add this */
}

.dashboard-content {
  flex: 1;
  padding: 2rem;
  transition: margin-left 0.3s ease; /* Add smooth transition */
}

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.card {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-info h3 {
  color: var(--text-color);
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.card-value {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.card-trend {
  font-size: 0.9rem;
  color: #f44336;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.card-trend.up {
  color: #4CAF50;
}

.card-icon {
  font-size: 2rem;
  color: var(--primary-color);
  opacity: 0.8;
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-container {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
}

.chart-container canvas {
  margin-top: 10px;
}

.chart-container h3 {
  color: var(--text-color);
  margin-bottom: 1px;
}

.recent-bookings {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.btn-view-all {
  padding: 0.5rem 1rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  font-weight: 600;
  color: var(--text-color);
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

.status.confirmed {
  background: #d4edda;
  color: #155724;
}

.status.cancelled {
  background: #f8d7da;
  color: #721c24;
}

.btn-action {
  background: none;
  border: none;
  color: #3b82f6;
  cursor: pointer;
  padding: 0.25rem;
  margin: 0 0.25rem;
  transition: color 0.3s ease;
}

.btn-action:hover {
  color: #2563eb;
}

.quick-actions {
  display: flex;
  gap: 1rem;
}

.action-btn {
  padding: 0.75rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.3s ease;
}

.action-btn:hover {
  background: #2563eb;
}

@media (max-width: 768px) {
  .dashboard-content {
    margin-left: 0;
    padding: 1rem;
  }

  .charts-section {
    grid-template-columns: 1fr;
  }

  .quick-actions {
    flex-direction: column;
  }

  .action-btn {
    width: 100%;
  }
}
</style> 