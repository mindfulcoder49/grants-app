<template>
    <!-- Grant List Component -->
    <div v-for="grant in grants" :key="grant.id" class="grant-item border-b border-gray-300 pb-4 mb-4">
      <h3 class="text-xl font-bold mb-2">{{ grant.title }} - Match: {{ grant.match }}%</h3>
      <p class="text-sm text-gray-700 mb-2">{{ grant.description }}</p>
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <strong>Amount:</strong> {{ grant.amount }}<br>
          <strong>Deadline:</strong> {{ grant.deadline }}<br>
          <strong>Purpose:</strong> {{ grant.purpose }}<br>
          <strong>Eligibility:</strong> {{ grant.eligibility }}
        </div>
        <div>
          <strong>Application Process:</strong> {{ grant.application_process }}<br>
          <strong>Contact:</strong> {{ grant.contact }}<br>
          <strong>Website:</strong> <a :href="grant.website" target="_blank" class="text-blue-500 hover:underline">{{ grant.website }}</a>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'GrantList',
    props: ['grants'],
  };
  </script>
  
  <style scoped>
  .grant-item {
    padding: 1rem;
  }
  </style>
  