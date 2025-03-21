<template>

    <Head title="Forgot Password" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
            <h1 class="text-xl font-bold mb-6 text-center text-gray-800">Forgot your password?</h1>
            <p class="text-sm text-gray-600 text-center mb-6">
                Enter your email and we'll send you a password reset link.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" v-model="form.email"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="email" />
                    <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-2 px-4 rounded-md"
                        :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                        Email Password Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onSuccess: () => toast.success('Password reset link sent successfully!'),
        onError: () => toast.error('Please check the form for errors.'),
    });
};
</script>