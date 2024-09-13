<template>
  <div>
    <!-- Header Section -->
    <nav class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
            </div>
            <!-- Navigation Links (Visible on both Guest and Authenticated views) -->
            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
              <SpanNavLink :active="currentPage === 'home'" @click="setPage('home')">Home</SpanNavLink>
              <SpanNavLink :active="currentPage === 'about'" @click="setPage('about')">About</SpanNavLink>
              <SpanNavLink :active="currentPage === 'update'" @click="setPage('update')">
                Last Data Update:&nbsp <LastUpdateText :lastUpdate="lastUpdate" />
              </SpanNavLink>
            </div>
          </div>

          <div class="hidden sm:flex sm:items-center sm:ml-6">
            <!-- If user is authenticated -->
            <div v-if="user" class="ml-3 relative">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button
                      type="button"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                    >
                      {{ user.name }}
                      <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </span>
                </template>

                <template #content>
                  <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                  <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                </template>
              </Dropdown>
            </div>
            <!-- If guest -->
            <div v-else class="ml-3 relative">
              <Dropdown align="right" width="48">
                <template #trigger>
                  <span class="inline-flex rounded-md">
                    <button
                      type="button"
                      class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                    >
                      Login/Register
                      <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </span>
                </template>

                <template #content>
                  <DropdownLink :href="route('login')" @click="warnNavigation">Login</DropdownLink>
                  <DropdownLink :href="route('register')" @click="warnNavigation">Register</DropdownLink>
                </template>
              </Dropdown>
            </div>
          </div>

          <!-- Hamburger (Mobile View) -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Responsive Navigation Menu -->
      <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <SpanResponsiveNavLink :href="route('home')" :active="currentPage === 'home'" @click="setPage('home')">Home</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :href="route('about')" :active="currentPage === 'about'" @click="setPage('about')">About</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :href="route('update')" :active="currentPage === 'update'" @click="setPage('update')">
            Last Data Update:&nbsp <LastUpdateText :lastUpdate="lastUpdate" />
          </SpanResponsiveNavLink>
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
          <!-- If authenticated user -->
          <div v-if="user">
            <div class="px-4">
              <div class="font-medium text-base text-gray-800">{{ user.name }}</div>
              <div class="font-medium text-sm text-gray-500">{{ user.email }}</div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
            </div>
          </div>

          <!-- If guest -->
          <div v-else>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('login')" @click="warnNavigation">Login</ResponsiveNavLink>
              <ResponsiveNavLink :href="route('register')" @click="warnNavigation">Register</ResponsiveNavLink>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="py-6">
      <div v-show="currentPage === 'home'">
        <HomeSection />
      </div>
      <div v-show="currentPage === 'about'">
        <About />
      </div>
      <div v-show="currentPage === 'update'">
        <Update />
      </div>
    </main>

    <!-- Footer -->
    <footer class="pl-20">
      <p>&copy; 2024 <a href="https://alcivartech.com" class="text-blue-500 hover:underline">AlcivarTech</a></p>
    </footer>
  </div>
</template>

<script>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SpanNavLink from '@/Components/SpanNavLink.vue';
import SpanResponsiveNavLink from '@/Components/SpanResponsiveNavLink.vue';
import LastUpdateText from '@/Components/LastUpdateText.vue';
import HomeSection from '@/Components/HomeSection.vue';
import About from '@/Pages/About.vue';
import Update from '@/Pages/Update.vue';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';


export default {
  components: { ApplicationLogo, Dropdown, DropdownLink, NavLink, ResponsiveNavLink, LastUpdateText, HomeSection, About, Update, Link, SpanNavLink, SpanResponsiveNavLink },  
  setup() {
    const showingNavigationDropdown = ref(false);
    const currentPage = ref('home'); // Default to home
    const lastUpdate = ref(''); // Fetch last update dynamically if needed

    const warnNavigation = () => {
      if (confirm('Navigating to Login/Register will reload the page and reset your current search and assistant conversation. Proceed?')) {
        // Allow navigation
      } else {
        event.preventDefault(); // Prevent navigation
      }
    };

    const setPage = (page) => {
      currentPage.value = page;
    };

    return {
      showingNavigationDropdown,
      currentPage,
      lastUpdate,
      warnNavigation,
      setPage,
    };
  },
  computed: {
    user() {
      return this.$page.props.user; // Check for authenticated user
    },
  },
};
</script>

<style scoped>
main {
  margin-left: 3%;
  margin-right: 3%;
}
</style>
