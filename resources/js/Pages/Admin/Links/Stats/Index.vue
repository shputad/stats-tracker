<template>
    <Head title="Link Stats" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Stats for {{ link.name }}</h1>

        <!-- Log Table -->
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Time</th>
                    <th class="border border-gray-300 p-2">Logs</th>
                    <th class="border border-gray-300 p-2">Last 10 minutes</th>
                    <th class="border border-gray-300 p-2">Last hour</th>
                    <th class="border border-gray-300 p-2">Today</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="stat in stats" :key="stat.id">
                    <td class="border border-gray-300 p-2">{{ new Date(stat.created_at).toLocaleString() }}</td>
                    <td class="border border-gray-300 p-2">{{ stat.log }}</td>
                    <td class="border border-gray-300 p-2">{{ stat.last_10_minutes_diff }}</td>
                    <td class="border border-gray-300 p-2">{{ stat.last_hour_diff !== null ? stat.last_hour_diff : '' }}</td>
                    <td class="border border-gray-300 p-2">{{ stat.today_diff }}</td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;
const stats = props.stats;
</script>
