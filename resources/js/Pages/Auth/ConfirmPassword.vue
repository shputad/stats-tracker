<template>

    <Head title="Confirm Password" />

    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100 px-4">
        <div class="w-full max-w-md bg-white p-6 rounded-lg shadow">
            <h1 class="text-xl font-bold mb-6 text-center text-gray-800">Confirm Your Password</h1>

            <p class="text-sm text-gray-600 mb-6 text-center">
                This is a secure area of the application. Please confirm your password before continuing.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input v-model="form.password" id="password" type="password" autocomplete="current-password"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p v-if="form.errors.password" class="text-sm text-red-600 mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 transition text-white font-semibold py-2 px-4 rounded-md"
                        :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                        Confirm
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
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onSuccess: () => toast.success('Password confirmed.'),
        onError: () => toast.error('Incorrect password.'),
    });
};
</script>