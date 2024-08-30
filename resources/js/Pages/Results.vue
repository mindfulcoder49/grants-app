<template>
    <AppLayout>
    <!-- Results Page Layout -->

    <div class="results-container">
      <div class="results-header">Relevant Grants</div>
      <div class="results-content">
        <GrantList :grants="grants" class="grant-list" />
      </div>
    </div>
    <ai-assistant />
    </AppLayout>
  </template>
  
  <script>
  import AiAssistant from '@/Components/AiAssistant.vue';
import GrantList from '@/Components/GrantList.vue';
  import AppLayout from '@/Layouts/AppLayout.vue';
  
  export default {
    name: 'Results',
    components: { GrantList, AppLayout, AiAssistant },
    props: ['grants'],
  };
  </script>
  