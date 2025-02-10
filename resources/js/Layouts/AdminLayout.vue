<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <nav class="w-1/6 bg-gray-800 text-white p-4">
            <h2 class="text-lg font-bold mb-4 text-orange-400 cursor-default">Stats Tracker</h2>
            <ul>
                <li>
                    <Link
                        :href="'/dashboard'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/dashboard'), 'hover:text-gray-300': !currentRoute.includes('/dashboard') }"
                    >
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/network-channels'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/network-channels'), 'hover:text-gray-300': !currentRoute.includes('/admin/network-channels') }"
                    >
                        <i class="fas fa-network-wired mr-2"></i> Network Channels
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/links'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/links'), 'hover:text-gray-300': !currentRoute.includes('/admin/links') }"
                    >
                        <i class="fas fa-link mr-2"></i> Links
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/network-profiles'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/network-profiles'), 'hover:text-gray-300': !currentRoute.includes('/admin/network-profiles') }"
                    >
                        <i class="fas fa-user-circle mr-2"></i> Network Profiles
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/users'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/users'), 'hover:text-gray-300': !currentRoute.includes('/admin/users') }"
                    >
                        <i class="fas fa-users mr-2"></i> Users
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/settings'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/settings'), 'hover:text-gray-300': !currentRoute.includes('/admin/settings') }"
                    >
                        <i class="fas fa-cog mr-2"></i> Settings
                    </Link>
                </li>
                <li>
                    <Link
                        :href="'/admin/tools'"
                        class="block py-2 cursor-pointer"
                        :class="{ 'text-orange-200': currentRoute.includes('/admin/tools'), 'hover:text-gray-300': !currentRoute.includes('/admin/tools') }"
                    >
                        <i class="fas fa-tools mr-2"></i> Tools
                    </Link>
                </li>
                <li>
                    <button 
                        @click.prevent="logout" 
                        class="block w-full text-left py-2 text-red-600 hover:text-red-400 mt-4"
                    >
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 bg-gray-100 p-6">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';

// Get the current route from Inertia
const currentRoute = usePage().url;

const logout = () => {
    router.post(route('logout'));
};
</script>
