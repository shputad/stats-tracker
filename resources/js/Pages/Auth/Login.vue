<template>

    <Head title="Login" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
            <h1 class="text-xl font-bold mb-6 text-center text-gray-800">Login to Your Account</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="form.email" id="email" type="email"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input v-model="form.password" id="password" type="password"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input v-model="form.remember" type="checkbox"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <Link href="/forgot-password" class="text-sm text-blue-600 hover:underline">Forgot password?</Link>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-2 px-4 rounded-md">
                        Login
                    </button>
                </div>
            </form>

            <div class="text-center text-sm text-gray-600 mt-4">
                Don't have an account?
                <Link href="/register" class="text-blue-600 hover:underline">Register</Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

const toast = useToast();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => toast.success('Logged in successfully!'),
        onError: () => toast.error('Invalid credentials. Please try again.'),
    });
};
</script>