<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Profile Stats</h1>
        <Link :href="route('admin.network-profiles.stats.create', profile.id)" class="mb-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Add Stat</Link>
        <table class="min-w-full border-collapse border border-gray-200 mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2">Type</th>
                    <th class="border border-gray-300 p-2">Amount</th>
                    <th class="border border-gray-300 p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="stat in stats" :key="stat.id">
                    <td class="border border-gray-300 p-2">{{ stat.type }}</td>
                    <td class="border border-gray-300 p-2">{{ stat.amount }}</td>
                    <td class="border border-gray-300 p-2">
                        <Link :href="route('admin.network-profiles.stats.edit', [profile.id, stat.id])" class="text-blue-600 underline">Edit</Link>
                        <button @click="deleteStat(stat.id)" class="text-red-600 underline ml-2">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const { props } = usePage();
const profile = props.profile;
const stats = props.stats;

const deleteStat = (id) => {
    if (confirm('Are you sure you want to delete this stat?')) {
        router.delete(route('admin.network-profiles.stats.destroy', [profile.id, id]));
    }
};
</script>
