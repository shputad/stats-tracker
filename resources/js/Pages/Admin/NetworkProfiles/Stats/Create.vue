<template>
    <AdminLayout>
        <h1 class="text-2xl font-bold mb-6">Add Profile Stat</h1>
        <form @submit.prevent="submit">
            <div class="mb-4">
                <label for="type" class="block text-gray-700">Type</label>
                <input v-model="form.type" type="text" id="type" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.type" class="text-red-600 text-sm">{{ form.errors.type }}</span>
            </div>
            <div class="mb-4">
                <label for="amount" class="block text-gray-700">Amount</label>
                <input v-model="form.amount" type="number" id="amount" class="mt-1 block w-full border-gray-300 rounded-md" />
                <span v-if="form.errors.amount" class="text-red-600 text-sm">{{ form.errors.amount }}</span>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create</button>
        </form>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineProps({ profile: Object });

const form = useForm({
    type: '',
    amount: '',
});

const submit = () => {
    form.post(route('admin.network-profiles.stats.store', profile.id));
};
</script>
