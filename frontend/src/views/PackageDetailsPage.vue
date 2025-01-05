<template>
  <div class="package-details-page">
    <div class="content-wrapper">
      <div v-if="isLoading" class="loading">
        <div class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i>
          Loading package details...
        </div>
      </div>
      <div v-else-if="packageData" class="package-container">
        <div class="package-header">
          <img :src="getImageUrl(packageData.package_image)" :alt="packageData.package_name" class="package-image">
          <div class="package-overlay">
            <span class="package-type-badge" :style="getPackageTypeBadgeStyle(packageData.package_type)">
              {{ packageData.package_type }}
            </span>
            <h2>{{ packageData.package_name }}</h2>
            <div class="price">₱{{ formatNumber(packageData.package_price) }}</div>
          </div>
        </div>

        <div class="package-content">
          <p class="package-description">{{ packageData.package_description }}</p>

          <div class="package-details">
            <div class="detail-item">
              <i class="fas fa-users"></i>
              <div>
                <h4>Guest Capacity</h4>
                <p>{{ packageData.packs || 0 }} persons</p>
              </div>
            </div>
            <div class="detail-item">
              <i class="fas fa-calendar-alt"></i>
              <div>
                <h4>Event Type</h4>
                <p>{{ packageData.package_type }}</p>
              </div>
            </div>
            <div class="detail-item">
              <i class="fas fa-star"></i>
              <div>
                <h4>Rating</h4>
                <p>{{ packageData.rating || '0.0' }} / 5.0</p>
              </div>
            </div>
          </div>

          <div class="inclusions">
            <h3>Package Inclusions</h3>
            <div class="inclusions-grid">
              <div v-for="(inclusion, index) in packageInclusions" :key="index" class="inclusion-item">
                <i class="fas fa-check"></i>
                <span>{{ inclusion }}</span>
              </div>
            </div>
          </div>

          <div class="ratings-section">
            <h3>Ratings and reviews</h3>
            <div class="rating-overview">
              <div class="rating-score">
                <h2 class="score">{{ packageData.rating || '0.0' }}</h2>
                <div class="stars">
                  <template v-for="i in 5" :key="i">
                    <i class="fas fa-star" :class="{ active: i <= Math.floor(packageData.rating || 0) }"></i>
                  </template>
                </div>
                <p>{{ packageData.reviews_count || 0 }} {{ (packageData.reviews_count || 0) === 1 ? 'review' : 'reviews' }}</p>
              </div>
              <div class="rating-bars">
                <div v-for="i in 5" :key="i" class="rating-bar-item">
                  <span class="star-number">{{ 6 - i }}</span>
                  <div class="bar-container">
                    <div class="bar" :style="{ width: calculateRatingPercentage(6 - i) + '%' }"></div>
                  </div>
                  <span class="rating-count">{{ getRatingCount(6 - i) }}</span>
                </div>
              </div>
            </div>

            <div class="reviews-list" v-if="packageReviews.length > 0">
              <div v-for="review in packageReviews" :key="review.id" class="review-item">
                <div class="review-header">
                  <img 
                    :src="review.user_avatar" 
                    :alt="review.user_name" 
                    class="user-avatar"
                    @error="$event.target.src = `${import.meta.env.VITE_API_URL}/storage/avatars/defaultAvatar.png`"
                  >
                  <div class="review-meta">
                    <h4>{{ review.user_name }}</h4>
                    <div class="stars">
                      <template v-for="i in 5" :key="i">
                        <i class="fas fa-star" :class="{ active: i <= review.rating }"></i>
                      </template>
                    </div>
                    <p class="review-date">{{ formatDate(review.created_at) }}</p>
                  </div>
                </div>
                <p class="review-text">{{ review.review }}</p>
              </div>
            </div>
            <div v-else class="no-reviews">
              <p>No reviews yet</p>
            </div>
          </div>

          <div class="package-actions">
            <button @click="goBack" class="btn-back">
              <i class="fas fa-arrow-left"></i>
              Back to Packages
            </button>
            <button @click="bookNow" class="btn-book">
              <i class="fas fa-calendar-check"></i>
              Book Now
            </button>
          </div>
        </div>
      </div>
      <div v-else class="error">
        <i class="fas fa-exclamation-circle"></i>
        Package not found
      </div>
    </div>

    <RateBookingModal
      v-if="showRateModal"
      :booking="selectedBooking"
      @close="showRateModal = false"
      @rating-added="handleRatingAdded"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import RateBookingModal from '@/components/bookings/RateBookingModal.vue';
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();
const packageData = ref(null);
const isLoading = ref(true);
const packageReviews = ref([]);
const ratingDistribution = ref({});
const totalReviews = ref(0);
const showRateModal = ref(false);
const selectedBooking = ref(null);

// Predefined color pairs for common package types
const predefinedColors = {
  wedding: { bg: '#ffebee', text: '#c62828' },
  debut: { bg: '#fff3e0', text: '#ef6c00' },
  christening: { bg: '#e3f2fd', text: '#1565c0' },
  party: { bg: '#e8f5e9', text: '#2e7d32' },
  platinum: { bg: '#ede7f6', text: '#4527a0' }
};

// Function to generate a color from a string
const stringToColor = (str) => {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  
  // Generate HSL values
  const h = Math.abs(hash % 360); // Hue
  const s = 85; // Saturation
  const l = 95; // Lightness for background
  const textL = 25; // Lightness for text

  return {
    bg: `hsl(${h}, ${s}%, ${l}%)`,
    text: `hsl(${h}, ${s}%, ${textL}%)`
  };
};

// Function to get badge styles
const getPackageTypeBadgeStyle = (type) => {
  if (!type) return {};
  
  const normalizedType = type.toLowerCase();
  const colors = predefinedColors[normalizedType] || stringToColor(normalizedType);
  
  return {
    backgroundColor: colors.bg,
    color: colors.text
  };
};

const packageInclusions = computed(() => {
  if (!packageData.value?.package_inclusion) return [];
  try {
    return JSON.parse(packageData.value.package_inclusion);
  } catch (e) {
    return [];
  }
});

const formatNumber = (num) => {
  if (num === undefined || num === null) return '0';
  return Number(num).toLocaleString('en-PH');
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return '/default-package.jpg';
  if (imagePath.startsWith('http')) return imagePath;
  if (imagePath.startsWith('images/')) return `/src/assets/${imagePath}`;
  return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
};

const fetchPackageDetails = async () => {
  try {
    isLoading.value = true;
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-package-by-id/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    
    // Check if package is inactive
    if (response.data.status?.toLowerCase() !== 'active') {
      await Swal.fire({
        icon: 'warning',
        title: 'Package Unavailable',
        text: 'This package is currently not available.'
      });
      router.push('/packages');
      return;
    }
    
    packageData.value = response.data;
    console.log('Package data:', response.data);
  } catch (error) {
    console.error('Error fetching package details:', error);
    await Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'Failed to fetch package details'
    });
    router.push('/packages');
  } finally {
    isLoading.value = false;
  }
};

const bookNow = () => {
  if (packageData.value?.id) {
    router.push(`/bookings/${packageData.value.id}`);
  }
};

const goBack = () => {
  router.push('/packages');
};

const fetchReviews = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/package-reviews/${route.params.id}`, {
      headers: {
        'Authorization': `Bearer ${token.value}`
      }
    });
    packageReviews.value = response.data.reviews;
    ratingDistribution.value = response.data.distribution;
    totalReviews.value = response.data.total;
    
    // Update package data with latest rating
    if (packageData.value) {
      packageData.value.rating = response.data.average;
      packageData.value.reviews_count = response.data.total;
    }
  } catch (error) {
    console.error('Error fetching reviews:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to fetch package reviews'
    });
  }
};

const calculateRatingPercentage = (rating) => {
  if (totalReviews.value === 0) return 0;
  return (ratingDistribution.value[rating] || 0) / totalReviews.value * 100;
};

const getRatingCount = (rating) => {
  return ratingDistribution.value[rating] || 0;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const handleRatingAdded = async (updatedPackage) => {
  try {
    // Update the package data
    packageData.value = {
      ...packageData.value,
      rating: updatedPackage.rating,
      reviews_count: updatedPackage.reviews_count
    };
    
    // Refresh the reviews
    await fetchReviews();
    showRateModal.value = false;
  } catch (error) {
    console.error('Error updating package rating:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update package rating'
    });
  }
};

onMounted(() => {
  fetchPackageDetails();
  fetchReviews();
});
</script>

<style scoped>
.package-details-page {
  min-height: 100vh;
  background: var(--background-color);
  padding: 6rem 0 2rem;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.loading-spinner {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: var(--text-muted);
  font-size: 1.1rem;
}

.loading-spinner i {
  color: var(--primary-color);
  font-size: 1.5rem;
}

.package-container {
  background: var(--card-background);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  margin-top: 2rem;
}

.package-header {
  position: relative;
  height: 400px;
  background: var(--card-background);
}

.package-header img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.package-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 3rem 2rem 2rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
  color: white;
}

.package-type-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 1rem;
  backdrop-filter: blur(8px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.package-overlay h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.price {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--primary-color);
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.package-content {
  padding: 2rem;
}

.package-description {
  font-size: 1.1rem;
  line-height: 1.7;
  color: var(--text-color);
  margin-bottom: 2.5rem;
  padding: 1.5rem;
  background: var(--hover-background);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.package-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  transition: transform 0.2s, box-shadow 0.2s;
}

.detail-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.detail-item i {
  font-size: 1.5rem;
  color: var(--primary-color);
  background: var(--primary-color-light);
  padding: 1rem;
  border-radius: 12px;
}

.detail-item h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-muted);
  margin: 0 0 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item p {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.inclusions {
  margin-bottom: 3rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 2rem;
}

.inclusions h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.inclusions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.inclusion-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--hover-background);
  border-radius: 10px;
  transition: transform 0.2s;
}

.inclusion-item:hover {
  transform: translateX(4px);
}

.inclusion-item i {
  color: var(--primary-color);
  font-size: 1rem;
  background: var(--primary-color-light);
  padding: 0.5rem;
  border-radius: 8px;
}

.inclusion-item span {
  font-size: 1rem;
  color: var(--text-color);
}

.ratings-section {
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 3rem;
}

.ratings-section h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.rating-overview {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 3rem;
  margin-bottom: 2rem;
}

.rating-score {
  text-align: center;
  padding-right: 2rem;
  border-right: 1px solid var(--border-color);
}

.rating-score .score {
  font-size: 3rem;
  font-weight: 700;
  color: var(--text-color);
  margin: 0;
}

.rating-score .stars {
  margin: 0.5rem 0;
}

.rating-score p {
  color: var(--text-muted);
  margin: 0;
}

.rating-bars {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.rating-bar-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.star-number {
  min-width: 1rem;
  text-align: right;
  color: var(--text-muted);
}

.bar-container {
  flex: 1;
  height: 8px;
  background: var(--hover-background);
  border-radius: 4px;
  overflow: hidden;
}

.bar {
  height: 100%;
  background: var(--primary-color);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.rating-count {
  min-width: 3rem;
  color: var(--text-muted);
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.review-item {
  padding: 1.5rem;
  background: var(--hover-background);
  border-radius: 12px;
}

.review-header {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.user-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.review-meta h4 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 0.25rem;
}

.stars {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 0.25rem;
}

.stars i {
  color: var(--border-color);
}

.stars i.active {
  color: #fbbf24;
}

.review-date {
  font-size: 0.875rem;
  color: var(--text-muted);
  margin: 0;
}

.review-text {
  color: var(--text-color);
  line-height: 1.6;
  margin: 0;
}

.no-reviews {
  text-align: center;
  padding: 3rem;
  color: var(--text-muted);
  background: var(--hover-background);
  border-radius: 12px;
}

.package-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-back,
.btn-book {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 2rem;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.2s;
  cursor: pointer;
  min-width: 160px;
}

.btn-back {
  background: var(--hover-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.btn-back:hover {
  background: var(--hover-color);
}

.btn-book {
  background: var(--primary-color);
  color: white;
  border: none;
}

.btn-book:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem;
  color: var(--text-muted);
  text-align: center;
}

.error i {
  font-size: 3rem;
  color: var(--error-color);
}

@media (max-width: 768px) {
  .package-header {
    height: 300px;
  }

  .package-overlay {
    padding: 2rem 1.5rem 1.5rem;
  }

  .package-overlay h2 {
    font-size: 2rem;
  }

  .package-content {
    padding: 1.5rem;
  }

  .rating-overview {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .rating-score {
    padding-right: 0;
    padding-bottom: 2rem;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
  }

  .package-actions {
    flex-direction: column-reverse;
  }

  .btn-back,
  .btn-book {
    width: 100%;
    justify-content: center;
    min-width: unset;
  }

  .package-details-page {
    padding: 5rem 0 2rem;
  }
}

:root[data-theme="dark"] {
  .package-type-badge {
    background: rgba(255, 255, 255, 0.1);
  }

  .detail-item i {
    background: rgba(var(--primary-rgb), 0.2);
  }

  .inclusion-item i {
    background: rgba(var(--primary-rgb), 0.2);
  }

  .stars i.active {
    color: #fbbf24;
  }
}
</style> 