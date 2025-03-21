<template>

    <Head title="Reset Password" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
            <h1 class="text-xl font-bold mb-6 text-center text-gray-800">Reset Your Password</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <input type="hidden" name="token" v-model="form.token" />

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" v-model="form.email"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="email" />
                    <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input id="password" type="password" v-model="form.password"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="new-password" />
                    <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm
                        Password</label>
                    <input id="password_confirmation" type="password" v-model="form.password_confirmation"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500"
                        autocomplete="new-password" />
                    <p v-if="form.errors.password_confirmation" class="text-sm text-red-600 mt-1">{{
                        form.errors.password_confirmation }}</p>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-2 px-4 rounded-md"
                        :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { usePage } from '@inertiajs/vue3';

const toast = useToast();
const page = usePage();

const form = useForm({
    token: page.props.token || '',
    email: page.props.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onSuccess: () => toast.success('Password reset successfully!'),
        onError: () => toast.error('Please check the form for errors.'),
    });
};
</script>