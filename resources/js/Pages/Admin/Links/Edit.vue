<template>
    <Head title="Edit Link" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/links`"></GoBack> Edit Link
        </h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="name" class="block text-gray-700">Name</label>
                <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
            </div>
            <div class="mb-4">
                <label for="url" class="block text-gray-700">URL</label>
                <textarea v-model="form.url" type="url" id="url" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                <span v-if="form.errors.url" class="text-red-600 text-sm">{{ form.errors.url }}</span>
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
            <Link href="/admin/links" class="px-4 py-2.5 bg-gray-600 text-white rounded-md ml-3" role="button">
                Cancel
            </Link>
        </form>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;

const form = useForm({
    name: link.name,
    url: link.url,
    status: link.status,
});

const submit = () => {
    form.put(route('admin.links.update', link.id));
};
</script>
