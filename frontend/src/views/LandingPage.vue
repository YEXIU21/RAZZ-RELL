<template>
  <div class="landing-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-content">
        <h1 class="hero-title">
          Create Unforgettables
          <span class="highlight">Events</span>
        </h1>
        <p class="hero-subtitle">
          Transform your special moments into extraordinary memories with our professional event planning services
        </p>
        <div class="hero-actions">
          <router-link to="/packages" class="cta-button">
            Book Now
            <i class="fas fa-arrow-right"></i>
          </router-link>
          <button class="video-button" @click="showVideo = true">
            <i class="fas fa-play"></i>
            Watch Video
          </button>
        </div>
      </div>
      <div class="hero-image">
        <HeroSlideshow :slides="heroSlides" />
      </div>
    </section>

    <!-- Services Section -->
    <section class="services">
      <h2 class="section-title">Our Services</h2>
      <div class="services-grid">
        <div
          v-for="service in services"
          :key="service.id"
          class="service-card"
          @mouseenter="activeService = service.id"
          @mouseleave="activeService = null"
          :class="{ active: activeService === service.id }"
        >
          <div class="service-icon">
            <i :class="service.icon"></i>
          </div>
          <h3>{{ service.title }}</h3>
          <p>{{ service.description }}</p>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features">
      <div class="features-content">
        <h2 class="section-title">Why Choose Us</h2>
        <div class="features-grid">
          <div
            v-for="feature in features"
            :key="feature.id"
            class="feature-item"
          >
            <div class="feature-icon">
              <i :class="feature.icon"></i>
            </div>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery">
      <h2 class="section-title">Our Portfolios</h2>
      <div class="gallery-grid">
        <div
          v-for="portfolio in portfolios.slice(0, 3)"
          :key="portfolio.id"
          class="gallery-item"
          @click="openGallery(portfolio.id)"
        >
          <img 
            :src="getImageUrl(portfolio.main_image_url)" 
            :alt="portfolio.title"
            @error="handleImageError"
          />
          <div class="gallery-overlay">
            <h4>{{ portfolio.title }}</h4>
            <p>{{ portfolio.description }}</p>
          </div>
        </div>
        <router-link to="/gallery" class="view-all-projects">
          View All Projects
          <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
      <div class="stats-grid">
        <div
          v-for="stat in stats"
          :key="stat.id"
          class="stat-item"
        >
          <div class="stat-value">
            <span class="counter">{{ stat.value }}</span>
            <span class="suffix">{{ stat.suffix }}</span>
          </div>
          <p class="stat-label">{{ stat.label }}</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
      <div class="cta-content">
        <h2>Ready to Create Your Dream Event?</h2>
        <p>Let's work together to make your vision a reality</p>
        <div class="cta-actions">
          <router-link to="/contact" class="contact-button">
            Contact Us
            <i class="fas fa-envelope"></i>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Video Modal -->
    <div v-if="showVideo" class="video-modal" @click="showVideo = false">
      <div class="video-container" @click.stop>
        <button class="close-video" @click="showVideo = false">
          <i class="fas fa-times"></i>
        </button>
        <iframe
          src="https://www.youtube.com/embed/VIDEO_ID"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
        ></iframe>
      </div>
    </div>

    <!-- Gallery Modal -->
    <div v-if="selectedImage" class="gallery-modal" @click="closeGallery">
      <div class="gallery-modal-content" @click.stop>
        <button class="close-gallery" @click="closeGallery">
          <i class="fas fa-times"></i>
        </button>
        <img :src="getImageUrl(selectedImage.main_image_url)" :alt="selectedImage.title" />
        <div class="gallery-modal-info">
          <h3>{{ selectedImage.title }}</h3>
          <p>{{ selectedImage.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useAuth } from '@/composables/useAuth';
import axios from 'axios';
import HeroSlideshow from '@/components/HeroSlideshow.vue';

const showVideo = ref(false);
const activeService = ref(null);
const currentTestimonial = ref(0);
const portfolios = ref([]);
const tm = ref(0);
const bookings = ref(0);
const selectedImage = ref(null);  
let testimonialInterval;
const currentSlide = ref(0);
let slideInterval;

const { isAdmin } = useAuth();

const services = [
  {
    id: 1,
    title: 'Weddings',
    description: 'Create your perfect wedding day with our comprehensive planning services.',
    icon: 'fas fa-heart',
  },
  {
    id: 2,
    title: 'Debuts',
    description: 'Make your debut celebration an unforgettable milestone with our expert planning.',
    icon: 'fas fa-star',
  },
  {
    id: 3,
    title: 'Christenings',
    description: 'Plan a beautiful and meaningful christening ceremony for your little one.',
    icon: 'fas fa-baby',
  },
  {
    id: 4,
    title: 'Party',
    description: 'Create magical and fun-filled celebrations for any occasion.',
    icon: 'fas fa-birthday-cake',
  }
];

const features = [
  {
    id: 1,
    title: 'Expert Planning',
    description: 'Our experienced team ensures every detail is perfect.',
    icon: 'fas fa-clipboard-check',
  },
  {
    id: 2,
    title: 'Custom Packages',
    description: 'Tailored solutions to match your vision and budget.',
    icon: 'fas fa-box-open',
  },
  {
    id: 3,
    title: '24/7 Support',
    description: 'Always available to assist you throughout the process.',
    icon: 'fas fa-headset',
  },
  {
    id: 4,
    title: 'Quality Service',
    description: 'Premium vendors and top-notch execution.',
    icon: 'fas fa-award',
  },
];

const testimonials = [
  {
    id: 1,
    text: 'Working with Razz Rell Events was the best decision we made for our wedding. Their attention to detail and professionalism made our day perfect!',
    name: 'Sarah Johnson',
    role: 'Bride',
    avatar: '@/assets/testimonials/sarah.jpg',
  },
  {
    id: 2,
    text: 'The team went above and beyond for our corporate event. The planning was seamless, and our clients were thoroughly impressed.',
    name: 'Jerad',
    role: 'Marketing Director',
    avatar: '@/assets/testimonials/jerad.jpg',
  },
  {
    id: 3,
    text: 'From concept to execution, they nailed every aspect of our themed party. Highly recommended for any special occasion!',
    name: 'Emily Rodriguez',
    role: 'Client',
    avatar: '@/assets/testimonials/emily.jpg',
  },
];

const stats = [
  {
    id: 1,
    value: bookings,
    suffix: '+',
    label: 'Events Completed',
  },
  {
    id: 2,
    value: '98',
    suffix: '%',
    label: 'Client Satisfaction',
  },
  {
    id: 3,
    value: '15',
    suffix: '+',
    label: 'Years Experience',
  },
  {
    id: 4,
    value: tm,
    suffix: '+',
    label: 'Team Members',
  },
];

const galleryImages = [];

const heroSlides = ref([]);

const loadHeroImages = async () => {
  const heroImageContext = import.meta.glob('@/assets/Heroimage/*.(png|jpg|jpeg)', { eager: true });
  
  heroSlides.value = Object.entries(heroImageContext).map(([path, module]) => {
    const fileName = path.split('/').pop();
    const title = fileName.split('.')[0]
      .replace(/([A-Z])/g, ' $1') // Add space before capital letters
      .replace(/-/g, ' ') // Replace hyphens with spaces
      .trim();
    
    return {
      image: module.default,
      title: title
    };
  });
};

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % heroSlides.value.length;
};

const prevSlide = () => {
  currentSlide.value = currentSlide.value === 0 
    ? heroSlides.value.length - 1 
    : currentSlide.value - 1;
};

const goToSlide = (index) => {
  currentSlide.value = index;
};

const nextTestimonial = () => {
  if (currentTestimonial.value < testimonials.length - 1) {
    currentTestimonial.value++;
  }
};

const prevTestimonial = () => {
  if (currentTestimonial.value > 0) {
    currentTestimonial.value--;
  }
};

const setTestimonial = (index) => {
  currentTestimonial.value = index;
};

const openGallery = (portfolioId) => {
  selectedImage.value = portfolios.value.find(p => p.id === portfolioId);
};

const closeGallery = () => {
  selectedImage.value = null;
};

const defaultImageUrl = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgdmlld0JveD0iMCAwIDIwMCAyMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIyMDAiIGZpbGw9IiNFNUU3RUIiLz48cGF0aCBkPSJNODAgOTBIMTIwVjExMEg4MFY5MFoiIGZpbGw9IiM5Q0EzQUYiLz48cGF0aCBkPSJNNjUgNjBIMTM1VjgwSDY1VjYwWiIgZmlsbD0iIzlDQTNBRiIvPjwvc3ZnPg==';

const getImageUrl = (imagePath) => {
  console.log('Raw image path:', imagePath);
  if (!imagePath) {
    console.log('No image path, using default');
    return defaultImageUrl;
  }
  if (imagePath.startsWith('http')) {
    console.log('Direct URL detected:', imagePath);
    return imagePath;
  }
  const fullUrl = `${import.meta.env.VITE_API_URL}/storage/${imagePath}`;
  console.log('Constructed URL:', fullUrl);
  return fullUrl;
};

const fetchportfolios = async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-portfolios`);
    portfolios.value = response.data;
    console.log('Fetched portfolios:', portfolios.value);
    if (portfolios.value.length > 0) {
      console.log('First portfolio:', portfolios.value[0]);
      console.log('First portfolio main image:', portfolios.value[0].main_image_url);
    }
  } catch (error) {
    console.error('Error fetching portfolios:', error);
  }
};

const fetchUsers = async () => {
  const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-users`);
  const userLength = response.data.filter(user => user.role === 'admin' || user.role === 'staff').map(user => user.id);
  tm.value = userLength.length;
};

const fetchBookings = async () => {
  const response = await axios.get(`${import.meta.env.VITE_API_URL}/api/get-all-bookings`);
  const bookingLength = response.data.filter(booking => booking.status === 'completed').map(booking => booking.id);
  bookings.value = bookingLength.length;
};

onMounted(async () => {
  await loadHeroImages();
  await fetchportfolios();
  await fetchUsers();
  await fetchBookings();

  // Clear any existing intervals first
  if (slideInterval) clearInterval(slideInterval);
  if (testimonialInterval) clearInterval(testimonialInterval);

  // Start slideshow with 3-second interval
  slideInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % heroSlides.value.length;
  }, 3000);

  // Start testimonial rotation
  testimonialInterval = setInterval(() => {
    if (currentTestimonial.value < testimonials.length - 1) {
      currentTestimonial.value++;
    } else {
      currentTestimonial.value = 0;
    }
  }, 5000);
});

onBeforeUnmount(() => {
  clearInterval(testimonialInterval);
  if (slideInterval) clearInterval(slideInterval);
});

const handleImageError = (event) => {
  console.error('Image failed to load:', event.target.src);
  event.target.src = defaultImageUrl;
};
</script>

<style scoped>
.landing-page {
  overflow-x: hidden;
}

/* Hero Section */
.hero {
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding: 2rem;
  background: linear-gradient(
    135deg,
    var(--background-color) 0%,
    var(--card-background) 100%
  );
}

.hero-content {
  flex: 1;
  max-width: 600px;
  padding: 2rem;
}

.hero-image {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 600px;
}

.slideshow-container {
  position: relative;
  width: 100%;
  height: 100%;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.5s ease-in-out;
}

.slide.active {
  opacity: 1;
}

.slide img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
}

.slideshow-navigation {
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  transform: translateY(-50%);
  display: flex;
  justify-content: space-between;
  padding: 0 20px;
  z-index: 2;
}

.slideshow-navigation, .slideshow-dots {
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.slideshow-container:hover .slideshow-navigation,
.slideshow-container:hover .slideshow-dots {
  opacity: 1;
  visibility: visible;
}

.nav-btn {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  border: none;
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.4);
  transform: scale(1.1);
}

.slideshow-dots {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 10px;
  z-index: 2;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.dot.active {
  background: white;
}

.hero-title {
  font-size: 4rem;
  line-height: 1.2;
  margin-bottom: 1.5rem;
  color: var(--text-color);
}

.highlight {
  color: var(--primary-color);
}

.hero-subtitle {
  font-size: 1.25rem;
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.hero-actions {
  display: flex;
  gap: 1rem;
}

.cta-button {
  padding: 1rem 2rem;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
}

.cta-button:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
}

.video-button {
  padding: 1rem 2rem;
  background: transparent;
  color: var(--text-color);
  border: 2px solid var(--border-color);
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
}

.video-button:hover {
  background: var(--card-background);
  transform: translateY(-2px);
}

/* Services Section */
.services {
  padding: 6rem 2rem;
  background: var(--card-background);
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 3rem;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
  max-width: 1500px;
  margin: 0 auto;
}

.service-card {
  padding: 2rem;
  background: var(--background-color);
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s;
}

.service-card:hover {
  transform: translateY(-10px);
  box-shadow: var(--shadow-lg);
}

.service-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  background: var(--primary-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.service-card h3 {
  font-size: 1.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.service-card p {
  color: var(--text-muted);
  margin-bottom: 1.5rem;
}

.learn-more {
  color: var(--primary-color);
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 500;
}

/* Features Section */
.features {
  padding: 6rem 2rem;
  background: var(--background-color);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.feature-item {
  text-align: center;
  padding: 2rem;
}

.feature-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 1rem;
  background: var(--primary-color);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.feature-item h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.feature-item p {
  color: var(--text-muted);
}

/* Gallery Section */
.gallery {
  padding: 4rem 2rem;
  background: var(--background-color);
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(280px, 1fr));
  gap: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
}

.gallery-item {
  position: relative;
  border-radius: 1rem;
  overflow: hidden;
  cursor: pointer;
  height: 450px;
  width: 100%;
  min-width: 280px;
  background: #fff;
  transition: transform 0.3s ease;
}

.gallery-item:last-child {
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
  font-weight: 500;
}

.gallery-item:last-child:hover {
  background: var(--primary-hover);
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.gallery-item:hover {
  transform: translateY(-5px);
}

.gallery-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.7));
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 2rem;
  opacity: 1;
  transition: opacity 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
  opacity: 0;
}

.gallery-overlay h4 {
  color: white;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
  word-break: break-word;
}

.gallery-overlay p {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  line-height: 1.5;
  word-break: break-word;
}

.view-all-projects {
  position: relative;
  height: 450px;
  background: #4070f4;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  text-decoration: none;
  font-size: 1.2rem;
  font-weight: 500;
  padding: 0 2rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(64, 112, 244, 0.2);
}

.view-all-projects:hover {
  background: #2952c8;
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(64, 112, 244, 0.3);
}

.view-all-projects i {
  margin-left: 0.75rem;
  font-size: 1.1rem;
  transition: transform 0.3s ease;
}

.view-all-projects:hover i {
  transform: translateX(5px);
}

/* Testimonials Section */
.testimonials {
  padding: 6rem 2rem;
  background: var(--background-color);
}

.testimonials-slider {
  position: relative;
  max-width: 800px;
  margin: 0 auto;
  overflow: hidden;
}

.testimonial-card {
  position: absolute;
  width: 100%;
  opacity: 0;
  transform: translateX(100%);
  transition: all 0.5s;
}

.testimonial-card.active {
  opacity: 1;
  transform: translateX(0);
}

.testimonial-content {
  background: var(--card-background);
  padding: 2rem;
  border-radius: 12px;
  box-shadow: var(--shadow-md);
}

.quote-icon {
  color: var(--primary-color);
  font-size: 2rem;
  margin-bottom: 1rem;
}

.testimonial-text {
  font-size: 1.1rem;
  color: var(--text-color);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.testimonial-author img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.author-info h4 {
  color: var(--text-color);
  margin-bottom: 0.25rem;
}

.author-info p {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.testimonial-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.control-btn {
  width: 40px;
  height: 40px;
  border: none;
  border-radius: 50%;
  background: var(--card-background);
  color: var(--text-color);
  cursor: pointer;
  transition: all 0.3s;
}

.control-btn:hover:not(:disabled) {
  background: var(--primary-color);
  color: white;
}

.control-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.testimonial-dots {
  display: flex;
  gap: 0.5rem;
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border-color);
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.dot.active {
  background: var(--primary-color);
  transform: scale(1.5);
}

/* Stats Section */
.stats {
  padding: 4rem 2rem;
  background: var(--primary-color);
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
}

.stat-value {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
}

.stat-label {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* CTA Section */
.cta {
  padding: 6rem 2rem;
  background: var(--card-background);
  text-align: center;
}

.cta-content {
  max-width: 800px;
  margin: 0 auto;
}

.cta-content h2 {
  font-size: 2.5rem;
  color: var(--text-color);
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.25rem;
  color: var(--text-muted);
  margin-bottom: 2rem;
}

.cta-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.contact-button {
  padding: 1rem 2rem;
  background: transparent;
  color: var(--text-color);
  border: 2px solid var(--border-color);
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
}

.contact-button:hover {
  background: var(--card-background);
  transform: translateY(-2px);
}

/* Video Modal */
.video-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.video-container {
  position: relative;
  width: 90%;
  max-width: 1000px;
  aspect-ratio: 16/9;
}

.close-video {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

.video-container iframe {
  width: 100%;
  height: 100%;
}

/* Gallery Modal */
.gallery-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.gallery-modal-content {
  position: relative;
  max-width: 90%;
  max-height: 90vh;
}

.close-gallery {
  position: absolute;
  top: -40px;
  right: 0;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

.gallery-modal-content img {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
}

.gallery-modal-info {
  background: var(--card-background);
  padding: 1rem;
  border-radius: 0 0 12px 12px;
}

.gallery-modal-info h3 {
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.gallery-modal-info p {
  color: var(--text-muted);
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero {
    flex-direction: column;
    text-align: center;
    padding: 4rem 1rem;
  }

  .hero-content {
    padding: 0;
    margin-bottom: 2rem;
  }

  .hero-image {
    width: 100%;
    height: 400px;
  }

  .slideshow-container {
    width: 100%;
    aspect-ratio: 16/10;
  }

  .services,
  .features,
  .gallery,
  .testimonials,
  .stats,
  .cta {
    padding: 4rem 1rem;
  }

  .section-title {
    font-size: 2rem;
  }

  .cta-actions {
    flex-direction: column;
  }

  .cta-button,
  .contact-button {
    width: 100%;
    justify-content: center;
  }

  .gallery {
    padding: 3rem 1rem;
  }
  
  .gallery-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .gallery-item {
    height: 250px;
  }
  
  .gallery-overlay {
    opacity: 1;
  }
}

@media (max-width: 1400px) {
  .gallery-grid {
    grid-template-columns: repeat(3, minmax(280px, 1fr));
  }
}

@media (max-width: 1024px) {
  .gallery-grid {
    grid-template-columns: repeat(2, minmax(280px, 1fr));
  }
  
  .gallery-item, .view-all-projects {
    height: 400px;
  }
}

@media (max-width: 768px) {
  .gallery {
    padding: 3rem 1rem;
  }
  
  .gallery-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .gallery-item, .view-all-projects {
    height: 350px;
  }
}
</style> 