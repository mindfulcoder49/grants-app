<template>
  <div>
    <h1 class="text-center text-3xl font-bold my-12">Saved Grants</h1>

    <AiAssistant :grants="selectedGrants" @remove-grant="removeSelectedGrant" />
    
    <div v-if="grants.length > 0">
      <h2 class="text-lg font-semibold p-4">Grants for {{ grants[0].email }}</h2>
      
      <GrantList 
        :grants="extractedGrantInfo" 
        :addedGrants="selectedGrants.map(g => g.id)"
        @add-to-ai-conversation="addSelectedGrant"
        @remove-from-ai-conversation="removeSelectedGrant"
      />
    </div>

    <div v-else>
      <p class="text-center">No saved grants found.</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import JsonTree from '@/Components/JsonTree.vue';
import AiAssistant from '@/Components/AiAssistant.vue';
import GrantList from '@/Components/GrantList.vue';

export default {
  name: 'SavedGrants',
  components: { JsonTree, AiAssistant, GrantList },
  data() {
    return {
      grants: [],
      selectedGrants: [], // Store selected grants here
      collapsed: {}
    };
  },
  computed: {
    // Computed property to extract all grant_info properties
    extractedGrantInfo() {
      return this.grants.map(grant => JSON.parse(grant.grant_info));
    }
  },
  methods: {
    async fetchGrants() {
      try {
        const response = await axios.get('/saved-grants');
        this.grants = response.data;
      } catch (error) {
        console.error('Failed to fetch saved grants:', error);
      }
    },
    async deleteGrant(grantId) {
      try {
        await axios.delete(`/saved-grants/${grantId}`);
        this.grants = this.grants.filter(grant => grant.id !== grantId);
        this.removeSelectedGrant(grantId); // Remove from selected grants if deleted
      } catch (error) {
        console.error('Failed to delete grant:', error);
      }
    },
    toggleGrantCollapse(index) {
      this.collapsed[index] = !this.collapsed[index];
    },
    addSelectedGrant(grant) {
      // Add grant to selected grants
      this.selectedGrants.push(grant);
    },
    removeSelectedGrant(grantId) {
      // Remove grant by its ID from the selectedGrants array
      this.selectedGrants = this.selectedGrants.filter(g => g.id !== grantId);
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
