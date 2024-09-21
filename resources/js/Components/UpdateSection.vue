<template>
  <!-- About Section Template -->
  <div class="about-container">
    <div class="about-header">Data Updates</div>
    <div class="about-section">
      <h2>How This Site Works</h2>
      <p>This site provides an advanced search tool for analyzing grant opportunities from the Grants.gov database. By utilizing vector search technology, we offer fast and accurate search results, helping users find relevant grants based on complex queries.</p>

      <h2>Data Source and Updates</h2>
      <p>
        Our data comes directly from the Grants.gov database, which exports an updated XML file daily. This XML file contains all active grant information and serves as the foundation for our search functionality. 
        <br /><br />
        The most recent update was on: 
        <LastUpdateText />
      </p>

      <h2>Why Vector Search?</h2>
      <p>
        Unlike traditional keyword searches, vector search uses machine learning models to find connections and context between words. This enables users to search for grants in a more intuitive and meaningful way, improving the chances of finding the right opportunities.
      </p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import LastUpdateText from '@/Components/LastUpdateText.vue';

export default {
  name: 'AboutSection',
  components: {
    LastUpdateText,
  },
};
</script>
