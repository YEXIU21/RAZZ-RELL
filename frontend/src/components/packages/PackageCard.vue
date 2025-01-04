<template>
  <div class="package-card">
    <div class="package-image">
      <img 
        :src="packageImageUrl" 
        :alt="package.name"
      >
      <div class="package-badge" :style="eventTypeStyle">
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
          <li v-for="(inclusion, index) in displayedInclusions" :key="index">
            <i class="fas fa-check"></i>
            {{ inclusion }}
          </li>
        </ul>
      </div>

      <div class="package-actions">
        <button class="btn-details" @click="viewDetails">View Details</button>
        <button class="btn-book" @click="bookNow">Book Now</button>
      </div>
    </div>
  </div>
</template>

<script>
import { useRouter } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';
import { computed } from 'vue';
import { getEventTypeColors } from '@/utils/eventTypeColors';

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

    const packageImageUrl = computed(() => {
      if (!props.package.image) {
        return '/src/assets/images/default-package.jpg';
      }
      return `${import.meta.env.VITE_STORAGE_URL}/api/storage/${props.package.image}`;
    });

    const viewDetails = () => {
      router.push(`/packages/${props.package._id}`);
    };

    const bookNow = () => {
      router.push(`/bookings/${props.package._id}`);
    };

    const eventTypeStyle = computed(() => {
      const colors = getEventTypeColors(props.package.eventType);
      return {
        backgroundColor: colors.background,
        color: colors.color
      };
    });

    return {
      formatNumber,
      viewDetails,
      bookNow,
      eventTypeStyle,
      packageImageUrl
    };
  },
  computed: {
    displayedInclusions() {
      return this.package.inclusions?.slice(0, 3) || [];
    }
  }
};
</script>

<style scoped>
.package-card {
  background: var(--card-background);
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
  transition: all 0.2s ease;
}

.package-content {
  padding: 20px;
}

.package-name {
  font-size: 20px;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 8px 0;
}

.package-price {
  font-size: 24px;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 12px;
}

.package-description {
  font-size: 14px;
  color: var(--text-muted);
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
  color: var(--text-color);
}

.detail-item i {
  color: var(--text-muted);
}

.package-inclusions {
  margin-bottom: 16px;
}

.package-inclusions h4 {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-color);
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
  color: var(--text-muted);
  margin-bottom: 4px;
}

.package-inclusions li i {
  color: #10B981;
  font-size: 12px;
}

.package-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}

.btn-details,
.btn-book {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-details {
  background: #F3F4F6;
  color: #374151;
  border: 1px solid #E5E7EB;
}

.btn-details:hover {
  background: #E5E7EB;
  transform: translateY(-1px);
}

.btn-book {
  background: #3B82F6;
  color: white;
  border: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-book:hover {
  background: #2563EB;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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