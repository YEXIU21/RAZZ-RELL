<template>
  <div class="booking-page">
    <div class="booking-container">
      <!-- Step Progress Indicator -->
      <div class="step-progress">
        <div
          v-for="(label, index) in stepLabels"
          :key="index"
          class="step"
          :class="{
            active: currentStep >= index + 1,
            complete: currentStep > index + 1,
          }"
        >
          <div class="step-icon">
            <template v-if="currentStep > index + 1">
              <i class="fas fa-check"></i>
            </template>
            <template v-else>
              <span>{{ index + 1 }}</span>
            </template>
          </div>
          <div class="step-label">{{ label }}</div>
          <div class="step-line" v-if="index < stepLabels.length - 1"></div>
        </div>
      </div>

      <!-- Dynamic Content Based on Step -->
      <div class="step-content">
        <!-- Step 1: Package Selection -->
        <div v-if="currentStep === 1" class="fade-in">
          <div class="step-header">
            <h2>Select Your Package</h2>
            <p>Choose the perfect package for your event</p>
          </div>

          <div v-if="selectedPackage" class="package-card">
            <div class="package-image">
              <img
                :src="getPackageImageUrl"
                :alt="selectedPackage.name"
              />
              <div class="package-badge">Selected Package</div>
            </div>
            <div class="package-details">
              <h3>{{ selectedPackage.name }}</h3>
              <div class="package-price">
                ₱{{ formatPrice(selectedPackage.price) }}
              </div>
              <p class="package-description">
                {{ selectedPackage.description }}
              </p>
              <div class="package-features">
                <div class="feature">
                  <i class="fas fa-users"></i>
                  <span>Up to {{ formData.packs }} Guests</span>
                </div>
                <div class="feature">
                  <i class="fas fa-clock"></i>
                  <span>Full Day Event</span>
                </div>
                <div class="feature">
                  <i class="fas fa-star"></i>
                  <span>Premium Service</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Personal Information -->
        <div v-if="currentStep === 2" class="fade-in">
          <div class="step-header">
            <h2>Personal Information</h2>
            <p>Tell us about yourself</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Full Name*</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  v-model="formData.fullName"
                  required
                  placeholder="Enter your full name"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Email Address*</label>
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input
                  type="email"
                  v-model="formData.email"
                  required
                  placeholder="Enter your email"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Phone Number*</label>
              <div class="input-group">
                <i class="fas fa-phone"></i>
                <input
                  type="tel"
                  v-model="formData.phone"
                  required
                  placeholder="Enter your phone number"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Event Details -->
        <div v-if="currentStep === 3" class="fade-in">
          <div class="step-header">
            <h2>Event Details</h2>
            <p>Tell us about your event</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Event Start Date*</label>
              <div class="input-group">
                <label for="event_date">
                  <i class="fas fa-calendar"></i>
                </label>
                <input
                  id="event_date"
                  type="date"
                  v-model="formData.event_date"
                  required
                  :min="minDate"
                  placeholder="MM/DD/YYYY"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Event Time*</label>
              <div class="input-group">
                <label for="event_time">
                  <i class="fas fa-clock"></i>
                </label>
                <input
                  id="event_time"
                  type="time"
                  v-model="formData.event_time"
                  required
                  placeholder="HH:MM"
                />
              </div>
            </div>

            <div class="form-group">
              <label>Event Duration (Days)*</label>
              <div class="input-group">
                <i class="fas fa-calendar-day"></i>
                <select
                  v-model="formData.event_duration"
                  required
                  class="form-select"
                  @change="updateTotalPrice"
                >
                  <option value="" disabled>Select number of days</option>
                  <option v-for="n in 7" :key="n" :value="n">
                    {{ n }} {{ n === 1 ? "day" : "days" }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Number of Guests/Packs*</label>
              <div class="input-group" :class="{ error: isPacksError }">
                <i class="fas fa-users"></i>
                <div class="packs-input-wrapper">
                  <input
                    type="text"
                    v-model="formData.packs"
                    required
                    class="packs-input"
                    @input="validatePacks"
                    @keypress="allowOnlyNumbers"
                    @blur="formatPacksOnBlur"
                    :placeholder="selectedPackage?.packs + ' pax'"
                  />
                  <span class="packs-suffix">pax</span>
                </div>
              </div>
              <small class="text-muted" v-if="selectedPackage">
                Minimum {{ selectedPackage.packs }} pax required for this
                package
              </small>
            </div>

            <div class="form-group span-2">
              <label>Venue Address*</label>
              <div class="input-group">
                <i class="fas fa-map-marker-alt"></i>
                <input
                  type="text"
                  v-model="formData.venue_name"
                  required
                  placeholder="Enter complete venue address"
                />
              </div>
            </div>

            <div class="form-group span-2">
              <label
                >Special Requests
                <span class="optional">(Optional)</span></label
              >
              <div class="input-group">
                <i class="fas fa-comment-alt"></i>
                <textarea
                  v-model="formData.special_requests"
                  placeholder="Any special requirements or requests?"
                  rows="4"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Payment -->
        <div v-if="currentStep === 4" class="fade-in">
          <div class="step-header">
            <h2>Payment Details</h2>
            <p>Choose your payment method</p>
          </div>

          <div class="payment-summary-card">
            <h3>Booking Summary</h3>
            <div class="summary-details">
              <div class="summary-row">
                <span
                  >Package Price (Standard
                  {{ selectedPackage?.packs }} pax)</span
                >
                <span
                  >₱{{
                    formatPrice(selectedPackage ? selectedPackage.price : 0)
                  }}</span
                >
              </div>

              <!-- Show pack adjustment if different from standard -->
              <div
                class="summary-row pack-adjustment"
                v-if="
                  packPriceAdjustment !== 0 ||
                  formData.packs !== selectedPackage?.packs
                "
              >
                <div class="adjustment-details">
                  <span>Pack Adjustment ({{ packAdjustmentText }})</span>
                  <div class="calculation-details" v-if="selectedPackage">
                    <div class="formula">
                      Base package price: ₱{{
                        formatPrice(selectedPackage.price)
                      }}
                      <br />
                      Additional Guest Price:
                      {{ selectedPackage.additional_price_percentage || 10 }}%
                      per person
                      <br />
                      Additional guests:
                      {{ formData.packs - selectedPackage.packs }}
                      <br />
                      <template
                        v-if="
                          selectedPackage.additional_price_percentage > 0 &&
                          formData.packs > selectedPackage.packs
                        "
                      >
                        Price per additional guest: ₱{{
                          formatPrice(
                            selectedPackage.price *
                              ((selectedPackage.additional_price_percentage ||
                                10) /
                                100)
                          )
                        }}
                        <br />
                        Total additional charge: ₱{{
                          formatPrice(packPriceAdjustment)
                        }}
                      </template>
                    </div>
                  </div>
                </div>
                <span
                  :class="{
                    increase: packPriceAdjustment > 0,
                    decrease: packPriceAdjustment < 0,
                  }"
                >
                  {{ packPriceAdjustment > 0 ? "+" : "" }}₱{{
                    formatPrice(packPriceAdjustment)
                  }}
                </span>
              </div>

              <div class="summary-row subtotal">
                <span>Package Subtotal</span>
                <span>₱{{ formatPrice(packageSubtotal) }}</span>
              </div>

              <!-- Show duration multiplier if more than 1 day -->
              <div
                class="summary-row duration-charge"
                v-if="formData.event_duration > 1"
              >
                <span
                  >Additional Days ({{
                    formData.event_duration - 1
                  }}
                  extra)</span
                >
                <span class="increase"
                  >+₱{{ formatPrice(additionalDaysCharge) }}</span
                >
              </div>

              <div class="divider"></div>

              <div class="summary-row total">
                <span>Total Payment</span>
                <span>₱{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>
          </div>

          <div class="payment-methods">
            <label
              class="payment-method-card"
              :class="{ active: formData.payment_method === 'cash' }"
            >
              <input
                type="radio"
                v-model="formData.payment_method"
                value="cash"
                required
              />
              <div class="method-content">
                <i class="fas fa-money-bill-wave"></i>
                <div class="method-details">
                  <h4>Cash Payment</h4>
                  <p>Pay in cash on meet-up</p>
                </div>
              </div>
            </label>
          </div>

          <!-- Terms and Conditions -->
          <div class="terms-section">
            <label class="terms-checkbox">
              <input
                type="checkbox"
                v-model="formData.termsAccepted"
                required
              />
              <span
                >I agree to the
                <a href="#" @click.prevent="showTerms"
                  >Terms and Conditions</a
                ></span
              >
            </label>
          </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="form-navigation">
        <button
          v-if="currentStep > 1"
          type="button"
          class="nav-btn back"
          @click="currentStep--"
        >
          <i class="fas fa-arrow-left"></i>
          Back
        </button>

        <button
          v-if="currentStep < 4"
          type="button"
          class="nav-btn next"
          @click="handleNextStep"
          :disabled="!canProceedToNextStep"
        >
          Next
          <i class="fas fa-arrow-right"></i>
        </button>

        <button
          v-if="currentStep === 4"
          type="button"
          class="nav-btn submit"
          :disabled="isSubmitting || !formData.termsAccepted"
          @click="handleSubmit"
        >
          <span v-if="isSubmitting">
            <i class="fas fa-spinner fa-spin"></i>
            Processing...
          </span>
          <span v-else>
            <i class="fas fa-check"></i>
            Confirm Booking
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.booking-page {
  min-height: 100vh;
  background: var(--background-color);
  padding: 8rem 1rem 2rem;
  position: relative;
}

.booking-container {
  max-width: 800px;
  margin: 0 auto;
  background: var(--card-background);
  border-radius: 20px;
  box-shadow: var(--shadow-lg);
  padding: 2rem;
  position: relative;
  z-index: 1;
  border: 1px solid var(--border-color);
}

.step-progress {
  display: flex;
  justify-content: space-between;
  margin-bottom: 3rem;
  position: relative;
  padding: 0 1rem;
}

.step {
  flex: 1;
  text-align: center;
  position: relative;
}

.step-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--card-background);
  border: 2px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-weight: 600;
  position: relative;
  z-index: 2;
  transition: all 0.3s ease;
  color: var(--text-muted);
}

.step.active .step-icon {
  border-color: var(--primary-color);
  background: var(--primary-color);
  color: white;
}

.step.complete .step-icon {
  background: var(--primary-color);
  border-color: var(--primary-color);
  color: white;
}

.step-label {
  margin-top: 0.75rem;
  font-size: 0.9rem;
  color: var(--text-muted);
  font-weight: 500;
}

.step.active .step-label {
  color: var(--primary-color);
  font-weight: 600;
}

.step-line {
  position: absolute;
  top: 20px;
  left: 50%;
  right: -50%;
  height: 2px;
  background: var(--border-color);
  z-index: 1;
}

.step.active .step-line,
.step.complete .step-line {
  background: var(--primary-color);
}

.step-content {
  animation: fadeIn 0.3s ease;
}

.step-header {
  text-align: center;
  margin-bottom: 2rem;
}

.step-header h2 {
  font-size: 2rem;
  color: var(--text-color);
  margin: 0;
}

.step-header p {
  color: var(--text-muted);
  margin: 0.5rem 0 0;
}

.package-card {
  background: var(--card-background);
  border-radius: 15px;
  box-shadow: var(--shadow-md);
  overflow: hidden;
  display: flex;
  margin-bottom: 2rem;
  border: 1px solid var(--border-color);
}

.package-image {
  width: 40%;
  position: relative;
}

.package-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.package-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background: var(--primary-color);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.package-details {
  flex: 1;
  padding: 2rem;
}

.package-details h3 {
  font-size: 1.8rem;
  margin: 0 0 1rem;
  color: var(--text-color);
}

.package-price {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 1rem;
}

.package-description {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.package-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-color);
}

.feature i {
  color: var(--primary-color);
  font-size: 1.2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: var(--text-color);
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.input-group:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-light);
}

.input-group i {
  padding: 0.75rem;
  color: var(--text-muted);
  font-size: 1rem;
  cursor: pointer;
  z-index: 1;
}

.form-control {
  flex: 1;
  padding: 0.75rem;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: var(--text-color);
  outline: none;
}

.form-control::placeholder {
  color: var(--text-muted);
}

/* Error state */
.input-group.error {
  border-color: var(--error-color);
}

.input-group.error input::placeholder {
  color: var(--error-color);
}

/* Success state */
.input-group.success {
  border-color: var(--success-color);
}

/* Form grid layout */
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-group.span-2 {
  grid-column: span 2;
}

.payment-summary-card {
  background: var(--hover-background);
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  border: 1px solid var(--border-color);
}

.payment-summary-card h3 {
  margin: 0 0 1rem;
  color: var(--text-color);
}

.summary-details {
  background: var(--card-background);
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  color: var(--text-color);
  font-size: 0.95rem;
}

.pack-adjustment,
.duration-charge {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.subtotal {
  color: var(--text-color);
  font-weight: 500;
}

.divider {
  height: 1px;
  background-color: var(--border-color);
  margin: 0.5rem 0;
}

.summary-row.total {
  font-weight: 600;
  font-size: 1.1rem;
  color: var(--text-color);
  padding-top: 0.5rem;
}

.terms-section {
  margin-top: 2rem;
  text-align: center;
}

.terms-checkbox {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.terms-checkbox input {
  width: 18px;
  height: 18px;
}

.terms-checkbox span {
  color: var(--text-color);
}

.terms-checkbox a {
  color: var(--primary-color);
  text-decoration: none;
}

.form-navigation {
  display: flex;
  justify-content: space-between;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid var(--border-color);
}

.nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s;
}

.nav-btn.back {
  background: var(--hover-background);
  color: var(--text-color);
  border: 1px solid var(--border-color);
}

.nav-btn.next,
.nav-btn.submit {
  background: var(--primary-color);
  color: white;
}

.nav-btn:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.nav-btn:disabled {
  background: var(--border-color);
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
  opacity: 0.7;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .booking-container {
    padding: 1.5rem;
  }

  .package-card {
    flex-direction: column;
  }

  .package-image {
    width: 100%;
    height: 200px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.span-2 {
    grid-column: auto;
  }

  .qr-section {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .step-label {
    font-size: 0.8rem;
  }
}

.input-group select.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.25rem;
  padding-right: 2rem;
}

.input-group i.fa-calendar-day {
  color: var(--text-muted);
}

/* Add these new styles */
.packs-input-wrapper {
  position: relative;
  flex: 1;
  display: flex;
  align-items: center;
}

.packs-input {
  width: calc(100% - 45px);
  padding: 0.75rem;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: var(--text-color);
  outline: none;
  appearance: textfield;
  -moz-appearance: textfield;
}

.packs-input::-webkit-outer-spin-button,
.packs-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

.packs-suffix {
  position: absolute;
  right: 12px;
  color: var(--text-muted);
  user-select: none;
  pointer-events: none;
}

.input-group.error {
  border-color: var(--error-color);
}

.input-group.error input {
  color: var(--error-color);
}

.input-group.error .packs-suffix {
  color: var(--error-color);
}

.input-group.error i {
  color: var(--error-color);
}

/* Add these new styles */
.summary-details .calculation {
  background: var(--hover-background);
  border-radius: 8px;
  padding: 1rem;
  margin: 1rem 0;
  border: 1px solid var(--border-color);
}

.calculation-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.calculation-details span {
  color: var(--text-color);
  font-weight: 500;
}

.calculation-details .formula {
  color: var(--text-muted);
  font-family: monospace;
  font-size: 0.95rem;
}

.summary-row.total {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid var(--border-color);
}

@media (max-width: 768px) {
  .calculation-details {
    font-size: 0.9rem;
  }

  .calculation-details .formula {
    word-break: break-word;
  }
}

/* Add these new styles */
.increase {
  color: var(--success-color);
  font-weight: 500;
}

.decrease {
  color: var(--error-color);
  font-weight: 500;
}

/* Update existing styles */
.pack-adjustment,
.duration-charge {
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* Remove the old .negative class if it exists */
.adjustment-details {
  flex: 1;
}

.calculation-details {
  margin-top: 0.5rem;
  padding: 0.75rem;
  background: var(--hover-background);
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.formula {
  font-family: monospace;
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.6;
}

/* Update existing styles */
.pack-adjustment {
  flex-direction: column;
  gap: 0.5rem;
}

.pack-adjustment > span {
  align-self: flex-end;
}

@media (max-width: 768px) {
  .formula {
    font-size: 0.8rem;
    word-break: break-word;
  }

  .calculation-details {
    padding: 0.5rem;
  }
}

/* Form inputs */
input,
select,
textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 1rem;
  background: var(--card-background);
  color: var(--text-color);
  outline: none;
}

input:focus,
select:focus,
textarea:focus {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-light);
}

/* Input group styles */
.input-group {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.input-group:focus-within {
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px var(--primary-light);
}

.input-group i {
  padding: 0.75rem;
  color: var(--text-muted);
  font-size: 1rem;
  cursor: pointer;
  z-index: 1;
}

.form-control {
  flex: 1;
  padding: 0.75rem;
  border: none;
  background: transparent;
  font-size: 1rem;
  color: var(--text-color);
  outline: none;
}

.form-control::placeholder {
  color: var(--text-muted);
}

/* Calendar input specific styles */
input[type="date"],
input[type="time"] {
  color-scheme: var(--color-scheme);
  background: var(--card-background);
  color: var(--text-color);
}

input[type="date"]::-webkit-calendar-picker-indicator,
input[type="time"]::-webkit-calendar-picker-indicator {
  filter: var(--calendar-icon-filter);
  cursor: pointer;
}

/* Special styling for select elements */
select {
  appearance: none;
  background-image: var(--select-arrow);
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 2.5rem;
  cursor: pointer;
}

/* Textarea specific styles */
textarea {
  min-height: 100px;
  resize: vertical;
  background: var(--card-background);
  color: var(--text-color);
}

/* Dark mode overrides */
:root[data-theme="dark"] {
  --calendar-icon-filter: invert(0.8);
  --select-arrow: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23ffffff' viewBox='0 0 16 16'%3E%3Cpath d='M8 11.5l-6-6h12z'/%3E%3C/svg%3E");
  --color-scheme: dark;

  .input-group,
  input,
  select,
  textarea {
    background: var(--input-background);
    color: var(--text-color);
  }

  .form-control {
    color: var(--text-color);
    background: transparent;
  }

  .form-control::placeholder {
    color: var(--text-muted);
  }

  .input-group i {
    color: var(--text-muted);
  }

  input::placeholder,
  textarea::placeholder {
    color: var(--text-muted);
  }

  select {
    background-color: var(--input-background);
  }

  /* Ensure dark mode for calendar and time inputs */
  input[type="date"],
  input[type="time"] {
    background: var(--input-background);
  }
}

/* Placeholder text color */
::placeholder {
  color: var(--text-muted);
  opacity: 1;
}

:-ms-input-placeholder {
  color: var(--text-muted);
}

::-ms-input-placeholder {
  color: var(--text-muted);
}

.payment-methods {
  margin: 2rem 0;
}

.payment-method-card {
  display: block;
  background: var(--card-background);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.payment-method-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.payment-method-card.active {
  border-color: var(--primary-color);
  background: var(--primary-light);
}

.payment-method-card input[type="radio"] {
  display: none;
}

.method-content {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.method-content i {
  font-size: 1.5rem;
  color: var(--primary-color);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--hover-background);
  border-radius: 50%;
}

.method-details {
  flex: 1;
}

.method-details h4 {
  margin: 0 0 0.25rem;
  font-size: 1.1rem;
  color: var(--text-color);
}

.method-details p {
  margin: 0;
  color: var(--text-muted);
  font-size: 0.9rem;
}

/* Dark mode overrides */
:root[data-theme="dark"] .payment-method-card.active {
  background: var(--primary-dark);
}

:root[data-theme="dark"] .method-content i {
  background: var(--card-background);
}

@media (max-width: 768px) {
  .payment-method-card {
    padding: 1.25rem;
  }
  
  .method-content {
    gap: 1rem;
  }
  
  .method-content i {
    font-size: 1.25rem;
    width: 35px;
    height: 35px;
  }
  
  .method-details h4 {
    font-size: 1rem;
  }
  
  .method-details p {
    font-size: 0.85rem;
  }
}
</style>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();

// Form Data
const formData = ref({
  fullName: "",
  email: "",
  phone: "",
  event_date: "",
  event_time: "",
  venue_name: "",
  packs: null,
  special_requests: "",
  payment_method: "",
  receiptImage: null,
  termsAccepted: false,
  user_id: "",
  package_id: "",
  event_duration: 1,
});

// Selected Package
const selectedPackage = ref(null);

// UI States
const isSubmitting = ref(false);
const uploadError = ref("");
const receiptPreview = ref("");
const fileInput = ref(null);

// GCash Details
const gcashQRCode = "/images/gcash-qr.png";
const gcashNumber = "0963-646-6496";
const gcashName = "Mark Jayson Cayude";

// Step Labels
const stepLabels = ["Package", "Personal Info", "Event Details", "Payment"];

// Current Step
const currentStep = ref(1);

// Computed Properties
const minDate = computed(() => {
  const today = new Date();
  today.setDate(today.getDate() + 7); // Minimum 7 days from today
  return today.toISOString().split("T")[0];
});

const getPackageImageUrl = computed(() => {
  if (!selectedPackage.value?.image) {
    return '/src/assets/images/default-package.jpg'
  }
  return `${import.meta.env.VITE_STORAGE_URL}/api/storage/${selectedPackage.value.image}`
})

// Update the packPriceAdjustment computed property
const packPriceAdjustment = computed(() => {
  if (!selectedPackage.value || !formData.value.packs) return 0;

  const basePrice = Number(selectedPackage.value.price);
  const basePacks = Number(selectedPackage.value.packs);
  const currentPacks = Number(formData.value.packs);
  const additionalPricePercentage = Number(
    selectedPackage.value.additional_price_percentage
  );

  // If current packs is less than or equal to base packs, no adjustment needed
  if (currentPacks <= basePacks) return 0;

  // Calculate additional guests
  const additionalGuests = currentPacks - basePacks;

  // Calculate price per additional guest using the percentage from database
  const pricePerAdditionalGuest = basePrice * (additionalPricePercentage / 100);

  return pricePerAdditionalGuest * additionalGuests;
});

// Update the packAdjustmentText computed property
const packAdjustmentText = computed(() => {
  if (!selectedPackage.value || !formData.value.packs) return "";

  const basePacks = Number(selectedPackage.value.packs);
  const currentPacks = Number(formData.value.packs);
  const additionalPricePercentage = Number(
    selectedPackage.value.additional_price_percentage
  );

  if (currentPacks > basePacks) {
    const additionalGuests = currentPacks - basePacks;
    const pricePerAdditionalGuest =
      selectedPackage.value.price * (additionalPricePercentage / 100);
    return `${additionalGuests} additional pax (+₱${formatPrice(
      pricePerAdditionalGuest
    )} per additional guest)`;
  }
  return "";
});

const packageSubtotal = computed(() => {
  if (!selectedPackage.value) return 0;

  const basePrice = Number(selectedPackage.value.price) || 0;
  const adjustment = Number(packPriceAdjustment.value) || 0;

  return basePrice + adjustment;
});

const additionalDaysCharge = computed(() => {
  if (!selectedPackage.value || !formData.value.event_duration) return 0;

  const duration = Number(formData.value.event_duration) || 1;
  if (duration <= 1) return 0;

  const subtotal = Number(packageSubtotal.value) || 0;
  return subtotal * (duration - 1);
});

const totalPrice = computed(() => {
  const subtotal = Number(packageSubtotal.value) || 0;
  const daysCharge = Number(additionalDaysCharge.value) || 0;

  return subtotal + daysCharge;
});

// Update the formatPrice function to handle invalid numbers
const formatPrice = (price) => {
  // Convert to number and handle invalid values
  const numPrice = Number(price);
  if (isNaN(numPrice)) return "0.00";

  return numPrice.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

// Update canProceedToNextStep to include event duration validation
const canProceedToNextStep = computed(() => {
  switch (currentStep.value) {
    case 1:
      return selectedPackage.value !== null;
    case 2:
      return (
        formData.value.fullName &&
        formData.value.fullName.trim() !== "" &&
        formData.value.email &&
        formData.value.phone
      );
    case 3:
      return (
        formData.value.event_date &&
        formData.value.event_time &&
        formData.value.venue_name &&
        formData.value.packs &&
        formData.value.event_duration
      );
    default:
      return true;
  }
});

// Methods
const getBookingById = async () => {
  try {
    const response = await axios.get(
      `http://127.0.0.1:8000/api/get-package-by-id/${route.params.id}`
    );

    // Set the packs from the package
    formData.value.packs = response.data.packs;
    formData.value.package_id = response.data.id;

    selectedPackage.value = {
      id: response.data.id,
      name: response.data.package_name,
      price: response.data.package_price,
      description: response.data.package_description,
      packs: response.data.packs,
      additional_price_percentage: response.data.additional_price_percentage,
      image: response.data.package_image
        ? `${import.meta.env.VITE_STORAGE_URL}/api/storage/${
            response.data.package_image
          }`
        : null,
    };

    // Set user info if available
    const userInfo = JSON.parse(localStorage.getItem("user_info"));
    console.log("User Info from localStorage:", userInfo); // Debug log

    if (userInfo && userInfo.first_name && userInfo.last_name) {
      // Ensure proper string concatenation for full name
      const firstName = String(userInfo.first_name || "").trim();
      const lastName = String(userInfo.last_name || "").trim();
      formData.value.fullName = `${firstName} ${lastName}`;
      formData.value.email = String(userInfo.email || "").trim();
      formData.value.phone = String(userInfo.phone_number || "");
      formData.value.user_id = userInfo.id;

      console.log("Set form data:", {
        fullName: formData.value.fullName,
        email: formData.value.email,
        phone: formData.value.phone,
        user_id: formData.value.user_id,
      }); // Debug log
    } else {
      console.warn("User info incomplete or not found in localStorage");
    }
  } catch (error) {
    console.error("Error fetching package:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load package details. Please try again.",
      confirmButtonColor: "#d33",
    });
  }
};

const triggerFileInput = () => {
  fileInput.value.click();
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  handleFile(file);
};

const handleFileDrop = (event) => {
  event.preventDefault();
  const file = event.dataTransfer.files[0];
  handleFile(file);
};

const handleFile = (file) => {
  if (!file) return;

  if (!file.type.startsWith("image/")) {
    uploadError.value = "Please upload an image file";
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    // 5MB limit
    uploadError.value = "File size should be less than 5MB";
    return;
  }

  uploadError.value = "";
  formData.value.receiptImage = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    receiptPreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const removeReceipt = () => {
  formData.value.receiptImage = null;
  receiptPreview.value = "";
  uploadError.value = "";
};

const showTerms = () => {
  // Implement terms modal logic
};

const handleSubmit = async (event) => {
  try {
    // Pre-validate the full name
    if (
      !formData.value.fullName ||
      String(formData.value.fullName).trim() === ""
    ) {
      throw new Error("Full name is required");
    }

    // Validate all required fields
    const requiredFields = {
      "Full Name": formData.value.fullName,
      Email: formData.value.email,
      "Phone Number": formData.value.phone,
      "Event Date": formData.value.event_date,
      "Event Time": formData.value.event_time,
      Venue: formData.value.venue_name,
      Package: formData.value.package_id,
      "Payment Method": formData.value.payment_method,
      "Number of Packs": formData.value.packs,
      "Event Duration": formData.value.event_duration,
    };

    // Check for empty required fields
    const emptyFields = Object.entries(requiredFields)
      .filter(([field, value]) => {
        if (field === "Full Name") {
          return !value || String(value).trim() === "";
        }
        return !value || (typeof value === "string" && !value.trim());
      })
      .map(([key]) => key);

    if (emptyFields.length > 0) {
      throw new Error(
        `Please fill in all required fields: ${emptyFields.join(", ")}`
      );
    }

    isSubmitting.value = true;
    const submitFormData = new FormData();

    // Format and validate the full name with extra checks
    const fullName = String(formData.value.fullName).trim();
    console.log("Submitting full name:", fullName); // Debug log

    if (!fullName) {
      throw new Error("Full name cannot be empty");
    }

    // Add all form fields with proper naming and validation
    submitFormData.append("full_name", fullName);
    submitFormData.append("email", String(formData.value.email || "").trim());
    submitFormData.append("phone_number", String(formData.value.phone || ""));
    submitFormData.append("event_date", formData.value.event_date);
    submitFormData.append("event_time", formData.value.event_time);
    submitFormData.append(
      "venue_name",
      String(formData.value.venue_name || "").trim()
    );
    submitFormData.append("package_id", formData.value.package_id);
    submitFormData.append("payment_method", formData.value.payment_method);
    submitFormData.append(
      "special_requests",
      String(formData.value.special_requests || "").trim()
    );
    submitFormData.append("packs", formData.value.packs);
    submitFormData.append("total_price", totalPrice.value);
    submitFormData.append("status", "pending");
    submitFormData.append("event_duration", formData.value.event_duration);

    // Calculate and validate end date
    const startDate = new Date(formData.value.event_date);
    if (isNaN(startDate.getTime())) {
      throw new Error("Invalid event date");
    }

    const endDate = new Date(startDate);
    endDate.setDate(
      startDate.getDate() + (parseInt(formData.value.event_duration) - 1)
    );
    submitFormData.append(
      "event_end_date",
      endDate.toISOString().split("T")[0]
    );

    // Add receipt image if exists
    if (formData.value.receiptImage) {
      submitFormData.append("receipt", formData.value.receiptImage);
    }

    // Validate and add user ID
    const userInfo = JSON.parse(localStorage.getItem("user_info"));
    if (!userInfo || !userInfo.id) {
      throw new Error("User information not found. Please log in again.");
    }
    submitFormData.append("user_id", userInfo.id);

    // Log the final form data for debugging
    console.log("Submitting booking with data:");
    for (let pair of submitFormData.entries()) {
      console.log(`${pair[0]}: ${pair[1]}`);
    }

    const response = await axios.post(
      "http://127.0.0.1:8000/api/add-booking",
      submitFormData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
          Accept: "application/json",
        },
      }
    );

    console.log("Server response:", response.data); // Debug log

    if (response.data.status === "success" || response.status === 200) {
      await Swal.fire({
        icon: "success",
        title: "Success!",
        text: "Your booking has been submitted successfully.",
        confirmButtonColor: "#3085d6",
        confirmButtonText: "View My Bookings",
      });
      router.push("/bookings");
    } else {
      throw new Error(response.data.message || "Failed to create booking");
    }
  } catch (error) {
    console.error("Booking error:", error);
    let errorMessage =
      error.message || "Failed to create booking. Please try again.";

    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
      console.error("Server error response:", error.response.data);
    }

    await Swal.fire({
      icon: "error",
      title: "Error",
      text: errorMessage,
      confirmButtonColor: "#d33",
      confirmButtonText: "OK",
    });
  } finally {
    isSubmitting.value = false;
  }
};

// Update handleNextStep to include packs validation
const handleNextStep = () => {
  if (!canProceedToNextStep.value) {
    let missingFields = [];

    switch (currentStep.value) {
      case 1:
        if (!selectedPackage.value) missingFields.push("Package selection");
        break;
      case 2:
        if (!formData.value.fullName) missingFields.push("Full Name");
        if (!formData.value.email) missingFields.push("Email");
        if (!formData.value.phone) missingFields.push("Phone Number");
        break;
      case 3:
        if (!formData.value.event_date) missingFields.push("Event Date");
        if (!formData.value.event_time) missingFields.push("Event Time");
        if (!formData.value.venue_name) missingFields.push("Venue Address");
        if (!formData.value.packs) missingFields.push("Number of Guests");
        if (!formData.value.event_duration)
          missingFields.push("Event Duration");
        break;
    }

    Swal.fire({
      icon: "warning",
      title: "Required Fields",
      text: `Please fill in the following required fields: ${missingFields.join(
        ", "
      )}`,
      confirmButtonColor: "#3085d6",
    });
    return;
  }
  currentStep.value++;
};

const allowOnlyNumbers = (event) => {
  // Allow only numbers
  if (!/^\d*$/.test(event.key)) {
    event.preventDefault();
  }
};

// Add this ref for error state
const isPacksError = ref(false);

const validatePacks = (event) => {
  const value = event.target.value;

  // Allow empty input for now
  if (value === "") {
    formData.value.packs = "";
    isPacksError.value = false;
    return;
  }

  // Only process if it's a valid number
  const numValue = parseInt(value);
  if (!isNaN(numValue)) {
    const minPacks = selectedPackage.value
      ? parseInt(selectedPackage.value.packs)
      : 50;

    // If trying to set value below minPacks, don't update and show error
    if (numValue < minPacks) {
      isPacksError.value = true;
      return;
    }

    // If value is above max, cap it
    if (numValue > 500) {
      formData.value.packs = "500";
      isPacksError.value = false;
    } else {
      formData.value.packs = value;
      isPacksError.value = false;
    }
  }
};

const formatPacksOnBlur = () => {
  if (formData.value.packs === "") {
    const minPacks = selectedPackage.value
      ? parseInt(selectedPackage.value.packs)
      : 50;
    formData.value.packs = minPacks.toString();
    isPacksError.value = false;
    return;
  }

  let numValue = parseInt(formData.value.packs);
  const minPacks = selectedPackage.value
    ? parseInt(selectedPackage.value.packs)
    : 50;

  // If value is below minimum, reset to minimum
  if (numValue < minPacks) {
    formData.value.packs = minPacks.toString();
  } else if (numValue > 500) {
    formData.value.packs = "500";
  }

  isPacksError.value = false;
};

// Initialize component
onMounted(async () => {
  await getBookingById();
});
</script>
