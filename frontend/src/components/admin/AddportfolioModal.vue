<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add New Portfolio</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="portfolio-form">
        <div class="form-group">
          <label for="portfolioName">Portfolio Title</label>
          <input type="text" id="portfolioName" v-model="formData.title" required placeholder="Enter Portfolio Title"
            class="form-input" />
        </div>

        <div class="form-group">
          <label for="description">Portfolio Description</label>
          <textarea id="description" v-model="formData.description" required rows="4"
            placeholder="Enter Portfolio Description" class="form-input"></textarea>
        </div>

        <div class="form-group">
          <label for="eventType">Event Type</label>
          <select id="eventType" v-model="formData.eventType" required class="form-input"
            @change="handleEventTypeChange">
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
          <input type="text" id="customEventType" v-model="formData.customEventType" required
            placeholder="Enter custom event type" class="form-input" />
        </div>

        <div class="form-group">
          <label for="coverImage">Cover Image</label>
          <div class="upload-container" :class="{ 'has-image': imagePreview }">
            <div class="upload-content" v-if="!imagePreview">
              <input type="file" id="coverImage" ref="coverImageInput" @change="handleImageUpload" accept="image/*"
                class="file-input" />
              <i class="fas fa-cloud-upload-alt"></i>
              <span>Click to upload cover image</span>
              <p class="upload-hint">Supports: JPG, PNG, WebP (Max 5MB)</p>
            </div>
            <div v-else class="preview-container">
              <img :src="getPreviewImageUrl" 
                alt="Preview" 
                class="image-preview" />
              <div class="preview-overlay">
                <div class="preview-actions">
                  <button type="button" @click="handleEditImage" class="action-btn">
                    <i class="fas fa-crop"></i> Edit
                  </button>
                  <button type="button" @click="handleChangeImage" class="action-btn">
                    <i class="fas fa-image"></i> Change Image
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="albumImages">Album Images</label>
          <div class="upload-container album-container" :class="{ 'has-images': albumPreviews.length > 0 }">
            <input type="file" id="albumImages" ref="albumImagesInput" @change="handleAlbumImagesUpload"
              accept="image/*" multiple class="file-input" />
            <div class="upload-content" v-if="albumPreviews.length === 0">
              <i class="fas fa-images"></i>
              <span>Click to upload album images</span>
              <p class="upload-hint">Select multiple images (Max 10 images)</p>
            </div>
            <div v-else class="album-grid">
              <div v-for="(preview, index) in albumPreviews" :key="index" class="album-item">
                <img :src="preview" alt="Album preview" />
                <button type="button" @click="removeAlbumImage(index)" class="remove-image-btn">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div class="add-more-images" @click="$refs.albumImagesInput.click()" v-if="albumPreviews.length < 10">
                <i class="fas fa-plus"></i>
                <span>Add More</span>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Adding...' : 'Add Portfolio' }}
          </button>
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
            <vue-cropper ref="cropper" :src="cropperImage" :aspect-ratio="16 / 9" :view-mode="2" :guides="true"
              :background="true" :rotatable="true" :zoomable="true" :movable="true" :responsive="true" :modal="true"
              :autoCropArea="1" :center="true" :highlight="true" :cropBoxMovable="true" :cropBoxResizable="true"
              :toggleDragModeOnDblclick="true"></vue-cropper>
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

const emit = defineEmits(['close', 'success']);

const formData = reactive({
  title: '',
  description: '',
  eventType: '',
  customEventType: '',
  image: null,
  albumImages: []
});

const imagePreview = ref(null);
const albumPreviews = ref([]);
const isSubmitting = ref(false);
const showCropper = ref(false);
const cropperImage = ref(null);
const cropper = ref(null);
const hasAppliedImage = ref(false);

const getPreviewImageUrl = computed(() => {
  if (!imagePreview.value) {
    return '/src/assets/images/default-portfolio.jpg';
  }
  if (imagePreview.value.startsWith('data:')) {
    return imagePreview.value;
  }
  const storageUrl = import.meta.env.VITE_API_URL || '';
  return `${storageUrl}/storage/${imagePreview.value}`;
});

const handleImageUpload = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    alert('Please upload a valid image file (JPEG, PNG, or WebP)');
    event.target.value = '';
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    cropperImage.value = e.target.result;
    showCropper.value = true;
  };
  reader.readAsDataURL(file);
};

const handleAlbumImagesUpload = async (event) => {
  const files = Array.from(event.target.files || []);
  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  const maxSize = 5 * 1024 * 1024; // 5MB

  const validFiles = files.filter(file => {
    if (!allowedTypes.includes(file.type)) {
      return false;
    }
    if (file.size > maxSize) {
      alert(`File ${file.name} is too large. Maximum size is 5MB.`);
      return false;
    }
    return true;
  });

  if (validFiles.length !== files.length) {
    alert('Some files were skipped. Please ensure all files are images (JPEG, PNG, or WebP) and under 5MB.');
  }

  // Check total number of images
  if (formData.albumImages.length + validFiles.length > 10) {
    alert('Maximum 10 images allowed in the album.');
    return;
  }

  // Upload each valid file
  for (const file of validFiles) {
    try {
      const formDataToSend = new FormData();
      formDataToSend.append('file', file);
      
      const response = await axios.post(
        `${import.meta.env.VITE_API_URL}/api/storage/portfolios`,
        formDataToSend,
        {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        }
      );

      if (response.data && response.data.path) {
        formData.albumImages.push(response.data.path);
        albumPreviews.value.push(response.data.path);
      }
    } catch (error) {
      console.error('Error uploading album image:', error);
      Swal.fire({
        title: 'Error',
        text: `Failed to upload image: ${file.name}`,
        icon: 'error'
      });
    }
  }
};

const removeAlbumImage = async (index) => {
  try {
    const imagePath = formData.albumImages[index];
    
    // Remove from storage if it's a stored image
    if (imagePath && imagePath.startsWith('portfolios/')) {
      await axios.delete(
        `${import.meta.env.VITE_API_URL}/api/storage/${imagePath}`,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        }
      );
    }

    // Remove from local state
    formData.albumImages.splice(index, 1);
    albumPreviews.value.splice(index, 1);
  } catch (error) {
    console.error('Error removing album image:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to remove image',
      icon: 'error'
    });
  }
};

const removeCoverImage = () => {
  formData.image = null;
  imagePreview.value = null;
  if (this.$refs.coverImageInput) {
    this.$refs.coverImageInput.value = '';
  }
};

const handleEventTypeChange = () => {
  if (formData.eventType !== 'Other') {
    formData.customEventType = '';
  }
};

const rotateLeft = () => {
  cropper.value.rotate(-90);
};

const rotateRight = () => {
  cropper.value.rotate(90);
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
  formData.image = null;
  imagePreview.value = null;
  if (this.$refs.coverImageInput) {
    this.$refs.coverImageInput.value = '';
  }
};

const applyCrop = () => {
  try {
    const canvas = cropper.value.getCroppedCanvas({
      width: 1600,
      height: 900,
      imageSmoothingQuality: 'high',
      fillColor: '#fff'
    });
    
    // Store the data URL for immediate preview
    imagePreview.value = canvas.toDataURL('image/jpeg', 0.9);
    
    canvas.toBlob((blob) => {
      if (blob) {
        formData.image = new File([blob], 'portfolio-image.jpg', { 
          type: 'image/jpeg',
          lastModified: new Date().getTime()
        });
        hasAppliedImage.value = true;
        showCropper.value = false;
      }
    }, 'image/jpeg', 0.9);
  } catch (error) {
    console.error('Error applying crop:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to crop image. Please try again.',
      icon: 'error'
    });
  }
};

const handleSubmit = async () => {
  if (isSubmitting.value) return;

  if (!formData.image) {
    Swal.fire({
      title: 'Error',
      text: 'Please upload a cover image',
      icon: 'error',
    });
    return;
  }

  if (!hasAppliedImage.value) {
    Swal.fire({
      title: 'Error',
      text: 'Please crop and apply the image before submitting',
      icon: 'error',
    });
    return;
  }

  try {
    isSubmitting.value = true;
    
    // First upload the main image
    const mainImageFormData = new FormData();
    mainImageFormData.append('file', formData.image);
    
    const mainImageResponse = await axios.post(
      `${import.meta.env.VITE_API_URL}/storage/portfolios`,
      mainImageFormData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      }
    );

    if (!mainImageResponse.data || !mainImageResponse.data.path) {
      throw new Error('Failed to upload main image');
    }

    // Update the preview with the new path
    imagePreview.value = mainImageResponse.data.path;

    // Then upload album images if any
    const albumImagePaths = [];
    if (formData.albumImages && formData.albumImages.length > 0) {
      for (const image of formData.albumImages) {
        const albumImageFormData = new FormData();
        albumImageFormData.append('file', image);
        
        const albumImageResponse = await axios.post(
          `${import.meta.env.VITE_API_URL}/storage/portfolios`,
          albumImageFormData,
          {
            headers: {
              'Content-Type': 'multipart/form-data',
              'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
            }
          }
        );
        
        if (albumImageResponse.data && albumImageResponse.data.path) {
          albumImagePaths.push(albumImageResponse.data.path);
        }
      }
    }

    // Finally submit the portfolio data
    const portfolioData = {
      title: formData.title,
      description: formData.description,
      event_type: formData.eventType === 'Other' ? formData.customEventType : formData.eventType,
      main_image_url: mainImageResponse.data.path,
      images_url: albumImagePaths
    };

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/add-portfolio`,
      portfolioData,
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        }
      }
    );

    if (response.status === 200) {
      Swal.fire({
        title: 'Success',
        text: 'Portfolio added successfully',
        icon: 'success'
      });
      emit('success');
      emit('close');
    }
  } catch (error) {
    console.error('Error submitting portfolio:', error);
    Swal.fire({
      title: 'Error',
      text: error.message || 'Failed to add portfolio. Please try again.',
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const handleEditImage = () => {
  if (imagePreview.value) {
    cropperImage.value = imagePreview.value;
    showCropper.value = true;
  }
};

const handleChangeImage = () => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = handleImageUpload;
  input.click();
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: var(--card-background);
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2rem;
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-lg);
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
  font-weight: 600;
}

.close-btn {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0.5rem;
  transition: all 0.2s ease;
}

.close-btn:hover {
  color: var(--text-color);
  background: var(--hover-background);
  border-radius: 50%;
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
  font-size: 0.875rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 1rem;
  background: var(--input-background);
  color: var(--text-color);
  transition: all 0.2s;
}

.form-input:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-light);
  outline: none;
}

.upload-container {
  position: relative;
  min-height: 200px;
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  background: var(--card-background);
  transition: all 0.2s ease;
}

.upload-container:hover {
  border-color: var(--primary-color);
  background: var(--hover-background);
}

.upload-content {
  position: relative;
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: var(--text-secondary);
}

.file-input {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  z-index: 1;
}

.preview-container {
  position: relative;
  width: 100%;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid var(--border-color);
  background: var(--card-background);
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: all 0.3s ease;
}

.preview-container:hover .preview-overlay {
  opacity: 1;
}

.preview-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn i {
  font-size: 1rem;
}

/* Style for Edit button */
.action-btn:first-child {
  background: var(--primary-color);
  color: white;
}

.action-btn:first-child:hover {
  background: var(--primary-hover);
}

/* Style for Change Image button */
.action-btn:last-child {
  background: var(--card-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.action-btn:last-child:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

.upload-hint {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.remove-image-btn:hover {
  background: var(--error-color);
  transform: scale(1.1);
}

.album-container {
  min-height: 250px;
}

.album-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
  padding: 1rem;
}

.album-item {
  position: relative;
  width: 100%;
  height: 150px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid var(--border-color);
  background: var(--card-background);
}

.album-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.add-more-images {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 150px;
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--card-background);
}

.add-more-images:hover {
  background: var(--hover-background);
  border-color: var(--primary-color);
}

.add-more-images i {
  font-size: 1.5rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.add-more-images span {
  font-size: 0.875rem;
  color: var(--text-color);
  font-weight: 500;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.cancel-btn {
  padding: 0.75rem 1.5rem;
  background: var(--card-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.cancel-btn:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

.submit-btn {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Cropper Modal Styles */
.cropper-modal-wrapper {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.95);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-modal {
  position: relative;
  width: 90%;
  max-width: 1200px;
  background: var(--card-background);
  border-radius: 12px;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
}

.cropper-modal-content {
  display: flex;
  flex-direction: column;
  height: 90vh;
  max-height: 800px;
  background: var(--card-background);
  border-radius: 12px;
  overflow: hidden;
}

.cropper-modal-header {
  padding: 1.25rem;
  background: var(--card-background);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cropper-modal-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: var(--text-color);
  font-weight: 600;
}

.cropper-modal-body {
  flex: 1;
  background: #000000;
  position: relative;
  min-height: 400px;
  max-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-modal-footer {
  padding: 1.25rem;
  background: var(--card-background);
  border-top: 1px solid var(--border-color);
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cropper-controls {
  display: flex;
  gap: 0.75rem;
  flex-wrap: nowrap;
}

.control-btn {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--card-background);
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-color);
  white-space: nowrap;
}

.control-btn:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
}

.btn-primary:hover {
  background: var(--primary-hover);
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  color: var(--text-color);
}

.btn-secondary:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

/* Override vue-cropper default styles */
:deep(.cropper-view-box) {
  outline: 2px solid var(--primary-color);
  outline-color: var(--primary-color);
}

:deep(.cropper-line) {
  background-color: var(--primary-color);
}

:deep(.cropper-point) {
  background-color: var(--primary-color);
  width: 8px;
  height: 8px;
}

:deep(.cropper-point.point-se) {
  width: 10px;
  height: 10px;
}

:deep(.cropper-center) {
  width: 8px;
  height: 8px;
  background-color: var(--primary-color);
}

:deep(.cropper-face) {
  background-color: inherit;
}

:deep(.cropper-dashed) {
  border-color: var(--primary-color);
}

:deep(.cropper-modal) {
  opacity: 0.8;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
    width: 95%;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
  }

  .album-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.75rem;
  }

  .cropper-controls {
    flex-wrap: wrap;
    justify-content: center;
  }

  .control-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }
}
</style>