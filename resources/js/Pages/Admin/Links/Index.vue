<template>
    <Head title="Links" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Links</h1>
        <Link 
            :href="route('admin.links.create')" 
            class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
            Create Link
        </Link>
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2" style="max-width: 400px; overflow: hidden;">URL</th>
                    <th class="border border-gray-300 p-2">Status</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="link in links" :key="link.id">
                    <td class="border border-gray-300 p-2">{{ link.name }}</td>
                    <td class="border border-gray-300 p-2" style="max-width: 400px; overflow: hidden;">
                        <a 
                            :href="link.url" 
                            target="_blank" 
                            class="text-blue-600 underline truncate block" 
                            :title="link.url"
                            style="max-width:100%; white-space: break-spaces; text-overflow: ellipsis; word-break: break-word;"
                        >
                            {{ link.url }}
                        </a>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${link.status == 'active' ? 'bg-green-600' : 'bg-red-600'}`">{{ String(link.status).charAt(0).toUpperCase() + String(link.status).slice(1) }}</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <Link :href="route('admin.links.stats.index', link.id)" class="text-purple-600 underline">
                            <i class="fas fa-chart-simple text-sm"></i>
                        </Link>
                        <Link :href="route('admin.links.edit', link.id)" class="text-blue-600 underline ml-2">
                            <i class="fas fa-pencil text-sm"></i>
                        </Link>
                        <button @click="deleteLink(link.id)" class="text-red-600 underline ml-2">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

const { props } = usePage();
const links = ref(props.links);

const deleteLink = async (id) => {
    if (confirm('Are you sure you want to delete this link?')) {
        await router.delete(route('admin.links.destroy', id), {
            onSuccess: () => {
                links.value = links.value.filter(link => link.id !== id);
            },
        });
    }
};
</script>
