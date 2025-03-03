<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const alertSettings = ref(user.alerts_setting || { frequency: 'weekly' });
const alertFrequency = computed({
    get: () => alertSettings.value?.frequency || 'weekly',
    set: (value) => { alertSettings.value.frequency = value; },
});
const companyDescription = ref(user.company_description || '');
const statusMessage = ref('');
const isError = ref(false);

const updateAlertSettings = async () => {
    try {
        const response = await axios.post('/profile/settings', {
            company_description: companyDescription.value,
            alerts_setting: alertSettings.value,
        });
        statusMessage.value = 'Settings updated successfully.';
        isError.value = false;
    } catch (error) {
        statusMessage.value = 'Failed to update settings. Please try again.';
        isError.value = true;
        console.error(error);
    }
};

const saveAndTestAlert = async () => {
    try {
        await updateAlertSettings();
        const response = await axios.post('/dispatch-saved-alerts');
        statusMessage.value += ' Test alert sent successfully!';
        console.log('Test Response:', response.data);
    } catch (error) {
        statusMessage.value = 'Failed to send test alert. Please try again.';
        isError.value = true;
        console.error(error);
    }
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>
                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>

        <!-- Saved Search and Alert Settings Section -->
        <section class="mt-10">
            <header>
                <h2 class="text-lg font-medium text-gray-900">Saved Search Alerts</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Configure your saved search alerts and update frequency.
                </p>
            </header>

            <div class="mt-6 space-y-6">
                <div>
                    <InputLabel for="company-description" value="Company Description" />
                    <textarea
                        id="company-description"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        v-model="companyDescription"
                        rows="3"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.company_description" />
                </div>

                <div>
                    <InputLabel for="alert-frequency" value="Alert Frequency" />
                    <select
                        id="alert-frequency"
                        v-model="alertFrequency"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    >
                        <option value="disabled">Disabled</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.frequency" />
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton @click="updateAlertSettings">Update Alerts</PrimaryButton>
                    <PrimaryButton @click="saveAndTestAlert" class="bg-blue-600 hover:bg-blue-700 text-white">
                        Save & Test Alert
                    </PrimaryButton>
                </div>
                <p
                    v-if="statusMessage"
                    :class="['mt-4 text-sm text-center', isError ? 'text-red-600' : 'text-green-600']"
                >
                    {{ statusMessage }}
                </p>
            </div>
        </section>
    </section>
</template>
