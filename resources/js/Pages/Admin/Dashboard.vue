<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Dashboard Title -->
            <h1 class="text-2xl font-bold mb-6">Welcome to Admin Dashboard</h1>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Network Channels -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Network Channels</h2>
                    <p class="text-3xl font-bold text-blue-600">{{ channelsCount }}</p>
                    <p class="text-gray-600 text-sm mt-1">Channels configured in system</p>
                </div>

                <!-- Links -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Links</h2>
                    <p class="text-3xl font-bold text-green-600">{{ linksCount }}</p>
                    <p class="text-gray-600 text-sm mt-1">Available tracked links</p>
                </div>

                <!-- Network Profiles -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Network Profiles</h2>
                    <p class="text-3xl font-bold text-yellow-600">{{ profilesCount }}</p>
                    <p class="text-gray-600 text-sm mt-1">User network profiles</p>
                </div>

                <!-- Users -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Users</h2>
                    <p class="text-3xl font-bold text-purple-600">{{ usersCount }}</p>
                    <p class="text-gray-600 text-sm mt-1">Registered users</p>
                </div>
            </div>

            <!-- Recent Links Table -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold mb-4">Recent Links</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-3 border-b">Name</th>
                                <th class="text-left p-3 border-b">URL</th>
                                <th class="text-center p-3 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="link in recentLinks" :key="link.id" class="hover:bg-gray-50">
                                <td class="p-3 border-b">{{ link.name }}</td>
                                <td class="p-3 border-b">
                                    <a :href="link.url" target="_blank"
                                    class="text-blue-600 hover:underline break-words block max-w-[400px] truncate"
                                    :title="link.url">
                                        {{ link.url }}
                                    </a>
                                </td>
                                <td class="p-3 border-b text-center">
                                    <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${link.status === 'active' ? 'bg-green-600' : 'bg-red-600'}`">
                                        {{ String(link.status).charAt(0).toUpperCase() + String(link.status).slice(1) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="recentLinks.length === 0">
                                <td colspan="3" class="p-4 text-center text-gray-500">No recent links found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Profiles Table -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold mb-4">Recent Profiles</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-3 border-b">Channel</th>
                                <th class="text-left p-3 border-b">Account ID</th>
                                <th class="text-left p-3 border-b">Link</th>
                                <th class="text-center p-3 border-b">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="profile in recentProfiles" :key="profile.id" class="hover:bg-gray-50">
                                <td class="p-3 border-b">{{ profile.network_channel.name }}</td>
                                <td class="p-3 border-b">{{ profile.account_id }}</td>
                                <td class="p-3 border-b">{{ profile.link?.name ?? '—' }}</td>
                                <td class="p-3 border-b text-center">
                                    <span :class="`px-2 py-1 rounded text-xs text-white font-bold ${profile.status === 'active' ? 'bg-green-600' : 'bg-red-600'}`">
                                        {{ String(profile.status).charAt(0).toUpperCase() + String(profile.status).slice(1) }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="recentProfiles.length === 0">
                                <td colspan="4" class="p-4 text-center text-gray-500">No recent profiles found.</td>
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