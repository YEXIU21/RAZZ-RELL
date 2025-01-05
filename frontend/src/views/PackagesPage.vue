<template>
  <transition name="fade" mode="out-in">
    <div class="packages-page">
      <div class="content-wrapper">
        <div class="packages-content">
          <header class="packages-header">
            <h1 class="page-title">Our Event Packages</h1>
            <p class="page-subtitle">Find the perfect package for your special occasions</p>
          </header>

          <div class="packages-filters">
            <div class="search-and-filters">
              <div class="search-bar">
                <input 
                  v-model="searchQuery" 
                  type="text" 
                  placeholder="Search packages..."
                  @input="filterPackages"
                >
                <i class="fas fa-search search-icon"></i>
              </div>
              
              <div class="filter-section">
                <div class="select-wrapper">
                  <label>Event Type</label>
                  <select 
                    v-model="selectedType" 
                    class="event-type-select"
                    @change="filterByType(selectedType)"
                  >
                    <option value="All">All Event Types</option>
                    <optgroup label="Standard Events">
                      <option value="Wedding">Wedding</option>
                      <option value="Debut">Debut</option>
                      <option value="Christening">Christening</option>
                      <option value="Party">Party</option>
                    </optgroup>
                    <optgroup v-if="customTypes.size > 0" label="Special Events">
                      <option 
                        v-for="type in Array.from(customTypes)" 
                        :key="type" 
                        :value="type"
                      >
                        {{ type }}
                      </option>
                    </optgroup>
                  </select>
                </div>

                <div class="select-wrapper">
                  <label>Sort By</label>
                  <select v-model="sortBy" @change="sortPackages" class="sort-select">
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="rating">Rating</option>
                    <option value="popularity">Most Popular</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="active-filters" v-if="hasActiveFilters">
              <span class="filter-label">Active Filters:</span>
              <div class="filter-tags">
                <span v-if="selectedType !== 'All'" class="filter-tag">
                  {{ selectedType }}
                  <button @click="resetTypeFilter" class="remove-filter">×</button>
                </span>
                <span v-if="searchQuery" class="filter-tag">
                  "{{ searchQuery }}"
                  <button @click="resetSearchFilter" class="remove-filter">×</button>
                </span>
              </div>
              <button @click="resetAllFilters" class="reset-all-btn">
                Reset All
              </button>
            </div>
          </div>

          <div class="packages-grid">
            <template v-if="isLoading">
              <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading packages...</p>
              </div>
            </template>
            <template v-else-if="filteredPackages.length">
              <PackageCard 
                v-for="pkg in filteredPackages" 
                :key="pkg._id"
                :package="pkg"
              />
            </template>
            <div v-else class="no-results">
              <i class="fas fa-search"></i>
              <p>No packages found matching your criteria</p>
              <button @click="resetAllFilters" class="reset-btn">
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import PackageCard from '@/components/packages/PackageCard.vue';
import { useApi } from '@/composables/useApi';
import { useNotifications } from '@/composables/useNotifications';
import axios from 'axios';

export default {
  name: 'PackagesPage',
  components: {
    PackageCard
  },
  setup() {
    const { api } = useApi();
    const { showNotification } = useNotifications();
    
    const isLoading = ref(false);
    const packages = ref([]);
    const searchQuery = ref('');
    const selectedType = ref('All');
    const sortBy = ref('price-asc');
    const customTypes = ref(new Set());

    const fetchPackages = async () => {
      try {
        isLoading.value = true;
        const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-packages`);
        
        packages.value = response.data.map(pkg => ({
          _id: pkg.id,
          name: pkg.package_name,
          description: pkg.package_description,
          price: pkg.package_price ? parseFloat(pkg.package_price) : 0,
          eventType: pkg.package_type,
          image: pkg.package_image ? `${import.meta.env.VITE_API_URL}/storage/${pkg.package_image}` : null,
          inclusions: pkg.package_inclusion ? JSON.parse(pkg.package_inclusion) : [],
          status: pkg.status,
          rating: pkg.rating || 0,
          bookingsCount: pkg.bookingsCount || 0
        }));

        // Collect custom event types
        packages.value.forEach(pkg => {
          if (!['Wedding', 'Debut', 'Christening', 'Party', 'All'].includes(pkg.eventType)) {
            customTypes.value.add(pkg.eventType);
          }
        });

      } catch (error) {
        console.error('Error fetching packages:', error);
        showNotification('Error loading packages. Please try again.', 'error');
        packages.value = [];
      } finally {
        isLoading.value = false;
      }
    };

    const filteredPackages = computed(() => {
      let filtered = [...packages.value];

      // Filter out inactive packages
      filtered = filtered.filter(pkg => pkg.status?.toLowerCase() === 'active');

      // Filter by type
      if (selectedType.value !== 'All') {
        filtered = filtered.filter(pkg => pkg.eventType === selectedType.value);
      }

      // Filter by search query
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(pkg => 
          pkg.name.toLowerCase().includes(query) ||
          pkg.description.toLowerCase().includes(query) ||
          pkg.eventType.toLowerCase().includes(query)
        );
      }

      // Sort packages
      switch (sortBy.value) {
        case 'price-asc':
          filtered.sort((a, b) => a.price - b.price);
          break;
        case 'price-desc':
          filtered.sort((a, b) => b.price - a.price);
          break;
        case 'rating':
          filtered.sort((a, b) => b.rating - a.rating);
          break;
        case 'popularity':
          filtered.sort((a, b) => b.bookingsCount - a.bookingsCount);
          break;
      }

      return filtered;
    });

    const filterByType = (type) => {
      selectedType.value = type;
    };
    
    const filterPackages = () => {
      // Reset type filter if searching
      if (searchQuery.value && selectedType.value !== 'All') {
        selectedType.value = 'All';
      }
    };

    const resetFilters = () => {
      searchQuery.value = '';
      selectedType.value = 'All';
      sortBy.value = 'price-asc';
    };

    const hasActiveFilters = computed(() => {
      return selectedType.value !== 'All' || searchQuery.value !== '';
    });

    const resetTypeFilter = () => {
      selectedType.value = 'All';
    };

    const resetSearchFilter = () => {
      searchQuery.value = '';
    };

    const resetAllFilters = () => {
      resetTypeFilter();
      resetSearchFilter();
      sortBy.value = 'price-asc';
    };

    onMounted(() => {
      fetchPackages();
    });

    return {
      packages,
      searchQuery,
      selectedType,
      sortBy,
      customTypes,
      filteredPackages,
      filterByType,
      filterPackages,
      resetFilters,
      isLoading,
      hasActiveFilters,
      resetTypeFilter,
      resetSearchFilter,
      resetAllFilters
    };
  }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.packages-page {
  padding-top: 80px;
  width: 100%;
  min-height: 100vh;
  background: var(--background-color);
  position: relative;
  margin: 0;
  overflow-x: hidden;
}

.content-wrapper {
  width: 100%;
  min-height: 100vh;
  background: var(--background-color);
  padding: 0 2rem;
}

.packages-content {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.packages-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 600;
  text-align: center;
  color: var(--primary-color);
  margin-bottom: 0.5rem;
}

.page-subtitle {
  font-size: 1.1rem;
  text-align: center;
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.packages-filters {
  background: var(--card-background);
  border-radius: 8px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.search-and-filters {
  display: flex;
  gap: 1.5rem;
  align-items: flex-end;
}

.search-bar {
  position: relative;
  flex: 2;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: all 0.2s ease;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted);
}

.filter-section {
  display: flex;
  gap: 1.5rem;
  flex: 3;
}

.select-wrapper {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.select-wrapper label {
  font-size: 0.9rem;
  color: var(--text-muted);
  font-weight: 500;
}

.event-type-select,
.sort-select {
  width: 100%;
  padding: 0.75rem 2rem 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--input-background);
  font-size: 1rem;
  color: var(--text-color);
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.7rem center;
  background-size: 1em;
  transition: all 0.2s ease;
}

.event-type-select:focus,
.sort-select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

.event-type-select:hover,
.sort-select:hover {
  border-color: var(--primary-color);
}

.active-filters {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-label {
  font-size: 0.9rem;
  color: var(--text-muted);
  font-weight: 500;
}

.filter-tags {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  background: var(--primary-light);
  color: var(--primary-color);
  border-radius: 20px;
  font-size: 0.9rem;
}

.remove-filter {
  background: none;
  border: none;
  color: inherit;
  font-size: 1.2rem;
  padding: 0;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  transition: all 0.2s;
}

.remove-filter:hover {
  background: rgba(var(--primary-rgb), 0.1);
}

.reset-all-btn {
  margin-left: auto;
  padding: 0.5rem 1rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  color: var(--text-muted);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-all-btn:hover {
  background: var(--hover-background);
  color: var(--text-color);
  border-color: var(--primary-color);
}

.packages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  background: var(--card-background);
  border-radius: 15px;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.no-results i {
  font-size: 3rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
  opacity: 0.7;
}

.no-results p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.reset-btn {
  padding: 0.75rem 2rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.loading-spinner {
  grid-column: 1 / -1;
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-muted);
}

.loading-spinner i {
  font-size: 2rem;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

@media (max-width: 992px) {
  .search-and-filters {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-section {
    flex-direction: row;
  }

  .search-bar,
  .filter-section {
    flex: 1;
  }
}

@media (max-width: 768px) {
  .packages-page {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .packages-header {
    margin-bottom: 1.5rem;
  }

  .filter-section {
    flex-direction: column;
  }

  .select-wrapper {
    width: 100%;
  }
}

/* Base styles */
.packages-page {
  padding-top: 80px;
  width: 100%;
  min-height: 100vh;
  background: var(--background-color);
  position: relative;
  margin: 0;
  overflow-x: hidden;
}

.content-wrapper {
  width: 100%;
  min-height: 100vh;
  background: var(--background-color);
  padding: 0 2rem;
}

.packages-content {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* Dark mode specific styles */
:root[data-theme="dark"] {
  .packages-page {
    background: var(--background-color);
    min-height: 100vh;
    width: 100vw;
  }

  .content-wrapper {
    background: var(--background-color);
    min-height: 100vh;
  }

  .packages-filters {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-color);
  }

  .search-bar input {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
    border-color: var(--border-color);
  }

  .event-type-select,
  .sort-select {
    background-color: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
    border-color: var(--border-color);
  }

  .filter-tag {
    background: rgba(var(--primary-rgb), 0.2);
    color: var(--primary-color);
  }

  .reset-all-btn {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-color);
    color: var(--text-muted);
  }

  .reset-all-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-color);
    border-color: var(--primary-color);
  }

  .no-results {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-color);
  }

  .packages-grid {
    background: var(--background-color);
  }
}
</style>