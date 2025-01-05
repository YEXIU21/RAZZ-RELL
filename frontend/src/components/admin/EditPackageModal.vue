<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Edit Package</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="edit-form">
        <div class="form-section">
          <h3>Package Details</h3>
          
          <div class="form-group">
            <label for="packageName">Package Name</label>
            <input
              type="text"
              id="packageName"
              v-model="formData.name"
              required
              placeholder="Enter package name"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="eventType">Event Type</label>
              <select id="eventType" v-model="formData.eventType" required @change="handleEventTypeChange">
                <option value="">Select event type</option>
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
              />
            </div>

            <div class="form-group">
              <label for="price">Price (₱)</label>
              <input
                type="number"
                id="price"
                v-model="formData.price"
                required
                min="0"
                step="0.01"
                placeholder="Enter price"
              />
            </div>

            <div class="form-group">
              <label for="additional_price_percentage">Additional Guest Price (%)</label>
              <input
                type="number"
                id="additional_price_percentage"
                v-model="formData.additional_price_percentage"
                required
                min="0"
                max="100"
                step="0.01"
                placeholder="Enter additional guest price percentage"
              />
            </div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea
              id="description"
              v-model="formData.description"
              required
              rows="4"
              placeholder="Enter package description"
            ></textarea>
          </div>
        </div>

        <div class="form-section">
          <h3>Package Image</h3>
          
          <div class="image-upload">
            <div class="upload-placeholder" v-if="!imagePreview && !formData.previewImage">
              <input type="file" id="image" @change="handleImageUpload" accept="image/*" class="file-input" />
              <i class="fas fa-cloud-upload-alt"></i>
              <span>Click to upload image</span>
            </div>
            <div v-else class="preview-container">
              <img 
                :src="currentImageUrl"
                alt="Package Preview"
                class="image-preview"
              />
              <div class="preview-actions">
                <button type="button" @click="handleEditImage" class="action-btn" v-if="hasAppliedImage">
                  <i class="fas fa-crop"></i> Edit
                </button>
                <button type="button" @click="handleChangeImage" class="action-btn">
                  <i class="fas fa-image"></i> Change Image
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="section-header">
            <h3>Inclusions</h3>
            <button type="button" @click="addInclusion" class="add-btn">
              <i class="fas fa-plus"></i>
              Add Inclusion
            </button>
          </div>
          
          <div class="inclusions-list">
            <div v-for="(inclusion, index) in formData.inclusions" :key="index" class="inclusion-item">
              <input
                type="text"
                v-model="formData.inclusions[index]"
                placeholder="Enter inclusion"
                required
              />
              <button type="button" @click="removeInclusion(index)" class="remove-btn" :disabled="formData.inclusions.length === 1">
                <i class="fas fa-minus"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Package Status</h3>
          
          <div class="form-row">
            <div class="form-group">
              <label for="status">Status</label>
              <select id="status" v-model="formData.status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="draft">Draft</option>
              </select>
            </div>

            <div class="form-group">        
              <label>Packs</label>
              <select v-model="formData.packs" class="form-select" required>   
                <option value="" disabled>Select number of packs</option>
                <option value="50">50 pax</option>
                <option value="100">100 pax</option>
                <option value="150">150 pax</option>
                <option value="200">200 pax</option>
                <option value="250">250 pax</option>
                <option value="300">300 pax</option>
                <option value="350">350 pax</option>
                <option value="400">400 pax</option>
                <option value="450">450 pax</option>
                <option value="500">500 pax</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" @click="$emit('close')">Cancel</button>
          <button type="submit" class="submit-btn" :disabled="isSubmitting || (!hasAppliedImage && !formData.previewImage)">
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
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
            <button @click="cancelCrop" class="close-btn">
              <i class="fas fa-times"></i>
            </button>
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
            <div class="cropper-controls">
              <button type="button" @click="rotateLeft" class="control-btn">
                <i class="fas fa-undo"></i> Rotate Left
              </button>
              <button type="button" @click="rotateRight" class="control-btn">
                <i class="fas fa-redo"></i> Rotate Right
              </button>
              <button type="button" @click="zoomIn" class="control-btn">
                <i class="fas fa-search-plus"></i> Zoom In
              </button>
              <button type="button" @click="zoomOut" class="control-btn">
                <i class="fas fa-search-minus"></i> Zoom Out
              </button>
              <button type="button" @click="resetCrop" class="control-btn">
                <i class="fas fa-sync"></i> Reset
              </button>
            </div>
            <div class="action-buttons">
              <button type="button" @click="cancelCrop" class="btn-secondary">
                Cancel
              </button>
              <button type="button" @click="applyCrop" class="btn-primary">
                Apply
              </button>
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
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import Swal from 'sweetalert2';
import defaultInclusions from '@/constants/defaultInclusions';

const props = defineProps({
  package: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['close', 'update']);
const { token } = useAuth();

const isSubmitting = ref(false);
const imagePreview = ref(null);
const isLoadingImage = ref(false);
const showCropper = ref(false);
const cropperImage = ref(null);
const cropper = ref(null);
const hasAppliedImage = ref(false);

// Initialize form data with package props
const formData = reactive({
  id: '',
  name: '',
  eventType: '',
  price: '',
  description: '',
  packs: '',
  status: 'active',
  inclusions: [''],
  image: null,
  previewImage: null,
  additional_price_percentage: '0'
});

// Function to initialize form data
const initializeFormData = () => {
  formData.id = props.package.id;
  formData.name = props.package.package_name || '';
  formData.eventType = props.package.package_type || '';
  formData.price = props.package.package_price || '';
  formData.description = props.package.package_description || '';
  formData.packs = props.package.packs || '';
  formData.status = props.package.status || 'active';
  formData.previewImage = props.package.package_image || null;
  formData.additional_price_percentage = props.package.additional_price_percentage || '0';

  // Parse inclusions
  try {
    if (props.package.package_inclusion) {
      const parsedInclusions = typeof props.package.package_inclusion === 'string' 
        ? JSON.parse(props.package.package_inclusion)
        : props.package.package_inclusion;
      
      formData.inclusions = Array.isArray(parsedInclusions) && parsedInclusions.length > 0 
        ? parsedInclusions
        : [''];
    }
  } catch (e) {
    console.error('Error parsing inclusions:', e);
    formData.inclusions = [''];
  }
};

onMounted(() => {
  initializeFormData();
  // Set hasAppliedImage to true if there's an existing image
  hasAppliedImage.value = !!formData.previewImage;
});

const handleSubmit = async () => {
  try {
    isSubmitting.value = true;
    
    // Validation
    if (!formData.name || !formData.eventType || !formData.price || !formData.description || !formData.packs) {
      throw new Error('Please fill in all required fields');
    }

    // Validate additional price percentage
    const additionalPricePercentage = parseFloat(formData.additional_price_percentage);
    if (isNaN(additionalPricePercentage) || additionalPricePercentage < 0 || additionalPricePercentage > 100) {
      throw new Error('Additional guest price percentage must be between 0% and 100%');
    }

    const formDataToSend = new FormData();
    formDataToSend.append('id', formData.id);
    formDataToSend.append('name', formData.name);
    formDataToSend.append('eventType', formData.eventType);
    formDataToSend.append('price', formData.price);
    formDataToSend.append('description', formData.description);
    formDataToSend.append('packs', formData.packs);
    formDataToSend.append('status', formData.status);
    formDataToSend.append('inclusions', JSON.stringify(formData.inclusions.filter(inc => inc.trim())));
    formDataToSend.append('additional_price_percentage', formData.additional_price_percentage);
    
    if (formData.image instanceof File) {
      formDataToSend.append('image', formData.image);
    }

    const response = await axios.post(
      `${import.meta.env.VITE_API_URL}/api/update-package`,
      formDataToSend,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      }
    );

    if (response.data.status === 200) {
      await Swal.fire({
        title: 'Success',
        text: 'Package updated successfully',
        icon: 'success'
      });
      
      emit('update');
      emit('close');
    } else {
      throw new Error(response.data.message || 'Failed to update package');
    }

  } catch (error) {
    console.error('Error updating package:', error);
    let errorMessage = 'Failed to update package. Please try again.';
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      errorMessage = Object.entries(errors)
        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
        .join('\n');
    } else if (error.message) {
      errorMessage = error.message;
    }

    await Swal.fire({
      title: 'Error',
      text: errorMessage,
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Handle edit button click
const handleEditImage = async () => {
  try {
    isLoadingImage.value = true;
    
    if (imagePreview.value) {
      cropperImage.value = imagePreview.value;
      showCropper.value = true;
      return;
    }

    if (formData.previewImage) {
      const response = await axios.get(
        `${import.meta.env.VITE_API_URL}/storage/${formData.previewImage}`,
        {
          responseType: 'arraybuffer',
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }
      );

      const blob = new Blob([response.data], { type: 'image/jpeg' });
      const reader = new FileReader();
      
      reader.onloadend = () => {
        cropperImage.value = reader.result;
        showCropper.value = true;
      };
      
      reader.readAsDataURL(blob);
    }
  } catch (error) {
    console.error('Error loading image for editing:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to load image for editing. Please try again.',
      icon: 'error'
    });
  } finally {
    isLoadingImage.value = false;
  }
};

// Handle image upload
const handleImageUpload = (event) => {
  const file = event.target?.files?.[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    Swal.fire({
      title: 'Error',
      text: 'Please upload a valid image file (JPEG, PNG, or WebP)',
      icon: 'error'
    });
    return;
  }

  formData.image = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    cropperImage.value = e.target.result;
    showCropper.value = true;
  };
  reader.readAsDataURL(file);
};

const applyCrop = () => {
  if (!cropper.value) return;

  try {
    const canvas = cropper.value.getCroppedCanvas({
      width: 1600,
      height: 900,
      imageSmoothingQuality: 'high',
      fillColor: '#fff'
    });
    
    imagePreview.value = canvas.toDataURL('image/jpeg', 0.9);
    
    canvas.toBlob((blob) => {
      if (blob) {
        formData.image = new File([blob], 'package-image.jpg', { 
          type: 'image/jpeg',
          lastModified: new Date().getTime()
        });
        formData.previewImage = null;
        hasAppliedImage.value = true;
      }
    }, 'image/jpeg', 0.9);
    
    showCropper.value = false;
  } catch (error) {
    console.error('Error applying crop:', error);
    Swal.fire({
      title: 'Error',
      text: 'Failed to crop image. Please try again.',
      icon: 'error'
    });
  }
};

const cancelCrop = () => {
  showCropper.value = false;
  if (!imagePreview.value) {
    formData.image = null;
    cropperImage.value = null;
  }
};

const rotateLeft = () => {
  if (cropper.value) {
    cropper.value.rotate(-90);
  }
};

const rotateRight = () => {
  if (cropper.value) {
    cropper.value.rotate(90);
  }
};

const zoomIn = () => {
  if (cropper.value) {
    cropper.value.zoom(0.1);
  }
};

const zoomOut = () => {
  if (cropper.value) {
    cropper.value.zoom(-0.1);
  }
};

const resetCrop = () => {
  if (cropper.value) {
    cropper.value.reset();
  }
};

// Handle change image button click
const handleChangeImage = () => {
  hasAppliedImage.value = false;
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.onchange = handleImageUpload;
  input.click();
};

// Computed property for current image URL
const currentImageUrl = computed(() => {
  if (imagePreview.value) return imagePreview.value;
  if (formData.previewImage) return `${import.meta.env.VITE_API_URL}/storage/${formData.previewImage}`;
  return null;
});

// Inclusion handling functions
const addInclusion = () => {
  formData.inclusions.push('');
};

const removeInclusion = (index) => {
  if (formData.inclusions.length > 1) {
    formData.inclusions = formData.inclusions.filter((_, i) => i !== index);
  }
};

const handleEventTypeChange = () => {
  if (formData.eventType === 'Other' && !formData.customEventType) {
    formData.inclusions = [...defaultInclusions.Other];
  } else if (formData.eventType && formData.eventType !== 'Other') {
    formData.inclusions = [...defaultInclusions[formData.eventType]];
  }
};
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
  max-width: 800px;
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

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.form-section h3 {
  font-size: 1.2rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
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

.image-preview {
  width: 100%;
  height: 200px;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 1rem;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-upload {
  position: relative;
  height: 300px;
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  background: var(--input-background);
  transition: all 0.3s ease;
}

.image-upload:hover {
  border-color: var(--border-hover);
  background: var(--hover-background);
}

.preview-container {
  position: relative;
  width: 100%;
  height: 100%;
  background: var(--input-background);
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 6px;
}

.preview-actions {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  background: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: all 0.3s ease;
  border-radius: 6px;
}

.preview-container:hover .preview-actions {
  opacity: 1;
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
  color: var(--text-muted);
  transition: all 0.3s ease;
}

.upload-placeholder i {
  font-size: 2rem;
  color: var(--text-muted);
  transition: all 0.3s ease;
}

.upload-placeholder span {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-color);
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

.inclusions-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.inclusion-item {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  background: var(--input-background);
  padding: 0.5rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.inclusion-item:hover {
  border-color: var(--border-hover);
  background: var(--hover-background);
}

.inclusion-item input {
  flex: 1;
  padding: 0.5rem;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  color: var(--text-color);
  outline: none;
}

.inclusion-item input:focus {
  outline: none;
}

.remove-btn {
  padding: 0.5rem;
  background: var(--danger-light);
  border: none;
  border-radius: 0.375rem;
  color: var(--danger-color);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
}

.remove-btn:hover {
  background: var(--danger-color);
  color: white;
}

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: var(--input-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  margin-top: 0.5rem;
}

.add-btn:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

.add-btn i {
  font-size: 0.875rem;
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
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: var(--secondary-hover, #5a6268);
  transform: translateY(-1px);
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
  transition: all 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-content {
    padding: 1rem;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
  }
}

@media (max-width: 640px) {
  .image-upload {
    height: 200px;
  }

  .upload-placeholder i {
    width: 48px;
    height: 48px;
    font-size: 20px;
  }

  .upload-placeholder span {
    font-size: 0.875rem;
  }
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
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1),
              0 8px 32px rgba(0, 0, 0, 0.4);
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
  background: var(--dark);
  position: relative;
  min-height: 400px;
  max-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cropper-modal-body :deep(.vue-cropper) {
  width: 100%;
  height: 100%;
  background: #000000;
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
}

.cropper-modal-footer {
  padding: 1.25rem;
  background: var(--card-background);
  border-top: 1px solid var(--border-color);
}

/* Override vue-cropper default styles */
:deep(.cropper-view-box) {
  outline: 2px solid #3b82f6;
  outline-color: #3b82f6;
}

:deep(.cropper-line) {
  background-color: #3b82f6;
}

:deep(.cropper-point) {
  background-color: #3b82f6;
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
  background-color: #3b82f6;
}

:deep(.cropper-face) {
  background-color: inherit;
}

:deep(.cropper-dashed) {
  border-color: #3b82f6;
}

:deep(.cropper-modal) {
  opacity: 0.8;
}

.cropper-controls {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
  justify-content: center;
}

.control-btn {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  background: var(--input-background);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
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
  justify-content: flex-end;
  gap: 1rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  color: white;
  transition: all 0.2s;
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
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: var(--hover-background);
  border-color: var(--border-color);
}

.close-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: var(--text-muted);
  transition: color 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: var(--text-color);
}

.preview-container {
  position: relative;
  width: 100%;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid var(--border-color);
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-actions {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  background: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: all 0.3s ease;
}

.preview-container:hover .preview-actions {
  opacity: 1;
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

/* Style for Edit button */
.action-btn:first-child {
  background: var(--primary-color);
  color: white;
}

.action-btn:first-child:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

/* Style for Change Image button */
.action-btn:last-child {
  background: var(--input-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.action-btn:last-child:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
  transform: translateY(-1px);
}

/* Dark mode specific overrides */
:root[data-theme="dark"] {
  .action-btn:first-child {
    background: var(--primary-color);
    color: white;
  }

  .action-btn:first-child:hover {
    background: var(--primary-hover);
  }

  .action-btn:last-child {
    background: var(--input-background);
    color: var(--text-color);
    border-color: var(--border-color);
  }

  .action-btn:last-child:hover {
    background: var(--hover-background);
    border-color: var(--border-hover);
  }

  .image-upload {
    background: var(--input-background);
    border-color: var(--border-color);
  }

  .image-upload:hover {
    border-color: var(--border-hover);
    background: var(--hover-background);
  }

  .preview-container {
    background: var(--input-background);
  }

  .upload-placeholder {
    color: var(--text-muted);
  }

  .upload-placeholder i {
    color: var(--text-muted);
  }

  .upload-placeholder span {
    color: var(--text-color);
  }
}
</style> 