<template>
  <div>
    <!-- Check if the user is authenticated -->
    <AuthenticatedLayout v-if="user">
      <slot />

      <!-- Authenticated-only footer -->
      <footer class="pl-20">
        <p>&copy; 2024 <a href="https://alcivartech.com" class="text-blue-500 hover:underline">AlcivarTech</a></p>
      </footer>
    </AuthenticatedLayout>

    <!-- Guest layout if not authenticated -->
    <GuestLayout v-else>
      <slot />

      <!-- Guest-only footer -->
      <footer class="pl-20">
        <p>&copy; 2024 <a href="https://alcivartech.com" class="text-blue-500 hover:underline">AlcivarTech</a></p>
      </footer>
    </GuestLayout>
  </div>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';


export default {
  components: { AuthenticatedLayout, GuestLayout },
  data() {
    return {
      // Track the current page (view)
      currentPage: 'home',  // Default to 'home'
    };
  },
  computed: {
    user() {
      return this.$page.props.auth.user;  // Get user from Inertia page props
    }
  },
};
</script>
