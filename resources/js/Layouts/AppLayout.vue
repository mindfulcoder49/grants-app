<template>
    <header class="">
      <!-- Logo -->
      <Logo class="logo" />
  
      <!-- Navigation Links -->
      <div class="header-links flex items-center text-gray-500 text-sm">
        <a href="/about" class="hover:underline">About</a>
        <span class="mx-2">|</span>
        <LastUpdateText :lastUpdate="lastUpdate" />
      </div>
    </header>
  
    <main class="py-20 px-8">
      <slot></slot>
    </main>
   <!--
    <footer class="flex justify-center items-center py-4 px-8 bg-gray-100 border-t border-gray-200">
     
      <div class="flex items-center space-x-4">
        <a href="#" class="text-gray-500 hover:text-gray-700">
          <i class="fab fa-facebook"></i> 
        </a>
        <a href="#" class="text-gray-500 hover:text-gray-700">
          <i class="fab fa-twitter"></i> 
        </a>
        <a href="#" class="text-gray-500 hover:text-gray-700">
          <i class="fab fa-linkedin"></i> 
        </a>
        
        <a href="mailto:admin@site.com" class="text-blue-500 hover:underline">Contact Me</a>
      </div>
    </footer>
    -->
  </template>
  
  <script>
  import Logo from '@/Components/Logo.vue';
  import LastUpdateText from '@/Components/LastUpdateText.vue';
  
  export default {
    name: 'AppLayout',
    components: { Logo, LastUpdateText },
    data() {
      return {
        lastUpdate: '', // Store the last update date
      };
    },
    created() {
      this.fetchLastUpdate();
    },
    methods: {
      fetchLastUpdate() {
        // Fetch last update date from the server
        axios.get('/api/last-update')
          .then(response => {
            this.lastUpdate = response.data.lastUpdate;
          })
          .catch(error => {
            console.error('Error fetching last update:', error);
          });
      },
    },
  };
  </script>
  