<template>
    <Head title="Admin Settings" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Admin Settings</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="link_stats_update_interval" class="block text-gray-700">
                    Link Stats Update Interval (in minutes)
                </label>
                <input
                    v-model="form.link_stats_update_interval"
                    type="number"
                    id="link_stats_update_interval"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.link_stats_update_interval" class="text-red-600 text-sm">
                    {{ form.errors.link_stats_update_interval }}
                </span>
            </div>

            <div class="mb-4">
                <label for="profile_stats_update_interval" class="block text-gray-700">
                    Profile Stats Update Interval (in minutes)
                </label>
                <input
                    v-model="form.profile_stats_update_interval"
                    type="number"
                    id="profile_stats_update_interval"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.profile_stats_update_interval" class="text-red-600 text-sm">
                    {{ form.errors.profile_stats_update_interval }}
                </span>
            </div>

            <div class="mb-4">
                <label for="capmonster_api_key" class="block text-gray-700">CapMonster API Key</label>
                <input
                    v-model="form.capmonster_api_key"
                    type="text"
                    id="capmonster_api_key"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.capmonster_api_key" class="text-red-600 text-sm">
                    {{ form.errors.capmonster_api_key }}
                </span>
            </div>

            <div class="mb-4">
                <label for="twocaptcha_api_key" class="block text-gray-700">2Captcha API Key</label>
                <input
                    v-model="form.twocaptcha_api_key"
                    type="text"
                    id="twocaptcha_api_key"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.twocaptcha_api_key" class="text-red-600 text-sm">
                    {{ form.errors.twocaptcha_api_key }}
                </span>
            </div>

            <div class="mb-4">
                <label for="google_cloud_run_url" class="block text-gray-700">Google Cloud Run URL</label>
                <input
                    v-model="form.google_cloud_run_url"
                    type="text"
                    id="google_cloud_run_url"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.google_cloud_run_url" class="text-red-600 text-sm">
                    {{ form.errors.google_cloud_run_url }}
                </span>
            </div>

            <div class="mb-4">
                <label for="proxy_type" class="block text-gray-700">Proxy Type</label>
                <select id="proxy_type" class="mt-1 block w-full border-gray-300 rounded-md" v-model="form.proxy_type">
                    <option value="">Select a type</option>
                    <option value="http">http</option>
                    <option value="https">https</option>
                    <option value="socks4">socks4</option>
                    <option value="socks5">socks5</option>
                </select>
                <span v-if="form.errors.proxy_type" class="text-red-600 text-sm">
                    {{ form.errors.proxy_type }}
                </span>
            </div>

            <div class="mb-4">
                <label for="proxy_address" class="block text-gray-700">Proxy Address</label>
                <input
                    v-model="form.proxy_address"
                    type="text"
                    id="proxy_address"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.proxy_address" class="text-red-600 text-sm">
                    {{ form.errors.proxy_address }}
                </span>
            </div>

            <div class="mb-4">
                <label for="proxy_port" class="block text-gray-700">Proxy Port</label>
                <input
                    v-model="form.proxy_port"
                    type="text"
                    id="proxy_port"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.proxy_port" class="text-red-600 text-sm">
                    {{ form.errors.proxy_port }}
                </span>
            </div>

            <div class="mb-4">
                <label for="proxy_login" class="block text-gray-700">Proxy Login</label>
                <input
                    v-model="form.proxy_login"
                    type="text"
                    id="proxy_login"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.proxy_login" class="text-red-600 text-sm">
                    {{ form.errors.proxy_login }}
                </span>
            </div>

            <div class="mb-4">
                <label for="proxy_password" class="block text-gray-700">Proxy Password</label>
                <input
                    v-model="form.proxy_password"
                    type="password"
                    id="proxy_password"
                    class="mt-1 block w-full border-gray-300 rounded-md"
                />
                <span v-if="form.errors.proxy_password" class="text-red-600 text-sm">
                    {{ form.errors.proxy_password }}
                </span>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save Settings
            </button>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useToast } from 'vue-toastification';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';

const toast = useToast();
const { props } = usePage();

const form = useForm({
    link_stats_update_interval: props.settings.link_stats_update_interval || '',
    profile_stats_update_interval: props.settings.profile_stats_update_interval || '',
    capmonster_api_key: props.settings.capmonster_api_key || '',
    twocaptcha_api_key: props.settings.twocaptcha_api_key || '',
    google_cloud_run_url: props.settings.google_cloud_run_url || '',
    proxy_type: props.settings.proxy_type || '',
    proxy_address: props.settings.proxy_address || '',
    proxy_port: props.settings.proxy_port || '',
    proxy_login: props.settings.proxy_login || '',
    proxy_password: props.settings.proxy_password || '',
});

const flash = props.flash || {};

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Settings updated successfully!');
        },
        onError: () => {
            toast.error('Error updating settings.');
        },
    });
};
</script>
