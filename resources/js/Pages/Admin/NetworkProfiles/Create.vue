<template>
    <Head title="Create Network Profile" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.network-profiles.index')" />
                Create Network Profile
            </h1>
            <form @submit.prevent="submit" class="bg-white p-6 shadow rounded">
                <!-- User Field -->
                <div class="mb-4">
                    <label for="user_id" class="block text-gray-700 font-medium">User</label>
                    <select v-model="form.user_id" id="user_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option v-for="user in users" :value="user.id" :key="user.id">{{ user.name }}</option>
                    </select>
                    <span v-if="form.errors.user_id" class="text-red-600 text-sm">{{ form.errors.user_id }}</span>
                </div>
                <!-- Network Channel Field -->
                <div class="mb-4">
                    <label for="channel_id" class="block text-gray-700 font-medium">Network Channel</label>
                    <select v-model="form.channel_id" id="channel_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option v-for="channel in channels" :value="channel.id" :key="channel.id">{{ channel.name }}
                        </option>
                    </select>
                    <span v-if="form.errors.channel_id" class="text-red-600 text-sm">{{ form.errors.channel_id }}</span>
                </div>
                <!-- Link Field -->
                <div class="mb-4">
                    <label for="link_id" class="block text-gray-700 font-medium">Link</label>
                    <select v-model="form.link_id" id="link_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option v-for="link in links" :value="link.id" :key="link.id">{{ link.name }}</option>
                    </select>
                    <span v-if="form.errors.link_id" class="text-red-600 text-sm">{{ form.errors.link_id }}</span>
                </div>
                <!-- Account ID Field -->
                <div class="mb-4">
                    <label for="account_id" class="block text-gray-700 font-medium">Account ID</label>
                    <input v-model="form.account_id" type="text" id="account_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.account_id" class="text-red-600 text-sm">{{ form.errors.account_id }}</span>
                </div>
                <!-- API Key Field -->
                <div class="mb-4">
                    <label for="api_key" class="block text-gray-700 font-medium">API Key</label>
                    <input v-model="form.api_key" type="text" id="api_key"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.api_key" class="text-red-600 text-sm">{{ form.errors.api_key }}</span>
                </div>
                <!-- Status Field -->
                <div class="mb-4">
                    <label for="status" class="block text-gray-700 font-medium">Status</label>
                    <select v-model="form.status" id="status"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</span>
                </div>
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Create
                    </button>
                    <Link :href="route('admin.network-profiles.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700" role="button">
                    Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({ users: Array, channels: Array, links: Array });

const form = useForm({
    user_id: '',
    channel_id: '',
    link_id: '',
    account_id: '',
    api_key: '',
    status: '',
});

const submit = () => {
    form.post(route('admin.network-profiles.store'));
};
</script>