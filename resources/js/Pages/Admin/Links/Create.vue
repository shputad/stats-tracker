<template>
    <Head title="Create Link" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">
                <GoBack :href="route('admin.links.index')" />
                Create Link
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
                <!-- URL Field -->
                <div class="mb-4">
                    <label for="url" class="block text-gray-700 font-medium">URL</label>
                    <textarea v-model="form.url" id="url"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
                    <span v-if="form.errors.url" class="text-red-600 text-sm">
                        {{ form.errors.url }}
                    </span>
                </div>
                <!-- Type Field -->
                <div class="mb-4">
                    <label for="type" class="block text-gray-700 font-medium">Type</label>
                    <select v-model="form.type" id="type" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
                        <option value="lumma">Lumma</option>
                        <option value="vidar">Vidar</option>
                    </select>
                    <span v-if="form.errors.type" class="text-red-600 text-sm">
                        {{ form.errors.type }}
                    </span>
                </div>
                <!-- Status Field -->
                <div class="mb-4">
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
    url: '',
    type: 'lumma',
    status: 'active'
});

const submit = () => {
    form.post(route('admin.links.store'));
};
</script>