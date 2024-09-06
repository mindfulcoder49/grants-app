<template>
  <AppLayout>
    <!-- Home Page Layout (content only, no header/footer) -->
    <h1 class="text-center text-3xl font-bold my-12">
      I am Massachusetts-based and looking for AI grants
    </h1>
    <SearchInput v-model="companyDescription" />
    <SearchButton :companyDescription="companyDescription" @search="performSearch" />

    <!-- Conditionally render content only after a search is performed -->
    <div v-if="searchPerformed">
      <!-- Pass selectedGrants to AiAssistant -->
      <ai-assistant :grants="selectedGrants" :govgrants="govgrants" />

      <div class="results-container">
        <div v-if="$page.props.grants != null" class="results-header">
          Relevant Grants
        </div>
        <div class="results-content">
          <!-- Add event listener to track selected grants -->
          <GrantList :grants="grants" class="grant-list" @add-to-ai-conversation="addSelectedGrant" />
        </div>
      </div>

      <GrantsGovSearch :companyDescription="companyDescription" @grant-details="storeGovGrants"  />
    </div>
  </AppLayout>
</template>

<script>
import SearchInput from '@/Components/SearchInput.vue';
import SearchButton from '@/Components/SearchButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AiAssistant from '@/Components/AiAssistant.vue';
import GrantList from '@/Components/GrantList.vue';
import GrantsGovSearch from '@/Components/GrantsGovSearch.vue';

export default {
  name: 'Home',
  components: { SearchInput, SearchButton, AppLayout, AiAssistant, GrantList, GrantsGovSearch },
  data() {
    return {
      companyDescription: this.searchTerm || '',
      searchPerformed: false,
      govgrants: [],  // Store all the loaded government grant data here
      selectedGrants: [],  // Store selected grants here
    };
  },
  props: ['grants','searchTerm'],
  methods: {
    performSearch(description) {
      // Logic to handle search
      this.searchPerformed = true;
      this.$inertia.post('/', { description });
    },
    storeGovGrants(grant) {
      // Add the emitted government grant data to the govgrants array
      this.govgrants.push(grant);
    },
    addSelectedGrant(grant) {
      // Add the selected grant to the selectedGrants array
      if (!this.selectedGrants.some(g => g.id === grant.id)) {
        this.selectedGrants.push(grant);
      }
    },
  },
};
</script>
