<template>
    <Head title="Links" />

    <AuthenticatedLayout>
        <div class="mx-auto">
            <!-- Page Title -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Your Accessible Links</h1>
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="p-3 text-left">Name</th>
                            <th class="p-3 text-left">URL</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Stats</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="link in links" :key="link.id" class="border-t hover:bg-gray-50 transition">
                            <td class="p-3 font-medium text-gray-800">{{ link.name }}</td>
                            <td class="p-3">
                                <a :href="link.url" target="_blank"
                                   class="text-blue-600 hover:underline break-words block max-w-[400px] truncate"
                                   :title="link.url">
                                    {{ link.url }}
                                </a>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="[
                                    'px-2 py-1 rounded text-xs font-bold',
                                    link.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                ]">
                                    {{ link.status.charAt(0).toUpperCase() + link.status.slice(1) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <Link :href="route('user.links.stats.index', link.id)"
                                      class="text-purple-600 hover:text-purple-800" title="View Stats">
                                    <i class="fas fa-chart-simple"></i>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="links.length === 0">
                            <td colspan="4" class="p-4 text-center text-gray-500">No accessible links found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const { props } = usePage();
const links = ref(props.links);
</script>