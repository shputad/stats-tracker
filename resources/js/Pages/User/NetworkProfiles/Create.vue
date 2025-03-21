<template>
    <Head title="Create Network Profile" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl">
            <!-- Page Heading -->
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('user.network-profiles.index')" class="mr-2" />
                Create Network Profile
            </h1>

            <!-- Form Card -->
            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Profile Information</h2>

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
                        :href="route('user.network-profiles.index')"
                        class="px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded-md hover:bg-gray-600 transition text-center"
                    >
                        Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({ channels: Array, links: Array });

const form = useForm({
    channel_id: '',
    account_id: '',
    api_key: '',
    status: '',
});

const submit = () => {
    form.post(route('user.network-profiles.store'));
};
</script>