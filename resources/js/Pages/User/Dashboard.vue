<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="mx-auto">
            <!-- Dashboard Title -->
            <h1 class="text-2xl font-bold mb-6">Welcome to Your Dashboard</h1>

            <!-- Grid Layout -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Total Links -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Your Links</h2>
                    <p class="text-3xl font-bold text-blue-600">{{ links.length }}</p>
                    <p class="text-gray-600 text-sm mt-1">These are linked via your network profiles</p>
                </div>

                <!-- Total Network Profiles -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Network Profiles</h2>
                    <p class="text-3xl font-bold text-green-600">{{ profiles.length }}</p>
                    <p class="text-gray-600 text-sm mt-1">Accounts you've connected</p>
                </div>

                <!-- Today's Logs -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h2 class="text-lg font-semibold mb-2">Today's Logs</h2>
                    <p class="text-3xl font-bold text-green-600">{{ todayLogsDisplay }}</p>
                    <p class="text-gray-600 text-sm mt-1">Increase in logs since midnight</p>
                </div>
            </div>

            <!-- Recent Stats Table -->
            <div class="mt-10">
                <h2 class="text-xl font-semibold mb-4">Recent Stats</h2>
                <div class="overflow-x-auto bg-white rounded-lg shadow">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="text-left p-3 border-b">Time</th>
                                <th class="text-left p-3 border-b">Link</th>
                                <th class="text-left p-3 border-b">Logs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stat in recentStats" :key="stat.id" class="hover:bg-gray-50">
                                <td class="p-3 border-b">{{ new Date(stat.created_at).toLocaleString() }}</td>
                                <td class="p-3 border-b">{{ stat.link_name }}</td>
                                <td class="p-3 border-b font-semibold text-blue-700">{{ stat.log }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p v-if="recentStats.length === 0" class="text-gray-500 p-4">No stats available yet.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const links = props.links || [];
const profiles = props.profiles || [];
const recentStats = props.recentStats || [];

const today = new Date();
const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate());

const todayStats = recentStats
    .filter(stat => new Date(stat.created_at) >= startOfToday)
    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

let todayLogs = 0;

if (todayStats.length >= 2) {
    const firstLog = parseInt(todayStats[0].log);
    const lastLog = parseInt(todayStats[todayStats.length - 1].log);
    todayLogs = lastLog - firstLog;
}

const todayLogsDisplay = todayStats.length < 2 ? 'N/A' : todayLogs;
</script>
