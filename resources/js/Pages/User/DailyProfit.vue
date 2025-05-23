<template>
    <Head title="Daily Profit" />
    <AuthenticatedLayout>
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Daily Profit</h1>
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

            <!-- Profit Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="bg-white p-4 shadow rounded-md">
                    <p class="text-xs uppercase text-gray-500">This Month Profit</p>
                    <p :class="thisMonthProfit >= 0 ? 'text-green-700' : 'text-red-600'" class="text-2xl font-bold">
                        {{ formatDecimal(thisMonthProfit) }}
                    </p>
                </div>
                <div class="bg-white p-4 shadow rounded-md">
                    <p class="text-xs uppercase text-gray-500">Last Month Profit</p>
                    <p :class="lastMonthProfit >= 0 ? 'text-green-700' : 'text-red-600'" class="text-2xl font-bold">
                        {{ formatDecimal(lastMonthProfit) }}
                    </p>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-xs text-gray-700 uppercase">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Spending</th>
                            <th class="p-3 text-left">Logs</th>
                            <th class="p-3 text-left">CR</th>
                            <th class="p-3 text-left">Profit</th>
                            <th class="p-3 text-left">Projected Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="row in summary" :key="row.date">
                            <tr @click="toggleExpand(row.date)" v-if="row.spending" class="border-t hover:bg-gray-50 cursor-pointer transition">
                                <td class="p-3 font-medium text-gray-800">
                                    {{ row.date }}
                                    <i v-if="row.date === today && (row.last_spending || row.last_logs)" class="fas fa-info-circle text-gray-400 ml-2"
                                        :title="`Spending: $${formatDecimal(row.last_spending)} in ${row.spending_interval || '—'} (Oldest update: ${row.oldest_snapshot_time || '—'})\nLogs: ${row.last_logs || 0} in ${row.logs_interval || '—'} (Last update: ${row.latest_log_time || '—'})`">
                                    </i>
                                    <i class="fas ml-2" :class="expandedDates.includes(row.date) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                                </td>
                                <td class="p-3 text-red-600 font-semibold">{{ formatDecimal(row.spending) }}</td>
                                <td class="p-3 text-indigo-600 font-semibold">{{ row.total_logs }}</td>
                                <td class="p-3 text-gray-700">{{ formatDecimal(row.cr) }}</td>
                                <td
                                    :class="calculateProfit(row).value >= 0 ? 'text-green-700' : 'text-red-600'"
                                    class="p-3 font-semibold"
                                >
                                    {{ formatDecimal(calculateProfit(row).value) }}

                                    <i
                                        v-if="calculateProfit(row).hasSpecial"
                                        class="fas fa-info-circle text-gray-400 ml-1"
                                        :title="calculateProfit(row).tooltip"
                                    ></i>
                                </td>
                                <td class="p-3 font-semibold text-gray-500">
                                    <span
                                        v-if="row.date === today"
                                        :class="calculateProjectedProfit(row) >= 0 ? 'text-blue-600' : 'text-red-600'"
                                    >
                                        {{ formatDecimal(calculateProjectedProfit(row)) }}
                                    </span>
                                    <span v-else class="text-gray-400 italic">–</span>
                                </td>
                            </tr>

                            <!-- Expanded Per Link -->
                            <tr v-if="expandedDates.includes(row.date)">
                                <td colspan="6" class="bg-gray-50 px-4 py-3">
                                    <table class="w-full text-sm border rounded">
                                        <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                            <tr>
                                                <th class="p-2 text-left">Link</th>
                                                <th class="p-2 text-left">Spending</th>
                                                <th class="p-2 text-left">Logs</th>
                                                <th class="p-2 text-left">CR</th>
                                                <th class="p-2 text-left">Profit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="link in row.links" :key="link.link_id" class="border-t hover:bg-white transition">
                                                <td class="p-2 text-gray-700">{{ link.name || 'Link #' + link.link_id }}</td>
                                                <td class="p-2 text-red-600">{{ formatDecimal(link.spending) }}</td>
                                                <td class="p-2 text-indigo-600">{{ link.logs }}</td>
                                                <td class="p-2 relative">
                                                    <div v-if="!editingCR[`${row.date}-${link.link_id}`]" class="w-4 flex items-center gap-2">
                                                        <span>{{ formatDecimal(link.cr) }}</span>
                                                    </div>
                                                </td>
                                                <td class="p-2 font-semibold text-gray-800">
                                                    {{ formatDecimal(calculateLinkProfit(link, row)) }}
                                                </td>
                                            </tr>
                                            <tr v-if="!Object.values(row.links).length">
                                                <td colspan="6" class="text-center py-3 text-gray-400">No link data</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
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
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';

const { props } = usePage();
const currentUser = props.auth?.user;
const summary = ref(props.summary || []);
const isRefreshing = ref(false);
const profitPercentage = ref(props.profitPercentage || 0);
const today = new Date().toLocaleDateString('en-CA');

const updateIntervalMinutes = parseFloat(props.settings?.profile_stats_update_interval || 10);
const updateIntervalMs = updateIntervalMinutes * 60000;
const nextRefreshTime = ref(null);
const remainingTime = ref(0);

const expandedDates = ref([]);
const editingCR = reactive({});
const crInputs = reactive({});

const thisMonthProfit = computed(() => {
    const now = new Date();
    const month = now.getMonth() + 1;
    const year = now.getFullYear();

    return summary.value.reduce((total, row) => {
        const [rowYear, rowMonth] = row.date.split('-').map(Number);
        if (rowYear === year && rowMonth === month) {
            return total + calculateProfit(row).value;
        }
        return total;
    }, 0);
});

const lastMonthProfit = computed(() => {
    const now = new Date();
    const month = now.getMonth();
    const year = now.getFullYear();

    return summary.value.reduce((total, row) => {
        const [rowYear, rowMonth] = row.date.split('-').map(Number);
        if (rowYear === year && rowMonth === month) {
            return total + calculateProfit(row).value;
        }
        return total;
    }, 0);
});

const overrideForm = useForm({
    link_id: null,
    date: '',
    override_cr: 0,
});

const toggleExpand = (date) => {
    const idx = expandedDates.value.indexOf(date);
    idx > -1 ? expandedDates.value.splice(idx, 1) : expandedDates.value.push(date);
};

const formatDecimal = (val) => val !== null ? parseFloat(val).toFixed(2) : '0.00';

const formattedRemainingTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60000);
    const seconds = Math.floor((remainingTime.value % 60000) / 1000);
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

const calculateProfit = (row) => {
    const baseProfit = ((row.spending * row.cr) - row.spending);

    const percentage = currentUser?.profit_percentage ?? 0;
    const minCap = currentUser?.min_daily_profit_cap ?? null;
    const specialPercent = currentUser?.special_profit_percentage ?? null;

    let profit = baseProfit * (percentage / 100);
    let tooltip = `Total Average Profit: ${formatDecimal(baseProfit)}`;
    tooltip += `\nBase: ${formatDecimal(baseProfit)} × ${percentage}% = ${formatDecimal(profit)}`;
    let hasSpecial = false;

    if (minCap && specialPercent && profit < minCap) {
        const specialProfit = baseProfit * (specialPercent / 100);
        const cappedProfit = specialProfit > minCap ? minCap : specialProfit;

        tooltip += `\nSpecial: ${formatDecimal(baseProfit)} × ${specialPercent}% = ${formatDecimal(specialProfit)}`;

        if (specialProfit > minCap) {
            tooltip += `\nCapped to: ${minCap}`;
        }

        profit = cappedProfit;
        hasSpecial = true;
    }

    return {
        value: parseFloat(profit.toFixed(2)),
        tooltip,
        hasSpecial,
    };
};

const calculateProjectedProfit = (row) => {
    if (row.date !== today) return calculateProfit(row).value;
    const hoursPassed = new Date().getHours() + (new Date().getMinutes() / 60);
    return hoursPassed === 0 ? 0 : parseFloat((calculateProfit(row).value / hoursPassed * 24).toFixed(2));
};

const calculateLinkProfit = (link, row) => {
    let proratedSpending = 0;

    if (row.total_logs > link.logs) {
        proratedSpending = link.spending / row.total_logs * link.logs;
    } else {
        proratedSpending = link.spending;
    }

    const profit = ((link.spending * link.cr) - proratedSpending) * (profitPercentage.value / 100);
    return parseFloat(profit.toFixed(2));
};

const refreshSummary = () => {
    isRefreshing.value = true;
    router.visit(window.location.pathname, {
        method: 'get',
        data: { refresh: Date.now() },
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            summary.value = page.props.summary || [];
            profitPercentage.value = page.props.profitPercentage || 0;
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
<style scoped>
.mt-2.sm\:mt-6 {
    margin-top: 0.75rem;
}
</style>