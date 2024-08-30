<template>
    <!-- Last Update Text Template -->
    <div class="last-update-text">
      last update {{ lastUpdate }}
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
  