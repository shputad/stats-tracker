<template>
    <Head title="Links" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Responsive Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0">Links</h1>
                <Link :href="route('admin.links.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create Link
                </Link>
            </div>

            <!-- Table Container for Responsive Scrolling -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left">Name</th>
                            <th class="border border-gray-300 p-2 text-left">URL</th>
                            <th class="border border-gray-300 p-2 text-center">Type</th>
                            <th class="border border-gray-300 p-2 text-center">Status</th>
                            <th class="border border-gray-300 p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="link in links" :key="link.id" class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2">{{ link.name }}</td>
                            <td class="border border-gray-300 p-2">
                                <a :href="link.url" target="_blank" class="text-blue-600 underline truncate block"
                                    :title="link.url"
                                    style="max-width: 400px; white-space: break-spaces; text-overflow: ellipsis; word-break: break-word;">
                                    {{ link.url }}
                                </a>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                {{ link.type.charAt(0).toUpperCase() + link.type.slice(1) }}
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${link.status === 'active' ? 'bg-green-600' : 'bg-red-600'
                                    }`">
                                    {{ String(link.status).charAt(0).toUpperCase() + String(link.status).slice(1) }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <Link :href="route('admin.links.stats.index', link.id)"
                                    class="text-purple-600 hover:underline">
                                <i class="fas fa-chart-simple text-sm"></i>
                                </Link>
                                <Link :href="route('admin.links.edit', link.id)"
                                    class="text-blue-600 hover:underline ml-2">
                                <i class="fas fa-pencil text-sm"></i>
                                </Link>
                                <button @click="deleteLink(link.id)" class="text-red-600 hover:underline ml-2">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const { props } = usePage();
const links = ref(props.links);

const deleteLink = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this link?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb', // Tailwind blue-600
        cancelButtonColor: '#6b7280',  // Tailwind gray-600
    });

    if (result.isConfirmed) {
        await router.delete(route('admin.links.destroy', id), {
            onSuccess: () => {
                links.value = links.value.filter(link => link.id !== id);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The link has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#2563eb',
                });
            },
        });
    }
};
</script>