<template>
    <div class="min-h-screen flex flex-col">
        <!-- Mobile Header -->
        <div class="bg-gray-800 text-white p-4 flex items-center justify-between sm:hidden">
            <h2 class="text-lg font-bold text-orange-400">Stats Tracker</h2>
            <button @click="toggleSidebar" class="text-white focus:outline-none">
                <i :class="showSidebar ? 'fas fa-times' : 'fas fa-bars'"></i>
            </button>
        </div>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside :class="[
                'bg-gray-800 text-white p-4 w-full sm:w-64 sm:block sm:relative z-40 sm:z-0 sm:translate-x-0',
                !isDesktop && showSidebar ? 'absolute top-[56px] animate-dropdown' : '',
                !isDesktop && !showSidebar ? 'hidden' : ''
            ]">
                <ul class="space-y-1">
                    <li>
                        <Link :href="'/dashboard'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/dashboard') }">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/network-channels'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/network-channels') }">
                        <i class="fas fa-network-wired mr-2"></i> Network Channels
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/links'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/links') }">
                        <i class="fas fa-link mr-2"></i> Links
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/network-profiles'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/network-profiles') }">
                            <i class="fas fa-user-circle mr-2"></i> Network Profiles
                            <span v-if="unlinkedProfilesCount > 0"
                                class="ml-auto bg-red-600 text-white text-xs font-semibold px-2 py-1 rounded-full"
                                :title="`${unlinkedProfilesCount} profile(s) missing links – please review`"
                            >
                                {{ unlinkedProfilesCount }}
                            </span>
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/daily-summary'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/daily-summary') }">
                            <i class="fas fa-calendar-day mr-2"></i> Daily Summary
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/daily-profit'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/daily-profit') }">
                            <i class="fas fa-sack-dollar mr-2"></i> Daily Profit
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/users'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/users') }">
                        <i class="fas fa-users mr-2"></i> Users
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/settings'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/settings') }">
                        <i class="fas fa-cog mr-2"></i> Settings
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/admin/tools'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition relative group"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/tools') }">
                            <i class="fas fa-tools mr-2 opacity-50 group-hover:opacity-80"></i>
                            <span class="line-through opacity-60 group-hover:opacity-90">Tools</span>
                            <span 
                                class="absolute top-1/2 right-5 text-[16px] text-yellow-400 px-1 rounded-full transform translate-x-1/2 -translate-y-1/2 shadow-md"
                                title="This section has been discontinued"
                            >
                                <i class="fas fa-circle-exclamation"></i>
                            </span>
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('admin.profile.edit')"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/admin/profile') }">
                        <i class="fas fa-user mr-2"></i> Edit Profile
                        </Link>
                    </li>
                    <li v-if="isImpersonating">
                        <button @click.prevent="leaveImpersonation"
                            class="flex items-center w-full px-2 py-2 rounded text-orange-400 hover:text-orange-300">
                            <i class="fas fa-user-shield mr-2"></i> Leave Impersonation
                        </button>
                    </li>
                    <li>
                        <button @click.prevent="logout"
                            class="flex items-center w-full px-2 py-2 rounded text-red-600 hover:text-red-400">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 bg-gray-100 p-6 overflow-x-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const showSidebar = ref(false);
const currentRoute = usePage().url;
const isImpersonating = usePage().props.auth.user.isImpersonating || false;
const isDesktop = ref(false);

// Fetch the count of unlinked network profiles from the backend
const unlinkedProfilesCount = ref(usePage().props.unlinkedProfilesCount || 0);

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

onMounted(() => {
    const checkDesktop = () => {
        isDesktop.value = window.innerWidth >= 640;
        showSidebar.value = isDesktop.value; // true for desktop, false for mobile
    };

    checkDesktop();
    window.addEventListener('resize', checkDesktop);
});

const leaveImpersonation = () => {
    router.post(route('admin.users.leave-impersonation'), {}, {
        onSuccess: () => router.reload({ preserveState: false }),
        onFinish: () => setTimeout(() => window.location.reload(), 300),
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<style scoped>
@keyframes dropdown {
    0% {
        transform: translateY(-10%);
        opacity: 0;
    }

    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-dropdown {
    animation: dropdown 0.3s ease forwards;
}
</style>