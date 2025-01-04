`<template>
  <div class="christening-package">
    <div class="package-header">
      <img :src="package.image ? `${import.meta.env.VITE_STORAGE_URL}/api/storage/${package.image}` : '/src/assets/images/default-package.jpg'" 
           :alt="package.name" 
           class="package-image">
      <div class="package-overlay">
        <h2>{{ package.name }}</h2>
        <div class="price">₱{{ formatNumber(package.price) }}</div>
      </div>
    </div>

    <div class="package-content">
      <div class="package-description">
        <p>{{ package.description }}</p>
      </div>

      <div class="package-details">
        <div class="detail-item">
          <i class="fas fa-users"></i>
          <div>
            <h4>Guest Capacity</h4>
            <p>{{ package.capacity }} persons</p>
          </div>
        </div>
        <div class="detail-item">
          <i class="fas fa-clock"></i>
          <div>
            <h4>Duration</h4>
            <p>{{ package.duration }}</p>
          </div>
        </div>
        <div class="detail-item">
          <i class="fas fa-map-marker-alt"></i>
          <div>
            <h4>Venue</h4>
            <p>{{ package.venue }}</p>
          </div>
        </div>
      </div>

      <div class="inclusions">
        <h3>Package Inclusions</h3>
        <div class="inclusions-grid">
          <div v-for="(inclusion, index) in package.inclusions" :key="index" class="inclusion-item">
            <i class="fas fa-check"></i>
            <span>{{ inclusion }}</span>
          </div>
        </div>
      </div>

      <div class="add-ons">
        <h3>Optional Add-ons</h3>
        <div class="add-ons-list">
          <div v-for="(addon, index) in package.addons" :key="index" class="addon-item">
            <label class="addon-checkbox">
              <input 
                type="checkbox"
                v-model="selectedAddons"
                :value="addon"
                @change="updateTotal"
              >
              <span class="checkbox-custom"></span>
              <span class="addon-name">{{ addon.name }}</span>
            </label>
            <span class="addon-price">₱{{ formatNumber(addon.price) }}</span>
          </div>
        </div>
      </div>

      <div class="total-section">
        <div class="subtotal">
          <span>Package Price:</span>
          <span>₱{{ formatNumber(package.price) }}</span>
        </div>
        <div class="addons-total" v-if="addonsTotal > 0">
          <span>Add-ons:</span>
          <span>₱{{ formatNumber(addonsTotal) }}</span>
        </div>
        <div class="grand-total">
          <span>Total:</span>
          <span>₱{{ formatNumber(totalPrice) }}</span>
        </div>
      </div>

      <div class="package-actions">
        <button @click="bookNow" class="book-btn">
          Book This Package
        </button>
        <button @click="inquire" class="inquire-btn">
          Inquire Now
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useNotifications } from '@/composables/useNotifications';

export default {
  name: 'ChristeningPackage',
  props: {
    package: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const router = useRouter();
    const { showNotification } = useNotifications();
    const selectedAddons = ref([]);

    const addonsTotal = computed(() => {
      return selectedAddons.value.reduce((total, addon) => total + addon.price, 0);
    });

    const totalPrice = computed(() => {
      return props.package.price + addonsTotal.value;
    });

    const formatNumber = (num) => {
      return num.toLocaleString('en-PH');
    };

    const updateTotal = () => {
      // This function can be used to trigger any additional logic when addons change
    };

    const bookNow = () => {
      router.push({
        path: '/booking',
        query: {
          packageId: props.package._id,
          addons: selectedAddons.value.map(addon => addon._id).join(',')
        }
      });
    };

    const inquire = () => {
      router.push({
        path: '/contact',
        query: {
          subject: `Inquiry about ${props.package.name}`,
          package: props.package._id
        }
      });
    };

    return {
      selectedAddons,
      addonsTotal,
      totalPrice,
      formatNumber,
      updateTotal,
      bookNow,
      inquire
    };
  }
};
</script>

<style scoped>
.christening-package {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.package-header {
  position: relative;
  height: 300px;
}

.package-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.package-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 24px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
  color: white;
}

.package-overlay h2 {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
}

.price {
  font-size: 24px;
  font-weight: 600;
}

.package-content {
  padding: 24px;
}

.package-description {
  font-size: 16px;
  line-height: 1.6;
  color: #4B5563;
  margin-bottom: 24px;
}

.package-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: #F3F4F6;
  border-radius: 12px;
}

.detail-item i {
  font-size: 24px;
  color: #4F46E5;
}

.detail-item h4 {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin: 0 0 4px 0;
}

.detail-item p {
  font-size: 16px;
  color: #6B7280;
  margin: 0;
}

.inclusions {
  margin-bottom: 32px;
}

.inclusions h3 {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 16px 0;
}

.inclusions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 12px;
}

.inclusion-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #374151;
}

.inclusion-item i {
  color: #10B981;
}

.add-ons {
  margin-bottom: 32px;
}

.add-ons h3 {
  font-size: 20px;
  font-weight: 600;
  color: #111827;
  margin: 0 0 16px 0;
}

.add-ons-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.addon-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  background: #F9FAFB;
  border-radius: 8px;
}

.addon-checkbox {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-custom {
  width: 20px;
  height: 20px;
  border: 2px solid #D1D5DB;
  border-radius: 4px;
  position: relative;
}

input[type="checkbox"]:checked + .checkbox-custom {
  background: #4F46E5;
  border-color: #4F46E5;
}

input[type="checkbox"]:checked + .checkbox-custom::after {
  content: '';
  position: absolute;
  left: 6px;
  top: 2px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.addon-name {
  font-size: 14px;
  color: #374151;
}

.addon-price {
  font-size: 14px;
  font-weight: 600;
  color: #4F46E5;
}

.total-section {
  padding: 20px;
  background: #F3F4F6;
  border-radius: 12px;
  margin-bottom: 24px;
}

.subtotal,
.addons-total,
.grand-total {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
}

.grand-total {
  font-size: 18px;
  font-weight: 600;
  color: #111827;
  border-top: 1px solid #E5E7EB;
  margin-top: 8px;
  padding-top: 16px;
}

.package-actions {
  display: flex;
  gap: 16px;
}

.book-btn,
.inquire-btn {
  flex: 1;
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.book-btn {
  background: #4F46E5;
  color: white;
}

.book-btn:hover {
  background: #4338CA;
}

.inquire-btn {
  background: #F3F4F6;
  color: #374151;
}

.inquire-btn:hover {
  background: #E5E7EB;
}

@media (max-width: 640px) {
  .package-header {
    height: 200px;
  }

  .package-overlay h2 {
    font-size: 24px;
  }

  .price {
    font-size: 20px;
  }

  .package-content {
    padding: 16px;
  }

  .package-actions {
    flex-direction: column;
  }
}
</style>` 