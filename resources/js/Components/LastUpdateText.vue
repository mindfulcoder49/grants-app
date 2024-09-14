<template>
  <span class="last-update-text">
    {{ lastUpdate }}
  </span>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'LastUpdateText',
  props: {
    // If you still want to allow passing the lastUpdate via prop
    lastUpdateProp: {
      type: String,
      default: ''
    },
  },
  setup(props) {
    const lastUpdate = ref(props.lastUpdateProp);

    // Fetch the last update from the API
    const getLastUpdate = async () => {
      try {
        const response = await axios.get('/api/last-update');
        lastUpdate.value = response.data.lastUpdate || props.lastUpdateProp;
      } catch (error) {
        console.error('Error fetching last update:', error);
      }
    };

    // Call the getLastUpdate function when the component is mounted
    onMounted(() => {
      if (!props.lastUpdateProp) {
        getLastUpdate();
      }
    });

    return {
      lastUpdate,
    };
  },
};
</script>

<style scoped>
.last-update-text {
  font-weight: bold;
  color: #2c3e50;
}
</style>
