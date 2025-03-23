<template>

    <Head :title="`Daily Stats – ${profile.account_id} – ${profile.network_channel?.name || 'Unknown'}`" />

    <AdminLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <GoBack :href="`/admin/network-profiles`" /> Daily Stats for "{{ profile.account_id }}" – {{ profile.network_channel?.name || 'Unknown' }}
                    </h1>
                    <p v-if="remainingTime > 0" class="text-sm text-gray-500 mt-1">
                        Next auto-refresh in: <span class="font-semibold">{{ formattedRemainingTime }}</span>
                    </p>
                </div>
                <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                    <Link :href="route('admin.network-profiles.snapshots.index', profile.id)"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md shadow hover:bg-indigo-700 transition"
                        title="View Detailed Snapshots">
                        <i class="fas fa-list-alt mr-1"></i> Snapshots
                    </Link>
                    <button @click="refreshStats"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-md shadow hover:bg-green-700 transition"
                        title="Refresh Table">
                        <i class="fas fa-sync-alt" :class="{ 'animate-spin': isRefreshing }"></i>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Opening</th>
                            <th class="p-3 text-left">Topups</th>
                            <th class="p-3 text-left">Closing</th>
                            <th class="p-3 text-left">Spending</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="stat in stats" :key="stat.id" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800 whitespace-nowrap">
                                {{ stat.date }}
                            </td>
                            <td class="p-3 text-gray-700">{{ formatDecimal(stat.opening_balance) }}</td>
                            <td class="p-3 text-blue-600 font-medium">{{ formatDecimal(stat.topup_today) }}</td>
                            <td class="p-3 text-gray-700">{{ formatDecimal(stat.closing_balance ?? stat.current_balance)
                                }}</td>
                            <td class="p-3 text-red-600 font-semibold">{{ spendingByDate[stat.date] }}</td>
                        </tr>
                        <tr v-if="stats.length === 0">
                            <td colspan="5" class="p-4 text-center text-gray-500">No stats available yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';

const { props } = usePage();
const profile = props.profile;
const stats = ref(Array.isArray(props.stats) ? props.stats : Object.values(props.stats ?? {}));
const isRefreshing = ref(false);

const updateIntervalMinutes = parseFloat(props.settings?.profile_stats_update_interval || 10);
const updateIntervalMs = updateIntervalMinutes * 60000;

const nextRefreshTime = ref(null);
const remainingTime = ref(0);

const formatDecimal = (val) => val !== null ? parseFloat(val).toFixed(2) : '0.00';

const spendingByDate = computed(() => {
    const result = {};
    for (const stat of (stats.value || [])) {
        if (!stat?.date) continue;
        const opening = parseFloat(stat.opening_balance || 0);
        const topup = parseFloat(stat.topup_today || 0);
        const closing = parseFloat(stat.closing_balance ?? stat.current_balance ?? 0);
        result[stat.date] = (opening + topup - closing).toFixed(2);
    }
    return result;
});

const refreshStats = () => {
    isRefreshing.value = true;
    router.visit(window.location.pathname + '?refresh=' + Date.now(), {
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            stats.value = page.props.stats;
            isRefreshing.value = false;
        },
    });
};

const formattedRemainingTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60000);
    const seconds = Math.floor((remainingTime.value % 60000) / 1000);
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

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
    return nextFull.getTime() + 60000 - now.getTime(); // add 1 min offset
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
