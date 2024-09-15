<template>
  <div>
    <h1 class="text-center text-3xl font-bold my-12">Saved Grants</h1>

    <!-- Check if there are saved grants -->
    <div v-if="grants.length > 0">
      <h2 class="text-lg font-semibold p-4">Grants for {{ grants[0].email }}</h2>
      <div v-for="(grant, index) in grants" :key="grant.id" class="saved-grant">
        
        <!-- Root collapse toggle for the grant -->
        <div @click="toggleGrantCollapse(index)" class="root-toggle">
          <strong>Saved Grant #{{ grant.id }}</strong>
          <span class="toggle">[{{ collapsed[index] ? 'Show' : 'Hide' }}]</span>
        </div>

        <!-- Conditionally display the JsonTree component -->
        <div v-show="!collapsed[index]">
          <JsonTree :json="JSON.parse(grant.grant_info)" />
        </div>

        <button class="mt-2 text-red-500 hover:text-red-700" @click="deleteGrant(grant.id)">Delete</button>
      </div>
    </div>

    <!-- No grants found message -->
    <div v-else>
      <p class="text-center">No saved grants found.</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import JsonTree from '@/Components/JsonTree.vue'; // Import JsonTree component

export default {
  name: 'SavedGrants',
  components: { JsonTree }, // Register JsonTree
  data() {
    return {
      grants: [], // Store saved grants here
      collapsed: {} // Track collapse state for each grant
    };
  },
  methods: {
    // Fetch saved grants when the component is mounted
    async fetchGrants() {
      try {
        const response = await axios.get('/saved-grants');
        this.grants = response.data;
      } catch (error) {
        console.error('Failed to fetch saved grants:', error);
      }
    },
    // Delete a saved grant
    async deleteGrant(grantId) {
      try {
        await axios.delete(`/saved-grants/${grantId}`);
        this.grants = this.grants.filter(grant => grant.id !== grantId); // Remove grant from local array
      } catch (error) {
        console.error('Failed to delete grant:', error);
      }
    },
    // Toggle root collapse for a specific grant
    toggleGrantCollapse(index) {
      // Vue 3 allows direct manipulation of reactive objects
      this.collapsed[index] = !this.collapsed[index];
    }
  },
  mounted() {
    this.fetchGrants(); // Fetch grants when component is mounted
  }
};
</script>

<style scoped>
.saved-grant {
  border: 1px solid #e5e5e5;
  padding: 1rem;
  margin-bottom: 1rem;
  border-radius: 0.5rem;
  background-color: #f9f9f9;
}

button {
  cursor: pointer;
  border: none;
  background: none;
}

.root-toggle {
  cursor: pointer;
  font-size: 1.2rem;
  margin-bottom: 10px;
}

.toggle {
  color: blue;
  cursor: pointer;
}
</style>
