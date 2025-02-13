<template>
    <Head title="Network Channels" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Responsive Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0">Network Channels</h1>
                <Link :href="route('admin.network-channels.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create Channel
                </Link>
            </div>

            <!-- Table Container for Responsive Scrolling -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left">Name</th>
                            <th class="border border-gray-300 p-2 text-left">Website</th>
                            <th class="border border-gray-300 p-2 text-center">Has API</th>
                            <th class="border border-gray-300 p-2 text-center">Status</th>
                            <th class="border border-gray-300 p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="channel in channels" :key="channel.id" class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2">{{ channel.name }}</td>
                            <td class="border border-gray-300 p-2">
                                <a :href="channel.website" target="_blank"
                                    class="text-blue-600 underline truncate block" :title="channel.website"
                                    style="max-width: 400px; white-space: break-spaces; text-overflow: ellipsis; word-break: break-word;">
                                    {{ channel.website }}
                                </a>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                {{ channel.has_api ? 'Yes' : 'No' }}
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${channel.status === 'active' ? 'bg-green-600' : 'bg-red-600'
                                    }`">
                                    {{ String(channel.status).charAt(0).toUpperCase() + String(channel.status).slice(1)
                                    }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <Link :href="route('admin.network-channels.edit', channel.id)"
                                    class="text-blue-600 hover:underline">
                                <i class="fas fa-pencil text-sm"></i>
                                </Link>
                                <button @click="deleteChannel(channel.id)" class="text-red-600 hover:underline ml-3">
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
const channels = ref(props.channels);

const deleteChannel = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this channel?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb', // Tailwind blue-600
        cancelButtonColor: '#6b7280',  // Tailwind gray-600
    });

    if (result.isConfirmed) {
        await router.delete(route('admin.network-channels.destroy', id), {
            onSuccess: () => {
                channels.value = channels.value.filter(channel => channel.id !== id);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The channel has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#2563eb',
                });
            },
        });
    }
};
</script>