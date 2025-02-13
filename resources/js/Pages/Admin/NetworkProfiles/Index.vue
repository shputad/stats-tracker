<template>
    <Head title="Network Profiles" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Responsive Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold mb-4 sm:mb-0">Network Profiles</h1>
                <Link :href="route('admin.network-profiles.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Create Profile
                </Link>
            </div>

            <!-- Table Container for Responsive Scrolling -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 p-2 text-left">User</th>
                            <th class="border border-gray-300 p-2 text-left">Channel</th>
                            <th class="border border-gray-300 p-2 text-left">Link</th>
                            <th class="border border-gray-300 p-2 text-left">Account ID</th>
                            <th class="border border-gray-300 p-2 text-left">API Key</th>
                            <th class="border border-gray-300 p-2 text-center">Status</th>
                            <th class="border border-gray-300 p-2 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="profile in profiles" :key="profile.id" class="hover:bg-gray-50">
                            <td class="border border-gray-300 p-2">{{ profile.user.name }}</td>
                            <td class="border border-gray-300 p-2">{{ profile.network_channel.name }}</td>
                            <td class="border border-gray-300 p-2">{{ profile.link.name }}</td>
                            <td class="border border-gray-300 p-2">{{ profile.account_id }}</td>
                            <td class="border border-gray-300 p-2">{{ profile.api_key }}</td>
                            <td class="border border-gray-300 p-2 text-center">
                                <span
                                    :class="`px-2 py-1 rounded text-xs text-white font-bold ${profile.status === 'active' ? 'bg-green-600' : 'bg-red-600'}`">
                                    {{ String(profile.status).charAt(0).toUpperCase() + String(profile.status).slice(1)
                                    }}
                                </span>
                            </td>
                            <td class="border border-gray-300 p-2 text-center">
                                <Link :href="route('admin.network-profiles.edit', profile.id)"
                                    class="text-blue-600 hover:underline">
                                <i class="fas fa-pencil text-sm"></i>
                                </Link>
                                <button @click="deleteProfile(profile.id)" class="text-red-600 hover:underline ml-2">
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
const profiles = ref(props.profiles);

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