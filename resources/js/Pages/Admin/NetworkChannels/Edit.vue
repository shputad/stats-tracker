<template>
    <Head title="Edit Channel" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Edit Network Channel</h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
            </div>
            <div class="mb-4">
                <label for="website" class="block text-gray-700">Website</label>
                <input v-model="form.website" type="url" id="website" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.website" class="text-red-600 text-sm">{{ form.errors.website }}</span>
            </div>
            <div class="mb-4">
                <label for="has_api" class="block text-gray-700">Has API</label>
                <select v-model="form.has_api" id="has_api" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
                <span v-if="form.errors.has_api" class="text-red-600 text-sm">{{ form.errors.has_api }}</span>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-gray-700">Status</label>
                <select v-model="form.status" id="status" class="mt-1 block w-full border-gray-300 rounded-md">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <span v-if="form.errors.status" class="text-red-600 text-sm">{{ form.errors.status }}</span>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
            <Link href="/admin/network-channels" class="px-4 py-2.5 bg-gray-600 text-white rounded-md ml-3">
                Cancel
            </Link>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const channel = props.channel;

const form = useForm({
    name: channel.name,
    website: channel.website,
    has_api: channel.has_api.toString(),
    status: channel.status
});

const submit = () => {
    form.put(route('admin.network-channels.update', channel.id));
};
</script>
