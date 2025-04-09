<template>
    <Head title="Create Network Profile" />

    <AdminLayout>
        <div class="mx-auto max-w-3xl">
            <!-- Page Heading -->
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('admin.network-profiles.index')" class="mr-2" />
                Create Network Profile
            </h1>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Profile Details</h2>

                <!-- User -->
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                    <select v-model="form.user_id" id="user_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option disabled value="">Select User</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                    <p v-if="form.errors.user_id" class="text-red-600 text-sm mt-1">{{ form.errors.user_id }}</p>
                </div>

                <!-- Network Channel -->
                <div class="mb-4">
                    <label for="channel_id" class="block text-sm font-medium text-gray-700">Network Channel</label>
                    <select v-model="form.channel_id" id="channel_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option disabled value="">Select Channel</option>
                        <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                    </select>
                    <p v-if="form.errors.channel_id" class="text-red-600 text-sm mt-1">{{ form.errors.channel_id }}</p>
                </div>

                <!-- Account ID -->
                <div class="mb-4">
                    <label for="account_id" class="block text-sm font-medium text-gray-700">Account ID</label>
                    <input v-model="form.account_id" type="text" id="account_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.account_id" class="text-red-600 text-sm mt-1">{{ form.errors.account_id }}</p>
                </div>

                <!-- API Username -->
                <div class="mb-4">
                    <label for="api_username" class="block text-sm font-medium text-gray-700">API Username</label>
                    <input v-model="form.api_username" type="text" id="api_username"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.api_username" class="text-red-600 text-sm mt-1">{{ form.errors.api_username }}</p>
                </div>

                <!-- API Password -->
                <div class="mb-4">
                    <label for="api_password" class="block text-sm font-medium text-gray-700">API Password</label>
                    <input v-model="form.api_password" type="password" id="api_password"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.api_password" class="text-red-600 text-sm mt-1">{{ form.errors.api_password }}</p>
                </div>

                <!-- API Key -->
                <div class="mb-4">
                    <label for="api_key" class="block text-sm font-medium text-gray-700">API Key</label>
                    <input v-model="form.api_key" type="text" id="api_key"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.api_key" class="text-red-600 text-sm mt-1">{{ form.errors.api_key }}</p>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select v-model="form.status" id="status"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option disabled value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <p v-if="form.errors.status" class="text-red-600 text-sm mt-1">{{ form.errors.status }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700 transition"
                    >
                        Create
                    </button>
                    <Link
                        :href="route('admin.network-profiles.index')"
                        class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition text-center"
                    >
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

defineProps({ users: Array, channels: Array });

const form = useForm({
    user_id: '',
    channel_id: '',
    account_id: '',
    api_username: '',
    api_password: '',
    api_key: '',
    status: '',
});

const submit = () => {
    form.post(route('admin.network-profiles.store'));
};
</script>