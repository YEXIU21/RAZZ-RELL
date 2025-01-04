<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add New Package</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Stepper -->
      <div class="stepper">
        <div class="step" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
          <div class="step-number">1</div>
          <div class="step-line"></div>
        </div>
        <div class="step" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
          <div class="step-number">2</div>
          <div class="step-line"></div>
        </div>
        <div class="step" :class="{ active: currentStep === 3, completed: currentStep > 3 }">
          <div class="step-number">3</div>
          <div class="step-line"></div>
        </div>
        <div class="step" :class="{ active: currentStep === 4 }">
          <div class="step-number">4</div>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="package-form">
        <!-- Step 1: Event Type Selection -->
        <div v-if="currentStep === 1" class="form-step">
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
        </div>

        <!-- Step 2: Package Details -->
        <div v-if="currentStep === 2" class="form-step">
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
          
          <div class="form-group">
            <label>Packs</label>
            <select v-model="formData.pack" class="form-select" required>   
              <option value="" disabled>Select number of packs</option>
              <option value="50">50 pax</option>
              <option value="100">100 pax</option>
              <option value="150">150 pax</option>
              <option value="other">Other</option>
            </select>
            <input
              v-if="formData.pack === 'other'"
              type="number"
              v-model="formData.customPack"
              class="form-control mt-2"
              placeholder="Enter custom pack size"
              min="200"
              max="500"
              step="50"
              required
            />
          </div>

          <div class="form-group">
            <label>Package Price*</label>
            <div class="input-group">
              <span class="currency-symbol">₱</span>
              <input 
                type="number"
                v-model="formData.price"
                required
                placeholder="Enter base package price"
                min="0"
                step="0.01"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Additional Guest Price (%)</label>
            <div class="input-group">
              <input 
                type="number"
                v-model="formData.additional_price_percentage"
                placeholder="Enter percentage for additional guests"
                min="0"
                max="100"
                step="1"
              />
              <span class="percentage-symbol">%</span>
            </div>
            <small class="helper-text">This percentage will be added to the per-guest price for additional guests above the base pack size.</small>
          </div>

          <div class="form-group">
            <div class="checkbox-group">
              <input 
                type="checkbox" 
                id="pricePerDay"
                v-model="formData.price_per_day"
              />
              <label for="pricePerDay">Price is per day</label>
            </div>
            <small class="helper-text">Price will be charged per day of the event</small>
          </div>

          <div class="form-group" v-if="!formData.price_per_day">
            <label>Price Increase Per Additional Day</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input
                type="number"
                v-model="formData.price_increase_per_day"
                class="form-control"
                min="0"
                max="999999999999"
                placeholder="Enter price increase for each additional day"
                @input="handlePriceIncreaseInput"
              >
            </div>
            <small class="text-muted">
              Leave empty or set to 0 if no additional charge for extra days
            </small>
          </div>
        </div>

        <!-- Step 3: Package Details -->
        <div v-if="currentStep === 3" class="form-step">
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

          <div class="form-group">
            <label>Inclusions</label>
            <div class="inclusions-list">
              <div v-for="(inclusion, index) in formData.inclusions" :key="index" class="inclusion-item">
                <input
                  type="text"
                  v-model="formData.inclusions[index]"
                  placeholder="Enter inclusion"
                  class="w-full"
                  style="width: 100%"
                />
                <button type="button" @click="removeInclusion(index)" class="remove-btn">
                  <i class="fas fa-minus"></i>
                </button>
              </div>
            </div>
            <button type="button" @click="addInclusion" class="add-btn">
              <i class="fas fa-plus"></i> Add Inclusion
            </button>
          </div>

          <div class="form-group">
            <label for="image" class="upload-label">Package Image</label>
            <div class="image-upload-container" :class="{ 'has-image': imagePreview }">
              <div v-if="!imagePreview" class="upload-area" @click="triggerFileInput" @dragover.prevent @drop.prevent="handleFileDrop">
                <input 
                  type="file" 
                  id="image" 
                  ref="fileInput"
                  @change="handleImageUpload" 
                  accept="image/*" 
                  class="file-input" 
                  style="display: none;"
                />
                <div class="upload-content">
                  <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                  </div>
                  <div class="upload-text">
                    <h4>Drag and drop your image here</h4>
                    <p>or <span class="browse-text">browse files</span></p>
                  </div>
                  <div class="upload-info">
                    <p>Supported formats: JPEG, PNG, WebP</p>
                    <p>Maximum file size: 5MB</p>
                  </div>
                </div>
              </div>
              
              <div v-else class="image-preview-container">
                <img :src="imagePreview" alt="Preview" class="preview-image" />
                <div class="image-overlay">
                  <div class="overlay-actions">
                    <button type="button" @click="handleEditImage" class="overlay-btn edit-btn">
                      <i class="fas fa-crop"></i>
                      Edit
                    </button>
                    <button type="button" @click="handleChangeImage" class="overlay-btn change-btn">
                      <i class="fas fa-image"></i>
                      Change Image
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Review -->
        <div v-if="currentStep === 4" class="form-step">
          <div class="form-group">
            <h3>Review Package Details</h3>
            <div class="review-details">
              <div class="review-item">
                <span class="label">Event Type:</span>
                <span class="value">{{ formData.eventType }}</span>
              </div>
              <div class="review-item">
                <span class="label">Package Name:</span>
                <span class="value">{{ formData.name }}</span>
              </div>
              <div class="review-item">
                <span class="label">Price:</span>
                <span class="value">₱{{ formatNumber(formData.price) }}</span>
              </div>
              <div class="review-item">
                <span class="label">Packs:</span>
                <span class="value">{{ formData.pack === 'other' ? formData.customPack : formData.pack }} pax</span>
              </div>
              <div class="review-item">
                <span class="label">Number of Inclusions:</span>
                <span class="value">{{ formData.inclusions.length }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button 
            type="button" 
            class="back-btn" 
            @click="prevStep" 
            v-if="currentStep > 1"
          >
            Back
          </button>
          <button 
            type="button" 
            class="next-btn" 
            @click="nextStep" 
            v-if="currentStep < 4"
          >
            Next
          </button>
          <button 
            type="submit" 
            class="submit-btn" 
            :disabled="isSubmitting" 
            v-if="currentStep === 4"
          >
            <i class="fas fa-spinner fa-spin" v-if="isSubmitting"></i>
            {{ isSubmitting ? 'Adding...' : 'Add Package' }}
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
              <button type="button" @click="cancelCrop" class="btn-secondary">Cancel</button>
              <button type="button" @click="applyCrop" class="btn-primary">Apply</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import Swal from 'sweetalert2';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';
import defaultInclusions from '@/constants/defaultInclusions';

// Utility function to generate consistent colors from strings
const generateEventTypeColor = (eventType) => {
  let hash = 0;
  for (let i = 0; i < eventType.length; i++) {
    hash = eventType.charCodeAt(i) + ((hash << 5) - hash);
  }
  
  // Generate pastel colors for better visibility
  const h = Math.abs(hash) % 360; // Hue
  const s = 25 + (Math.abs(hash) % 30); // Saturation between 25-55%
  const l = 85 + (Math.abs(hash) % 10); // Lightness between 85-95%
  
  return {
    background: `hsl(${h}, ${s}%, ${l}%)`,
    color: `hsl(${h}, ${s + 20}%, 25%)` // Darker text color
  };
};

const { token } = useAuth();
const emit = defineEmits(['close', 'package-added']);
const currentStep = ref(1);
const imagePreview = ref(null);
const fileInput = ref(null);

const formData = reactive({
  eventType: '',
  customEventType: '',
  name: '',
  pack: '',
  customPack: null,
  price: '',
  additional_price_percentage: 0,
  price_per_day: false,
  price_increase_per_day: 0,
  description: '',
  inclusions: [''],
  image: null
});

const isSubmitting = ref(false);
const showCropper = ref(false);
const cropperImage = ref(null);
const hasAppliedImage = ref(false);
const cropper = ref(null);

const nextStep = () => {
  if (validateCurrentStep()) {
    currentStep.value++;
  }
};

const prevStep = () => {
  currentStep.value--;
};

const validateCurrentStep = () => {
  switch (currentStep.value) {
    case 1:
      if (!formData.eventType) {
        Swal.fire({
          icon: 'error',
          title: 'Required Fields',
          text: 'Please select an event type'
        });
        return false;
      }
      if (formData.eventType === 'Other' && !formData.customEventType) {
        Swal.fire({
          icon: 'error',
          title: 'Required Fields',
          text: 'Please enter a custom event type'
        });
        return false;
      }
      return true;
    case 2:
      if (!formData.name || !formData.price || !formData.pack) {
        Swal.fire({
          icon: 'error',
          title: 'Required Fields',
          text: 'Please fill in package name, price, and select number of packs'
        });
        return false;
      }
      if (formData.pack === 'other' && !formData.customPack) {
        Swal.fire({
          icon: 'error',
          title: 'Required Fields',
          text: 'Please enter a custom pack size'
        });
        return false;
      }
      return true;
    case 3:
      if (!formData.description || formData.inclusions.length === 0) {
        Swal.fire({
          icon: 'error',
          title: 'Required Fields',
          text: 'Please add description and at least one inclusion'
        });
        return false;
      }
      return true;
    default:
      return true;
  }
};

const formatNumber = (num) => {
  return Number(num).toLocaleString('en-PH');
};

const addInclusion = () => {
  formData.inclusions.push('');
};

const removeInclusion = (index) => {
  formData.inclusions = formData.inclusions.filter((_, i) => i !== index);
};

const handleImageUpload = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    Swal.fire({
      title: 'Invalid File Type',
      text: 'Please upload a JPEG, PNG, or WebP image',
      icon: 'error'
    });
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    Swal.fire({
      title: 'File Too Large',
      text: 'Please upload an image smaller than 5MB',
      icon: 'error'
    });
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    cropperImage.value = e.target.result;
    showCropper.value = true;
  };
  reader.readAsDataURL(file);
  event.target.value = ''; // Reset the input
};

const handleEventTypeChange = () => {
  if (formData.eventType === 'Other' && !formData.customEventType) {
    formData.inclusions = [...defaultInclusions.Other];
  } else if (formData.eventType && formData.eventType !== 'Other') {
    formData.inclusions = [...defaultInclusions[formData.eventType]];
  }
};

const handlePriceInput = (event) => {
  const value = event.target.value;
  // Remove any non-numeric characters except decimal point
  let numericValue = value.replace(/[^\d.]/g, '');
  
  // Ensure only one decimal point
  const decimalCount = (numericValue.match(/\./g) || []).length;
  if (decimalCount > 1) {
    numericValue = numericValue.replace(/\.(?=.*\.)/g, '');
  }
  
  // Split number into integer and decimal parts
  const parts = numericValue.split('.');
  
  // Limit integer part to 12 digits (for 1 trillion) and decimal part to 2 digits
  if (parts[0] && parts[0].length > 12) {
    parts[0] = parts[0].slice(0, 12);
  }
  if (parts[1] && parts[1].length > 2) {
    parts[1] = parts[1].slice(0, 2);
  }
  
  // Reconstruct the number
  numericValue = parts.join('.');
  
  // Ensure the value doesn't exceed 999,999,999,999.99
  const finalValue = Math.min(Number(numericValue), 999999999999.99);
  
  // Update the form data
  formData.price = finalValue.toString();
};

const handlePriceIncreaseInput = (event) => {
  const value = event.target.value;
  // Remove any non-numeric characters except decimal point
  let numericValue = value.replace(/[^\d.]/g, '');
  
  // Ensure only one decimal point
  const decimalCount = (numericValue.match(/\./g) || []).length;
  if (decimalCount > 1) {
    numericValue = numericValue.replace(/\.(?=.*\.)/g, '');
  }
  
  // Split number into integer and decimal parts
  const parts = numericValue.split('.');
  
  // Limit integer part to 12 digits and decimal part to 2 digits
  if (parts[0] && parts[0].length > 12) {
    parts[0] = parts[0].slice(0, 12);
  }
  if (parts[1] && parts[1].length > 2) {
    parts[1] = parts[1].slice(0, 2);
  }
  
  // Reconstruct the number
  numericValue = parts.join('.');
  
  // Ensure the value doesn't exceed 999,999,999,999.99
  const finalValue = Math.min(Number(numericValue), 999999999999.99);
  
  // Update the form data
  formData.price_increase_per_day = finalValue.toString();
};

const API_URL = import.meta.env.VITE_API_URL || 'https://razz-rell.onrender.com';

const handleSubmit = async () => {
  try {
    const packageData = new FormData();
    packageData.append('package_name', formData.name);
    packageData.append('package_type', formData.eventType === 'Other' ? formData.customEventType : formData.eventType);
    packageData.append('package_description', formData.description);
    packageData.append('package_price', formData.price);
    packageData.append('additional_price_percentage', formData.additional_price_percentage);
    packageData.append('packs', formData.pack === 'other' ? formData.customPack : formData.pack);
    packageData.append('package_inclusion', JSON.stringify(formData.inclusions));
    packageData.append('price_per_day', formData.price_per_day ? 1 : 0);
    packageData.append('price_increase_per_day', formData.price_increase_per_day);
    
    if (formData.image) {
      packageData.append('package_image', formData.image);
    }

    const token = localStorage.getItem('auth_token');
    if (!token) {
      throw new Error('Authentication token not found');
    }

    const response = await axios.post(`${API_URL}/api/add-package`, packageData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      withCredentials: true
    });

    if (response.data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Package added successfully',
        timer: 1500,
        showConfirmButton: false
      });
      emit('package-added');
      emit('close');
    }
  } catch (error) {
    console.error('Error adding package:', error);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
      console.error('Response headers:', error.response.headers);
    }
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.response?.data?.message || 'Failed to add package. Please try again.'
    });
  }
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
  cropperImage.value = null;
  if (!imagePreview.value) {
    formData.image = null;
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

const retakeCrop = () => {
  if (imagePreview.value) {
    cropperImage.value = imagePreview.value;
    showCropper.value = true;
  }
};

const handleEditImage = () => {
  if (imagePreview.value) {
    cropperImage.value = imagePreview.value;
    showCropper.value = true;
  }
};

const handleChangeImage = () => {
  hasAppliedImage.value = false;
  fileInput.value.click();
};

const removeImage = () => {
  imagePreview.value = null;
  cropperImage.value = null;
  formData.image = null;
  fileInput.value.value = '';
};

const triggerFileInput = () => {
  if (!showCropper.value) {
    fileInput.value.click();
  }
};

const handleFileDrop = (event) => {
  event.preventDefault();
  const file = event.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    handleImageUpload({ target: { files: [file] } });
  }
};

onMounted(() => {
  getAllGuessst();
}); 
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
  border-radius: 16px;
  width: 95%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--card-background);
  position: sticky;
  top: 0;
  z-index: 10;
}

.modal-header h2 {
  font-size: 1.75rem;
  font-weight: 600;
  color: var(--text-color);
  font-family: 'Inter', sans-serif;
  margin: 0;
  line-height: 1.2;
}

.close-btn {
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  color: var(--text-secondary);
}

.close-btn:hover {
  background: var(--hover-background);
  color: var(--text-color);
}

.close-btn i {
  font-size: 1.25rem;
}

.package-form {
  padding: 1.5rem;
}

.form-step {
  background: var(--card-background);
  border-radius: 8px;
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: var(--text-color);
  background-color: var(--input-background);
  transition: all 0.2s;
}

.form-group input[type="text"]:focus,
.form-group input[type="number"]:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px var(--primary-light);
}

.input-group {
  display: flex;
  align-items: stretch;
  width: 100%;
  border: 1px solid var(--border-color);
  border-radius: 0.5rem;
  overflow: hidden;
  background: var(--input-background);
}

.input-group input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: none;
  outline: none;
  font-size: 0.875rem;
  color: var(--text-color);
  background: transparent;
}

.input-group-text,
.currency-symbol,
.percentage-symbol {
  display: flex;
  align-items: center;
  padding: 0 1rem;
  background-color: var(--hover-background);
  color: var(--text-secondary);
  font-size: 0.875rem;
  border-right: 1px solid var(--border-color);
}

.percentage-symbol {
  border-right: none;
  border-left: 1px solid var(--border-color);
}

.helper-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin-top: 0.375rem;
}

.checkbox-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0.75rem 0;
}

.checkbox-group input[type="checkbox"] {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
  border: 1px solid var(--border-color);
  cursor: pointer;
  accent-color: var(--primary-color);
}

.checkbox-group label {
  margin: 0;
  font-size: 0.875rem;
  color: var(--text-color);
  cursor: pointer;
}

/* Stepper improvements */
.stepper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 600px;
  margin: 0 auto 2rem;
  padding: 1rem 2rem;
  background: var(--hover-background);
  border-radius: 0.75rem;
  border: 1px solid var(--border-color);
}

.step {
  display: flex;
  align-items: center;
  position: relative;
  flex: 1;
}

.step:last-child {
  flex: 0;
}

.step-number {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: var(--hover-background);
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
  z-index: 1;
  transition: all 0.2s;
  border: 1px solid var(--border-color);
}

.step.active .step-number {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
}

.step.completed .step-number {
  background: var(--success-color);
  color: white;
  border-color: var(--success-color);
}

.step-line {
  flex: 1;
  height: 2px;
  background: var(--border-color);
  margin: 0 0.5rem;
  transition: all 0.2s;
}

.step.active .step-line,
.step.completed .step-line {
  background: var(--primary-color);
}

/* Form actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
  padding: 1.25rem 1.5rem;
  background: var(--hover-background);
  border-top: 1px solid var(--border-color);
}

.back-btn,
.next-btn,
.submit-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.back-btn {
  background: var(--hover-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.back-btn:hover {
  background: var(--hover-color);
}

.next-btn,
.submit-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.next-btn:hover,
.submit-btn:hover {
  background: var(--primary-hover);
}

.submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Inclusions section */
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
  background: var(--hover-background);
  padding: 0.5rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.inclusion-item:hover {
  border-color: var(--border-hover);
  background: var(--hover-color);
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
  background: var(--error-light);
  border: none;
  border-radius: 0.375rem;
  color: var(--error-color);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 32px;
  height: 32px;
}

.remove-btn:hover {
  background: var(--error-color);
  color: white;
}

.add-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: var(--hover-background);
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
  background: var(--hover-color);
  border-color: var(--border-hover);
}

.add-btn i {
  font-size: 0.875rem;
}

/* Review section */
.review-details {
  background: var(--hover-background);
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-top: 1rem;
  border: 1px solid var(--border-color);
}

.review-item {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border-color);
}

.review-item:last-child {
  border-bottom: none;
}

.review-item .label {
  color: var(--text-secondary);
  font-weight: 500;
}

.review-item .value {
  color: var(--text-color);
  font-weight: 600;
}

/* Image upload section */
.image-upload-container {
  width: 100%;
  background: var(--card-background);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
  border: 2px dashed var(--border-color);
  min-height: 300px;
  position: relative;
}

.upload-area {
  position: relative;
  width: 100%;
  min-height: 300px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.upload-area:hover {
  background: var(--hover-background);
}

.upload-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 2rem;
  text-align: center;
}

.upload-icon {
  width: 64px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--hover-background);
  border-radius: 50%;
  margin-bottom: 1rem;
}

.upload-icon i {
  font-size: 24px;
  color: var(--text-secondary);
}

.upload-text h4 {
  font-size: 1rem;
  font-weight: 500;
  color: var(--text-color);
  margin: 0;
}

.upload-text p {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0.5rem 0 0 0;
}

.browse-text {
  color: var(--primary-color);
  font-weight: 500;
  cursor: pointer;
}

.upload-info {
  margin-top: 1rem;
  text-align: center;
}

.upload-info p {
  font-size: 0.75rem;
  color: var(--text-secondary);
  margin: 0.25rem 0;
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

.cropper-modal-header .close-btn {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: var(--text-color);
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
  border-radius: 6px;
}

.cropper-modal-header .close-btn:hover {
  background: var(--hover-background);
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
  transform: translateY(-1px);
}

.action-buttons {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 0.875rem;
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary:hover {
  background: var(--primary-hover);
}

/* Dark mode specific styles */
:root[data-theme="dark"] {
  .modal-content,
  .modal-header,
  .form-step,
  .cropper-modal,
  .cropper-modal-content,
  .cropper-modal-header,
  .cropper-modal-footer {
    background: var(--card-background);
    border-color: var(--border-color);
  }

  .form-group input[type="text"],
  .form-group input[type="number"],
  .form-group select,
  .form-group textarea,
  .input-group {
    background: var(--input-background);
    color: var(--text-color);
    border-color: var(--border-color);
  }

  .input-group input {
    color: var(--text-color);
  }

  .input-group-text,
  .currency-symbol,
  .percentage-symbol {
    background: var(--hover-background);
    color: var(--text-secondary);
    border-color: var(--border-color);
  }

  .stepper,
  .review-details,
  .inclusion-item {
    background: var(--hover-background);
    border-color: var(--border-color);
  }

  .step-number {
    background: var(--hover-background);
    color: var(--text-secondary);
    border-color: var(--border-color);
  }

  .upload-area:hover {
    background: var(--hover-background);
  }

  .upload-icon {
    background: var(--hover-background);
  }

  .upload-icon i {
    color: var(--text-secondary);
  }

  .browse-text {
    color: var(--primary-color);
  }

  .control-btn,
  .btn-secondary {
    background: var(--card-background);
    border-color: var(--border-color);
    color: var(--text-color);
  }

  .control-btn:hover,
  .btn-secondary:hover {
    background: var(--hover-background);
    border-color: var(--border-hover);
  }
}

@media (max-width: 768px) {
  .inclusions-list {
    grid-template-columns: 1fr;
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

.overlay-btn {
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
  background: var(--card-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.overlay-btn i {
  font-size: 1rem;
}

.overlay-btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.edit-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.edit-btn:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
}

.change-btn {
  background: var(--card-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.change-btn:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

/* Dark mode styles */
:root[data-theme="dark"] {
  .change-btn {
    background: var(--card-background);
    color: var(--text-color);
  }

  .change-btn:hover {
    background: var(--hover-background);
  }
}

.image-preview-container {
  position: relative;
  width: 100%;
  height: 300px;
  overflow: hidden;
  border-radius: 8px;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-overlay {
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
  transition: opacity 0.2s ease;
}

.image-preview-container:hover .image-overlay {
  opacity: 1;
}

.overlay-actions {
  display: flex;
  gap: 1rem;
}

.overlay-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.edit-btn {
  background: var(--primary-color);
  color: white;
  border: none;
}

.edit-btn:hover {
  background: var(--primary-hover);
}

.change-btn {
  background: var(--card-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.change-btn:hover {
  background: var(--hover-background);
  border-color: var(--border-hover);
}

/* Dark mode styles */
:root[data-theme="dark"] {
.change-btn {
    background: var(--card-background);
    color: var(--text-color);
}

.change-btn:hover {
    background: var(--hover-background);
  }
}

/* Ensure the cropper container is visible */
:deep(.cropper-container) {
  background-color: #000;
}

:deep(.cropper-view-box) {
  outline: 2px solid var(--primary-color);
  outline-color: var(--primary-color);
}

:deep(.cropper-face) {
  background-color: inherit;
}

:deep(.cropper-view-box) {
  box-shadow: 0 0 0 1px var(--primary-color);
  outline: 0;
}
</style> 