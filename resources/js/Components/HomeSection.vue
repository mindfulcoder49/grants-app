<template>
  <div v-if="searchPerformed">
    <!-- Pass selectedGrants to AiAssistant -->
    <ai-assistant :grants="selectedGrants" :govgrants="govgrants" @remove-grant="removeSelectedGrant" />
  </div>
  
  <!-- Home Page Layout (content only, no header/footer) -->
  <h1 class="text-center text-3xl font-bold my-12">
    I am looking for grants
  </h1>
  
  <SearchInput v-model="companyDescription" />
  <SearchButton :companyDescription="companyDescription" @search="performSearch" />

  <!-- Conditionally render content only after a search is performed -->
  <div v-if="searchPerformed">
    <div class="results-container">
      <div v-if="$page.props.grants != null" class="results-header">
        Relevant Grants
      </div>
      <div class="results-content">
        <!-- Pass selectedGrants to GrantList -->
        <GrantList
          :grants="$page.props.grants"
          :addedGrants="selectedGrants.map(g => g.id)"  
          class="grant-list"
          @add-to-ai-conversation="addSelectedGrant"
          @remove-from-ai-conversation="removeSelectedGrant"
        />
      </div>
    </div>
  </div>
</template>

<script>
import SearchInput from '@/Components/SearchInput.vue';
import SearchButton from '@/Components/SearchButton.vue';
import AiAssistant from '@/Components/AiAssistant.vue';
import GrantList from '@/Components/GrantList.vue';

export default {
  name: 'Home',
  components: { SearchInput, SearchButton, AiAssistant, GrantList },
  data() {
    return {
      companyDescription: this.searchTerm || '',
      searchPerformed: false,
      govgrants: [],  // Store all the loaded government grant data here
      selectedGrants: [],  // Store selected grants here
    };
  },
  props: {
    searchTerm: {
      type: String,
      default: '',
    },
    grants: {
      type: Array,
      default: () => [],
    },
  },
  methods: {
    performSearch(description) {
      this.searchPerformed = true;
      this.$inertia.post('/', { description });
    },
    addSelectedGrant(grant) {
      if (!this.selectedGrants.some(g => g.id === grant.id)) {
        this.selectedGrants.push(grant);
      }
    },
    removeSelectedGrant(grantId) {
      // Remove the grant by its ID from the selectedGrants array
      this.selectedGrants = this.selectedGrants.filter(g => g.id !== grantId);
    },
  },
};
</script>
