<template>
    <Head title="Network Channels" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Network Channels</h1>
        <Link :href="route('admin.network-channels.create')" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create Channel</Link>
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Name</th>
                    <th class="border border-gray-300 p-2">Website</th>
                    <th class="border border-gray-300 p-2">Has API</th>
                    <th class="border border-gray-300 p-2">Status</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="channel in channels" :key="channel.id">
                    <td class="border border-gray-300 p-2">{{ channel.name }}</td>
                    <td class="border border-gray-300 p-2">
                        <a :href="channel.website" target="_blank" class="text-blue-600 underline">{{ channel.website }}</a>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">{{ channel.has_api ? 'Yes' : 'No' }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${channel.status == 'active' ? 'bg-green-600' : 'bg-red-600'}`">{{ String(channel.status).charAt(0).toUpperCase() + String(channel.status).slice(1) }}</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <Link :href="route('admin.network-channels.edit', channel.id)" class="text-blue-600 underline">
                            <i class="fas fa-pencil text-sm"></i>
                        </Link>
                        <button @click="deleteChannel(channel.id)" class="text-red-600 underline ml-3">
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
const channels = ref(props.channels);

const deleteChannel = async (id) => {
    if (confirm('Are you sure you want to delete this channel?')) {
        await router.delete(route('admin.network-channels.destroy', id), {
            onSuccess: () => {
                channels.value = channels.value.filter(channel => channel.id !== id);
            },
        });
    }
};
</script>
