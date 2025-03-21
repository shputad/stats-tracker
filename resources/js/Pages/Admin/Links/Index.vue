<template>
    <Head title="Links" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0 text-gray-800">Manage Links</h1>
                <Link :href="route('admin.links.create')"
                      class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition">
                    + Create Link
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">URL</th>
                            <th class="p-3 text-center">Type</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="link in links" :key="link.id" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800">{{ link.name }}</td>
                            <td class="p-3">
                                <a :href="link.url" target="_blank"
                                   class="text-blue-600 hover:underline break-words block max-w-[400px] truncate"
                                   :title="link.url">
                                   {{ link.url }}
                                </a>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded">
                                    {{ link.type.charAt(0).toUpperCase() + link.type.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-bold',
                                    link.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                ]">
                                    {{ link.status.charAt(0).toUpperCase() + link.status.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center flex justify-center space-x-3">
                                <Link :href="route('admin.links.stats.index', link.id)"
                                      class="text-purple-600 hover:text-purple-800" title="View Stats">
                                    <i class="fas fa-chart-simple"></i>
                                </Link>
                                <Link :href="route('admin.links.edit', link.id)"
                                      class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </Link>
                                <button @click="deleteLink(link.id)"
                                        class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="links.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-500">No links found.</td>
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