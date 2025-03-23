<template>

    <Head title="Daily Summary" />

    <AuthenticatedLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        Daily Summary
                    </h1>
                    <p v-if="remainingTime > 0" class="text-sm text-gray-500 mt-1">
                        Next auto-refresh in: <span class="font-semibold">{{ formattedRemainingTime }}</span>
                    </p>
                </div>
                <button @click="refreshSummary"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-md shadow hover:bg-green-700 transition mt-4 sm:mt-0">
                    <i class="fas fa-sync-alt" :class="{ 'animate-spin': isRefreshing }"></i>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Opening</th>
                            <th class="p-3 text-left">Topups</th>
                            <th class="p-3 text-left">Closing</th>
                            <th class="p-3 text-left">Spending</th>
                            <th class="p-3 text-left">Stats</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in summary" :key="row.date" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800 whitespace-nowrap">{{ row.date }}</td>
                            <td class="p-3 text-gray-700">{{ formatDecimal(row.opening_balance) }}</td>
                            <td class="p-3 text-blue-600 font-medium">{{ formatDecimal(row.topup_today) }}</td>
                            <td class="p-3 text-gray-700">{{ formatDecimal(row.closing_balance) }}</td>
                            <td class="p-3 text-red-600 font-semibold">{{ computeSpending(row) }}</td>
                            <td class="p-3 text-indigo-600 font-semibold">{{ row.total_logs }}</td>
                        </tr>
                        <tr v-if="summary.length === 0">
                            <td colspan="6" class="p-4 text-center text-gray-500">No records available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const { props } = usePage();
const summary = ref(props.summary || []);
const isRefreshing = ref(false);

const updateIntervalMinutes = parseFloat(props.settings?.profile_stats_update_interval || 10);
const updateIntervalMs = updateIntervalMinutes * 60000;

const nextRefreshTime = ref(null);
const remainingTime = ref(0);

const formatDecimal = (val) => val !== null ? parseFloat(val).toFixed(2) : '0.00';

const computeSpending = (row) => {
    const open = parseFloat(row.opening_balance || 0);
    const topup = parseFloat(row.topup_today || 0);
    const close = parseFloat(row.closing_balance || 0);
    return (open + topup - close).toFixed(2);
};

const formattedRemainingTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60000);
    const seconds = Math.floor((remainingTime.value % 60000) / 1000);
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const refreshSummary = () => {
    isRefreshing.value = true;
    router.visit(window.location.pathname + '?refresh=' + Date.now(), {
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            summary.value = page.props.summary || [];
            isRefreshing.value = false;
        },
    });
};

const computeFirstRefreshDelay = () => {
    const now = new Date();
    const minutes = now.getMinutes();
    const remainder = minutes % updateIntervalMinutes;
    const nextFull = new Date(now);
    if (remainder === 0) {
        nextFull.setSeconds(0, 0);
    } else {
        nextFull.setMinutes(minutes + (updateIntervalMinutes - remainder), 0, 0);
    }
    return nextFull.getTime() + 60000 - now.getTime(); // +1 min offset
};

const updateRemainingTime = () => {
    if (nextRefreshTime.value) {
        remainingTime.value = nextRefreshTime.value - Date.now();
        if (remainingTime.value < 0) remainingTime.value = 0;
    }
};

let initialTimeout, refreshInterval, remainingTimer;
onMounted(() => {
    const firstDelay = computeFirstRefreshDelay();
    nextRefreshTime.value = new Date(Date.now() + firstDelay);
    updateRemainingTime();
    remainingTimer = setInterval(updateRemainingTime, 1000);

    initialTimeout = setTimeout(() => {
        refreshSummary();
        nextRefreshTime.value = new Date(Date.now() + updateIntervalMs);
        refreshInterval = setInterval(() => {
            refreshSummary();
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
