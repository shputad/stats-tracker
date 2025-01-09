<template>
    <Head title="Network Profiles" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Network Profiles</h1>
        <Link :href="route('admin.network-profiles.create')" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create Profile</Link>
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">User</th>
                    <th class="border border-gray-300 p-2">Channel</th>
                    <th class="border border-gray-300 p-2">Link</th>
                    <th class="border border-gray-300 p-2">Account ID</th>
                    <th class="border border-gray-300 p-2">API Key</th>
                    <th class="border border-gray-300 p-2">Status</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="profile in profiles" :key="profile.id">
                    <td class="border border-gray-300 p-2">{{ profile.user.name }}</td>
                    <td class="border border-gray-300 p-2">{{ profile.network_channel.name }}</td>
                    <td class="border border-gray-300 p-2">{{ profile.link.name }}</td>
                    <td class="border border-gray-300 p-2">{{ profile.account_id }}</td>
                    <td class="border border-gray-300 p-2">{{ profile.api_key }}</td>
                    <td class="border border-gray-300 p-2 text-center">
                        <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${profile.status == 'active' ? 'bg-green-600' : 'bg-red-600'}`">{{ String(profile.status).charAt(0).toUpperCase() + String(profile.status).slice(1) }}</span>
                    </td>
                    <td class="border border-gray-300 p-2 text-center">
                        <Link :href="route('admin.network-profiles.edit', profile.id)" class="text-blue-600 underline">
                            <i class="fas fa-pencil text-sm"></i>
                        </Link>
                        <button @click="deleteProfile(profile.id)" class="text-red-600 underline ml-2">
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
const profiles = ref(props.profiles);

const deleteProfile = async (id) => {
    if (confirm('Are you sure you want to delete this profile?')) {
        await router.delete(route('admin.network-profiles.destroy', id), {
            onSuccess: () => {
                profiles.value = profiles.value.filter(profile => profile.id !== id);
            },
        });
    }
};
</script>
