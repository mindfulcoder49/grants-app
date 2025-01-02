<template>
  <Head :title="currentTitle" />
  <div>
    <!-- Header Section -->
    <nav class="">
      <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- Logo -->
            <div class="shrink-0 flex items-center"
            @click="setPage('home')">
              <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" /> 
            </div>
            <!-- Navigation Links (Visible on both Guest and Authenticated views) -->
            <div class="hidden space-x-8 md:-my-px md:ml-10 md:flex">
              <SpanNavLink :active="currentPage === 'home'" @click="setPage('home')">Home</SpanNavLink>
              <SpanNavLink :active="currentPage === 'about'" @click="setPage('about')">About</SpanNavLink>
              <SpanNavLink :active="currentPage === 'sources'" @click="setPage('sources')">Sources</SpanNavLink>
              <SpanNavLink :active="currentPage === 'help-us-improve'" @click="setPage('help-us-improve')">Help Us Improve</SpanNavLink>
              <SpanNavLink :active="currentPage === 'update'" @click="setPage('update')">
                Last Data Update:&nbsp <LastUpdateText />
              </SpanNavLink>
              <SpanNavLink :active="currentPage === 'saved-grants'" @click="setPage('saved-grants')">Saved Grants</SpanNavLink>
            </div>
          </div>

          <div class="hidden md:flex md:items-center md:ml-6">
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
          <div class="-mr-2 flex items-center md:hidden">
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
      <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="md:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <SpanResponsiveNavLink :active="currentPage === 'home'" @click="setPage('home')">Home</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :active="currentPage === 'about'" @click="setPage('about')">About</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :active="currentPage === 'sources'" @click="setPage('sources')">Sources</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :active="currentPage === 'help-us-improve'" @click="setPage('help-us-improve')">Help Us Improve</SpanResponsiveNavLink>
          <SpanResponsiveNavLink :active="currentPage === 'update'" @click="setPage('update')">
            Last Data Update:&nbsp <LastUpdateText />
          </SpanResponsiveNavLink>
          <SpanResponsiveNavLink :active="currentPage === 'saved-grants'" @click="setPage('saved-grants')">Saved Grants</SpanResponsiveNavLink>
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
    <main class="">
      <!--Absolutely positioned vertical navigation buttons-->
      <button @click="setPage('help-us-improve')" id="feedback_button" class="fixed bottom-4 right-4 bg-[#ccc] hover:bg-[#bbb] text-black font-bold py-2 px-4 rounded-md border border-black">
        Help Us Improve
      </button>

      <div v-show="currentPage === 'home'">
        <HomeSection :searchTerm="searchTerm" :permaGrants="grants" />
      </div>
      <div v-show="currentPage === 'about'">
        <About />
      </div>
      <div v-show="currentPage === 'sources'">
        <Sources />
      </div>
      <div v-show="currentPage === 'help-us-improve'">
        <HelpUsImprove />
      </div>
      <div v-show="currentPage === 'update'">
        <!-- can emit -->
        <Update />
      </div>
      <div v-show="currentPage === 'saved-grants'">
        <SavedGrants ref="savedGrantsRef" />
      </div>

    </main>

    <!-- Footer -->
    <footer class="">
      <p class="mb-4">&copy; 2024 <a href="https://alcivartech.com" class="text-blue-500 hover:underline">AlcivarTech</a></p>
      <p class="mb-4">
          <!-- <a href="/grants-android-2024-09-20.apk" class="text-blue-500 hover:underline">Download the Android app</a> -->
        </p>
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
import Sources from '@/Pages/Sources.vue';
import HelpUsImprove from '@/Pages/HelpUsImprove.vue';
import Update from '@/Pages/Update.vue';
import SavedGrants from '@/Pages/SavedGrants.vue';
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { event } from 'vue-gtag';
import { Head } from '@inertiajs/vue3'



export default {
  name: 'Home',
  components: {
    ApplicationLogo, Dropdown, DropdownLink, NavLink, ResponsiveNavLink, 
    LastUpdateText, HomeSection, About, Update, Link, SpanNavLink, SpanResponsiveNavLink, SavedGrants,
    Sources, HelpUsImprove, Head
  },
  setup() {
    const showingNavigationDropdown = ref(false);
    const currentPage = ref('home'); // Default to home
    const searchTerm = ref('');

    const setPage = (page) => {
      currentPage.value = page;
    };

    // Global click tracking with gtag 
    onMounted(() => {
      document.addEventListener('click', (e) => {
        event('click', {
          event_category: 'click',
          //make the label the text of the element clicked
          event_label: e.target.innerText,
          value: 1
        });
      });

      // Track page views
      event('page_view', {
        page_location: currentPage.value,
        page_path: window.location.pathname, 
        page_title: document.title
      });



    });

    return {
      showingNavigationDropdown,
      currentPage,
      setPage,
      searchTerm,
    };
  },
  methods: {
    warnNavigation() {
      if (!confirm('Navigating to Login/Register will clear your search and AI Chat. Continue?')) {
        event.preventDefault();
      }
    }
  },
  props: {
    grants: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    user() {
      return this.$page.props.auth.user;
    },
    currentTitle() {
      switch (this.currentPage) {
        case 'home':
          return 'Home';
        case 'about':
          return 'About';
        case 'sources':
          return 'Sources';
        case 'help-us-improve':
          return 'Help Us Improve';
        case 'update':
          return 'Data Updates';
        case 'saved-grants':
          return 'Saved Grants';
        default:
          return 'Page';
      }
    }
  },
  watch: {
    // Watch for changes to the current page
    currentPage(newPage) {
      if (newPage === 'saved-grants') {
        // Trigger data update when navigating to "Saved Grants"
        this.$refs.savedGrantsRef.fetchGrants();
      }
    }
  }
};

</script>

<style scoped>
main {
  margin-left: 3%;
  margin-right: 3%;
}

/* Optional: Add some basic styles for the footer */
footer {
  margin-top: 2rem;
  text-align: center;
}

#feedback_button {
  z-index: 1000;
}

</style>
