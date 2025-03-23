<template>

    <Head title="Create Snapshot" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <GoBack :href="route('user.network-profiles.snapshots.index', profile.id)" class="mr-2" />
                Create Snapshot for "{{ profile.account_id }}" – {{ profile.network_channel?.name || 'Unknown' }}
            </h1>

            <form @submit.prevent="submit" class="bg-white p-6 shadow-lg rounded-lg space-y-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Balance Input -->
                    <div class="flex-1">
                        <label for="balance" class="block text-sm font-medium text-gray-700">Balance</label>
                        <input v-model="form.balance" type="number" step="0.01" min="0" id="balance"
                            class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p v-if="form.errors.balance" class="text-red-600 text-sm mt-1">{{ form.errors.balance }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 transition">
                        Save Snapshot
                    </button>
                    <Link :href="route('user.network-profiles.snapshots.index', profile.id)"
                        class="px-4 py-2 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 text-center transition">
                    Cancel
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import GoBack from '@/Components/GoBack.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';

const { props } = usePage();
const profile = props.profile;

const form = useForm({
    balance: ''
});

const submit = () => {
    form.post(route('user.network-profiles.snapshots.store', profile.id));
};
</script>