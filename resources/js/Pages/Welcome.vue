<template>

    <Head title="Home" />

    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <header class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shadow">
            <h1 class="text-xl font-bold text-orange-400">Stats Tracker</h1>
            <div>
                <template v-if="!authUser">
                    <Link href="/login" class="text-white hover:text-orange-300 transition px-4">Login</Link>
                    <Link href="/register"
                        class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-600 transition ml-2">
                    Get Started
                    </Link>
                </template>
                <template v-else>
                    <Link href="/dashboard"
                        class="bg-orange-500 text-white px-4 py-2 rounded-md hover:bg-orange-500 transition">
                    Go to Dashboard
                    </Link>
                </template>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="flex-grow flex items-center justify-center px-6 min-h-[60vh]">
            <div class="max-w-3xl text-center">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Track and Manage Your Network Stats Seamlessly</h2>
                <p class="text-gray-600 text-lg mb-6">
                    Stats Tracker helps you efficiently manage links, channels, user profiles, and more — all in one
                    dashboard.
                </p>
                <Link href="/register"
                    class="bg-blue-600 text-white px-6 py-3 rounded-md font-semibold hover:bg-blue-700 transition">
                Start Tracking Now
                </Link>
            </div>
        </section>

        <!-- Features -->
        <section class="py-12 bg-white">
            <div class="max-w-5xl mx-auto px-6 grid sm:grid-cols-2 md:grid-cols-3 gap-8 text-center">
                <div>
                    <i class="fas fa-chart-line text-3xl text-blue-600 mb-2"></i>
                    <h3 class="font-bold text-lg mb-1">Real-Time Stats</h3>
                    <p class="text-gray-600 text-sm">Stay updated with accurate stats for every link and profile.</p>
                </div>
                <div>
                    <i class="fas fa-network-wired text-3xl text-purple-600 mb-2"></i>
                    <h3 class="font-bold text-lg mb-1">Network Channels</h3>
                    <p class="text-gray-600 text-sm">Organize channels and manage access for better tracking.</p>
                </div>
                <div>
                    <i class="fas fa-users-cog text-3xl text-green-600 mb-2"></i>
                    <h3 class="font-bold text-lg mb-1">User Management</h3>
                    <p class="text-gray-600 text-sm">Easily manage users, impersonate roles, and view activity.</p>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white py-4 text-center text-sm">
            &copy; {{ new Date().getFullYear() }} Stats Tracker. All rights reserved.
        </footer>
    </div>
</template>

<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';

const authUser = usePage().props.auth?.user || null;
</script>
