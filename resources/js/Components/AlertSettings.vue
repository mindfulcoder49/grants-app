<template>
    <div>
      <div v-if="!user" class="alert-settings">
        <!-- Not Logged In -->
        <p class="description">
          Please <a href="/login" class="text-blue-600 underline">log in</a> to save your search and receive results directly to your email.
        </p>
      </div>
      <div v-else-if="!alerts_setting && !localCompanyDescription" class="alert-settings">
        <!-- No Alerts Configured -->
        <p class="description">
          Save your search and configure email alerts by selecting your preferences below.
        </p>
        <div class="alert-controls">
          <!-- Dropdown for Update Frequency -->
          <label for="update-frequency" class="label">Select Frequency:</label>
          <select id="update-frequency" v-model="localUpdateFrequency" class="update-frequency">
            <option value="disabled">Disabled</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
          </select>
        </div>
        <textarea
          v-model="localCompanyDescription"
          placeholder="Describe your company..."
          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          rows="3"
        ></textarea>
        <!-- Enable Alerts Button -->
        <button @click="enableAlerts" class="enable-alerts-btn">Enable Search Alerts</button>
      </div>
      <div v-else  class="alert-settings">
        <!-- Alerts Configured -->
        <p class="description">
          Your current search alert settings:
        </p>
        <div>
          <strong>Frequency:</strong> {{ alerts_setting.frequency }}
        </div>
        <div>
          <strong>Company Description:</strong>
          <textarea
            v-model="localCompanyDescription"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            rows="3"
          ></textarea>
        </div>
        <div class="alert-controls">
          <label for="update-frequency" class="label">Update Frequency:</label>
          <select id="update-frequency" v-model="localUpdateFrequency" class="update-frequency">
            <option value="disabled">Disabled</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
          </select>
        </div>
        <button @click="enableAlerts" class="enable-alerts-btn">Update Search Alerts</button>
      </div>
      <!-- Status Message -->
      <p v-if="statusMessage" :class="['status-message', { error: isError }]">{{ statusMessage }}</p>
    </div>
</template>

<script>
import axios from "axios";
import { usePage } from "@inertiajs/vue3";
import { ref } from "vue";

export default {
  name: "AlertSettings",
  setup() {
    const page = usePage();
    const user = page.props.auth?.user || null;
    const alerts_setting = user?.alerts_setting || { frequency: "disabled" };
    const companyDescription = user?.company_description || "";

    const localUpdateFrequency = ref(alerts_setting.frequency);
    const localCompanyDescription = ref(companyDescription);
    const statusMessage = ref("");
    const isError = ref(false);

    const enableAlerts = async () => {
      try {
        const response = await axios.post("/profile/settings", {
          company_description: localCompanyDescription.value,
          alerts_setting: { frequency: localUpdateFrequency.value },
        });
        statusMessage.value = "Search alerts updated successfully!";
        isError.value = false;
        console.log("Response:", response.data);
      } catch (error) {
        statusMessage.value = "Failed to update search alerts. Please try again.";
        isError.value = true;
        console.error("Error:", error);
      }
    };

    return {
      user,
      alerts_setting,
      localUpdateFrequency,
      localCompanyDescription,
      statusMessage,
      isError,
      enableAlerts,
    };
  },
};
</script>

<style scoped>
.alert-controls {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.alert-settings {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.description {
  font-size: 1rem;
  color: #555;
}

.label {
  font-weight: bold;
}

.update-frequency {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  padding-right: 2rem;
}

.enable-alerts-btn {
  padding: 0.4rem 0.75rem;
  color: black;
  border: 2px solid black;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.enable-alerts-btn:hover {
  background-color: #0056b3;
  color: white;
}

.status-message {
  font-size: 0.9rem;
  color: #28a745; /* Success: Green */
}

.status-message.error {
  color: #dc3545; /* Error: Red */
}
</style>
