<template>

    <Head title="Admin Settings" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Admin Settings</h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Link Stats Update Interval -->
                    <div>
                        <label for="link_stats_update_interval" class="block text-sm font-medium text-gray-700">
                            Link Stats Update Interval (in minutes)
                        </label>
                        <input v-model="form.link_stats_update_interval" type="number" id="link_stats_update_interval"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.link_stats_update_interval" class="text-red-600 text-sm mt-1">
                            {{ form.errors.link_stats_update_interval }}
                        </p>
                    </div>

                    <!-- Profile Stats Update Interval -->
                    <div>
                        <label for="profile_stats_update_interval" class="block text-sm font-medium text-gray-700">
                            Profile Stats Update Interval (in minutes)
                        </label>
                        <input v-model="form.profile_stats_update_interval" type="number"
                            id="profile_stats_update_interval"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.profile_stats_update_interval" class="text-red-600 text-sm mt-1">
                            {{ form.errors.profile_stats_update_interval }}
                        </p>
                    </div>

                    <!-- Stats Per Page -->
                    <div>
                        <label for="stats_per_page" class="block text-sm font-medium text-gray-700">
                            Stats Per Page
                        </label>
                        <input v-model="form.stats_per_page" type="number" id="stats_per_page"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.stats_per_page" class="text-red-600 text-sm mt-1">
                            {{ form.errors.stats_per_page }}
                        </p>
                    </div>

                    <!-- CapMonster API Key -->
                    <div>
                        <label for="capmonster_api_key" class="block text-sm font-medium text-gray-700">
                            CapMonster API Key
                        </label>
                        <input v-model="form.capmonster_api_key" type="text" id="capmonster_api_key"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.capmonster_api_key" class="text-red-600 text-sm mt-1">
                            {{ form.errors.capmonster_api_key }}
                        </p>
                    </div>

                    <!-- 2Captcha API Key -->
                    <div>
                        <label for="twocaptcha_api_key" class="block text-sm font-medium text-gray-700">
                            2Captcha API Key
                        </label>
                        <input v-model="form.twocaptcha_api_key" type="text" id="twocaptcha_api_key"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.twocaptcha_api_key" class="text-red-600 text-sm mt-1">
                            {{ form.errors.twocaptcha_api_key }}
                        </p>
                    </div>

                    <!-- Google Cloud Run URL -->
                    <div>
                        <label for="google_cloud_run_url" class="block text-sm font-medium text-gray-700">
                            Google Cloud Run URL
                        </label>
                        <input v-model="form.google_cloud_run_url" type="text" id="google_cloud_run_url"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.google_cloud_run_url" class="text-red-600 text-sm mt-1">
                            {{ form.errors.google_cloud_run_url }}
                        </p>
                    </div>

                    <!-- Proxy Type -->
                    <div>
                        <label for="proxy_type" class="block text-sm font-medium text-gray-700">
                            Proxy Type
                        </label>
                        <select id="proxy_type" v-model="form.proxy_type"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a type</option>
                            <option value="http">http</option>
                            <option value="https">https</option>
                            <option value="socks4">socks4</option>
                            <option value="socks5">socks5</option>
                        </select>
                        <p v-if="form.errors.proxy_type" class="text-red-600 text-sm mt-1">
                            {{ form.errors.proxy_type }}
                        </p>
                    </div>

                    <!-- Proxy Address -->
                    <div>
                        <label for="proxy_address" class="block text-sm font-medium text-gray-700">
                            Proxy Address
                        </label>
                        <input v-model="form.proxy_address" type="text" id="proxy_address"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.proxy_address" class="text-red-600 text-sm mt-1">
                            {{ form.errors.proxy_address }}
                        </p>
                    </div>

                    <!-- Proxy Port -->
                    <div>
                        <label for="proxy_port" class="block text-sm font-medium text-gray-700">
                            Proxy Port
                        </label>
                        <input v-model="form.proxy_port" type="text" id="proxy_port"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.proxy_port" class="text-red-600 text-sm mt-1">
                            {{ form.errors.proxy_port }}
                        </p>
                    </div>

                    <!-- Proxy Login -->
                    <div>
                        <label for="proxy_login" class="block text-sm font-medium text-gray-700">
                            Proxy Login
                        </label>
                        <input v-model="form.proxy_login" type="text" id="proxy_login"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.proxy_login" class="text-red-600 text-sm mt-1">
                            {{ form.errors.proxy_login }}
                        </p>
                    </div>

                    <!-- Proxy Password -->
                    <div>
                        <label for="proxy_password" class="block text-sm font-medium text-gray-700">
                            Proxy Password
                        </label>
                        <input v-model="form.proxy_password" type="password" id="proxy_password"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.proxy_password" class="text-red-600 text-sm mt-1">
                            {{ form.errors.proxy_password }}
                        </p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from 'vue-toastification';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const toast = useToast();
const { props } = usePage();

const form = useForm({
    link_stats_update_interval: props.settings.link_stats_update_interval || 10,
    profile_stats_update_interval: props.settings.profile_stats_update_interval || 10,
    stats_per_page: props.settings.stats_per_page || 25,
    capmonster_api_key: props.settings.capmonster_api_key || '',
    twocaptcha_api_key: props.settings.twocaptcha_api_key || '',
    google_cloud_run_url: props.settings.google_cloud_run_url || '',
    proxy_type: props.settings.proxy_type || '',
    proxy_address: props.settings.proxy_address || '',
    proxy_port: props.settings.proxy_port || '',
    proxy_login: props.settings.proxy_login || '',
    proxy_password: props.settings.proxy_password || '',
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Settings updated successfully!'),
        onError: () => toast.error('Error updating settings.')
    });
};
</script>