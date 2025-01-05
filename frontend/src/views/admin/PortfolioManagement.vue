<template>
  <div class="portfolios-management">
    <main class="management-content">
      <div class="page-header">
        <h1>Portfolios Management</h1>
      </div>
      
      <div class="content-container">
        <div class="search-filter-container">
          <div class="search-bar">
            <i class="fas fa-search search-icon"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search portfolios..."
              @input="handleSearch"
            />
          </div>
          <div class="filter-select-container">
            <select v-model="eventTypeFilter" class="filter-select">
              <option value="">All Event Types</option>
              <option value="Wedding">Wedding</option>
              <option value="Debut">Debut</option>
              <option value="Christening">Christening</option>
              <option value="Party">Party</option>
              <option value="Other">Other Events</option>
            </select>
          </div>
          <button class="add-portfolio-btn" @click="openAddportfolioModal">
            <i class="fas fa-plus"></i>
            Add Portfolio
          </button>
        </div>

        <div class="portfolios-grid">
          <div v-for="portfolioItem in filteredportfolios" :key="portfolioItem.id" class="portfolio-card">
            <div class="portfolio-image">
              <img :src="getImageUrl(portfolioItem.main_image_url)" :alt="portfolioItem.portfolio_name" />
              <div class="portfolio-actions">
                <button @click="viewportfolio(portfolioItem)" class="action-btn edit">
                  <i class="fas fa-eye"></i>
                </button>
                <button @click="editportfolio(portfolioItem)" class="action-btn edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button @click="deleteportfolio(portfolioItem.id)" class="action-btn delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
            
            <div class="portfolio-content">
              <div class="portfolio-header">
                <h3>{{ portfolioItem.title }}</h3>
              </div>
              <p class="portfolio-description">{{ portfolioItem.description }}</p>
            </div>
          </div>
        </div>

        <div v-if="filteredportfolios.length === 0" class="no-results">
          <i class="fas fa-box-open"></i>
          <p>No portfolios founds in {{ portfolio_typeFilter }}</p>
        </div>
      </div>
    </main>

    <!-- Modals -->
    <AddportfolioModal
      v-if="showAddModal"
      @close="showAddModal = false"
      @success="handleportfolioAdded"
    />

    <EditportfolioModal
      v-if="showEditModal"
      :portfolio="selectedportfolio"
      @close="showEditModal = false"
      @update="handleportfolioUpdated"
    />

    <ViewPortfolioModal
      v-if="showPortfolioModal"
      :portfolio="selectedportfolio"
      @close="showPortfolioModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import AdminSidebar from '@/components/admin/AdminSidebar.vue';
import AddportfolioModal from '@/components/admin/AddportfolioModal.vue';
import ViewPortfolioModal from '@/components/admin/ViewPortfolioModal.vue';
import EditportfolioModal from '@/components/admin/EditportfolioModal.vue';
import ConfirmationModal from '@/components/ui/ConfirmationModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'portfoliosManagement',
  components: {
    AdminSidebar,
    AddportfolioModal,
    EditportfolioModal,
    ViewPortfolioModal,
    ConfirmationModal
  },
  setup() {
    const { token } = useAuth();

    const portfolios = ref([]);
    const searchQuery = ref('');
    const eventTypeFilter = ref('');
    const showAddModal = ref(false);
    const showEditModal = ref(false);
    const showPortfolioModal = ref(false);
    const showDeleteModal = ref(false);
    const selectedportfolio = ref(null); 

    const filteredportfolios = computed(() => {
      return portfolios.value.filter(portfolioItem => {
        const matchesSearch = !searchQuery.value || 
          portfolioItem.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          portfolioItem.description.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesEventType = !eventTypeFilter.value || 
          (eventTypeFilter.value === 'Other' 
            ? !['Wedding', 'Debut', 'Christening', 'Party'].includes(portfolioItem.event_type)
            : portfolioItem.event_type === eventTypeFilter.value);
        
        return matchesSearch && matchesEventType;
      });
    });

    const fetchportfolios = async () => {
      const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-portfolios`);
      portfolios.value = response.data;
    };

    const handleSearch = () => {
      eventTypeFilter.value = '';
    };

    const formatNumber = (num) => {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    const openAddportfolioModal = () => {
      showAddModal.value = true;
    };

    const openDeleteportfolioModal = () => {
      showDeleteModal.value = true;
    };

    const editportfolio = (pkg) => {
      selectedportfolio.value = pkg;
      showEditModal.value = true;
    };

    const viewportfolio = (pkg) => {
      selectedportfolio.value = pkg;
      showPortfolioModal.value = true;
    };

    const deleteportfolio = async (pkg) => {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Okay',
        cancelButtonText: 'Close'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/delete-portfolio/${pkg}`);
            if(response.status === 200){
              await fetchportfolios();
              Swal.fire('Deleted!', 'Portfolio has been deleted.', 'success');
            } else {
              Swal.fire('Error!', 'Something went wrong.', 'error');
            }
          } catch (error) {
            console.error('Error deleting portfolio:', error);
            Swal.fire('Error!', 'Failed to delete portfolio.', 'error');
          }
        }
      });
    };

    const confirmDeleteportfolio = async () => {
      try {
        const response = await axios.post(`${import.meta.env.VITE_API_URL}/api/delete-portfolio/${selectedportfolio.value.id}`);
        if(response.status === 200){
          Swal.fire({
            title: 'Success',
            text: response.data.message,
            icon: 'success'
          });
          await fetchportfolios();
          showDeleteModal.value = false;
        }else{
          Swal.fire({
            title: 'Error',
            text: response.data.message,
            icon: 'error'
          });
          await fetchportfolios();
          showDeleteModal.value = false;
        }
      } catch (error) {
        console.error('Error deleting portfolio:', error);
      }
    };

    const handleportfolioAdded = async () => {
      await fetchportfolios();
      showAddModal.value = false;
    };

    const handleportfolioUpdated = async () => {
      await fetchportfolios();
      showEditModal.value = false;
    };

    const defaultImageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFNUU3RUIiLz48cGF0aCBkPSJNODAgOTBIMTIwVjExMEg4MFY5MFoiIGZpbGw9IiM5Q0EzQUYiLz48cGF0aCBkPSJNNjUgNjBIMTM1VjgwSDY1VjYwWiIgZmlsbD0iIzlDQTNBRiIvPjwvc3ZnPg==';

    const getImageUrl = (imagePath) => {
      if (!imagePath) {
        return defaultImageUrl;
      }
      const storageUrl = import.meta.env.VITE_STORAGE_URL || import.meta.env.VITE_API_URL;
      return `${storageUrl}/storage/${imagePath}`;
    };

    onMounted(async () => {
      await fetchportfolios();
    });

    return {
      portfolios,
      viewportfolio,
      searchQuery,
      eventTypeFilter,
      showDeleteModal,
      selectedportfolio,
      filteredportfolios,
      handleSearch,
      deleteportfolio,
      editportfolio,
      formatNumber,
      openAddportfolioModal,
      confirmDeleteportfolio,
      showEditModal,
      handleportfolioAdded,
      handleportfolioUpdated,
      showPortfolioModal,
      showAddModal,
      getImageUrl
    };
  }
};
</script>

<style scoped>
.portfolios-management {
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
}

.page-header h1 {
  font-size: 1.8rem;
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
}

.content-container {
  background: var(--card-background);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  margin-bottom: 1.5rem;
}

.search-filter-container {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 2rem;
}

.search-bar {
  position: relative;
  flex: 1;
}

.search-bar input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  font-size: 0.95rem;
  background: var(--card-background);
  color: var(--text-color);
  transition: all 0.2s ease;
}

.search-bar input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
}

.filter-select-container {
  width: 200px;
}

.filter-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:hover {
  border-color: var(--border-color);
}

.filter-select:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.add-portfolio-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.add-portfolio-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.add-portfolio-btn i {
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1rem;
  }

  .content-container {
    padding: 1.5rem;
  }

  .search-filter-container {
    flex-direction: column;
    gap: 1rem;
  }

  .filter-select-container {
    width: 100%;
  }

  .add-portfolio-btn {
    width: 100%;
    justify-content: center;
  }
}

.portfolios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
  padding: 0.5rem;
}

.portfolio-card {
  background: var(--card-background);
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
  border: 1px solid var(--border-color);
}

.portfolio-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.portfolio-image {
  position: relative;
  height: 250px;
  overflow: hidden;
}

.portfolio-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.portfolio-card:hover .portfolio-image img {
  transform: scale(1.05);
}

.portfolio-actions {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.75rem;
  opacity: 0;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.portfolio-card:hover .portfolio-actions {
  opacity: 1;
  transform: translateY(0);
}

.action-btn {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 50%;
  border: none;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-sm);
}

.action-btn i {
  font-size: 1.1rem;
}

.action-btn.edit:hover {
  background: var(--primary-color);
  color: white;
  transform: translateY(-2px);
}

.action-btn.delete:hover {
  background: var(--error-color);
  color: white;
  transform: translateY(-2px);
}

.portfolio-content {
  padding: 1.75rem;
}

.portfolio-header {
  margin-bottom: 1rem;
}

.portfolio-header h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  font-weight: 600;
  margin: 0;
}

.portfolio-description {
  color: var(--text-secondary);
  margin: 0;
  line-height: 1.6;
  font-size: 0.95rem;
}

.no-results {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--text-secondary);
  background: var(--card-background);
  border-radius: 1rem;
  margin-top: 2rem;
  box-shadow: var(--shadow-sm);
}

.no-results i {
  font-size: 3.5rem;
  margin-bottom: 1.5rem;
  color: var(--text-secondary);
}

.no-results p {
  font-size: 1.1rem;
  margin: 0;
  color: var(--text-color);
}

@media (max-width: 1280px) {
  .management-content {
    padding: 2rem;
  }
  
  .portfolios-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  }
}

@media (max-width: 768px) {
  .management-content {
    margin-left: 0;
    padding: 1.5rem;
  }

  .page-header {
    padding: 1.5rem;
  }

  .header-actions {
    flex-direction: column;
    gap: 1rem;
  }

  .search-bar {
    width: 100%;
    max-width: none;
  }

  .add-portfolio-btn {
    width: 100%;
    justify-content: center;
  }

  .portfolios-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

.search-and-filters {
  display: flex;
  gap: 1rem;
  flex: 1;
  max-width: 800px;
}

.filters {
  min-width: 200px;
}

.filter-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--card-background);
  color: var(--text-color);
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:hover {
  border-color: var(--border-color);
}

.filter-select:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.filter-select option {
  background: var(--card-background);
  color: var(--text-color);
  padding: 8px;
}

@media (max-width: 768px) {
  .search-and-filters {
    flex-direction: column;
    width: 100%;
    max-width: none;
  }

  .filters {
    min-width: 100%;
  }
}
</style> 