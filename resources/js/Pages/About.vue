<template>
    <AppLayout>
    <!-- About Section Template -->
    <AboutSection />
    </AppLayout>
  </template>
  
  <script>
import AboutSection from '@/Components/AboutSection.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

  export default {
    name: 'About',
    components: {AppLayout, AboutSection},
  };
  </script>
  