<template>
    <Head title="Link Stats" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Responsive Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold mb-4 sm:mb-0">
                        <GoBack :href="`/admin/links`" /> Stats for "{{ link.name }}" link
                    </h1>
                    <p v-if="remainingTime > 0" class="text-sm text-gray-600">
                        Next auto-refresh in: {{ formattedRemainingTime }}
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.links.stats.create', link.id)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Stats
                    </Link>
                    <button @click="refreshStats"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" title="Refresh Table">
                        <i class="fas fa-sync-alt" :class="{ 'animate-spin': isRefreshing }"></i>
                    </button>
                </div>
            </div>

            <!-- No stats message -->
            <div v-if="Object.keys(groupedStats).length === 0" class="text-center text-gray-500 p-4">
                No stats found.
            </div>

            <!-- Grouped Stats by Date -->
            <div v-for="(statsGroup, date) in groupedStats" :key="date" class="mt-4 mb-8">
                <h2 class="text-xl font-semibold mb-4">{{ date }}</h2>
                <!-- Responsive Table Wrapper -->
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse border border-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 p-2 text-left">Time</th>
                                <th class="border border-gray-300 p-2 text-left">Logs</th>
                                <th class="border border-gray-300 p-2 text-left">Last 10 minutes</th>
                                <th class="border border-gray-300 p-2 text-left">Last hour</th>
                                <th class="border border-gray-300 p-2 text-left">Today</th>
                                <th class="border border-gray-300 p-2 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(stat, index) in statsGroup" :key="stat.id" class="hover:bg-gray-50">
                                <td class="border border-gray-300 p-2 whitespace-nowrap">
                                    {{ formatTime(stat.created_at) }}
                                </td>
                                <td class="border border-gray-300 p-2 whitespace-nowrap">
                                    {{ stat.log }}
                                </td>
                                <td class="border border-gray-300 p-2 whitespace-nowrap">
                                    <span>
                                        {{ stat.last_10_minutes_diff }}
                                        <span
                                            :class="getChangeClass(stat.last_10_minutes_diff, index, 'last_10_minutes_diff', statsGroup)"
                                            v-html="getChangeArrow(stat.last_10_minutes_diff, index, 'last_10_minutes_diff', statsGroup)"></span>
                                    </span>
                                </td>
                                <td class="border border-gray-300 p-2 whitespace-nowrap">
                                    <span v-if="stat.last_hour_diff !== null">{{ stat.last_hour_diff }}</span>
                                </td>
                                <td class="border border-gray-300 p-2 whitespace-nowrap">{{ stat.today_diff }}</td>
                                <td class="border border-gray-300 p-2 text-center whitespace-nowrap">
                                    <Link :href="route('admin.links.stats.edit', { link: link.id, stat: stat.id })"
                                        class="text-blue-600 hover:underline inline-block">
                                    <i class="fas fa-pencil-alt text-sm"></i>
                                    </Link>
                                    <button @click="deleteStat(stat.id)"
                                        class="text-red-600 hover:underline inline-block ml-2">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Responsive Pagination -->
            <div v-if="stats.links && stats.links.length > 3" class="flex justify-center mt-8">
                <button v-for="(paginationLink, index) in stats.links" :key="index" :class="[
                    'mx-1 px-4 py-2 rounded border transition-colors duration-300',
                    paginationLink.active
                        ? 'bg-orange-400 text-white border-orange-400'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100',
                    !paginationLink.url ? 'opacity-50 cursor-not-allowed' : ''
                ]" :disabled="!paginationLink.url" @click="router.get(paginationLink.url)"
                    v-html="paginationLink.label"></button>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const { props } = usePage();
const stats = ref(props.stats);
const link = props.link;
const isRefreshing = ref(false);
const nextRefreshTime = ref(null);
const remainingTime = ref(0);

// Format time for display
const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

// Format remaining time as mm:ss
const formattedRemainingTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60000);
    const seconds = Math.floor((remainingTime.value % 60000) / 1000);
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// Group stats by date (using locale date string)
const groupedStats = computed(() => {
    if (!stats.value.data) return {};
    return stats.value.data.reduce((acc, stat) => {
        const date = new Date(stat.created_at).toLocaleDateString();
        if (!acc[date]) acc[date] = [];
        acc[date].push(stat);
        return acc;
    }, {});
});

// Functions for arrow icons and percentage for "Last 10 minutes"
const getChangeClass = (currentValue, rowIndex, key, group) => {
    const previousValue = rowIndex + 1 < group.length ? group[rowIndex + 1][key] : 0;
    if (currentValue > previousValue) return 'text-green-600 font-bold';
    if (currentValue < previousValue) return 'text-red-600 font-bold';
    return '';
};

const getChangeArrow = (currentValue, rowIndex, key, group) => {
    const previousValue = rowIndex + 1 < group.length ? group[rowIndex + 1][key] : 0;
    const percentChange = previousValue
        ? ((currentValue - previousValue) / previousValue * 100).toFixed(0)
        : (currentValue ? 100 : 0);
    if (currentValue > previousValue) {
        return '<i class="fas fa-arrow-up"></i> ' + percentChange + '%';
    } else if (currentValue < previousValue) {
        return '<i class="fas fa-arrow-down"></i> ' + percentChange + '%';
    }
    return '';
};

// Delete stat using SweetAlert2 for confirmation
const deleteStat = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this stat?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb', // Tailwind blue-600
        cancelButtonColor: '#6b7280',  // Tailwind gray-600
    });
    if (result.isConfirmed) {
        await router.delete(route('admin.links.stats.destroy', { link: link.id, stat: id }), {
            onSuccess: () => {
                stats.value.data = stats.value.data.filter((stat) => stat.id !== id);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The stat has been deleted.',
                    icon: 'success',
                    confirmButtonColor: '#2563eb',
                });
            },
        });
    }
};

// Dynamic auto-refresh using the setting: link_stats_update_interval (in minutes)
// Add 1 minute offset to ensure backend updates are available
const updateIntervalMinutes = parseFloat(props.settings?.link_stats_update_interval) || 10;
const updateIntervalMs = updateIntervalMinutes * 60000;

// Compute delay until next refresh: next full multiple of updateIntervalMinutes + 1 minute
const computeFirstRefreshDelay = () => {
    const now = new Date();
    const minutes = now.getMinutes();
    const remainder = minutes % updateIntervalMinutes;
    let nextFull = new Date(now);
    if (remainder === 0) {
        nextFull.setSeconds(0, 0);
    } else {
        nextFull.setMinutes(minutes + (updateIntervalMinutes - remainder), 0, 0);
    }
    // Add 1 minute offset
    const firstRefreshTime = new Date(nextFull.getTime() + 60000);
    return firstRefreshTime.getTime() - now.getTime();
};

const refreshStats = () => {
    isRefreshing.value = true;
    router.visit(window.location.pathname + '?refresh=' + Date.now(), {
        preserveScroll: true,
        replace: true,
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

const updateRemainingTime = () => {
    if (nextRefreshTime.value) {
        remainingTime.value = nextRefreshTime.value - Date.now();
        if (remainingTime.value < 0) remainingTime.value = 0;
    }
};

let refreshInterval;
let initialTimeout;
let remainingTimer;
onMounted(() => {
    const firstDelay = computeFirstRefreshDelay();
    nextRefreshTime.value = new Date(Date.now() + firstDelay);
    updateRemainingTime();
    remainingTimer = setInterval(updateRemainingTime, 1000);

    initialTimeout = setTimeout(() => {
        refreshStats();
        nextRefreshTime.value = new Date(Date.now() + updateIntervalMs);
        refreshInterval = setInterval(() => {
            refreshStats();
            nextRefreshTime.value = new Date(Date.now() + updateIntervalMs);
        }, updateIntervalMs);
    }, firstDelay);
});
onUnmounted(() => {
    clearTimeout(initialTimeout);
    clearInterval(refreshInterval);
    clearInterval(remainingTimer);
});
</script>