<template>
    <Head title="Admin Tools" />

    <AdminLayout>
        <div class="mx-auto max-w-md mt-20 p-6 bg-white shadow-md rounded">
            <h1 class="text-2xl font-bold mb-4">Enter Tools Password</h1>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <input v-model="form.password" type="password"
                        class="w-full border border-gray-300 rounded p-2"
                        :class="{ 'border-red-500': form.errors.password }"
                    />
                    <p v-if="form.errors.password" class="text-red-500 text-sm mt-1">
                        {{ form.errors.password }}
                    </p>
                </div>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Submit
                </button>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('admin.tools.password'), {
        preserveScroll: true,
    });
};
</script>
