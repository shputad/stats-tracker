<template>
    <Head title="Network Profiles" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-4 sm:mb-0">All Network Profiles</h1>
                <Link
                    :href="route('admin.network-profiles.create')"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition"
                >
                    + Create Profile
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
                        <tr>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">Channel</th>
                            <th class="p-3 text-left">Link</th>
                            <th class="p-3 text-left">Account ID</th>
                            <th class="p-3 text-left">API Username</th>
                            <th class="p-3 text-left">API Key</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="profile in profiles"
                            :key="profile.id"
                            class="border-t hover:bg-gray-50 transition"
                        >
                            <td class="p-3 font-medium text-gray-800">{{ profile.user.name }}</td>
                            <td class="p-3 text-gray-700">{{ profile.network_channel.name }}</td>
                            <td class="p-3 text-gray-700">
                                {{ profile.link ? profile.link.name : '—' }}
                            </td>
                            <td class="p-3 text-gray-700">{{ profile.account_id }}</td>
                            <td class="p-3 text-gray-700 max-w-[220px] truncate">
                                <span v-if="profile.api_username" class="truncate inline-block max-w-[180px]" :title="profile.api_username">
                                    {{ profile.api_username }}
                                </span>
                                <span v-else class="text-gray-400 italic">—</span>
                            </td>
                            <td class="p-3 text-gray-700 max-w-[220px] truncate relative group">
                                <div v-if="profile.api_key" class="flex items-center space-x-2">
                                    <span
                                        class="truncate max-w-[180px] inline-block"
                                        :title="profile.api_key"
                                        style="white-space: nowrap"
                                    >
                                        {{ profile.api_key }}
                                    </span>
                                    <button
                                        @click="copyToClipboard(profile.api_key)"
                                        class="text-gray-400 hover:text-gray-600"
                                        title="Copy API Key"
                                    >
                                        <i class="fas fa-copy text-sm"></i>
                                    </button>
                                </div>
                                <span v-else class="text-gray-400 italic">—</span>
                            </td>
                            <td class="p-3 text-center">
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-bold text-white',
                                        profile.status === 'active' ? 'bg-green-600' : 'bg-red-600'
                                    ]"
                                >
                                    {{ String(profile.status).charAt(0).toUpperCase() + profile.status.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center flex justify-center space-x-3">
                                <Link :href="route('admin.network-profiles.stats.index', profile.id)"
                                    class="text-purple-600 hover:text-purple-800" title="View Stats">
                                    <i class="fas fa-chart-simple"></i>
                                </Link>
                                <Link :href="route('admin.network-profiles.snapshots.index', profile.id)"
                                    class="text-green-600 hover:text-green-800" title="View Snapshots">
                                    <i class="fas fa-camera-retro"></i>
                                </Link>
                                <Link
                                    :href="route('admin.network-profiles.edit', profile.id)"
                                    class="text-blue-600 hover:text-blue-800 transition"
                                    title="Edit"
                                >
                                    <i class="fas fa-pencil-alt"></i>
                                </Link>
                                <button
                                    @click="deleteProfile(profile.id)"
                                    class="text-red-600 hover:text-red-800 ml-3 transition"
                                    title="Delete"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="profiles.length === 0">
                            <td colspan="7" class="text-center text-gray-500 p-4">No network profiles found.</td>
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
const profiles = ref(props.profiles);

const copyToClipboard = (text) => {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'API key copied!',
            showConfirmButton: false,
            timer: 1500,
        });
    }).catch(() => {
        Swal.fire('Oops', 'Failed to copy API key.', 'error');
    });
};

const deleteProfile = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this profile?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb', // Tailwind blue-600
        cancelButtonColor: '#6b7280',  // Tailwind gray-600
    });

    if (result.isConfirmed) {
        await router.delete(route('admin.network-profiles.destroy', id), {
            onSuccess: () => {
                profiles.value = profiles.value.filter(profile => profile.id !== id);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The profile has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#2563eb',
                });
            },
        });
    }
};
</script>