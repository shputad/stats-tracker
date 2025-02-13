<template>
    <Head title="Admin Tools" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6">Admin Tools</h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tool in tools" :key="tool.name"
                    class="bg-white shadow-md rounded-lg hover:shadow-xl transition-all duration-300">
                    <Link :href="tool.url" class="flex items-center space-x-4 p-4">
                        <i :class="[tool.icon, 'text-xl text-blue-500']"></i>
                        <div>
                            <h2 class="text-lg font-semibold">{{ tool.name }}</h2>
                            <p class="text-gray-600 text-sm">{{ tool.description }}</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const tools = ref([
    {
        name: 'Command Builder',
        icon: 'fas fa-terminal',
        description: 'Build and test commands easily',
        url: '/admin/tools/command-builder'
    },
    {
        name: 'Lander Builder',
        icon: 'far fa-file-code',
        description: 'Create and manage landing pages',
        url: '/admin/tools/lander-builder'
    }
]);
</script>