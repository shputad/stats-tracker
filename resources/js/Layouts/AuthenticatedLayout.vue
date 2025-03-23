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
                        <Link :href="'/links'" class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/links') }">
                        <i class="fas fa-link mr-2"></i> Links
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/network-profiles'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/network-profiles') }">
                        <i class="fas fa-user-circle mr-2"></i> Network Profiles
                        </Link>
                    </li>
                    <li>
                        <Link :href="'/daily-summary'"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/daily-summary') }">
                            <i class="fas fa-calendar-day mr-2"></i> Daily Summary
                        </Link>
                    </li>
                    <li>
                        <Link :href="route('user.profile.edit')"
                            class="flex items-center px-2 py-2 rounded hover:bg-gray-700 transition"
                            :class="{ 'bg-gray-700 text-orange-200': currentRoute.includes('/profile') }">
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

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

onMounted(() => {
    const checkDesktop = () => {
        isDesktop.value = window.innerWidth >= 640;
        showSidebar.value = isDesktop.value;
    };
    checkDesktop();
    window.addEventListener('resize', checkDesktop);
});

const leaveImpersonation = () => {
    router.post(route('user.leave-impersonation'), {}, {
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