<template>
    <Head title="Network Channels" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0 text-gray-800">Manage Network Channels</h1>
                <Link :href="route('admin.network-channels.create')"
                      class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition">
                    + Create Channel
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white rounded-lg shadow">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">Website</th>
                            <th class="p-3 text-center">Has API</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="channel in channels" :key="channel.id" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800">{{ channel.name }}</td>
                            <td class="p-3">
                                <a :href="channel.website" target="_blank"
                                   class="text-blue-600 hover:underline break-words block max-w-[400px] truncate"
                                   :title="channel.website">
                                   {{ channel.website }}
                                </a>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded">
                                    {{ channel.has_api ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-bold',
                                    channel.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                ]">
                                    {{ channel.status.charAt(0).toUpperCase() + channel.status.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center flex justify-center space-x-3">
                                <Link :href="route('admin.network-channels.edit', channel.id)"
                                      class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </Link>
                                <button @click="deleteChannel(channel.id)"
                                        class="text-red-600 hover:text-red-800" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="channels.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-500">No network channels found.</td>
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
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
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