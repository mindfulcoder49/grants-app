<template>
  <div class="container w-full sm:w-[70%] mx-auto ">
    <div v-if="!user" class="alert-settings text-center">
      <!-- Not Logged In -->
      <p class="description text-lg text-black">
        Please <a href="/login" class="text-blue-600 underline">log in</a> to save your search and receive results directly to your email.
      </p>
    </div>

    <div v-else-if="!alerts_setting && !localCompanyDescription" class="alert-settings">
      <!-- No Alerts Configured -->
      <p class="description text-lg text-black mb-4">
        Save your search and configure email alerts by selecting your preferences below.
      </p>

      <div class="alert-controls flex flex-col sm:flex-row gap-4 items-start">
        <!-- Dropdown for Update Frequency -->
        <div class="w-auto">
          <label for="update-frequency" class="label block text-black font-bold mb-2">Select Frequency:</label>
          <select id="update-frequency" v-model="localUpdateFrequency" class="update-frequency w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="disabled">Disabled</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
          </select>
        </div>

        <textarea
          v-model="localCompanyDescription"
          placeholder="Describe your company..."
          class="textarea w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
          rows="3"
        ></textarea>
      </div>

      <button @click="enableAlerts" class="enable-alerts-btn mt-4 bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700">Enable Search Alerts</button>
    </div>

    <div v-else class="alert-settings">
      <!-- Alerts Configured -->
      <p class="description text-lg text-black mb-4">
        Your current search alert settings:
      </p>

      <div class="flex flex-col sm:flex-row gap-4 items-start">
        <div class="w-auto">
          <label for="update-frequency" class="label block text-black font-bold mb-2">Update Frequency:</label>
          <select id="update-frequency" v-model="localUpdateFrequency" class="update-frequency w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="disabled">Disabled</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
          </select>
        </div>

        <div class="flex-1 w-full">
          <label for="company-description" class="label block text-black font-bold mb-2">Company Description:</label>
          <textarea
            id="company-description"
            v-model="localCompanyDescription"
            class="textarea w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
            rows="2"
          ></textarea>
        </div>
      </div>

      <button @click="enableAlerts" class="enable-alerts-btn mt-4 border-2 border-black text-black px-4 py-2 rounded-md hover:bg-blue-700 hover:text-white">Update Search Alerts</button>
      <button @click="testAlert" class="test-alert-btn mt-4 ml-4 border-2 border-black text-black px-4 py-2 rounded-md hover:bg-blue-700 hover:text-white">Save & Test Alert</button>
    </div>

    <!-- Status Message -->
    <p v-if="statusMessage" :class="['status-message mt-4 text-center', isError ? 'text-red-600' : 'text-blue-700']">{{ statusMessage }}</p>
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
    //save the alert settings and if that succeeds send a second call to dispatch a test alert
    const testAlert = async () => {
      try {
        const response = await axios.post("/profile/settings", {
          company_description: localCompanyDescription.value,
          alerts_setting: { frequency: localUpdateFrequency.value },
        });
        statusMessage.value = "Search alerts updated successfully!";
        isError.value = false;
        console.log("Response:", response.data);
        //send a test alert
        const testResponse = await axios.post("/dispatch-saved-alerts");
        console.log("Test Response:", testResponse.data);
        statusMessage.value += " Test alert sent successfully!";
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
      testAlert,
    };
  },
};
</script>

<style>
/* Tailwind CSS is used for styling */
</style>
