<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Dashboard Title -->
            <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 shadow rounded">
                    <h3 class="text-lg font-semibold">Network Channels</h3>
                    <p class="text-2xl font-bold">{{ channelsCount }}</p>
                </div>
                <div class="bg-white p-4 shadow rounded">
                    <h3 class="text-lg font-semibold">Links</h3>
                    <p class="text-2xl font-bold">{{ linksCount }}</p>
                </div>
                <div class="bg-white p-4 shadow rounded">
                    <h3 class="text-lg font-semibold">Network Profiles</h3>
                    <p class="text-2xl font-bold">{{ profilesCount }}</p>
                </div>
                <div class="bg-white p-4 shadow rounded">
                    <h3 class="text-lg font-semibold">Users</h3>
                    <p class="text-2xl font-bold">{{ usersCount }}</p>
                </div>
            </div>

            <!-- Recent Links Table -->
            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4">Recent Links</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 p-2 text-left">Name</th>
                                <th class="border border-gray-300 p-2 text-left">URL</th>
                                <th class="border border-gray-300 p-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="link in recentLinks" :key="link.id" class="hover:bg-gray-50">
                                <td class="border border-gray-300 p-2">{{ link.name }}</td>
                                <td class="border border-gray-300 p-2">
                                    <a :href="link.url" target="_blank" class="text-blue-600 underline truncate block"
                                        :title="link.url"
                                        style="max-width: 400px; white-space: break-spaces; text-overflow: ellipsis; word-break: break-word;">
                                        {{ link.url }}
                                    </a>
                                </td>
                                <td class="border border-gray-300 p-2 text-center">
                                    <span
                                        :class="`px-2 py-1 rounded text-xs text-white font-bold ${link.status === 'active' ? 'bg-green-600' : 'bg-red-600'}`">
                                        {{ String(link.status).charAt(0).toUpperCase() + String(link.status).slice(1) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Profiles Table -->
            <div class="mt-6">
                <h2 class="text-xl font-bold mb-4">Recent Profiles</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 p-2 text-left">Channel</th>
                                <th class="border border-gray-300 p-2 text-left">Account ID</th>
                                <th class="border border-gray-300 p-2 text-left">Link</th>
                                <th class="border border-gray-300 p-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="profile in recentProfiles" :key="profile.id" class="hover:bg-gray-50">
                                <td class="border border-gray-300 p-2">{{ profile.network_channel.name }}</td>
                                <td class="border border-gray-300 p-2">{{ profile.account_id }}</td>
                                <td class="border border-gray-300 p-2">{{ profile.link.name }}</td>
                                <td class="border border-gray-300 p-2 text-center">
                                    <span
                                        :class="`px-2 py-1 rounded text-xs text-white font-bold ${profile.status === 'active' ? 'bg-green-600' : 'bg-red-600'}`">
                                        {{ String(profile.status).charAt(0).toUpperCase() + String(profile.status).slice(1)
                                        }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    channelsCount: Number,
    linksCount: Number,
    profilesCount: Number,
    usersCount: Number,
    recentLinks: Array,
    recentProfiles: Array,
});
</script>