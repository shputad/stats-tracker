<template>
    <Head title="Admin Tools" />

    <AdminLayout>
        <div class="mx-auto">
            <h1 class="text-2xl font-bold mb-6 flex justify-between items-center">
                Admin Tools
                <button
                    @click="logoutTools"
                    class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded shadow"
                >
                    Tools Logout
                </button>
            </h1>

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
import { Head, Link, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';

const toast = useToast();

const tools = ref([
    {
        name: 'Payload Builder',
        icon: 'fas fa-project-diagram',
        description: 'Build payload easily',
        url: '/admin/tools/payload-builder'
    },
    {
        name: 'Stager Builder',
        icon: 'fas fa-spinner',
        description: 'Build stager easily',
        url: '/admin/tools/stager-builder'
    },
    {
        name: 'Command Builder',
        icon: 'fas fa-terminal',
        description: 'Build and test commands easily',
        url: '/admin/tools/command-builder'
    },
    {
        name: 'Lander Templates',
        icon: 'far fa-file-lines',
        description: 'Create and manage landing templates',
        url: '/admin/tools/lander-templates'
    },
    {
        name: 'Lander Builder',
        icon: 'far fa-file-code',
        description: 'Create and manage landing pages',
        url: '/admin/tools/lander-builder'
    }
]);

const logoutTools = () => {
    Swal.fire({
        title: 'Logout Tools?',
        text: 'Are you sure you want to revoke access to the tools section?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e3342f',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, logout',
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('admin.tools.password.logout'), {}, {
                preserveScroll: true,
                onSuccess: () => toast.success('Tools access revoked.'),
                onError: () => toast.error('Logout failed.'),
            });
        }
    });
};
</script>