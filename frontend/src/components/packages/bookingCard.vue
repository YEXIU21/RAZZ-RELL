<template>
  <div class="package-card">
    <div class="package-image">
      <img :src="package.image ? `${import.meta.env.VITE_STORAGE_URL}/api/storage/${package.image}` : '/src/assets/images/default-package.jpg'" :alt="package.name">
      <div class="package-badge" :class="package.eventType.toLowerCase()">
        {{ package.eventType }}
      </div>
    </div>

    <div class="package-content">
      <h3 class="package-name">{{ package.name }}</h3>
      <div class="package-price">₱{{ formatNumber(package.price) }}</div>
      
      <p class="package-description">{{ package.description }}</p>
      
      <div class="package-details">
        <div class="detail-item">
          <i class="fas fa-users"></i>
          <span>{{ package.capacity }} guests</span>
        </div>
        <div class="detail-item">
          <i class="fas fa-clock"></i>
          <span>{{ package.duration }}</span>
        </div>
      </div>

      <div class="package-inclusions">
        <h4>Inclusions:</h4>
        <ul>
          <li v-for="(inclusion, index) in package.inclusions" :key="index">
            <i class="fas fa-check"></i>
            {{ inclusion }}
          </li>
        </ul>
      </div>

      <div class="package-stats">
        <div class="stat">
          <i class="fas fa-star"></i>
          <span>{{ package.rating.toFixed(1) }} ({{ package.reviewsCount }})</span>
        </div>
        <div class="stat">
          <i class="fas fa-calendar-check"></i>
          <span>{{ package.bookingsCount }} bookings</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';

export default {
  name: 'PackageCard',
  props: {
    package: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const router = useRouter();
    const { showNotification } = useNotifications();

    const formatNumber = (num) => {
      return num.toLocaleString('en-PH');
    };

    const viewDetails = () => {
      router.push(`/packages/${props.package._id}`);
    };

    const bookNow = () => {
      router.push({
        path: '/booking',
        query: { packageId: props.package._id }
      });
    };

    const validatePacks = (event) => {
      const minPacks = props.package.packs;
      const value = parseInt(event.target.value);
      
      if (value < minPacks) {
        formData.packs = minPacks;
      }
    };

    return {
      formatNumber,
      viewDetails,
      bookNow,
      validatePacks
    };
  }
};
</script>

<style scoped>
.package-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.package-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.package-image {
  position: relative;
  height: 200px;
}

.package-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.package-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.package-badge.wedding {
  background: #FDF2F8;
  color: #DB2777;
}

.package-badge.debut {
  background: #EEF2FF;
  color: #4F46E5;
}

.package-badge.christening {
  background: #ECFDF5;
  color: #059669;
}

.package-badge.party {
  background: #FEF3C7;
  color: #D97706;
}

.package-content {
  padding: 20px;
}

.package-name {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 8px 0;
}

.package-price {
  font-size: 24px;
  font-weight: 700;
  color: #4F46E5;
  margin-bottom: 12px;
}

.package-description {
  font-size: 14px;
  color: #6B7280;
  margin-bottom: 16px;
  line-height: 1.5;
}

.package-details {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
}

.detail-item i {
  color: #6B7280;
}

.package-inclusions {
  margin-bottom: 16px;
}

.package-inclusions h4 {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 8px 0;
}

.package-inclusions ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.package-inclusions li {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #6B7280;
  margin-bottom: 4px;
}

.package-inclusions li i {
  color: #10B981;
  font-size: 12px;
}

.package-stats {
  display: flex;
  gap: 16px;
  margin-bottom: 16px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  color: #6B7280;
}

.stat i {
  color: #9CA3AF;
}

.package-actions {
  display: flex;
  gap: 8px;
}

.btn-details,
.btn-book {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-details {
  background: #F3F4F6;
  color: #374151;
}

.btn-details:hover {
  background: #E5E7EB;
}

.btn-book {
  background: #4F46E5;
  color: white;
}

.btn-book:hover {
  background: #4338CA;
}

@media (max-width: 640px) {
  .package-image {
    height: 160px;
  }

  .package-content {
    padding: 16px;
  }

  .package-name {
    font-size: 18px;
  }

  .package-price {
    font-size: 20px;
  }

  .package-actions {
    flex-direction: column;
  }
}
</style> 