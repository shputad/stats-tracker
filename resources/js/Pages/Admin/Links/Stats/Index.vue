<template>
    <Head title="Link Stats" />

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">
            <GoBack :href="`/admin/links`"></GoBack> Stats for "{{ link.name }}" link
        </h1>
        <Link 
            :href="route('admin.links.stats.create', link.id)" 
            class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
            Create Stats
        </Link>
        <!-- Grouped Stats by Date -->
        <div v-for="(statsGroup, date) in groupedStats" :key="date" class="mt-4 mb-8">
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
                        <th class="border border-gray-300 p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(stat, index) in statsGroup" :key="stat.id">
                        <td class="border border-gray-300 p-2">{{ new Date(stat.created_at).toLocaleTimeString() }}</td>
                        <td class="border border-gray-300 p-2">{{ stat.log }}</td>
                        <td class="border border-gray-300 p-2">
                            <span>
                                {{ stat.last_10_minutes_diff }}
                                <span :class="getChangeClass(stat.last_10_minutes_diff, index, 'last_10_minutes_diff', statsGroup)"
                                    v-html="getChangeArrow(stat.last_10_minutes_diff, index, 'last_10_minutes_diff', statsGroup)"></span>
                            </span>
                        </td>
                        <td class="border border-gray-300 p-2">{{ stat.last_hour_diff !== null ? stat.last_hour_diff : '' }}</td>
                        <td class="border border-gray-300 p-2">{{ stat.today_diff }}</td>
                        <td class="border border-gray-300 p-2 text-center">
                            <Link :href="route('admin.links.stats.edit', {link: link.id, stat: stat.id})" class="text-blue-600 underline ml-2">
                                <i class="fas fa-pencil text-sm"></i>
                            </Link>
                            <button @click="deleteStat(stat.id)" class="text-red-600 underline ml-2">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<script setup>
import GoBack from '@/components/GoBack.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';

const { props } = usePage();
const link = props.link;
const stats = ref(props.stats);

/**
 * Group stats by date
 */
 const groupedStats = computed(() => {
    return stats.value.reduce((acc, stat) => {
        const date = new Date(stat.created_at).toLocaleDateString();
        if (!acc[date]) acc[date] = [];
        acc[date].push(stat);
        return acc;
    }, {});
});

/**
 * Get the CSS class based on the increase or decrease in the last 10 minutes count.
 */
const getChangeClass = (currentValue, index, key, group) => {
    const previousValue = (index + 1) < group.length ? group[index + 1][key] : 0;

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
const getChangeArrow = (currentValue, index, key, group) => {
    const previousValue = (index + 1) < group.length ? group[index + 1][key] : 0;
    const percentChange = previousValue ? ((currentValue - previousValue) / previousValue * 100).toFixed(0) : (currentValue ? 100 : 0);

    if (currentValue > previousValue) {
        return '<i class="fas fa-arrow-up"></i> ' + percentChange + '%';
    } else if (currentValue < previousValue) {
        return '<i class="fas fa-arrow-down"></i> ' + percentChange + '%';
    }

    return '';
};

const deleteStat = async (id) => {
    if (confirm('Are you sure you want to delete this stat?')) {
        await router.delete(route('admin.links.stats.destroy', {link: link.id, stat: id}), {
            onSuccess: () => {
                stats.value = stats.value.filter(stat => stat.id !== id);
            },
        });
    }
};
</script>
