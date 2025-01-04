<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>View Portfolio</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form class="portfolio-form">
        <div class="form-group">
          <label for="portfolioName">Portfolio Title</label>
          <input
            type="text"
            id="portfolioName"
            v-model="formData.title"
            required
            placeholder="Enter Portfolio Title"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <label for="description">Portfolio Description</label>
          <textarea
            id="description"
            v-model="formData.description"
            required
            rows="4"
            placeholder="Enter Portfolio Description"
            class="form-input"
          ></textarea>
        </div>

        <div class="form-group">
          <label for="eventType">Event Type</label>
          <select id="eventType" v-model="formData.eventType" required class="form-input" @change="handleEventTypeChange">
            <option value="">Select Event Type</option>
            <option value="Wedding">Wedding</option>
            <option value="Debut">Debut</option>
            <option value="Christening">Christening</option>
            <option value="Party">Party</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <div class="form-group" v-if="formData.eventType === 'Other'">
          <label for="customEventType">Custom Event Type</label>
          <input
            type="text"
            id="customEventType"
            v-model="formData.customEventType"
            required
            placeholder="Enter custom event type"
            class="form-input"
          />
        </div>
                
        <div class="form-group">
          <label for="coverImage">Cover Image </label>
          <div class="upload-container" :class="{ 'has-image': imagePreview }">
            <div class="preview-container">
              <img 
                :src="getImageUrl(formData.image)"
                alt="Preview" 
                class="image-preview" 
              />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="albumImages">Album Images</label>
          <div class="upload-container album-container">
            <div v-if="!images.length" class="upload-area">
              <div class="upload-content">
                <i class="fas fa-images"></i>
                <span>No album images available</span>
              </div>
            </div>
            <div v-else class="album-grid">
              <div v-for="(preview, index) in images" :key="index" class="album-item">
                <img 
                  :src="getImageUrl(preview)"
                  :alt="`Album image ${index + 1}`" 
                />
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Image Cropper Modal -->
  <Teleport to="body">
    <div v-if="showCropper" class="cropper-modal-wrapper">
      <div class="cropper-modal">
        <div class="cropper-modal-content">
          <div class="cropper-modal-header">
            <h3>Crop Image</h3>
            <button @click="cancelCrop" class="close-btn">×</button>
          </div>
          <div class="cropper-modal-body">
            <vue-cropper
              ref="cropper"
              :src="cropperImage"
              :aspect-ratio="16/9"
              :view-mode="2"
              :guides="true"
              :background="true"
              :rotatable="true"
              :zoomable="true"
              :movable="true"
              :responsive="true"
              :modal="true"
              :autoCropArea="1"
              :center="true"
              :highlight="true"
              :cropBoxMovable="true"
              :cropBoxResizable="true"
              :toggleDragModeOnDblclick="true"
            ></vue-cropper>
          </div>
          <div class="cropper-modal-footer">
            <div class="footer-content">
              <div class="cropper-controls">
                <button type="button" @click="rotateLeft" class="control-btn">Rotate Left</button>
                <button type="button" @click="rotateRight" class="control-btn">Rotate Right</button>
                <button type="button" @click="zoomIn" class="control-btn">Zoom In</button>
                <button type="button" @click="zoomOut" class="control-btn">Zoom Out</button>
                <button type="button" @click="resetCrop" class="control-btn">Reset</button>
              </div>
              <div class="action-buttons">
                <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
                <button type="button" @click="applyCrop" class="btn-primary">Apply</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import Swal from 'sweetalert2';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
  portfolio: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update']);

const formData = reactive({
  title: props.portfolio.title || '',
  description: props.portfolio.description || '',
  eventType: props.portfolio.event_type || '',
  customEventType: '',
  image: props.portfolio.main_image_url,
  images: props.portfolio.images_url
});

const imagePreview = ref(props.portfolio.image_url || null);
const albumPreviews = ref(props.portfolio.album_images?.map(img => img.url) || []);
const isSubmitting = ref(false);
const showCropper = ref(false);
const cropperImage = ref(null);
const cropper = ref(null);
const hasAppliedImage = ref(true); 

const handleEventTypeChange = () => {
  if (formData.eventType !== 'Other') {
    formData.customEventType = '';
  }
};

const images = computed(() => {
  try {
    if (typeof props.portfolio.images_url === 'string') {
      return JSON.parse(props.portfolio.images_url);
    }
    if (Array.isArray(props.portfolio.images_url)) {
      return props.portfolio.images_url;
    }
    return [];
  } catch (error) {
    console.error('Error parsing images:', error);
    return [];
  }
});

const rotateLeft = () => {
  cropper.value.rotate(-90);
};

const rotateRight = () => {
  cropper.value.rotate(90);
};

const image = (path) => {
  if (!path) return '/src/assets/images/default-portfolio.jpg';
  const storageUrl = import.meta.env.VITE_STORAGE_URL || '';
  return storageUrl + '/api/storage/' + path;
};

const zoomIn = () => {
  cropper.value.zoom(0.1);
};

const zoomOut = () => {
  cropper.value.zoom(-0.1);
};

const resetCrop = () => {
  cropper.value.reset();
};

const cancelCrop = () => {
  showCropper.value = false;
  cropperImage.value = null;
  if (!imagePreview.value) {
    formData.image = null;
  }
};

const applyCrop = () => {
  const canvas = cropper.value.getCroppedCanvas();
  imagePreview.value = canvas.toDataURL();
  
  canvas.toBlob((blob) => {
    const file = new File([blob], 'cropped-image.jpg', { type: 'image/jpeg' });
    formData.image = file;
    hasAppliedImage.value = true;
  }, 'image/jpeg');
  
  showCropper.value = false;
};

const getCoverImageUrl = computed(() => {
  if (!formData.image) {
    return '/src/assets/images/default-portfolio.jpg';
  }
  const storageUrl = import.meta.env.VITE_STORAGE_URL || '';
  return storageUrl + '/api/storage/' + formData.image;
});

const getAlbumImageUrl = computed(() => (path) => {
  if (!path) {
    return '/src/assets/images/default-portfolio.jpg';
  }
  const storageUrl = import.meta.env.VITE_STORAGE_URL || '';
  return storageUrl + '/api/storage/' + path;
});

const getImageUrl = computed(() => (path) => {
  if (!path) return '/src/assets/images/default-portfolio.jpg';
  return `${import.meta.env.VITE_STORAGE_URL}/api/storage/${path}`;
});

onMounted(() => {
  // Set initial event type
  if (props.portfolio.event_type) {
    const standardEventTypes = ['Wedding', 'Debut', 'Christening', 'Party'];
    if (standardEventTypes.includes(props.portfolio.event_type)) {
      formData.eventType = props.portfolio.event_type;
    } else {
      formData.eventType = 'Other';
      formData.customEventType = props.portfolio.event_type;
    }
  }
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: var(--modal-background, #fff);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.modal-header h2 {
  font-size: 1.5rem;
  color: var(--text-color);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-color);
  cursor: pointer;
  padding: 0.5rem;
}

.portfolio-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-color);
}

input, select, textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color, #ddd);
  border-radius: 6px;
  font-size: 1rem;
  background: var(--input-background, #fff);
  color: var(--text-color);
}

textarea {
  resize: vertical;
}

.inclusions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.inclusion-item {
  display: flex;
  gap: 0.5rem;
}

.remove-btn {
  padding: 0.5rem;
  background: var(--danger-color, #dc3545);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.add-btn {
  padding: 0.75rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.image-upload {
  position: relative;
  height: 200px;
  border: 2px dashed var(--border-color, #ddd);
  border-radius: 6px;
  overflow: hidden;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.upload-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-muted, #666);
}

.upload-placeholder i {
  font-size: 2rem;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--secondary-color, #6c757d);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
  }
}

.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  background: white;
  color: #333;
  cursor: pointer;
}

.form-select:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.1);
}

.album-upload {
  margin-top: 1rem;
}

.album-previews {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.album-preview-item {
  position: relative;
  aspect-ratio: 1;
}

.album-preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 4px;
}

.album-preview-item .remove-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(255, 0, 0, 0.7);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.album-preview-item .remove-btn:hover {
  background: rgba(255, 0, 0, 0.9);
}

.upload-container {
  position: relative;
  min-height: 200px;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  overflow: hidden;
}

.upload-area {
  position: relative;
  height: 200px;
  width: 100%;
  cursor: pointer;
}

.upload-content {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #6b7280;
  pointer-events: none;
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.preview-container {
  position: relative;
  width: 100%;
  height: 200px;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-actions {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.5rem;
  z-index: 5;
}

.action-btn {
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  font-weight: 500;
  color: #374151;
  backdrop-filter: blur(4px);
}

.action-btn:hover {
  background: white;
  transform: scale(1.05);
}

.action-btn i {
  font-size: 0.875rem;
}

.upload-hint {
  font-size: 0.875rem;
  color: #9ca3af;
}

/* Album grid styles */
.album-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
  padding: 1rem;
}

.album-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
}

.album-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #ef4444;
  transition: all 0.2s;
  z-index: 5;
}

.remove-image-btn:hover {
  background: #ef4444;
  color: white;
}

.add-more-images {
  aspect-ratio: 1;
  border: 2px dashed #e5e7eb;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.add-more-images:hover {
  border-color: #3b82f6;
  background-color: rgba(59, 130, 246, 0.05);
}

.add-more-images i {
  font-size: 1.5rem;
  color: #3b82f6;
}

.add-more-images span {
  font-size: 0.875rem;
  color: #6b7280;
}
</style> 