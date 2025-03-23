<template>
    <Head :title="`Balance Snapshots – ${profile.account_id} – ${profile.network_channel?.name || 'Unknown'}`" />

    <AuthenticatedLayout>
        <div class="mx-auto">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        <GoBack :href="`/network-profiles`" /> Balance Snapshots for "{{ profile.account_id }}" – {{ profile.network_channel?.name || 'Unknown' }}
                    </h1>
                    <p v-if="remainingTime > 0" class="text-sm text-gray-500 mt-1">
                        Next auto-refresh in: <span class="font-semibold">{{ formattedRemainingTime }}</span>
                    </p>
                </div>
                <div class="flex items-center space-x-3 mt-4 sm:mt-0">
                    <Link :href="route('user.network-profiles.snapshots.create', profile.id)"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md shadow hover:bg-blue-700 transition">
                        + Create Snapshot
                    </Link>
                    <button @click="refreshSnapshots"
                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-md shadow hover:bg-green-700 transition"
                        title="Refresh Table">
                        <i class="fas fa-sync-alt" :class="{ 'animate-spin': isRefreshing }"></i>
                    </button>

                    <!-- Mute Toggle -->
                    <label for="muteAnnouncement" class="flex items-center cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" id="muteAnnouncement" v-model="muteAnnouncement" class="sr-only" />
                            <div class="toggle-bg w-10 h-4 rounded-full shadow-inner"></div>
                            <div class="toggle-dot absolute w-6 h-6 rounded-full shadow -left-1 -top-1 transition-transform duration-300"></div>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">Mute</span>
                    </label>
                </div>
            </div>

            <!-- No snapshots -->
            <div v-if="Object.keys(groupedSnapshots).length === 0" class="text-center text-gray-500 py-12">
                No snapshots found for this profile.
            </div>

            <!-- Grouped Snapshots by Date -->
            <div v-for="(snapshotsGroup, date) in groupedSnapshots" :key="date" class="mb-10">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ date }}</h2>
                <div class="overflow-x-auto">
                    <!-- Summary Cards for Each Day -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                        <div class="bg-white p-4 shadow rounded-md">
                            <p class="text-xs uppercase text-gray-500">Opening</p>
                            <p class="text-xl font-bold text-gray-800">
                                {{ formatDecimal(dailyStats[date]?.opening_balance) }}
                            </p>
                        </div>
                        <div class="bg-white p-4 shadow rounded-md relative">
                            <p class="text-xs uppercase text-gray-500 flex justify-between items-center">
                                Topups
                                <i v-if="!editingTopups[date]" class="fas fa-pencil-alt cursor-pointer text-gray-400 hover:text-gray-600"
                                    @click="enableTopupEdit(date)"></i>
                            </p>
                            <p v-if="!editingTopups[date]" class="text-xl font-bold text-blue-600">
                                {{ formatDecimal(dailyStats[date]?.topup_today) }}
                            </p>
                            <div v-else class="flex items-center space-x-2 mt-2">
                                <input v-model="topupInputs[date]" type="number" step="0.01"
                                    class="w-full px-2 py-1 border rounded text-sm focus:outline-none focus:ring"
                                />
                                <button @click="saveTopupValue(date)" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <div class="bg-white p-4 shadow rounded-md">
                            <p class="text-xs uppercase text-gray-500">Closing</p>
                            <p class="text-xl font-bold text-gray-800">
                                {{ formatDecimal(dailyStats[date]?.closing_balance) }}
                            </p>
                        </div>
                        <div class="bg-white p-4 shadow rounded-md">
                            <p class="text-xs uppercase text-gray-500">Spending</p>
                            <p class="text-xl font-bold text-red-600">
                                {{ computedSpending[date] }}
                            </p>
                        </div>
                    </div>
                    <table class="min-w-full text-sm bg-white shadow rounded-lg">
                        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                            <tr>
                                <th class="p-3 text-left">Time</th>
                                <th class="p-3 text-left">Balance</th>
                                <th class="p-3 text-left">Last 10 mins</th>
                                <th class="p-3 text-left">Last hour</th>
                                <th class="p-3 text-left">Today</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="snapshot in snapshotsGroup" :key="snapshot.id"
                                class="border-t hover:bg-gray-50 transition">
                                <td class="p-3 font-medium text-gray-800 whitespace-nowrap">
                                    {{ formatTime(snapshot.taken_at) }}
                                </td>
                                <td class="p-3 font-semibold text-blue-700">{{ formatDecimal(snapshot.balance) }}</td>
                                <td class="p-3 text-gray-700">
                                    <span v-html="getChangeDisplay(snapshot.last_10_minutes_diff, snapshot.last_10_minutes_tooltip)"></span>
                                </td>
                                <td class="p-3 text-grey-700">
                                    <span v-if="snapshot.last_hour_diff !== null">{{ formatDecimal(snapshot.last_hour_diff) }}</span>
                                </td>
                                <td class="p-3 text-grey-700">{{ formatDecimal(snapshot.today_diff) }}</td>
                                <td class="p-3 text-center">
                                    <Link :href="route('user.network-profiles.snapshots.edit', { network_profile: profile.id, snapshot: snapshot.id })"
                                        class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-pencil-alt text-sm"></i>
                                    </Link>
                                    <button @click="deleteSnapshot(snapshot.id)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="snapshots.links && snapshots.links.length > 3" class="flex justify-center mt-8">
                <button v-for="(paginationLink, index) in snapshots.links" :key="index"
                    :class="[
                        'mx-1 px-4 py-2 rounded border text-sm',
                        paginationLink.active
                            ? 'bg-orange-500 text-white border-orange-500'
                            : 'bg-white text-gray-800 border-gray-300 hover:bg-gray-100',
                        !paginationLink.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    :disabled="!paginationLink.url"
                    @click="router.get(paginationLink.url)"
                    v-html="paginationLink.label">
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed, onMounted, onUnmounted, reactive, watch } from 'vue';

const { props } = usePage();
const snapshots = ref(props.snapshots ?? { data: [] }); // Ensure snapshots is always an object
const profile = props.profile;
const isRefreshing = ref(false);
let refreshIntervalId = null;

const nextRefreshTime = ref(null);
const remainingTime = ref(0);

const dailyStats = props.stats;

const editingTopups = reactive({});
const topupInputs = reactive({});

const enableTopupEdit = (date) => {
    editingTopups[date] = true;
    topupInputs[date] = dailyStats[date]?.topup_today ?? 0;
};

const computedSpending = computed(() => {
    const spendingByDate = {};
    for (const date in dailyStats) {
        const stat = dailyStats[date] || {};
        const opening = parseFloat(stat.opening_balance ?? 0);
        const topup = parseFloat(stat.topup_today ?? 0);
        const closing = parseFloat(stat.closing_balance ?? 0);
        spendingByDate[date] = (opening + topup - closing).toFixed(2);
    }
    return spendingByDate;
});

const saveTopupValue = async (date) => {
    try {
        const value = parseFloat(topupInputs[date]);
        if (isNaN(value)) return;

        await router.put(route('user.network-profiles.stats.updateTopup', {
            profile: profile.id,
            date: date,
        }), {
            topup_today: value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                dailyStats[date].topup_today = value;
                editingTopups[date] = false;
            }
        });
    } catch (e) {
        console.error('Failed to update topup', e);
    }
};

// Format time properly
const formatTime = (dateString) => {
    if (!dateString) return '--:--:--';
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

// Ensure balance values only show up to 2 decimals
const formatDecimal = (value) => {
    return value !== null ? parseFloat(value).toFixed(2) : '0.00';
};

const getChangeDisplay = (value, tooltip = null) => {
    if (value === null || value === undefined) return 'N/A';
    const color = value > 0 ? 'text-green-600' : (value < 0 ? 'text-red-600' : 'text-gray-500');
    const arrow = value > 0 ? 'fa-arrow-up' : (value < 0 ? 'fa-arrow-down' : '');

    const base = `${formatDecimal(value)} <i class="fas ${arrow} ${color}"></i>`;
    if (tooltip) {
        return `${base} <i class="fas fa-info-circle text-gray-400 ml-1" title="${tooltip}"></i>`;
    }
    return base;
};

const formatToLocalISODate = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Group snapshots by date
const groupedSnapshots = computed(() => {
    if (!snapshots.value || !snapshots.value.data) return {};
    return snapshots.value.data.reduce((acc, snapshot) => {
        const date = formatToLocalISODate(snapshot.taken_at); // e.g., "2025-03-22"
        if (!acc[date]) acc[date] = [];
        acc[date].push(snapshot);
        return acc;
    }, {});
});

// Delete snapshot
const deleteSnapshot = async (id) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this snapshot?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#6b7280',
    });
    if (result.isConfirmed) {
        await router.delete(route('user.network-profiles.snapshots.destroy', {
            network_profile: profile.id,
            snapshot: id,
        }), {
            preserveScroll: true,
            replace: true,
            onSuccess: (page) => {
                snapshots.value = page.props.snapshots;
                Object.assign(dailyStats, page.props.stats);

                Swal.fire('Deleted!', 'The snapshot has been deleted.', 'success');
            }
        });
    }
};

// Compute remaining refresh time
const formattedRemainingTime = computed(() => {
    const minutes = Math.floor(remainingTime.value / 60000);
    const seconds = Math.floor((remainingTime.value % 60000) / 1000);
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
});

// Mute toggle with localStorage
const muteAnnouncement = ref(localStorage.getItem('muteSnapshots') === 'true');
watch(muteAnnouncement, (val) => {
    localStorage.setItem('muteSnapshots', val);
});

// Timer-related
const updateIntervalMinutes = parseFloat(props.settings?.profile_stats_update_interval || 10);
const updateIntervalMs = updateIntervalMinutes * 60000;

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
    return nextFull.getTime() + 60000 - now.getTime(); // +1 min offset
};

const updateRemainingTime = () => {
    if (nextRefreshTime.value) {
        remainingTime.value = nextRefreshTime.value - Date.now();
        if (remainingTime.value < 0) remainingTime.value = 0;
    }
};

const announceLastSnapshot = () => {
    const latest = snapshots.value?.data?.[0];
    if (!latest) return;

    const amount = parseFloat(latest.last_10_minutes_diff);
    const tooltip = latest.last_10_minutes_tooltip; // Will exist if gap > 10 mins

    let msg = '';

    if (tooltip) {
        // If snapshot is older than 10 mins, announce it clearly
        if (isNaN(amount) || amount === 0) {
            msg = `No balance change since the last snapshot. ${tooltip}.`;
        } else if (amount < 0) {
            msg = `Balance dropped by ${Math.abs(amount).toFixed(2)}. ${tooltip}.`;
        } else {
            msg = `Balance increased by ${amount.toFixed(2)}. ${tooltip}.`;
        }
    } else {
        // Standard 10-minute interval announcement
        if (isNaN(amount) || amount === 0) {
            msg = 'No balance change in the last 10 minutes.';
        } else if (amount < 0) {
            msg = `You have spent ${Math.abs(amount).toFixed(2)} in the last 10 minutes.`;
        } else {
            msg = `Top-up detected. Balance increased by ${amount.toFixed(2)} in the last 10 minutes.`;
        }
    }

    if (!muteAnnouncement.value && 'speechSynthesis' in window) {
        const utterance = new SpeechSynthesisUtterance(msg);
        utterance.lang = 'en-US';
        window.speechSynthesis.speak(utterance);
    }
};

// Auto-refresh function
const refreshSnapshots = () => {
    isRefreshing.value = true;
    router.visit(window.location.pathname + '?refresh=' + Date.now(), {
        preserveScroll: true,
        replace: true,
        onSuccess: (page) => {
            isRefreshing.value = false;
            snapshots.value = page.props.snapshots;
            Object.assign(dailyStats, page.props.stats);
            announceLastSnapshot();
        },
    });
};

let refreshInterval, initialTimeout, remainingTimer;
onMounted(() => {
    const firstDelay = computeFirstRefreshDelay();
    nextRefreshTime.value = new Date(Date.now() + firstDelay);
    updateRemainingTime();
    remainingTimer = setInterval(updateRemainingTime, 1000);

    initialTimeout = setTimeout(() => {
        refreshSnapshots();
        nextRefreshTime.value = new Date(Date.now() + updateIntervalMs);
        refreshInterval = setInterval(() => {
            refreshSnapshots();
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
/* iOS-style mute toggle */
label.flex.items-center.cursor-pointer>div.relative {
    display: inline-block;
    width: 2.5rem;
    /* 40px */
    height: 1rem;
    /* 16px */
    position: relative;
}

.toggle-bg {
    width: 100%;
    height: 100%;
    background-color: #d1d5db;
    /* Tailwind gray-300 */
    border-radius: 9999px;
    transition: background-color 0.3s ease;
}

.toggle-dot {
    position: absolute;
    top: -0.125rem;
    left: -0.125rem;
    width: 1.5rem;
    /* 24px */
    height: 1.5rem;
    /* 24px */
    background-color: white;
    border-radius: 9999px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

input:checked+.toggle-bg {
    background-color: #2563eb;
    /* Tailwind blue-600 */
}

input:checked+.toggle-bg+.toggle-dot {
    transform: translateX(100%);
}
</style>