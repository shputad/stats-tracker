<template>
    <Head title="Link Stats" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Stats for {{ link.name }}</h1>

        <!-- Grouped Stats by Date -->
        <div v-for="(statsGroup, date) in groupedStats" :key="date" class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Date: {{ date }}</h2>
            
            <!-- Log Table -->
            <table class="min-w-full border-collapse border border-gray-200">
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
                    <tr v-for="(stat, index) in statsGroup" :key="stat.id">
                        <td class="border border-gray-300 p-2">{{ new Date(stat.created_at).toLocaleTimeString() }}</td>
                        <td class="border border-gray-300 p-2">{{ stat.log }}</td>
                        <td class="border border-gray-300 p-2">
                            <span>
                                {{ stat.last_10_minutes_diff }}
                                <span :class="getChangeClass(stat.last_10_minutes_diff, index, 'last_10_minutes_diff')"
                                    v-html="getChangeArrow(stat.last_10_minutes_diff, index, 'last_10_minutes_diff')"></span>
                            </span>
                        </td>
                        <td class="border border-gray-300 p-2">{{ stat.last_hour_diff !== null ? stat.last_hour_diff : '' }}</td>
                        <td class="border border-gray-300 p-2">{{ stat.today_diff }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;
const stats = props.stats;

/**
 * Group stats by date
 */
const groupedStats = stats.reduce((acc, stat) => {
    const date = new Date(stat.created_at).toLocaleDateString();
    if (!acc[date]) acc[date] = [];
    acc[date].push(stat);
    return acc;
}, {});

/**
 * Get the CSS class based on the increase or decrease in the last 10 minutes count.
 */
const getChangeClass = (currentValue, index, key) => {
    const previousValue = (index + 1) < stats.length ? stats[index + 1][key] : 0;

    if (currentValue > previousValue) {
        return 'text-green-600 font-bold';
    } else if (currentValue < previousValue) {
        return 'text-red-600 font-bold';
    }

    return '';
};

/**
 * Get the arrow icon for the change.
 */
const getChangeArrow = (currentValue, index, key) => {
    const previousValue = (index + 1) < stats.length ? stats[index + 1][key] : 0;
    const percentChange = previousValue ? ((currentValue - previousValue) / previousValue * 100).toFixed(0) : (currentValue ? 100 : 0);

    if (currentValue > previousValue) {
        return '<i class="fas fa-arrow-up"></i> ' + percentChange + '%';
    } else if (currentValue < previousValue) {
        return '<i class="fas fa-arrow-down"></i> ' + percentChange + '%';
    }

    return '';
};
</script>
