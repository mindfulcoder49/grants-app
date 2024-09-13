<template>
    <!-- Last Update Text Template -->
    <div class="last-update-text">
      {{ lastUpdate }}
    </div>
  </template>
  
  <script>
  export default {
    name: 'LastUpdateText',
    props: {
      lastUpdate: {
        type: String,
        required: true,
      },
    },
  };
  </script>
  
  <style scoped>

  </style>
  