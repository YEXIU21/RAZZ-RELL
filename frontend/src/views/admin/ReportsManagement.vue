<template>
  <div class="reports-management">
    <main class="management-content">
      <div class="page-header">
        <h1>Reports Management</h1>
        <button class="generate-btn" @click="generateReport">
          <i class="fas fa-file-export"></i>
          Generate Report
        </button>
      </div>

      <div class="reports-grid">
        <!-- Revenue Reports -->
        <div class="report-card">
          <div class="report-header">
            <h3>Revenue Overview</h3>
            <div class="period-selector">
              <button 
                v-for="period in periods" 
                :key="period"
                :class="['period-btn', { active: selectedPeriod === period }]"
                @click="selectedPeriod = period">
                {{ period }}
              </button>
            </div>
          </div>
          
          <div class="chart-container">
            <canvas id="revenueChart"></canvas>
          </div>

          <div class="stats-grid">
            <div class="stat-item">
              <span class="label">Total Revenue</span>
              <span class="value">₱{{ stats.totalRevenue.toLocaleString() }}</span>
              <span class="trend up">
                <i class="fas fa-arrow-up"></i>
                {{ stats.revenueTrend }}%
              </span>
            </div>
            <div class="stat-item">
              <span class="label">Average Order Value</span>
              <span class="value">₱{{ stats.averageOrderValue.toLocaleString() }}</span>
              <span class="trend up">
                <i class="fas fa-arrow-up"></i>
                {{ stats.bookingTrend }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Bookings Reports -->
        <div class="report-card">
          <div class="report-header">
            <h3>Bookings by Event Type</h3>
          </div>
          
          <div class="chart-container">
            <canvas id="bookingsChart"></canvas>
          </div>

          <div class="stats-grid">
            <div class="stat-item">
              <span class="label">Total Bookings</span>
              <span class="value">{{ stats.totalBookings }}</span>
              <span class="trend up">
                <i class="fas fa-arrow-up"></i>
                {{ stats.bookingTrend }}%
              </span>
            </div>
            <div class="stat-item">
              <span class="label">Completion Rate</span>
              <span class="value">{{ stats.completionRate.toFixed(2) }}%</span>
              <span class="trend up">
                <i class="fas fa-arrow-up"></i>
                {{ stats.bookingTrend }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Customer Reports -->
        <div class="report-card">
          <div class="report-header">
            <h3>Customer Analytics</h3>
          </div>
          
          <div class="chart-container">
            <canvas id="customerChart"></canvas>
          </div>

          <div class="stats-grid">
            <div class="stat-item">
              <span class="label">Active Users</span>
              <span class="value">{{ activeUsersCount }}</span>
              <span class="trend" :class="{ 'up': userTrend > 0, 'down': userTrend < 0 }">
                <i class="fas" :class="userTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                {{ Math.abs(userTrend) }}%
              </span>
            </div>
            <div class="stat-item">
              <span class="label">Customer Satisfaction</span>
              <span class="value">{{ customerSatisfaction }}%</span>
              <span class="trend" :class="{ 'up': satisfactionTrend > 0, 'down': satisfactionTrend < 0 }">
                <i class="fas" :class="satisfactionTrend > 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                {{ Math.abs(satisfactionTrend) }}%
              </span>
            </div>
          </div>
        </div>

        <!-- Popular Packages -->
        <div class="report-card">
          <div class="report-header">
            <h3>Popular Packages</h3>
          </div>

          <div class="packages-list">
            <div v-for="pkg in popularPackages" :key="pkg.package_name" class="package-item">
              <div class="package-info">
                <span class="package-name">{{ pkg.package_name }}</span>
                <span :class="['event-type', pkg.event_type.toLowerCase()]">
                  {{ pkg.event_type }}
                </span>
              </div>
              <div class="package-stats">
                <div class="stat">
                  <span class="label">Bookings</span>
                  <span class="value">{{ pkg.bookings }}</span>
                </div>
                <div class="stat">
                  <span class="label">Revenue</span>
                  <span class="value">{{ formatCurrency(pkg.revenue) }}</span>
                </div>
                <div class="stat">
                  <span class="label">Rating</span>
                  <span class="value">{{ pkg.rating.toFixed(1) }}</span>
                </div>
              </div>
            </div>

            <div v-if="popularPackages.length === 0" class="no-data">
              No package data available
            </div>
          </div>
        </div>
      </div>

      <!-- Report Generator Modal -->
      <div v-if="showGenerateModal" class="modal-overlay" @click.self="showGenerateModal = false">
        <div class="modal-content">
          <div class="modal-header">
            <h2>Generate Report</h2>
            <button class="close-btn" @click="showGenerateModal = false">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="handleGenerateReport" class="generate-form">
            <div class="form-group">
              <label for="reportType">Report Type</label>
              <select id="reportType" v-model="reportForm.type" required>
                <option value="revenue">Revenue Report</option>
                <option value="bookings">Bookings Report</option>
                <option value="customers">Customer Analytics</option>
                <option value="packages">Package Performance</option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="startDate">Start Date</label>
                <input
                  type="date"
                  id="startDate"
                  v-model="reportForm.startDate"
                  required
                />
              </div>

              <div class="form-group">
                <label for="endDate">End Date</label>
                <input
                  type="date"
                  id="endDate"
                  v-model="reportForm.endDate"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="format">Export Format</label>
              <select id="format" v-model="reportForm.format" required>
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
                <option value="csv">CSV</option>
              </select>
            </div>

            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="showGenerateModal = false">
                Cancel
              </button>
              <button type="submit" class="submit-btn" :disabled="isGenerating">
                <i class="fas fa-spinner fa-spin" v-if="isGenerating"></i>
                {{ isGenerating ? 'Generating...' : 'Generate Report' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Chart from 'chart.js/auto';

const showGenerateModal = ref(false);
const isGenerating = ref(false);

const stats = ref({
  totalRevenue: 0,
  averageOrderValue: 0,
  totalBookings: 0,
  completionRate: 0,
  revenueTrend: 0,
  bookingTrend: 0
});

const revenueData = ref({
  labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  datasets: [{
    label: 'Revenue',
    data: [],
    borderColor: '#4CAF50',
    tension: 0.4
  }]
});

const bookingsChartData = ref({
  labels: ['Wedding', 'Debut', 'Christening', 'Party', 'Other'],
  datasets: [{
    data: [],
    backgroundColor: [
      '#FF6384', // Pink for Wedding
      '#36A2EB', // Blue for Debut
      '#FFCE56', // Yellow for Christening
      '#4BC0C0', // Turquoise for Party
      '#9966FF'  // Purple for Other
    ],
    borderWidth: 1,
    borderColor: '#ffffff'
  }]
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
};

const reportForm = ref({
  type: 'revenue',
  startDate: '',
  endDate: '',
  format: 'pdf'
});

const periods = ['7D', '30D', '90D', '1Y'];
const selectedPeriod = ref('30D');

const activeUsersCount = ref(0);
const customerSatisfaction = ref(0);
const userTrend = ref(0);
const satisfactionTrend = ref(0);

const revenueChart = ref(null);
const bookingsChart = ref(null);
const customerChart = ref(null);

// Initialize popularPackages as an empty array
const popularPackages = ref([]);

// Add formatCurrency function
const formatCurrency = (value) => {
  if (!value) return '₱0';
  
  if (value >= 1000000) {
    return `₱${(value / 1000000).toFixed(1)}M`;
  } else if (value >= 1000) {
    return `₱${(value / 1000).toFixed(1)}K`;
  }
  return `₱${value.toFixed(2)}`;
};

// Fetch monthly revenue data
const fetchMonthlyRevenue = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-monthly-revenue`);
    const monthlyRevenue = response.data.monthlyRevenue || [];
    revenueData.value.datasets[0].data = monthlyRevenue.map(value => {
      const numValue = Number(value);
      return isNaN(numValue) ? 0 : numValue;
    });
    
    // Update the chart if it exists
    if (revenueChart.value) {
      revenueChart.value.update();
    }
  } catch (error) {
    console.error('Error fetching monthly revenue:', error);
    revenueData.value.datasets[0].data = Array(12).fill(0);
    if (revenueChart.value) {
      revenueChart.value.update();
    }
  }
};

const fetchRevenueData = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-revenue-overview`);
    stats.value = {
      ...stats.value,
      totalRevenue: response.data.totalRevenue,
      averageOrderValue: response.data.averageOrderValue,
      revenueTrend: response.data.revenueTrend
    };

    // Update revenue chart if needed
    if (revenueChart.value) {
      revenueChart.value.data.datasets[0].data = [
        response.data.revenueData.yesterday,
        response.data.revenueData.today
      ];
      revenueChart.value.update();
    }
  } catch (error) {
    console.error('Error fetching revenue data:', error);
  }
};

const fetchBookingsData = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-bookings-by-event-type`);
    stats.value = {
      ...stats.value,
      totalBookings: response.data.totalBookings,
      bookingTrend: response.data.bookingTrend,
      completionRate: response.data.completionRate
    };

    // Update bookings chart data
    if (bookingsChart.value && response.data.bookingsByType) {
      const chartData = response.data.bookingsByType;
      const labels = Object.keys(chartData);
      const data = labels.map(label => chartData[label].count || 1); // Use 1 as minimum to show the circle

      bookingsChart.value.data.labels = labels;
      bookingsChart.value.data.datasets[0].data = data;
      bookingsChart.value.update('none'); // Update without animation
    }
  } catch (error) {
    console.error('Error fetching bookings data:', error);
  }
};

const fetchCustomerAnalytics = async () => {
  try {
    // Fetch active users
    const usersResponse = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-active-users`);
    activeUsersCount.value = usersResponse.data.active_users_count;
    userTrend.value = usersResponse.data.trend;

    // Fetch satisfaction data
    const satisfactionResponse = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-ratings`);
    console.log('Satisfaction Response:', satisfactionResponse.data); // Debug log
    
    // Update the reactive refs
    customerSatisfaction.value = satisfactionResponse.data.satisfaction_percentage || 0;
    satisfactionTrend.value = satisfactionResponse.data.trend || 0;

  } catch (error) {
    console.error('Error fetching customer analytics:', error);
    // Set default values in case of error
    customerSatisfaction.value = 0;
    satisfactionTrend.value = 0;
  }
};

// Update the fetchPopularPackages function
const fetchPopularPackages = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-popular-packages`);
    popularPackages.value = response.data.popular_packages || []; // Ensure it's always an array
  } catch (error) {
    console.error('Error fetching popular packages:', error);
    popularPackages.value = []; // Reset to empty array on error
  }
};

// Separate function for initializing charts
const initializeCharts = () => {
  // Initialize Revenue Chart
  const revenueCtx = document.querySelector('#revenueChart').getContext('2d');
  revenueChart.value = new Chart(revenueCtx, {
    type: 'line',
    data: revenueData.value,
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
              const value = context.parsed.y;
              if (value >= 1000000000) {
                return `₱${(value / 1000000000).toFixed(1)}B`;
              } else if (value >= 1000000) {
                return `₱${(value / 1000000).toFixed(1)}M`;
              } else if (value >= 1000) {
                return `₱${(value / 1000).toFixed(1)}K`;
              }
              return `₱${value}`;
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
              if (value >= 1000000000) {
                return `₱${(value / 1000000000).toFixed(1)}B`;
              } else if (value >= 1000000) {
                return `₱${(value / 1000000).toFixed(1)}M`;
              } else if (value >= 1000) {
                return `₱${(value / 1000).toFixed(1)}K`;
              }
              return `₱${value}`;
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

  // Initialize Bookings Chart
  const bookingsCtx = document.querySelector('#bookingsChart').getContext('2d');
  bookingsChart.value = new Chart(bookingsCtx, {
    type: 'doughnut',
    data: {
      labels: ['Wedding', 'Debut', 'Christening', 'Party', 'Other'],
      datasets: [{
        data: [1, 1, 1, 1, 1], // Set some initial data to show the circle
        backgroundColor: [
          '#FF6384', // Wedding
          '#36A2EB', // Debut
          '#FFCE56', // Christening
          '#4BC0C0', // Party
          '#9966FF'  // Other
        ],
        borderColor: '#ffffff',
        borderWidth: 2,
        hoverOffset: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 20,
            usePointStyle: true,
            pointStyle: 'circle',
            font: {
              size: 12
            }
          }
        }
      },
      cutout: '70%',
      radius: '90%'
    }
  });

  // Initialize Customer Chart
  const customerChart = new Chart(document.querySelector('#customerChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'New Users',
        data: [120, 150, 180, 220, 280, 350],
        backgroundColor: '#4CAF50'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            display: true,
            color: 'rgba(0, 0, 0, 0.1)'
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
};

onMounted(async () => {
  await Promise.all([
    fetchMonthlyRevenue(),
    fetchRevenueData(),
    fetchBookingsData(),
    fetchCustomerAnalytics(),
    fetchPopularPackages()
  ]);

  initializeCharts();
});

// Make sure formatCurrency is available to the template
defineExpose({
  formatCurrency
});
</script>

<style scoped>
.reports-management {
  display: flex;
  min-height: 100vh;
  background: var(--background-color);
}

.management-content {
  flex: 1;
  padding: 2rem;
  margin-left: 250px;
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
  background: var(--background-color);
}

.page-header {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
}

.generate-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.generate-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.report-card {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.report-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.report-header h3 {
  font-size: 1.2rem;
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
}

.period-selector {
  display: flex;
  gap: 0.5rem;
}

.period-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-secondary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.period-btn.active {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.export-btn {
  padding: 0.5rem 1rem;
  background: none;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--text-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.chart-container {
  margin-bottom: 1.5rem;
  height: 300px;
  background: var(--card-background);
  border-radius: 8px;
  padding: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-item .label {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.stat-item .value {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-color);
}

.trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.trend.up {
  color: var(--success-color);
}

.trend.down {
  color: var(--error-color);
}

.packages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.package-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: var(--hover-background);
  border-radius: 6px;
  border: 1px solid var(--border-color);
}

.package-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.package-name {
  font-weight: 500;
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

.event-type.party {
  background: #f3e5f5;
  color: #7b1fa2;
}

.package-stats {
  display: flex;
  gap: 1.5rem;
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.stat .label {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.stat .value {
  font-weight: 600;
  color: var(--text-color);
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--card-background);
  border-radius: 12px;
  padding: 2rem;
  width: 100%;
  max-width: 500px;
  box-shadow: var(--shadow-lg);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.modal-header h2 {
  color: var(--text-color);
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 1.25rem;
  padding: 0.5rem;
  transition: color 0.2s ease;
}

.close-btn:hover {
  color: var(--text-color);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: var(--text-color);
  font-weight: 500;
}

.form-group select,
.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.95rem;
}

.form-group select:focus,
.form-group input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--hover-background);
  border: 1px solid var(--border-color);
  color: var(--text-color);
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: var(--hover-color);
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.submit-btn:hover {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Chart customization for dark mode */
:root[data-theme="dark"] {
  .chart-container canvas {
    filter: invert(1) hue-rotate(180deg);
  }
}

@media (max-width: 1280px) {
  .reports-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .generate-btn {
    width: 100%;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style> 