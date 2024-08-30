<template>
    <AppLayout>
      <!-- Home Page Layout (content only, no header/footer) -->
      <h1 class="text-center text-3xl font-bold my-12">I am Massachusetts-based and looking for AI grants</h1>
      <SearchInput v-model="companyDescription" />
      <SearchButton :companyDescription="companyDescription" @search="performSearch" />
    </AppLayout>
  </template>
  
  <script>
  import SearchInput from '@/Components/SearchInput.vue';
  import SearchButton from '@/Components/SearchButton.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  
  export default {
    name: 'Home',
    components: { SearchInput, SearchButton, AppLayout },
    data() {
      return {
        companyDescription: '',
      };
    },
    methods: {
      performSearch(description) {
        // Logic to handle search
        this.$inertia.post('/search', { description });
      },
    },
  };
  </script>
  