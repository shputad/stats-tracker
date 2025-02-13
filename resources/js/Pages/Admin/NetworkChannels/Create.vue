<template>
    <Head title="Create Channel" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.network-channels.index')" />
                Create Network Channel
            </h1>
            <form @submit.prevent="submit" class="bg-white p-6 shadow rounded">
                <!-- Name Field -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium">Name</label>
                    <input v-model="form.name" type="text" id="name"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.name" class="text-red-600 text-sm">
                        {{ form.errors.name }}
                    </span>
                </div>
                <!-- Website Field -->
                <div class="mb-4">
                    <label for="website" class="block text-gray-700 font-medium">Website</label>
                    <input v-model="form.website" type="url" id="website"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                    <span v-if="form.errors.website" class="text-red-600 text-sm">
                        {{ form.errors.website }}
                    </span>
                </div>
                <!-- Has API and Status Fields in Single Row -->
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <div class="flex-1">
                        <label for="has_api" class="block text-gray-700 font-medium">Has API</label>
                        <select v-model="form.has_api" id="has_api"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <span v-if="form.errors.has_api" class="text-red-600 text-sm">
                            {{ form.errors.has_api }}
                        </span>
                    </div>
                    <div class="flex-1">
                        <label for="status" class="block text-gray-700 font-medium">Status</label>
                        <select v-model="form.status" id="status"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <span v-if="form.errors.status" class="text-red-600 text-sm">
                            {{ form.errors.status }}
                        </span>
                    </div>
                </div>
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Create
                    </button>
                    <Link :href="route('admin.network-channels.index')"
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

const form = useForm({
    name: '',
    website: '',
    has_api: '1',
    status: 'active'
});

const submit = () => {
    form.post(route('admin.network-channels.store'));
};
</script>