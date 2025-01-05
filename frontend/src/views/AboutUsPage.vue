<template>
  <div class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
      <div class="about-hero-pattern"></div>
      <div class="about-hero-content">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Creating Unforgettable Moments</h1>
        <p class="text-xl md:text-2xl opacity-90">Your premier partner in event planning and management</p>
      </div>
    </section>

    <!-- Our Story Section -->
    <section class="about-story">
      <div class="story-container">
        <div class="story-image">
          <img src="@/assets/images/about/story-image.jpg" alt="Razzrel Events Story">
        </div>
        <div class="story-content">
          <h2>Our Story</h2>
          <p>Founded with a passion for creating extraordinary experiences, Razzrel Events has been transforming dreams into reality since 2020. We believe that every event tells a unique story, and we're here to help you tell yours.</p>
          <p>Our team of dedicated professionals brings creativity, attention to detail, and years of expertise to every project, ensuring your event is nothing short of spectacular.</p>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="bg-gray-50 dark:bg-gray-900 py-24">
      <div class="container mx-auto px-6">
        <div class="section-title-wrapper">
          <h2 class="section-title">Our Core Values</h2>
        </div>
        <div class="values-grid">
          <div v-for="(value, index) in values" :key="index" class="value-card">
            <div class="value-icon">
              <i :class="value.icon"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ value.title }}</h3>
            <p class="text-gray-600 dark:text-gray-300">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="py-24">
      <div class="container mx-auto px-6">
        <div class="section-title-wrapper">
          <h2 class="section-title">Meet Our Team</h2>
        </div>
        <div class="team-grid">
          <div v-for="(member, index) in team" :key="index" class="team-card">
            <div class="team-image">
              <img :src="member.image" :alt="member.name" class="w-full h-full object-cover">
              <div class="team-info">
                <h3 class="text-xl font-semibold mb-1">{{ member.name }}</h3>
                <p class="text-sm opacity-90">{{ member.position }}</p>
              </div>
              <div class="team-social">
                <a v-for="social in member.socials" 
                    :key="social.platform" 
                    :href="social.link" 
                    class="team-social-icon">
                  <i :class="social.icon"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-container">
      <div class="container mx-auto px-6">
        <div class="stats-grid">
          <div v-for="(stat, index) in stats" :key="index" class="stat-item">
            <div class="stat-number">{{ stat.value }}+</div>
            <p class="text-lg mt-2 opacity-90">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-container">
      <div class="mission-bg"></div>
      <div class="mission-content">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Our Mission</h2>
        <p class="mission-quote">
          "To create extraordinary experiences that exceed expectations and leave lasting impressions, one event at a time."
        </p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import jeradImage from '@/assets/images/team/jerad.jpg';
import jerwinImage from '@/assets/images/team/jerwin.jpg';
import raphunzellImage from '@/assets/images/team/raphunzell.jpg';

const values = ref([
  {
    icon: 'fas fa-star',
    title: 'Excellence',
    description: 'We strive for perfection in every detail, ensuring each event exceeds expectations.'
  },
  {
    icon: 'fas fa-heart',
    title: 'Passion',
    description: 'Our love for what we do drives us to create unique and memorable experiences.'
  },
  {
    icon: 'fas fa-handshake',
    title: 'Integrity',
    description: 'We build trust through transparency, honesty, and reliability in all our dealings.'
  }
]);

const team = ref([
  {
    name: 'Jerad',
    position: 'Creative Director',
    image: jeradImage,
    socials: [
      { platform: 'linkedin', icon: 'fab fa-linkedin', link: '#' },
      { platform: 'twitter', icon: 'fab fa-twitter', link: '#' }
    ]
  },
  {
    name: 'Raphunzell',
    position: 'Event Manager',
    image: raphunzellImage,
    socials: [
      { platform: 'linkedin', icon: 'fab fa-linkedin', link: '#' },
      { platform: 'instagram', icon: 'fab fa-instagram', link: '#' }
    ]
  },
  {
    name: 'Jerwin',
    position: 'Design Specialist',
    image: jerwinImage,
    socials: [
      { platform: 'linkedin', icon: 'fab fa-linkedin', link: '#' },
      { platform: 'behance', icon: 'fab fa-behance', link: '#' }
    ]
  }
]);

const stats = ref([
  { value: '0', label: 'Events Completed' },
  { value: '0', label: 'Team Members' },
  { value: '0', label: 'Partner Venues' },
  { value: '0', label: 'Happy Clients' }
]);

onMounted(async () => {
  try {
    const response = await axios.get(`${import.meta.env.VITE_API_URL}/about-us/stats`);
    if (response.data.status === 200) {
      const data = response.data.data;
      stats.value = [
        { value: data.events_completed.toString(), label: 'Events Completed' },
        { value: data.team_members.toString(), label: 'Team Members' },
        { value: data.partner_venues.toString(), label: 'Partner Venues' },
        { value: data.happy_clients.toString(), label: 'Happy Clients' }
      ];
    }
  } catch (error) {
    console.error('Error fetching statistics:', error);
  }
});
</script>

<style scoped>
.about-hero {
  position: relative;
  min-height: 50vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--primary-color);
  overflow: hidden;
  padding: 4rem 1.5rem;
  margin-top: 100px;
}

.about-hero-pattern {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.1);
  background-image: 
    linear-gradient(45deg, transparent 45%, rgba(255, 255, 255, 0.1) 45%, rgba(255, 255, 255, 0.1) 55%, transparent 55%);
  background-size: 20px 20px;
  opacity: 0.1;
  animation: float 30s linear infinite;
}

.about-hero-content {
  position: relative;
  z-index: 10;
  text-align: center;
  max-width: 800px;
  padding: 0 1rem;
}

.about-hero-content h1 {
  font-family: var(--font-family);
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1.2;
  margin-bottom: 1rem;
  font-weight: 700;
  color: white;
  letter-spacing: -0.02em;
}

.about-hero-content p {
  font-family: var(--font-family);
  font-size: clamp(1rem, 1.5vw, 1.25rem);
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 400;
  max-width: 600px;
  margin: 0 auto;
}

.about-story {
  padding: 4rem 0;
  background: var(--card-background);
}

.story-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.story-image {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-lg);
  aspect-ratio: 4/3;
}

.story-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform 0.3s ease;
}

.story-content {
  padding: 2rem 0;
}

.story-content h2 {
  font-size: 2.5rem;
  font-weight: 600;
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.story-content p {
  font-size: 1.1rem;
  line-height: 1.7;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.section-title-wrapper {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-family: var(--font-family);
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--text-color);
  position: relative;
  display: inline-block;
  padding-bottom: 1rem;
  letter-spacing: -0.02em;
}

.section-title::after {
  content: '';
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 3rem;
  height: 0.25rem;
  background: var(--primary-color);
  border-radius: 4px;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  max-width: 1100px;
  margin: 5rem auto;
  padding: 0 2rem;
}

.value-card {
  padding: 2.5rem 2rem;
  background: var(--card-background);
  border-radius: 1rem;
  text-align: center;
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
}

.value-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.value-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1.5rem;
  background: var(--primary-color);
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.team-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2.5rem;
  max-width: 1100px;
  margin: 5rem auto;
  padding: 0 2rem;
}

.team-card {
  background: var(--card-background);
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: transform 0.3s ease;
}

.team-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.team-image {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
}

.team-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.team-card:hover .team-image img {
  transform: scale(1.05);
}

.team-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1.5rem;
  background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
  color: white;
}

.team-social {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.5rem;
}

.team-social-icon {
  width: 35px;
  height: 35px;
  background: var(--card-background);
  color: var(--text-color);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.team-social-icon:hover {
  background: var(--primary-color);
  color: white;
}

.stats-container {
  padding: 5rem 0;
  background: var(--primary-color);
  margin: 5rem 0;
  position: relative;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
  text-align: center;
  color: white;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.stat-item {
  padding: 2rem;
}

.stat-number {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  line-height: 1;
}

.mission-container {
  position: relative;
  padding: 6rem 0;
  background: var(--card-background);
  overflow: hidden;
}

.mission-content {
  position: relative;
  z-index: 1;
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
  padding: 0 2rem;
}

.mission-quote {
  font-size: 1.5rem;
  line-height: 1.6;
  color: var(--text-color);
  font-style: italic;
}

@media (max-width: 1024px) {
  .values-grid,
  .team-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .story-container {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .values-grid,
  .team-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

@keyframes float {
  from {
    background-position: 0 0;
  }
  to {
    background-position: 40px 40px;
  }
}
</style>
