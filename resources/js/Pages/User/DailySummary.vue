<template>

    <Head title="Daily Summary" />

    <AuthenticatedLayout>
        <div class="mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daily Summary</h1>
                    <p v-if="remainingTime > 0" class="text-sm text-gray-500 mt-1">
                        Next auto-refresh in: <span class="font-semibold">{{ formattedRemainingTime }}</span>
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                    <button @click="refreshSummary"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-md shadow hover:bg-green-700 transition mt-2 sm:mt-6">
                        <i class="fas fa-sync-alt" :class="{ 'animate-spin': isRefreshing }"></i>
                    </button>
                </div>
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
                        <template v-for="row in summary" :key="row.date">
                            <tr class="border-t hover:bg-gray-50 transition cursor-pointer"
                                @click="toggleExpand(row.date)">
                                <td class="p-3 font-medium text-gray-800 whitespace-nowrap">
                                    {{ row.date }}
                                    <i v-if="row.date === today && row.oldest_update_ago && row.spending_interval"
                                        class="fas fa-info-circle text-gray-400 ml-1"
                                        :title="`Spent $${formatDecimal(row.last_spending)} in ${row.spending_interval}. Oldest update: ${row.oldest_update_ago}`">
                                    </i>
                                    <i :class="[
                                        'fas ml-2 transition-transform duration-300',
                                        expandedDates.includes(row.date) ? 'fa-chevron-up rotate-180' : 'fa-chevron-down'
                                    ]"></i>
                                </td>
                                <td class="p-3 text-gray-700">{{ formatDecimal(row.opening_balance) }}</td>
                                <td class="p-3 text-blue-600 font-medium">{{ formatDecimal(row.topup_today) }}</td>
                                <td class="p-3 text-gray-700">{{ formatDecimal(row.closing_balance) }}</td>
                                <td class="p-3 text-red-600 font-semibold">{{ computeSpending(row) }}</td>
                                <td class="p-3 text-indigo-600 font-semibold">{{ row.total_logs }}</td>
                            </tr>
                            <transition name="fade-expand">
                                <tr v-if="expandedDates.includes(row.date)">
                                    <td colspan="6" class="bg-gray-50 px-4 py-2">
                                        <table class="w-full text-sm border rounded">
                                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                                <tr>
                                                    <th class="p-2 text-left">Profile</th>
                                                    <th class="p-2 text-left">Opening</th>
                                                    <th class="p-2 text-left">Topup</th>
                                                    <th class="p-2 text-left">Closing</th>
                                                    <th class="p-2 text-left">Spending</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="profile in profilesByDate[row.date]"
                                                    :key="profile.profile_id"
                                                    class="border-t hover:bg-white transition">
                                                    <td class="p-2 text-gray-700">
                                                        {{ profile.channel }} ({{ profile.account_id }})
                                                        <i v-if="row.date === today && profile.last_update_at && profile.spending_interval"
                                                            class="fas fa-info-circle text-gray-400 ml-1"
                                                            :title="`Spent $${formatDecimal(profile.last_spending)} in ${profile.spending_interval}. Latest update: ${profile.last_update_at}`">
                                                        </i>
                                                    </td>
                                                    <td class="p-2 text-gray-600">{{
                                                        formatDecimal(profile.opening_balance) }}</td>
                                                    <td class="p-2 text-blue-600">{{ formatDecimal(profile.topup_today)
                                                        }}</td>
                                                    <td class="p-2 text-gray-600">{{
                                                        formatDecimal(profile.closing_balance) }}</td>
                                                    <td class="p-2 text-red-600">{{ computeSpending(profile) }}</td>
                                                </tr>
                                                <tr
                                                    v-if="!profilesByDate[row.date] || profilesByDate[row.date].length === 0">
                                                    <td colspan="5" class="text-center text-gray-500 py-2">No breakdown
                                                        available</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </transition>
                        </template>

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
const profilesByDate = props.profilesByDate || {};
const isRefreshing = ref(false);
const expandedDates = ref([]);
const today = new Date().toLocaleDateString('en-CA');

const updateIntervalMinutes = parseFloat(props.settings?.profile_stats_update_interval || 10);
const updateIntervalMs = updateIntervalMinutes * 60000;
const nextRefreshTime = ref(null);
const remainingTime = ref(0);

const toggleExpand = (date) => {
    const index = expandedDates.value.indexOf(date);
    if (index > -1) {
        expandedDates.value.splice(index, 1);
    } else {
        expandedDates.value.push(date);
    }
};

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
    router.visit(window.location.pathname, {
        method: 'get',
        data: {
            refresh: Date.now(),
        },
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            summary.value = page.props.summary || [];
            isRefreshing.value = false;

            // Optionally re-expand latest row on auto-refresh
            expandedDates.value = [];
            if (summary.value.length > 0) {
                expandedDates.value.push(summary.value[0].date);
            }
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
    if (summary.value.length > 0) {
        expandedDates.value.push(summary.value[0].date);
    }

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

<style scoped>
.mt-2.sm\:mt-6 {
    margin-top: 0.75rem;
}
.fade-expand-enter-active,
.fade-expand-leave-active {
    transition: all 0.3s ease;
}
.fade-expand-enter-from,
.fade-expand-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
